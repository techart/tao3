$(function() {
	$('textarea').each(function() {
		var $textarea = $(this);
		var url = $textarea.data('save-url');
		if (url !== undefined) {
			var $button = $('<a>').attr('href', url).text('Сохранить').css('visibility', 'hidden').click(function() {
				var value = $textarea.val();
				var token = $textarea.data('token');
				$.ajax({
					url: url,
					data: {
						textarea: value,
						_token: token
					},
					type: 'POST',
					dataType: 'json'
				}).done(function(response) {
					if (response.result == 'ok') {
						$button.css('visibility', 'hidden');
					} else {
						alert(response.message);
					}
				});
				return false;
			});
			$textarea.after($button).bind('keyup change', function() {
				$button.css('visibility', 'visible');
			});
		}
	});
});