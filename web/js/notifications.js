(function notifications($) {
    let box = $('#notifications');
    let bell = box.find('.notification-bell');
    let menu = box.find('.notifications__menu');
    let list = box.find('.notifications__list');

    function showMessage(text) {
        list.empty().append($('<p class="notifications__message">').text(text));
    }

    function updateCount(count) {
        bell.attr('aria-label', count > 0 ? 'Értesítések, ' + count + ' olvasatlan' : 'Értesítések');
        bell.find('.notification-bell__count').text(count > 99 ? '99+' : count).prop('hidden', count === 0);
        $('#notifications-seen').prop('hidden', count === 0);
    }

    function load(before) {
        return $.getJSON(box.data('url'), before ? { before: before } : {}).done(function (res) {
            updateCount(res.unseenCount);
        });
    }

    box.on('show.bs.dropdown', function () {
        if (list.is(':empty')) {
            showMessage('Betöltés…');
        }

        load()
            .done(function (res) {
                list.html(res.html).scrollTop(0);
            })
            .fail(function () {
                showMessage('Az értesítések betöltése nem sikerült.');
            });
    });

    // a click inside would close the dropdown
    menu.on('click', function (e) {
        e.stopPropagation();
    });

    list.on('click', '[data-before]', function () {
        let button = $(this).prop('disabled', true);

        load(button.data('before'))
            .done(function (res) {
                button.replaceWith(res.html);
            })
            .fail(function () {
                button.prop('disabled', false);
            });
    });

    // the browser would only scroll if the notification is about the observation being viewed
    list.on('click', 'a.notification', function (e) {
        if (this.pathname === location.pathname && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            e.preventDefault();
            location.hash = this.hash;
            location.reload();
        }
    });

    $('#notifications-seen').on('click', function () {
        $.post(box.data('seen-url')).done(function (res) {
            updateCount(res.unseenCount);
            list.find('.notification--unseen').removeClass('notification--unseen').find('.notification__dot').remove();
        });
    });
})(jQuery);
