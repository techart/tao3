<?php

namespace TAO\Schema\Index;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class Builder
 * @package TAO\Schema\Index
 *
 * Класс, реализующий управление индексами для работы схемы.
 */
class Builder
{
	/**
	 * @param $indexName
	 * @param $columns
	 * @param $type
	 * @return Index
	 *
	 * Метод создает объект индекса
	 */
	public function makeIndex($indexName, $columns, $type)
	{
		return [
			'name' => $indexName,
			'columns' => $columns,
			'type' => $type == 'fulltext' ? 'fulltext' : 'btree',
			'unique' => $type == 'unique',
			'primary' => false,
		];
	}

	/**
	 * @param array $index
	 * @param Blueprint $table
	 * @param Connection $connection
	 *
	 * Метод проверяет наличие данного индекса в указанной таблице. В случае отсутствия оного - создает,
	 * при наличии - обновляет.
	 */
	public function process($index, Blueprint $table)
	{
		$existingIndex = $this->getExistingIndex($index['name'], $table->getTable());
		if ($existingIndex) {
			if (!$this->indexesIsSame($existingIndex, $index)) {
				$this->drop($existingIndex, $table);
				$this->create($index, $table);
			}
		} else {
			$this->create($index, $table);
		}
	}

	/**
	 * @param Index $index
	 * @param Blueprint $table
	 *
	 * Создает указанный индекс в таблице
	 */
	public function create($index, Blueprint $table)
	{
		$name = $index['name'];
		$columns = $index['columns'];
		if ($index['unique'] ?? false) {
			return $table->unique($columns, $name);
		}
		if ($index['primary'] ?? false) {
			return $table->primary($columns, $name);
		}
		if ('fulltext' === $index['type'] ?? '') {
			return $table->fullText($columns, $name);
		}
		return $table->index($columns, $name);
	}

	/**
	 * @param Index $index
	 * @param Blueprint $table
	 *
	 * Удаляет указанный индекс из таблицы
	 */
	public function drop($index, Blueprint $table)
	{
		$name = $index['name'];
		if ($index['unique'] ?? false) {
			return $table->dropUnique($name);
		}
		if ($index['primary'] ?? false) {
			return $table->dropPrimary($name);
		}
		if ('fulltext' === $index['type'] ?? '') {
			return $table->dropFullText($name);
		}
		return $table->dropIndex($name);
	}

	/**
	 * @param Connection $connection
	 * @param $indexName
	 * @param $tableName
	 * @return Index|null
	 *
	 * Ищет индекс по имени в данной таблице. Возвращает объект индекса при наличии. В случае отсутсвия
	 * - возвращает null.
	 */
	public function getExistingIndex($indexName, $tableName)
	{
		$indexes = \Schema::getIndexes($tableName);
		foreach ($indexes as $index) {
			if ($index['name'] == $indexName) {
				return $index;
			}
		}
		return null;
	}

	public function indexesIsSame($index1, $index2)
	{
		return $index1 === $index2;
	}
}