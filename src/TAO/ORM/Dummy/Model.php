<?php

namespace TAO\ORM\Dummy;

class Model implements \ArrayAccess
{
	protected $attributes = [];
	protected $belongs = [];
	public $code;

	public function getTable()
	{
		if ($this->code) {
			return ":dummy_{$this->code}";
		}
		return ':dummy';
	}

	public function getForeignKey()
	{
		if ($this->code) {
			return "{$this->code}_id";
		}
		return 'id';
	}

	public function getKey()
	{
		return isset($this->attributes['id']) ? (int)$this->attributes['id'] : 0;
	}

	public function belongsToMany($class, $relations, $key1, $key2)
	{
		if (!isset($this->belongs[$relations])) {
			$this->belongs[$relations] = new BelongsToMany();
		}
		return $this->belongs[$relations];
	}

	public function getAttributeValue($name)
	{
		return $this[$name] ?? null;
	}

	public function offsetExists($offset)
	{
		return isset($this->attributes[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->attributes[$offset] ?? null;
	}

	public function offsetSet($offset, $value)
	{
		return $this->attributes[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->attributes[$offset]);
	}

}