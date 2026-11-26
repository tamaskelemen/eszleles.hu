(function recommender($) {
    let form = $('#rec-filters');
    let result = $('#rec-result');

    if (!form.length) {
        return;
    }

    const STORAGE_KEY = 'recommender-filters';
    const HISTORY_LIMIT = 20;
    let remembers = form.data('remember') === 1;
    let currentId = null;
    let history = [];
    let request = null;
    let timer = null;

    function formatMagnitude(value) {
        return String(value).replace('.', ',').replace('-', '−');
    }

    // the two handles of the magnitude scale
    function updateRange() {
        let range = form.find('.rec-range');
        let scaleMin = Number(range.data('min'));
        let scaleMax = Number(range.data('max'));
        let min = Number(range.find('.rec-range__input--min').val());
        let max = Number(range.find('.rec-range__input--max').val());
        let low = Math.min(min, max);
        let high = Math.max(min, max);
        let span = scaleMax - scaleMin;
        let from = low <= scaleMin ? 'bármilyen fényes' : formatMagnitude(low) + ' mag';
        let to = high >= scaleMax ? 'bármilyen halvány' : formatMagnitude(high) + ' mag';

        range.find('.rec-range__output').text(from + ' – ' + to);
        range.find('.rec-range__fill').css({
            left: (low - scaleMin) / span * 100 + '%',
            right: (scaleMax - high) / span * 100 + '%',
        });
    }

    function save() {
        if (!remembers) {
            return;
        }

        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(form.serializeArray()));
        } catch (e) {
            // the filters simply do not outlive the page
        }
    }

    function restore() {
        let saved = null;

        try {
            saved = JSON.parse(localStorage.getItem(STORAGE_KEY));
        } catch (e) {
            return;
        }

        if (!remembers || !Array.isArray(saved)) {
            return;
        }

        let values = {};
        saved.forEach(function (field) {
            (values[field.name] = values[field.name] || []).push(field.value);
        });

        form.find('input, select').not('[type=hidden]').each(function () {
            let saved = values[this.name];

            // a filter added since then keeps its default
            if (saved === undefined) {
                return;
            }

            if (this.type === 'checkbox') {
                this.checked = saved.indexOf(this.value) !== -1;
            } else {
                $(this).val(saved[saved.length - 1]);
            }
        });
    }

    /**
     * @param {boolean} keep whether the current object is kept if it still passes the filters
     */
    function load(keep) {
        let params = form.serializeArray();

        if (keep && currentId !== null) {
            params.push({ name: 'current', value: currentId });
        }
        params.push({ name: 'exclude', value: history.join(',') });

        if (request) {
            request.abort();
        }

        result.attr('aria-busy', 'true');
        request = $.getJSON(form.data('url'), $.param(params))
            .done(function (res) {
                result.html(res.html);
                currentId = res.id;

                if (res.id !== null && history.indexOf(res.id) === -1) {
                    history = history.concat([res.id]).slice(-HISTORY_LIMIT);
                }
            })
            .fail(function (xhr, status) {
                if (status !== 'abort') {
                    result.html($('<p class="rec-empty">').text('Most nem sikerült ajánlatot kérni, próbáld újra később.'));
                }
            })
            .always(function () {
                result.attr('aria-busy', 'false');
            });
    }

    form.on('input change', '.rec-range__input', updateRange);

    form.on('change', function () {
        save();
        clearTimeout(timer);
        // a dragged handle or quick clicks send one request
        timer = setTimeout(function () {
            load(true);
        }, 300);
    });

    form.on('reset', function () {
        // the browser resets the fields after the event
        setTimeout(function () {
            try {
                localStorage.removeItem(STORAGE_KEY);
            } catch (e) {
                // nothing was saved
            }
            updateRange();
            load(true);
        });
    });

    form.on('submit', function (e) {
        e.preventDefault();
    });

    $('#rec-next').on('click', function () {
        load(false);
    });

    restore();
    updateRange();
    load(false);
})(jQuery);
