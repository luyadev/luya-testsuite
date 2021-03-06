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
                'urlManager' => [
                    'cache' => null,
                ],
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

        

        $r = PermissionScope::run($this->app, function(PermissionScope $scope) {
            $controller = new NgRestTestController('testid', $this->app);
            $this->assertSame(1000, $scope->userId);

            $scope->createAndAllowRoute('foobar');
            $scope->denyRoute('foobar');
            $scope->removeRoute('foobar');

            $scope->createAndAllowApi('barfoo');
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
        

        PermissionScope::run($this->app, function(PermissionScope $scope) {
            $api = new NgRestTestApi('testapi', $this->app);
            // this might change with version 2.2 as then its a forbidden exception.
            $this->expectException('yii\web\ForbiddenHttpException');
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