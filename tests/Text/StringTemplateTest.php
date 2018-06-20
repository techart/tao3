<?php

namespace TaoTests\Text;

use TAO\Text\StringTemplate;
use TaoTests\TestCase;

class StringTemplateTest extends TestCase
{
	public function testProcess()
	{
		$strWithoutVars = '/test/';
		$this->assertEquals($strWithoutVars, StringTemplate::process($strWithoutVars, ['id' => '3']));

		$strWithOneVar = '/test/{id}/';
		$this->assertEquals('/test/777/', StringTemplate::process($strWithOneVar, ['id' => '777']));

		$strWithManyVars = '/{category}/{id}/';
		$this->assertEquals('/sale/777/', StringTemplate::process($strWithManyVars, [
			'category' => 'sale', 'id' => '777'
		]));

		$strWithOneVar = '/test/{id}/{unknown}/';
		$this->assertEquals('/test/777/{unknown}/', StringTemplate::process($strWithOneVar, ['id' => '777']));

		$strWithNonStandardDelimiters = '/[$category]/[$id]/';
		$this->assertEquals('/sale/777/', StringTemplate::process($strWithNonStandardDelimiters, [
			'category' => 'sale', 'id' => '777'
		], '/\[\$(.+?)\]/'));

		$strWithNonStandardDelimiters = '/[$category]/[id]/';
		$this->assertEquals('/sale/777/', StringTemplate::process($strWithNonStandardDelimiters, [
			'category' => 'sale', 'id' => '777'
		], '/\[(\$)?(.+?)\]/', 2));
	}

	public function testProcessWithCallback()
	{
		$strWithOneVar = '/test/{id}/';
		$this->assertEquals('/test/777/', StringTemplate::process($strWithOneVar, function () {
			return '777';
		}));

		$strWithManyVars = '/{category}/{id}/';
		$this->assertEquals('/sale/777/', StringTemplate::process($strWithManyVars, function ($name) {
			$values = ['category' => 'sale', 'id' => '777'];
			return $values[$name];
		}));

		$strWithOneVar = '/test/{id}/{unknown}/';
		$this->assertEquals('/test/777/{unknown}/', StringTemplate::process($strWithOneVar, function ($name) {
			if ($name == 'id') {
				return 777;
			}
		}));
	}
}