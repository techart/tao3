<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;
use TAO\Fields\MultivariantField;

class StringField extends MultivariantField
{
	protected $value_processors = [];

	/**
	 * @param Blueprint $table
	 * @param bool $column
	 * @return mixed
	 */
	public function createField(Blueprint $table, $column = false)
	{
		$column = $column? $column : $this->name;
		$len = $this->typeParamsIntArg(250);
		return $table->string($column, $len)->default('');
	}

	/**
	 * @param $value
	 * @return mixed|string
	 */
	protected function prepareValue($value)
	{
		if (isset($this->data['prepare_value'])) {
			if (!is_callable($this->data['prepare_value'])) {
				$value = \TAO\Text::process($value, $this->data['prepare_value']);
			} else {
				$value = parent::prepareValue($value);
			}
		}
		return $value;
	}
}
