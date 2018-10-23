<?php

namespace TaoTests\Fields;

use TaoTests\TestCase;
use TaoTests\TestFields;

class Multilink extends TestCase
{
	use TestFields {
		createField as baseCreateField;
	}

	public function testMultilinkDefaultValue()
	{
		$ids = [1, 3];
		/** @var \TAO\Fields\Type\Multilink $field */
		$field = $this->createField(['default' => $ids]);
		$field->setupDefault();
		$this->assertEquals(array_values($field->value()), $ids);
	}

	/**
	 * @param array $data
	 * @return \TAO\Fields\Type\Upload
	 */
	protected function createField($data = [])
	{
		$data['type'] = 'multilink';
		return $this->baseCreateField('multilink', $data);
	}
}
