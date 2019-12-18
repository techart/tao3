$(function() {
	$('.tao-field-huge-multilink').each(function() {
		var $field = $(this);
		var $input = $('.tao-field-huge-multilink__hidden', $field);
		var $search = $('.tao-field-huge-multilink__search', $field);
		var $searchInput = $('.tao-field-huge-multilink__search-input', $field);
		var $searchSelect = $('.tao-field-huge-multilink__search-select', $field);
		var $addButton = $('.tao-field-huge-multilink__add', $field);
		var $attachedItems = $('.tao-field-huge-multilink__items', $field);
		var $selectedSearchItem = false;
		var searchOpened = false;
		var maxN = -1;

		$input.val('|');
		$('.tao-field-huge-multilink__item > em', $field).each(function() {
			var val = $input.val();
			val += $(this).data('id') + '|';
			$input.val(val);
		});

		$addButton.click(function() {
			openSearch();
			return false
		});

		$('.tao-field-huge-multilink__item > em', $field).click(function() {
			var id = $(this).data('id');
			removeId(id);
			return false
		});

		$(document).mouseup(function(e) {
			if (searchOpened) {
				if (!$field.is(e.target) && $field.has(e.target).length === 0) {
					closeSearch();
				}
			}
		});

		$(document).keydown(function(e) {
			if (!searchOpened) {
				return;
			}
			if (e.keyCode == 27 || e.keyCode == 9) {
				closeSearch();
			} else {
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
		});

		$(document).keyup(function(e) {
			if (e.keyCode != 38 && e.keyCode != 40 && e.keyCode != 13) {
				var value = $searchInput.val().replace(/^\s+/,'').replace(/\s+$/,'');
				var url = $field.data('url') + '&q=' + value;
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
								}).appendTo($searchSelect);
						})
						$searchSelect.show();
					} else {
						$searchSelect.hide();
					}
				});
			}
		});

		function setMaxn(v)
		{
			maxN = v;
		}

		function getMaxn()
		{
			return maxN;
		}

		function nextSearchItem()
		{
			var maxn = getMaxn();
			if ($searchSelect && (maxn >= 0)) {
				var nn = 0;
				if ($selectedSearchItem) {
					var nn = $selectedSearchItem.data('nn');
					if (nn >= maxn) {
						nn = 0;
					} else {
						nn++;
					}
				}
				selectSearchItem($('.tao-search-item-n'+nn, $searchSelect));
			}
		}

		function prevSearchItem()
		{
			var maxn = getMaxn();
			if ($searchSelect && (maxn >= 0)) {
				var nn = maxn;
				if ($selectedSearchItem) {
					var nn = $selectedSearchItem.data('nn');
					if (nn <= 0) {
						nn = maxn;
					} else {
						nn--;
					}
				}
				selectSearchItem($('.tao-search-item-n'+nn, $searchSelect));
			}
		}

		function emptySelect() {
			$searchSelect.empty();
			setMaxn(-1);
		}

		function selectSearchItem($item) {
			if ($item) {
				$selectedSearchItem = $item;
				$('div', $searchSelect).removeClass('selected');
				$item.addClass('selected');
			}
		}

		function applySearchItem() {
			if ($selectedSearchItem) {
				var id = $selectedSearchItem.data('id');
				var title = $selectedSearchItem.html();
				addId(id, title);
			} else {
				if ($field) {
					var value = $searchInput.val().replace(/^\s+/,'').replace(/\s+$/,'');
					if (value.length > 0) {
						var add = $field.data('add');
						var addUrl = $field.data('add-url') + '&q=' + value;
						$.getJSON(addUrl, function (data) {
							if (data.error) {
								if (data.error == 'notfound') {
									return;
								}
								alert(data.error);
							} else {
								addId(data.id, data.title);
								closeSearch();
							}
						});
						return;
					}
				}
			}
			closeSearch();
		}

		function removeId(id)
		{
			var value = $input.val();
			value = value.replace('|'+id+'|', '|');
			$('.attached-id-'+id, $field).remove();
			$input.val(value);
		}

		function addId(id, title)
		{
			removeId(id);
			var value = $input.val();
			value = value + id + '|';
			$input.val(value);
			var $em = $('<em>').attr('data-id', id).click(function () {
				var id = $(this).data('id');
				removeId(id);
				return false
			});
			var $p = $('<p>').append(title);
			var $item = $('<div>').addClass('tao-field-huge-multilink__item').addClass('attached-id-'+id).append($p).append($em).insertBefore($addButton);
		}

		function openSearch()
		{
			$search.show();
			$searchSelect.hide();
			$addButton.hide();
			$searchInput.val('').focus();
			searchOpened = true;
		}
		
		function closeSearch() {
			if (searchOpened) {
				$search.hide();
				$searchSelect.hide();
				$addButton.show();
				searchOpened = false;
			}
		}

	});
});