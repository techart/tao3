<?php
namespace TAO\Components\Sitemap;

class Controller extends \TAO\Controller
{
	/**
	 * @var Manager
	 */
	protected $sitemapManager;

	public function __construct()
	{
		$this->sitemapManager = app('sitemap.manager');
	}

	public function generate()
	{
		$this->sitemapManager->registerDefaultSources();
		return $this->sitemapManager->render((int)config('sitemap.cache.lifetime'));
	}
}