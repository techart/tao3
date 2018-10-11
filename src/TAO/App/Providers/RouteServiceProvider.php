<?php

namespace TAO\App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
	/**
	 * This namespace is applied to your controller routes.
	 *
	 * In addition, it is set as the URL generator's root namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'App\Http\Controllers';

	/**
	 * Define your route model bindings, pattern filters, etc.
	 *
	 * @return void
	 */
	public function boot()
	{
		//

		parent::boot();
	}

	/**
	 * Define the routes for the application.
	 *
	 * @return void
	 */
	public function map()
	{
		app()->modifyRequest();
		$this->mapApiRoutes();
		$this->mapWebRoutes();
	}

	/**
	 * Define the "web" routes for the application.
	 *
	 * These routes all receive session state, CSRF protection, etc.
	 *
	 * @return void
	 */
	protected function mapWebRoutes()
	{
		if (empty(env('APP_KEY'))) {
			return $this;
		}
		Route::middleware('web')
			->namespace($this->namespace)
			->group($this->webRoutesPath());
	}

	/**
	 * Define the "api" routes for the application.
	 *
	 * These routes are typically stateless.
	 *
	 * @return void
	 */
	protected function mapApiRoutes()
	{
		Route::middleware('api')
			->namespace($this->namespace)
			->group($this->apiRoutesPath());
	}

	/**
	 * @return string
	 */
	protected function apiRoutesPath()
	{
		return $this->packageRoutesPath() . '/api.php';
	}

	/**
	 * @return string
	 */
	protected function webRoutesPath()
	{
		return $this->packageRoutesPath() . '/web.php';
	}

	protected function packageRoutesPath()
	{
		if (\App::environment('testing')) {
			$path = realpath('./routes');
		} else {
			$path = base_path('vendor/techart/tao3/routes');
		}
		return $path;
	}
}
