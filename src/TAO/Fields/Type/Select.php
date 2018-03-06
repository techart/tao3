<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Select extends Field
{
	public function createField(Blueprint $table)
	{
		$string = false;
		$length = 100;
		if ($args = $this->typeParamsArgs()) {
			foreach ($this->typeParamsArgs() as $arg) {
				if ($arg == 'string') {
					$string = true;
				} elseif ($arg == 'integer') {
					$string = false;
				} elseif ($m = \TAO::regexp('{^string(\d+)$}', $arg)) {
					$string = true;
					$length - (int)$m[1];
				}
			}
		}
		if ($string) {
			return $table->string($this->name, $length)->default('');
		}
		return $table->integer($this->name, false, false)->default(0);
	}

	public function items()
	{
		$src = \TAO::inAdmin() ? $this->param(['admin_items', 'items']) : $this->param('items');
		return \TAO::itemsForSelect($src);
	}

	public function defaultInputContext()
	{
		$data = parent::defaultInputContext();
		$data['items'] = $this->items();
		return $data;
	}
}
