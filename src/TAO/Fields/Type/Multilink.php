<?php

namespace TAO\Fields\Type;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Schema\Blueprint;
use TAO\Exception\UnknownDatatype;
use TAO\Fields\Field;
use TAO\ORM\Model;

class Multilink extends Field
{
	protected $attachedIds = [];
	protected $relationsIsLoaded = false;
	protected $relatedItems;

	/**
	 * Устанавливает переданные привязки, удаляя существующие. Не сохраняет данные в БД до сохранения модели.
	 *
	 * @param array|int $ids
	 */
	public function set($ids)
	{
		$this->resetAttachedIds();
		$this->addToAttachedIds($ids);
	}

	/**
	 * Добавляет указанные привязки к уже существующим. Не сохраняет данные в БД до сохранения модели.
	 *
	 * @param array|int $ids
	 */
	public function add($ids)
	{
		$this->loadRelationsIfRequired();
		$this->addToAttachedIds($ids);
	}

	/**
	 * Удаляет указанные привязки. Не сохраняет данные в БД до сохранения модели.
	 *
	 * @param array|int $ids
	 * @throws UnknownDatatype
	 */
	public function delete($ids)
	{
		$this->deleteFromAttachedIds($ids);
	}

	/**
	 * Добавляет привязки к переданным ключам, не удаляя старые. Передавать можно как массив, так и единичный ключ.
	 * Изменения сразу сохраняются в БД. Если это нежелательно - используйте метод add.
	 *
	 * @param array|int $ids
	 * @throws UnknownDatatype
	 */
	public function attach($ids)
	{
		$this->add($ids);
		$this->sync();
	}

	/**
	 * Удаляет привязки к переданным ключам. Передавать можно как массив, так и единичный ключ. Изменения сразу
	 * сохраняются в БД. Если это нежелательно - используйте метод delete.
	 *
	 * @param array|int $ids
	 * @throws UnknownDatatype
	 */
	public function detach($ids)
	{
		$this->deleteFromAttachedIds($ids);
		$this->sync();
	}

	/**
	 * Сохраняет в БД привязки, которые были установлены в филде.
	 *
	 * @param bool $withDetaching
	 * @throws UnknownDatatype
	 */
	public function sync($withDetaching = true)
	{
		$this->belongsToMany()->sync($this->attachedIds(), $withDetaching);
	}

	/**
	 * @param array|int $value
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
		return array_filter(array_wrap($value), function ($val) {
			return is_numeric($val);
		});
	}

	/**
	 * @return BelongsToMany
	 * @throws UnknownDatatype
	 */
	public function belongsToMany()
	{
		return $this->item->belongsToMany($this->relatedModelClass(), $this->tableRelations(), $this->thisKey(), $this->relatedKey());
	}

	/**
	 * @return array
	 * @throws UnknownDatatype
	 */
	public function attachedIds()
	{
		$this->loadRelationsIfRequired();
		return $this->attachedIds;
	}

	/**
	 * @param array|int $ids
	 */
	protected function addToAttachedIds($ids)
	{
		foreach($this->normalizeValue($ids) as $id) {
			$this->attachedIds[$id] = $id;
		}
	}

	public function defaultValue()
	{
		return [];
	}

	protected function getValueFromRequest($request)
	{
		return array_keys(parent::getValueFromRequest($request));
	}

	public function setFromRequest($request)
	{
		parent::setFromRequest($request);
		$this->relationsIsLoaded = true;
	}

	public function setFromFilter($request)
	{
		parent::setFromFilter($request);
		$this->relationsIsLoaded = true;
	}

	/**
	 * @param array|int $ids
	 * @throws UnknownDatatype
	 */
	protected function deleteFromAttachedIds($ids)
	{
		foreach($this->normalizeValue($ids) as $id) {
			if ($this->isAttached($id)) {
				unset($this->attachedIds[$id]);
			}
		}
	}

	protected function resetAttachedIds()
	{
		$this->attachedIds = $this->nullValue();
	}

	/**
	 * @return array
	 * @throws UnknownDatatype
	 */
	public function value()
	{
		return $this->attachedIds();
	}

	/**
	 * @param $id
	 * @return bool
	 * @throws UnknownDatatype
	 */
	public function isAttached($id)
	{
		return isset($this->attachedIds()[$id]);
	}

	/**
	 * @return array
	 * @throws UnknownDatatype
	 */
	public function items()
	{
		$items = $this->param('items');
		if (!empty($items)) {
			$items = \TAO::itemsForSelect($items);
			return $items;
		}
		return $this->itemsFromModel();
	}

	protected function itemsFromModel()
	{
		return $this->relatedModel()->itemsForSelect();
	}

	/**
	 * @param Blueprint $table
	 * @throws UnknownDatatype
	 */
	public function checkSchema(Blueprint $table)
	{
		$relTable = $this->tableRelations();
		if (!$this->item->dbSchema()->hasTable($relTable)) {
			$this->item->dbSchema()->create($relTable, function (Blueprint $table) {
				$thisKey = $this->thisKey();
				$relatedKey = $this->relatedKey();
				$table->integer($thisKey)->unsigned();
				$table->index($thisKey, $this->indexName($thisKey));
				$table->integer($relatedKey)->unsigned();
				$table->index($relatedKey, $this->indexName($relatedKey));
			});
		}
	}

	/**
	 * @return bool
	 * @throws UnknownDatatype
	 */
	protected function itemHasValue()
	{
		return $this->item->getKey() && $this->belongsToMany()->count() > 0;
	}

	/**
	 * @throws UnknownDatatype
	 */
	public function afterItemSave()
	{
		$this->sync();
	}

	/**
	 * @return string
	 * @throws UnknownDatatype
	 */
	public function tableRelations()
	{
		if (isset($this->data['table_relations'])) {
			$table = $this->data['table_relations'];
		} else {
			$table = $this->item->getTableName() . '_' . $this->relatedModel()->getTableName() . '_relations';
			if (method_exists($this->item, 'transformRelationTableName')) {
				$table = $this->item->transformRelationTableName($table);
			}
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
	 * @throws UnknownDatatype
	 */
	public function relatedKey()
	{
		if (isset($this->data['related_key'])) {
			return $this->data['related_key'];
		}
		return $this->relatedModel()->getForeignKey();
	}

	/**
	 * @return string
	 * @throws UnknownDatatype
	 */
	public function relatedModelClass()
	{
		$model = $this->param('model');
		if (!$model) {
			$datatype = $this->param('datatype');
			if (!$datatype) {
				return \TAO\ORM\Dummy\Model::class;
			}
			$model = \TAO::datatypeClass($datatype);
		}
		return $model;
	}

	/**
	 * @return Model|\TAO\ORM\Dummy\Model
	 * @throws UnknownDatatype
	 */
	public function relatedModel()
	{
		$class = $this->relatedModelClass();
		if ($class == \TAO\ORM\Dummy\Model::class) {
			$model = new \TAO\ORM\Dummy\Model;
			$model->code = $this->name;
			return $model;
		}
		return app()->make($class);
	}

	/**
	 * Возвращает коллекцию связанных объектов
	 * @return Collection
	 * @throws UnknownDatatype
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
	 * @throws UnknownDatatype
	 */
	public function attachedItems()
	{
		return $this->relatedItems();
	}

	/**
	 * @return bool
	 * @throws UnknownDatatype
	 */
	public function attached()
	{
		return !empty($this->attachedIds());
	}

	/**
	 * @param $id
	 * @return mixed
	 * @throws UnknownDatatype
	 */
	public function select($id)
	{
		$key = $this->relatedKey();
		return $this->item->whereHas("{$this->name}_belongs_to_many",
			function ($query) use ($id, $key) {
				/** @var Builder $query */
				if (is_array($id)) {
					$query->whereIn($key, $id);
				} else {
					$query->where($key, $id);
				}
			}
		);
	}

	/**
	 * @param $id
	 * @return Model
	 * @throws UnknownDatatype
	 */
	public function find($id)
	{
		return $this->relatedModel()->find($id);
	}

	/**
	 * @param bool $class
	 * @return array
	 * @throws UnknownDatatype
	 */
	public function relatedLinks($class = false)
	{
		$cs = $class ? " class=\"{$class}\"" : '';
		$out = [];
		foreach ($this->relatedItems() as $item) {
			$title = $item->title();
			$url = $item->url();
			$out[] = "<a href=\"{$url}\" {$cs}>{$title}</a>";
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

	public function dataExportValue()
	{
		return implode(',', array_keys($this->attachedIds()));
	}

	public function dataImport($src)
	{
		$ids = [];
		foreach (explode(',', $src) as $id) {
			$id = (int)trim($id);
			if ($id>0) {
				$ids[] = $id;
			}
		}
		$this->attach($ids);
	}

	public function render($arg1 = false, $arg2 = false)
	{
		if ($arg1 || $arg2) {
			return parent::render($arg1, $arg2);
		}
		$out = [];
		foreach ($this->relatedItems() as $item) {
			$out[] = $item->title();
		}
		sort($out);
		return implode(', ', $out);
	}

	/**
	 * Метод загружает привязки из БД
	 *
	 * @throws UnknownDatatype
	 */
	protected function loadRelations()
	{
		if ($this->item->getKey()) {
			$ids = $this->belongsToMany()->allRelatedIds();
			$this->set(is_array($ids) ? $ids : $ids->toArray());
		}
		$this->relationsIsLoaded = true;
	}

	/**
	 * Метод вызывает загрузку привязок из БД если они еще не были загружены
	 *
	 * @throws UnknownDatatype
	 */
	protected function loadRelationsIfRequired()
	{
		if (!$this->relationsIsLoaded) {
			$this->loadRelations();
		}
	}
}
