<?php

namespace TaoTests\Fields;

use TAO\Fields\FileField;
use TaoTests\TestCase;
use TaoTests\TestFields;

class FileFieldTest extends TestCase
{
	use TestFields;

	public function testDestinationFile()
	{
		/** @var FileField $field1 */
		$field1 = $this->createField('test', [
			'type' => 'upload',
			'file_name_template' => '{translit}.{ext}'
		]);

		$this->assertEquals('privet.txt', $field1->destinationFileName(['name' => 'привет', 'ext' => 'txt']));

		/** @var FileField $field1 */
		$field2 = $this->createField('test', [
			'type' => 'upload',
			'generate_file_name' => function ($info) {
				return 'file_' . $info->name . '.' . $info->ext;
			}
		]);

		$this->assertEquals('file_privet.txt', $field2->destinationFileName(['name' => 'privet', 'ext' => 'txt']));

	}

	public function testCheckUploadedFile()
	{
		$fileName = 'valid_file.txt';
		/** @var FileField $field */
		$field = $this->createField('test', [
			'type' => 'upload',
			'check_uploaded_file' => function ($file) use ($fileName) {
				return $file->name == $fileName;
			}
		]);
		$fakeFile = new \stdClass();
		$fakeFile->name = $fileName;
		$info = [];
		$this->assertTrue($field->checkUploadedFile($fakeFile, $info));
		$fakeFile->name = 'false';
		$this->assertFalse($field->checkUploadedFile($fakeFile, $info));

		// Without callback
		$field2 = $this->createField('test', [
			'type' => 'upload',
		]);
		$this->assertTrue($field2->checkUploadedFile($fakeFile, $info));
	}
}