<?php

namespace TAO\Support;

use Illuminate\Support\ServiceProvider;

abstract class ComponentServiceProvider extends ServiceProvider
{
	abstract public function mnemocode();

	public function viewsPath()
	{
		return false;
	}

	public function boot()
	{
		$path = $this->viewsPath();
		if ($path) {
			$code = $this->mnemocode();
			$this->loadViewsFrom($path, $code);
			\TAO::addViewsPath($path, $code);
		}
	}
}