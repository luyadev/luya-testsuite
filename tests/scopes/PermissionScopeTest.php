<?php

namespace luya\testsuite\tests\scopes;

use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\scopes\PermissionScope;
use luya\testsuite\tests\data\NgRestTestApi;
use luya\testsuite\tests\data\NgRestTestController;

class PermissionScopeTest extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'ngresttest',
            'basePath' => dirname(__DIR__),
            'modules' => [
                'admin' => 'luya\admin\Module',
            ],
            'components' => [
                'db' => [
                        'class' => 'yii\db\Connection',
                        'dsn' => 'sqlite::memory:',
                    ]
            ]
        ];
    }

    public function testBuild()
    {
        $this->assertSame('foo', PermissionScope::run($this->app, function() {
            return 'foo';
        }));

        $controller = new NgRestTestController('testid', $this->app);

        $r = PermissionScope::run($this->app, function(PermissionScope $scope) use ($controller) {
            $this->assertSame(1000, $scope->userId);

            $scope->createRoute('foobar');
            $scope->createAndAllowRoute('foobar');
            $scope->allowRoute('foobar');
            $scope->denyRoute('foobar');
            $scope->removeRoute('foobar');

            $scope->createApi('barfoo');
            $scope->createAndAllowApi('barfoo');
            $scope->allowApi('barfoo');
            $scope->denyApi('barfoo');

            $scope->removeApi('barfoo');

            $scope->loginUser();

            $this->expectException('yii\base\InvalidConfigException');
            $r = $scope->runControllerAction($controller, 'index');

        }, function(PermissionScope $scope) {
            $scope->userId = 1000;
        });
    }

    public function testApiDeleteMethod()
    {
        $api = new NgRestTestApi('testapi', $this->app);

        PermissionScope::run($this->app, function(PermissionScope $scope) use($api) {
            // this might change with version 2.2 as then its a forbidden exception.
            $this->expectException('yii\base\InvalidConfigException');
            $scope->runControllerAction($api, 'delete', ['id' => 1], 'DELETE');
        });
    }

    public function testCustomToken()
    {
        PermissionScope::run($this->app, function(PermissionScope $scope) {
            $scope->setQueryAuthToken(false);
            $this->assertNull($this->app->request->getQueryParams()['access-token']); 
        });
    }
}