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

		$this->assertStringContainsString("src=\"$scriptUrl\"", \Assets::scripts());
		$this->assertStringContainsString("href=\"$stylesUrl\"", \Assets::styles());
	}

	public function testSupportExternalHttpsUrls()
	{
		$scriptUrl = 'https://example.ru/scripts.js';
		$stylesUrl = 'https://example.ru/styles.css';
		\Assets::useFile($scriptUrl);
		\Assets::useFile($stylesUrl);

		$this->assertStringContainsString("src=\"$scriptUrl\"", \Assets::scripts());
		$this->assertStringContainsString("href=\"$stylesUrl\"", \Assets::styles());
	}
}
