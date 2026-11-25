(function skyObjects($) {
    const DELAY = 400;

    function debounce(callback) {
        let timer;

        return function () {
            clearTimeout(timer);
            timer = setTimeout(callback, DELAY);
        };
    }

    $('.sky-objects').each(function () {
        let root = $(this);
        let list = root.find('.sky-objects__list');
        let search = root.find('.sky-objects__search input');
        let menu = root.find('.sky-objects__search .dropdown-menu');
        let titleInput = $(root.data('title-input') || []);

        let items = root.data('items');
        let dismissed = root.data('dismissed');
        // the title the items belong to, the server uses it to find out if we could not follow the title
        let title = String(root.attr('data-title'));
        let lastMatch = 0;
        let lastSearch = 0;

        function hasItem(id) {
            return items.some(item => item.id === id);
        }

        function render() {
            list.empty();

            items.forEach(function (item) {
                $('<li class="sky-objects__item">')
                    .append($('<span>').text(item.label))
                    .append($('<input type="hidden">').attr('name', root.data('items-name')).val(item.value))
                    .append(
                        $('<button type="button" class="sky-objects__remove">&times;</button>')
                            .attr('aria-label', 'Eltávolítás: ' + item.label)
                            .on('click', () => remove(item))
                    )
                    .appendTo(list);
            });

            root.find('.sky-objects__empty').prop('hidden', items.length > 0 || title.trim() === '');
            root.find('.sky-objects__title').val(title);
        }

        function remove(item) {
            items = items.filter(other => other !== item);
            dismissed.push(item.id);
            render();
            search.trigger('focus');
        }

        function add(item) {
            if (!hasItem(item.id)) {
                items.push(Object.assign({}, item, {isMatched: false}));
            }

            dismissed = dismissed.filter(id => id !== item.id);
            closeMenu();
            search.val('').trigger('focus');
            render();
        }

        function match() {
            let newTitle = titleInput.val();

            if (newTitle === title) {
                return;
            }

            let request = ++lastMatch;

            $.getJSON(root.data('match-url'), {title: newTitle}, function (found) {
                // a newer request is on its way
                if (request !== lastMatch) {
                    return;
                }

                title = newTitle;
                items = items.filter(item => !item.isMatched);
                found.forEach(function (item) {
                    if (!hasItem(item.id) && !dismissed.includes(item.id)) {
                        items.push(Object.assign({}, item, {isMatched: true}));
                    }
                });
                render();
            });
        }

        function closeMenu() {
            lastSearch++;
            menu.removeClass('show').empty();
        }

        function find() {
            let q = search.val().trim();

            closeMenu();

            if (q.length < 2) {
                return;
            }

            let request = ++lastSearch;

            $.getJSON(root.data('search-url'), {q: q}, function (found) {
                if (request !== lastSearch) {
                    return;
                }

                found.forEach(function (item) {
                    $('<button type="button" class="dropdown-item">').text(item.label).on('click', () => add(item)).appendTo(menu);
                });

                if (found.length === 0) {
                    menu.append('<span class="dropdown-item-text">Nincs találat.</span>');
                }

                menu.addClass('show');
            });
        }

        search.on('input', debounce(find));
        search.on('keydown', function (event) {
            if (event.key === 'Enter') {
                // do not submit the form
                event.preventDefault();
                menu.find('button').first().trigger('click');
            } else if (event.key === 'Escape') {
                closeMenu();
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                menu.find('button').first().trigger('focus');
            }
        });
        menu.on('keydown', 'button', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                $(this).next().trigger('focus');
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                ($(this).prev().length ? $(this).prev() : search).trigger('focus');
            } else if (event.key === 'Escape') {
                closeMenu();
                search.trigger('focus');
            }
        });
        $(document).on('click', function (event) {
            if (!$(event.target).closest(root.find('.sky-objects__search')).length) {
                closeMenu();
            }
        });

        titleInput.on('input', debounce(match)).on('change', match);

        root.append($('<input type="hidden" class="sky-objects__title">').attr('name', root.data('title-name')));
        root.prop('hidden', false);
        render();
        // the browser may have filled the title
        if (titleInput.length) {
            match();
        }
    });
})(jQuery);
