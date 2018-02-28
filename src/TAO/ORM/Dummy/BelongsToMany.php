<?php

namespace TAO\ORM\Dummy;

class BelongsToMany
{
	protected $values = [];

	public function attach($id)
	{
		$this->values[$id] = true;
	}

	public function detach($id)
	{
		unset($this->values[$id]);
	}

	public function allRelatedIds()
	{
		return array_keys($this->values);
	}

	public function sync($ids, $withDetaching = true)
	{
		if ($withDetaching) {
			$this->values = [];
		}
		foreach ($ids as $id) {
			$this->attach($id);
		}
	}
}


