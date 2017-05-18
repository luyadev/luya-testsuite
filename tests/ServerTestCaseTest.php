<?php

namespace luya\testsuite\tests;

use luya\testsuite\cases\ServerTestCase;

class ServerTestCaseTest extends ServerTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'servertest',
            'basePath' => dirname(__DIR__),
        ];
    }
}