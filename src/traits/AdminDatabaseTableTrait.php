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

    /**
     * Add permission route.
     *
     * @param string $route
     * @return integer Returns the fake id for the auth entry.
     */
    public function addPermissionRoute($id, $route, $moduleName = '@app')
    {
        $this->insertRow('admin_auth', [
            'id' => $id,
            'module_name' => $moduleName,
            'alias_name' => $route,
            'route' => $route,
        ]);
        return $id;
    }

    /**
     * Remove permission route
     *
     * @param string $route
     * @return integer The number of affected rows.
     */
    public function removePermissionRoute($route)
    {
        return $this->deleteRow('admin_auth', [
            'route' => $route,
        ]);
    }

    /**
     * Assigne a group to an auth entry.
     *
     * @param integer $groupId
     * @param integer $authId
     * @param boolean $canCreate
     * @param boolean $canUpdate
     * @param boolean $canDelete
     * @return integer The number of affected rows.
     */
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

    /**
     * Unassigne a group from auth
     *
     * @param integer $groupId
     * @param integer $authId
     * @return integer The number of affected rows.
     */
    public function unAssignGroupAuth($groupId, $authId)
    {
        return $this->deleteRow('admin_group_auth', [
            'group_id' => $groupId,
            'auth_id' => $authId,
        ]);
    }

    /**
     * Add permission api entry
     *
     * @param string $api
     * @param boolean $isCrud
     * @return integer Returns the "fake" id fro the given api
     */
    public function addPermissionApi($id, $api, $isCrud = true)
    {
        $this->insertRow('admin_auth', [
            'id' => $id,
            'module_name' => '@app',
            'alias_name' => $api,
            'is_crud' => (int) $isCrud,
            'api' => $api,
        ]);
        return $id;
    }

    /**
     * Remove permission api entry.
     *
     * @param string $api
     * @return integer The number of affected rows.
     */
    public function removePermissionApi($api)
    {
        return $this->deleteRow('admin_auth', [
            'api' => $api,
        ]);
    }

    /**
     * Create a table if not exists
     *
     * @param string $table
     * @param array $columns
     */
    public function createTableIfNotExists($table, array $columns)
    {
        if ($this->getDatabaseComponent()->getTableSchema($table, true) === null) {
            $this->getDatabaseComponent()->createCommand()->createTable($table, $columns)->execute();
        }
    }

    /**
     * Drop a table if not exists.
     *
     * @param string $table
     */
    public function dropTableIfExists($table)
    {
        if ($this->getDatabaseComponent()->getTableSchema($table, true) !== null) {
            $this->getDatabaseComponent()->createCommand()->dropTable($table)->execute();
        }
    }

    /**
     * Create the admin auth table.
     */
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

    /**
     * Drop the admin auth table.
     */
    public function dropAdminAuthTable()
    {
        $this->dropTableIfExists('admin_auth');
    }

    /**
     * Create the admin group auth table.
     */
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

    /**
     * Drop the admin group auth table.
     */
    public function dropAdminGroupAuthTable()
    {
        $this->dropTableIfExists('admin_group_auth');
    }    

    /**
     * Create the admin user group table.
     */
    public function createAdminUserGroupTable()
    {
        $this->createTableIfNotExists('admin_user_group', [
            'id' => 'INT(11) PRIMARY KEY',
            'user_id' => 'int(11)',
            'group_id' => 'int(11)',
        ]);
    }

    /**
     * Drop the admin user group table.
     */
    public function dropAdminUserGroupTable()
    {
        $this->dropTableIfExists('admin_user_group');   
    }

    /**
     * Create the admin user auth notification table.
     */
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

    /**
     * Drop the admin user auth notification table.
     */
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

    /**
     * Create the NgRest Log Fixture.
     *
     * @return ActiveRecordFixture
     */
    public function createNgRestLogFixture()
    {
        return new ActiveRecordFixture(['modelClass' => NgrestLog::class]);
    }

    /**
     * Create the User Online Fixture.
     *
     * @return ActiveRecordFixture
     */
    public function createUserOnlineFixture()
    {
        return new ActiveRecordFixture(['modelClass' => UserOnline::class]);
    }

    /**
     * Create the User Fixture with given fixture Data.
     *
     * @param array $fixtureData
     * @return NgRestModelFixture
     */
    public function createUserFixture(array $fixtureData)
    {
        return new NgRestModelFixture([
            'modelClass' => User::class,
            'schema' => [
                'title' => 'int(11)',
                'firstname' => 'text',
                'lastname' => 'text',
                'email' => 'text',
                'is_deleted' => 'int(11)',
                'is_api_user' => 'boolean',
                'api_last_activity' => 'int(11)',
                'auth_token' => 'text',
                'is_request_logger_enabled' => 'boolean',
                'email_verification_token_timestamp' => 'int(11)',
                'login_attempt_lock_expiration' => 'int(11)',
                'login_attempt' => 'int(11)',
                'email_verification_token' => 'text',
                'api_allowed_ips' => 'text',
                'api_rate_limit' => 'int(11)',
                'cookie_token' => 'text',
                'settings' => 'text',
                'force_reload' => 'int(11)',
                'secure_token_timestamp' => 'int(11)',
                'secure_token' => 'text',
                'password' => 'text',
                'password_salt' => 'text',
            ],
            'fixtureData' => $fixtureData,
        ]);
    }
    
    /**
     * Create the Group Fixture with given ID.
     *
     * @param integer $id
     * @return NgRestModelFixture
     */
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
