<?php

namespace TaoTests\Utils\Text\Processor;

use TAO\Text\ConfigurableProcessorInterface;

class Additive implements ConfigurableProcessorInterface
{
	public function process($text, $options = [])
	{
		$prefix = $options['prefix'] ?? '';
		$postfix = $options['postfix'] ?? '';
		return $prefix . $text . $postfix;
	}
}