<?php

namespace TAO\Components\Sitemap;


class MainPageSource implements SitemapSource
{
	public function sitemapLinks()
	{
		return [
			array_filter([
				'loc' => $this->url(),
				'lastmod' => $this->lastmod(),
				'changefreq' => $this->changefreq(),
				'priority' => $this->priority(),
			])
		];
	}

	/**
	 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
	 */
	protected function url()
	{
		return $this->config('url');
	}

	protected function config($name)
	{
		return config("sitemap.main_page.$name");
	}

	/**
	 * @return false|string
	 */
	protected function lastmod()
	{
		return $this->config('lastmod');
	}

	/**
	 * @return string
	 */
	protected function changefreq()
	{
		return $this->config('changefreq');
	}

	/**
	 * @return int
	 */
	protected function priority()
	{
		return $this->config('priority');
	}


}
