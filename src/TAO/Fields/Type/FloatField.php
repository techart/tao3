<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class FloatField extends Field
{

	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		list($digs, $prec) = $this->lengths();
		return $table->float($this->name, $digs, $prec)->default(0);
	}
	
	protected function lengths()
	{
		$digs = 15;
		$prec = 3;
		$args = $this->typeParamsArgs();
		if (is_array($args) && !empty($args)) {
			if (count($args) == 1) {
				$prec = $args[0];
			} else {
				$digs = $args[0];
				$prec = $args[1];
			}
		}
		return [$digs, $prec];
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
		$value = str_replace(',', '.', $value);
		$this->item[$this->name] = preg_replace('{[^\d\.]}', '', $value);
	}

	public function renderWithoutTemplate()
	{
		return (string)$this->value();
	}
}
