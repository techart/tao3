$(function() {
	
	if ($('#admin-form').length) {
		window.$adminForm = $('#admin-form');
	}
	
	$('.tao-filter-button').click(function() {
		var $that = $(this);
		if ($that.hasClass('filter-active')) {
			$that.removeClass('filter-active').empty().append('<i class="icon-search icon-white"></i> Поиск</a>');
			$('#content').removeClass('col-md-9').addClass('col-md-12');
			$('#content-sidebar').removeClass('col-md-3').addClass('unvisible');
		} else {
			$that.addClass('filter-active').empty().append('<i class="icon-search icon-white"></i> Скрыть поиск</a>');
			$('#content').removeClass('col-md-12').addClass('col-md-9');
			$('#content-sidebar').removeClass('unvisible').addClass('col-md-3');
		}
		return false;
	});
	
	$('.use-tab-key').keydown(function(e) {
		if(e.keyCode === 9) {
			var $this = $(this);
			var start = this.selectionStart;
			var end = this.selectionEnd;
			
			var value = $this.val();
			$this.val(value.substring(0, start) + "\t" + value.substring(end));
			
			this.selectionStart = this.selectionEnd = start + 1;
			e.preventDefault();
		}
	});
});
