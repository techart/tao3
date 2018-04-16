<?php

namespace TAO;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TAO\Components\Sitemap\Manager;


class Provider extends ServiceProvider
{
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/../../config/tao.php' => config_path('tao.php'),
		]);
		$this->loadViewsFrom(__DIR__ . '/../../views', 'tao');

		foreach (array_keys(app()->tao->routers()) as $name) {
			$router = app()->tao->router($name);
			if (method_exists($router, 'boot')) {
				$router->boot();
			}
		}

		Blade::directive('layout', function () {
			$layout = app()->tao->layout;
			return $layout;
		});

		Blade::directive('navflag', function ($name) {
			return "<?php TAO::navigation()->flag({$name}); ?>";
		});

		Blade::directive('asset', function ($args) {
			return "<?php Assets::useFile({$args}); ?>";
		});

		Blade::directive('style', function ($args) {
			return "<?php Assets::useFile({$args}); ?>";
		});

		Blade::directive('script', function ($args) {
			return "<?php Assets::useFile({$args}); ?>";
		});

		Blade::directive('bottomScript', function ($args) {
			return "<?php Assets::useBottomScript({$args}); ?>";
		});

		Blade::directive('block', function ($args) {
			return "<?php print TAO::frontend()->render({$args}); ?>";
		});

		if ($this->app->runningInConsole()) {
			$commands = [];
			$paths = \TAO::merge([
				'\TAO' => __DIR__ . '/Console/Commands',
				'\App' => app_path('Console/Commands'),
			], config('app.artisan.paths', []));
			foreach ($paths as $namespace => $path) {
				if (is_dir($path)) {
					foreach (\File::allFiles($path) as $commandFile) {
						$commandClassName = str_replace('.' . $commandFile->getExtension(), '', $commandFile->getFilename());
						$commands[] = $namespace.'\\Console\\Commands\\' . $commandClassName;
					}
				}
			}
			$commands = \TAO::merge($commands, config('app.artisan.commands', []));
			$this->commands($commands);
		}
	}

	public function register()
	{
		$this->app->bind('view.finder', function ($app) {
			return new \TAO\View\Finder($app['files'], $app['config']['view.paths']);
		});

		$this->app->singleton('url', function ($app) {
			$routes = $app['router']->getRoutes();
			$app->instance('routes', $routes);

			$url = new UrlGenerator($routes, $app->rebinding('request', function ($app, $request) {
				$app['url']->setRequest($request);
			}));

			$url->setSessionResolver(function () {
				return $this->app['session'];
			});

			$app->rebinding('routes', function ($app, $routes) {
				$app['url']->setRoutes($routes);
			});

			return $url;
		});

		$this->app->singleton('session', function ($app) {
			return new \TAO\Session\Manager($app);
		});

		$this->app->singleton('tao', function () {
			$tao = app()->make('\TAO\Foundation\TAO');
			$tao->app = $this->app;
			return $tao;
		});

		$this->app->singleton('tao.fields', function () {
			$fields = app()->make(\TAO\Fields::class);
			$fields->init();
			return $fields;
		});

		$this->app->singleton('tao.admin', function () {
			return app()->make(\TAO\Admin::class);
		});

		$this->app->singleton('tao.assets', function () {
			$assets = app()->make(\TAO\Foundation\Assets::class);
			$assets->init();
			return $assets;
		});

		$this->app->singleton('tao.images', function () {
			$images = app()->make(\TAO\Foundation\Images::class);
			$images->init();
			return $images;
		});

		$this->app->singleton('tao.view', function () {
			$assets = app()->make(\TAO\View::class);
			$assets->init();
			return $assets;
		});

		$this->app->singleton('sitemap.manager', function() {
			return app()->make(Manager::class);
		});

		$this->app->singleton('tao.utils', function() {
			return app()->make(\TAO\Foundation\Utils::class);
		});

		foreach (array_keys(app()->tao->routers()) as $name) {
			$router = app()->tao->router($name);
			if (method_exists($router, 'register')) {
				$router->register();
			}
		}

		$www = \TAO::publicPath();
		$link = "{$www}/tao";
		if (!is_link($link)) {
			$assets = str_replace('/src/TAO', '', __DIR__) . '/public';
			symlink($assets, $link);
		}
		$link = "{$www}/storage";
		if (!is_link($link)) {
			symlink(storage_path('app/public'), $link);
		}
	}


}