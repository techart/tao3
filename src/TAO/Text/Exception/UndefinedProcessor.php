<?php

namespace TAO\Text\Exception;

use TAO\Exception;
use Throwable;

/**
 * Class UndefinedProcessor
 *
 * @package TAO\Text\Exception
 */
class UndefinedProcessor extends Exception
{
	/**
	 * UndefinedProcessor constructor.
	 * @param string $processor_code
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct($processor_code = "", $code = 0, Throwable $previous = null)
	{
		parent::__construct('Undefined processor for code "' . $processor_code . '"', $code, $previous);
	}

}