(function planAttach($) {
    const DELAY = 300;

    let modal = $('#plan-attach');

    if (!modal.length) {
        return;
    }

    let form = modal.find('form');
    let search = modal.find('#plan-attach-search');
    let results = modal.find('.plan-attach__results');
    let lastSearch = 0;
    let timer;

    function find() {
        let request = ++lastSearch;

        $.getJSON(modal.data('url'), {q: search.val().trim()}, function (found) {
            // a newer request is on its way
            if (request !== lastSearch) {
                return;
            }

            results.empty();

            found.forEach(function (observation) {
                $('<button type="button" class="list-group-item list-group-item-action">')
                    .append($('<span>').text(observation.label))
                    .append($('<small class="text-muted d-block">').text(observation.details))
                    .on('click', function () {
                        form.find('[name="observation_id"]').val(observation.id);
                        form.trigger('submit');
                    })
                    .appendTo(results);
            });

            if (found.length === 0) {
                results.append($('<p class="text-muted">').text(
                    search.val().trim() ? 'Nincs ilyen című észlelésed.' : 'Még nincs feltöltött észlelésed.'
                ));
            }
        });
    }

    modal.on('show.bs.modal', function (event) {
        let button = $(event.relatedTarget);

        form.find('[name="item_id"]').val(button.data('item-id'));
        modal.find('.plan-attach__object').text(button.data('label'));
        search.val('');
        results.empty();
        find();
    });

    modal.on('shown.bs.modal', function () {
        search.trigger('focus');
    });

    search.on('input', function () {
        clearTimeout(timer);
        timer = setTimeout(find, DELAY);
    });

    search.on('keydown', function (event) {
        if (event.key === 'Enter') {
            // only a chosen observation submits the form
            event.preventDefault();
            results.find('button').first().trigger('click');
        }
    });
})(jQuery);
