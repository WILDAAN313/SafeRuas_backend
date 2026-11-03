import { initializeReportMap } from './report-map.js';

function initializeReportManagement() {
    const searchForms = document.querySelectorAll('[data-laporan-search]');
    searchForms.forEach((form) => {
        form.addEventListener('submit', () => {
            const searchInput = form.querySelector('[name="cari"]');
            if (searchInput) {
                searchInput.value = searchInput.value.trim();
            }
        });
    });

    const rows = [...document.querySelectorAll('[data-report-row]')];
    const panel = document.getElementById('selected-report-panel');

    if (rows.length && panel) {
        let map;
        const mapContainer = document.getElementById('admin-laporan-map');

        if (mapContainer && window.L) {
            try {
                map = initializeReportMap(
                    mapContainer,
                    JSON.parse(panel.dataset.mapReports),
                    JSON.parse(panel.dataset.mapBounds),
                );
            } catch {
                mapContainer.textContent = 'Peta belum dapat dimuat. Periksa koneksi internet untuk memuat Leaflet dan OpenStreetMap.';
                mapContainer.classList.add('grid', 'place-items-center', 'p-4', 'text-center', 'text-xs', 'text-slate-600');
            }
        } else if (mapContainer) {
            mapContainer.textContent = 'Peta belum dapat dimuat. Periksa koneksi internet untuk memuat Leaflet dan OpenStreetMap.';
            mapContainer.classList.add('grid', 'place-items-center', 'p-4', 'text-center', 'text-xs', 'text-slate-600');
        }

        const detailFields = {
            id: document.getElementById('detail-report-id'),
            title: document.getElementById('detail-report-title'),
            address: document.getElementById('detail-report-address'),
            reporter: document.getElementById('detail-reporter'),
            email: document.getElementById('detail-email'),
            category: document.getElementById('detail-category'),
            coordinates: document.getElementById('detail-coordinates'),
            description: document.getElementById('detail-report-description'),
            createdAt: document.getElementById('detail-created-at'),
            status: document.getElementById('detail-report-status'),
            photo: document.querySelector('[data-detail-photo]'),
            noPhoto: document.getElementById('detail-no-photo'),
            noLocation: document.getElementById('detail-no-location'),
            openLink: document.getElementById('detail-open-link'),
            statusForm: document.getElementById('detail-status-form'),
            nextStatus: document.getElementById('detail-next-status'),
            statusComplete: document.getElementById('detail-status-complete'),
        };

        function selectReport(row) {
            for (const item of rows) {
                const selected = item === row;
                item.classList.toggle('bg-blue-50', selected);
                item.setAttribute('aria-selected', String(selected));
            }

            const data = row.dataset;
            const statusLabels = { menunggu: 'Menunggu Verifikasi', diproses: 'Sedang Diproses', selesai: 'Selesai' };
            const statusStyles = {
                menunggu: ['bg-red-100', 'text-red-700'],
                diproses: ['bg-blue-100', 'text-blue-700'],
                selesai: ['bg-emerald-100', 'text-emerald-700'],
            };

            detailFields.id.textContent = `#REP-${data.reportId}`;
            detailFields.title.textContent = data.title;
            detailFields.address.textContent = data.address;
            detailFields.reporter.textContent = data.reporter;
            detailFields.email.textContent = data.email;
            detailFields.category.textContent = data.category;
            detailFields.description.textContent = data.description || 'Pelapor tidak memberikan deskripsi.';
            detailFields.createdAt.textContent = data.createdAt;
            detailFields.status.textContent = statusLabels[data.status] ?? data.status;
            detailFields.status.className = 'rounded-full px-2.5 py-1 text-[10px] font-bold';
            detailFields.status.classList.add(...(statusStyles[data.status] ?? ['bg-slate-100', 'text-slate-600']));
            detailFields.openLink.href = data.detailUrl;

            const hasPhoto = Boolean(data.photo);
            detailFields.photo.hidden = !hasPhoto;
            detailFields.noPhoto.hidden = hasPhoto;
            if (hasPhoto) {
                detailFields.photo.src = data.photo;
            } else {
                detailFields.photo.removeAttribute('src');
            }

            const latitude = data.latitude.trim() === '' ? Number.NaN : Number(data.latitude);
            const longitude = data.longitude.trim() === '' ? Number.NaN : Number(data.longitude);
            const hasCoordinates = Number.isFinite(latitude) && Number.isFinite(longitude)
                && latitude >= -90 && latitude <= 90 && longitude >= -180 && longitude <= 180;
            detailFields.coordinates.textContent = hasCoordinates ? `${latitude}, ${longitude}` : 'Tidak tersedia';
            detailFields.noLocation.hidden = hasCoordinates;
            if (hasCoordinates) {
                map?.focus(data.reportId);
            }

            detailFields.statusForm.action = data.statusUrl;
            detailFields.statusForm.hidden = data.status === 'selesai';
            detailFields.statusComplete.hidden = data.status !== 'selesai';
            if (data.status === 'menunggu') {
                detailFields.nextStatus.value = 'diproses';
                detailFields.nextStatus.options[0].hidden = false;
                detailFields.nextStatus.options[1].hidden = true;
            } else {
                detailFields.nextStatus.value = 'selesai';
                detailFields.nextStatus.options[0].hidden = true;
                detailFields.nextStatus.options[1].hidden = false;
            }
        }

        for (const row of rows) {
            row.addEventListener('click', (event) => {
                if (!event.target.closest('a')) {
                    selectReport(row);
                }
            });
            row.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    selectReport(row);
                }
            });
        }

        const initiallySelected = rows.find((row) => row.getAttribute('aria-selected') === 'true') ?? rows[0];
        selectReport(initiallySelected);
    }

    const images = document.querySelectorAll('[data-laporan-image]');
    images.forEach((image) => {
        image.addEventListener('error', () => {
            image.hidden = true;
            const placeholder = image.nextElementSibling;
            if (placeholder?.hasAttribute('data-image-placeholder')) {
                placeholder.hidden = false;
            }
        });
    });

    const detailPhoto = document.querySelector('[data-detail-photo]');
    detailPhoto?.addEventListener('error', () => {
        detailPhoto.hidden = true;
        document.getElementById('detail-no-photo').hidden = false;
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeReportManagement, { once: true });
} else {
    initializeReportManagement();
}
