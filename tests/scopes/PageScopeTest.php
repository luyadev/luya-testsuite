<?php

namespace luya\testsuite\tests\scopes;

use luya\cms\frontend\blocks\HtmlBlock;
use luya\cms\models\NavItemPage;
use luya\testsuite\cases\WebApplicationTestCase;
use luya\testsuite\scopes\PageScope;

class PageScopeTest extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'pagescope',
            'basePath' => dirname(__DIR__),
            'modules' => [
                'admin' => 'luya\admin\Module',
                'cms' => 'luya\cms\frontend\Module',
                'cmsadmin' => 'luya\cms\admin\Module',
            ],
            'components' => [
                'db' => [
                        'class' => 'yii\db\Connection',
                        'dsn' => 'sqlite::memory:',
                    ]
            ]
        ];
    }

    public function testCreatePage()
    {
        PageScope::run($this->app, function(PageScope $scope) {
            $scope->createPage('home', '@app/data/viewfile.php', ['content'])->addBlockAndContent(HtmlBlock::class, 'content', [
                'html' => '<p>foobar</p>',
            ]);

            $this->assertInstanceOf('yii\db\Connection', $scope->getDatabaseComponent());

            $page = NavItemPage::findOne($scope->pageId);

            $scope->createCmsBlockGroupFixture([]);
            $scope->createCmsNavPermissionFixture([]);
            $scope->createCmsRedirectFixture([]);
            $scope->createCmsPropertyFixture([]);
            $scope->createAdminLangFixture([]);

            $this->assertSameTrimmed('<h1>view file</h1>

            <p>foobar</p>', $page->getContent());
        });
    }
}