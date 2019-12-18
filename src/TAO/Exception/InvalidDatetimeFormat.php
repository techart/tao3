<?php

namespace TAO\Exception;

class InvalidDatetimeFormat extends \TAO\Exception
{
	public function __construct($date = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct("Invalid datetime format: Указанная дата '{$date}' имеет неизвестный формат", $code, $previous);
	}
}
