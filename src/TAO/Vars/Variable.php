<?php

namespace TAO\Vars;

class Variable implements \ArrayAccess
{
	public $scope;
	public $name;

	protected $fields = [];
	protected $description;
	protected $values = [];
	protected $adminRender;

	public function __construct($scope, $name, $data)
	{
		$this->scope = $scope;
		$this->name = $name;
		$this->fields = $data['fields'];
		$this->description = isset($data['description']) ? $data['description'] : $name;
		$this->adminRender = isset($data['admin_render']) ? $data['admin_render'] : false;
		$this->setup();
	}

	public function setup()
	{
		foreach ($this->fields as $field => $data) {
			$this->field($field)->setupDefault();
		}
		foreach (\TAO::vars()->load($this->scope, $this->name) as $name => $value) {
			$this->values[$name] = $value;
		}
	}

	public function titleEdit()
	{
		return $this->description;
	}

	public function accessEdit($user)
	{
		return $user['is_admin'];
	}

	public function fields()
	{
		return $this->fields;
	}

	public function field($name)
	{
		if (isset($this->fields[$name])) {
			return app()->taoFields->create($name, $this->fields[$name], $this);
		}
	}

	public function save()
	{
		\TAO::vars()->save($this->scope, $this->name, $this->values);
	}

	public function adminFormFields()
	{
		$fields = array();
		foreach ($this->fields as $field => $data) {
			$f = $this->field($field);
			$fields[$field] = $f;
		}
		return $fields;
	}

	public function errors()
	{
		return [];
	}

	public function render()
	{
		if (is_callable($this->render)) {
			return call_user_func($this->render, $this);
		}
		$fields = array_keys($this->values);
		$field = array_shift($fields);
		return $this->field($field)->render();
	}

	public function renderForAdminList()
	{
		if (is_callable($this->adminRender)) {
			return call_user_func($this->adminRender, $this);
		}
		$fields = array_keys($this->values);
		$field = array_shift($fields);
		return $this->field($field)->renderForAdminList();
	}

	public function getHomeSubDir()
	{
		$path = 'vars';
		if ($this->scope) {
			$path .= "/{$this->scope}";
		}
		$path .= "/{$this->name}";
		return $path;
	}

	public function getHomeDir()
	{
		$path = 'public/' . $this->getHomeSubDir();
		if (!\Storage::exists($path)) {
			\Storage::makeDirectory($path);
		}
		return $path;
	}

	public function getPrivateHomeDir()
	{
		$path = $this->getHomeSubDir();
		if (!\Storage::exists($path)) {
			\Storage::makeDirectory($path);
		}
		return $path;
	}

	public function getKey()
	{
		return $this->name;
	}

	public function getKeyName()
	{
		return 'id';
	}

	public function getDatatype()
	{
		return '_vars';
	}

	public function getAttributeValue($key)
	{
		return $this->values[$key] ?? null;
	}

	public function offsetExists($offset)
	{
		return isset($this->values[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->values[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->values[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->values[$offset]);
	}

	public function update($values = [])
	{
		foreach ($values as $k => $v) {
			$this[$k] = $v;
		}
		$this->save();
	}

	public function where()
	{
		return $this;
	}

	public function __toString()
	{
		return (string)$this->render();
	}
}