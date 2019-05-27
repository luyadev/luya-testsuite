<?php

namespace luya\testsuite\tests\cases;

use luya\testsuite\cases\NgRestTestCase;

final class NgRestTestCaseTest extends NgRestTestCase
{
    public $modelClass = 'luya\testsuite\tests\data\NgRestTestModel';
    
    public $modelFixtureData = [
        'model1' => [
            'id' => 1,
            'user_id' => 1,
            'group_id' => 1,
        ],
    ];
    
    public $apiClass = 'luya\testsuite\tests\data\NgRestTestApi';
    
    public $controllerClass = 'luya\testsuite\tests\data\NgRestTestController';
    
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
    
    public function testAssertion()
    {
        $model1 = $this->modelFixture->getModel('model1');
        $this->assertSame(1, $model1->user_id);
        $this->assertSame(1, $model1->group_id);
        
        // do inserts, updates or deletions with the model
        // $model = $this->modelFixture->newModel;
    }

    public function testAfterSetup_authDataInitialized()
    {
        $users = $this->app->db->createCommand('SELECT * FROM admin_user')->queryAll();
        $groups = $this->app->db->createCommand('SELECT * FROM admin_group')->queryAll();
        $userGroups = $this->app->db->createCommand('SELECT * FROM admin_user_group')->queryAll();
        $auth = $this->app->db->createCommand('SELECT * FROM admin_auth')->queryAll();
        
        $this->assertEquals(1, count($users));
        $this->assertEquals(self::ID_USER_TESTER, $users[0]['id']);
        $this->assertEquals(1, count($groups));
        $this->assertEquals(self::ID_GROUP_TESTER, $groups[0]['id']);
        $this->assertEquals(1, count($userGroups));
        $this->assertEquals(self::ID_USER_TESTER, $userGroups[0]['user_id']);
        $this->assertEquals(self::ID_GROUP_TESTER, $userGroups[0]['group_id']);
        $this->assertEquals(2, count($auth));
        $this->assertEquals(self::ID_AUTH_API, $auth[0]['id']);
        $this->assertEquals(self::ID_AUTH_CONTROLLER, $auth[1]['id']);
    }

    /**
     * @dataProvider resetApiPermissionsDataProvider
     */
    public function testResetApiPermissions($create, $update, $delete)
    {
        $this->resetApiPermissions($create, $update, $delete);

        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();

        $this->assertEquals(1, count($perm));
        $this->assertEquals((int)$create, $perm[0]['crud_create']);
        $this->assertEquals((int)$update, $perm[0]['crud_update']);
        $this->assertEquals((int)$delete, $perm[0]['crud_delete']);
    }

    public function resetApiPermissionsDataProvider()
    {
        return [
            [true, false, false],
            [false, true, false],
            [false, false, true],
            [true, false, true],
            [true, true, true],
            [false, false, false],
        ];
    }

    public function testResetApiPermissions_noParams_listAccess()
    {
        $this->resetApiPermissions();

        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();

        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testRemoveApiPermissions()
    {
        $this->resetApiPermissions();

        $this->removeApiPermissions();

        $row = $this->app->db->createCommand('SELECT * FROM admin_group_auth')->queryOne();
        $this->assertEquals(null, $row);
    }

    public function testApiCanList_insert()
    {
        $this->apiCanList();
        
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();

        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanList_update_doesntChange()
    {
        $this->resetApiPermissions(true);
        
        $this->apiCanList();
        
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(1, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanList_remove()
    {
        $this->resetApiPermissions();

        $this->apiCanList(false);
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(0, count($perm));
    }

    public function testApiCanCreate_insert()
    {
        $this->apiCanCreate();
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(1, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanCreate_update()
    {
        $this->resetApiPermissions(false, true);
        
        $this->apiCanCreate();
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(1, $perm[0]['crud_create']);
        $this->assertEquals(1, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanCreate_remove()
    {
        $this->resetApiPermissions(true);
        
        $this->apiCanCreate(false);
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }
    
    public function testApiCanUpdate_insert()
    {
        $this->apiCanUpdate();
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(1, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanUpdate_update()
    {
        $this->resetApiPermissions(true, false);
        
        $this->apiCanUpdate();
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(1, $perm[0]['crud_create']);
        $this->assertEquals(1, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanUpdate_remove()
    {
        $this->resetApiPermissions(false, true);
        
        $this->apiCanUpdate(false);
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanDelete_insert()
    {
        $this->apiCanDelete();
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(1, $perm[0]['crud_delete']);
    }

    public function testApiCanDelete_update()
    {
        $this->resetApiPermissions(true);
        
        $this->apiCanDelete();
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(1, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(1, $perm[0]['crud_delete']);
    }

    public function testApiCanDelete_remove()
    {
        $this->resetApiPermissions(false, false, true);
        
        $this->apiCanDelete(false);
        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(0, $perm[0]['crud_create']);
        $this->assertEquals(0, $perm[0]['crud_update']);
        $this->assertEquals(0, $perm[0]['crud_delete']);
    }

    public function testApiCanChain()
    {
        $this->apiCanCreate()->apiCanUpdate()->apiCanDelete();

        $perm = $this->app->db->createCommand('SELECT crud_create, crud_update, crud_delete FROM admin_group_auth')->queryAll();
        
        $this->assertEquals(1, count($perm));
        $this->assertEquals(1, $perm[0]['crud_create']);
        $this->assertEquals(1, $perm[0]['crud_update']);
        $this->assertEquals(1, $perm[0]['crud_delete']);
    }

    public function testControllerCanAccess()
    {
        $this->controllerCanAccess('index');

        $auth = $this->app->db->createCommand('SELECT * FROM admin_auth WHERE id = :id', ['id' => self::ID_AUTH_CONTROLLER])->queryOne();
        $perm = $this->app->db->createCommand('SELECT * FROM admin_group_auth')->queryOne();

        $this->assertTrue(is_array($auth));
        $this->assertTrue(is_array($perm));
        $this->assertEquals('ngresttest/ngresttest/index', $auth['route']);
        $this->assertEquals(self::ID_GROUP_TESTER, $perm['group_id']);
        $this->assertEquals(self::ID_AUTH_CONTROLLER, $perm['auth_id']);
    }

    public function testControllerCanAccess_remove()
    {
        $this->app->db->createCommand()->update('admin_auth', [
            'route' => 'ngresttest/ngresttest/index',
        ], [
            'id' => self::ID_AUTH_CONTROLLER
        ])->execute();
        $this->app->db->createCommand()->insert('admin_group_auth', [
            'id' => self::ID_GROUP_AUTH_CONTROLLER,
            'group_id' => self::ID_GROUP_TESTER,
            'auth_id' => self::ID_AUTH_CONTROLLER,
        ])->execute();

        $this->controllerCanAccess('index', false);

        $perm = $this->app->db->createCommand('SELECT * FROM admin_group_auth WHERE id = :id', ['id' => self::ID_AUTH_CONTROLLER])->queryOne();
        $this->assertFalse($perm);
    }
}
