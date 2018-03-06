<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class RememberToken extends StringField
{
	public function createField(Blueprint $table, $column = false)
	{
		$column = $column? $column : $this->name;
		return $table->rememberToken($column);
	}
}
