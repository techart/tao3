<?php
namespace TAO\Schema\Index;

use Doctrine\DBAL\Schema\Index;
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
        $flags = [];
        if ($type == 'fulltext') {
            $flags[] = $type;
        }
        return new Index($indexName, $columns, $type == 'unique', false, $flags);
    }

    /**
     * @param Index $index
     * @param Blueprint $table
     * @param Connection $connection
     *
     * Метод проверяет наличие данного индекса в указанной таблице. В случае отсутствия оного - создает,
     * при наличии - обновляет.
     */
    public function process(Index $index, Blueprint $table, Connection $connection)
    {
        $existingIndex = $this->getExistingIndex($connection, $index->getName(), $table->getTable());
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
    public function create(Index $index, Blueprint $table)
    {
        $this->mode($index)->create($index, $table);
    }

    /**
     * @param Index $index
     * @param Blueprint $table
     *
     * Удаляет указанный индекс из таблицы
     */
    public function drop(Index $index, Blueprint $table)
    {
        $this->mode($index)->drop($index, $table);
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
    public function getExistingIndex(Connection $connection, $indexName, $tableName)
    {
        $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($tableName);
        return isset($indexes[$indexName]) ? $indexes[$indexName] : null;
    }

    public function indexesIsSame(Index $index1, Index $index2)
    {
        return $index1->getName() == $index2->getName() &&
            $index1->getColumns() == $index2->getColumns() &&
            $index1->isUnique() == $index2->isUnique() &&
            $index1->hasFlag('fulltext') == $index2->hasFlag('fulltext');
    }

    /**
     * @param Index $index
     * @return Mode\ModeInterface
     *
     * Возвращает объект, который реализует логику управления данным видом индекса.
     */
    protected function mode(Index $index)
    {
        return Mode\Factory::mode($index);
    }
}