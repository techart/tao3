<?php

namespace TaoTests\Components\Sitemap;

use TAO\Components\Sitemap\SitemapSource;

class Source implements SitemapSource
{
	protected static $links;

	public static function addLinks($links)
	{
		self::$links = $links;
	}

	public function sitemapLinks()
	{
		return self::$links;
	}
}
