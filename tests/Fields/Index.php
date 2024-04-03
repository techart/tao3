<?php

namespace TaoTests\Fields;

use TAO\Fields\Type\Multilink;
use TaoTests\TestCase;
use TaoTests\Utils\SimpleDatatype;

class Index extends TestCase
{
	protected function getDatatypes()
	{
		return [
			'simple' => SimpleDatatype::class,
		];
	}

	public function testCreateSimpleIndex()
	{
		$columnName = 'column';
		$item = $this->item([
			$columnName => [
				'type' => 'integer(11) index'
			],
		]);
		$tableName = $item->getTableName();
		$indexes = \DB::connection()->getSchemaBuilder()->getIndexes($tableName);
		$found = false;
		foreach ($indexes as $index) {
			if (in_array($columnName, $index['columns'])) {
				$found = true;
				break;
			}
		}
		$this->assertTrue($found);
	}

	public function testIndexWithLongName()
	{
		$columnName = 'column';
		$tableName = 'string_____________of_____________60_____________characters';
		$this->item([
			$columnName => [
				'type' => 'integer(11) index'
			]
		], $tableName);

		$indexes = \DB::connection()->getSchemaBuilder()->getIndexes($tableName);
		$indexFound = false;
		$indexNameMaxLength = 0;
		foreach ($indexes as $indexName => $index) {
			$indexNameMaxLength = max($indexNameMaxLength, strlen($indexName));
			if (in_array($columnName, $index['columns'])) {
				$indexFound = true;
				break;
			}
		}
		$this->assertTrue($indexFound);
		// Limit index name in mysql - 64 symbols
		$this->assertTrue($indexNameMaxLength <= 64);
	}

	public function testMultilinkIndex()
	{
		$columnName = 'column';
		$item = $this->item([
			$columnName => [
				'type' => 'multilink',
				'datatype' => 'simple',
				'related_key' => 'simple_rel'
			],
		]);
		/**
		 * @var Multilink $field
		 */
		$field = $item->field($columnName);
		$tableRelName = $item->field($columnName)->tableRelations();
		$column1 = $field->thisKey();
		$column2 = $field->relatedKey();
		$column1Found = false;
		$column2Found = false;
		$indexes = \DB::connection()->getSchemaBuilder()->getIndexes($tableRelName);
		foreach ($indexes as $index) {
			if (in_array($column1, $index['columns'])) {
				$column1Found = true;
			}
			if (in_array($column2, $index['columns'])) {
				$column2Found = true;
			}
		}
		$this->assertTrue($column1Found && $column2Found);
	}

	public function testMultilinkIndexWithLongName()
	{
		$columnName = 'column';
		$tableName = 'string_____________of_____________60_____________characters';
		$item = $this->item([
			$columnName => [
				'type' => 'multilink',
				'datatype' => 'simple',
				'related_key' => 'simple_rel'
			],
		], $tableName);

		$field = $item->field($columnName);
		$tableRelName = $item->field($columnName)->tableRelations();
		$column1 = $field->thisKey();
		$column2 = $field->relatedKey();
		$column1Found = false;
		$column2Found = false;
		$indexNameMaxLength = 0;
		$indexes = \DB::connection()->getSchemaBuilder()->getIndexes($tableRelName);
		foreach ($indexes as $indexName => $index) {
			$indexNameMaxLength = max($indexNameMaxLength, strlen($indexName));
			if (in_array($column1, $index['columns'])) {
				$column1Found = true;
			}
			if (in_array($column2, $index['columns'])) {
				$column2Found = true;
			}
		}
		$this->assertTrue($column1Found && $column2Found);
		// Limit index name in mysql - 64 symbols
		$this->assertTrue($indexNameMaxLength <= 64);
	}

	protected function item($columns, $tableName = null)
	{
		$item = new SimpleDatatype();
		$item->addFields($columns);
		if (!is_null($tableName)) {
			$item->setTable($tableName);
		}
		$item->updateSchema();
		return $item;
	}
}
