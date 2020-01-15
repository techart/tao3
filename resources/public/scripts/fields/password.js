$(function() {
    $('span.tao-fields-password').each(function() {
        var $span = $(this);
        var $input = $('input', $span);
        var $a = $('a', $span);
        $a.click(function() {
            var src = prompt('Password', '');
            if (src) {
                var url = $a.attr('data-url')+'&src='+src;
                $.getJSON(url, function(data) {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        $input.attr('value', data.response);
                    }
                })
            }
        });
    });
});