<?php

namespace luya\testsuite\scopes;

use Yii;
use luya\cms\models\NavItem;
use luya\helpers\Inflector;
use luya\helpers\Json;
use luya\testsuite\traits\CmsDatabaseTableTrait;

/**
 * Create a CMS Page Scoped view:
 * 
 * ```php
 * PageScope::run($this->app, function(PageScope $scope) {
 * 
 *           $scope
 *               ->createPage('home', '@app/data/cmslayoutviewfile.php', ['content'])
 *               ->addBlockAndContent(HtmlBlock::class, 'content', [
 *                   'html' => '<p>foobar</p>',
 *               ]);
 *
 *           $page = NavItemPage::findOne($scope->pageId);
 *           $content = $page->getContent();
 *
 *           $this->assertSameTrimmed('<h1>view file</h1>
 *
 *           <p>foobar</p>', $content);
 * });
 * ```
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

    /**
     * Returns the application database componenet.
     *
     * @return \yii\db\Connection
     */
    public function getDatabaseComponent()
    {
        return $this->getApp()->db;
    }

    public $pageId = 1;

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
            'navItem1' => [
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
            ]
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

        return $this;
    }

    protected $blockFixture;

    public function addBlock($blockClass)
    {
        $model = $this->blockFixture->newModel;
        $model->group_id = 1;
        $model->class = $blockClass;
        $model->is_disabled = 0;
        $model->save();

        return $model;
    }

    protected $navItemPageBlockItem;

    public function addContent($blockId, $layoutPlacholderVar, array $values = [], array $cfgs = [])
    {
        /** @var \luya\cms\models\NavItemPageBlockItem $model */
        $model = $this->navItemPageBlockItem->newModel;
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

        return $model->save();
    }

    public function addBlockAndContent($blockClass, $layoutPlacholderVar, array $values = [], array $cfgs = [])
    {
        $block = $this->addBlock($blockClass);
        return $this->addContent($block->id, $layoutPlacholderVar, $values, $cfgs);
    }

    public function prepare()
    {
        $this->logFixture = $this->createCmsLog([]);
        $this->blockFixture = $this->createCmsBlockFixture([]);
        $this->navItemPageBlockItem = $this->createCmsNavItemPageBlockItemFixture([]);
        $this->ngRestLogFixture = $this->createNgRestLogFixture();
    }

    public function cleanup()
    {
        $this->blockFixture->cleanup();
        $this->navItemPageBlockItem->cleanup();
        $this->logFixture->cleanup();
        $this->cleanupFixture($this->navContainerFixture);
        $this->cleanupFixture($this->navFixture);
        $this->cleanupFixture($this->navItemFixture);
        $this->cleanupFixture($this->navItemPageFixture);
    }
}