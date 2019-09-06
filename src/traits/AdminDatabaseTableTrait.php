<?php

namespace luya\testsuite\traits;

use luya\admin\models\Group;
use luya\admin\models\NgrestLog;
use luya\admin\models\User;
use luya\admin\models\UserOnline;
use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * A trait to make it easier to work with database tables and LUYA admin permission.
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.20
 */
trait AdminDatabaseTableTrait
{
    /**
     * @return \yii\db\Connection
     */
    public function getDatabaseComponent()
    {
        return $this->app->db;
    }

    // routes

    public function addPermissionRoute($route)
    {
        return $this->insertRow('admin_auth', [
            'module_name' => '@app',
            'alias_name' => $route,
            'route' => $route,
        ]);
    }

    public function removePermissionRoute($route)
    {
        return $this->deleteRow('admin_auth', [
            'route' => $route,
        ]);
    }

    public function assignGroupAuth($groupId, $authId, $canCreate = true, $canUpdate = true, $canDelete = true)
    {
        return $this->insertRow('admin_group_auth', [
            'group_id' => $groupId,
            'auth_id' => $authId,
            'crud_create' => (int) $canCreate,
            'crud_update' => (int) $canUpdate,
            'crud_delete' => (int) $canDelete,
        ]);
    }

    public function unAssignGroupAuth($groupId, $authId)
    {
        return $this->deleteRow('admin_group_auth', [
            'group_id' => $groupId,
            'auth_id' => $authId,
        ]);
    }

    // apis

    public function addPermissionApi($api, $isCrud = true)
    {
        return $this->insertRow('admin_auth', [
            'module_name' => '@app',
            'alias_name' => $api,
            'is_crud' => (int) $isCrud,
            'api' => $api,
        ]);
    }

    public function removePermissionApi($api)
    {
        return $this->deleteRow('admin_auth', [
            'api' => $api,
        ]);
    }

    // table handling

    public function createTableIfNotExists($table, array $columns)
    {
        if ($this->getDatabaseComponent()->getTableSchema($table, true) === null) {
            $this->getDatabaseComponent()->createCommand()->createTable($table, $columns)->execute();
        }
    }
    public function dropTableIfExists($table)
    {
        if ($this->getDatabaseComponent()->getTableSchema($table, true) !== null) {
            $this->getDatabaseComponent()->createCommand()->dropTable($table)->execute();
        }
    }

    public function createAdminAuthTable()
    {
        $this->createTableIfNotExists('admin_auth', [
            'id' => 'INT(11) PRIMARY KEY',
            'alias_name' => 'text',
            'module_name' => 'text',
            'is_crud' => 'int(11)',
            'route' => 'text',
            'api' => 'text',
        ]);
    }

    public function dropAdminAuthTable()
    {
        $this->dropTableIfExists('admin_auth');
    }

    public function createAdminGroupAuthTable()
    {
        $this->createTableIfNotExists('admin_group_auth', [
            'id' => 'INT(11) PRIMARY KEY',
            'group_id' => 'int(11)',
            'auth_id' => 'int(11)',
            'crud_create' => 'int(11)',
            'crud_update' => 'int(11)',
            'crud_delete' => 'int(11)',
        ]);
    }

    public function dropAdminGroupAuthTable()
    {
        $this->dropTableIfExists('admin_group_auth');
    }    

    public function createAdminUserGroupTable()
    {
        $this->createTableIfNotExists('admin_user_group', [
            'id' => 'INT(11) PRIMARY KEY',
            'user_id' => 'int(11)',
            'group_id' => 'int(11)',
        ]);
    }

    public function dropAdminUserGroupTable()
    {
        $this->dropTableIfExists('admin_user_group');   
    }

    public function createAdminUserAuthNotificationTable()
    {
        $this->createTableIfNotExists('admin_user_auth_notification', [
            'id' => 'INT(11) PRIMARY KEY',
            'user_id' => 'int(11)',
            'auth_id' => 'int(11)',
            'is_muted' => 'int(11)',
            'model_latest_pk_value' => 'text',
            'model_class' => 'text',
            'created_at' => 'int(11)',
            'updated_at' => 'int(11)',
        ]);
    }

    public function dropAdminUserAuthNotificationTable()
    {
        $this->dropTableIfExists('admin_user_auth_notification');
    }

    /**
     * Insert row
     *
     * @param string $table
     * @param array $values
     * @return integer returns the number of rows inserted.
     */
    public function insertRow($table, array $values)
    {
        return $this->getDatabaseComponent()->createCommand()->insert($table, $values)->execute();
    }

    /**
     * Delete row
     *
     * @param string $table
     * @param array $condition
     * @return integer returns the number of rows deleted.
     */
    public function deleteRow($table, array $condition)
    {
        return $this->getDatabaseComponent()->createCommand()->delete($table, $condition)->execute();
    }

    public function createNgRestLogFixture()
    {
        return new ActiveRecordFixture(['modelClass' => NgrestLog::class]);
    }

    public function createUserOnlineFixture()
    {
        return new ActiveRecordFixture(['modelClass' => UserOnline::class]);
    }

    public function createUserFixture(array $fixtureData,$isApiUser = true)
    {
        return new NgRestModelFixture([
            'modelClass' => User::class,
            'schema' => [
                'firstname' => 'text',
                'lastname' => 'text',
                'email' => 'text',
                'is_deleted' => 'int(11)',
                'is_api_user' => 'boolean',
                'api_last_activity' => 'int(11)',
                'auth_token' => 'text',
                'is_request_logger_enabled' => 'boolean',
            ],
            'fixtureData' => $fixtureData,
        ]);
    }
    
    public function createGroupFixture($id)
    {
        return new NgRestModelFixture([
            'modelClass' => Group::class,
            'fixtureData' => [
                'tester' => [
                    'id' => $id,
                    'name' => 'Test Group',
                ],
            ],
        ]);
    }
}