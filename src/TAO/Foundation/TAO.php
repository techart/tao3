<?php

namespace TAO\Foundation;

use TAO\Callback;
use TAO\Exception\UnknownSelector;
use TAO\Frontend\Manager;
use TAO\Navigation;
use TAO\ORM\Model;
use TAO\ORM\Model\User;
use TAO\Router;
use TAO\Exception\UnknownDatatype;
use TAO\Type;
use TAO\Type\Collection;

class TAO
{
	/**
	 * @var
	 */
	public $app;
	/**
	 * @var
	 */
	public $routers;
	/**
	 * @var
	 */
	public $router;
	/**
	 * @var
	 */
	public $routerName;

	public $layout = 'layouts.app';

	protected $datatypes = [];
	protected $datatypeClasses;
	protected $controller;
	protected $inAdmin = false;
	protected $selectors = [];
	protected $viewsPaths = [];
	protected $viewsPrefixes = [];
	protected $variant = false;
	protected $itemsForSelectCache = [];

	public function useLayout($name)
	{
		$this->layout = $name;
	}

	public function getLayout()
	{
		return $this->layout;
	}

	public function setController($controller)
	{
		$this->controller = $controller;
	}

	public function controller()
	{
		return $this->controller;
	}

	public function setInAdmin($value)
	{
		$this->inAdmin = $value;
		return $this;
	}

	public function inAdmin()
	{
		return $this->inAdmin;
	}

	public function isHttps()
	{
		return request()->isSecure();
	}

	public function pageNotFound()
	{
		return response(view('404'), 404);
	}

	public function version()
	{
		$data = json_decode(file_get_contents('../vendor/techart/tao3/composer.json'));
		return $data->version;
	}

	/**
	 *
	 */
	public function route()
	{
		if (!$this->isCLI()) {
			/**
			 * @var Request $request
			 */
			$request = app()->request();
			foreach (array_keys($this->routers()) as $name) {
				$router = $this->router($name);
				if (method_exists($router, 'route')) {
					$data = $router->route($request);
					if ($data) {
						$this->router = $router;
						$this->routerName = $name;

						if (is_string($data)) {
							$controller = $data;
							$action = false;
							if (preg_match('{^(.+)@(.+)$}', $data, $m)) {
								$controller = $m[1];
								$action = $m[2];
							}
							$data = array(
								'controller' => $controller,
								'action' => $action,
							);
						}

						$controller = isset($data['controller']) ? $data['controller'] : false;
						$action = isset($data['action']) ? $data['action'] : 'index';

						if ($controller && $action) {
							$pattern = $request->getPathInfo();
							$controller = '\\' . trim($controller, '\\');
							app()->router->any($pattern, "{$controller}@{$action}");
							break;
						}
					}
				}
			}
		}
	}

	public function addRouter($name, $class)
	{
		$this->routers();
		if (!isset($this->routers[$name])) {
			$this->routers[$name] = $class;
		}
		return $this;
	}

	public function routes()
	{
		foreach (array_keys($this->routers()) as $name) {
			$router = $this->router($name);
			if (method_exists($router, 'routes')) {
				$router->routes();
			}
		}
		$this->route();
	}

	/**
	 * @return array
	 */
	public function routers()
	{
		if (is_null($this->routers)) {
			$this->routers = config('tao.routers');
			if (!is_array($this->routers)) {
				$this->routers = array();
			}
		}
		return $this->routers;
	}

	/**
	 * @param $name
	 * @return Router|bool
	 */
	public function router($name)
	{
		$this->routers();
		if (isset($this->routers[$name])) {
			if (is_string($this->routers[$name])) {
				$this->routers[$name] = app($this->routers[$name]);
			}
			return $this->routers[$name];
		}
		return false;
	}

	public function datatypeClasses()
	{
		if (!$this->datatypeClasses) {
			$this->datatypeClasses = config('tao.datatypes', array());
		}
		return $this->datatypeClasses;
	}

	/**
	 * @param $name
	 * @param string|null $default
	 * @return string
	 * @throws UnknownDatatype
	 */
	public function datatypeClass($name, $default = null)
	{
		$datatypeClasses = $this->datatypeClasses();
		if (!isset($datatypeClasses[$name])) {
			$callbacks = config('tao.get_datatype_class', []);
			$callbacks = is_string($callbacks)? [$callbacks] : $callbacks;
			foreach($callbacks as $cb) {
				if (\TAO\Callback::isValidCallback($cb)) {
					if ($class = \TAO\Callback::instance($cb)->args([$name])->call($this)) {
						return $class;
					}
				}
			}
			if (is_null($default)) {
				throw new UnknownDatatype($name);
			}
			return $default;
		}
		return $datatypeClasses[$name];
	}

	/**
	 * @param string $name
	 * @param Model|string|null $default
	 * @return Model
	 * @throws UnknownDatatype
	 */
	public function datatype($name, $default = null)
	{
		if (isset($this->datatypes[$name])) {
			return $this->datatypes[$name];
		}

		$datatype = $this->makeDatatype($name);
		if (!is_null($datatype)) {
			return $this->datatypes[$name] = $datatype;
		}
		
		if (!is_null($default) && $default) {
			if (is_string($default)) {
				$default = app($default);
			}
			$default->initDatatype();
			return $default;
		}
		
		if (!is_null($default)) {
			return $default;
		}

		throw new UnknownDatatype($name);
	}

	public function isDatatypeExists($name)
	{
		$ret = true;
		try {
			if($this->datatypeClass($name)) {
				$ret = true;
			}
		} catch (UnknownDatatype $e) {
			$ret = false;
		}
		return $ret;
	}

	/**
	 * @param string $name
	 * @return Model|null
	 * @throws UnknownDatatype
	 */
	protected function makeDatatype($name)
	{
		$datatype = null;
		if ($this->isDatatypeExists($name)) {
			/** @var Model $datatype */
			$datatype = app($this->datatypeClass($name));
			$datatype->initDatatype();
		}
		return $datatype;
	}

	public function addDatatype($name, $class)
	{
		$this->datatypeClasses[$name] = $this->datatypeClass($name, $class);
		return $this;
	}

	public function datatypeCodes()
	{
		return array_keys($this->datatypeClasses());
	}

	/**
	 * @return Model[]
	 */
	public function datatypes()
	{
		$datatypes = array();
		foreach ($this->datatypeCodes() as $code) {
			$datatypes[$code] = $this->datatype($code);
		}
		return $datatypes;
	}

	public function datatypeCodeByClass($class)
	{
		$class = ltrim($class, '/');
		$datatypes = $this->datatypeClasses();
		foreach ($datatypes as $code => $dclass) {
			$dclass = ltrim($dclass, '/');
			if ($class == $dclass) {
				return $code;
			}
		}
		return $class;
	}

	public function addSelector($code, $class)
	{
		$this->selectors[$code] = $class;
	}

	public function selector($code, $default = null)
	{

		if (isset($this->selectors[$code])) {
			$class = $this->selectors[$code];
			if (is_object($class)) {
				return $class;
			}
			return app()->make($class)->setMnemocode($code);
		}

		$class = config("tao.selectors.{$code}");
		if ($class) {
			return app()->make($class)->setMnemocode($code);
		}

		$datatype = static::datatype($code, false);
		if ($datatype) {
			return $datatype->selector();
		}
		
		if ($selector = \TAO\Selector::$routedSelectors[$code] ?? false) {
			return $selector;
		}

		if (!is_null($default)) {
			return $default;
		}

		throw new UnknownSelector($code);
	}

	public function publicPath()
	{
		return app()->make('path.public');
	}

	/**
	 * @return bool
	 */
	public function isCLI()
	{
		return !isset($_SERVER['REQUEST_URI']);
	}

	public function regexp($regexp, $s)
	{
		return preg_match($regexp, $s, $m) ? $m : false;
	}

	public function connectionNameFor()
	{
		return false;
	}

	public function classModified($class)
	{
		$name = is_string($class) ? $class : get_class($class);
		$time = $this->getClassModifyTime($class);
		$key = 'class-modify-' . str_replace('\\', '-', $name);
		$cachedTime = \Cache::get($key, 0);
		if ($time > $cachedTime) {
			\Cache::put($key, $time, 500000);
			return true;
		}
		return false;
	}

	public function getClassModifyTime($class)
	{
		$ref = $class instanceof \ReflectionClass ? $class : new \ReflectionClass($class);
		$file = $ref->getFileName();
		$time = filemtime($file);
		$timeParent = 0;
		$refParent = $ref->getParentClass();
		if ($refParent) {
			$timeParent = $this->getClassModifyTime($refParent);
		}
		return $time > $timeParent ? $time : $timeParent;
	}

	public function path($extra = false)
	{
		$path = str_replace('/src/TAO/Foundation', '', __DIR__);
		if ($extra) {
			$path .= "/$extra";
		}
		return $path;
	}

	public function isIterable(&$object)
	{
		return is_array($object) || $object instanceof \Traversable;
	}

	public function navigation($name = 'site')
	{
		return Navigation::instance($name);
	}

	public function setMeta($name, $value)
	{
		\Assets::setMeta($name, $value);
	}

	public function setMetas($metas)
	{
		\Assets::setMetas($metas);
	}

	public function meta()
	{
		return \Assets::meta();
	}

	public function render($template, $context = array())
	{
		return app('tao.view')->render($template, $context);
	}

	public function renderWithinLayout($template, $context = array())
	{
		return app('tao.view')->renderWithinLayout($template, $context);
	}

	public function itemsForSelect($src)
	{
		if (is_array($src)) {
			return $src;
		}
		$out = [];
		$args = [];
		$cacheKey = false;
		if (is_string($src)) {
			$cacheKey = 'itemsForSelect-'.md5($src);
			if (isset($this->itemsForSelectCache[$cacheKey])) {
				return $this->itemsForSelectCache[$cacheKey];
			}
			if ($m = \TAO::regexp('{^(.+?)/(.+)$}', $src)) {
				$src = trim($m[1]);
				$args = Collection::parseString(trim($m[2]));
			}
			if ($m = \TAO::regexp('{^datatype:(.+)$}', $src)) {
				$datatypeCode = trim($m[1]);
				$method = 'itemsForSelect';
				if ($m = \TAO::regexp('{^(.+)::(.+)$}', $datatypeCode)) {
					$datatypeCode = trim($m[1]);
					$method = trim($m[2]);
				}
				$src = "datatype.{$datatypeCode}::{$method}";
			}
		}
		if (Type::isCallable($src)) {
			$callback = Callback::instance($src);
			if (!empty($args)) {
				$callback->args([$args]);
			}
			$out = $callback->call();
		}
		if ($cacheKey) {
			$this->itemsForSelectCache[$cacheKey] = $out;
		}
		return $out;
	}

	public function merge($a, $b)
	{
		foreach ($b as $k => $v) {
			if (isset($a[$k]) && is_array($a[$k]) && is_array($v)) {
				$a[$k] = $this->merge($a[$k], $v);
			} else {
				$a[$k] = $v;
			}
		}
		return $a;
	}

	public function vars($name = false)
	{
		static $storage = false;
		if (!$storage) {
			$storage = app()->make('\\TAO\\Vars\\Storage');
		}
		if ($name) {
			return $storage->get($name);
		}
		return $storage;
	}

	public function authorized($callback = false)
	{
		$user = \Auth::user();
		if (is_object($user)) {
			if (Type::isCallable($callback)) {
				return Callback::instance($callback)->call();
			}
			return true;
		} else {
			if (Type::isCallable($callback)) {
				return redirect('/users/login/');
			}
			return false;
		}
	}

	public function frontend($path = false, $options = [])
	{
		return Manager::instanse($path, $options);
	}

	public function addViewsPath($path, $vendor = false)
	{
		if (!$vendor) {
			$vendor = uniqid('v');
		}
		$this->viewsPaths[$vendor] = $path;
		return $this;
	}

	public function getViewsPaths()
	{
		return $this->viewsPaths;
	}


	public function addViewsPrefix($prefix)
	{
		$this->viewsPrefixes[] = $prefix;
		return $this;
	}

	public function getViewsPrefixes()
	{
		return $this->viewsPrefixes;
	}

	/**
	 * Возвращает список вариантов контента (например языков или регионов сайта)
	 */
	public function getVariants()
	{
		static $variants = null;
		if (is_null($variants)) {
			$variants = config('tao.variants', false);
			if (!is_array($variants) || empty($variants)) {
				return $variants = false;
			}
			if (!isset($variants['default'])) {
				$variants['default'] = array(
					'label' => 'Default',
					'postfix' => '',
				);
			}
			$out = array();
			foreach ($variants as $code => $variant) {
				if (!isset($variant['postfix'])) {
					$variant['postfix'] = $code == 'default' ? '' : "_v_{$code}";
				}
				$out[$code] = $variant;
			}
			$variants = $out;
		}
		return $variants;
	}

	/**
	 * Устанавливает текущий вариант контента
	 *
	 * @param $variant
	 * @return $this
	 */
	public function setVariant($variant)
	{
		$this->variant = $variant;
		return $this;
	}

	/**
	 * Возвращает текущий вариант контента
	 *
	 * @return bool
	 */
	public function getVariant()
	{
		$variant = $this->variant;
		if (!$variant) {
			return 'default';
		}
		return $variant;
	}
}
