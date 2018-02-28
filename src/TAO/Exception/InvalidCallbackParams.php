<?php

namespace TAO\Exception;

class InvalidCallbackParams extends \TAO\Exception
{
	public function __construct($message = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct("Invalid callback params: {$message}", $code, $previous);
	}

}