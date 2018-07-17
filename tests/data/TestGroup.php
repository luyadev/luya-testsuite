<?php

namespace luya\testsuite\tests\data;

final class TestGroup
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
