<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Integer extends Field
{
	public function createField(Blueprint $table)
	{
		$size = $this->typeParamsEnumArg(array('tiny', 'small', 'medium', 'big'));
		$unsigned = (bool)$this->typeParamsEnumArg(array('unsigned'));
		$method = $size ? "{$size}Integer" : 'integer';
		return $table->$method($this->name, false, $unsigned);
	}

	public function defaultValue()
	{
		return 0;
	}

	public function nullValue()
	{
		return 0;
	}

	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		return empty($style) ? 'width:200px' : $style;
	}
}
