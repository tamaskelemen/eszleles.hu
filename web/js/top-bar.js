/* The tips of the sunset, sunrise and darkness times open on tap too, hover does not exist on touch screens */
(function topBar($) {
    let items = $('.sun-times__item');

    function close() {
        items.removeClass('is-open').find('.sun-times__button').attr('aria-expanded', 'false');
    }

    items.on('click', '.sun-times__button', function (event) {
        let item = $(this).closest('.sun-times__item');
        let wasOpen = item.hasClass('is-open');
        close();
        if (!wasOpen) {
            item.addClass('is-open');
            $(this).attr('aria-expanded', 'true');
        }
        event.stopPropagation();
    });

    $(document).on('click', close);
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape') {
            close();
        }
    });
})(jQuery);
