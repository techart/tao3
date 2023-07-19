<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class FloatField extends Field
{
	const DEFAULT_TOTAL_DIGITS = 15;
	const DEFAULT_DECIMAL_DIGITS = 3;

	/**
	 * @param Blueprint $table
	 * @return mixed
	 */
	public function createField(Blueprint $table)
	{
		list($digs, $prec) = $this->lengths();
		return $table->float($this->name, $digs, $prec)->default($this->defaultValue());
	}
	
	protected function lengths()
	{
		$digs = self::DEFAULT_TOTAL_DIGITS;
		$prec = self::DEFAULT_DECIMAL_DIGITS;
		$args = $this->typeParamsArgs();
		if (is_array($args) && !empty($args)) {
			$intArgs = $this->getFirstIntArgs($args);
			if (count($intArgs) == 1) {
				$prec = $intArgs[0];
			} else {
				$digs = $intArgs[0];
				$prec = $intArgs[1];
			}
		}
		return [$digs, $prec];
	}

	protected function getFirstIntArgs($args) {
		$intArgs = [];
		foreach ($args as $arg) {
			if (preg_match('{^\d+$}', $arg)) {
				$intArgs[] = (int)$arg;
			} else {
				break;
			}
		}

		return $intArgs;
	}

	public function defaultValue()
	{
		return 0;
	}

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
		$this->set($src);
	}

	public function set($value)
	{
		$newValue = $value;
		if ($value) {
			$newValue = preg_replace('{[^\d\.]}', '', $value);
			if (mb_substr((string) $value, 0, 1) === '-') {
				$newValue = '-' . $newValue;
			}
		}

		parent::set($newValue);
	}

	public function renderWithoutTemplate()
	{
		return (string)$this->value();
	}
}
