<?php

namespace luya\testsuite\commands;

use luya\console\Command;
use luya\helpers\FileHelper;
use luya\helpers\Inflector;
use Yii;
use yii\db\Connection;
use yii\db\TableSchema;
use yii\db\Query;
use yii\di\Instance;

/**
 * Generate Fixtures.
 * 
 * @since 1.0.25
 * @author Basil Suter <basil@nadar.io>
 */
class GenerateFixtureController extends Command
{
    const MODE_MODEL = 'model';

    const MODE_TABLE = 'table';

    /**
     * @var Connection|string
     */
    public $db = 'db';

    /**
     * @var string The mode which will be taken to genrate the fixture either model or table.
     */
    public $mode;

    /**
     * @var string If mode is table, the name of the table to generate the fixture.
     */
    public $table;

    /**
     * @var string If mode is model, the full class name (path) to the model, like: `app/models/MyUserModel`.
     */
    public $model;

    /**
     * @var boolean Whether the data of the current table should be added in getData() section or not.
     */
    public $data;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * {@inheritDoc}
     */
    public function options($actionID)
    {
        return array_merge(['db', 'model', 'table', 'model', 'data'], parent::options($actionID));
    }

    /**
     * Generate the Fixture.
     *
     * @return void
     */
    public function actionIndex()
    {
        if ($this->mode === null) {
            $this->mode = $this->select('Table or Model (ActiveRecord) based Fixture', [
                self::MODE_MODEL => 'Model (Active Record)',
                self::MODE_TABLE => 'Table',
            ]);
        }
        if ($this->table === null && $this->mode == self::MODE_TABLE) {
            $this->table = $this->prompt("Enter the database table name:");
        }

        if ($this->model === null && $this->mode == self::MODE_MODEL) {
            $this->model = $this->prompt("Path to model class (\app\models\MyModel):");
        }

        if ($this->table === null && $this->mode == self::MODE_MODEL) {
            $this->table = $this->model::tableName();
        }

        if ($this->data === null) {
            $this->data = $this->confirm("Add current table data?", true);
        }

        $className = $this->mode == self::MODE_TABLE ? $this->table : $this->model;

        $schema = $this->getSchema($this->table);
        $className = $this->generateClassName($className);
        $fixtureClassContent = $this->generateClassFile(
            $schema,
            $className,
            $this->generateData($schema, $this->table),
            $this->model,
            $this->table,
            $this->data
        );

        $folder = Yii::getAlias('@app/tests/fixtures');
        $filePath = Yii::getAlias('@app/tests/fixtures/'.$className.'.php');

        if (FileHelper::createDirectory($folder) && FileHelper::writeFile($filePath, $fixtureClassContent)) {
            return $this->outputSuccess("fixture file {$filePath} has been created.");
        }

        return $this->outputError("Error while generting fixture.");
    }

    /**
     * Get Schema
     *
     * @param string $table
     * @return TableSchema
     */
    public function getSchema($table)
    {
        return $this->db->getTableSchema($table);
    }

    /**
     * Gnerate class name
     *
     * @param string $className
     * @return string
     */
    public function generateClassName($className)
    {
        return Inflector::classify($className.'Fixture');
    }

    /**
     * Generate the view
     *
     * @param TableSchema $schema
     * @param [type] $className
     * @param array $data
     * @param [type] $modelClass
     * @param [type] $tableName
     * @return string
     */
    public function generateClassFile(TableSchema $schema, $className, array $data, $modelClass = null, $tableName = null, $addData)
    {
        return $this->view->renderFile(__DIR__ . '/views/generate-fixture.php', [
            'schema' => $schema,
            'data' => $data,
            'className' => $className,
            'modelClass' => $modelClass,
            'tableName' => $tableName,
            'addData' => $addData,
        ]);
    }

    /**
     * Generate fixture data
     *
     * @param TableSchema $schema
     * @param string $table
     * @return array
     */
    public function generateData(TableSchema $schema, $table)
    {
        $data = [];
        foreach ((new Query)->from($table)->all($this->db) as $index => $items) {
            $data[$this->primaryKeyValue($schema, $items, $index)] = $items;
        }

        return $data;
    }

    /**
     * Get the primary key value for a given items row
     *
     * @param TableSchema $schema
     * @param array $items
     * @param string $defaultValue
     * @return string
     */
    protected function primaryKeyValue(TableSchema $schema, array $items, $defaultValue)
    {
        $values = [];
        foreach ($schema->primaryKey as $keyName) {
            if (array_key_exists($keyName, $items)) {
                $values[] = $items[$keyName];
            }
        }

        return empty($values) ? $defaultValue : implode("-", $values);
    }
}