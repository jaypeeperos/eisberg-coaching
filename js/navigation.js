(function () {
    var toggle = document.querySelector('.menu-toggle');
    var menu = document.getElementById('primary-menu');

    if (!toggle || !menu) {
        return;
    }

    function closeMenu() {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.setAttribute('aria-label', 'Menü öffnen');
    }

    function openMenu() {
        menu.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        toggle.setAttribute('aria-label', 'Menü schließen');
    }

    toggle.addEventListener('click', function () {
        if (toggle.getAttribute('aria-expanded') === 'true') {
            closeMenu();
        } else {
            openMenu();
        }
    });

    menu.addEventListener('click', function (event) {
        if (event.target.closest('a') && !event.target.closest('.region-menu')) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            closeMenu();
            toggle.focus();
        }
    });
})();