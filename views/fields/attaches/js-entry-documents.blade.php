var $date = $('<span>')
    .addClass('date')
    .append(data.info.date)
;

var $title = $('<div>')
    .addClass('title')
    .append(data.info.title)
;

var $entry = $('<div>')
    .addClass('entry documents-entry')
    .addClass('entry-'+key)
    .attr('id', 'filelist-entry-'+key)
    .attr('data-key', key)
    .append($date)
    .append($name)
    .append($title)
    .append($edit)
    .append($del)
;
