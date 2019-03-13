<?php

namespace TAO\ORM;

use Illuminate\View\View;

class Router extends \TAO\Router
{
	public $item;

	public function routes()
	{
		if (\TAO::isCLI()) {
			return;
		}
		foreach (\TAO::datatypeCodes() as $code) {
			$datatype = \TAO::datatype($code);
			$datatype->automaticRoutes();
		}
	}
}
