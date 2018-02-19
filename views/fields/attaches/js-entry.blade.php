var $entry = $('<div>')
    .attr('id', 'filelist-entry-'+key)
    .attr('data-key', key)
    .addClass('entry attaches-entry')
    .addClass('entry-'+key)
    .append($name)
    .append($edit)
    .append($del)
;
