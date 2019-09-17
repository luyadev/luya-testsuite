<?php

namespace luya\testsuite\traits;

/**
 * Base Trait for Database Actions.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.21
 */
trait DatabaseTableTrait
{
    /**
     * @return \yii\db\Connection
     */
    public function getDatabaseComponent()
    {
        return $this->app->db;
    }

    /**
     * Create a table if not exists
     *
     * @param string $table
     * @param array $columns
     */
    public function createTableIfNotExists($table, array $columns)
    {
        if ($this->getDatabaseComponent()->getTableSchema($table, true) === null) {
            $this->getDatabaseComponent()->createCommand()->createTable($table, $columns)->execute();
        }
    }

    /**
     * Drop a table if not exists.
     *
     * @param string $table
     */
    public function dropTableIfExists($table)
    {
        if ($this->getDatabaseComponent()->getTableSchema($table, true) !== null) {
            $this->getDatabaseComponent()->createCommand()->dropTable($table)->execute();
        }
    }

    /**
     * Insert row
     *
     * @param string $table
     * @param array $values
     * @return integer returns the number of rows inserted.
     */
    public function insertRow($table, array $values)
    {
        return $this->getDatabaseComponent()->createCommand()->insert($table, $values)->execute();
    }

    /**
     * Delete row
     *
     * @param string $table
     * @param array $condition
     * @return integer returns the number of rows deleted.
     */
    public function deleteRow($table, array $condition)
    {
        return $this->getDatabaseComponent()->createCommand()->delete($table, $condition)->execute();
    }
}