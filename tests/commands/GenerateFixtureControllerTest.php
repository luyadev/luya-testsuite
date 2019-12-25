<?php

namespace luya\testsuite\tests\commands;

use luya\testsuite\cases\ConsoleApplicationTestCase;
use luya\testsuite\commands\GenerateFixtureController;
use luya\testsuite\fixtures\ActiveRecordFixture;
use Yii;

class GenerateFixtureControllerTest extends ConsoleApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'command',
            'basePath' => dirname(__DIR__),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ]
            ]
        ];
    }

    public function testGenerate()
    {
        $testschema = new ActiveRecordFixture([
            'tableName' => 'testschema',
            'schema' => [
                'id' => 'pk',
                'name' => 'text',
                'is_active' => 'int(11)',
            ],
            'fixtureData' => [
                1 => [
                    'id' => 1,
                    'name' => 'John Doe',
                    'is_active' => true,
                ],
                2 => [
                    'id' => 2,
                    'name' => 'Jane Doe',
                    'is_active' => 0,
                ],
            ]
        ]);

        $command = new GenerateFixtureController('id', Yii::$app);
        $command->table = 'testschema';
        $command->actionIndex();
    }
}