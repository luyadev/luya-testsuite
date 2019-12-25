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

class GenerateFixtureController extends Command
{
    const MODE_MODEL = 'model';

    const MODE_TABLE = 'table';

    /**
     * @var Connection|string
     */
    public $db = 'db';

    public $table;

    public $mode;

    public $model;

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

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

        $className = $this->mode == self::MODE_TABLE ? $this->table : $this->model;

        $schema = $this->getSchema($this->table);
        $className = Inflector::classify($className.'Fixture');
        $fixtureClassContent = $this->generateClassFile($schema, $className, $this->generateData($schema, $this->table), $this->model, $this->table);

        $folder = Yii::getAlias('@app/tests');
        $filePath = Yii::getAlias('@app/tests/'.$className.'.php');

        if (FileHelper::createDirectory($folder) && FileHelper::writeFile($filePath, $fixtureClassContent)) {
            return $this->outputSuccess("fixture file {$filePath} has been created.");
        }
    }

    public function getSchema($table)
    {
        return $this->db->getTableSchema($table);
    }

    public function generateClassFile(TableSchema $schema, $className, array $data, $modelClass = null, $tableName = null)
    {
        return $this->view->renderFile(__DIR__ . '/views/generate-fixture.php', [
            'schema' => $schema,
            'data' => $data,
            'className' => $className,
            'modelClass' => $modelClass,
            'tableName' => $tableName,
        ]);
    }

    public function generateData(TableSchema $schema, $table)
    {
        $data = [];
        foreach ((new Query)->from($table)->all($this->db) as $index => $items) {
            $data[$this->primaryKeyValue($schema, $items, $index)] = $items;
        }

        return $data;
    }

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