<?php

namespace TAO\Fields\Type;

class HugeMultilink extends Multilink
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

	protected function getValueFromRequest($request)
	{
		$ids = [];
		foreach (explode('|', $request->input($this->name, '')) as $id) {
			if ($m = \TAO::regexp('{^\d+$}', trim($id))) {
				$ids[] = (int)$id;
			}
		}
		return $ids;
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
}
