<?php

namespace TaoTests\Utils\Fields;

use TAO\ORM\Model;

class DatatypeForStorableFields extends Model
{
	public function fields()
	{
		return [
			'storable' => [
				'type' => 'string(250)'
			],
			'nonstorable' => [
				'type' => 'string(250)',
				'storable' => false
			],
		];
	}

	public function addField($name, $data)
	{
		$this->processedFields[$name] = $data;
	}
}
