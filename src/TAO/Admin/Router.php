<?php

namespace TAO\Admin;

class Router extends \TAO\Router
{
	public $path;
	public $datatype;
	public $datatypeCode;
	public $vars;
	public $varsScope;

	public function route($request)
	{
		if (config('tao.std_routes.admin_login', true)) {
			app()->router->get('admin/login', '\\TAO\\Admin\\Controller\\Login@showLoginForm');
			app()->router->post('admin/login', '\\TAO\\Admin\\Controller\\Login@login');
			app()->router->post('admin/logout', '\\TAO\\Admin\\Controller\\Login@logout');
		}
		app()->router->any('admin', '\\TAO\\Admin\\Controller\\Dashboard@index');
		app()->router->any('tao/fields/api', '\\TAO\\Fields\\Controllers\\API@index');

		$path = $request->path();
		if ($m = app()->tao->regexp('{^admin/(.+)$}', $path)) {
			$path = $m[1];
			if ($m = app()->tao->regexp('{^datatype/([^/]+)$}', $path)) {
				$code = $m[1];
				$datatype = app()->tao->datatype($code, false);
				if ($datatype) {
					$this->datatype = $datatype;
					$this->datatypeCode = $code;
					$controller = $datatype->adminController();
					if ($controller) {
						app()->router->any("/admin/datatype/{$code}", $controller);
					}
				}
			} elseif ($m = app()->tao->regexp('{^vars/([^/]+)$}', $path)) {
				$group = $m[1];
				$this->vars = config("vars.{$group}", false);
				$this->varsScope = $group;
				if (is_array($this->vars)) {
					app()->router->any("/admin/vars/{$group}", '\\TAO\\Admin\\Controller\\Vars@entryPointAction');
				}
			} elseif ($path == 'vars') {
				$this->vars = config("vars", []);
				app()->router->any("/admin/vars", '\\TAO\\Admin\\Controller\\Vars@entryPointAction');
			}
		}

		return false;
	}
}
