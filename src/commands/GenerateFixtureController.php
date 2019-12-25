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
    /**
     * @var Connection|string
     */
    public $db = 'db';

    public $table;

    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    public function actionIndex()
    {
        if ($this->table === null) {
            $this->table = $this->prompt("Enter table name");
        }

        $schema = $this->db->getTableSchema($this->table);
        $className = Inflector::classify($this->table.'Fixture');
        $classFile = $this->view->renderFile(__DIR__ . '/views/generate-fixture.php', [
            'schema' => $schema,
            'data' => $this->generateData($schema, $this->table, $this->db),
            'className' => $className,
            'modelClass' => 'ModelClassName',
        ]);

        
        $folder = Yii::getAlias('@app/tests');
        $filePath = Yii::getAlias('@app/tests/'.$className.'.php');
        if (FileHelper::createDirectory($folder) && FileHelper::writeFile($filePath, $classFile)) {

        }
    }

    protected function generateData(TableSchema $schema, $table, $db)
    {
        $data = [];
        foreach ((new Query)->from($this->table)->all($this->db) as $index => $items) {
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