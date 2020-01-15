$(function() {
    $('.tao-field-multilink-tags').each(function() {
        var $textarea = $('textarea', $(this));
        var $links =  $('.tao-field-multilink-tags__links', $(this));
        $('span', $links).click(function() {
            $link = $(this);
            var tag = $link.text();
            if ($link.hasClass('selected')) {
              $link.removeClass('selected');
              var value = $textarea.val();
              value = value.replace(/\s+/, ' ');
              value = value.replace(/^\s+/, '');
              value = value.replace(/\s+$/, '');
              value = value.replace(new RegExp(',\\s*'+tag+'\\s*,', 'gi'), ',');
              value = value.replace(new RegExp('^\\s*'+tag+'\\s*,\\s*', 'gi'), '');
              value = value.replace(new RegExp(',\\s*'+tag+'\\s*$', 'gi'), '');
              value = value.replace(new RegExp('^\\s*'+tag+'\\s*$', 'gi'), '');
              $textarea.val(value);
            } else {
                $link.addClass('selected');
                var value = $textarea.val();
                value = value.replace(/^\s+/, '');
                value = value.replace(/\s+$/, '');
                if (value!='') {
                    value += ', ';
                }
                value += tag;
                $textarea.val(value);
            }
        });
    });
});