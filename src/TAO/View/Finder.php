<?php

namespace TAO\View;

use Illuminate\View\FileViewFinder;

class Finder extends FileViewFinder
{
	protected $resourcesPaths = null;
	protected $symlinks = [];
	protected $locationsProcessed = false;

	public function exists($view)
	{
		$path = false;
		try {
			$path = parent::find($view);
		} catch (\Exception $e) {
			return false;
		}
		return $path;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	public function findPath($name)
	{
		$this->setupResourcesPaths();
		$this->setupLocations();

		if ($name == '~layout') {
			$name = app()->tao->layout;
		}

		$name = preg_replace('{\s*~\s*}', '.', $name);

		if ($path = $this->exists($name)) {
			return $path;
		}

		foreach (\TAO::getViewsPrefixes() as $prefix) {
			if ($path = $this->exists("{$prefix}{$name}")) {
				return $path;
			}
		}

		if (is_string($name) && strpos($name, '#') !== false) {
			$path = str_replace('#', '/', $name);
			foreach (['phtml', 'blade.php', 'twig', 'html.twig'] as $ext) {
				if ($this->files->exists("{$path}.{$ext}")) {
					if ($ext == 'phtml') {
						\View::addExtension('phtml', 'php');
					}
					return "{$path}.{$ext}";
				}
			}
		}

		if ($path = $this->findInTAO($name)) {
			return $path;
		}

		return false;
	}

	public function find($name)
	{
		if ($path = $this->findPath($name)) {
			return $path;
		}

		return parent::find($name);
	}


	public function findInTAO($name)
	{
		$paths = \TAO::getViewsPaths();
		$paths['tao'] = true;

		$names = explode('|', $name);
		foreach ($names as $name) {
			if ($name = trim($name)) {
				if ($view = $this->exists($name)) {
					return $view;
				}
				foreach ($paths as $vendor => $path) {
					if ($view = $this->exists("{$vendor}::{$name}")) {
						return $view;
					}
				}
			}
		}
		return false;
	}

	public function setupResourcesPaths()
	{
		if (is_null($this->resourcesPaths)) {
			$this->resourcesPaths = [];
			$www = \TAO::publicPath();
			foreach ($this->buildResourcesPathsSource() as $path) {
				$namespace = false;

				if ($m = \TAO::regexp('{^([a-z0-9_-]+)::(.+)$}', $path)) {
					$namespace = $m[1];
					$path = $m[2];
				}
				$path = rtrim(trim($path), '/');
				if (!empty($path)) {
					if ($path[0] != '.' && $path[0] != '/') {
						$path = base_path($path);
					}
				}

				if ($namespace) {
					$link = "{$www}/{$namespace}";
					$public = "{$path}/public";
					if (is_dir($public)) {
						$this->symlinks[$link] = $public;
					}
				}

				$this->resourcesPaths[] = $path;
			}
			$this->checkSymlinks();
		}

		return $this;
	}

	protected function setupLocations()
	{
		if ($this->locationsProcessed) {
			return;
		}

		foreach ($this->resourcesPaths as $path) {
			$this->addLocation("{$path}/views");
		}

		$this->locationsProcessed = true;
	}

	protected function buildResourcesPathsSource()
	{
		$src = config('tao.resources_paths', []);
		if (is_string($src)) {
			$src = [$src];
		}
		$paths = ['resources'];
		foreach ($src as $path) {
			if (\TAO\Callback::isValidCallback($path)) {
				$addPaths = \TAO\Callback::instance($path)->call();
				if (is_string($addPaths)) {
					$paths[] = $addPaths;
				} elseif (is_array($addPaths)) {
					$paths = array_merge($addPaths);
				}
			} else {
				if (!empty($path)) {
					$paths[] = $path;
				}
			}
		}
		$paths[] = 'tao::vendor/techart/tao3/resources';
		return $paths;
	}

	protected function checkSymlinks()
	{
		foreach ($this->symlinks as $link => $path) {
			if (!is_link($link) && !is_dir($link)) {
				symlink($path, $link);
			}
		}
	}

	public function findInResources($files)
	{
		$this->setupResourcesPaths();

		if (is_string($files)) {
			$files = [$files];
		}
		foreach ($this->resourcesPaths as $path) {
			foreach ($files as $file) {
				$_path = "{$path}/{$file}";
				if (is_file($_path)) {
					return $_path;
				}
			}
		}
	}
}
