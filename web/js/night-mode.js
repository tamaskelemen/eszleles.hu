(function nightMode() {
    let root = document.documentElement;
    let button = document.getElementById('night-toggle');

    if (!button) {
        return;
    }

    function update() {
        button.setAttribute('aria-pressed', root.hasAttribute('data-night') ? 'true' : 'false');
    }

    button.addEventListener('click', function () {
        root.toggleAttribute('data-night');

        try {
            localStorage.setItem('night-mode', root.hasAttribute('data-night') ? '1' : '0');
        } catch (e) {
            // the setting simply does not outlive the page
        }

        update();
    });

    update();
})();
