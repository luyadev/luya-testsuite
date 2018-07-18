<?php

namespace luya\testsuite\tests\cases;

use luya\testsuite\cases\NgRestTestCase;

final class NgRestTestCaseTest extends NgRestTestCase
{
    public $modelClass = 'luya\testsuite\tests\data\NgRestTestModel';
    
    public $modelFixtureData = [
        'model1' => [
            'id' => 1,
            'user_id' => 1,
            'group_id' => 1,
        ],
    ];
    
    public $apiClass = 'luya\testsuite\tests\data\NgRestTestApi';
    
    public $controllerClass = 'luya\testsuite\tests\data\NgRestTestController';
    
    public function getConfigArray()
    {
        return [
            'id' => 'ngresttest',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                        'class' => 'yii\db\Connection',
                        'dsn' => 'sqlite::memory:',
                    ]
            ]
        ];
    }
    
    public function testAssertion()
    {
        $model1 = $this->modelFixture->getModel('model1');
        $this->assertSame(1, $model1->id);
        
        // do inserts, updates or deletions with the model
        // $model = $this->modelFixture->newModel;
    }
}
