<?php

namespace TAO\Text;

/**
 * Interface ConfigurableProcessorInterface
 * @package TAO\Text
 */
interface ConfigurableProcessorInterface
{
	/**
	 * @param string $text
	 * @param array $options
	 * @return string
	 */
	public function process($text, $options);
}