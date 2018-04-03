<?php
namespace TAO\Console\Commands;

use Illuminate\Console\Command;
use TAO\Components\Sitemap\Manager;

class GenerateSitemap extends Command
{
	protected $signature = 'sitemap:generate';

	protected $description = 'Generate sitemap file';

	public function handle()
	{
		/** @var Manager $sitemapManager */
		$sitemapManager = app('sitemap.manager');
		$sitemapManager->registerDefaultSources();
		echo $sitemapManager->store(config('sitemap.limit', 50000));
	}
}