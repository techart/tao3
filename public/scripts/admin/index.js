$(function() {

    if ($('#admin-form').length) {
        window.$adminForm = $('#admin-form');
    }

    $('.tao-filter-button').click(function() {
        var $that = $(this);
        if ($that.hasClass('filter-active')) {
            $that.removeClass('filter-active').empty().append('<i class="icon-search icon-white"></i> Поиск</a>');
            $('#content').removeClass('span9').addClass('span12');
            $('#content-sidebar').removeClass('span3').addClass('unvisible');
        } else {
            $that.addClass('filter-active').empty().append('<i class="icon-search icon-white"></i> Скрыть поиск</a>');
            $('#content').removeClass('span12').addClass('span9');
            $('#content-sidebar').removeClass('unvisible').addClass('span3');
        }
        return false;
    });


});
