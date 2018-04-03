<?php

namespace TAO;

class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{
	public function to($path, $extra = [], $secure = null)
	{
		if (\TAO::isDatatypeExists('urlrewriter')) {
			$urlRewriter = \TAO::datatype('urlrewriter');
			$url = Urls::sortUrl($path);
			$path = $urlRewriter->getReplaced($url);
		}

		$url = parent::to($path, $extra, $secure);
		if ($this->hasTrailingSlash($path) && !$this->hasTrailingSlash($url)) {
			$url = $this->addTrailingSlash($url);
		}
		return $url;
	}

	protected function hasTrailingSlash($url)
	{
		return ends_with(parse_url($url, PHP_URL_PATH), '/');
	}

	protected function addTrailingSlash($url)
	{
		$urlParts = parse_url($url);
		$urlParts['path'] .= '/';
		return \URL::gather($urlParts);
	}

	/**
	 * Метод, обратный функции parse_url
	 *
	 * @param array $urlParts
	 * @return string
	 */
	public function gather($urlParts)
	{
		if (!isset($urlParts['path'])) {
			$urlParts['path'] = '/';
		}

		$url = $urlParts['scheme'] . '://';

		if(isset($urlParts['user']) && isset($urlParts['pass'])) {
			$url .= $urlParts['user'] . ':' . $urlParts['pass'] . '@';
		}

		$url .= $urlParts['host'];

		if(isset($urlParts['port'])) {
			$url .= ':' . $urlParts['port'];
		}

		$url .= '/' . ltrim($urlParts['path'], '/');

		if (isset($urlParts['query'])) {
			$url .= '?' . $urlParts['query'];
		}

		if (isset($urlParts['fragment'])) {
			$url .= '#' . $urlParts['fragment'];
		}

		return $url;
	}
}