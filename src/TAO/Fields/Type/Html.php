<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;

class Html extends Text
{
	/**
	 * @param Blueprint $table
	 * @param bool $column
	 * @return \Illuminate\Support\Fluent|mixed
	 */
	public function createField(Blueprint $table, $column = false)
	{
		return $table->longText($this->name);
	}

	/**
	 * Обработка конфига редактора,
	 * добавление css селектора для контейнера,
	 * добавление стилей для редактора из указанной точки входа tao-webpack (пар-р tao_webpack_css)
	 *
	 * Каждое поле имеет свой конфиг, который можно описать/переопределить в описании поля в пункте editor_config
	 *
	 * @return array
	 */
	public function editorConfig()
	{
		$user_config = [];

		/**
		 * Забираем "персональные" настройки каждого поля
		 */
		if (isset($this->data['editor_config']) && !empty($this->data['editor_config'])) {
			$user_config = $this->data['editor_config'];
		}

		/**
		 * Забираем файл стилей контента из тао вебпака (если есть)
		 */
		if (!empty($user_config) && isset($user_config['tao_webpack_css']) && !empty($user_config['tao_webpack_css'])) {
			$user_config['content_css'] = \TAO::frontend()->cssUrl($user_config['tao_webpack_css']);
		}

		return array_merge_recursive(config('html-editor'), $user_config, ['selector' => '#' . $this->editorID()]);
	}

	/**
	 * Формирование ID для элемента-контейнера редактора
	 *
	 * @return string
	 */
	public function editorID()
	{
		return 'editor_' . $this->name . '_' . ($this->item ? $this->item->getKey() : rand(1, 100));
	}

	/**
	 * При сохранении значения "очищаем" html содержимое от всякого мусора
	 * (лишних тегов, атрибутов и проч)
	 * используется библиотека Purifier
	 * @see https://github.com/mewebstudio/Purifier
	 *
	 * @param $value
	 */
	public function set($value)
	{
		parent::set(empty($value) ? $value : \Purifier::clean($value));
	}
}