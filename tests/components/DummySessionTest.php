<?php

namespace luya\testsuite\tests\components;

use luya\base\Boot;
use luya\testsuite\cases\BaseTestSuite;
use luya\testsuite\components\DummySession;

class DummySessionTest extends BaseTestSuite
{
    public function getConfigArray()
    {
        return [
            'id' => 'basetestcase',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ];
    }

    public function bootApplication(Boot $boot)
    {
        $boot->applicationConsole();
    }

    public function testComponent()
    {
        $comp = new DummySession();
        $comp->open();
        $comp->set('foo', 'bar');
        $this->assertSame('bar', $comp->get('foo'));
        $comp->remove('foo');
        $this->assertNull($comp->get('foo'));
    }
}