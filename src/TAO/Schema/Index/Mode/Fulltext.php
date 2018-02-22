<?php
namespace TAO\Schema\Index\Mode;

use Doctrine\DBAL\Schema\Index;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * Class Fulltext
 * @package TAO\Schema\Index\Mode
 */
class Fulltext implements ModeInterface
{
    public function create(Index $index, Blueprint $table)
    {
        DB::statement($this->rawCreateQuery($index, $table));
    }

    public function drop(Index $index, Blueprint $table)
    {
        DB::statement($this->rawDropQuery($index, $table));
    }

    protected function rawCreateQuery(Index $index, Blueprint $table)
    {
        $tableName = $table->getTable();
        $indexName = $index->getName();
        $columns = implode(',',  $index->getColumns());
        return "ALTER TABLE `$tableName` ADD FULLTEXT $indexName ($columns)";
    }

    protected function rawDropQuery(Index $index, Blueprint $table)
    {
        $tableName = $table->getTable();
        $indexName = $index->getName();
        return "ALTER TABLE `$tableName` DROP INDEX $indexName";
    }
}