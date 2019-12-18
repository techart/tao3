$(function() {
	var $activeField = false;
	var $activeSearch = false;
	var $activeSearchInput = false;
	var $activeSearchSelect = false;
	var $activeValueContainer = false;
	var $selectedSearchItem = false;
	var maxN = -1;

	$('.tao-field-huge-select').click(function() {
		if ($activeField) {
			return;
		}

		var $field = $(this);
		var $valueContainer = $('.tao-field-huge-select__visible-value', $field);
		var $search = $('.tao-field-huge-select__search', $field);
		var $input = $('.tao-field-huge-select__search-input', $search);

		$activeField = $field;
		$activeSearch = $search;
		$activeSearchInput = $input;
		$activeValueContainer = $valueContainer
		$activeSearchSelect = $('.tao-field-huge-select__search-select', $activeSearch);
		emptySelect();

		$valueContainer.hide();
		$search.show();
		$input.val('').focus();
		$activeSearchSelect.hide();
	});

	$(document).mouseup(function(e) {
		if ($activeField) {
			if (!$activeField.is(e.target) && $activeField.has(e.target).length === 0) {
				closeSearch();
			}
		}
	});

	$(document).keydown(function(e) {
		if (!$activeField) {
			return;
		}
		if (e.keyCode == 27 || e.keyCode == 9) {
			closeSearch();
		} else {
			if ($activeField) {
				if (e.keyCode == 13) {
					applySearchItem();
					return false;
				}
				else if (e.keyCode == 38) { // up
					prevSearchItem();
					return false;
				}
				else if (e.keyCode == 40) { // down
					nextSearchItem();
					return false;
				}
			}
		}
	});

	$(document).keyup(function(e) {
		if ($activeField && e.keyCode != 38 && e.keyCode != 40 && e.keyCode != 13) {
			var value = $activeSearchInput.val().replace(/^\s+/,'').replace(/\s+$/,'');
			var url = $activeField.data('url') + '&q=' + value;
			$.getJSON(url, function(data) {
				emptySelect();
				$selectedSearchItem = false;
				var items = data.items;
				setMaxn(items.length - 1);
				if (items.length > 0) {
					$.each(items, function (key, item) {
						var $item = $('<div>')
							.addClass('tao-search-item-n' + key)
							.addClass('tao-search-item-'+item.id)
							.attr('data-nn', key)
							.attr('data-id', item.id)
							.append(item.title)
							.mouseenter(function() {
								selectSearchItem($(this));
							})
							.click(function() {
								selectSearchItem($(this));
								applySearchItem();
								return false;
							}).appendTo($activeSearchSelect);
					})
					$activeSearchSelect.show();
				} else {
					$activeSearchSelect.hide();
				}
			});
		}
	});

	function nextSearchItem()
	{
		var maxn = getMaxn();
		if ($activeSearchSelect && (maxn >= 0)) {
			var nn = 0;
			if ($selectedSearchItem) {
				var nn = $selectedSearchItem.data('nn');
				if (nn >= maxn) {
					nn = 0;
				} else {
					nn++;
				}
			}
			selectSearchItem($('.tao-search-item-n'+nn, $activeSearchSelect));
		}
	}

	function prevSearchItem()
	{
		var maxn = getMaxn();
		if ($activeSearchSelect && (maxn >= 0)) {
			var nn = maxn;
			if ($selectedSearchItem) {
				var nn = $selectedSearchItem.data('nn');
				if (nn <= 0) {
					nn = maxn;
				} else {
					nn--;
				}
			}
			selectSearchItem($('.tao-search-item-n'+nn, $activeSearchSelect));
		}
	}

	function setMaxn(v)
	{
		maxN = v;
	}

	function getMaxn()
	{
		return maxN;
	}

	function emptySelect() {
		$activeSearchSelect.empty();
		setMaxn(-1);
	}

	function selectSearchItem($item) {
		if ($activeField && $item) {
			$selectedSearchItem = $item;
			$('div', $activeSearchSelect).removeClass('selected');
			$item.addClass('selected');
		}
	}

	function applySearchItem() {
		if ($activeField && $selectedSearchItem) {
			$('.tao-field-huge-select__hidden', $activeField).val($selectedSearchItem.data('id'));
			$activeValueContainer.empty().append($selectedSearchItem.html()).show();
		} else {
			if ($activeField) {
				var $field = $activeField;
				var $cont = $activeValueContainer;
				var value = $activeSearchInput.val().replace(/^\s+/,'').replace(/\s+$/,'');
				if (value.length > 0) {
					var add = $activeField.data('add');
					var addUrl = $activeField.data('add-url') + '&q=' + value;
					$.getJSON(addUrl, function (data) {
						if (data.error) {
							if (data.error == 'notfound') {
								return;
							}
							alert(data.error);
						} else {
							$('.tao-field-huge-select__hidden', $field).val(data.id);
							$cont.empty().append(data.title).show();
							closeSearch();
						}
					});
					return;
				}
			}
		}
		closeSearch();
	}

	function closeSearch() {
		if ($activeField) {
			emptySelect();
			$activeValueContainer.show();
			$activeSearch.hide();
			$activeField = false;
			$activeSearch = false;
			$activeSearchInput = false;
			$activeSearchSelect = false;
			$activeValueContainer = false;
			$selectedSearchItem = false;
		}
	}
});