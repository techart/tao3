<?php

namespace TAO;

class Navigation
{
	public $byIds = array();
	public $flags = array();
	public $id;
	/**
	 * @var string
	 */
	public $url = '/';
	/**
	 * @var string
	 */
	public $title = 'Index';
	/**
	 * @var int
	 */
	public $level = 0;
	/**
	 * @var null
	 */
	public $parent = null;
	/**
	 * @var array|bool
	 */
	public $data = array();

	/**
	 * @var Navigation[]
	 */
	protected $sub;
	/**
	 * @var array
	 */
	protected $filter = array();
	/**
	 * @var null
	 */
	protected $flag = null;
	/**
	 * @var null
	 */
	protected $selected = null;
	/**
	 * @var null
	 */
	protected $match = null;
	/**
	 * @var null
	 */
	protected $access = null;

	/**
	 * @var string
	 */
	protected $defaultTemplate = 'simple';

	/**
	 * @var null
	 */
	protected $route = null;

	/**
	 * @var string
	 */
	protected $delimiter = '::';

	/**
	 * @var bool
	 */
	protected $isRoute = false;

	protected static $instances = array();

	public static function instance($name = 'site')
	{
		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new self($name);
		}
		return self::$instances[$name];
	}

	/**
	 * Navigation constructor.
	 * @param bool|false $data
	 */
	public function __construct($data = false)
	{
		static $counter = 0;
		$counter++;

		if ($data === 'route') {
			$this->defaultTemplate = 'route';
			$this->isRoute = true;
			return;
		}

		if (!$data) {
			$data = 'navigation';
		}

		if (is_string($data)) {
			$this->initRoot($data);
		} elseif (is_array($data)) {
			if (!isset($data['id'])) {
				$data['id'] = 'default' . $counter;
			}
			if (isset($data['url']) && isset($data['title'])) {
				$this->id = $data['id'];
				$this->url = $data['url'];
				$this->title = $data['title'];
				if (isset($data['parent'])) {
					$this->parent = $data['parent'];
					$this->level = $this->parent->level + 1;
				}

				if (isset($data['flag'])) {
					$this->flag = $data['flag'];
					unset($data['flag']);
				}

				if (isset($data['match'])) {
					$this->match = $data['match'];
					unset($data['match']);
				}

				if (isset($data['selected'])) {
					$this->selected = $data['selected'];
					unset($data['selected']);
				}

				if (isset($data['access'])) {
					$this->access = $data['access'];
					unset($data['access']);
				}

				unset($data['id']);
				unset($data['url']);
				unset($data['title']);
				unset($data['parent']);
				$this->byIds[$this->id] = $this;
				if (isset($data['sub'])) {
					if (Type::isCallable($data['sub'])) {
						$this->addArray(Callback::instance($data['sub'])->call($this, $data));
					} else if (\TAO::isIterable($data['sub'])) {

						$this->addArray($data['sub']);
					}
					unset($data['sub']);
				}
				$this->data = $data;
				return;
			}
		}
		//print 'Invalid navigation node<hr>';
		//var_dump($data);
		//die();
	}

	/**
	 * @param $sitemap
	 */
	public function sitemap($sitemap)
	{
		foreach ($this->links() as $link) {
			$sitemap->addEntry($link->url);
			if ($link->count() > 0) {
				$link->filter($this->filter);
				$link->sitemap($sitemap);
				$link->filter();
			}
		}
	}

	/**
	 * @return bool|mixed|null
	 */
	public function checkAccess()
	{
		if (is_null($this->access)) {
			return true;
		}
		if (is_bool($this->access)) {
			return $this->access;
		}
		$user = \Auth::user();
		if (!$user) {
			return false;
		}
		if (Type::isCallable($this->access)) {
			return Callback::instance($this->access)->call($this, $user);
		}
		return $user->checkAccess($this->access);
	}

	/**
	 * @return null|Navigation
	 */
	public function route()
	{
		if (empty($this->route)) {
			$this->route = new self('route');
			$node = $this->selectedNode();
			while ($node) {
				$this->route->add($node);
				$node = $node->selectedNode();
			}
		}
		return $this->route;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function flag($name)
	{
		$this->flags[$name] = true;
		return $this;
	}

	/**
	 * @param $value
	 * @return $this
	 */
	public function delimiter($value)
	{
		$this->delimiter = $value;
		return $this;
	}

	/**
	 * @param $name
	 * @return $this
	 */
	public function unsetFlag($name)
	{
		unset($this->flags[$name]);
		return $this;
	}

	/**
	 * @param $flag
	 * @return bool
	 */
	public function isFlag($flag)
	{
		if (is_object($this->parent)) {
			return $this->parent->isFlag($flag);
		}
		return isset($this->flags[$flag]) && $this->flags[$flag];
	}

	/**
	 *
	 */
	protected function initRoot($name = 'site')
	{
		$path = "../navigation/{$name}.php";
		if (!is_file($path)) {
			$path = \TAO::path("navigation/{$name}.php");
		}
		$struct = include($path);
		$this->sub = new \ArrayObject();
		if (\TAO::isIterable($struct)) {
			$this->addArray($struct);
		}
	}

	/**
	 * @param $data
	 */
	public function add($data)
	{
		if (is_null($this->sub)) {
			$this->sub = new \ArrayObject();
		}
		if ($data instanceof \TAO\Navigation) {
			$node = $data;
		} else {
			$data['parent'] = $this;
			$node = new self($data);
		}
		$this->sub[$node->id] = $node;
		return $this;
	}

	/**
	 * @param $struct
	 */
	public function addArray($struct)
	{
		$count = 0;
		foreach ($struct as $k => $data) {
			$count++;
			if (is_string($k) && is_string($data)) {
				$data = array(
					'url' => $k,
					'title' => $data,
				);
				$k = $count;
			}
			if (!isset($data['url']) || !isset($data['title'])) {
				continue;
			}
			if (is_string($k) && !isset($data['id'])) {
				$data['id'] = $k;
			}
			$this->add($data);
		}
	}

	/**
	 * @return array
	 */
	public function links()
	{
		if (empty($this->sub)) {
			return array();
		}
		$links = array();
		foreach ($this->sub as $link) {
			$valid = true;
			foreach ($this->filter as $p) {
				if (!$link->checkFilter($p)) {
					$valid = false;
					break;
				}
			}
			if ($valid) {
				$links[] = $link;
			}
		}
		return $links;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		if (empty($this->sub)) {
			return 0;
		}
		return count($this->sub);
	}

	/**
	 * @return bool
	 */
	public function hasChilds()
	{
		return $this->count() > 0;
	}

	/**
	 * @return bool
	 */
	public function hasGrantedChilds()
	{
		if (!$this->hasChilds()) {
			return false;
		}
		foreach ($this->links() as $link) {
			if ($link->checkAccess()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @return $this
	 */
	public function filter()
	{
		$args = func_get_args();
		if (count($args) == 1 && is_array($args[0])) {
			$args = $args[0];
		}
		$this->filter = $args;
		return $this;
	}

	/**
	 * @param $p
	 * @return bool
	 */
	public function checkFilter($p)
	{
		if (is_string($p) && $p != '') {
			if ($p[0] == '!') {
				$p = substr($p, 1);
				return !isset($this->data[$p]) || $this->data[$p] === false;
			}
			return isset($this->data[$p]) && $this->data[$p] !== false;
		}
		return true;
	}

	/**
	 * @return bool|null
	 */
	public function isSelected($defTrue = true, $defFalse = false)
	{
		if (!is_null($this->selected)) {
			if (Type::isCallable($this->selected)) {
				return Callback::instance($this->selected)->call($this) ? $defTrue : $defFalse;
			}
			return $this->selected ? $defTrue : $defFalse;
		}
		if (!empty($this->flag)) {
			if (\TAO::isIterable($this->flag)) {
				foreach ($this->flag as $flag) {
					if ($this->isFlag($flag)) {
						$this->selected = true;
						return $defTrue;
					}
				}
			} elseif (is_string($this->flag) && $this->isFlag($this->flag)) {
				$this->selected = true;
				return $defTrue;
			}
		}
		if (\TAO\Urls::isCurrent($this->url)) {
			$this->selected = true;
			return $defTrue;
		}
		if (!empty($this->match) && is_string($this->match)) {
			if ($this->match == '*') {
				if (\TAO\Urls::isCurrentStartsWith($this->url)) {
					$this->selected = true;
					return $defTrue;
				}
			} elseif (mb_substr($this->match, mb_strlen($this->match) - 1) == '*') {
				$m = mb_substr($this->match, 0, mb_strlen($this->match) - 1);
				if (\TAO\Urls::isCurrentStartsWith($m)) {
					$this->selected = true;
					return $defTrue;
				}
			}
		}
		foreach ($this->links() as $link) {
			if ($link->isSelected()) {
				$this->selected = true;
				return $defTrue;
			}
		}
		$this->selected = false;
		return $defFalse;
	}

	/**
	 * @return bool
	 */
	public function isCurrent()
	{
		return \TAO\Urls::isCurrent($this->url);
	}

	/**
	 * @return Navigation|bool
	 */
	public function selectedNode()
	{
		foreach ($this->links() as $link) {
			if ($link->isSelected()) {
				return $link;
			}
		}
		return false;
	}

	/**
	 * @return $this
	 */
	public function topNode()
	{
		foreach ($this->links() as $link) {
			if ($link->isSelected()) {
				return $link->topNode();
			}
		}
		return $this;
	}

	/**
	 * @param $n
	 * @return $this|bool
	 */
	public function level($n)
	{
		if ($n < 1) {
			return $this;
		}
		$node = $this->selectedNode();
		if ($node) {
			if ($n > 1) {
				return $node->level($n - 1);
			}
			return $node->count() == 0 ? false : $node;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function withDivider()
	{
		return isset($this->data['divider']) && $this->data['divider'];
	}

	/**
	 * @return string
	 */
	public function tag()
	{
		if ($this->isCurrent()) {
			$check = config('tao.navigation.check_tag', true);
			if ($check) {
				if (substr($this->url, 0, 6) != '/admin') {
					return 'div';
				}
			}
		}
		return 'a';
	}

	/**
	 * @return string
	 */
	public function tagClass()
	{
		$class = 'a';
		if ($this->isSelected()) {
			$class .= ' selected';
		}
		return $class;
	}

	/**
	 * @param string $tpl
	 * @param array $args
	 * @return string
	 */
	public function render($tpl = false, $args = array())
	{
		$tpl = $tpl ? $tpl : $this->defaultTemplate;
		$args['links'] = $this->links();
		if ($this->isRoute) {
			if (!isset($args['main'])) {
				$args['main'] = 'Главная';
			}
			if (!isset($args['main_url'])) {
				$args['main_url'] = '/';
			}
			if (!isset($args['delimiter'])) {
				$args['delimiter'] = ' &nbsp;&gt;&nbsp; ';
			}
		}
		return view("navigation ~ {$tpl}", $args);
	}
}