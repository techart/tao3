"use strict";

// Полифильчик
(function () {
	if (!Element.prototype.matches) {
		Element.prototype.matches = Element.prototype.msMatchesSelector || Element.prototype.webkitMatchesSelector;
	}

	if (!Element.prototype.closest) {
		Element.prototype.closest = function(s) {
			var el = this;
			if (!document.documentElement.contains(el)) return null;
			do {
				if (el.matches(s)) return el;
				el = el.parentElement || el.parentNode;
			} while (el !== null && el.nodeType === 1);
			return null;
		};
	}
})();

// Основной код филда
(function () {
	var block = "b-pairs";

	document.addEventListener('DOMContentLoaded', function () {
		var elements = document.querySelectorAll('.' + block);

		for (var i = 0, ilen = elements.length; i < ilen; i++) {
			new Pairs(elements[i]);
		}
	});

	/**
	 * @param {HTMLElement} element
	 * @constructor
	 */
	function Pairs(element) {
		if (typeof element === "undefined" || !element) {
			return;
		}

		this.element = element;
		this.elementKeyDown = this.elementKeyDown.bind(this);
		this.addNewRow = this.addNewRow.bind(this);

		this.init();
	}

	/**
	 * Инициализация
	 */
	Pairs.prototype.init = function () {
		this.blankRow = this.element.querySelector('.' + block + "__row--blank");
		this.count = this.element.querySelectorAll('.' + block + "__row").length - 1;
		this.addButton = this.element.querySelector('.' + block + "__add-button");

		this.element.addEventListener('keydown', this.elementKeyDown);
		this.addButton.addEventListener('click', this.addNewRow);

		// this.initValidation();
		this.shiftToSelectRows();
		this.deleteButton();
		this.visualAid();
	};

	/**
	 * Клик по клавишам в пустой строчке.
	 * Отрабатывает только при нажатии клавиши Enter
	 *
	 * @param {KeyboardEvent} e
	 */
	Pairs.prototype.elementKeyDown = function (e) {
		if (e.keyCode === 13 && e.target.closest('.' + block + "__row--blank")) {
			e.preventDefault();

			var nextInput = this.nextInput();

			if (nextInput) {
				nextInput.focus();
			} else {
				this.addNewRow();

				this.nextInput().focus();
			}
		}
	};

	/**
	 * Добавление новой строчки
	 *
	 * @return {HTMLElement}
	 */
	Pairs.prototype.addNewRow = function () {
		var newBlankRow = this.createRowFromBlankRow();

		this.transformCurrentBlankRow(this.blankRow);
		this.blankRow.parentNode.appendChild(newBlankRow);

		this.blankRow = newBlankRow;
		return newBlankRow;
	};

	/**
	 * Подчистить текущую строку "пустую строку"
	 * и сделать из нее обыкновенную
	 *
	 * @param {HTMLElement} row
	 */
	Pairs.prototype.transformCurrentBlankRow = function (row) {
		var inputs = row.querySelectorAll('.' + block + '__input');

		for (var i = 0, ilen = inputs.length; i < ilen; i++) {
			inputs[i].removeAttribute('placeholder');
			inputs[i].name = inputs[i].name.replace('blank', this.count);
		}

		row.classList.remove(block + '__row--blank');
		this.count++

		return row;
	};

	/**
	 * Создать новую строчку и текущей "пустой"
	 *
	 * @return {HTMLElement}
	 */
	Pairs.prototype.createRowFromBlankRow = function () {
		var row = this.blankRow.cloneNode(true);
		var inputs = row.querySelectorAll('.' + block + '__input');

		for (var i = 0, ilen = inputs.length; i < ilen; i++) {
			inputs[i].value = '';
		}

		return row;
	};

	/**
	 * Поиск следующего инпута
	 */
	Pairs.prototype.nextInput = function () {
		if (!document.activeElement) {
			return null;
		}

		var inputs = Array.prototype.filter.call(this.element.querySelectorAll('.' + block + "__input--text"), function (item) {
			var isVisible = !!(item.offsetWidth || item.offsetHeight || item.getClientRects().length);
			return item.tabIndex >= 0 && isVisible;
		});
		var currentIndex = inputs.indexOf(document.activeElement);

		if (typeof inputs[currentIndex + 1] === "undefined") {
			return null;
		}

		return inputs[currentIndex + 1];
	};

	Pairs.prototype.shiftToSelectRows = function () {
		var self = this;

		this.element.addEventListener('click', function (e) {
			var target = e.target;

			if (target.classList.contains(block + '__input--checkbox')) {
				if (e.shiftKey && target !== self.lastChecked) {
					checkCheckboxesBetween(self.lastChecked, target);
				}

				self.lastChecked = target;
			}
		});

		function checkCheckboxesBetween(start, end) {
			var checkboxes = self.element.querySelectorAll('.' + block + '__input--checkbox');
			var startState = start.checked;

			if (Array.prototype.indexOf.call(checkboxes, start) > Array.prototype.indexOf.call(checkboxes, end)) {
				var _ref = [start, end];
				end = _ref[0];
				start = _ref[1];
			}

			var startIndex = Array.prototype.indexOf.call(checkboxes, start),
				endIndex = Array.prototype.indexOf.call(checkboxes, end);

			Array.prototype.filter.call(checkboxes, function (item, key) {
				return startIndex < key && key < endIndex;
			}).forEach(function (checkbox) {
				checkbox.checked = startState;
			});
		}
	};

	/**
	 * Обработчик для кнопки удаления
	 */
	Pairs.prototype.deleteButton = function () {
		var self = this;
		var button = this.element.querySelector('.' + block + '__delete');
		
		if (button) {
			button.addEventListener('click', function() {
				var checkboxes = self.element.querySelectorAll('.' + block + '__input--checkbox:checked');

				if (checkboxes.length > 0 && confirm(self.element.dataset.removeConfirm)) {
					Array.prototype.forEach.call(checkboxes, function (checkbox) {
						var tr = checkbox.closest('tr');
						tr.parentNode.removeChild(tr);
					});
				}
			});
		}
	};

	/**
	 * Подсветка выбраного ряда
	 */
	Pairs.prototype.visualAid = function () {
		var currentRow = null;

		this.element.addEventListener('focus', function (e) {
			currentRow = e.target.closest('.' + block + '__row');
			if (!currentRow) {
				return;
			}

			currentRow.classList.add(block + '__row--focus-within');
		}, true);

		this.element.addEventListener('blur', function (e) {
			if (currentRow) {
				currentRow.classList.remove(block + '__row--focus-within');
				currentRow = null;
			}
		}, true);
	};

	// Pairs.prototype.initValidation = function() {
	// 	var self = this;
	// 	var form = this.element.querySelector('.' + block + '__input').form;
	//
	// 	form.addEventListener('submit', function(e) {
	// 		var pairOfDuffuses = self.checkValidity();
	//
	// 		if (pairOfDuffuses !== true) {
	// 			if (confirm(self.element.dataset.duplicatesMessage)) {
	// 				return;
	// 			} else {
	// 				e.preventDefault();
	// 			}
	//
	// 			for (var i = 0; i < 2; i++) {
	// 				var duffus = pairOfDuffuses[i];
	//
	// 				duffus.closest('.' + block + '__row').classList.add(block + '__row--error');
	// 				duffus.addEventListener('input', duffusIsOk);
	// 			}
	// 		}
	//
	// 		function duffusIsOk() {
	// 			for (var i = 0; i < 2; i++) {
	// 				var duffus = pairOfDuffuses[i];
	//
	// 				duffus.closest('.' + block + '__row').classList.remove(block + '__row--error');
	// 				duffus.removeEventListener('input', duffusIsOk);
	// 			}
	// 		}
	// 	});
	// };
	//
	// Pairs.prototype.checkValidity = function() {
	// 	var keyElements = this.element.querySelectorAll('.' + block + '__input--key');
	//
	// 	for (var i = 0, ilen = keyElements.length; i < ilen; i++) {
	// 		var iKeyElement = keyElements[i];
	//
	// 		for (var j = 0; j < ilen; j++) {
	// 			var jKeyElement = keyElements[j];
	//
	// 			if (!!iKeyElement.value && iKeyElement.value === jKeyElement.value && i !== j) {
	// 				return [iKeyElement, jKeyElement];
	// 			}
	// 		}
	// 	}
	//
	// 	return true;
	// };
})();
