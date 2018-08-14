<?php

namespace TaoTests;

use TAO\Callback;
use TaoTests\Utils\Callback\Datatype;
use TaoTests\Utils\SimpleDatatype;

class CallbackTest extends TestCase
{
	protected function getDatatypes()
	{
		return [
			'callbackTest' => Datatype::class
		];
	}

	public function testFunctionCall()
	{
		require 'Utils/Callback/function.php';

		$this->assertEquals(5, Callback::instance('callbackTest')->call());
	}

	public function testMethodCall()
	{
		$this->assertEquals(self::methodForTesting(), Callback::instance([self::class, 'methodForTesting'])->call());
		$this->assertEquals(self::methodForTesting(), Callback::instance([$this, 'methodForTesting'])->call());
	}

	public function testArgumentsPassing()
	{
		$arg1 = 1;
		$arg2 = 2;
		$this->assertEquals(
			$arg1 + $arg2,
			Callback::instance([$this, 'methodForTestArgs'])->call($arg1, $arg2)
		);
		$this->assertEquals(
			$arg1 + $arg2,
			Callback::instance([$this, 'methodForTestArgs'])->args([$arg1, $arg2])->call()
		);
	}

	public function testValidationCallback()
	{
		require 'Utils/Callback/function.php';

		$this->assertTrue(Callback::isValidCallback('callbackTest'));
		$this->assertTrue(Callback::isValidCallback([self::class, 'methodForTesting']));
		$this->assertTrue(Callback::isValidCallback([$this, 'methodForTesting']));
		$this->assertFalse(Callback::isValidCallback('nonexistentFunction'));
		$this->assertFalse(Callback::isValidCallback([self::class, 'nonexistentMethod']));
		$this->assertFalse(Callback::isValidCallback([$this, 'nonexistentMethod']));
	}

	public function testDatatypeMethodsCall()
	{
		$this->assertTrue(Callback::isValidCallback('datatype.callbackTest::callbackTest'));

		$this->assertEquals(
			\TAO::datatype('callbackTest')->callbackTest(),
			Callback::instance('datatype.callbackTest::callbackTest')->call()
		);
	}

	public function testFunctionWithArgsCall()
	{
		require 'Utils/Callback/function.php';

		$this->assertEquals(3, Callback::instance('callbackTestWithArgs', [1, 2])->call());
		$this->assertEquals(3, Callback::instance('callbackTestWithArgs')->call(1, 2));
	}

	public function testMethodWithArgsCall()
	{
		$args = [1, 3];
		$this->assertEquals(
			self::methodForTestArgs($args[0], $args[1]),
			Callback::instance([self::class, 'methodForTestArgs'], $args)->call()
		);
		$this->assertEquals(
			self::methodForTestArgs($args[0], $args[1]),
			Callback::instance([self::class, 'methodForTestArgs'])->call($args[0], $args[1])
		);
		$this->assertEquals(
			self::methodForTestArgs($args[0], $args[1]),
			Callback::instance([$this, 'methodForTestArgs'], $args)->call()
		);
		$this->assertEquals(
			self::methodForTestArgs($args[0], $args[1]),
			Callback::instance([$this, 'methodForTestArgs'])->call($args[0], $args[1])
		);
	}

	public function testDatatypeMethodsWithArgsCall()
	{
		$this->assertTrue(Callback::isValidCallback('datatype.callbackTest::callbackTest'));

		$arg1 = 2;
		$arg2 = 3;
		$this->assertEquals(
			\TAO::datatype('callbackTest')->callbackArgumentsTest($arg1, $arg2),
			Callback::instance('datatype.callbackTest::callbackArgumentsTest')->call($arg1, $arg2)
		);

		$this->assertEquals(
			\TAO::datatype('callbackTest')->callbackArgumentsTest($arg1, $arg2),
			Callback::instance('datatype.callbackTest::callbackArgumentsTest', [$arg1, $arg2])->call()
		);
	}

	public function testUnknownMethodInClassWithMagicMethodCall()
	{
		$this->assertFalse(Callback::instance([new SimpleDatatype(), 'unknownMethod'])->isValid());
	}

	// Utility methods
	public static function methodForTesting()
	{
		return 6;
	}

	public static function methodForTestArgs($arg1, $arg2)
	{
		return $arg1 + $arg2;
	}
}
