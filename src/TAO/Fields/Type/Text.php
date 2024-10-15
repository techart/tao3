<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

class Text extends StringField
{
	/**
	 * @param Blueprint $table
	 * @param bool $column
	 * @return mixed
	 */
	public function createField(Blueprint $table, $column = false)
	{
		$column = $column? $column : $this->name;
		$size = $this->typeParamsEnumArg(array('medium', 'long'));
		$method = $size ? "{$size}Text" : 'text';
		return $table->$method($column)->nullable();
	}

	/**
	 * @return null|string
	 */
	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		if (!$style) {
			$style = 'width:90%;height:300px;';
		}
		return $this->validateStyle($style);
	}
	
	public function tabKeyClass()
	{
		return $this->param('tab_key', false)? ' use-tab-key' : '';
	}
	
	public function apiActionSave()
	{
		if (!$this->item->getKey()) {
			return [
				'result' => 'error',
				'message' => 'Item not found',
			];
		}
		$field = request()->get('field');
		$value = request()->get('textarea');

		if (method_exists($this->item, 'validateFieldValue')) {
			$rc = $this->item->validateFieldValue($field, $value);
			if (is_string($rc) && strlen($rc) > 0) {
				return [
					'result' => 'error',
					'message' => $rc,
				];
			}
		}

		$this->item[$field] = $value;
		$this->item->save();
		return [
			'result' => 'ok',
		];
	}
}
