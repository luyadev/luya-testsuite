<?php

namespace luya\testsuite\tests\scopes;

use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\scopes\PermissionScope;

class PermissionScopeTest extends WebApplicationTestCase
{
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

    public function testBuild()
    {
        $this->assertSame('foo', PermissionScope::run($this->app->db, function() {
            return 'foo';
        }));
    }
}