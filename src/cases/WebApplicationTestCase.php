<?php

namespace luya\testsuite\cases;

abstract class WebApplicationTestCase extends BaseTestSuite
{
    public function beforeSetup()
    {
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        $_SERVER['SCRIPT_NAME'] =  '/index.php';
        $_SERVER['REQUEST_URI'] = '/';
    }
}