$(function () {
    $('.tao-field-multilink-tags').each(function () {
        var $textarea = $('textarea', $(this));
        var $links = $('.tao-field-multilink-tags__links', $(this));
        var delimiter = parseDelimiter($links.data('delimiter'))

        $('span', $links).click(function () {
            $link = $(this);
            var tag = $link.text();
            var value = $textarea.val();
            var valueArray = value.split(delimiter);

            if ($link.hasClass('selected')) {
                $link.removeClass('selected');

                filteredValuesArray = valueArray.filter(function (text) {
                    return text !== tag;
                })
                $textarea.val(filteredValuesArray.join(delimiter));

                return
            }

            $link.addClass('selected');

            if (value === '') {
                $textarea.val(tag)

                return
            }

            valueArray.push(tag)
            $textarea.val(valueArray.join(delimiter));
        });
    });

    /**
     * Parse delimiter from json
     *
     * @param {string} delimiterRaw
     */
    function parseDelimiter(delimiterRaw) {
        var delimiter = String(JSON.parse(delimiterRaw))
        if (delimiter.indexOf("\n") >= 0) {
            return "\n";
        }

        return delimiter.replace(/\s+/, ' ')
    }
});

