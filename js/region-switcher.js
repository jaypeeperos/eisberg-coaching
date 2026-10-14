/**
 * Header region switcher: click to toggle, Escape or outside click to close.
 * The panel also opens on hover/focus via CSS, so the links stay reachable
 * if this script never runs.
 */
(function () {
    var switchers = document.querySelectorAll('[data-region-switcher]');

    Array.prototype.forEach.call(switchers, function (switcher) {
        var toggle = switcher.querySelector('.region-toggle');
        if (!toggle) {
            return;
        }

        // Hands the panel over to the click handler below, so aria-expanded
        // always matches what is on screen.
        switcher.setAttribute('data-region-js', '');

        function close() {
            switcher.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        }

        function open() {
            switcher.classList.add('is-open');
            toggle.setAttribute('aria-expanded', 'true');
        }

        toggle.addEventListener('click', function (event) {
            event.preventDefault();
            if (switcher.classList.contains('is-open')) {
                close();
            } else {
                open();
            }
        });

        switcher.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && switcher.classList.contains('is-open')) {
                close();
                toggle.focus();
            }
        });

        document.addEventListener('click', function (event) {
            if (!switcher.contains(event.target)) {
                close();
            }
        });

        document.addEventListener('focusin', function (event) {
            if (!switcher.contains(event.target)) {
                close();
            }
        });
    });
})();
