<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;

class Decimal extends FloatField
{
	public function createField(Blueprint $table)
	{
		list($digs, $prec) = $this->lengths();
		$unsigned = (bool)$this->typeParamsEnumArg(array('unsigned'));
		$method = $unsigned ? "unsignedDecimal" : 'decimal';

		return $table->$method($this->name, $digs, $prec)->default($this->defaultValue());
	}

	public function set($value)
	{
		$newValue = preg_replace('{[^\d\.]}', '', $value);
		$unsigned = (bool)$this->typeParamsEnumArg(array('unsigned'));
		if (!$unsigned && $value[0] === '-') {
			$newValue = '-' . $newValue;
		}

		parent::set($newValue);
	}
}
