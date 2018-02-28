<?php

namespace TAO\FSPages;

use Illuminate\Contracts\View\Factory as ViewFactory;

class Router extends \TAO\Router
{
	public $path;

	public function route($request)
	{
		$path = 'pages/' . $request->path();

		$paths = array(
			resource_path("{$path}/index.php"),
			resource_path("{$path}/index.phtml"),
			resource_path("{$path}.phtml"),
			resource_path("{$path}.php"),
			__DIR__ . "/../../../{$path}/index.phtml",
			__DIR__ . "/../../../{$path}/index.php",
			__DIR__ . "/../../../{$path}.phtml",
			__DIR__ . "/../../../{$path}.php",
		);
		foreach ($paths as $fpath) {
			if (is_file($fpath)) {
				$this->path = $fpath;
				return array(
					'controller' => Controller::class,
					'action' => 'file',
				);
			}
		}

		$paths = array(
			$path,
			"{$path}/index",
			"tao::{$path}",
			"tao::{$path}/index",
		);
		$factory = app(ViewFactory::class);
		foreach ($paths as $path) {
			if ($factory->exists($path)) {
				$this->path = $path;
				return array(
					'controller' => Controller::class,
				);
			}

		}
		return false;
	}
}
