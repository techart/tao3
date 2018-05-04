<?php

namespace TaoTests;

use TAO\Navigation;

class NavigationTest extends TestCase
{
	public function testStructureNavigation()
	{
		$data = [
			'title' => 'Пункт меню',
			'url' => '/item/',
			'sub' => [
				[
					'url' => '/subitem1/',
					'title' => 'Подпункт меню 1',
				],
				[
					'url' => '/subitem2/',
					'title' => 'Подпункт меню 2',
				],
			]
		];
		$navigation = new Navigation($data);

		$this->assertEquals($data['title'], $navigation->title);
		$this->assertEquals($data['url'], $navigation->url);
		$links = $navigation->links();
		$this->assertEquals(count($data['sub']), count($links));
		$this->assertEquals($data['sub'][0]['url'], reset($links)->url);
		$this->assertEquals($data['sub'][0]['title'], reset($links)->title);
	}

	public function testCallbackInSubTest()
	{
		$data = [
			'title' => 'Пункт меню',
			'url' => '/item/',
			'sub' => [$this, 'callbackSubMethod']
		];
		$navigation = new Navigation($data);
		$links = $navigation->links();
		$expectedLinks = $this->callbackSubMethod();
		$this->assertEquals(count($data['sub']), count($links));
		$this->assertEquals($expectedLinks[0]['url'], reset($links)->url);
		$this->assertEquals($expectedLinks[0]['title'], reset($links)->title);
	}

	public function testCallbackIsSelected()
	{
		$isSelected = false;
		$data = [
			'title' => 'Пункт меню',
			'url' => '/item/',
			'sub' => [$this, 'callbackSubMethod'],
			'selected' => function () use (&$isSelected) {
				return $isSelected;
			}
		];
		$navigation = new Navigation($data);
		$this->assertEquals($isSelected, $navigation->isSelected());
		$isSelected = true;
		$this->assertEquals($isSelected, $navigation->isSelected());
	}

	// Utility methods
	public function callbackSubMethod()
	{
		return [
			[
				'title' => 'Subitem 1',
				'url' => '/subitem1/',
			],
			[
				'title' => 'Subitem 2',
				'url' => '/subitem2/',
			],
		];
	}
}