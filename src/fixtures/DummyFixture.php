<?php

namespace luya\testsuite\fixtures;

use yii\test\ActiveFixture;

/**
 * 
 * $r = Yii::$app->sqllite->createCommand()->createTable('mytest', ['name' => 'varchar(120)', 'value' => 'varchar(120)'])->execute();
 *
 * @author nadar
 *
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
