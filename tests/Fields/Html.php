<?php

namespace TaoTests\Fields;

use TaoTests\TestCase;
use TaoTests\TestFields;

class Html extends TestCase
{
	use TestFields {
		createField as baseCreateField;
	}

	public function testMergeConfig()
	{
		$testConfig = ['test' => 'test'];
		$fieldConfig = config('html-editor', $testConfig);
		foreach ($fieldConfig as $varKey => $varValue) {
			if (is_string($varValue)) {
				break;
			}
		}
		if (is_null($varKey)) {
			config(['html-editor' => $testConfig]);
			$varKey = array_first(array_keys($testConfig));
		}
		$checkValue = 'test2';
		$field = $this->createField([
			'editor_config' => [
				$varKey => $checkValue,
			],
		]);

		$editorConfig = $field->editorConfig();
		$this->assertTrue(isset($editorConfig[$varKey]) && $editorConfig[$varKey] == $checkValue);
	}

	/**
	 * @param array $data
	 * @return \TAO\Fields\Type\Html
	 */
	protected function createField($data = [])
	{
		$data['type'] = 'html';
		return $this->baseCreateField('image', $data);
	}
}
