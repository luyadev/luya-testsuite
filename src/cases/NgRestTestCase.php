<?php

namespace luya\testsuite\cases;

use yii\base\InvalidConfigException;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\base\Boot;
use luya\helpers\ArrayHelper;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\admin\models\User;
use luya\admin\models\UserOnline;
use luya\admin\models\Group;

/**
 * NgRest Test Case.
 * 
 * The NgRestTestCase allows you to test the 
 * 
 * + model
 * + api
 * + controller
 * 
 * The api and controller tests are optional. The basic tests is just doing the some basic execution
 * test to see if properties and methods does have values and does not return any php exception or
 * parse error.
 * 
 * Example usage:
 * 
 * ```php
 * class NgRestTestCaseTest extends NgRestTestCase
 * {
 *     public $modelClass = 'luya\testsuite\tests\data\NgRestTestModel';
 *     
 *     public $apiClass = 'luya\testsuite\tests\data\NgRestTestApi';
 *   
 *     public $controllerClass = 'luya\testsuite\tests\data\NgRestTestController';
 *     
 *     public $modelFixtureData = [
 *         'model1' => [
 *             'id' => 1,
 *             'user_id' => 1,
 *             'group_id' => 1,
 *         ],
 *     ];
 *     
 *     public function getConfigArray()
 *     {
 *         return [
 *             'id' => 'ngresttest',
 *             'basePath' => dirname(__DIR__),
 *             'components' => [
 *                 'db' => [
 *                         'class' => 'yii\db\Connection',
 *                         'dsn' => 'sqlite::memory:',
 *                     ]
 *             ]
 *         ];
 *     }
 *     
 *     public function testAssertion()
 *     {
 *         $model1 = $this->modelFixture->getModel('model1');
 *         $this->assertSame(1, $model1->id);
 *         
 *         // do inserts, updates or deletions with the model
 *         // $model = $this->modelFixture->newModel;
 *     }
 * }
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.9
 */
abstract class NgRestTestCase extends WebApplicationTestCase
{
    /**
     * @var string The path to the ngrest model.
     */
    public $modelClass;
    
    /**
     * @var array An array with fixture data for the model.
     */
    public $modelFixtureData = [];
    
    /**
     * @var array An array with schema defintion for the fixture model.
     */
    public $modelSchema = [];
    
    /**
     * @var string The path to the ngrest api.
     */
    public $apiClass;
    
    /**
     * @var string The path to the ngrest controller.
     */
    public $controllerClass;

    /**
     * @var \luya\testsuite\fixtures\NgRestModelFixture
     */
    protected $modelFixture;
    
    /**
     * @var \luya\admin\ngrest\base\Api
     */
    protected $api;
    
    /**
     * @var \luya\admin\ngrest\base\Controller
     */
    protected $controller;
    
    /**
     * @var \luya\testsuite\fixtures\NgRestModelFixture
     */
    protected $userFixture;
    
    /**
     * @var \luya\testsuite\fixtures\ActiveRecordFixture
     */
    protected $userOnlineFixture;
    
    /**
     * @var \luya\testsuite\fixtures\NgRestModelFixture
     */
    protected $userGroupFixture;
    
    public function afterSetup()
    {
        parent::afterSetup();
        
        if (!$this->modelClass) {
            throw new InvalidConfigException("The 'modelClass' property can not be empty.");
        }
        
        $this->modelFixture = new ActiveRecordFixture([
            'modelClass' => $this->modelClass, 
            'fixtureData' => $this->modelFixtureData,
            'schema' => $this->modelSchema,
        ]);
        
        if ($this->apiClass) {
            $class = $this->apiClass;
            $this->api = new $class('api', $this->app);
        }
        
        if ($this->controllerClass) {
            $class = $this->controllerClass;
            $this->controller = new $class('controller', $this->app);
        }
        
        $this->mockBasicAdminModels();
    }
    
    /**
     * Basic admin module env model mocking.
     */
    protected function mockBasicAdminModels()
    {
        // user
        $this->userFixture = new NgRestModelFixture([
            'modelClass' => User::class,
            'schema' => [
                'firstname' => 'text',
                'lastname' => 'text',
                'email' => 'text',
                'is_deleted' => 'int(11)'
            ],
            'fixtureData' => [
                'user1' => [
                    'id' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john@example.com',
                    'is_deleted' => 0
                ]
            ]
        ]);
       
        // generate raw tables for missing active records
        $this->app->db->createCommand()->createTable('admin_user_group', ['id' => 'INT(11) PRIMARY KEY', 'user_id' => 'int(11)', 'group_id' => 'int(11)'])->execute();
        $this->app->db->createCommand()->createTable('admin_group_auth', ['id' => 'INT(11) PRIMARY KEY', 'group_id' => 'int(11)', 'auth_id' => 'int(11)', 'crud_create' => 'int(11)', 'crud_update' => 'int(11)'])->execute();
        $this->app->db->createCommand()->createTable('admin_auth', ['id' => 'INT(11) PRIMARY KEY', 'alias_name' => 'text', 'module_name' => 'text', 'is_crud' => 'int(11)', 'route' => 'text', 'api' => 'text'])->execute();
        
        // user group
        $this->userGroupFixture = new NgRestModelFixture(['modelClass' => Group::class]);
        
        // login the user
        $this->app->adminuser->login($this->userFixture->getModel('user1'));
        
        // user online table
        $this->userOnlineFixture = new ActiveRecordFixture(['modelClass' => UserOnline::class]);
    }
    
    /**
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::bootApplication()
     */
    public function bootApplication(Boot $boot)
    {
        // ensure the admin module is registered, if not do so.
        $config = ArrayHelper::merge([
            'modules' => [
                'admin' => ['class' => 'luya\admin\Module']
            ],
            'components' => [
                'session' => ['class' => 'yii\web\CacheSession'],
                'cache' => ['class' => 'yii\caching\DummyCache'],
                'adminuser' => ['class' => 'luya\admin\components\AdminUser', 'enableSession' => false],
            ]
        ], $boot->getConfigArray());
        
        // set the new config.
        $boot->setConfigArray($config);
        
        // boot the application
        $boot->applicationWeb();
    }
    
    /**
     * Basic Tests
     */
    public function testBasicNgRestMethods()
    {
        $class = $this->modelClass;
        
        $this->assertNotNull($class::ngRestApiEndpoint());
        $this->assertNotNull($class::tableName());
        $this->assertTrue(is_array($this->modelFixture->newModel->ngRestAttributeTypes()));
        $this->assertNotNull($this->modelFixture->newModel->attributeLabels());
        $this->assertTrue(is_array($this->modelFixture->newModel->ngRestScopes()));
        
        if ($this->api) {
            $this->assertInstanceOf('luya\admin\ngrest\base\NgRestModel', $this->api->model);
            $this->assertNull($this->api->actionUnlock());
            
            /*
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionServices();
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionSearch('foo');
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionSearchProvider();
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionSearchHiddenFields();
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionFullResponse();
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionRelationCall(1, 'foo', 'none');
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionFilter('none');
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionActiveWindowCallback();
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionActiveWindowRender();
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->api->actionExport();
            */
        }
        
        if ($this->controller) {
            $this->assertInstanceOf('luya\admin\ngrest\base\NgRestModel', $this->controller->getModel());
            $this->assertContains('<script>zaa.bootstrap.register', $this->controller->actionIndex());
        }
    }
}