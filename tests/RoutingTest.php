<?php

namespace TaoTests;

use Illuminate\Routing\Route;

class RoutingTest extends TestCase
{
	public function testWithoutCallbackInRoutes()
	{
		foreach (\Route::getRoutes() as $route) {
			/** @var Route $route */
			$this->assertFalse(is_callable($route->action['uses']));
		}
	}
}
