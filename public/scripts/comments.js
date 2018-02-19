$(function() {

    var token = $('.b-comments').attr('data-token');

    commentsEvents();

    $('.b-comments').each(function() {
        var $comments = $(this);
        var $branches = $('.b-comments__branches', $comments);
        var $addform = $('.b-comments__form', $comments);
        var $addbutton = $('span', $addform);
        var $addtext = $('textarea', $addform);
    
        $addbutton.click(function() {
            if ($addbutton.hasClass('disabled')) {
                return false;
            }
            var text = $addtext.val().replace(/^\s+/, '').replace(/\s+$/, '');
            if (text != '') {
                $addtext.val("");
                $addbutton.addClass('disabled');
                var url = $addform.attr('data-url');
                $.post(url, {
                    _token: token,
                    message: text
                }, function(data) {
                    $branches.append(data);
                    $addbutton.removeClass('disabled');
                    commentsEvents();
                });
            }
        });
    });
    
    
    function commentsEvents() {
        $('.b-comments__comment').each(function() {
            var $comment = $(this);
            if ($comment.attr('data-events') == 'ok') {
                return;
            }
            var id = $comment.attr('data-id');
            var $del = $('.b-comments__delete_button', $comment);
            var $edit = $('.b-comments__edit_button', $comment);
            var $message = $('.b-comments__message', $comment);
            
            $del.click(function() {
                if (confirm('Вы уверены?')) {
                    var url = $del.attr('data-url');
                    $.get(url, function(data) {
                        if (data == 'ok') {
                            $comment.remove();
                        } else {
                            alert(data);
                        }
                    });
                }
            });
            
            $edit.click(function() {
                $del.hide();
                $edit.hide();
                var url = $edit.attr('data-url-raw');
                $.get(url, function(data) {
                    $message.hide();
                    var $container = $('<div>').addClass('b-comments__edit');
                    var $textarea = $('<textarea>').val(data);
                    
                    var $ok = $('<span>').append('Изменить').click(function() {
                        var urlUpdate = $edit.attr('data-url-update');
                        var text = $textarea.val().replace(/^\s+/, '').replace(/\s+$/, '');
                        $.post(urlUpdate, {
                            _token: token,
                            message: text
                        }, function(data) {
                            if (data != '') {
                                $comment.replaceWith(data);
                                commentsEvents();
                            }
                        });
                    });
                    
                    var $cancel = $('<span>').append('Отмена').click(function() {
                        $container.remove();
                        $del.show();
                        $edit.show();
                        $message.show();
                    });
                    
                    $container.append($textarea).append($cancel).append($ok);
                    $comment.append($container);
                });
            });
            
            $comment.attr('data-events', 'ok');
        });
    }
    
});
