<?php

namespace TAO;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use TAO\Callback;
use TAO\Fields\Model;
use TAO\Foundation\Request;

/**
 * Class Selector
 * @package TAO
 *
 * @property string $mnemocode
 * @property Model $datatype
 * @property string $title
 * @property array $args
 * @property array $data
 */
class Selector
{
	public $mnemocode = false;
	public $datatype = false;
	public $title = false;
	public $args = [];
	public $data = [];


	public function setMnemocode($code)
	{
		$this->mnemocode = $code;
		return $this;
	}

	/**
	 * @return Builder|bool
	 */
	public function query()
	{
		if (isset($this->data['query'])) {
			$query = $this->data['query'];
			if ($this->datatype && is_string($query) && preg_match('{^[a-z][a-z0-9_]*$}i', $query)) {
				$query = [$this->datatype, $query];
			}
			return Callback::instance($query)->call($this);
		}
		if ($this->datatype) {
			return $this->datatype->getAccessibleItems($this->data);
		}
		return false;
	}

	/**
	 * @param string $url
	 * @param array $data
	 * @return bool
	 */
	public function routeBase($url, $data = [])
	{
		$base = isset($data['base']) ? $data['base'] : '/' . $this->mnemocode . '/';
		if (Type::isCallable($base)) {
			$args = Callback::instance($base)->call($url, $this);
			if ($args) {
				$this->args = $args;
				return true;
			}
		}
		if (is_string($base) && strlen($base) > 0) {

			if ($url == $base) {
				return true;
			}

			if ($base[0] == '{') {
				if ($m = \TAO::regexp($base, $url)) {
					$this->args = $m;
					return true;
				}
			}
		}
		if (is_array($base) && isset($base['url_of'])) {
			if (isset($base['prefix'])) {
				$prefix = trim($base['prefix'], '/');
				if ($m = \TAO::regexp("{^/{$prefix}/(.+)$}", $url)) {
					$url = '/' . $m[1];
				} else {
					return false;
				}
			}
			if (isset($base['postfix'])) {
				$postfix = trim($base['postfix'], '/');
				if ($m = \TAO::regexp("{^(.+)/{$postfix}/$}", $url)) {
					$url = $m[1] . '/';
				} else {
					return false;
				}
			}
			$finder = isset($base['finder']) ? $base['finder'] : 'getItemByUrl';
			$item = \TAO::datatype($base['url_of'])->$finder($url);
			if ($item instanceof \Illuminate\Database\Eloquent\Builder) {
				$item = $item->first();
			}
			$this->args['item'] = $item;
			if ($item) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array $data
	 * @return $this
	 */
	public function route($data = [])
	{
		if (isset($data['url']) && !isset($data['base'])) {
			$data['base'] = $data['url'];
		}
		$this->data = $data;


		/**
		 * @var Request $request
		 */
		$request = app()->request();
		$url = $urlSrc = $request->getPathInfo();

		$this->data['pager_var'] = $var = isset($this->data['pager_var']) ? $this->data['pager_var'] : 'page';
		$this->data['base'] = isset($this->data['base']) ? $this->data['base'] : '/' . $this->mnemocode . '/';
		$this->data['per_page'] = $perPage = isset($this->data['per_page']) ? $this->data['per_page'] : 10;

		$page = 1;
		if ($m = \TAO::regexp('{^(.+)/' . $var . '-(\d+)/$}', $url)) {
			$url = $m[1] . '/';
			$page = (int)$m[2];
		}

		$this->data['page'] = $page;


		if ($this->routeBase($url, $this->data)) {
			$this->data['args'] = $this->args;
			if (!isset($this->data['pager_callback'])) {
				$this->data['pager_callback'] = function ($page) use ($url, $var) {
					if ($page > 1) {
						$url = rtrim($url, '/') . "/{$var}-{$page}/";
					}
					$qs = app()->request()->getQueryString();
					if (!empty($qs)) {
						$url .= "?{$qs}";
					}
					return $url;
				};
			}
			$data = $this->data;
			\Route::any($urlSrc, function () use ($data) {
				return $this->render($data);
			});
		}
		return $this;
	}

	/**
	 * @param $mode
	 * @return string
	 */
	public function findView($mode)
	{
		$factory = app(ViewFactory::class);
		$code = $this->mnemocode;
		$views = [];
		$views[] = "selector.{$code}.{$mode}";
		$views[] = "selector.{$code}";
		$views[] = "selector";
		foreach ($views as $view) {
			if ($factory->exists($view)) {
				return $view;
			}
		}
		return 'tao::selector';
	}

	/**
	 * @param string $mode
	 * @return string
	 */
	public function defaultTemplate($mode)
	{
		return $this->findView($mode);
	}

	public function beforeRender()
	{
		\Assets::setMeta('title', $this->data['title']);
	}

	/**
	 * @return string
	 */
	public function title()
	{
		$title = $this->title;
		if (isset($this->data['title'])) {
			$title = $this->data['title'];
		}
		$title = $title ? $title : get_class($this);
		return $title;
	}

	public function render($data)
	{
		$this->data = $data;
		$this->data['per_page'] = $perPage = isset($this->data['per_page']) ? $this->data['per_page'] : 10;
		$this->data['page'] = $page = isset($this->data['page']) ? $this->data['page'] : 1;
		$this->data['mode'] = $mode = isset($this->data['mode']) ? $this->data['mode'] : 'page';
		$this->data['row_mode'] = isset($this->data['row_mode']) ? $this->data['row_mode'] : 'teaser';
		$this->data['title'] = $this->title();
		$this->data['selector'] = $this;
		$query = $this->query();
		if (!$query) {
			return view('tao::no-query-for-selector', $this->data);
		}
		if (is_string($query) || $query instanceof Response) {
			return $query;
		}
		$count = $query->count();
		$numPages = ceil($count / $perPage);
		$rows = [];
		foreach ($query->limit($perPage)->offset(($page - 1) * $perPage)->get() as $row) {
			$rows[] = $row;
		}
		$this->data['count'] = $count;
		$this->data['numpages'] = $numPages;
		$this->data['rows'] = $rows;

		if ((0 < $this->data['count']) &&
			((1 > $this->data['page']) ||
			($this->data['numpages'] < $this->data['page']))) {
			return response(view('404'), 404);
		}

		if (isset($this->data['empty_block'])) {
			if (!view()->exists($this->data['empty_block'])) {
				unset($this->data['empty_block']);
			}
		}

		$template = isset($this->data['template']) ? $this->data['template'] : $this->defaultTemplate($mode);

		if ((0 === $this->data['count']) && isset($this->data['empty_template'])) {
			if (view()->exists($this->data['empty_template'])) {
				$template = $this->data['empty_template'];
			}
		}

		$this->beforeRender();

		if (isset($this->data['before_render'])) {
			$beforeRender = $this->data['before_render'];
			if ($this->datatype && is_string($beforeRender) && preg_match('{^[a-z][a-z0-9_]*$}i', $beforeRender)) {
				$beforeRender = [$this->datatype, $beforeRender];
			}
			Callback::instance($beforeRender)->call($this);
		}

		return view($template, $this->data);
	}
}