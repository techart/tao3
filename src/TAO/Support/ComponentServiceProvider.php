<?php

namespace TAO\Support;

use Illuminate\Support\ServiceProvider;
use TAO\Config\ConfigFileGenerator;

abstract class ComponentServiceProvider extends ServiceProvider
{
	abstract public function mnemocode();

	abstract protected function packageDir();

	abstract protected function namespace();

	public function viewsPath()
	{
		return $this->packageDir() . '/resources/views';
	}

	public function routesPath()
	{
		return $this->packageDir() . '/routes';
	}

	public function configPath()
	{
		return $this->packageDir() . '/config';
	}

	public function commandsPath()
	{
		return $this->packageDir() . '/src/Console/Commands';
	}

	public function translationsPath()
	{
		return $this->packageDir() . '/resources/lang';
	}

	public function boot()
	{
		$this->loadViews();
		if (!$this->isPublished()) {
			$this->loadConfig();
			$this->setPublished();
		}
		if ($this->routesAutoloadEnabled()) {
			$this->loadRoutes();
		}
		if ($this->app->runningInConsole()) {
			$this->loadCommands();
		}
		$this->loadTranslations();
	}

	protected function loadViews()
	{
		$path = $this->viewsPath();
		if ($path && file_exists($path)) {
			$this->loadViewsFrom($path, $this->mnemocode());
			\TAO::addViewsPath($path, $this->mnemocode());
		}
	}

	protected function loadRoutes()
	{
		$path = $this->routesPath();
		if ($path && is_dir($path)) {
			foreach (\File::allFiles($path) as $file) {
				switch ($file->getFilename()) {
					case 'web.php':
						\Route::middleware('web')->group((string)$file);
						break;
					case 'api.php':
						\Route::middleware('api')->group((string)$file);
						break;
					default:
						$this->loadRoutesFrom($file);
				}
			}
		}
	}

	protected function loadConfig()
	{
		foreach ($this->getConfigFiles() as $file) {
			$configName = $file->getBasename('.' . $file->getExtension());
			ConfigFileGenerator::run($configName, $this->mnemocode());
		}
	}

	protected function getConfigFiles()
	{
		$files = [];
		$path = $this->configPath();
		if ($path && is_dir($path)) {
			$files = \File::allFiles($path);
		}
		return $files;
	}

	protected function loadCommands()
	{
		$commands = [];
		$path = $this->commandsPath();
		if ($path && is_dir($path)) {
			foreach (\File::allFiles($path) as $file) {
				$commandClassName = $file->getBasename('.' . $file->getExtension());
				$commands[] = $this->namespace() . '\\Console\\Commands\\' . $commandClassName;
			}
		}
		if ($commands) {
			$this->commands($commands);
		}
	}

	protected function loadTranslations()
	{
		$path = $this->translationsPath();
		if ($path && is_dir($path)) {
			$this->loadTranslationsFrom($path, $this->mnemocode());
		}
	}

	protected function isPublished()
	{
		return \Cache::get($this->cachePublishedKey());
	}

	protected function setPublished()
	{
		return \Cache::set($this->cachePublishedKey(), 1);
	}

	protected function cachePublishedKey()
	{
		return $this->mnemocode() . '.published';
	}

	protected function routesAutoloadEnabled()
	{
		return config($this->mnemocode() . '.routes_autoload', true);
	}
}
