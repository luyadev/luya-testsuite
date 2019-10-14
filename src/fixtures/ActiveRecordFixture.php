<?php

namespace luya\testsuite\fixtures;

use yii\test\ActiveFixture;
use luya\helpers\ArrayHelper;
use yii\db\sqlite\Schema;

/**
 * Active Record Fixture.
 *
 * Provides a very basic way to generate a database schema for the given table based on the rules or via
 * $schema definition. So you don't have to create migrations or sql files. Just provide the $modelClass
 * to the ActiveRecord you'd like to test.
 *
 * When working with NgRestModels you can also use {{luya\testsuite\fixtures\NgRestModelFixture}} or maybe
 * checkout {{luya\testsuite\cases\NgRestTestCase}} which makes model testing even more easy, as it will auto
 * setup the sqlite connection.
 *
 * The only requirement for the ActiveRecordFixture is to have the sqlite database
 * connection enabled. You could use {{luya\testsuite\cases\NgRestTestCase}} which ensures
 * this behavior by default.
 *
 * ```php
 * 'components' => [
 *     'db' => [
 *         'class' => 'yii\db\Connection',
 *         'dsn' => 'sqlite::memory:',
 *     ]
 * ]
 * ```
 *
 * ActiveRecordFixture usage example:
 *
 * ```php
 * $fixture = new ActiveRecordFixture([
 *     'modelClass' => 'luya\testsuite\tests\data\TestModel', // path to the model
 *     'fixtureData' => ['model1' => [
 *         'id' => 1,
 *         'user_id' => 1,
 *         'group_id' => 1,
 *     ]]
 * ]);
 *
 * // insert new model
 * $newModel = $fixture->newModel;
 * $newModel->attributes = ['user_id' => 1, 'group_id' => '1];
 * $newModel->save();
 *
 * // or return the defined $fixtureData model
 * $model = $fixture->getModel('model1'); // definde in `$fixtureData`
 * ```
 *
 * By default the primary key `id` is used as (in general) the primary key wont appear in the
 * rules defintion. You can override the default primary key defintion with the $primarKey property:
 *
 * ```php
 * $model = new ActiveRecordFixture([
 *     'modelClass' => 'luya\testsuite\tests\data\TestModel',
 *     'primaryKey' => ['my_primary_key' => 'int(11) PRIMARY KEY'],
 *     'fixtureData' => ['model1' => [
 *          'id' => 1,
 *          'user_id' => 1,
 *          'group_id1' => 1,
 *     ]]
 * ]);
 * ```
 *
 * > In order to support compisite primary keys on sqlite you have to define primaryKey property as followed:
 * > `'primaryKey' => ['user_id' => 'int(11)', 'group_id' => 'int(11)', 'PRIMARY KEY (user_id, group_id)']`
 * 
 * An example which provides data and schema from a extended fixture class:
 * 
 * ```php
 * class MyApiModelFixture extends ActiveRecordFixture
 * {
 *     public $modelClass = MyApiModel::class;
 * 
 *     public function getSchema()
 *     {
 *         return [
 *             'id' => 'int(11)',
 *             'access_token' => 'text',
 *             'name' => 'text',
 *         ];
 *     }
 * 
 *     public function getData()
 *     {
 *         return [
 *             1 => [
 *                 'id' => 1,
 *                 'access_token' => '123123123',
 *                 'name' => 'masterapi',
 *             ]
 *         ];
 *     }
 * }
 * ```
 *
 * @property array $schema
 * @property array $primaryKey
 * @property array $data
 * @property \yii\db\ActiveRecord $newModel
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.10
 */
class ActiveRecordFixture extends ActiveFixture
{
    const RULE_TYPE_BOOLEAN = 'boolean';
    const RULE_TYPE_INTEGER = 'integer';
    const RULE_TYPE_SAFE = 'safe';

    /**
     * @var array An array with columns which will be ignored when creating the table columns.
     * @since 1.0.13.1
     */
    public $ignoreColumns = [];

    /**
     * @var boolean Disabled the default behavior to remove safe attributes as wrongly introduced in 1.0.13. By default this behavior must be disable. If enabled
     * all safe attribute rules column will ignored when creating the table.
     * @since 1.0.13.1
     */
    public $removeSafeAttributes = false;

    /**
     * @var boolean If the table already exists while creating, the creation process will be skipped. This can be the case if any other test
     * created the table but cleanup was not executed therefore an "table exists already" exception can be prevented.
     * @since 1.0.16
     */
    public $skipIfExists = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->build();
    }

    /**
     * Create table, create columns from schema and load fixture data.
     *
     * @since 1.0.16
     */
    public function build()
    {
        // create table schema
        $this->createTable();
        // create
        $this->createColumns();
        // load fixture data into the model
        $this->load();
    }
    
    /**
     * Cleanup (drop table) and then rebuild (create) the table.
     *
     * @since 1.0.15
     * @see {{build()}}
     * @see {{cleanup()}}
     */
    public function rebuild()
    {
        $this->cleanup();
        $this->build();
    }

    /**
     * Create instance of the model class.
     *
     * @return \yii\db\ActiveRecord
     */
    public function getNewModel()
    {
        $class = $this->modelClass;
        
        return new $class();
    }
    
    private $_primaryKey;
    
    /**
     * Example
     *
     * ```
     * 'primaryKey' => ['id' => 'INT(11) PRIMARY KEY],
     * ```
     *
     * @param array $primaryKey
     */
    public function setPrimaryKey(array $primaryKey)
    {
        $this->_primaryKey = $primaryKey;
    }
    
    /**
     * Returns the primary key name(s) for this AR class.
     * The default implementation will return the primary key(s) as declared
     * in the DB table that is associated with this AR class.
     *
     * If the DB table does not declare any primary key, you should override
     * this method to return the attributes that you want to use as primary keys
     * for this AR class.
     *
     * Note that an array should be returned even for a table with single primary key.
     *
     * @return string|array the primary keys of the associated database table.
     */
    public function getPrimaryKey()
    {
        if ($this->_primaryKey === null) {
            $this->_primaryKey = ['id' => Schema::TYPE_PK];
        }
        
        return $this->_primaryKey;
    }
    
    private $_data = [];
    
    /**
     *
     * @param array $data
     */
    public function setFixtureData(array $data)
    {
        $this->_data = $data;
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \yii\test\ActiveFixture::getData()
     */
    public function getData()
    {
        return $this->_data;
    }
    
    private $_schema;
    
    /**
     *
     * @param array $schema
     */
    public function setSchema(array $schema)
    {
        $this->_schema = $schema;
    }
    
    public function getSchema()
    {
        // this allows even empty arrays to override.
        if (empty($this->_schema)) {
            $this->_schema = $this->createSchemaFromRules();
        }

        return $this->_schema;
    }
    
    /**
     * Create the database scheme based on the rules as attributes and fields are not available
     * as they are virtual properties from the table definition.
     */
    public function createSchemaFromRules()
    {
        $object = $this->getNewModel();
        
        $fields = [];
        foreach ($object->rules() as $row) {
            list($attributes, $rule) = $row;
            
            // if a none scalar value like callable or object, skip this rule
            if (!is_scalar($rule)) {
                continue;
            }

            $rule = strtolower($rule);

            foreach ((array) $attributes as $name) {
                if ($rule == self::RULE_TYPE_BOOLEAN) {
                    $fields[$name] = Schema::TYPE_BOOLEAN;
                } elseif ($rule == self::RULE_TYPE_INTEGER) {
                    $fields[$name] = Schema::TYPE_INTEGER;
                }

                if (!isset($fields[$name])) {
                    $fields[$name] = Schema::TYPE_TEXT;
                }

                if ($this->removeSafeAttributes && $rule == self::RULE_TYPE_SAFE) {
                    // remove safe validators fields as they are commonly used for setter getter
                    if (isset($fields[$name])) {
                        unset($fields[$name]);
                    }
                }
            }
        }
        
        // remove primary keys
        foreach ($this->primaryKey as $key => $value) {
            ArrayHelper::remove($fields, $key);
        }

        foreach ($fields as $name => $type) {
            if (in_array($name, $this->ignoreColumns)) {
                unset($fields[$name]);
            }
        }
        
        // try to find from labels
        return $fields;
    }

    /**
     * Decided whether table name should be resolved from property or model (if $tableName is empty).
     *
     * @return string
     * @since 1.0.23
     */
    public function getTableName()
    {
        $class = $this->modelClass;
        return $this->tableName ? $this->tableName : $class::tableName();
    }
    
    /**
     * Create the table based on the schema with primary keys
     */
    public function createTable()
    {
        $fields = [];
        
        foreach ($this->getPrimaryKey() as $key => $value) {
            $fields[$key] = $value;
        }
        // if skipIfExists is enabled, the table will only be created when the table does notexists.
        if ($this->skipIfExists) {
            if (!$this->db->schema->getTableSchema($this->getTableName())) {
                $this->db->createCommand()->createTable($this->getTableName(), $fields)->execute();
            }
        } else {
            $this->db->createCommand()->createTable($this->getTableName(), $fields)->execute();
        }
    }
    
    /**
     * Add columns to table.
     */
    public function createColumns()
    {
        foreach ($this->getSchema() as $column => $type) {
            $tableColumns = $this->db->schema->getTableSchema($this->getTableName(), true);
            if (!$tableColumns->getColumn($column)) {
                $this->db->createCommand()->addColumn($this->getTableName(), $column, $type)->execute();
            }
        }
    }
    
    /**
     * Cleanup active record fixture.
     */
    public function cleanup()
    {
        $class = $this->modelClass;
        $tableName = $this->getTableName();
        // ensure the table exists before dropping
        if ($this->db->schema->getTableSchema($tableName)) {
            $this->db->createCommand()->dropTable($tableName)->execute();
        }
    }
}
