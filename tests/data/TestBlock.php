<?php

namespace luya\testsuite\tests\data;

use luya\cms\base\InternalBaseBlock;


final class TestBlock extends InternalBaseBlock
{
    public function sayHelloWorld()
    {
        return 'Hello World';
    }

    public function name()
    {
        return 'Content';
    }
    public function config()
    {
        return [];
    }
    public function renderFrontend()
    {
        return 'frontend';
    }
    public function renderAdmin()
    {
        return 'admin';
    }
}
