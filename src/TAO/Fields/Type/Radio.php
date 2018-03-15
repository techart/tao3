<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;

class Radio extends Select
{
	public function labelClass()
	{
		return $this->param(['label_class'], '');
	}
}
