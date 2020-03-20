<?php

namespace TAO\FSPages;

use Illuminate\Contracts\View\Factory as ViewFactory;

class Router extends \TAO\Router
{
	public $path;

	public function route($request)
	{
		$path = 'pages/' . $request->path();
		$paths = [
			"{$path}/index.php",
			"{$path}.php",
		];
		
		if ($finded = $path = app('view.finder')->findInResources($paths)) {
			$this->path = $finded;
			return array(
				'controller' => Controller::class,
				'action' => 'file',
			);
		}
		
		$template = 'pages.' . str_replace('/', '.', trim($request->path(), '/'));
		if (view()->exists($template)) {
			$this->path = $template;
			return array(
				'controller' => Controller::class,
				'action' => 'template',
			);
		}
		
		return false;
	}
}
