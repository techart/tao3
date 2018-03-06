<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Text extends StringField
{
	public function createField(Blueprint $table, $column = false)
	{
		$column = $column? $column : $this->name;
		$size = $this->typeParamsEnumArg(array('medium', 'long'));
		$method = $size ? "{$size}Text" : 'text';
		return $table->$method($column)->nullable();
	}

	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		if (!$style) {
			$style = 'width:90%;height:300px;';
		}
		return $style;
	}
}
