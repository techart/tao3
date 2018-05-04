<?php

namespace TaoTests;

use TaoTests\Utils\SimpleDatatype;
use TaoTests\Utils\TAO\ItemsForSelectRedefined;

class TAO extends TestCase
{
	protected function getDatatypes()
	{
		return [
			'simple' => SimpleDatatype::class,
			'itemsForSelectRedefined' => ItemsForSelectRedefined::class,
		];
	}

	public function testItemsForSelectForArray()
	{
		$items = [0 => 'Первая запись', 1 => 'Вторая запись'];
		$this->assertEquals($items, \TAO::itemsForSelect($items));
	}

	public function testItemsForSelectForCallback()
	{
		$items = [0 => 'Первая запись', 1 => 'Вторая запись'];
		$callback = function () use ($items) {
			return $items;
		};
		$this->assertEquals($items, \TAO::itemsForSelect($callback));
	}

	public function testItemsForSelectFromDatatype()
	{
		$count = 10;
		factory(SimpleDatatype::class, $count, function () {
			return [
				'title' => 'Заголовок'
			];
		})->create();

		$items = \TAO::itemsForSelect('datatype:simple');
		$this->assertEquals($count, count($items));
	}

	public function testRedefinedItemsForSelectInDatatype()
	{
		$items = [0 => 'Первая запись', 1 => 'Вторая запись'];
		/** @var ItemsForSelectRedefined $datatype */
		$datatype = \TAO::datatype('itemsForSelectRedefined');
		$datatype->setItems($items);

		$this->assertEquals($items, \TAO::itemsForSelect('datatype:itemsForSelectRedefined'));
	}

	public function testParamsInCallbackForItemsForSelect()
	{
		$count = 10;
		factory(SimpleDatatype::class, $count, function() {
			return [
				'title' => 'Заголовок'
			];
		})->create();

		$items = \TAO::itemsForSelect('datatype:simple/0=default&100000=last&assoc=assoc');
		$this->assertEquals($count + 2, count($items));
		$this->assertEquals('default', $items[0]);
		$this->assertEquals('last', $items[100000]);
		$this->assertFalse(isset($items['assoc']));
	}
}