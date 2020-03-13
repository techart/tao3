<?php

namespace TAO\Frontend;

use Techart\Frontend\PathResolver;

class Manager extends \Techart\Frontend\Frontend
{
	protected static $instances = [];

	public static function instanse($path = false, $options = [])
	{
		if (!is_string($path) || empty($path)) {
			$path = config('tao.frontend_path', 'frontend');
		}
		if ($path[0] != '/' && $path[0] != '.') {
			$path = base_path($path);
		}
		$path = rtrim($path, '/');

		if (!isset(self::$instances[$path])) {
			$resolverOptions = array_merge(['twigCachePath' => "{$path}/twig"], $options);
			$resolver = new PathResolver($path, $resolverOptions);
			self::$instances[$path] = new self(Env::instanse(), $resolver);
		}
		return self::$instances[$path];
	}

	public function useStyle($name, $params = [])
	{
		return \Assets::useFile($this->cssUrl($name), $params);
	}

	public function useScript($name, $params = [])
	{
		return \Assets::useFile($this->jsUrl($name), $params);
	}

	public function repositoryInstance($factory)
	{
		return new Repository($factory);
	}

	/**
	 * @param $name
	 * @return \Techart\Frontend\Templates\Bem\Block
	 */
	public function block($name)
	{
		return new \Techart\Frontend\Templates\Bem\Block($name);
	}

	public function __call($name, $arguments)
	{
		switch ($name) {
			case 'render':
			case 'renderBlock':
				$obj = $this->templates();
				break;

			case 'url':
			case 'cssUrl':
			case 'jsUrl':
			case 'cssTag':
			case 'jsTag':
				$obj = $this->assets();
				break;
		}
		return call_user_func_array(array($obj, $name), $arguments);
	}
}
