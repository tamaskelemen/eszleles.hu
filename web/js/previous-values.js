(function previousValues($) {
    $(document).on('click', '.previous-values .dropdown-item', function () {
        let group = $(this).closest('.previous-values');

        // the change event makes the form validate the new value
        group.find('input').val($(this).attr('data-value')).trigger('change');
        group.find('.dropdown-toggle').trigger('focus');
    });
})(jQuery);
