<?php

namespace TaoTests;

use TAO\Text;
use TaoTests\Utils\Text\Processor\Additive;

class TextTest extends TestCase
{
	protected function resolveApplicationConfiguration($app)
	{
		parent::resolveApplicationConfiguration($app);

		$app['config']->set('tao.text.processors.additive', Additive::class);
	}

	public function testProcess()
	{
		$this->assertEquals('test', Text::process('тест', 'translit'));
		$this->assertEquals('1test', Text::process('тест', ['translit', 'additive' => ['prefix' => '1']]));
	}
}