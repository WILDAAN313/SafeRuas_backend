
document.addEventListener('DOMContentLoaded', () => {
    const searchForms = document.querySelectorAll('[data-laporan-search]');

    searchForms.forEach((form) => {
        form.addEventListener('submit', () => {
            const searchInput = form.querySelector('[name="cari"]');

            if (searchInput) {
                searchInput.value = searchInput.value.trim();
            }
        });
    });

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
});
