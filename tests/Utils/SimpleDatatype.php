<?php
namespace TaoTests\Utils;

use TAO\ORM\Model;

class SimpleDatatype extends Model
{
	public function fields()
	{
		return [
			'title' => [
				'type' => 'string(250)'
			]
		];
	}
}