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
 *          $assert = PermissionScope::run($this->app, function($scope) use ($controller) {
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
 *      PermissionScope::run($this->app, function($scope) use ($api) {
 *          $scope->createApiAndAllow('api-test-action', true, true, false); // create the route and also allocate the permission create and update but not delete.
 * 
 *          $this->expectException('yii\web\ForbiddenHttpRequest');
 *          $scope->runControllerAction($api, 'delete', 'DELETE');
 *      });
 * }
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

    // cfg

    public $userId = 1;

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

    public function __construct(Application $app, callable $fn, callable $invoke = null)
    {
        $this->_app = $app;
        $this->_invoke = $invoke;
        $this->_fn = $fn;    
    }

    public function getDatabaseComponent()
    {
        return $this->_app->db;
    }

    public function prepare()
    {
        if ($this->_invoke) {
            call_user_func_array($this->_invoke, [$this]);
        }

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

    // route permissions

    private $_routeAuthId;

    public function createRoute($route)
    {
        $this->_routeAuthId = $this->addPermissionRoute($route);
    }

    public function createAndAllowRoute($route)
    {
        $this->createRoute($route);
        $this->allowRoute($route);
    }

    public function removeRoute($route)
    {
        return $this->removePermissionRoute($route);
    }

    public function allowRoute($route)
    {
        return $this->assignGroupAuth($this->groupId, $this->_routeAuthId);
    }

    public function denyRoute($route)
    {
        return $this->unAssignGroupAuth($this->groupId, $this->_routeAuthId);
    }

    // api permissions

    private $_apiAuthId;

    public function createApi($api)
    {
        $this->_apiAuthId = $this->addPermissionApi($api, true);
    }

    public function createAndAllowApi($api, $canCreate = true, $canUpdate = true, $canDelete = true)
    {
        $this->createApi($api);
        $this->allowApi($api, $canCreate, $canUpdate, $canDelete);
    }

    public function removeApi($api)
    {
        return $this->removePermissionApi($api);
    }

    public function allowApi($api, $canCreate = true, $canUpdate = true, $canDelete = true)
    {
        return $this->assignGroupAuth($this->groupId, $this->_apiAuthId, $canCreate, $canUpdate, $canDelete);
    }

    public function denyApi($api)
    {
        return $this->unAssignGroupAuth($this->groupId, $this->_apiAuthId);
    }

    // scope methods

    public function updateApplicationConfig()
    {
        $this->_app->set('session',['class' => 'yii\web\CacheSession']);
        $this->_app->set('cache', ['class' => 'yii\caching\DummyCache']);
        $this->_app->set('adminuser', ['class' => 'luya\admin\components\AdminUser', 'enableSession' => false]);
    }

    public function loginUser()
    {
        return $this->_app->adminuser->login($this->userFixture->getModel('user'));
    }

    /**
     * Undocumented function
     *
     * @param Controller $controller
     * @param [type] $action
     * @param array $params
     * @param string $method GET, POST, HEAD, PUT, PATCH, DELETE
     * @return void
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

    public function setQueryAuthToken($value = true, $token = null)
    {
        if ($value) {
            $accessToken = $token ? $token : $this->userFixture->getModel('user')->auth_token;
            $this->_app->request->setQueryParams(['access-token' => $accessToken]);
        } else {
            $this->_app->request->setQueryParams(['access-token' => null]);
        }
    }

    public function runCallable(PermissionScope $scope)
    {
        return call_user_func_array($this->_fn, [$scope]);
    }

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
    
    public static function run($db, callable $fn, callable $invoke = null)
    {
        $scope = new self($db, $fn, $invoke);
        $scope->prepare();
        $scope->updateApplicationConfig();
        $response = $scope->runCallable($scope);
        $scope->cleanup();
        return $response;
    }
}