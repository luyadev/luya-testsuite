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
use luya\admin\models\NgrestLog;

/**
 * NgRest Test Case.
 *
 * The NgRestTestCase extends the {{luya\testsuite\fixture\NgRestModelFixture}} by auto setup the right
 * Database connection and allows you to test main components Model, API and Controller very easy.
 *
 * The API and Controller tests are optional, this means you don't have to provide {{$apiClass}} or
 * {{$controllerClass}} in order to setup the test case correctly. The basic tests is just doing
 * some basic execution test to see if properties and methods does have values and does not return any
 * php exception, parse or runtime error.
 *
 * With this test case you can easy access the Model, API and Controller object in order to test your
 * custom functionality.
 *
 * ```php
 * public function testSomeCustomFunctions()
 * {
 *     // accessing the controller object
 *     $this->assertSame('FooBar', $this->controller->actionFooBar()); // runs the action method `actionFooBar`
 *
 *     // accessing the api object
 *     $this->assertSame('FooBar', $this->api->actionFooBar()); // runs the action method `actionFooBar`
 * }
 * ```
 *
 * Full example usage and definition:
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
 * @since 1.0.10
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
    
    protected $ngrestLogFixture;
    
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
                'is_deleted' => 'int(11)',
                'is_api_user' => 'boolean'
            ],
            'fixtureData' => [
                'user1' => [
                    'id' => 1,
                    'firstname' => 'John',
                    'lastname' => 'Doe',
                    'email' => 'john@example.com',
                    'is_deleted' => 0,
                    'is_api_user' => true
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
        
        // ngrest logger
        $this->ngrestLogFixture = new ActiveRecordFixture(['modelClass' => NgrestLog::class]);
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
                'db' => ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:'],
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
    
    public function beforeTearDown()
    {
        parent::beforeTearDown();
        
        $this->modelFixture->cleanup();
        $this->userFixture->cleanup();
        $this->userGroupFixture->cleanup();
        $this->userOnlineFixture->cleanup();
        $this->ngrestLogFixture->cleanup();
    }
}
