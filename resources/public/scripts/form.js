function taoAjaxForm(id, formOptions) {
	var that = this;

	that.form = $('#' + id);
	that.options = formOptions;

    function onOk(data) {
        if (typeof data.redirect == 'string' && data.redirect != 'std') {
            location.href = data.return_url;
        } else {
            var ok = $('<div>').addClass('ok-message').html(data.ok_message);
            that.form.empty().append(ok);
        }
    }

    function onError(data) {
        var e = $('ul.ajax-errors', that.form).empty();
        $.each(data.errors, function (field, message) {
            if (message.length > 1) {
                var li = $('<li>').attr('data-field', field).addClass('error-field-' + field).html(message);
                e.append(li);
            }
            $('.tao-form-field-container-' + field, that.form).addClass('tao-error-field');
        });
        e.show();
    }

    function beforeSubmit(formData, jqForm, options) {
        $('.tao-form-field-container').removeClass('tao-error-field');
        var funcName = formOptions.before_submit;
        if (typeof funcName == 'string') {
            var func = window[funcName];
            if (typeof func == 'function') {
                var r = func(formData, jqForm, options);
                if (r === false) {
                    return false;
                }
            }
        }
        if (that.options.show_loader) {
            var shadow = $('<div>').addClass('shadow');
            that.form.append(shadow);
        }
    }

    function onAjaxReturn(data) {
        $('div.shadow', that.form).remove();
        if (data.result == 'ok') {
            var std = true;
            var funcName = data.on_ok;
            if (typeof funcName == 'string') {
                var func = window[funcName];
                if (typeof func == 'function') {
                    var r = func(data, that.form, that.options);
                    if (r === false) {
                        std = false;
                    }
                }
            }
            if (std) {
                onOk(data);
            }
        } else {
            var std = true;
            var funcName = data.on_error;
            if (typeof funcName == 'string') {
                var func = window[funcName];
                if (typeof func == 'function') {
                    var r = func(data, that.form, that.options);
                    if (r === false) {
                        std = false;
                    }
                }
            }
            if (std && that.options.show_errors) {
                onError(data);
            }
        }
        return false;
    }

    that.form.ajaxForm({
        dataType: 'json',
        beforeSubmit: beforeSubmit,
        success: onAjaxReturn
    });
}
