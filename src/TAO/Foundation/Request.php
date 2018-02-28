<?php

namespace TAO\Foundation;

class Request extends \Illuminate\Http\Request
{
	public function url()
	{
		return preg_replace('/\?.*/', '', $this->getUri());
	}

	public function replaceUri($uri, $get)
	{
		$query = http_build_query($get);
		if (!empty($query)) {
			$query = str_replace('%5B', '[', $query);
			$query = str_replace('%5D', ']', $query);
			$uri .= "?{$query}";
		}
		$this->requestUri = $uri;
		$this->query->replace($get);
		$this->pathInfo = $this->preparePathInfo();
		$this->server->set('REQUEST_URI', $uri);
		$this->server->set('QUERY_STRING', $query);
		$this->overrideGlobals();
	}
}