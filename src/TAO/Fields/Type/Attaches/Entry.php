<?php

namespace TAO\Fields\Type\Attaches;

class Entry implements \ArrayAccess
{
	protected $data = [];

	/**
	 * Entry constructor.
	 * @param array $data
	 */
	public function __construct($data = [])
	{
		$this->data = $data;
	}

	/**
	 * @param bool $mods
	 * @return mixed
	 */
	public function path($mods = false)
	{
		if ($mods) {
			return \Images::modify($this->data['path'], $mods);
		}
		return $this->data['path'];
	}

	/**
	 * @param bool $mods
	 * @return mixed
	 */
	public function url($mods = false)
	{
		if ($mods) {
			return \Storage::url($this->path($mods));
		}
		return $this->data['url'];
	}

	/**
	 * @return mixed
	 */
	public function name()
	{
		return $this->data['name'];
	}

	/**
	 * @return string
	 */
	public function ext()
	{
		$name = $this->data['name'];
		if ($m = \TAO::regexp('{\.([a-z0-9]+)$}i', $name)) {
			return strtolower($m[1]);
		}
	}

	/**
	 * @return bool
	 */
	public function isImage()
	{
		return in_array($this->ext(), ['jpg', 'jpeg', 'gif', 'png', 'svg']);
	}

	/**
	 * @param bool $name
	 * @return mixed
	 */
	public function info($name = false)
	{
		$info = $this->data['info'];
		if (is_string($name)) {
			return $info->$name;
		}
		return $info;
	}

	/**
	 * @param $mods
	 */
	public function previewPath($mods)
	{
		if (!$this->isImage()) {
			return;
		}
		return \Images::modify($this->path, $mods);
	}

	/**
	 * @param $mods
	 * @return mixed
	 */
	public function previewUrl($mods)
	{
		return \Storage::url($this->previewPath($mods));
	}

	/**
	 * @param mixed $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * @param mixed $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 * @return mixed
	 */
	public function offsetSet($offset, $value)
	{
		return $this->data[$offset] = $value;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}

	/**
	 * @param $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (isset($this->data[$name])) {
			return $this->data[$name];
		}
	}
}