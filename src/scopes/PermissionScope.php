<?php

namespace luya\testsuite\scopes;

use luya\testsuite\traits\AdminDatabaseTableTrait;
use yii\base\Application;
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
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.20
 */
class PermissionScope
{
    use AdminDatabaseTableTrait;

    private $_fn;

    private $_invoke;

    private $_app;

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
     * Permission Scope contstructor.
     *
     * @param Application $app
     * @param callable $fn
     * @param callable $invoke
     */
    public function __construct(Application $app, callable $fn, callable $invoke = null)
    {
        $this->_app = $app;
        $this->_invoke = $invoke;
        $this->_fn = $fn;    
    }

    /**
     * Returns the application database componenet.
     *
     * @return \yii\db\Connection
     */
    public function getDatabaseComponent()
    {
        return $this->_app->db;
    }

    // route permissions

    /**
     * Create a route in permission system
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
        $this->_app->set('session',['class' => 'yii\web\CacheSession']);
        $this->_app->set('cache', ['class' => 'yii\caching\DummyCache']);
        $this->_app->set('adminuser', ['class' => 'luya\admin\components\AdminUser', 'enableSession' => false]);
        $this->_app->set('db', ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:']);
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
        return $this->_app->adminuser->login($this->userFixture->getModel('user'));
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
        $this->_app->controller = $controller;

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
            $this->_app->request->setQueryParams(['access-token' => $accessToken]);
        } else {
            $this->_app->request->setQueryParams(['access-token' => null]);
        }
    }

    /**
     * This method is called before the callback runs in order to prepare and setup the permission scope.
     */
    public function prepare()
    {
        if ($this->_invoke) {
            call_user_func_array($this->_invoke, [$this]);
        }
        
        // ensure the given and required application components are available.
        $this->updateApplicationConfig();

        $this->userFixture = $this->createUserFixture([
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
        $this->groupFixture = $this->createGroupFixture($this->groupId);
        $this->userOnlineFixture = $this->createUserOnlineFixture();
        $this->ngRestLogFixture = $this->createNgRestLogFixture();

        $this->createAdminUserGroupTable();
        $this->createAdminGroupAuthTable();
        $this->createAdminAuthTable();
        $this->createAdminUserAuthNotificationTable();

        $this->userGroupId = $this->insertRow('admin_user_group', [
            'user_id' => $this->userId,
            'group_id' => $this->groupId,
        ]);
    }

    /**
     * Run the provided callable function
     *
     * @param PermissionScope $scope
     * @return mixed
     */
    public function runCallable(PermissionScope $scope)
    {
        return call_user_func_array($this->_fn, [$scope]);
    }

    /**
     * Clean up tables and fixtures.
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
    
    /**
     * Run a given function inside a permission scope.
     *
     * @param yii\base\Application $app
     * @param callable $fn The function to run.
     * @param callable $invoke The function to configure the scope.
     * @return mixed
     */
    public static function run(Application $app, callable $fn, callable $invoke = null)
    {
        $scope = new self($app, $fn, $invoke);
        $scope->prepare();
        $response = $scope->runCallable($scope);
        $scope->cleanup();
        return $response;
    }
}