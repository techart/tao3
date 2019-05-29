<?php

namespace TaoTests\Text;

use TAO\Text;
use TAO\Text\Exception\UndefinedProcessor;
use TaoTests\TestCase;

class TextProcessTest extends TestCase
{
	public function testUndefinedProcessorExceptionHandling()
	{
		$text = 'text';
		$processedText = Text::process('text', 'undefined_processor');
		$this->assertEquals($text, $processedText);
	}
}
