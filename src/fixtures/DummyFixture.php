<?php

namespace luya\testsuite\fixtures;

use yii\test\ActiveFixture;

/**
 * Dummy Fixture Model.
 *
 * Prepare config to enable sqlite3:
 *
 * ```php
 * 'components' => [
 *     'db' => [
 *   		'class' => 'yii\db\Connection',
 *   		'dsn' => 'sqlite::memory:',
 *     ]
 * ]
 * ```
 *
 * Create the database Schema
 *
 * ```php
 * Yii::$app->db->createCommand()->createTable('dummy_fixture', [
 *     'id' => 'INT(11) PRIMARY KEY',
 *     'string' => 'varchar(250)',
 *     'integer' => 'int(11)',
 *     'float' => 'float(2)',
 *     'boolean' => 'tinyint(1)',
 * ])->execute();
 * ```
 *
 *
 * ```php
 * $fixture = new DummyFixture();
 * $fixture->load();
 * $model = $fixture->getModel('data1');
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 */
class DummyFixture extends ActiveFixture
{
    public $modelClass = 'luya\testsuite\fixtures\DummyFixtureModel';

    public function getData()
    {
        return [
            'data1' => [
                'id' => 1,
                'string' => 'Lorem Ipsum',
                'integer' => 10,
                'float' => 10.00,
                'boolean' => true,
            ]
        ];
    }
}
