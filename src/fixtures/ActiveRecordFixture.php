<?php

namespace luya\testsuite\fixtures;

use yii\test\ActiveFixture;
use luya\helpers\ArrayHelper;

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
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->createTable();
        $this->createColumns();
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
            $this->_primaryKey = ['id' => 'INT(11) PRIMARY KEY'];
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
            list ($attributes, $rule) = $row;
            
            foreach ((array) $attributes as $name) {
                $fields[$name] = 'text';
            }
        }
        
        // remove primary keys
        foreach ($this->primaryKey as $key => $value) {
            ArrayHelper::remove($fields, $key);
        }
        
        // try to find from labels
        return $fields;
    }
    
    /**
     * Create the table based on the schema.
     */
    public function createTable()
    {
        $fields = [];
        
        foreach ($this->getPrimaryKey() as $key => $value) {
            $fields[$key] = $value;
        }
        $class = $this->modelClass;
        $this->db->createCommand()->createTable($class::tableName(), $fields)->execute();
    }
    
    /**
     * Add columns to table.
     */
    public function createColumns()
    {
        $class = $this->modelClass;
        $tableName = $class::tableName();
        
        foreach ($this->getSchema() as $column => $type) {
            $tableColumns = $this->db->schema->getTableSchema($tableName, true);
            if (!$tableColumns->getColumn($column)) {
                $this->db->createCommand()->addColumn($tableName, $column, $type)->execute();
            }
        }
        
        $this->load();
    }
    
    /**
     * Cleanup active record fixture.
     */
    public function cleanup()
    {
        $class = $this->modelClass;
        $tableName = $class::tableName();
        $this->db->createCommand()->dropTable($tableName);
    }
}
