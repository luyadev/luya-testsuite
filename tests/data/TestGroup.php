<?php

namespace luya\testsuite\tests\data;

use luya\cms\base\BlockGroup;

final class TestGroup extends BlockGroup
{
    public function identifier()
    {
        return 'test-group';
    }

    public function label()
    {
        return "Test Group Label";
    }

    public function getPosition()
    {
        return 50;
    }
}
