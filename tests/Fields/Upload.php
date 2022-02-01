<?php

namespace TaoTests\Fields;

use TaoTests\TestCase;
use TaoTests\TestFields;

class Upload extends TestCase
{
	use TestFields {
		createField as baseCreateField;
	}

	public function testRenderForAdminList()
	{
		$field = $this->createField();
		$this->assertStringContainsString('href=', $field->renderForAdminList());

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
	 * @return \TAO\Fields\Type\Upload
	 */
	protected function createField($data = [])
	{
		$data['type'] = 'upload';
		return $this->baseCreateField('upload', $data);
	}
}
