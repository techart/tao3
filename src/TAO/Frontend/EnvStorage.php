<?php

namespace TAO\Frontend;

use Techart\Frontend\EnvironmentStorageInterface;

class EnvStorage implements EnvironmentStorageInterface
{
	public function getFromConfig($name)
	{
		$env = env('FRONTEND_ENV', false);
		if ($env) {
			return $env;
		}
		$env = config('app.env', 'prod');
		return ($env == 'dev' || $env == 'development') ? 'dev' : 'prod';
	}

	public function getFromRequest($name)
	{
		return app()->request->get($name);
	}

	public function getFromSession($name)
	{
		return \Session::get($name);
	}

	public function setToSession($name, $value)
	{
		\Session::put($name, $value);
	}
}