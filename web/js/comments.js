(function comments($) {
    function toggle(thread, isOpen) {
        thread.find('.reply-form').prop('hidden', !isOpen);
        thread.find('.reply-button').attr('aria-expanded', isOpen);
    }

    $(document).on('click', '.reply-button', function () {
        let thread = $(this).closest('.comment-thread');

        toggle(thread, true);
        thread.find('textarea').trigger('focus');
    });

    $(document).on('click', '[data-reply-cancel]', function () {
        let thread = $(this).closest('.comment-thread');

        toggle(thread, false);
        thread.find('.reply-button').last().trigger('focus');
    });
})(jQuery);
