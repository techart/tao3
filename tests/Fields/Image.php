<?php

namespace TaoTests\Fields;

use TaoTests\TestCase;
use TaoTests\TestFields;

class Image extends TestCase
{
	use TestFields {
		createField as baseCreateField;
	}

	public function testRenderForAdminList()
	{
		// With callback
		$render = 'Admin content';
		$field = $this->createField([
			'render_in_list' => function () use ($render) {
				return $render;
			}
		]);
		$this->assertEquals($render, $field->renderForAdminList());
	}

	/**
	 * @param array $data
	 * @return \TAO\Fields\Type\Image
	 */
	protected function createField($data = [])
	{
		$data['type'] = 'image';
		return $this->baseCreateField('image', $data);
	}
}