<?php

namespace TAO\Foundation;

use \Illuminate\Support\Arr;
use \Illuminate\Contracts\Support\Arrayable;

class AppContext implements \ArrayAccess, Arrayable
{
	protected $attributes = [];

	public function set($key, $value)
	{
		Arr::set($this->attributes, $key, $value);
		return $this;
	}

	public function get($key, $default = null)
	{
		return Arr::get($this->attributes, $key, $default);
	}

	public function has($key)
	{
		return Arr::has($this->attributes, $key);
	}

	public function hasAny($keys)
	{
		return Arr::hasAny($this->attributes, $keys);
	}

	public function forget($key)
	{
		return Arr::forget($this->attributes, $key);
	}

	public function offsetSet($key, $value)
	{
		$this->set($key, $value);
		return $value;
	}

	public function offsetGet($key)
	{
		return $this->get($key);
	}

	public function offsetExists($key)
	{
		return $this->has($key);
	}

	public function offsetUnset($key)
	{
		return $this->forget($key);
	}

	public function toArray()
	{
		return $this->attributes;
	}
}
