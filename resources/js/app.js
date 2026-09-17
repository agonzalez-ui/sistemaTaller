const mobileNavigation = document.querySelector('[data-mobile-navigation]');

if (mobileNavigation) {
    const toggle = mobileNavigation.querySelector('summary');

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileNavigation.open) {
            mobileNavigation.open = false;
            toggle.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (mobileNavigation.open && !mobileNavigation.contains(event.target)) {
            mobileNavigation.open = false;
        }
    });

    mobileNavigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            mobileNavigation.open = false;
        });
    });

    const desktopViewport = window.matchMedia('(min-width: 1024px)');
    desktopViewport.addEventListener('change', () => {
        mobileNavigation.open = false;
    });
}