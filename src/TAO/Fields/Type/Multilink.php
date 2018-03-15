<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;
use TAO\Fields\Model;

class Multilink extends Field
{
	protected $attachedIds;
	protected $relatedItems;

	/**
	 * Метод устанавливает связи с переданными ключами, удаляя старые. Если необходимо добавить привязки,
	 * не удаляя старые, нужно использовать метод attach. Передавать можно как массив, так и единичный ключ.
	 *
	 * @param array|int $ids
	 */
	public function set($ids)
	{
		$this->sync($this->normalizeValue($ids));
	}

	/**
	 * Добавляет привязки к переданным ключам, не удаляя старые. Передавать можно как массив, так и единичный ключ.
	 *
	 * @param array|int $ids
	 */
	public function attach($ids)
	{
		$attachedIds = $this->attachedIds();
		$ids = $this->normalizeValue($ids);
		foreach ($ids as $id) {
			if (!$this->isAttached($id)) {
				$attachedIds[] = $id;
			}
		}
		$this->sync($attachedIds);
		$this->resetAttachedIds();
	}

	/**
	 * Удаляет привязки к переданным ключам. Передавать можно как массив, так и единичный ключ.
	 *
	 * @param array|int $ids
	 */
	public function detach($ids)
	{
		$ids = $this->normalizeValue($ids);
		$this->belongsToMany()->detach($ids);
		$this->resetAttachedIds();
	}

	/**
	 * Устанавливает привязки к переданным ключам. При значении $withDetaching=true удаялет старые привязки.
	 * Передавать можно как массив, так и единичный ключ.
	 *
	 * @param $ids
	 * @param bool $withDetaching
	 */
	public function sync($ids, $withDetaching = true)
	{
		$ids = $this->normalizeValue($ids);
		$this->belongsToMany()->sync($ids, $withDetaching);
		$this->resetAttachedIds();
	}

	/**
	 * @param $value
	 * @return array
	 */
	protected function normalizeValue($value)
	{
		if (is_null($value)) {
			$value = [];
		}
		if (is_array($value) && !\TAO\Type\Collection::isIndexed($value)) {
			$value = array_keys($value);
		}
		return array_wrap($value);
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function belongsToMany()
	{
		return $this->item->belongsToMany($this->relatedModelClass(), $this->tableRelations(), $this->thisKey(), $this->relatedKey());
	}

	/**
	 * @return array
	 */
	public function attachedIds()
	{
		if (is_null($this->attachedIds)) {
			$this->attachedIds = array();
			foreach ($this->belongsToMany()->allRelatedIds() as $id) {
				$this->attachedIds[$id] = $id;
			}
		}
		return $this->attachedIds;
	}

	/**
	 *
	 */
	protected function resetAttachedIds()
	{
		$this->attachedIds = null;
	}

	/**
	 * @return array
	 */
	public function value()
	{
		return $this->attachedIds();
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function isAttached($id)
	{
		if (empty($this->item->getKey())) {
			return false;
		}
		$ids = $this->attachedIds();
		return isset($ids[$id]);
	}

	/**
	 * @return null
	 */
	public function items()
	{
		$items = $this->param('items');
		if (!empty($items)) {
			$items = \TAO::itemsForSelect($items);
			return $items;
		}
		$model = $this->relatedModel();
		$items = $model->itemsForSelect([]);
		return $items;
	}

	/**
	 * @param Blueprint $table
	 */
	public function checkSchema(Blueprint $table)
	{
		$relTable = $this->tableRelations();
		if (!$this->item->dbSchema()->hasTable($relTable)) {
			$this->item->dbSchema()->create($relTable, function (Blueprint $table) {
				$thisKey = $this->thisKey();
				$relatedKey = $this->relatedKey();
				$table->integer($thisKey)->unsigned();
				$table->index($thisKey);
				$table->integer($relatedKey)->unsigned();
				$table->index($relatedKey);
			});
		}
	}

	/**
	 *
	 */
	public function setupDefault()
	{
	}

	/**
	 * @param \TAO\Foundation\Request $request
	 */
	public function setFromRequest($request)
	{
	}

	/**
	 * @param $request
	 */
	public function setFromRequestAfterSave($request)
	{
		$this->set($this->getValueFromRequest($request));
	}

	/**
	 * @return string
	 */
	public function tableRelations()
	{
		if (isset($this->data['table_relations'])) {
			$table = $this->data['table_relations'];
		} else {
			$table = $this->item->getTableName() . '_' . $this->relatedModel()->getTableName() . '_relations';
		}
		if ($db = $this->item->getTableDatabase()) {
			$table = "{$db}.{$table}";
		}
		return $table;
	}

	/**
	 * @return string
	 */
	public function thisKey()
	{
		if (isset($this->data['this_key'])) {
			return $this->data['this_key'];
		}
		return $this->item->getForeignKey();
	}

	/**
	 * @return mixed
	 */
	public function relatedKey()
	{
		if (isset($this->data['related_key'])) {
			return $this->data['related_key'];
		}
		return $this->relatedModel()->getForeignKey();
	}

	/**
	 * @return null
	 */
	public function relatedModelClass()
	{
		$model = $this->param('model');
		if (!$model) {
			$datatype = $this->param('datatype');
			if (!$datatype) {
				return \TAO\Fields\Dummy\Model::class;
			}
			$model = \TAO::datatypeClass($datatype);
		}
		return $model;
	}

	/**
	 * @return Model
	 */
	public function relatedModel()
	{
		$class = $this->relatedModelClass();
		if ($class == \TAO\Fields\Dummy\Model::class) {
			$model = new \TAO\Fields\Dummy\Model;
			$model->code = $this->name;
			return $model;
		}
		return app()->make($class);
	}

	/**
	 * Возвращает коллекцию связанных объектов
	 *
	 * @return Collection
	 */
	public function relatedItems()
	{
		if (is_null($this->relatedItems)) {
			$this->relatedItems = $this->relatedModel()->whereIn('id', $this->attachedIds())->get();
		}
		return $this->relatedItems;
	}

	/**
	 * @return Collection
	 */
	public function attachedItems()
	{
		return $this->relatedItems();
	}

	/**
	 * @return bool
	 */
	public function attached()
	{
		return !empty($this->attachedIds());
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function select($id)
	{
		$key = $this->relatedKey();
		return $this->item->whereHas("{$this->name}_belongs_to_many", function ($query) use ($id, $key) {
			if (is_array($id)) {
				$query->whereIn($key, $id);
			} else {
				$query->where($key, $id);
			}
		});
	}

	/**
	 * @param $id
	 * @return mixed
	 */
	public function find($id)
	{
		return $this->relatedModel()->find($id);
	}

	/**
	 * @param bool $class
	 * @return array
	 */
	public function relatedLinks($class = false)
	{
		$cs = $class ? " class=\"{$class}\"" : '';
		$out = [];
		foreach ($this->relatedItems() as $item) {
			$title = $item->title();
			$url = $item->url();
			$out[] = "<a href=\"{$url}\"{$cs}>{$title}</a>";
		}
		return $out;
	}

	/**
	 * @return array
	 */
	public function nullValue()
	{
		return [];
	}
}
