<?php

namespace luya\testsuite\traits;

use luya\cms\models\Block;
use luya\cms\models\BlockGroup;
use luya\cms\models\Layout;
use luya\cms\models\Log;
use luya\cms\models\Nav;
use luya\cms\models\NavContainer;
use luya\cms\models\NavItem;
use luya\cms\models\NavItemModule;
use luya\cms\models\NavItemPage;
use luya\cms\models\NavItemPageBlockItem;
use luya\cms\models\NavItemRedirect;
use luya\cms\models\NavPermission;
use luya\cms\models\Property;
use luya\cms\models\Redirect;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * A trait to make it easier to work with database tables and LUYA admin permission.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.21
 */
trait CmsDatabaseTableTrait
{
    use AdminDatabaseTableTrait;

    public function createCmsLog(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => Log::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsNavContainerFixture(array $fixtureData)
    {
        return new NgRestModelFixture([
            'modelClass' => NavContainer::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsNavFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => Nav::class,
            'fixtureData' => $fixtureData
        ]);
    }

    public function createCmsNavItemFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => NavItem::class,
            'fixtureData' => $fixtureData
        ]);
    }

    public function createCmsNavItemModuleFixture(arrray $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => NavItemModule::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsNavItemRedirectFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => NavItemRedirect::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsNavItemPageFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => NavItemPage::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsNavItemPageBlockItemFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => NavItemPageBlockItem::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsBlockFixture(array $fixtureData)
    {
        return new NgRestModelFixture([
            'modelClass' => Block::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsBlockGroupFixture(array $fixtureData)
    {
        return new NgRestModelFixture([
            'modelClass' => BlockGroup::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsLayoutFixture(array $fixtureData)
    {
        return new NgRestModelFixture([
            'modelClass' => Layout::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsNavPermissionFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => NavPermission::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsRedirectFixture(array $fixtureData)
    {
        return new NgRestModelFixture([
            'modelClass' => Redirect::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    public function createCmsPropertyFixture(array $fixtureData)
    {
        return new ActiveRecordFixture([
            'modelClass' => Property::class,
            'fixtureData' => $fixtureData,
        ]);
    }
}
