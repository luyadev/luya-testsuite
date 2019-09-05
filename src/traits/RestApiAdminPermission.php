<?php

namespace luya\testsuite\traits;

trait RestApiAdminPermission
{
    public function routePermission($route, $allowed = true)
    {
        
    }

    public function apiPermission($api, $create, $update, $delete)
    {
        
    }

    public function createTableIfNotExists($table, array $columns)
    {
        if ($this->app->db->getTableSchema($table, true) === null) {
            return $this->app->db->createCommand()->createTable($table, $columns)->execute();
        }

        return true;
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

    public function createAdminUserGroupTable()
    {
        $this->createTableIfNotExists('admin_user_group', [
            'id' => 'INT(11) PRIMARY KEY',
            'user_id' => 'int(11)',
            'group_id' => 'int(11)',
        ]);
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
}