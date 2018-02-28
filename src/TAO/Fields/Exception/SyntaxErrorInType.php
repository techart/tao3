<?php

namespace TAO\Fields\Exception;

class SyntaxErrorInType extends \TAO\Fields\Exception
{
	public function __construct($s)
	{
		return parent::__construct("Syntax error in field type: {$s}");
	}
}