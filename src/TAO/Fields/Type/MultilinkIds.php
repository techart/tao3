<?php

namespace TAO\Fields\Type;

class MultilinkIds extends Multilink
{
	public function tableRelations()
	{
		if (isset($this->data['table_relations'])) {
			$table = $this->data['table_relations'];
		} else {
			$table = $this->item->getTableName() . '_' . $this->name . '_rels';
		}
		if ($db = $this->item->getTableDatabase()) {
			$table = "{$db}.{$table}";
		}
		return $table;
	}

	public function relatedKey()
	{
		if (isset($this->data['related_key'])) {
			return $this->data['related_key'];
		}
		return 'attached_id';
	}

	protected function itemHasValue()
	{
		$key = $this->thisKey();
		$table = $this->tableRelations();
		$rc = \DB::select("SELECT COUNT(*) as cnt FROM {$table} WHERE {$key}=?", [$this->item->id]);
		return current($rc)->cnt > 0;
	}

	public function styleForAdminInput()
	{
		$style = parent::styleForAdminInput();
		if (!$style) {
			$style = 'width:90%;height:50px;';
		}
		return $style;
	}

	public function attachedIds()
	{
		$ids = [];
		$key = $this->thisKey();
		$rel = $this->relatedKey();
		$table = $this->tableRelations();
		$rc = \DB::select("SELECT * FROM {$table} WHERE {$key}=?", [$this->item->id]);
		foreach ($rc as $row) {
			$ids[] = $row->$rel;
		}
		return $ids;
	}

	public function setFromRequestAfterSave($request)
	{
		if ($request->has($this->name)) {
			$src = $request->get($this->name);
			$ids = [];
			foreach (preg_split('{[^\d]+}', $src) as $id) {
				if ($id = (int)trim($id)) {
					$ids[] = $id;
				}
			}

			$this->set($ids);
			$this->sync();
		}
	}

	public function sync($withDetaching = true)
	{
		$key = $this->thisKey();
		$rel = $this->relatedKey();
		$table = $this->tableRelations();
		if ($withDetaching) {
			\DB::delete("DELETE FROM {$table} WHERE {$key}=?", [$this->item->id]);
		}
		foreach ($this->attachedIds as $id) {
			\DB::insert("INSERT INTO {$table} SET {$key}=?, {$rel}=?", [$this->item->id, $id]);
		}
	}

	/**
	 * @return string
	 * @throws \TAO\Exception\UnknownDatatype
	 */
	public function inputValue()
	{
		$ids = $this->attachedIds();
		sort($ids);
		return implode(' ', $ids);
	}

	protected function getValueFromRequest($request)
	{
		return $request->input($this->name);
	}
	
	public function render($arg1 = false, $arg2 = false)
	{
		if ($arg1 || $arg2) {
			return parent::render($arg1, $arg2);
		}
		return implode(', ', $this->attachedIds());
	}
}
