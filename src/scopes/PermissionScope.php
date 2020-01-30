<?php

namespace luya\testsuite\scopes;

use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\base\Controller;
use yii\rest\Controller as RestController;

/**
 * Generate a permission Scope for a Rest Call.
 * 
 * Its recommend to have the application with the following components config:
 * 
 * ```php
 * 'modules' => [
 *      'admin' => ['class' => 'luya\admin\Module']
 * ]
 * ```
 * 
 * An example of how to test Rest Controllers with route permissions:
 * 
 * ```php
 * class MyUnitTest extends WebApplicationTestCase
 * {
 *      public function testRestController()
 *      {
 *          $controller = new RestController('id', $this->app);
 * 
 *          $assert = PermissionScope::run($this->app, function(PermissionScope $scope) use ($controller) {
 *              // there is now a permission for this route
 *              $scope->createRoute('module/controller/action');
 * 
 *              // now you can either allow this route or deny:
 *              $scope->allowRoute('module/controller/action');
 * 
 *              // which is equals to: $scope->createAndAllowRoute('module/controller/action');
 * 
 *              // now the user has permission to this route.
 *              return $scope->runControllerAction($controller, 'action');
 *          });
 * 
 *          $this->assertSame('foobar', $assert);
 *      }
 * }
 * ```
 * 
 * An example in how to test an RestActive Controllers with api permissions:
 * 
 * ```php
 * public function testRestActiveController()
 * {
 *      $api = new RestActiveController('id', $this->app);
 * 
 *      PermissionScope::run($this->app, function(PermissionScope $scope) use ($api) {
 *          $scope->createApiAndAllow('api-test-action', true, true, false); // create the route and also allocate the permission create and update but not delete.
 * 
 *          $this->expectException('yii\web\ForbiddenHttpRequest');
 *          $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');
 *      });
 * }
 * ```
 * 
 * In order to configure the scope use the second argument in run():
 * 
 * ```php
 * PermissionScop::run($this->app, function(PermissionScope $scope) {
 *     // do stuff
 * }, function(PermissionScope $setupScope) {
 *     // the setup scope allows you to configre scoped details before the call scope runs.
 *     $setupScope->userId = 1000;
 *     $scopeScope->userFixture = [
 *          'firstname' => 'Jane',
 *          'is_api_user' => false,
 *     ]
 * });
 * ```
 * 
 * An example of how to test a POST (save) action of an {{luya\admin\ngrest\base\Api}} controller with JWT enabled:
 * 
 * 
 * ```php
 * class MyNgRestTest extends NgRestTestCase
 * {
 *     public $modelClass = MyTestModel::class;
 * 
 *     public $apiClass = MyTestModelController::class;
 * 
 *     public $controllerClass = MyTestModelController::class;
 * 
 *     public function getConfigArray()
 *     {
 *         return [
 *             'id' => 'MyTestModel',
 *             'basePath' => dirname(__FILE__),
 *             'modules' => [
 *                 'admin' => [
 *                     'class' => 'luya\admin\Module',
 *                     'cors' => true
 *                 ],
 *                 'myadminmodel' => 'myadminmodel\admin\Module',
 *             ],
 *             'components' => [
 *                 'jwt' => [
 *                     'class' => 'luya\admin\components\Jwt',
 *                     'key' => '123',
 *                     'apiUserEmail' => 'unknown',
 *                     'identityClass' => 'myadminmodel\models\User',
 *                 ],
 *             ]
 *         ];
 *     }
 * 
 *     public function testSaveEntry()
 *     {
 *         $user = new NgRestModelFixture([
 *             'modelClass' => User::class,
 *         ]);
 *         $model = $user->newModel;
 *         $model->id = 1;
 *         // set the jwt identity model 
 *         $this->app->jwt->identity = $model;
 * 
 *         PermissionScope::run($this->app, function(PermissionScope $scope) {
 *             $fixture = new NgRestModelFixture([
 *                 'modelClass' => MyTestModel::class,
 *             ]);
 *             $scope->createAndAllowApi('MyTestModel');
 *             $ctrl = new MyTestModelController('MyTestModel', $this->app);
 *             $this->app->request->setBodyParams([
 *                 'field' => 'value',
 *                 // ... other post vlaues
 *             ]);
 *             $response = $scope->runControllerAction($ctrl, 'create', [], 'POST');
 *             // assert response content
 *         });
 *     }
 * }
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.20
 */
class PermissionScope extends BaseScope
{
    use AdminDatabaseTableTrait;
    
    protected $userGroupId;

    protected $userFixture;

    protected $groupFixture;

    protected $userOnlineFixture;

    protected $ngRestLogFixture;

    /**
     * @var integer The value which should be taken to generate the user.
     */
    public $userId = 1;

    /**
     * @var integer The value which should be taken to generate the group.
     */
    public $groupId = 1;

    /**
     * @var array An array which allows you to override user data for example to make user deleted:
     * 
     * ```php
     * 'userFixtureData' => [
     *     'is_deleted' => 1,
     * ]
     * ```
     * 
     * > the `id` attribute should never be provided, as its determed by {{$userId}} property.
     */
    public $userFixtureData = [];

    /**
     * Returns the application database componenet.
     *
     * @return \yii\db\Connection
     */
    public function getDatabaseComponent()
    {
        return $this->getApp()->db;
    }

    // route permissions

    /**
     * Create a route in permission system.
     * 
     * Assuming the application has id "myapp" (`['id' => 'myapp']`) and controller defined is mycontroller and the action is helloworld
     * the route would be `myapp/mycontroller/helloworld`.
     * 
     * ```php
     * $app = new Application(['id' => 'myapp']);
     * 
     * PermissionScope::run($app, function($scope) use ($app) {
     *    
     *    $controller = new MyController('mycontroller', $app);
     *    
     *    $scope->runControllerAction($controller, 'helloworld');
     * }); 
     * ```
     * 
     * The above example would nee to set:
     * 
     * ```php
     * $scope->createRoute('myapp/mycontroller/helloworld');
     * ```
     *
     * @param string $route
     * @return integer Returns the id of the admin_auth table entry.
     */
    public function createRoute($route)
    {
        return $this->addPermissionRoute(strlen($route), $route);
    }

    /**
     * Create a route in permission system and directly allow the route.
     * 
     * > Read more about details of the route and how its build in {{createRoute()}} method.
     *
     * @param string $route
     */
    public function createAndAllowRoute($route)
    {
        $this->createRoute($route);
        $this->allowRoute($route);
    }

    /**
     * Remove the route from the permission system.
     *
     * @param string $route
     */
    public function removeRoute($route)
    {
        return $this->removePermissionRoute($route);
    }

    /**
     * Allow the route for the current user and group.
     *
     * @param string $route
     */
    public function allowRoute($route)
    {
        return $this->assignGroupAuth($this->groupId, strlen($route));
    }

    /**
     * Deny the route for the given user and group (removes the group assigment).
     *
     * @param string $route
     */
    public function denyRoute($route)
    {
        return $this->unAssignGroupAuth($this->groupId, strlen($route));
    }

    // api permissions

    /**
     * Create an Api in permission system. (ActiveRestController).
     *
     * @param string $api
     * @return integer Returns the id of the admin_auth table entry.
     */
    public function createApi($api)
    {
        return $this->addPermissionApi(strlen($api), $api, true);
    }

    /**
     * Create the Api in the permission system and directly allow the api with given permissions.
     *
     * @param string $api
     * @param boolean $canCreate
     * @param boolean $canUpdate
     * @param boolean $canDelete
     */
    public function createAndAllowApi($api, $canCreate = true, $canUpdate = true, $canDelete = true)
    {
        $this->createApi($api);
        $this->allowApi($api, $canCreate, $canUpdate, $canDelete);
    }

    /**
     * Remove the api from permission system.
     *
     * @param string $api
     */
    public function removeApi($api)
    {
        return $this->removePermissionApi($api);
    }

    /**
     * Assign the Api permission to the current user and group with given permissions.
     *
     * @param string $api
     * @param boolean $canCreate
     * @param boolean $canUpdate
     * @param boolean $canDelete
     */
    public function allowApi($api, $canCreate = true, $canUpdate = true, $canDelete = true)
    {
        return $this->assignGroupAuth($this->groupId, strlen($api), $canCreate, $canUpdate, $canDelete);
    }

    /**
     * Deny the Api, which deletes the api permission entry.
     *
     * @param string $api
     */
    public function denyApi($api)
    {
        return $this->unAssignGroupAuth($this->groupId, strlen($api));
    }

    /**
     * Method to update the application config with requrired componenets.
     */
    public function updateApplicationConfig()
    {
        $this->getApp()->set('session',['class' => 'luya\testsuite\components\DummySession']);
        $this->getApp()->set('cache', ['class' => 'yii\caching\DummyCache']);
        $this->getApp()->set('adminuser', ['class' => 'luya\admin\components\AdminUser', 'enableSession' => false]);
        $this->getApp()->set('db', ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:']);
    }

    /**
     * Login the the given user into the admin user system.
     * 
     * > this is only used for WEB controller request with session based auth.
     *
     * @return boolean Whether login was successfull or not.
     */
    public function loginUser()
    {
        return $this->getApp()->adminuser->login($this->userFixture->getModel('user'));
    }

    /**
     * Make a call to a controllers action with params and request method defintion.
     *
     * @param Controller $controller
     * @param string $action
     * @param array $params
     * @param string $method GET, POST, HEAD, PUT, PATCH, DELETE
     * @return mixed
     */
    public function runControllerAction(Controller $controller, $action, array $params = [], $method = 'GET')
    {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $this->getApp()->controller = $controller;

        if ($controller instanceof RestController) {
            $this->setQueryAuthToken();
        } else {
            $this->loginUser();
            $controller->enableCsrfValidation = false;
        }
        
        return $controller->runAction($action, $params);
    }

    /**
     * Set authentification query.
     *
     * @param boolean $value
     * @param string $token
     */
    public function setQueryAuthToken($value = true, $token = null)
    {
        if ($value) {
            $accessToken = $token ? $token : $this->userFixture->getModel('user')->auth_token;
            $this->getApp()->request->setQueryParams(['access-token' => $accessToken]);
        } else {
            $this->getApp()->request->setQueryParams(['access-token' => null]);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        // ensure the given and required application components are available.
        $this->updateApplicationConfig();

        $this->userFixture = $this->createAdminUserFixture([
            'user' => array_merge([
                'id' => $this->userId,
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'john@example.com',
                'is_deleted' => 0,
                'is_api_user' => 1,
                'api_last_activity' => 12345678,
                'auth_token' => 'TestAuthToken',
                'is_request_logger_enabled' => false,
            ], $this->userFixtureData),
        ]);
        $this->groupFixture = $this->createAdminGroupFixture($this->groupId);
        $this->userOnlineFixture = $this->createAdminUserOnlineFixture();
        $this->ngRestLogFixture = $this->createAdminNgRestLogFixture();

        $this->createAdminUserGroupTable();
        $this->createAdminGroupAuthTable();
        $this->createAdminAuthTable();
        $this->createAdminUserAuthNotificationTable();
        $this->createAdminUserLoginLockoutFixture();

        $this->userGroupId = $this->insertRow('admin_user_group', [
            'user_id' => $this->userId,
            'group_id' => $this->groupId,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function cleanup()
    {
        $this->userFixture->cleanup();
        $this->groupFixture->cleanup();
        $this->userOnlineFixture->cleanup();
        $this->ngRestLogFixture->cleanup();
        $this->dropAdminAuthTable();
        $this->dropAdminGroupAuthTable();
        $this->dropAdminUserGroupTable();
        $this->dropAdminUserAuthNotificationTable();
    }
}