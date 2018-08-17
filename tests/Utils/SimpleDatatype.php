<?php

namespace TaoTests\Utils;

use TAO\ORM\Model;

class SimpleDatatype extends Model
{
	protected $_fields = [
		'title' => [
			'type' => 'string(250)'
		]
	];

	public function fields()
	{
		return $this->_fields;
	}

	public function addField($name, $data)
	{
		$this->processedFields[$name] = $data;
		$this->_fields[$name] = $data;
	}

	public function addFields($fields)
	{
		foreach ($fields as $fieldName => $fieldData) {
			$this->addField($fieldName, $fieldData);
		}
	}
}
