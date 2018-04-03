<?php

namespace TAO\ORM\Traits;

trait Sitemap
{
	public function inSitemap()
	{
		return true;
	}

	public function sitemapLinks()
	{
		$links = $this->sitemapExtraLinks();
		foreach ($this->sitemapBuilder()->get() as $item) {
			$links[] = $item->sitemapLinkData();
		}
		return $links;
	}

	protected function sitemapExtraLinks()
	{
		return [];
	}

	protected function sitemapLinkData()
	{
		return array_filter([
			'loc' => $this->sitemapUrl(),
			'lastmod' => $this->sitemapLastmod(),
			'changefreq' => $this->sitemapChangefreq(),
			'priority' => $this->sitemapPriority(),
		]);
	}

	protected function sitemapBuilder()
	{
		return $this->getAccessibleItems();
	}

	protected function sitemapUrl()
	{
		return $this->url();
	}

	protected function sitemapLastmod()
	{
		$updatedColumn = self::UPDATED_AT;
		return $this->$updatedColumn;
	}

	protected function sitemapChangefreq()
	{
	}

	protected function sitemapPriority()
	{
	}
}