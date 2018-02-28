<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class RememberToken extends StringField
{
	public function createField(Blueprint $table)
	{
		return $table->rememberToken($this->name);
	}
}
