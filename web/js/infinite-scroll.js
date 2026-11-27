/**
 * Endless lists rendered by the InfiniteList widget: the next batch is loaded as the end of the list comes near.
 *
 * Without IntersectionObserver, or after a failed load, a "Továbbiak betöltése" button loads the next one.
 */
(function infiniteScroll() {
    'use strict';

    function init(root) {
        var items = root.querySelector('[data-infinite-items]');
        var sentinel = root.querySelector('[data-infinite-sentinel]');
        var status = root.querySelector('[data-infinite-status]');
        var moreButton = root.querySelector('[data-infinite-more]');
        var allButton = root.querySelector('[data-infinite-all]');

        var cursor = root.dataset.cursor || null;
        var loadsLeft = root.dataset.loadsLeft === '' ? Infinity : parseInt(root.dataset.loadsLeft, 10);
        // starts loading this far before the end of the list is in view
        var preload = parseInt(root.dataset.preload, 10) || 0;
        var loading = false;
        // "load all" pressed while a batch was loading
        var allPending = false;
        var observer = null;

        function hasMore() {
            return cursor !== null && loadsLeft > 0;
        }

        function finish() {
            if (observer) {
                observer.disconnect();
                observer = null;
            }
            [sentinel, moreButton, allButton].forEach(function remove(element) {
                if (element && element.parentNode) {
                    element.parentNode.removeChild(element);
                }
            });
        }

        // observing again checks at once whether the end is still in view, e.g. on a tall screen
        function watch() {
            if (!observer) {
                return;
            }
            observer.unobserve(sentinel);
            observer.observe(sentinel);
        }

        function load(all) {
            if (loading) {
                allPending = allPending || all;
                return;
            }
            if (!hasMore()) {
                return;
            }
            loading = true;
            root.classList.add('is-loading');
            items.setAttribute('aria-busy', 'true');
            status.textContent = 'Betöltés…';
            if (moreButton) {
                moreButton.hidden = true;
            }

            var url = new URL(root.dataset.url, window.location.href);
            url.searchParams.set('utana', cursor);
            if (all) {
                url.searchParams.set('osszes', '1');
            }

            fetch(url.toString(), {
                credentials: 'same-origin',
                headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'}
            })
                .then(function checkResponse(response) {
                    if (!response.ok) {
                        throw new Error(response.status);
                    }
                    return response.json();
                })
                .then(function append(response) {
                    items.insertAdjacentHTML('beforeend', response.html);
                    cursor = response.next || null;
                    if (!all) {
                        loadsLeft--;
                    }
                    status.textContent = '';

                    if (hasMore()) {
                        if (!observer && moreButton) {
                            moreButton.hidden = false;
                        }
                        watch();
                    } else {
                        finish();
                    }
                })
                .catch(function showError() {
                    status.textContent = 'Nem sikerült betölteni a továbbiakat.';
                    if (moreButton) {
                        moreButton.hidden = false;
                    }
                })
                .finally(function done() {
                    loading = false;
                    root.classList.remove('is-loading');
                    items.removeAttribute('aria-busy');
                    if (allPending) {
                        allPending = false;
                        load(true);
                    }
                });
        }

        if (!hasMore()) {
            finish();
            return;
        }

        if ('IntersectionObserver' in window) {
            observer = new IntersectionObserver(function onIntersect(entries) {
                if (entries.some(function isVisible(entry) { return entry.isIntersecting; })) {
                    load(false);
                }
            }, {rootMargin: '0px 0px ' + preload + 'px 0px'});
            observer.observe(sentinel);
        } else if (moreButton) {
            moreButton.hidden = false;
        }

        if (moreButton) {
            moreButton.addEventListener('click', function onMore() {
                load(false);
            });
        }
        if (allButton) {
            allButton.addEventListener('click', function onAll() {
                load(true);
            });
        }
    }

    document.querySelectorAll('[data-infinite]').forEach(init);
})();
