<?php

namespace TAO\Foundation;

use TAO\Foundation\HTTP\MIME;
use TAO\Text\StringTemplate;

class HTTP
{
	protected $auth = false;
	protected $follow = true;
	protected $curl = false;
	protected $url = false;
	protected $headers = [];
	protected $options = [];

	public function auth($login, $password)
	{
		$this->auth = [
			'login' => $login,
			'password' => $password,
		];
		return $this;
	}

	public function follow($value = true)
	{
		$this->follow = $value;
		return $this;
	}

	public function noFollow()
	{
		$this->follow = false;
		return $this;
	}

	public function setHeader($name, $value)
	{
		$this->headers[$name] = $value;
	}

	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
	}

	protected function prepare($url)
	{
		$this->url = $url;
		$this->curl = new \Curl\Curl();

		if ($auth = $this->auth) {
			$this->curl->setBasicAuthentication($auth['login'], $auth['password']);
		}

		$this->curl->setOpt(CURLOPT_FOLLOWLOCATION, $this->follow);

		foreach ($this->options as $name => $value) {
			$this->curl->setOpt($name, $value);
		}

		foreach ($this->headers as $name => $value) {
			$this->curl->setHeader($name, $value);
		}

		return $this;
	}

	public function get($url)
	{
		$this->prepare($url);
		$this->curl->get($url);
		return $this;
	}

	public function post($url, $data = [])
	{
		$this->prepare($url);
		$this->curl->post($url, $data);
		return $this;
	}

	public function getBody($url, $post = false, $data = [])
	{
		if ($post) {
			$this->post($url, $data);
		} else {
			$this->get($url);
		}
		if ($this->curl->http_status_code != 200) {
			throw new \TAO\Exception\HTTP($this->curl);
		}
		return $this->curl->response;
	}

	public function getJSON($url, $post = false, $data = [])
	{
		return json_decode($this->getBody($url, $post, $data));
	}

	public function getResponseFilename()
	{
		if ($cd = $this->curl->getResponseHeaders('content-disposition')) {
			if ($m = \TAO::regexp('{filename=(.+)$}', $cd)) {
				return trim($m[1]);
			}
		}
		$url = preg_replace('{^https?://}', '', $this->url);
		$url = preg_replace('{^[^/]+}', '', $url);
		if ($m = \TAO::regexp('{/([^/]+)\.([a-z0-9]{1,4})$}', $url)) {
			return $m[1] . '.' . $m[2];
		}
		$mime = 'application/octet-stream';
		if ($ct = $this->curl->getResponseHeaders('content-type')) {
			$mime = preg_replace('{;.*$}', '', $ct);
		}
		$ext = MIME::getExtension($mime);
		return md5($url) . ".{$ext}";
	}

	public function saveFile($url, $dir, $nameTpl = '{name}.{ext}')
	{
		$this->get($url);
		if ($this->curl->http_status_code != 200) {
			throw new \TAO\Exception\HTTP($this->curl);
		}
		$name = $this->getResponseFilename();
		$ext = 'bin';
		if ($m = \TAO::regexp('{(.+)\.([a-z0-9]+)}', $name)) {
			$name = strtolower(trim($m[1]));
			$ext = strtolower(trim($m[2]));
		}
		$body = $this->curl->response;
		$newName = StringTemplate::process($nameTpl, [
			'name' => $name,
			'ext' => $ext,
		]);
		$dir = rtrim($dir, '/');
		if (is_dir($dir)) {
			file_put_contents("{$dir}/{$newName}", $body);
		} else {
			if (!\Storage::exists($dir)) {
				\Storage::makeDirectory($dir);
			}
			\Storage::put("{$dir}/{$newName}", $body);
		}

		return "{$dir}/{$newName}";
	}

	public function __call($method, $args)
	{
		return call_user_func_array([$this->curl, $method], $args);
	}
}