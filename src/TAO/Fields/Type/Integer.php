<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Integer extends Field
{
	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		$size = $this->typeParamsEnumArg(array('tiny', 'small', 'medium', 'big'));
		$unsigned = (bool)$this->typeParamsEnumArg(array('unsigned'));
		$method = $size ? "{$size}Integer" : 'integer';
		return $table->$method($this->name, false, $unsigned)->default(0);
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
	 * @return null|string
	 */
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		return empty($style) ? 'width:200px' : $style;
	}

	public function dataExportValue()
	{
		return $this->value();
	}

	public function dataImport($src)
	{
		$this->set(((int)$src));
	}

	public function set($value)
	{
		$this->item[$this->name] = preg_replace('{[^\d]}', '', $value);
	}
}
