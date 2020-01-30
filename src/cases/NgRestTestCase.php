<?php

namespace luya\testsuite\cases;

use yii\base\InvalidConfigException;
use yii\db\Exception as DbException;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\base\Boot;
use luya\helpers\ArrayHelper;
use luya\testsuite\fixtures\NgRestModelFixture;
use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\base\Controller;

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
 * How to call an API Endpoint:
 *
 * ```php
 * public function testMyEndpoints()
 * {
 *     // test a custom api endpoint (action) with auth checks
 *     $this->runControllerAction($this->api, 'test'); // where test is the action name.
 *
 *     // or you can access this action directly without auth checks.
 *     $this->api->actionTest();
 *
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.10
 */
abstract class NgRestTestCase extends WebApplicationTestCase
{
    use AdminDatabaseTableTrait;

    /**
     * @const int
     *
     * @since 1.0.14
     */
    const ID_AUTH_API = 1;
    
    /**
     * @const int
     *
     * @since 1.0.14
     */
    const ID_AUTH_CONTROLLER = 2;

    /**
     * @const int
     *
     * @since 1.0.14
     */
    const ID_USER_TESTER = 1;
    
    /**
     * @const int
     *
     * @since 1.0.14
     */
    const ID_GROUP_TESTER = 1;

    /**
     * @const int
     *
     * @since 1.0.14
     */
    const ID_GROUP_AUTH_API = 1;
    
    /**
     * @const int
     *
     * @since 1.0.14
     */
    const ID_GROUP_AUTH_CONTROLLER = 2;
    
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
     * @var \luya\admin\ngrest\base\Api
     */
    protected $api;
    
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var string
     * @since 1.0.14
     */
    protected $controllerId;

    /**
     * @var NgRestModelFixture
     */
    protected $modelFixture;

    /**
     * @var NgRestModelFixture
     */
    protected $userFixture;
    
    /**
     * @var ActiveRecordFixture
     */
    protected $userOnlineFixture;
    
    /**
     * @var ActiveRecordFixture
     */
    protected $userGroupFixture;
    
    /**
     * @var ActiveRecordFixture
     */
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
            $modelClass = $this->modelClass;
            $this->api = new $class($modelClass::ngRestApiEndpoint(), $this->app);
        }
        
        if ($this->controllerClass) {
            $class = $this->controllerClass;
            $className = preg_replace('/^.*\\\/', '', $class::className());
            $this->controllerId = strtolower(str_replace('Controller', '', $className));
            $this->controller = new $class($this->controllerId, $this->app);
        }
        
        $this->mockBasicAdminModels();
    }
    
    /**
     * Basic admin module env model mocking.
     */
    protected function mockBasicAdminModels()
    {
        $this->createAdminUserGroupTable();
        $this->createAdminGroupAuthTable();
        $this->createAdminAuthTable();
        $this->createAdminUserAuthNotificationTable();
        $this->createAdminUserLoginLockoutFixture();
        
        // user
        $this->userFixture = $this->createAdminUserFixture([
            'user1' => [
                'id' => self::ID_USER_TESTER,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john@example.com',
                'is_deleted' => 0,
                'is_api_user' => true,
                'api_last_activity' => time(),
                'auth_token' => 'TestAuthToken',
                'is_request_logger_enabled' => false,
            ]
        ]);
       
        // user group
        $this->userGroupFixture = $this->createAdminGroupFixture(self::ID_GROUP_TESTER);
        
        // login the user
        $this->app->adminuser->login($this->userFixture->getModel('user1'));
        
        // user online table
        $this->userOnlineFixture = $this->createAdminUserOnlineFixture();
        
        // ngrest logger
        $this->ngrestLogFixture = $this->createAdminNgRestLogFixture();

        $this->insertRow('admin_user_group', [
            'user_id' => self::ID_USER_TESTER,
            'group_id' => self::ID_GROUP_TESTER,
        ]);

        $apiEndpoint = $this->modelClass::ngRestApiEndpoint();
        list(, , $alias) = explode('-', $apiEndpoint);
        
        $this->insertRow('admin_auth', [
            'id' => self::ID_AUTH_API,
            'alias_name' => $alias,
            'module_name' => $this->app->id,
            'is_crud' => 1,
            'api' => $apiEndpoint,
        ]);
        
        $this->insertRow('admin_auth', [
            'id' => self::ID_AUTH_CONTROLLER,
            'module_name' => $this->app->id,
            'is_crud' => 0,
        ]);
    }
    
    /**
     * {@inheritDoc}
     */
    public function bootApplication(Boot $boot)
    {
        // ensure the admin module is registered, if not do so.
        $config = ArrayHelper::merge([
            'modules' => [
                'admin' => ['class' => 'luya\admin\Module']
            ],
            'components' => [
                'session' => ['class' => 'luya\testsuite\components\DummySession'],
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
        $this->assertTrue(is_array($this->modelFixture->newModel->attributeHints()));
        $this->assertTrue(is_array($this->modelFixture->newModel->ngRestScopes()));
        $this->assertTrue(is_array($this->modelFixture->newModel->ngRestActiveButtons()));
        $this->assertTrue(is_array($this->modelFixture->newModel->ngRestActiveWindows()));
        $this->assertTrue(is_array($this->modelFixture->newModel->ngRestRelations()));
        
        if ($this->api) {
            $this->assertInstanceOf('luya\admin\ngrest\base\NgRestModel', $this->api->model);
            
            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'unlock');
            //$this->assertNull($this->api->actionUnlock());

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'services');
            //$this->api->actionServices();

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'search', ['foo']);
            //$this->api->actionSearch('foo');

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'relation-call', [1, 'foo', 'none']);
            //$this->api->actionRelationCall(1, 'foo', 'none');

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'filter', ['none']);
            //$this->api->actionFilter('none');

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'active-window-callback');
            //$this->api->actionActiveWindowCallback();

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'active-window-render');
            //$this->api->actionActiveWindowRender();

            $this->expectException('yii\web\ForbiddenHttpException');
            $this->runControllerAction($this->api, 'export');
            //$this->api->actionExport();
        }
        
        if ($this->controller) {
            $this->assertInstanceOf('luya\admin\ngrest\base\NgRestModel', $this->controller->getModel());
            $this->assertContains('<script>zaa.bootstrap.register', $this->controller->actionIndex());
        }
    }
    
    /**
     * {@inheritDoc}
     */
    public function beforeTearDown()
    {
        parent::beforeTearDown();
        
        $this->modelFixture->cleanup();
        $this->userFixture->cleanup();
        $this->userGroupFixture->cleanup();
        $this->userOnlineFixture->cleanup();
        $this->ngrestLogFixture->cleanup();

        $this->dropAdminAuthTable();
        $this->dropAdminGroupAuthTable();
        $this->dropAdminUserGroupTable();
        $this->dropAdminUserAuthNotificationTable();
    }

    /**
     * Run a certain action insdie a controller, whether its an api or "controller" context.
     *
     * ```php
     * $this->runControllerAction($this->api, 'index');
     * ```
     *
     * Would run the index action of the API Controller.
     *
     * @param Controller $controller
     * @param string $action
     * @param array $params
     * @param boolean $permission
     * @return mixed
     * @since 1.0.18
     */
    protected function runControllerAction(Controller $controller, $action, array $params = [], $permission = true)
    {
        $this->app->controller = $controller;

        if ($permission) {
            $this->setQueryAuthToken();
        }

        return $controller->runAction($action, $params);
    }

    /**
     * Set the query parameter as auth token.
     *
     * @param boolean $value
     * @param string $token The token to set as query param, if not defined the token from user model user1 will be taken.
     * @since 1.0.18
     */
    protected function setQueryAuthToken($value = true, $token = null)
    {
        if ($value) {
            $accessToken = $token ? $token : $this->userFixture->getModel('user1')->auth_token;
            $this->app->request->setQueryParams(['access-token' => $accessToken]);
        } else {
            $this->app->request->setQueryParams(['access-token' => null]);
        }
    }

    /**
     * Disables api access for test user
     *
     * @since 1.0.14
     */
    protected function removeApiPermissions()
    {
        $this->app->db->createCommand()->delete('admin_group_auth', ['id' => self::ID_GROUP_AUTH_API])->execute();
    }

    /**
     * Helps to initialize api access permissions
     *
     * @since 1.0.14
     */
    protected function resetApiPermissions($create = false, $update = false, $delete = false)
    {
        $state = [
            'crud_create' => (int)$create,
            'crud_update' => (int)$update,
            'crud_delete' => (int)$delete,
        ];

        $this->app->db->createCommand()->upsert(
            'admin_group_auth',
            ArrayHelper::merge([
            'id' => self::ID_GROUP_AUTH_API,
            'group_id' => self::ID_GROUP_TESTER,
            'auth_id' => self::ID_AUTH_API,
        ], $state),
            $state
        )->execute();
        return $this;
    }

    /**
     * Gives the test user list api permission or removes access
     *
     * @since 1.0.14
     */
    protected function apiCanList($value = true)
    {
        if (!$value) {
            return $this->removeApiPermissions();
        }
        try {
            $this->app->db->createCommand()->insert('admin_group_auth', [
                'id' => self::ID_GROUP_AUTH_API,
                'group_id' => self::ID_GROUP_TESTER,
                'auth_id' => self::ID_AUTH_API,
                'crud_create' => 0,
                'crud_update' => 0,
                'crud_delete' => 0,
            ])->execute();
        } catch (DbException $e) {
            // permission is initialized, so having list access already
        }
        return $this;
    }

    /**
     * Gives the test user create api permission or removes it
     *
     * @since 1.0.14
     */
    protected function apiCanCreate($value = true)
    {
        $this->app->db->createCommand()->upsert('admin_group_auth', [
            'id' => self::ID_GROUP_AUTH_API,
            'group_id' => self::ID_GROUP_TESTER,
            'auth_id' => self::ID_AUTH_API,
            'crud_create' => (int)$value,
            'crud_update' => 0,
            'crud_delete' => 0,
        ], [
            'crud_create' => (int)$value,
        ])->execute();
        return $this;
    }

    /**
     * Gives the test user update api permission or removes it
     *
     * @since 1.0.14
     */
    protected function apiCanUpdate($value = true)
    {
        $this->app->db->createCommand()->upsert('admin_group_auth', [
            'id' => self::ID_GROUP_AUTH_API,
            'group_id' => self::ID_GROUP_TESTER,
            'auth_id' => self::ID_AUTH_API,
            'crud_create' => 0,
            'crud_update' => (int)$value,
            'crud_delete' => 0,
        ], [
            'crud_update' => (int)$value,
        ])->execute();
        return $this;
    }

    /**
     * Gives the test user delete api permission or removes it
     *
     * @since 1.0.14
     */
    protected function apiCanDelete($value = true)
    {
        $this->app->db->createCommand()->upsert('admin_group_auth', [
            'id' => self::ID_GROUP_AUTH_API,
            'group_id' => self::ID_GROUP_TESTER,
            'auth_id' => self::ID_AUTH_API,
            'crud_create' => 0,
            'crud_update' => 0,
            'crud_delete' => (int)$value,
        ], [
            'crud_delete' => (int)$value,
        ])->execute();
        return $this;
    }

    /**
     * Gives the test user access to the controller action
     *
     * @since 1.0.14
     */
    protected function controllerCanAccess($actionId, $value = true)
    {
        $this->app->db->createCommand()->update('admin_auth', [
            'alias_name' => $actionId,
            'route' => implode('/', [$this->app->id, $this->controllerId, $actionId]),
        ], [
            'id' => self::ID_AUTH_CONTROLLER
        ])->execute();
        if ($value) {
            $this->app->db->createCommand()->insert('admin_group_auth', [
                'id' => self::ID_GROUP_AUTH_CONTROLLER,
                'group_id' => self::ID_GROUP_TESTER,
                'auth_id' => self::ID_AUTH_CONTROLLER,
            ])->execute();
        } else {
            $this->app->db->createCommand()->delete('admin_group_auth', [
                'id' => self::ID_GROUP_AUTH_CONTROLLER,
            ])->execute();
        }
    }
}
