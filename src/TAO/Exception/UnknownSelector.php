<?php

namespace TAO\Exception;

class UnknownSelector extends \TAO\Exception
{
	public function __construct($selectorName = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct("Unknown selector: " . $selectorName, $code, $previous);
	}

}
