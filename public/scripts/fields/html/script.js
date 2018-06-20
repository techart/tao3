/**
 * Поле "Html"
 *
 * Каждое поле этого типа может работать 2 режимах
 *  - просто textarea
 *  - с визуальным редактором (tinyMCE по умолчанию)
 *
 * Для переключения режимов отображения предназначена кнопка Текст/HTML
 *
 * После изменения режима отображения состояние поля сохраняется в localStorage браузера,
 * а именно
 *  - после выключения визуального редактора в localStorage сохраняется переменная
 *    с именем id редактора (editor_{поле}). И в слеующий раз при загрузке страницы редактор для
 *    этого поля инициализирован не будет
 *
 *  - после включения редактора значение из localStorage удалится
 */
document.addEventListener("DOMContentLoaded", function () {
	/**
	 * Глобальное хранилище конфигов редакторов.
	 * Каждое поле может обладать своими настройками
	 *
	 * @type {object}
	 */
	window['editorsSettings'] = window['editorsSettings'] || {};

	var adminHtmlEditors = {

		/**
		 * Инфициализация визуального редактора
		 * Если в localStorage есть переременная с кодом этого поля (id)
		 * то редактор не инициализируется
		 *
		 * Параметр forced предназначен для принудительной инициализации
		 *
		 * При инициализации текст на кнопку переключения режимов ставится автоматически
		 *
		 * @param id
		 * @param forced
		 * @return {null}
		 */
		enableEditor: function (id, forced) {
			forced = !!forced || false;

			if (!forced && window.localStorage.getItem(id)) {
				this.setButtonHtmlMode(id);
				return null;
			}

			/**
			 * при принудительной инициализации
			 * удаляем признак в localStorage,
			 * запрещающий инициализацию
			 */
			if (forced) {
				window.localStorage.removeItem(id);
			}

			this.setButtonTextMode(id);
			return window['editorsSettings'][id] === void 0 ? null : this._redactor().init(window['editorsSettings'][id]);
		},

		/**
		 * Установка текста "HTML"
		 * кнопке переключения режимов редактора
		 *
		 * @param id
		 */
		setButtonHtmlMode: function (id) {
			var button = this._getButton(id);
			button.innerHTML = button.dataset.html;
		},

		/**
		 * Установка текста "Текст"
		 * кнопке переключения режимов редактора
		 *
		 * @param id
		 */
		setButtonTextMode: function (id) {
			var button = this._getButton(id);
			button.innerHTML = button.dataset.text;
		},

		/**
		 * Включение/выключение визуального редактора
		 *
		 * При включении, если редактор не был инициализирован, он инициализируется
		 * Если редактор уже есть, он просто показывется
		 *
		 * при выключении редактор не отключается целиком, а просто скрывается,
		 * в localStorage кладется переменная с id, которая предотвращает
		 * инициализацию в следующий раз (напрмер при перезагрузке)
		 *
		 * @param id
		 * @return {null}
		 */
		toggleEditor: function (id) {
			var editor = this._redactor().EditorManager.get(id);

			if (!editor) {
				return this.enableEditor(id, true);
			}

			if (editor.isHidden()) {
				editor.show();
				this.setButtonTextMode(id);
				window.localStorage.removeItem(id);
				return null;
			}

			editor.hide();
			this.setButtonHtmlMode(id);
			window.localStorage.setItem(id, true);
			return null;
		},

		/**
		 * Получение кнопки переключения режимов редактора для конкретного поля
		 *
		 * @param id
		 * @return {*}
		 * @private
		 */
		_getButton: function (id) {
			return Array.prototype.filter.call(document.getElementsByClassName('editor-mode-switcher'), function (button) {
				return button.dataset.for == id;
			})[0];
		},

		/**
		 * Получение объекта редактора
		 *
		 * @return {*}
		 * @private
		 */
		_redactor: function () {
			return tinymce;
		},
	};


	/**
	 * Перебор всех контейнеров и включение редакторов
	 */
	[].forEach.call(document.getElementsByClassName('tinyMCE-container'), function (element) {
		console.log('sadasdasds');
		adminHtmlEditors.enableEditor(element.id);
	});

	/**
	 * Установка обработчиков на кнопки переключения режимов редактора
	 * У кадого поля она своя
	 */
	[].forEach.call(document.getElementsByClassName('editor-mode-switcher'), function (switcherButton) {
		switcherButton.addEventListener('click', function (event) {
			adminHtmlEditors.toggleEditor(switcherButton.dataset.for);
			event.preventDefault();
		});
	});
});
