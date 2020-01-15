$(function() {
	$('.b-variants').each(function() {
		var $selector = $(this);
		var field = $selector.attr('data-field');
		$('button', $selector).click(function() {
			var $button = $(this);
			var code = $button.attr('data-code');
			$('button', $selector).removeClass('b-variants__button--current');
			$('.input-'+field+'-variant').hide();
			$('.input-'+field+'-variant-'+code).show();
			$button.addClass('b-variants__button--current');
			return false;
		});
	});
});