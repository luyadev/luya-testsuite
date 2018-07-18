<?php

namespace luya\testsuite\tests\data;

use luya\admin\ngrest\base\NgRestModel;

final class NgRestTestModel extends NgRestModel
{
    public static function ngRestApiEndpoint()
    {
        return 'api-test-model';
    }
    
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
