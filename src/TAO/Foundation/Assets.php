<?php

namespace TAO\Foundation;

class Assets
{
	protected $meta = array();
	protected $scopes = array();
	protected $textBlocks = array();
	protected $urlRewrited;
	protected $originalRequest;
	protected $needRedirectTo;
	protected $needRedirectType;
	protected $vars = array();

	public function init()
	{
		if (isset($_SERVER, $_SERVER['HTTP_HOST'])) {
			$this->meta['title'] = config('tao.meta.title', config('app.name', $_SERVER['HTTP_HOST']));
		}
	}

	public function setMeta($name, $value)
	{
		$this->meta[$name] = $value;
	}

	public function setMetas($metas)
	{
		foreach ($metas as $metaName => $metaValue) {
			$this->setMeta($metaName, $metaValue);
		}
	}

	public function setVar($name, $value)
	{
		$this->vars[$name] = $value;
	}

	public function getVar($name, $default = null)
	{
		return $this->vars[$name] ?? $default;
	}

	public function renderMeta()
	{
		foreach (['title', 'description', 'keywords'] as $name) {
			if (!empty($value = $this->getParam("meta_{$name}"))) {
				$this->setMeta($name, $value);
			}
		}
		return view('meta', array('meta' => $this->meta));
	}
	
	/**
	 * Возвращает массив текущих мета-тегов
	 *
	 * @return array
	 */
	public function getMetas() {
		return $this->meta;
	}

	public function getParam($name, $default = null)
	{
		if (is_object($item = $this->urlRewrited)) {
			if (isset($item[$name])) {
				$value = trim($item[$name]);
				if (!empty($value)) {
					return $value;
				}
			}
		}
		return $default;
	}

	public function meta()
	{
		return $this->renderMeta();
	}

	/**
	 * @param string $path
	 * @param array|string $fileParams
	 * @return $this
	 */
	public function useFile($path, $fileParams = [])
	{
		if (empty($path)) {
			return $this;
		}

		// Отрезаем timestamp из пути, если таковой там есть и ипользуем в проверках
		$path_without_timestamp = \explode('?', $path)[0];
		$scope = '';

		if (is_string($fileParams)) {
			$scope = $fileParams;
			$fileParams = [];
		} else if (isset($fileParams['scope'])) {
			$scope = $fileParams['scope'];
			unset($fileParams['scope']);
		}

		if (!preg_match('{^http(s)?://}', $path_without_timestamp) && !preg_match('{^/}', $path_without_timestamp)) {
			$path = "/{$path}";
		}

		$fileParams['path'] = $path;

		if (!$scope) {
			if (preg_match('{\.css$}i', $path_without_timestamp)) {
				$scope = 'styles';
				$fileParams['type'] = 'css';
			} elseif (preg_match('{\.js$}i', $path_without_timestamp)) {
				$scope = 'scripts';
				$fileParams['type'] = 'js';
			}
		}

		if ($scope) {
			if (!isset($this->scopes[$scope])) {
				$this->scopes[$scope] = array();
			}

			$this->scopes[$scope][$path] = $fileParams;
		}

		return $this;
	}

	public function useScript($file, $params = [])
	{
		if (!is_array($params)) {
			$params = [$params];
		}
		$this->useFile($file, array_merge($params, [
			'scope' => 'scripts',
			'type' => 'js'
		]));
	}

	public function useBottomScript($file, $params = [])
	{
		if (!is_array($params)) {
			$params = [$params];
		}
		$this->useFile($file, array_merge($params, [
			'scope' => 'bottom_scripts',
			'type' => 'js'
		]));
	}

	public function useStyle($file, $params = [])
	{
		if (!is_array($params)) {
			$params = [$params];
		}
		$this->useFile($file, array_merge($params, [
			'scope' => 'styles',
			'type' => 'css'
		]));
	}

	public function useScss($files, $name = false)
	{
		if ($url = app('scss')->make($files, $name)) {
			$this->useStyle($url);
		}
	}

	public function renderFile($file)
	{
		if (!isset($file['path'])) {
			return '';
		}
		$path = $file['path'];
		$type = $file['type'] ?? false;
		if (!$type && $m = \TAO::regexp('{\.([a-z]+)$}i', $path)) {
			$type = strtolower($m[1]);
		}
		$time = '';
		$fpath = $path[0] == '/' ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $path : false;
		if ($fpath && is_file($fpath)) {
			$time = filemtime($fpath);
		}
		$url = '';
		$tag = '';
		if ($type == 'js' || $type == 'css') {
			$url = config("tao.{$type}url", '%path%?%time%');
			$url = str_replace('%path%', $path, $url);
			$url = str_replace('%time%', $time, $url);
			$url = rtrim($url, '?');
		}

		if (!empty($url)) {
			if ($type == 'js') {
				$tag = config("tao.jstag", '<script src="%url%"></script>' . "\n");
			}
			if ($type == 'css') {
				$tag = config("tao.csstag", '<link href="%url%" rel="stylesheet" media="screen">' . "\n");
			}
			if (!empty($tag)) {
				$tag = str_replace('%url%', $url, $tag);
				return $tag;
			}
		}
		return '';
	}

	public function addLine($block, $line)
	{
		if (!isset($this->textBlocks[$block])) {
			$this->textBlocks[$block] = '';
		}
		$this->textBlocks[$block] .= "\n{$line}";
	}

	public function addBottomLine($line)
	{
		$this->addLine('bottom', $line);
	}

	public function textBlock($block)
	{
		return isset($this->textBlocks[$block]) ? $this->textBlocks[$block] : '';
	}

	public function block($scope)
	{
		if (!isset($this->scopes[$scope])) {
			return '';
		}
		$html = '';
		foreach ($this->sortFiles($this->scopes[$scope]) as $file) {
			$html .= $this->renderFile($file);
		}
		return $html;
	}

	public function bottomScripts()
	{
		return $this->block('bottom_scripts');
	}

	public function scripts()
	{
		return $this->block('scripts');
	}

	public function styles()
	{
		return $this->block('styles');
	}

	public function useLayout($name)
	{
		\TAO::useLayout($name);
	}

	public function noLayout()
	{
		\TAO::useLayout('layouts.empty');
	}

	public function frontend($name = false)
	{
		return \TAO::frontend($name);
	}

	public function useFrontendStyle($name, $params = [])
	{
		$this->frontend()->useStyle($name, $params);
	}

	public function useFrontendScript($name, $params = [])
	{
		$this->frontend()->useScript($name, $params);
	}

	public function urlRewrited($item = false, $request = false)
	{
		if ($item) {
			$this->urlRewrited = $item;
		}
		if (is_object($request)) {
			$this->originalRequest = clone $request;
		}
		return $this->urlRewrited;
	}

	public function getOriginalRequest()
	{
		return $this->originalRequest;
	}

	public function needRedirect($to, $type)
	{
		$this->needRedirectTo = $to;
		$this->needRedirectType = $type;
	}

	public function redirectIfNeed()
	{
		if (!empty($this->needRedirectTo)) {
			return redirect($this->needRedirectTo, $this->needRedirectType);
		}
	}

	/**
	 * @param array $filesList
	 * @return array
	 */
	protected function sortFiles($filesList)
	{
		$filesList = $this->assignAutoWeight($filesList);
		uasort($filesList, function ($fileParams1, $fileParams2) {
			$weight1 = $fileParams1['weight'] ?? 0;
			$weight2 = $fileParams2['weight'] ?? 0;
			return $weight1 > $weight2;
		});
		return $filesList;
	}

	protected function assignAutoWeight($filesList)
	{
		$autoWeight = 0.0001;
		foreach ($filesList as $key => $fileParams) {
			if (!isset($filesList[$key]['weight'])) {
				$filesList[$key]['weight'] = $autoWeight;
				$autoWeight += $autoWeight;
			}
		}
		return $filesList;
	}
}
