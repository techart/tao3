<?php
namespace TaoTests\Utils\Callback;

use TAO\ORM\Model;

class Datatype extends Model
{
	public function fields()
	{
		return [];
	}

	public function callbackTest()
	{
		return 4;
	}

	public function callbackArgumentsTest($arg1, $arg2)
	{
		return $arg1 * $arg2;
	}
}