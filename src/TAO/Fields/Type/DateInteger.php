<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class DateInteger extends Field
{
	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		return $table->integer($this->name, false, true)->default(0);
	}

	/**
	 * @return int
	 */
	public function defaultValue()
	{
		return 0;
	}

	/**
	 * @return int
	 */
	public function nullValue()
	{
		return 0;
	}

	/**
	 * @return null
	 */
	public function withTime()
	{
		return $this->param('with_time', false);
	}

	/**
	 * @return bool|null
	 */
	public function withSeconds()
	{
		if (!$this->withTime()) {
			return false;
		}
		return $this->param('with_seconds', false);
	}

	/**
	 * @return null|string
	 */
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		return empty($style) ? 'width:200px' : $style;
	}

	/**
	 * @return array
	 */
	protected function defaultContext()
	{
		$context = parent::defaultContext();
		$context['format'] = 'd.m.Y';
		$context['with_datepicker'] = $this->param('datepicker', true);
		return $context;
	}

	/**
	 * @return string
	 */
	protected function defaultTemplate()
	{
		return 'fields ~ date_integer.output';
	}

	/**
	 * @return string
	 */
	public function generateFormat()
	{
		if ($this->withTime()) {
			if ($this->withSeconds()) {
				return 'd.m.Y - H:i:s';
			}
			return 'd.m.Y - H:i';
		}
		return 'd.m.Y';
	}

	/**
	 * @return string
	 */
	public function renderForAdminList()
	{
		return $this->render();
	}

	/**
	 * @param $value
	 */
	public function set($value)
	{
		if (trim($value) == '') {
			$value = 0;
		} elseif ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)$}', $value)) {
			$value = mktime(0, 0, 0, $m[2], $m[1], $m[3]);
		} elseif ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+)$}', $value)) {
			$value = mktime($m[4], $m[5], 0, $m[2], $m[1], $m[3]);
		} elseif ($m = \TAO::regexp('{^(\d+)\.(\d+)\.(\d+)\s*-\s*(\d+):(\d+):(\d+)$}', $value)) {
			$value = mktime($m[4], $m[5], $m[6], $m[2], $m[1], $m[3]);
		}
		$this->item[$this->name] = $value;
	}
}
