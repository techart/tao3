<?php

namespace TaoTests\Fields;

use TaoTests\TestCase;
use TaoTests\TestFields;

class Coordinates extends TestCase
{
	use TestFields;

	public function testSetValueByLatLngMethods()
	{
		$fieldName = 'coords';
		$item = $this->createItemWithCoordsField($fieldName);

		$lat = 52.3;
		$lng = 37.5;

		$item->field($fieldName)->setLat($lat);
		$item->field($fieldName)->setLng($lng);

		$this->assertEquals($lat, $item->field($fieldName)->lat());
		$this->assertEquals($lng, $item->field($fieldName)->lng());
		$this->assertEquals($lat, $item->getAttribute($item->field($fieldName)->latColumnName()));
		$this->assertEquals($lng, $item->getAttribute($item->field($fieldName)->lngColumnName()));
	}

	public function testSetNumericArrayValueBySetMethod()
	{
		$fieldName = 'coords';
		$item = $this->createItemWithCoordsField($fieldName);

		$lat = 52.3;
		$lng = 37.5;

		$item->field($fieldName)->set([$lat, $lng]);

		$this->assertEquals($lat, $item->field($fieldName)->lat());
		$this->assertEquals($lng, $item->field($fieldName)->lng());
		$this->assertEquals($lat, $item->getAttribute($item->field($fieldName)->latColumnName()));
		$this->assertEquals($lng, $item->getAttribute($item->field($fieldName)->lngColumnName()));
	}

	public function testSetAssocArrayValueBySetMethod()
	{
		$fieldName = 'coords';
		$item = $this->createItemWithCoordsField($fieldName);

		$lat = 52.3;
		$lng = 37.5;

		$item->field($fieldName)->set(['lng' => $lng, 'lat' => $lat]);

		$this->assertEquals($lat, $item->field($fieldName)->lat());
		$this->assertEquals($lng, $item->field($fieldName)->lng());
		$this->assertEquals($lat, $item->getAttribute($item->field($fieldName)->latColumnName()));
		$this->assertEquals($lng, $item->getAttribute($item->field($fieldName)->lngColumnName()));
	}

	public function testSetStringValueBySetMethod()
	{
		$fieldName = 'coords';
		$item = $this->createItemWithCoordsField($fieldName);

		$lat = 52.3;
		$lng = 37.5;

		$value = number_format($lat, 2) . ', ' . number_format($lng, 2);
		$item->field($fieldName)->set($value);

		$this->assertEquals($lat, $item->field($fieldName)->lat());
		$this->assertEquals($lng, $item->field($fieldName)->lng());
		$this->assertEquals($lat, $item->getAttribute($item->field($fieldName)->latColumnName()));
		$this->assertEquals($lng, $item->getAttribute($item->field($fieldName)->lngColumnName()));
	}

	public function testIncorrectDecimalSeparator()
	{
		$fieldName = 'coords';
		$item = $this->createItemWithCoordsField($fieldName);

		$incorrectLat = ' 52,3 ';
		$incorrectLng = ' 37,5 ';

		$correctLat = 52.3;
		$correctLng = 37.5;

		$item->field($fieldName)->setLat($incorrectLat);
		$item->field($fieldName)->setLng($incorrectLng);

		$this->assertEquals($correctLat, $item->field($fieldName)->lat());
		$this->assertEquals($correctLng, $item->field($fieldName)->lng());
		$this->assertEquals($correctLat, $item->getAttribute($item->field($fieldName)->latColumnName()));
		$this->assertEquals($correctLng, $item->getAttribute($item->field($fieldName)->lngColumnName()));

		$item->field($fieldName)->set([$incorrectLat, $incorrectLng]);

		$this->assertEquals($correctLat, $item->field($fieldName)->lat());
		$this->assertEquals($correctLng, $item->field($fieldName)->lng());
		$this->assertEquals($correctLat, $item->getAttribute($item->field($fieldName)->latColumnName()));
		$this->assertEquals($correctLng, $item->getAttribute($item->field($fieldName)->lngColumnName()));
	}

	protected function createItemWithCoordsField($fieldName = 'coords')
	{
		$item = $this->createDefaultFieldItem();
		$item->addField($fieldName, ['type' => 'coordinates']);
		return $item;
	}
}
