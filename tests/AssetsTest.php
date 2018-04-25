<?php

namespace TaoTests;

class AssetsTest extends TestCase
{
	public function testSupportExternalUrls()
	{
		$scriptUrl = 'http://example.ru/scripts.js';
		$stylesUrl = 'http://example.ru/styles.css';
		\Assets::useFile($scriptUrl);
		\Assets::useFile($stylesUrl);

		$this->assertContains("src=\"$scriptUrl\"", \Assets::scripts());
		$this->assertContains("href=\"$stylesUrl\"", \Assets::styles());
	}

	public function testSupportExternalHttpsUrls()
	{
		$scriptUrl = 'https://example.ru/scripts.js';
		$stylesUrl = 'https://example.ru/styles.css';
		\Assets::useFile($scriptUrl);
		\Assets::useFile($stylesUrl);

		$this->assertContains("src=\"$scriptUrl\"", \Assets::scripts());
		$this->assertContains("href=\"$stylesUrl\"", \Assets::styles());
	}
}