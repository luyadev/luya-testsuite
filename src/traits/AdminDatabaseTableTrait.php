<?php

namespace luya\testsuite\traits;

use luya\admin\models\Group;
use luya\admin\models\Lang;
use luya\admin\models\NgrestLog;
use luya\admin\models\QueueLog;
use luya\admin\models\QueueLogError;
use luya\admin\models\Tag;
use luya\admin\models\TagRelation;
use luya\admin\models\User;
use luya\admin\models\UserDevice;
use luya\admin\models\UserLogin;
use luya\admin\models\UserLoginLockout;
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
    use DatabaseTableTrait;

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
            'pool' => 'text',
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
     * Create the NgRest Log Fixture.
     *
     * @return ActiveRecordFixture
     * @deprecated 1.0.27
     */
    public function createNgRestLogFixture(array $fixtureData = [])
    {
        trigger_error('use createAdminNgRestLogFixture() instead', E_USER_DEPRECATED);
        return $this->createAdminNgRestLogFixture($fixtureData);
    }

    /**
     * Create the NgRest Log Fixture.
     *
     * @return ActiveRecordFixture
     */
    public function createAdminNgRestLogFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => NgrestLog::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Create the User Online Fixture.
     *
     * @return ActiveRecordFixture
     * @deprecated 1.0.27
     */
    public function createUserOnlineFixture(array $fixtureData = [])
    {
        trigger_error('use createAdminUserOnlineFixture() instead', E_USER_DEPRECATED);
        return $this->createAdminUserOnlineFixture($fixtureData);
    }

    /**
     * Create the User Online Fixture.
     *
     * @return ActiveRecordFixture
     */
    public function createAdminUserOnlineFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => UserOnline::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Create the User Fixture with given fixture Data.
     *
     * @param array $fixtureData
     * @return NgRestModelFixture
     * @deprecated 1.0.27
     */
    public function createUserFixture(array $fixtureData = [])
    {
        trigger_error('use createAdminUserFixture() instead', E_USER_DEPRECATED);
        return $this->createAdminUserFixture($fixtureData);
    }

    /**
     * Create the User Fixture with given fixture Data.
     *
     * @param array $fixtureData
     * @return NgRestModelFixture
     */
    public function createAdminUserFixture(array $fixtureData = [], $defaultSchema = true)
    {
        return new NgRestModelFixture([
            'modelClass' => User::class,
            'schema' => $defaultSchema ? [
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
                'login_2fa_enabled' => 'int(11)',
                'login_2fa_secret' => 'text',
                'login_2fa_backup_key' => 'text',
                'password_verification_token' => 'text',
                'password_verification_token_timestamp' => 'int(11)',
            ] : null,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Admin User Fixture
     *
     * @param array $fixtureData
     * @return NgRestModelFixture
     * @since 1.0.27
     */
    public function createAdminUserLoginFixture(array $fixtureData = [])
    {
        return new NgRestModelFixture([
            'modelClass' => UserLogin::class,
            'fixtureData' => $fixtureData,
        ]);
    }
    
    /**
     * Create the Group Fixture with given ID.
     *
     * @param integer $id
     * @return NgRestModelFixture
     * @deprecated 1.0.27
     */
    public function createGroupFixture($id)
    {
        trigger_error('use createAdminGroupFixture() instead', E_USER_DEPRECATED);
        return $this->createAdminGroupFixture($id);
    }

    /**
     * Create the Group Fixture with given ID.
     *
     * @param integer $id
     * @return NgRestModelFixture
     */
    public function createAdminGroupFixture($id)
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

    /**
     * Create admin language fixture
     *
     * @param array $fixtureData
     * @return NgRestModelFixture
     * @since 1.0.21
     */
    public function createAdminLangFixture(array $fixtureData = [])
    {
        return new NgRestModelFixture([
            'modelClass' => Lang::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Create admin tag fixture
     *
     * @param array $fixtureData
     * @return NgRestModelFixture
     * @since 1.0.22
     */
    public function createAdminTagFixture(array $fixtureData = [])
    {
        return new NgRestModelFixture([
            'modelClass' => Tag::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Create admin tag relation fixture
     *
     * @param array $fixtureData
     * @return ActiveRecordFixture
     * @since 1.0.22
     */
    public function createAdminTagRelationFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => TagRelation::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * User Device
     *
     * @param array $fixtureData
     * @return ActiveRecordFixture
     * @since 1.0.27
     */
    public function createAdminUserDeviceFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => UserDevice::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * User Login Lockout
     *
     * @param array $fixtureData
     * @return ActiveRecordFixture
     * @since 1.0.27
     */
    public function createAdminUserLoginLockoutFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => UserLoginLockout::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Queue Log
     *
     * @param array $fixtureData
     * @return ActiveRecordFixture
     * @since @since 1.0.27
     */
    public function createAdminQueueLogFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => QueueLog::class,
            'fixtureData' => $fixtureData,
        ]);
    }

    /**
     * Queue Log Error
     *
     * @param array $fixtureData
     * @return ActiveRecordFixture
     * @since @since 1.0.27
     */
    public function createAdminQueueLogErrorFixture(array $fixtureData = [])
    {
        return new ActiveRecordFixture([
            'modelClass' => QueueLogError::class,
            'fixtureData' => $fixtureData,
        ]);
    }
}
