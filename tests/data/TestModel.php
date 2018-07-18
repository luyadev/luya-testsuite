<?php

namespace luya\testsuite\tests\data;

use yii\db\ActiveRecord;

final class TestModel extends ActiveRecord
{
    public static function tableName()
    {
        return 'my_test_table';
    }
    
    public function rules()
    {
        return [
            ['id', 'integer'],
            [['user_id', 'group_id'], 'integer'],
        ];
    }
}
