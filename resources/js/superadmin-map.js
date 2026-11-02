import '../css/superadmin-map.css';

const statusLabels = { menunggu: 'Menunggu', diproses: 'Diproses', selesai: 'Selesai' };
const statusColors = { menunggu: 'bg-amber-500', diproses: 'bg-blue-600', selesai: 'bg-emerald-500' };

function createTextElement(tag, text, className = '') {
    const element = document.createElement(tag);
    element.textContent = text ?? '';
    element.className = className;
    return element;
}

function createPopup(reports) {
    const popup = document.createElement('div');
    popup.className = 'report-map-popup';

    for (const report of reports) {
        const article = document.createElement('article');
        article.append(
            createTextElement('h3', report.judul),
            createTextElement('p', `${report.kategori} · ${statusLabels[report.status] ?? 'Status lainnya'}`),
            createTextElement('p', report.alamat || 'Alamat belum tersedia'),
            createTextElement('p', report.instansi),
            createTextElement('p', report.tanggal),
        );
        const link = createTextElement('a', 'Lihat detail laporan →');
        link.href = report.url;
        article.append(link);
        popup.append(article);
    }

    return popup;
}

function initializeReportMap() {
    const container = document.getElementById('report-map');

    if (!container) {
        return;
    }

    const message = document.getElementById('map-message');
    const retry = document.getElementById('map-retry');
    const list = document.getElementById('map-report-list');
    const listSummary = document.getElementById('map-list-summary');
    const empty = document.getElementById('map-empty');
    const filters = document.getElementById('map-filters');
    const panel = document.getElementById('report-map-panel');
    const fullscreen = document.getElementById('map-fullscreen');
    const satelliteNote = document.getElementById('map-satellite-note');
    const leaflet = window.L;

    if (!leaflet) {
        message.textContent = 'Peta belum dapat dimuat. Periksa koneksi internet atau buka menu Seluruh laporan.';
        listSummary.textContent = 'Peta tidak tersedia.';
        list.setAttribute('aria-busy', 'false');
        retry.hidden = false;
        retry.addEventListener('click', () => window.location.reload());
        return;
    }

    let regionBounds;

    try {
        regionBounds = leaflet.latLngBounds(JSON.parse(container.dataset.regionBounds));

        if (!regionBounds.isValid()) {
            throw new Error('Batas wilayah tidak valid.');
        }
    } catch {
        message.textContent = 'Batas wilayah Indramayu belum dapat dimuat. Muat ulang halaman atau buka menu Seluruh laporan.';
        listSummary.textContent = 'Peta tidak tersedia.';
        list.setAttribute('aria-busy', 'false');
        retry.hidden = false;
        retry.addEventListener('click', () => window.location.reload());
        return;
    }
    const map = leaflet.map(container, {
        fadeAnimation: false,
        maxBounds: regionBounds.pad(0.15),
        maxBoundsViscosity: 1,
        minZoom: 8,
    });

    function resetRegionView() {
        map.fitBounds(regionBounds, { padding: [24, 24], animate: false });
    }

    resetRegionView();
    map.setMinZoom(map.getZoom());
    map.on('resize', () => {
        map.setMinZoom(map.getBoundsZoom(regionBounds, false, leaflet.point(48, 48)));
    });

    const streetTiles = leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        noWrap: true,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    });
    // The 2025 public endpoint is licensed for non-commercial use under CC BY-NC-SA 4.0.
    const satelliteTiles = leaflet.tileLayer('https://tiles.maps.eox.at/wmts/1.0.0/s2cloudless-2025_3857/default/g/{z}/{y}/{x}.jpg', {
        maxZoom: 14,
        maxNativeZoom: 14,
        noWrap: true,
        updateWhenIdle: true,
        attribution: '<a href="https://cloudless.eox.at">EOxCloudless</a> by <a href="https://eox.at">EOX IT Services GmbH</a> (Contains modified Copernicus Sentinel data 2025) | <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/">CC BY-NC-SA 4.0</a>',
    });
    const basemapControl = leaflet.control.layers({
        'Peta biasa': streetTiles,
        'Satelit (2025)': satelliteTiles,
    }, null, { collapsed: false, position: 'topright' }).addTo(map);
    basemapControl.getContainer().setAttribute('role', 'group');
    basemapControl.getContainer().setAttribute('aria-label', 'Jenis peta');

    const markers = leaflet.layerGroup().addTo(map);
    let pendingRequest;
    let pendingRequestUrl = null;
    let renderedReportsSignature = null;
    let updateTimer;
    let requestVersion = 0;
    let selectedReportId = null;
    let tileError = false;
    let popupPanning = false;
    let activeBasemap = streetTiles;
    let reportMessage = 'Memuat lokasi laporan...';
    let reportLoadFailed = false;

    function renderMapMessage() {
        message.textContent = reportMessage;
        if (tileError) {
            message.textContent += activeBasemap === satelliteTiles
                ? ' Sebagian citra satelit gagal dimuat. Pilih Peta biasa atau coba lagi; pin laporan tetap dapat digunakan.'
                : ' Sebagian latar peta gagal dimuat. Pin dan detail laporan tetap dapat digunakan.';
        }
        retry.hidden = !reportLoadFailed && !tileError;
    }

    for (const layer of [streetTiles, satelliteTiles]) {
        layer.on('loading', () => {
            if (layer === activeBasemap) {
                tileError = false;
                renderMapMessage();
            }
        });
        layer.on('tileerror', () => {
            if (layer === activeBasemap && map.hasLayer(layer)) {
                tileError = true;
                renderMapMessage();
            }
        });
    }

    map.on('baselayerchange', ({ layer }) => {
        activeBasemap = layer;
        tileError = false;
        satelliteNote.hidden = layer !== satelliteTiles;
        renderMapMessage();
    });

    streetTiles.addTo(map);

    function renderReports(reports, meta) {
        empty.hidden = reports.length !== 0;
        listSummary.textContent = `${Math.min(reports.length, 30)} terbaru dari ${meta.total.toLocaleString('id-ID')} laporan di area ini`;
        const signature = JSON.stringify(reports);

        if (signature === renderedReportsSignature) {
            return;
        }

        markers.clearLayers();
        list.replaceChildren();
        const grouped = new Map();
        const markerById = new Map();

        for (const report of reports) {
            const key = `${report.latitude},${report.longitude}`;
            if (!grouped.has(key)) {
                grouped.set(key, []);
            }
            grouped.get(key).push(report);
        }

        for (const group of grouped.values()) {
            const report = group[0];
            const uniformStatus = group.every((item) => item.status === report.status);
            const status = uniformStatus && statusLabels[report.status] ? report.status : 'lainnya';
            const marker = leaflet.marker([report.latitude, report.longitude], {
                title: group.length === 1 ? report.judul : `${group.length} laporan pada lokasi ini`,
                icon: leaflet.divIcon({
                    className: 'report-map-marker',
                    html: `<span class="report-map-pin report-map-pin--${status}"><span>${group.length > 1 ? group.length : '•'}</span></span>`,
                    iconSize: [30, 36],
                    iconAnchor: [15, 36],
                    popupAnchor: [0, -32],
                }),
            }).bindPopup(createPopup(group), { maxWidth: 290, minWidth: 220, autoPanPadding: [24, 24] }).addTo(markers);

            marker.on('click', () => { selectedReportId = report.id; });
            for (const item of group) {
                markerById.set(item.id, marker);
            }
        }

        for (const report of reports.slice(0, 30)) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'flex w-full cursor-pointer gap-3 p-4 text-left hover:bg-blue-50 focus-visible:bg-blue-50';
            const dot = createTextElement('span', '', `mt-1 size-2 shrink-0 rounded-full ${statusColors[report.status] ?? 'bg-slate-400'}`);
            dot.setAttribute('aria-hidden', 'true');
            const content = document.createElement('span');
            content.className = 'grid min-w-0 gap-1';
            content.append(
                createTextElement('span', report.judul, 'text-xs font-semibold text-slate-700'),
                createTextElement('span', report.alamat || report.kategori, 'truncate text-[11px] text-slate-400'),
                createTextElement('span', `${statusLabels[report.status] ?? 'Status lainnya'} · ${report.tanggal ?? ''}`, 'text-[10px] text-slate-500'),
            );
            button.append(dot, content);
            button.addEventListener('click', () => {
                selectedReportId = report.id;
                const zoom = Math.min(map.getMaxZoom(), Math.max(map.getZoom(), 16));
                map.setView([report.latitude, report.longitude], zoom);
                markerById.get(report.id)?.openPopup();
            });
            list.append(button);
        }

        if (reports.length === 0) {
            list.append(createTextElement('p', 'Geser peta atau ubah filter untuk mencari laporan.', 'p-4 text-xs leading-relaxed text-slate-400'));
        }

        renderedReportsSignature = signature;
        markerById.get(selectedReportId)?.openPopup();
    }

    async function loadReports() {
        clearTimeout(updateTimer);
        const url = new URL(container.dataset.source, window.location.origin);

        for (const [key, value] of new FormData(filters)) {
            if (value) {
                url.searchParams.set(key, value);
            }
        }

        const bounds = map.getBounds();
        const south = Math.max(regionBounds.getSouth(), bounds.getSouth());
        const north = Math.min(regionBounds.getNorth(), bounds.getNorth());
        const west = Math.max(regionBounds.getWest(), bounds.getWest());
        const east = Math.min(regionBounds.getEast(), bounds.getEast());

        if (south > north || west > east) {
            pendingRequest?.abort();
            pendingRequest = null;
            pendingRequestUrl = null;
            ++requestVersion;
            renderReports([], { total: 0 });
            reportMessage = 'Area ini berada di luar cakupan peta Indramayu. Gunakan Kembali ke Indramayu untuk melihat laporan.';
            reportLoadFailed = false;
            renderMapMessage();
            list.setAttribute('aria-busy', 'false');
            return;
        }

        url.searchParams.set('south', south);
        url.searchParams.set('north', north);
        url.searchParams.set('west', west);
        url.searchParams.set('east', east);

        if (url.href === pendingRequestUrl) {
            return;
        }

        pendingRequest?.abort();
        const controller = new AbortController();
        pendingRequest = controller;
        pendingRequestUrl = url.href;
        const version = ++requestVersion;

        reportMessage = 'Memuat lokasi laporan...';
        reportLoadFailed = false;
        renderMapMessage();
        list.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (version !== requestVersion) {
                return;
            }

            if (response.status === 401 || response.status === 403) {
                markers.clearLayers();
                list.replaceChildren();
                renderedReportsSignature = null;
                throw new Error('Sesi berakhir atau akses ditolak. Silakan masuk kembali.');
            }
            if (!response.ok) {
                throw new Error('Lokasi laporan belum dapat dimuat. Silakan coba lagi.');
            }

            const result = await response.json();
            if (version !== requestVersion) {
                return;
            }
            renderReports(result.data, result.meta);
            reportMessage = result.meta.dibatasi
                ? `${result.meta.ditampilkan} dari ${result.meta.total.toLocaleString('id-ID')} laporan ditampilkan. Perbesar peta untuk melihat titik lainnya.`
                : `${result.meta.total.toLocaleString('id-ID')} laporan berkoordinat valid pada area peta Indramayu dan filter ini.`;
            renderMapMessage();
        } catch (error) {
            if (error.name !== 'AbortError' && version === requestVersion) {
                reportMessage = error.message === 'Failed to fetch'
                    ? 'Koneksi terputus. Periksa jaringan lalu coba lagi.'
                    : error.message;
                listSummary.textContent = 'Data belum berhasil diperbarui.';
                reportLoadFailed = true;
                renderMapMessage();
            }
        } finally {
            if (version === requestVersion) {
                pendingRequest = null;
                pendingRequestUrl = null;
                list.setAttribute('aria-busy', 'false');
            }
        }
    }

    map.on('autopanstart', () => { popupPanning = true; });
    map.on('moveend', () => {
        if (popupPanning) {
            popupPanning = false;
            return;
        }

        clearTimeout(updateTimer);
        updateTimer = setTimeout(() => loadReports(), 300);
    });

    filters.addEventListener('submit', (event) => event.preventDefault());
    filters.addEventListener('change', () => {
        selectedReportId = null;
        map.closePopup();
        loadReports();
    });

    document.getElementById('map-reset').addEventListener('click', () => {
        filters.reset();
        selectedReportId = null;
        map.closePopup();
        resetRegionView();
        loadReports();
    });

    retry.addEventListener('click', () => {
        tileError = false;
        activeBasemap.redraw();
        loadReports();
    });

    if (panel.requestFullscreen && document.fullscreenEnabled) {
        fullscreen.hidden = false;
        fullscreen.addEventListener('click', async () => {
            try {
                if (document.fullscreenElement === panel) {
                    await document.exitFullscreen();
                } else {
                    await panel.requestFullscreen();
                }
            } catch {
                message.textContent = 'Mode layar penuh tidak tersedia pada browser ini.';
            }
        });
        document.addEventListener('fullscreenchange', () => {
            fullscreen.textContent = document.fullscreenElement === panel ? 'Keluar layar penuh' : 'Layar penuh';
            map.invalidateSize();
        });
    }

    loadReports();
}

if (document.readyState !== 'complete') {
    document.addEventListener('DOMContentLoaded', initializeReportMap, { once: true });
} else {
    initializeReportMap();
}
