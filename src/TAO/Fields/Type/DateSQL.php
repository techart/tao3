<?php

namespace TAO\Fields\Type;

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class DateSQL extends DateInteger
{
	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		return $table->dateTime($this->name)->nullable();
	}

	/**
	 * @return int
	 */
	public function defaultValue()
	{
		return '';
	}

	/**
	 * @return int
	 */
	public function nullValue()
	{
		return '0000-01-01 00:00:00';
	}

	/**
	 * @return string
	 */
	public function inputTemplateFrom()
	{
		return 'date_integer';
	}

	/**
	 * @return string
	 */
	protected function defaultTemplate()
	{
		return 'fields ~ date_integer.output';
	}

	/**
	 * @param $value
	 */
	public function set($value)
	{
		if (trim($value) == '') {
			$value = $this->nullValue();
		} elseif (is_string($value)) {
			try {
				$dateTimeTimestamp = app('tao.utils')->dateTime($value, true)->format('Y-m-d H:i:s');
			} catch (\Exception $e) {
				$this->data['fieldError'] = $e->getMessage();
				$dateTimeTimestamp = $value;
			}
			$value = $dateTimeTimestamp;
		}
		$this->item[$this->name] = $value;
	}
}
