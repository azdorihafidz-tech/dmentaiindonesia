import './bootstrap';

const toggle = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#primary-navigation');

if (toggle && navigation) {
    const closeMenu = () => {
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Buka navigasi');
        navigation.classList.remove('is-open');
    };

    toggle.addEventListener('click', () => {
        const expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        toggle.setAttribute('aria-label', expanded ? 'Buka navigasi' : 'Tutup navigasi');
        navigation.classList.toggle('is-open', !expanded);
    });

    navigation.addEventListener('click', (event) => {
        if (event.target instanceof HTMLAnchorElement) closeMenu();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            closeMenu();
            toggle.focus();
        }
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 900) closeMenu();
    });
}
