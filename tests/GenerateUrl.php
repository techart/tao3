<?php

namespace TaoTests;

class GenerateUrl extends TestCase
{
	public function testGenerateUrl()
	{
		$domain = 'localhost';
		$url = '/url/';
		$this->assertEquals(url($url), 'http://' . $domain . $url);
	}

	public function testTrailingSlash()
	{
		$url = '/url/';
		$result = url($url);
		$this->assertTrue(substr($result, strlen($result) - 1, strlen($result)) == '/');

		$url = '/url';
		$result = url($url);
		$this->assertTrue(substr($result, strlen($result) - 1, strlen($result)) != '/');
	}
}
