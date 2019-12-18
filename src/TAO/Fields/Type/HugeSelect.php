<?php

namespace TAO\Fields\Type;

use TAO\Fields\Field;

class HugeSelect extends Select
{
	protected $datatype = null;
	
	public function datatype()
	{
		if (is_null($this->datatype)) {
			if ($code = $this->param('datatype')) {
				$this->datatype = dt($code);
			}
		}
		return $this->datatype;
	}
	
	public function visibleValue()
	{
		if ($cb = $this->param('visible_value')) {
			if (\TAO\Type::isCallable($cb)) {
				return \TAO\Callback::instance($cb)->call($this);
			}
		}
		if ($datatype = $this->datatype()) {
			if ($item = $datatype->find($this->value())) {
				if (method_exists($item, 'visibleValueForSelect')) {
					return $item->visibleValueForSelect();
				}
				return $item->title();
			}
		}
		if ($this->value() == 0) {
			return $this->param('null_visible_value', '');
		}
		return $this->value();
	}

	public function searchPlaceholder()
	{
		return $this->param('search_placeholder', 'Поиск');
	}

	public function apiActionSearch()
	{
		$items = [];
		if ($q = trim(request()->get('q', false))) {
			$items = $this->searchItems($q);
		}
		return ['items' => $items];
	}


	public function apiActionAdditem()
	{
		if ($q = trim(request()->get('q', false))) {
			if ($datatype = $this->datatype()) {
				try {
					$add = $this->param('with_add', false);
					if ($add == '1' || $add == 'true' || $add == 'yes' || $add === true) {
						$item = $datatype->addByTitle($q);
					} else {
						$item = $datatype->loadByTitle($q);
					}
					if (!$item) {
						return "notfound";
					}
					return [
						'id' => $item->id,
						'title' => $item->title(),
					];
				} catch (\Exception $e) {
					return $e->getMessage();
				}
			}
		}
	}

	public function searchItems($q)
	{
		if ($cb = $this->param('search_items')) {
			if (\TAO\Type::isCallable($cb)) {
				return \TAO\Callback::instance($cb)->args([$q])->call($this);
			}
		}
		if ($datatype = $this->datatype()) {
			$rows = [];
			if (method_exists($datatype, 'quickSearch')) {
				return $datatype->quickSearch($q);
			}
		}
		return [];
	}

	public function renderWithoutTemplate()
	{
		return $this->visibleValue();
	}
}
