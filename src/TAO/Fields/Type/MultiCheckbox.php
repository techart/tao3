<?php

namespace TAO\Fields\Type;

use TAO\Fields\Field;
use Illuminate\Database\Schema\Blueprint;


/**
 * По сути это тоже самое что и TAO\Fields\Type\Multilink
 * Но при этом связка с другой моделью не обязательна
 * и вполне можно обойтись объявлением 'items' в филдах
 *
 * Сохранение значений происходит после сохранения item'а
 * в отдельной таблице связанной с моделью
 *
 * Метод value возвращает массив ключ -> значение
 * сформированный из items
 *
 * Class MultiCheckbox
 *
 * @property \Illuminate\Support\Collection | null $attachedKeys
 * @property bool $relationsIsLoaded
 *
 * @package TAO\Fields\Type
 */
class MultiCheckbox extends Field
{
	protected $attachedKeys = null;
	protected $relationsIsLoaded = false;

	/**
	 * @return array
	 */
	public function value()
	{
		return $this->rawValue()->toArray();
	}

	/**
	 * @return \Illuminate\Support\Collection
	 */
	public function rawValue()
	{
		$itemsCollection = collect($this->items());

		return $itemsCollection->intersectByKeys($this->attachedKeys()->flip());
	}

	/**
	 * Список всех доступных значений
	 *
	 * @return array
	 */
	public function items()
	{
		return \TAO::itemsForSelect($this->param('items', $this->defaultValue()));
	}

	/**
	 * Коллекция связанных значений (от сюда и название)
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function attachedKeys()
	{
		if (!$this->relationsIsLoaded) {
			$this->attachedKeys = $this->table()
				->select('key')
				->where($this->item->getForeignKey(), $this->item->getKey())
				->where('field', $this->name)
				->get()->pluck('key');

			$this->relationsIsLoaded = true;
		}

		return $this->attachedKeys;
	}

	/**
	 * Узнать, сохранено ли значение для $id
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function isAttached($id)
	{
		return $this->attachedKeys()->containsStrict($id);
	}

	public function applyFilter($builder, $value)
	{
		if (!is_array($value)) {
			$value = [$value];
		}
		return $builder->join($this->tableName(), function($join) use ($value) {
		    $table = $this->item->table;
		    $rel = $this->tableName();
		    $fkey = $this->item->getForeignKey();
		    return $join
				->on("{$table}.id", '=', "{$rel}.{$fkey}")
				->where("{$rel}.field", $this->name)
				->whereIn("{$rel}.key", $value)
			;
		})->groupBy('id');
	}

	/**
	 * Запись в item значения.
	 * Сохранение в базу попозже
	 *
	 * @param $values
	 */
	public function set($values)
	{
		$this->attachedKeys = collect(array_wrap($values));
	}

	/**
	 * Запомнили то, что уже знаем данные из реквеста
	 *
	 * @param \TAO\Foundation\Request $request
	 */
	public function setFromRequest($request)
	{
		parent::setFromRequest($request);
		$this->relationsIsLoaded = true;
	}

	/**
	 * Сохранение в базу
	 */
	public function save() {
		\DB::transaction(function () {
			// Удалим старые значения
			$this->table()
				->where($this->item->getForeignKey(), $this->item->getKey())
				->where('field', $this->name)
				->delete();

			if (empty($this->attachedKeys)) {
				return;
			}

			// Добавим новые
			$this->table()->insert($this->attachedKeys->map([$this, 'mapValueForInsert'])->toArray());
		});
	}

	/**
	 * Используется для установки дефолтного значения
	 *
	 * @return array
	 */
	public function defaultValue()
	{
		return [];
	}

	/**
	 * Используется для проверки на пустое поле
	 *
	 * @return array
	 */
	public function nullValue()
	{
		return [];
	}

	/**
	 * Реализуем отложеное сохранение
	 */
	public function afterItemSave()
	{
		$this->save();
	}

	/**
	 * Получение названия таблицы, зависит от модели
	 *
	 * @return string
	 */
	public function tableName()
	{
		$table = $this->item->getTableName() . '_multi_checkbox_values';

		if ($db = $this->item->getTableDatabase()) {
			$table = "{$db}.{$table}";
		}
		return $table;
	}

	/**
	 * Проверка существования связанной таблицы. Если таблицы нет, то она создастся
	 *
	 * @param Blueprint $table
	 *
	 * @return MultiCheckbox $this
	 */
	public function checkSchema(Blueprint $table)
	{
		$valuesTable = $this->tableName();

		if (!$this->item->dbSchema()->hasTable($valuesTable)) {
			$this->item->dbSchema()->create($valuesTable, function($table) {
				$this->tableSchema($table);
			});
		}

		return $this;
	}

	/**
	 * Получение связанной таблицы
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	protected function table()
	{
		return \DB::table($this->tableName());
	}

	/**
	 * Шаблон схемы связанной таблицы для базы
	 *
	 * @param Blueprint $table
	 */
	protected function tableSchema(Blueprint $table)
	{
		$thisKey = $this->item->getForeignKey();

		$table->unsignedInteger($thisKey);
		$table->index($thisKey);
		$table->foreign($thisKey)->references($this->item->getKeyName())->on($this->item->getTable())->onDelete('cascade');
		$table->string('field', 32);
		$table->string('key', 32);
	}

	/**
	 * Подготавливаем значения из реквеста
	 *
	 * @param \TAO\Foundation\Request $request
	 *
	 * @return array
	 */
	protected function getValueFromRequest($request)
	{
		return array_keys(parent::getValueFromRequest($request));
	}

	public function renderForAdminView()
	{
		return $this->render();
	}

	public function renderWithoutTemplate()
	{
		return implode(', ', $this->value());
	}

	/**
	 * Подготавливает значение для вставки в таблицу
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function mapValueForInsert($value)
	{
		return [
			$this->item->getForeignKey() => $this->item->getKey(),
			'field' => $this->name,
			'key' => $value
		];
	}

	public function dataExportValue()
	{
		return implode(',', array_keys($this->attachedKeys()->toArray()));
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
		$this->set($ids);
	}

}
