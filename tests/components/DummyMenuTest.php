<?php

namespace luya\testsuite\tests\components;

use luya\base\Boot;
use luya\testsuite\cases\BaseTestSuite;
use luya\testsuite\components\DummyMenu;
use luya\testsuite\components\DummySession;

class DummyMenuTest extends BaseTestSuite
{
    public function getConfigArray()
    {
        return [
            'id' => 'basetestcase',
            'basePath' => dirname(__DIR__),
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

    public function bootApplication(Boot $boot)
    {
        $boot->applicationWeb();
    }

    public function testComponent()
    {
        $menu = new DummyMenu($this->app->request, [
            'items' => [
                'en' => [
                    1 => 'Foo',
                ]
            ]
        ]);

        $this->assertSame([
            1 => [
                'id' => 1,
                'nav_id' => 1,
                'lang' => 'en',
                'parent_nav_id' => 0,
                'link' => 'foo',
                'title' => 'Foo',
                'alias' => 'foo',
                'description' => '',
                'keywords' => '',
                'create_user_id' => 1,
                'update_user_id' => 1,
                'timestamp_create' => 1457091369,
                'timestamp_update' => 1483367249,
                'is_home' => 0,
                'sort_index' => 1,
                'is_hidden' => 0,
                'type' => 1,
                'nav_item_type_id' => 1,
                'redirect' => false,
                'module_name' => false,
                'container' => 'default',
                'depth' => 1,
                'image_id' => 0,
                'is_url_strict_parsing_disabled' => 1,
            ]
        ], $menu->getLanguageContainer('en'));
    }
}