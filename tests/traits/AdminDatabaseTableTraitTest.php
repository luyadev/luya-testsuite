<?php

namespace luya\testsuite\tests\traits;

use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\traits\AdminDatabaseTableTrait;

class AdminDatabaseTableTraitTest extends WebApplicationTestCase
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

    public function testCreateTable()
    {
        $sub = new AdminDatabaseTableTraitStub();
        $sub->app = $this->app;

        $this->assertEmpty($sub->createTableIfNotExists('foobar', ['id' => 'text']));
        $this->assertEmpty($sub->createTableIfNotExists('foobar', ['id' => 'text']));

        $this->assertSame(1, $sub->insertRow('foobar', ['id' => 'test']));
        $this->assertSame(1, $sub->insertRow('foobar', ['id' => 'test']));

        $this->assertSame(2, $sub->deleteRow('foobar', ['id' => 'test']));

        $this->assertEmpty($sub->dropTableIfExists('foobar'));
        $this->assertEmpty($sub->dropTableIfExists('foobar'));

        $this->assertEmpty($sub->createAdminQueueTable());
    }
}

class AdminDatabaseTableTraitStub
{
    public $app;
    use AdminDatabaseTableTrait;
}