<?php

namespace TAO\Exception;

class UnknownDatatype extends \TAO\Exception
{
	public function __construct($datatypeName = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct("Unknown datatype: " . $datatypeName, $code, $previous);
	}

}
