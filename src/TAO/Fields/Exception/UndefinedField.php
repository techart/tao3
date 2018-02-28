<?php

namespace TAO\Fields\Exception;

class UndefinedField extends \TAO\Fields\Exception
{
	public function __construct($f, $m)
	{
		return parent::__construct("Undefined field {$f} in model {$m}");
	}
}