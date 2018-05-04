<?php

namespace TaoTests\Utils\TAO;

use TAO\ORM\Model;

class TaoTestDatatype extends Model
{
	public function fields()
	{
		return [];
	}

	public function return50()
	{
		return 50;
	}
}