<?php

namespace TaoTests\Components;

use Spatie\Sitemap\SitemapServiceProvider;
use TAO\Components\Sitemap\Manager;
use TaoTests\Components\SitemapUtils\Datatype;
use TaoTests\Components\SitemapUtils\Source;
use TaoTests\TestCase;

class Sitemap extends TestCase
{
	public function testSimpleDynamicGeneration()
	{
		/** @var Manager $manager */
		$manager = app('sitemap.manager');
		$response = $manager->generateDynamically();

		$this->assertStringContainsString('<loc>http://localhost/</loc>', $response->getContent());
	}

	public function testAddLinksWithoutSource()
	{
		/** @var Manager $manager */
		$manager = app('sitemap.manager');
		$link1 = 'http://example.com/test1/';
		$link2 = 'http://example.com/test2/';
		$manager->addLinks([$link1, $link2]);
		$content = $manager->generateDynamically()->getContent();


		$this->assertStringContainsString('<loc>http://localhost/</loc>', $content);
		$this->assertStringContainsString("<loc>$link1</loc>", $content);
		$this->assertStringContainsString("<loc>$link2</loc>", $content);
	}

	public function testAddLinksFromSource()
	{
		/** @var Manager $manager */
		$manager = app('sitemap.manager');
		$manager->registerSource(Source::class);

		$link1 = 'http://example.com/test1/';
		$link2 = 'http://example.com/test2/';

		Source::addLinks([$link1, $link2]);
		$content = $manager->generateDynamically()->getContent();

		$this->assertStringContainsString('<loc>http://localhost/</loc>', $content);
		$this->assertStringContainsString("<loc>$link1</loc>", $content);
		$this->assertStringContainsString("<loc>$link2</loc>", $content);
	}

	public function testAddLinksFromDatatype()
	{
		/** @var Manager $manager */
		$manager = app('sitemap.manager');

		$link1 = '/test1/';
		$link2 = '/test2/';

		$page1 = new Datatype();
		$page1->isactive = 1;
		$page1->url = $link1;
		$page1->save();

		$page2 = new Datatype();
		$page2->isactive = 1;
		$page2->url = $link2;
		$page2->save();

		$content = $manager->generateDynamically()->getContent();
		$domain = config('app.url');

		$this->assertStringContainsString('<loc>http://localhost/</loc>', $content);
		$this->assertStringContainsString("<loc>$domain$link1</loc>", $content);
		$this->assertStringContainsString("<loc>$domain$link2</loc>", $content);
	}

	public function testCache()
	{
		/** @var Manager $manager */
		$manager = app('sitemap.manager');

		$link1 = 'http://example.com/test1/';
		$link2 = 'http://example.com/test2/';

		$cacheLifeTime = 10000;

		$content1 = $manager->generateDynamically($cacheLifeTime)->getContent();

		$manager->addLinks([$link1, $link2]);

		$content2 = $manager->generateDynamically($cacheLifeTime)->getContent();
		$content3 = $manager->generateDynamically()->getContent();

		$this->assertEquals($content1, $content2);
		$this->assertStringNotContainsString("<loc>$link1</loc>", $content1);
		$this->assertStringContainsString("<loc>$link1</loc>", $content3);
		$this->assertStringContainsString("<loc>$link2</loc>", $content3);
	}

	public function testHost()
	{
		/** @var Manager $manager */
		$manager = app('sitemap.manager');

		$link1 = '/test1/';
		$link2 = '/test2/';
		$domain = 'http://example.com';

		$manager->addLinks([$link1, $link2]);

		$content = $manager->generateDynamically(0, 'http://example.com')->getContent();

		$this->assertStringContainsString("<loc>$domain/</loc>", $content);
		$this->assertStringContainsString("<loc>$domain$link1</loc>", $content);
		$this->assertStringContainsString("<loc>$domain$link2</loc>", $content);
	}

	protected function resolveApplication()
	{
		$app = parent::resolveApplication();
		$app->addDeferredServices(['sitemap' => SitemapServiceProvider::class]);
		return $app;
	}

	protected function getDatatypes()
	{
		return ['sitemap' => Datatype::class];
	}

	protected function setUp(): void
	{
		parent::setUp(); // TODO: Change the autogenerated stub
		\Cache::clear();
	}
}
