let comm = (function comm($) {
    function ajaxFormSubmit(e) {
        var form = $(this);

        if (form.data('redirect')) {
            return true;
        }

        e.preventDefault();

        var replace = form.data('replace');
        var update = form.data('update');
        var options = {
            type: form.attr('method'),
            data: getFormData(form)
        };

        if (replace && replace.length) {
            options.dataType = 'html';
        }

        ajax(form.attr('action'), options)
            .then(function ajaxThen(res) {
                var replaceElem = $(replace);
                if (replaceElem.length) {
                    replaceElem.replaceWith(res);
                    return;
                }

                if (res.html) {
                    return res.html;
                }

                addAlertMessage(res.message);

                reloadPjax(update);
            })
            .catch(function ajaxCatch(error) {
                if (error) {
                    alert(error);
                }
            })
            .then(function ajaxFinally(html) {
                if (html) {
                    $('#dialog-modal-content').html(html);
                } else {
                    $('#dialog-modal').modal('hide');
                }
            });
    }

    function ajaxLinkClick(e) {
        e.preventDefault();
        alertContainer.empty();

        var link = $(this);
        var options = {
            data: {
                redirect: link.data('redirect'),
                replace: link.data('replace')
            },
            type: link.data('type')
        };

        ajax(link.attr('href'), options)
            .then(function ajaxThen(res) {
                addAlertMessage(res.message);

                if (res.html && res.html.length) {
                    var replaceElem = $(link.data('replace'));
                    if (link.data('replace') && replaceElem.length) {
                        replaceElem.replaceWith(res.html);
                    }
                }
            })
            .catch(function ajaxCatch(error) {
                if (error) {
                    alert(error);
                }
            })
            .then(function ajaxFinally() {
            });
    }

    function modalViewClick(e) {
        e.preventDefault();
        alertContainer.empty();

        var link = $(this);
        var modal = $('#dialog-modal');
        var modalContent = $('#dialog-modal-content');
        var options = {
            data: {
                redirect: link.data('redirect'),
                update: link.data('update')
            }
        };

        site.initModal();

        ajax(link.attr('href'), options, modal)
            .then(function ajaxThen(res) {
                return res;
            })
            .catch(function ajaxCatch(error) {
                modal.modal('hide');

                if (error) {
                    alert(error);
                }
            })
            .then(function ajaxFinally(res) {
                modalContent.html(res.html);

                initModalFocus(modal);
                // TODO: Hide loader.
            });
    }

    function reloadPjax(selector, options) {
        if ($(selector + '-pjax').length) {
            $.pjax.reload(selector + '-pjax', options);
        }
    }

    // Private properties.

    var alertContainer = $('#alert-container');

    function addAlertMessage(msg) {
        if (msg && msg.length) {
            alertContainer.append(msg);
        }
    }

    function ajax(url, options, modal) {
        var defaults = {
            type: 'get',
            url: url,
            data: null,
            dataType: 'json'
        };
        var settings = $.extend({}, defaults, options);

        return new Promise(function ajaxPromise(resolve, reject) {
            var ajax = $.ajax(settings);

            ajax.done(function ajaxPromiseDone(res) {
                resolve(res);
            });

            ajax.fail(function ajaxPromiseFail(XHR, textStatus) {
                var error = parseError(XHR, textStatus);
                reject(error);
            });

            if (modal !== undefined) {
                modal.on('hide.bs.modal', function ajaxPromiseAbort() {
                    ajax.abort();
                });
            }
        });
    }

    function getFormData(form) {
        var data = {};
        var inputs = form.serializeArray();
        var length = inputs.length;

        if (length === 0) {
            return data;
        }

        for (var i = 0; i < length; i++) {
            var input = inputs[i];
            var name = input.name;
            var value = input.value;
            var suffix = '[]';
            if (name.indexOf(suffix, name.length - suffix.length) !== -1) {
                if (data[name]) {
                    data[name].push(value);
                } else {
                    data[name] = [value];
                }
            } else {
                data[name] = value;
            }
        }

        return data;
    }

    function initAutoFocusIn(elem) {
        elem.find('[autofocus]').focus();
    }

    function initModalFocus(modal) {
        if (modal.hasClass('in')) {
            initAutoFocusIn(modal);
        } else {
            modal.on('shown.bs.modal', function initAutoFocusInModal() {
                initAutoFocusIn(modal);
            });
        }
    }

    function parseError(XHR, textStatus) {
        var error;
        switch (textStatus) {
            case 'timeout':
                error = 'The request has timed out.';
                break;
            case 'parsererror':
                error = 'Parser error.';
                break;
            case 'error':
                if (XHR.status && !/^\s*$/.test(XHR.status)) {
                    error = 'Error ' + XHR.status;
                } else {
                    error = 'Error';
                }

                if (XHR.responseText && !/^\s*$/.test(XHR.responseText)) {
                    error = error + ': ' + XHR.responseText;
                }

                break;
        }

        return error;
    }

    return {
        ajaxFormSubmit: ajaxFormSubmit,
        ajaxLinkClick: ajaxLinkClick,
        getFormData: getFormData,
        modalViewClick: modalViewClick,
        reloadPjax: reloadPjax
    };
})(window.jQuery);

let site = (function site($) {
    function init() {
        $(function () {
            var body = $('body');

            body.popover({ selector: '[data-toggle=popover]' });

            body.on('click', function (e) {
                //did not click a popover toggle, or icon in popover toggle, or popover
                if ($(e.target).data('toggle') !== 'popover'
                    && $(e.target).parents('[data-toggle="popover"]').length === 0
                    && $(e.target).parents('.popover.in').length === 0) {
                    $('[data-toggle="popover"]').popover('hide');
                }
            });

            body.on('click', 'a[data-ajax=1]', comm.ajaxLinkClick);
            body.on('click', 'a[data-view=modal]', comm.modalViewClick);
            body.on('submit', 'form[data-ajax=1]', comm.ajaxFormSubmit);
            body.on('blur', 'select[data-select=grid-col-select]', function updateGridCols() {
                var $this = $(this);
                comm.reloadPjax($this.data('update'), {
                    data: comm.getFormData($($this.data('filter-row-id')).find('input, select')),
                    replace: false
                });
            });
        });
    }

    function initModal(options) {
        var defaults = {
            size: null
        };
        var settings = $.extend({}, defaults, options);

        var modal = $('#dialog-modal');
        var modalContent = $('#dialog-modal-content');
        var modalDialog = modal.find('.modal-dialog');

        modalDialog.prop('class', 'modal-dialog');
        if (settings.size !== null) {
            modalDialog.addClass(settings.size);
        }
        modalContent.html('<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="dialog-modal-label"></h4></div><div class="modal-body"></div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>');

        modal.modal('show');
    }

    return {
        init: init,
        initModal: initModal
    };
})(window.jQuery);

site.init();