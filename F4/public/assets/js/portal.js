const menuToggle = document.querySelector('[data-menu-toggle]');
const siteNav = document.querySelector('[data-site-nav]');

if (menuToggle && siteNav) {
    menuToggle.addEventListener('click', () => {
        const isOpen = siteNav.classList.toggle('is-open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
        menuToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
    });

    siteNav.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => {
            siteNav.classList.remove('is-open');
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.setAttribute('aria-label', 'Open navigation');
        });
    });
}

document.querySelectorAll('[data-location-checkin]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const latitude = form.querySelector('[name="latitude"]');
        const longitude = form.querySelector('[name="longitude"]');
        if (latitude.value && longitude.value) return;

        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (!navigator.geolocation) {
            window.alert('Location is not available in this browser.');
            return;
        }

        button.disabled = true;
        button.textContent = 'Getting location...';
        navigator.geolocation.getCurrentPosition((position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
            form.requestSubmit();
        }, () => {
            button.disabled = false;
            button.textContent = 'Check in at site';
            window.alert('Location access is required to check in. Allow access and try again.');
        }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 });
    });
});