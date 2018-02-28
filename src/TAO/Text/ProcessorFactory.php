<?php

namespace TAO\Text;

use TAO\Text\Exception\UndefinedProcessor;

/**
 * Class ProcessorFactory
 *
 * Фабрика для обработчиков текста.
 *
 * @package TAO\Text
 */
class ProcessorFactory
{
	/**
	 * Возвращает объект обработчика текста по его мнемокоду
	 *
	 * @param string $processor_code
	 * @return ProcessorInterface|ConfigurableProcessorInterface
	 * @throws UndefinedProcessor
	 */
	public static function processor($processor_code)
	{
		$processors = config('tao.text.processors', []);
		if (isset($processors[$processor_code])) {
			$processor = app()->make($processors[$processor_code]);
		}
		if (!$processor) {
			throw new UndefinedProcessor($processor_code);
		}
		return $processor;
	}
}