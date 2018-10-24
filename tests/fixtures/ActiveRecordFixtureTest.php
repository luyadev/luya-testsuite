<?php

namespace luya\testsuite\tests\fixtures;

use luya\testsuite\cases\BaseTestSuite;
use luya\base\Boot;
use luya\testsuite\fixtures\ActiveRecordFixture;

class ActiveRecordFixtureTest extends BaseTestSuite
{
    public function getConfigArray()
    {
        return [
            'id' => 'basetestcase',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ];
    }
    
    public function bootApplication(Boot $boot)
    {
        $boot->applicationWeb();
    }
    
    public function testLoadSchemaFromRules()
    {
        $model = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'removeSafeAttributes' => true,
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
                'group_id' => 1,
            ]]
        ]);

        $this->assertSame([
            'user_id' => 'integer',
            'group_id' => 'integer',
            'text' => 'text',
            'is_deleted' => 'boolean',
            'switch' => 'integer',
        ], $model->getSchema());
        
        // try to add new record
        
        $user = $model->getNewModel();
        $user->attributes = ['id' => 2, 'user_id' => 1, 'group_id' => 1];
        $this->assertTrue($user->insert());
        
        // try to load data from model with fixture
        
        $select = $model->getModel('model1');
        
        $this->assertSame(1, $select->id);
        
        $model->cleanup();
    }

    public function testLoadSchemaFromRulesWithSafe()
    {
        $model = new ActiveRecordFixture([
            'modelClass' => 'luya\testsuite\tests\data\TestModel',
            'ignoreColumns' => ['group_id'],
            'fixtureData' => ['model1' => [
                'id' => 1,
                'user_id' => 1,
            ]]
        ]);

        $this->assertSame([
            'user_id' => 'integer',
            'text' => 'text',
            'is_deleted' => 'boolean',
            'switch' => 'integer',
            'hidden' => 'text',
        ], $model->getSchema());
        
        $model->cleanup();
    }
}
