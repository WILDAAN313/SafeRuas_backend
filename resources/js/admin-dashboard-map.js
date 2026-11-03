import { initializeReportMap } from './report-map.js';

function initializeAdminDashboardMap() {
    const container = document.getElementById('admin-report-map');

    if (!container) {
        return;
    }

    try {
        const reports = JSON.parse(container.dataset.reports);
        const bounds = JSON.parse(container.dataset.regionBounds);

        if (!window.L) {
            throw new Error('Leaflet belum dimuat.');
        }

        initializeReportMap(container, reports, bounds);
    } catch {
        container.textContent = 'Peta belum dapat dimuat. Periksa koneksi internet untuk memuat Leaflet dan OpenStreetMap.';
        container.classList.add('grid', 'place-items-center', 'px-6', 'text-center', 'text-sm', 'text-slate-600');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAdminDashboardMap, { once: true });
} else {
    initializeAdminDashboardMap();
}
