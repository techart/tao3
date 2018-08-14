<?php

namespace TaoTests\Fields;

use TAO\Fields\Field;
use TaoTests\TestCase;
use TaoTests\TestFields;
use TaoTests\Utils\Fields\DatatypeForStorableFields;
use TaoTests\Utils\SimpleDatatype;
use TaoTests\Utils\TAO\TaoTestDatatype;

class FieldTest extends TestCase
{
	use TestFields;

	protected function getDatatypes()
	{
		return [
			'tao_test' => TaoTestDatatype::class,
			'simple' => SimpleDatatype::class,
			'with_storable_fields' => \DatatypeForStorableFields::class
		];
	}


	public function testGettingCallableParam()
	{
		$data = [
			'type' => 'string',
			'param' => function ($arg = null) {
				return is_null($arg) ? 'paramValue' : $arg;
			},
			'param1' => 'param1Value',
		];
		/** @var Field $field */
		$field = $this->createField('test', $data);
		$this->assertEquals(call_user_func($data['param']), $field->callableParam('param', 'default'));
		$this->assertEquals($data['param1'], $field->callableParam('param1', 'default'));
		$this->assertEquals('default', $field->callableParam('param2', function () {
			return 'default';
		}));
		$this->assertEquals('default', $field->callableParam('param2', 'default'));

		// With args
		$this->assertEquals('argValue', $field->callableParam('param', null, ['argValue']));

		// With context
		/** @var Field $field2 */
		$field2 = $this->createField('test', [
			'type' => 'string',
			'param' => 'methodForContextCallbackParam'
		]);

		$arg = 10;
		$this->assertEquals($arg, $field2->callableParam('param', null, [$arg], $this));
	}

	public function testPrepareValue()
	{
		/** @var Field $field */
		$field = $this->createField('test', [
			'type' => 'string',
			'prepare_value' => function($value) {
				return $value .= '1';
			}
		]);
		$field->set('value');
		$this->assertEquals('value1', $field->value());
	}

	public function testIsEmpty()
	{
		$field = $this->createField('test', ['type' => 'string']);
		$this->assertTrue($field->isEmpty());
		$this->assertFalse($field->isNotEmpty());
		$field->set('value');
		$this->assertTrue($field->isNotEmpty());
		$this->assertFalse($field->isEmpty());

		// With callback
		$field = $this->createField('test', [
			'type' => 'string',
			'is_empty' => function ($field, $item) {
				return [$field, $item];
			}
		]);
		list($fieldFromCallback, $itemFromCallback) = $field->isEmpty();
		$this->assertEquals($fieldFromCallback, $field);
		$this->assertEquals($itemFromCallback, $field->item);
	}

	public function testRenderForAdmin()
	{
		$render = 'Admin content';
		$field = $this->createField('test', [
			'type' => 'string',
			'render_in_admin_list' => function () use ($render) {
				return $render;
			}
		]);
		$this->assertEquals($render, $field->renderForAdminList());

		$field = $this->createField('test', [
			'type' => 'string',
			'render_in_list' => function () use ($render) {
				return $render;
			}
		]);
		$this->assertEquals($render, $field->renderForAdminList());

		$field = $this->createField('test', [
			'type' => 'string',
			'render_in_admin_list' => function () use ($render) {
				return 'render_in_admin_list';
			},
			'render_in_list' => function () use ($render) {
				return 'render_in_list';
			}
		]);
		$this->assertEquals('render_in_admin_list', $field->renderForAdminList());

		$field = $this->createField('test', [
			'type' => 'string',
			'render_in_admin_list' => 'return50',
		], new TaoTestDatatype());
		$this->assertEquals(50, $field->renderForAdminList());

		// link_in_list
		$item = new SimpleDatatype();
		$field = $this->createField('test', [
			'type' => 'string',
			'render_in_list' => function () use ($render) {
				return $render;
			},
			'link_in_list' => '/test/{id}/'
		], $item);
		$item->save();
		$renderRes = $field->renderForAdminList();
		$this->assertContains('href="/test/' . $item->getKey() . '/"', $renderRes);
		$this->assertContains($render, $renderRes);
	}

	public function testCsvValue()
	{
		$items = [1 => 'Первый', 2 => 'Второй'];
		$field = $this->createField('test', [
			'type' => 'select',
			'items' => [1 => 'Первый', 2 => 'Второй']
		]);

		$field->set(1);
		$this->assertEquals($items[1], $field->csvValue());

		// With callback
		$field = $this->createField('test', [
			'type' => 'string',
			'csv_value' => function ($field) {
				return $field->value() . '1';
			}
		]);
		$field->set('test');
		$this->assertEquals('test1', $field->csvValue());

		// With callback from item
		$field = $this->createField('test', [
			'type' => 'string',
			'csv_value' => 'return50',
		], new TaoTestDatatype());
		$this->assertEquals(50, $field->csvValue());
	}

	public function testStorableField()
	{
		$item = new DatatypeForStorableFields();
		$fieldName = 'storable';

		$value = 'value';
		$item->field($fieldName)->set($value);
		$item->save();

		$this->assertTrue(\Schema::hasColumn('datatype_for_storable_fields', $fieldName));

		$item2 = DatatypeForStorableFields::find($item->getKey());
		$this->assertEquals($value, $item2->field($fieldName)->value());
	}

	public function testNonstorableField()
	{
		$item = new DatatypeForStorableFields();
		$fieldName = 'nonstorable';

		$value = 'value';
		$item->field($fieldName)->set($value);
		$item->save();

		$this->assertNull($item->$fieldName);
		$this->assertFalse(\Schema::hasColumn('datatype_for_storable_fields', $fieldName));

		$item2 = DatatypeForStorableFields::find($item->getKey());
		$this->assertEquals('', $item2->field($fieldName)->value());
	}

	//Utility methods
	public function methodForContextCallbackParam($arg)
	{
		return $arg;
	}
}
