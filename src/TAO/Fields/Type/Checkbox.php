<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Checkbox extends Field
{
	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		return $table->boolean($this->name)->default(0);
	}

	/**
	 * @return int
	 */
	public function defaultValue()
	{
		return 0;
	}

	/**
	 * @return mixed
	 */
	public function checked()
	{
		return $this->item[$this->name];
	}

	/**
	 * @return int
	 */
	public function nullValue()
	{
		return 0;
	}

	public function dataExportValue()
	{
		return (int)$this->value();
	}

	public function dataImport($src)
	{
		$this->set(((int)$src));
	}
}
