<?php

namespace luya\testsuite\scopes;

use Yii;
use luya\cms\models\NavItem;
use luya\helpers\Inflector;
use luya\helpers\Json;
use luya\testsuite\traits\CmsDatabaseTableTrait;

/**
 * Create a CMS Page Scope.
 * 
 * ```php
 * PageScope::run($this->app, function(PageScope $scope) {
 * 
 *     $scope
 *          >createPage('home', '@app/data/cmslayoutviewfile.php', ['content'])
*          ->addBlockAndContent(HtmlBlock::class, 'content', [
 *             'html' => '<p>foobar</p>',
 *          ]);
 *
 *     $page = NavItemPage::findOne($scope->pageId);
 *     $content = $page->getContent();
 *
 *     $this->assertSame('<h1>view file</h1>p>foobar</p>', $content);
 * });
 * ```
 * 
 * 
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.21
 */
class PageScope extends BaseScope
{
    use CmsDatabaseTableTrait;

    protected $navContainerFixture;
    
    protected $navFixture;

    protected $navItemFixture;

    protected $navItemPageFixture;

    protected $logFixture;

    protected $layoutFixture;

    protected $blockFixture;

    protected $navItemPageBlockItemFixture;

    protected $navItemRedirectFixture;

    protected $navItemModuleFixture;

    protected $propertyFixture;

    /**
     * @var integer The page id which will be created.
     */
    public $pageId = 1;

    /**
     * @var array An array with fixture data for page properties
     * @since 1.0.22
     */
    public $pagePropertyFixtureData = [];

    /**
     * Returns the application database componenet.
     *
     * @return \yii\db\Connection
     */
    public function getDatabaseComponent()
    {
        return $this->getApp()->db;
    }

    /**
     * Method to update the application config with requrired componenets.
     */
    public function updateApplicationConfig()
    {
        $this->getApp()->set('session',['class' => 'luya\testsuite\components\DummySession']);
        $this->getApp()->set('cache', ['class' => 'yii\caching\DummyCache']);
        $this->getApp()->set('db', ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:']);
    }

    /**
     * Create a CMS Page with title, path to layout file and layout file placholders.
     *
     * @param string $title
     * @param string $layoutViewFile The path to the cmslayout file, for example: `@app/views/cmslayouts/main.php`.
     * @param array $layoutPlaceholders An array only containing the available placeholders like: `['content', 'sidebar']`.
     * @return self
     */
    public function createPage($title, $layoutViewFile, array $layoutPlaceholders)
    {
        $json = [];
        foreach ($layoutPlaceholders as $c) {
            $json[] = ['label' => $c, 'var' => $c];
        }
        $this->layoutFixture = $this->createCmsLayoutFixture([
            'layout1' => [
                'id' => 1,
                'name' => 'layout1',
                'view_file' => $layoutViewFile,
                'json_config' => Json::encode(['placeholders' => [$json]]),
            ]
        ]);

        $this->navContainerFixture = $this->createCmsNavContainerFixture([
            'container1' => [
                'id' => 1,
                'name' => 'container',
                'alias' => 'container',
            ],
        ]);

        $this->navFixture = $this->createCmsNavFixture([
            'nav1' => [
                'id' => 1,
                'nav_container_id' => 1,
                'parent_nav_id' => 0,
                'sort_index' => 1,
                'is_deleted' => 0,
                'is_hidden' => 0,
                'is_offline' => 0,
                'is_home' => 1,
                'is_draft' => 0,
                'layout_file' => null,
                'publish_from' => null,
                'publish_till' => null,
            ]
        ]);

        $this->navItemFixture = $this->createCmsNavItemFixture([
            'navItemPage1' => [
                'id' => 1,
                'nav_id' => 1,
                'lang_id' => 1,
                'nav_item_type' => NavItem::TYPE_PAGE,
                'nav_item_type_id' => 1,
                'create_user_id' => 0,
                'update_user_id' => 0,
                'timestamp_create' => 123123,
                'timestamp_update' => 1231231,
                'title' => $title,
                'alias' => Inflector::slug($title),
                'description' => $title,
                'keywords' => $title,
                'title_tag' => $title,
                'image_id' => 0,
                'is_url_strict_parsing_disabled' => 0,
            ],
            'navItemPage1' => [
                'id' => 2,
                'nav_id' => 1,
                'lang_id' => 1,
                'nav_item_type' => NavItem::TYPE_MODULE,
                'nav_item_type_id' => 1,
                'create_user_id' => 0,
                'update_user_id' => 0,
                'timestamp_create' => 123123,
                'timestamp_update' => 1231231,
                'title' => $title,
                'alias' => Inflector::slug($title),
                'description' => $title,
                'keywords' => $title,
                'title_tag' => $title,
                'image_id' => 0,
                'is_url_strict_parsing_disabled' => 0,
            ],
            'navItemPage1' => [
                'id' => 3,
                'nav_id' => 1,
                'lang_id' => 1,
                'nav_item_type' => NavItem::TYPE_REDIRECT,
                'nav_item_type_id' => 1,
                'create_user_id' => 0,
                'update_user_id' => 0,
                'timestamp_create' => 123123,
                'timestamp_update' => 1231231,
                'title' => $title,
                'alias' => Inflector::slug($title),
                'description' => $title,
                'keywords' => $title,
                'title_tag' => $title,
                'image_id' => 0,
                'is_url_strict_parsing_disabled' => 0,
            ],
        ]);
        
        $this->navItemPageFixture = $this->createCmsNavItemPageFixture([
            'page1' => [
                'id' => $this->pageId,
                'layout_id' => 1,
                'nav_item_id' => 1,
                'timestamp_create' => 123123,
                'create_user_id' => 0,
                'version_alias' => $title,
            ]
        ]);

        $this->navItemRedirectFixture = $this->createCmsNavItemRedirectFixture([
            'redirect1' => [
                'id' => 1,
                'type' => 1,
                'value' => 'luya.io',
                'target' => '_blank',
            ]
        ]);

        $this->navItemModuleFixture = $this->createCmsNavItemModuleFixture([
            'module1' => [
                'id' => 1,
                'module_name' => 'test',
                'controller_name' => 'test',
                'action_name' => 'test',
                'action_params' => '',
            ]
        ]);
        

        $this->propertyFixture = $this->createCmsPropertyFixture($this->pagePropertyFixtureData);

        return $this;
    }

    /**
     * Add a cms block to the list of blocks.
     *
     * @param string $blockClass
     * @return Block
     */
    public function addBlock($blockClass)
    {
        $model = $this->blockFixture->newModel;
        $model->group_id = 1;
        $model->class = $blockClass;
        $model->is_disabled = 0;
        $model->save();

        return $model;
    }

    /**
     * Add the content for a given block id.
     *
     * @param integer $blockId
     * @param string $layoutPlacholderVar
     * @param array $values
     * @param array $cfgs
     * @return self
     */
    public function addContent($blockId, $layoutPlacholderVar, array $values = [], array $cfgs = [])
    {
        /** @var \luya\cms\models\NavItemPageBlockItem $model */
        $model = $this->navItemPageBlockItemFixture->newModel;
        $model->json_config_values = $values;
        $model->json_config_cfg_values = $cfgs;
        $model->block_id = $blockId;
        $model->placeholder_var = $layoutPlacholderVar;
        $model->nav_item_page_id = 1;
        $model->prev_id = 0;
        $model->create_user_id = 0;
        $model->update_user_id = 0;
        $model->is_dirty = 0;
        $model->is_hidden = 0;
        $model->save();

        return $this;
    }

    /**
     * Combination of add block and add content.
     *
     * @param string $blockClass
     * @param string $layoutPlacholderVar
     * @param array $values
     * @param array $cfgs
     * @return self
     */
    public function addBlockAndContent($blockClass, $layoutPlacholderVar, array $values = [], array $cfgs = [])
    {
        $block = $this->addBlock($blockClass);
        return $this->addContent($block->id, $layoutPlacholderVar, $values, $cfgs);
    }

    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        $this->updateApplicationConfig();
        $this->logFixture = $this->createCmsLog([]);
        $this->blockFixture = $this->createCmsBlockFixture([]);
        $this->navItemPageBlockItemFixture = $this->createCmsNavItemPageBlockItemFixture([]);
        $this->ngRestLogFixture = $this->createAdminNgRestLogFixture();
    }

    /**
     * {@inheritDoc}
     */
    public function cleanup()
    {
        $this->blockFixture->cleanup();
        $this->navItemPageBlockItemFixture->cleanup();
        $this->logFixture->cleanup();
        $this->cleanupFixture($this->navContainerFixture);
        $this->cleanupFixture($this->navFixture);
        $this->cleanupFixture($this->navItemFixture);
        $this->cleanupFixture($this->navItemPageFixture);
        $this->cleanupFixture($this->navItemRedirectFixture);
        $this->cleanupFixture($this->navItemModuleFixture);
    }
}