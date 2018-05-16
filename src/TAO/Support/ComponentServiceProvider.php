<?php

namespace TAO\Support;

use Illuminate\Support\ServiceProvider;

abstract class ComponentServiceProvider extends ServiceProvider
{
	abstract public function mnemocode();

	abstract protected function packageDir();

	abstract protected function namespace();

	public function viewsPath()
	{
		return $this->packageDir() . '/views';
	}

	public function routesPath()
	{
		return $this->packageDir() . '/routes';
	}

	public function commandsPath()
	{
		return $this->packageDir() . '/src/Commands';
	}

	public function translationsPath()
	{
		return $this->packageDir() . '/resources/lang';
	}

	public function boot()
	{
		$this->loadViews();
		$this->loadRoutes();
		$this->loadCommands();
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
				$this->loadRoutesFrom($file);
			}
		}
	}

	protected function loadCommands()
	{
		$commands = [];
		$path = $this->commandsPath();
		if ($path && file_exists($path)) {
			if (is_dir($path)) {
				foreach (\File::allFiles($path) as $file) {
					$commandClassName = str_replace('.' . $file->getExtension(), '', $file->getFilename());
					$commands[] = $this->namespace() . '\\Commands\\' . $commandClassName;
				}
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
}