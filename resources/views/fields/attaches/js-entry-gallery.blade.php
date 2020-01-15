var url = '{!! $field->adminPreviewUrl() !!}'+'&path='+files[key].path;

var $description = $('<div>')
    .addClass('description')
    .append(data.info.description)
;

var $image = $('<div>')
    .addClass('image')
    .css('background-image', 'url('+url+')')
;

var $entry = $('<div>')
    .addClass('entry gallery-entry')
    .addClass('entry-'+key)
    .attr('id', 'filelist-entry-'+key)
    .attr('data-key', key)
    .append($name)
    .append($edit.addClass('button'))
    .append($del.addClass('button'))
    .append($image)
    .append($description)
;
