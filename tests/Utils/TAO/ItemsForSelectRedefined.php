<?php

namespace TaoTests\Utils\TAO;

use TAO\ORM\Model;

class ItemsForSelectRedefined extends Model
{
	protected $items;

	public function fields()
	{
		return [];
	}

	public function setItems($items)
	{
		$this->items = $items;
	}

	public function itemsForSelect($args = false)
	{
		return $this->items;
	}
}