<?php

namespace luya\testsuite\components;

use luya\cms\Menu;
use luya\helpers\ArrayHelper;
use luya\helpers\Inflector;
use luya\testsuite\traits\CmsDatabaseTableTrait;

/**
 * LUYA CMS Dummy Menu Component.
 * 
 * A Component to create dummy menus using $items property for each language.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 1.0.30
 */
class DummyMenu extends Menu
{
    use CmsDatabaseTableTrait;

    /**
     * @var array An array with items for each language.
     * 
     * ```php
     * 'items' => [
     *     'de' => [
     *          1 => 'Hello',
     *          2 => 'World',
     *     ]
     * ],
     * ```
     * 
     * or with more specific infos:
     * 
     * 'items' => [
     *     'de' => [
     *          [
     *              'id' => 1,
     *              'title' => 'Hello',
     *              'is_home' => 1,
     *              'items' => [
     *                  [
     *                      'id' => 2,
     *                      'title' => 'World',
     *                      'link' => 'hello-world-link',
     *                  ]
     *              ]
     *          ],
     *     ],
     * ],
     * ```
     */
    public $items = [];

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        $langFixtures  = [];
        $langId = 1;
        foreach ($this->items as $lang => $items) {
            $langFixtures[] = [
                'id' => $langId,
                'short_code' => $lang,
                'name' => $lang,
                'is_default' => $langId == 1 ? 1 : 0,
                'is_deleted' => 0,
            ];
            $langId++;
            $this->setLanguageContainer($lang, $this->normalizeItems($items, $lang));
        }
        $this->createAdminLangFixture($langFixtures);
        parent::init();
    }

    /**
     * Generate a flat menu structure from items.
     *
     * @param array $items
     * @param string $lang
     * @param integer $parentNavId
     * @return array
     */
    protected function normalizeItems(array $items, $lang, $parentNavId = 0)
    {
        $data = [];
        foreach ($items as $key => $item) {

            if (!is_array($item)) {
                $item = ['id' => $key, 'title' => $item];
            }

            $normalized = $this->itemToStructure($item, $lang, $parentNavId);
            $data[$normalized['id']] = $normalized;

            if (isset($item['items'])) {
                $data = ArrayHelper::merge($data, $this->normalizeItems($item['items'], $lang, $item['id']));
            }

        }

        return $data;
    }

    /**
     * Generate Menu structure for item.
     *
     * @param string $item
     * @param string $lang
     * @param integer $parentNavId
     * @return array
     */
    protected function itemToStructure($item, $lang, $parentNavId)
    {
        $id = ArrayHelper::getValue($item, 'id');
        $title = ArrayHelper::getValue($item, 'title');
        return [
            'id' => $id,
            'nav_id' => ArrayHelper::getValue($item, 'nav_id', $id),
            'lang' => ArrayHelper::getValue($item, 'lang', $lang),
            'parent_nav_id' => ArrayHelper::getValue($item, 'parent_nav_id', $parentNavId),
            'link' => ArrayHelper::getValue($item, 'link', Inflector::slug($title)),
            'title' => $title,
            'alias' => ArrayHelper::getValue($item, 'alias', Inflector::slug($title)),
            'description' => ArrayHelper::getValue($item, 'description', ''),
            'keywords' => ArrayHelper::getValue($item, 'keywords', ''),
            'create_user_id' => ArrayHelper::getValue($item, 'create_user_id', 1),
            'update_user_id' => ArrayHelper::getValue($item, 'update_user_id', 1),
            'timestamp_create' => 1457091369,
            'timestamp_update' => 1483367249,
            'is_home' => ArrayHelper::getValue($item, 'is_home', 0),
            'sort_index' => ArrayHelper::getValue($item, 'sort_index', $id),
            'is_hidden' => ArrayHelper::getValue($item, 'is_hidden', 0),
            'type' => 1, // page
            'nav_item_type_id' => $id,
            'redirect' => false,
            'module_name' => false,
            'container' => ArrayHelper::getValue($item, 'container', 'default'),
            'depth' => ArrayHelper::getValue($item, 'depth', 1),
            'image_id' => ArrayHelper::getValue($item, 'image_id', 0),
            'is_url_strict_parsing_disabled' => 1,
        ];
    }
}