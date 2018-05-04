<?php

namespace TaoTests;

use TAO\Fields\Field;
use TAO\ORM\Model;
use TaoTests\Utils\SimpleDatatype;

trait TestFields
{
	/**
	 * @param string $name
	 * @param array $data
	 * @param Model|null $item
	 * @param string|null $mode
	 * @return Field
	 */
	public function createField($name, $data, $item = null, $mode = null)
	{
		if (is_null($item)) {
			$item = $this->createDefaultFieldItem();
		}

		return app('tao.fields')->create($name, $data, $item, $mode);

	}

	public function createDefaultFieldItem()
	{
		return new SimpleDatatype();
	}
}