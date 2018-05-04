<?php

if (!function_exists('callbackTest')) {
	function callbackTest()
	{
		return 5;
	}
}

if (!function_exists('callbackTestWithArgs')) {
	function callbackTestWithArgs()
	{
		return array_sum(func_get_args());
	}
}