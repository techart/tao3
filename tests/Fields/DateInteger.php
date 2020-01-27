<?php
//
//namespace TaoTests\Fields;
//
//use TaoTests\TestCase;
//use TaoTests\TestFields;
//
//class DateInteger extends TestCase
//{
//	use TestFields {
//		createField as baseCreateField;
//	}

//	public function testRenderDateIntegerField()
//	{
//		try {
//			(string)$this->createField();
//		} catch (\ErrorException $e) {
//			$this->fail($e->getMessage());
//		}
//
//		// Prevent risky test warning
//		$this->assertTrue(true);
//	}
//
//	/**
//	 * @param array $data
//	 * @return \TAO\Fields\Type\Image
//	 */
//	protected function createField($data = [])
//	{
//		$data['type'] = 'date_integer';
//		return $this->baseCreateField('image', $data);
//	}
//}
