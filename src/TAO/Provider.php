<?php

namespace TAO;

use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use TAO\Components\Sitemap\Manager;
use TAO\Foundation\HTTP;

class Provider extends ServiceProvider
{
	public function register()
	{
		$this->bindServices();
		$this->registerRouters();
	}

	public function boot()
	{
		setlocale(LC_ALL, config('app.php_locale'));
		Carbon::setLocale(config('app.locale'));
		$this->publishes([__DIR__ . '/../../config/tao.php' => config_path('tao.php')]);
		$this->loadTranslationsFrom(__DIR__ . '/../../resources/lang/', 'techart');

		$this->checkEnviroment();
		$this->bootRouters();
		$this->setupBlade();
		$this->setupConsoleCommands();
	}

	protected function setupConsoleCommands()
	{
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
						$commands[] = $namespace . '\\Console\\Commands\\' . $commandClassName;
					}
				}
			}
			$commands = \TAO::merge($commands, config('app.artisan.commands', []));
			$this->commands($commands);
		}
	}

	protected function bootRouters()
	{
		foreach (array_keys(app()->tao->routers()) as $name) {
			$router = app()->tao->router($name);
			if (method_exists($router, 'boot')) {
				$router->boot();
			}
		}
	}

	protected function setupBlade()
	{
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
			return "<?php Assets::useStyle({$args}); ?>";
		});

		Blade::directive('script', function ($args) {
			return "<?php Assets::useScript({$args}); ?>";
		});

		Blade::directive('bottomScript', function ($args) {
			return "<?php Assets::useBottomScript({$args}); ?>";
		});

		Blade::directive('block', function ($args) {
			return "<?php print TAO::frontend()->render({$args}); ?>";
		});

		Blade::directive('textProcess', function ($args) {
			return "<?php ob_start(); ?>";
		});

		Blade::directive('endTextProcess', function ($args) {
			return "<?php \$textProcess = ob_get_clean(); print \TAO\Text::process(\$textProcess, [$args]) ?>";
		});
	}

	protected function checkEnviroment()
	{
		$www = \TAO::publicPath();
		if (!\App::environment('testing')) {
			$link = "{$www}/storage";
			if (!is_link($link)) {
				symlink(storage_path('app/public'), $link);
			}
			$this->linkTinymce();
			if (is_null(config('logging'))) {
				$path = base_path('config/logging.php');
				if (!is_file($path)) {
					file_put_contents($path, '<?php return tao_cfg(\'logging\');');
				}
			}
		}
	}

	protected function makeService($service, $app)
	{
		if (is_string($service)) {
			if (starts_with($service, '*')) {
				$method = substr($service, 1);
				return call_user_func([$this, $method], $app);
			}
			if ($m = \TAO::regexp('{^(.+)\*(.+)$}', $service)) {
				$object = app()->make($m[1]);
				call_user_func([$object, $m[2]], $app);
				return $object;
			}
		}
		if (is_callable($service)) {
			return call_user_func($service, $app);
		}
		return app()->make($service);
	}

	protected function bindServices()
	{
		$services = config('tao.services.binds');
		if (is_array($services)) {
			foreach($services as $code => $service) {
				if ($service) {
					$this->app->bind($code, function($app) use ($service) {
						return $this->makeService($service, $app);
					});
				}
			}
		}

		$services = config('tao.services.singletons');
		if (is_array($services)) {
			foreach($services as $code => $service) {
				if ($service) {
					$this->app->singleton($code, function($app) use ($service) {
						return $this->makeService($service, $app);
					});
				}
			}
		}
	}

	protected function makeViewFinderService($app)
	{
		$finder = new \TAO\View\Finder($app['files'], []);
		return $finder;
	}

	protected function makeUrlService($app)
	{
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
	}

	protected function makeSessionService($app)
	{
		return new \TAO\Session\Manager($app);
	}

	protected function makeTaoService()
	{
		$tao = app()->make('\TAO\Foundation\TAO');
		$tao->app = $this->app;
		return $tao;
	}

	protected function registerRouters()
	{
		foreach (array_keys(app()->tao->routers()) as $name) {
			$router = app()->tao->router($name);
			if (method_exists($router, 'register')) {
				$router->register();
			}
		}
	}

	/**
	 * Создание симлинка на tinyMCE
	 */
	protected function linkTinymce()
	{
		$scriptsDir = \TAO::publicPath() . '/tao/scripts';
		$link = $scriptsDir . '/tinymce';
		$linkTo = base_path("vendor/tinymce/tinymce");

		if (is_dir($scriptsDir) && !is_link($link)) {
			symlink($linkTo, $link);
		}
	}
}
