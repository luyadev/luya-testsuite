<?php

namespace luya\testsuite\fixtures;

use yii\db\ActiveRecord;

class DummyFixture extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%dummy_fixture}}';
    }
    
    public function rules()
    {
        return [
            [['id', 'string', 'integer', 'float', 'boolean'], 'safe'],
        ];
    }
}
