<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

/**
 * Class Pairs
 *
 * @package TAO\Fields\Type
 */
class Pairs extends Field
{
	/**
	 * @param Blueprint $table
	 * @return \Illuminate\Support\Fluent
	 */
	public function createField(Blueprint $table)
	{
		return $table->json($this->name);
	}

	/**
	 * @param $value
	 */
	public function set($value)
	{
		parent::set(json_encode($value, JSON_UNESCAPED_UNICODE));
	}

	/**
	 * @param $value
	 * @return mixed
	 */
	protected function prepareValue($value)
	{
		if (!$value) {
			return $this->defaultValue();
		}
		return parent::prepareValue(json_decode($value, true));
	}

	/**
	 * @param \TAO\Foundation\Request $request
	 * @return array|mixed
	 */
	protected function getValueFromRequest($request)
	{
		$input = $request->input($this->name);
		$result = [];

		foreach ($input['value'] as $index => $value) {
			$key = $input['key'][$index];

			if (!(is_null($key) && is_null($value))) {
				$result[] = ['key' => $key ?? '', 'value' => $value ?? ''];
			}
		}

		return $result;
	}

	/**
	 * @return $this
	 */
	public function setupDefault()
	{
		if (!$this->itemHasValue()) {
			$this->setDefault($this->data['default'] ?? $this->defaultValue());
		}
		return $this;
	}

	/**
	 * Рендер без шаблона
	 * [ключ][: значение]
	 *
	 * @return string
	 */
	public function renderWithoutTemplate()
	{
		return collect($this->value())->map(function($item) {
			return implode(array_filter($item), ': ');
		})->implode('<br>');
	}

	/**
	 * @return string
	 */
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		return !empty($style) ? $style : "width: 90%;";
	}

	/**
	 * @param integer|string $index
	 * @return string
	 */
	public function nameForKey($index)
	{
		return $this->name . '[key][' . $index . ']';
	}

	/**
	 * @param integer|string $index
	 * @return string
	 */
	public function nameForValue($index)
	{
		return $this->name . '[value][' . $index . ']';
	}

	public function keyCaption()
	{
		return $this->param('key_caption', __('fields.key'));
	}

	public function valueCaption()
	{
		return $this->param('value_caption', __('fields.value'));
	}

	/**
	 * Стили для колонок
	 *
	 * @param string $column
	 * @return string
	 */
	public function styleForCol($column = "key")
	{
		return $this->param("{$column}_col", '');
	}

	/**
	 * @return array
	 */
	public function defaultValue()
	{
		return [];
	}

	/**
	 * @return array
	 */
	public function nullValue()
	{
		return [];
	}
}
