<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class StringField extends Field
{
	protected $value_processors = [];

	public function createField(Blueprint $table)
	{
		$len = $this->typeParamsIntArg(250);
		return $table->string($this->name, $len);
	}

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
