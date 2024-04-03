<?php

namespace TAO\ORM\Traits;

use Illuminate\Database\Schema\Blueprint;
use TAO\Fields\Field;

/**
 * Class Schema
 */
trait Schema
{
	/**
	 * Возвращает только имя таблицы в случае если оно указано через точку после имени базы данных
	 *
	 * @return string
	 */
	public function getTableName()
	{
		$name = $this->getTable();
		if ($m = \TAO::regexp('{\.(.+)$}', $name)) {
			$name = $m[1];
		}
		return $name;
	}

	/**
	 * Возвращает имя базы данных если оно указано в имени таблицы
	 *
	 * @return mixed
	 */
	public function getTableDatabase()
	{
		$name = $this->getTable();
		if ($m = \TAO::regexp('{^(.+)\.}', $name)) {
			return $m[1];
		}
		return false;
	}

	/**
	 * Проводит проверку и обновление схемы БД в случае если это необходимо. Т.е. если PHP-файл модели обновился или кеш был сброшен.
	 * Если вам не нужно автоматическое обновление схемы для конкретной модели, то измение этот метод.
	 *
	 * @return $this
	 */
	public function updateSchemaIfNecessary()
	{
		$class = get_class($this);
		if (app('tao.fields')->schemaWasUpdated($class)) {
			return $this;
		}
		if (app()->tao->classModified($this, false) || \App::environment('testing')) {
			$this->updateSchema();
		}
		app('tao.fields')->schemaUpdated($class);
		return $this;
	}

	/**
	 * Возвращает экземпляр конструктора схемы БД для данной модели (с нужным подключением к БД)
	 *
	 * @return mixed
	 */
	public function dbSchema()
	{
		return \Schema::connection($this->getConnectionName());
	}

	/**
	 * Работа с таблицей. В качестве аргумента передаем замыкание, в котором производятся нужные манипуляции
	 * Blueprint будет передан в замыкание.
	 *
	 * @param \Closure $closure
	 * @return mixed
	 */
	public function tableSchema(\Closure $closure)
	{
		return $this->dbSchema()->table($this->getTable(), $closure);
	}

	/**
	 * Имеется ли в таблице колонка с таким именем
	 *
	 * @param $name
	 * @return bool
	 */
	public function hasColumn($name)
	{
		return $this->dbSchema()->hasColumn($this->getTable(), $name);
	}

	/**
	 * Имеется ли в таблице набор колонок с такими именами
	 *
	 * @param array $columns
	 * @return mixed
	 */
	public function hasColumns(array $columns)
	{
		return $this->dbSchema()->hasColumns($this->getTable(), $columns);
	}

	/**
	 * Возвращает тип колонки в таблице
	 *
	 * @param $name
	 * @return mixed
	 */
	public function getColumnType($name)
	{
		return $this->dbSchema()->getColumnType($this->getTable(), $name);
	}


	/**
	 * Выполняет безусловное обновление схемы
	 *
	 */
	public function updateSchema()
	{
		\Log::debug('Update schema for ' . $this->getDatatype());
		$tableName = $this->getTable();
		if (!$this->dbSchema()->hasTable($tableName)) {
			$this->dbSchema()->create($tableName, function (Blueprint $table) {
				if ($this->idType == 'auto_increment') {
					$table->increments($this->primaryKey);
				} elseif ($this->idType == 'uuid') {
					$table->string($this->primaryKey, 36);
					$table->primary($this->primaryKey);
				}
			});
		}
		$this->tableSchema(function (Blueprint $table) {
			if ($this->idType == 'auto_increment' && $this->getColumnType($this->primaryKey) == 'string') {
				$table->increments($this->primaryKey)->change();
			} elseif ($this->idType == 'uuid' && $this->getColumnType($this->primaryKey) == 'integer') {
				$table->string($this->primaryKey, 36)->change();
			}
			if ($this->timestamps && !$this->hasColumns(['created_at', 'updated_at'])) {
				$table->timestamps();
			}
			$this->checkFieldsColumns($table);
		});
		$this->tableSchema(function (Blueprint $table) {
			$this->checkFieldsIndexes($table);
		});
	}

	/**
	 * @param Blueprint $table
	 * @return $this
	 */
	protected function checkFieldsColumns(Blueprint $table)
	{
		$fields = $this->fieldsObjects();
		array_walk($fields, function ($field) use ($table) {
			/** @var Field $field */
			if ($field->name !== $this->primaryKey) {
				$field->checkSchema($table);
			}
		});
	}

	/**
	 * @param Blueprint $table
	 * @return $this
	 */
	protected function checkFieldsIndexes(Blueprint $table)
	{
		$fields = $this->fieldsObjects();
		array_walk($fields, function ($field) use ($table) {
			/** @var Field $field */
			$field->checkIndexes($table);
		});
		return $this;
	}

}
