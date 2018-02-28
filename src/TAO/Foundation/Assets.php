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

	public function renderMeta()
	{
		foreach (['title', 'description', 'keywords'] as $name) {
			if (!empty($value = $this->getParam("meta_{$name}"))) {
				$this->setMeta($name, $value);
			}
		}
		return view('tao::meta', array('meta' => $this->meta));
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

	public function useFile($file, $scope = false)
	{
		if (empty($file)) {
			return $this;
		}
		if (is_string($file)) {
			$file = array(
				'path' => $file,
			);
		}
		if (!isset($file['path'])) {
			return;
		}
		$path = $file['path'];
		if (!preg_match('{^http://}', $path) && !preg_match('{^/}', $path)) {
			$path = "/{$path}";
		}
		$file['path'] = $path;

		if (!$scope) {
			if (preg_match('{\.css$}i', $path)) {
				$scope = 'styles';
			} elseif (preg_match('{\.js$}i', $path)) {
				$scope = 'scripts';
			}
		}

		if ($scope) {
			if (!isset($this->scopes[$scope])) {
				$this->scopes[$scope] = array();
			}
			$this->scopes[$scope][$path] = $file;
		}
	}

	public function useBottomScript($file)
	{
		$this->useFile($file, 'bottom_scripts');
	}

	public function renderFile($file)
	{
		if (!isset($file['path'])) {
			return '';
		}
		$path = $file['path'];
		$type = false;
		if ($m = \TAO::regexp('{\.([a-z]+)$}i', $path)) {
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
		$scope = $this->scopes[$scope];
		foreach ($scope as $file) {
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

}