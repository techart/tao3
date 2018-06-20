<?php

namespace TAO\Exception;

class HTTP extends \TAO\Exception
{
	public function __construct($curl, $code = 0, Throwable $previous = null)
	{
		parent::__construct("HTTP request error: [{$curl->http_status_code}] {$curl->http_error_message}", $code, $previous);
	}

}
