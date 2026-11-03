import '../css/superadmin-map.css';

const statusLabels = { menunggu: 'Menunggu Verifikasi', diproses: 'Sedang Diproses', selesai: 'Selesai' };

function createPopup(report) {
    const popup = document.createElement('div');
    popup.className = 'report-map-popup';

    const title = document.createElement('h3');
    title.textContent = report.judul;
    const category = document.createElement('p');
    category.textContent = `${report.kategori || 'Lainnya'} · ${statusLabels[report.status] ?? 'Status lainnya'}`;
    const address = document.createElement('p');
    address.textContent = report.alamat || 'Alamat belum tersedia';
    const link = document.createElement('a');
    link.href = report.url;
    link.textContent = 'Lihat detail laporan →';
    popup.append(title, category, address, link);

    return popup;
}

export function initializeReportMap(container, reports, bounds, selectedReportId = null) {
    const leaflet = window.L;
    const regionBounds = leaflet.latLngBounds(bounds);
    const map = leaflet.map(container, {
        maxBounds: regionBounds.pad(0.15),
        maxBoundsViscosity: 1,
        minZoom: 8,
    });
    const markers = new Map();

    map.fitBounds(regionBounds, { padding: [24, 24] });
    map.setMinZoom(map.getZoom());
    map.on('resize', () => {
        map.setMinZoom(map.getBoundsZoom(regionBounds, false, leaflet.point(48, 48)));
    });
    leaflet.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        noWrap: true,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    for (const report of reports) {
        if (!Number.isFinite(report.latitude) || !Number.isFinite(report.longitude)
            || report.latitude < -90 || report.latitude > 90
            || report.longitude < -180 || report.longitude > 180) {
            continue;
        }

        const status = ['menunggu', 'diproses', 'selesai'].includes(report.status) ? report.status : 'lainnya';
        const marker = leaflet.marker([report.latitude, report.longitude], {
            title: report.judul,
            icon: leaflet.divIcon({
                className: 'report-map-marker',
                html: `<span class="report-map-pin report-map-pin--${status}"><span>•</span></span>`,
                iconSize: [30, 36],
                iconAnchor: [15, 36],
                popupAnchor: [0, -32],
            }),
        }).bindPopup(createPopup(report)).addTo(map);
        markers.set(String(report.id), marker);
    }

    return {
        focus(reportId) {
            const marker = markers.get(String(reportId));

            if (!marker) {
                return false;
            }

            map.setView(marker.getLatLng(), Math.max(map.getZoom(), 16));
            marker.openPopup();

            return true;
        },
    };
}
