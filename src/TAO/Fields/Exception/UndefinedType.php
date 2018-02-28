<?php

namespace TAO\Fields\Exception;

class UndefinedType extends \TAO\Fields\Exception
{
	public function __construct($s)
	{
		return parent::__construct("Undefined type: {$s}");
	}
}