<?php

namespace TAO\ORM\Traits;

use Illuminate\Database\Schema\Blueprint;

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
     * @return $this
     */
    public function updateSchemaIfNecessary()
    {
        $table = $this->getTable();
        if (app()->taoFields->schemaWasUpdated($table)) {
            return $this;
        }
        if (app()->tao->classModified($this, false)) {
            $this->updateSchema();
        }
        app()->taoFields->schemaUpdated($table);
    }

    /**
     * @return mixed
     */
    public function dbSchema()
    {
        return \Schema::connection($this->getConnectionName());
    }

    /**
     * @param \Closure $closure
     * @return mixed
     */
    public function tableSchema(\Closure $closure)
    {
        return $this->dbSchema()->table($this->getTable(), $closure);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function hasColumn($name)
    {
        return $this->dbSchema()->hasColumn($this->getTable(), $name);
    }

    /**
     * @param array $columns
     * @return mixed
     */
    public function hasColumns(array $columns)
    {
        return $this->dbSchema()->hasColumns($this->getTable(), $columns);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getColumnType($name)
    {
        return $this->dbSchema()->getColumnType($this->getTable(), $name);
    }

    /**
     * @param $name
     * @return null
     */
    public function getIndexInfo($name)
    {
        $indexes = $this->getConnection()->getDoctrineSchemaManager()->listTableIndexes($this->getTable());
        return isset($indexes[$name]) ? $indexes[$name] : null;
    }

    /**
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
            $this->checkFieldsSchema($table);
        });
    }

    /**
     * @param Blueprint $table
     * @return $this
     */
    public function checkFieldsSchema(Blueprint $table)
    {
        foreach ($this->calculatedFields() as $name => $data) {
            $field = $this->field($name);
            $field->checkSchema($table);
            $field->checkIndexes($table);
        }
        return $this;
    }

}