
document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.getElementById('mobileMenu');
    const sidebar = document.getElementById('sidebar');

    if (!menuButton || !sidebar) {
        return;
    }

    menuButton.addEventListener('click', function () {
        sidebar.toggleAttribute('data-open');
    });

    document.addEventListener('click', function (event) {
        if (
            window.innerWidth <= 800 &&
            sidebar.hasAttribute('data-open') &&
            !sidebar.contains(event.target) &&
            !menuButton.contains(event.target)
        ) {
            sidebar.removeAttribute('data-open');
        }
    });
});
