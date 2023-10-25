<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class ArrayField extends StringField
{
	public function variants()
	{
		return false;
	}

	public function checkSchema(Blueprint $table)
	{
		if (!$this->item->hasColumn($this->name)) {
			$table->text($this->name)->nullable();
		}

		$column = $this->columnSrc();
		if (!$this->item->hasColumn($column)) {
			$table->text($column)->nullable();
		}

		return $this;
	}

	protected function columnSrc($column = false)
	{
		$column = $column? $column : $this->name;
		return $this->param('column_src', "{$column}_src");
	}

	/**
	 * @return null|string
	 */
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		if (!$style) {
			$style = 'width:90%;height:300px;font-family:monospace;';
		}
		return $style;
	}
	
	public function tabKeyClass()
	{
		return $this->param('tab_key', true)? ' use-tab-key' : '';
	}
	
	public function attrs()
	{
		return $this->param(['attrs', 'attributes'], 'wrap="off"');
	}

	public function valueSrc()
	{
		$column = $this->columnSrc();
		return $this->item[$column];
	}

	public function set($value)
	{
		if (is_string($value)) {
			$this->item[$this->name] = \TAO\Text::process($value, 'arrays');
			$this->item[$this->columnSrc()] = $value;
		} else if (is_array($value)) {
			$this->item[$this->name] = $value;
			$this->item[$this->columnSrc()] = $this->arrayToSrc($value);
		}
	}

	public function beforeItemSave()
	{
		if (!is_string($this->item[$this->name])) {
			$this->item[$this->name] = serialize($this->item[$this->name]);
		}
	}
	
	public function renderWithoutTemplate()
	{
		return 'array('.count($this->value()).')';
	}

	public function value($name = false, $default = null)
	{
		if (is_string($this->item[$this->name])) {
			$this->item[$this->name] = unserialize($this->item[$this->name]);
		}
		if ($name) {
			$data = $this->item[$this->name];
			foreach(explode('->', $name) as $key) {
				if (($key = trim($key)) && isset($data[$key])) {
					$data = $data[$key];
				} else {
					return $default;
				}
			}
			return $data;
		}
		return $this->item[$this->name];
	}

	protected function arrayToSrc($array, $prefix = '')
	{
		$lines = [];

		foreach ($array as $key => $value) {
			if (\Illuminate\Support\Str::startsWith($key, '__')) {
				continue;
			}
			if (is_array($value)) {
				$lines[] = $prefix . $key . ' = {';
				if ($line = rtrim($this->arrayToSrc($value, $prefix . "\t"), PHP_EOL)) {
					$lines[] = $line;
				}
				$lines[] = $prefix . '}';
			} else {
				$lines[] = $prefix . $key . ' = ' . strval($value);
			}
		}

		return rtrim(implode(PHP_EOL, $lines), PHP_EOL) . PHP_EOL;
	}
}
