<?php

namespace luya\testsuite\cases;

use luya\Boot;

require_once 'vendor/autoload.php';

abstract class BaseTestSuite extends \PHPUnit\Framework\TestCase
{
    public $boot;
    public $app;

    abstract public function getConfigArray();

    public function setUp()
    {
        $boot = new Boot();
        $boot->setConfigArray($this->getConfigArray());
        $boot->mockOnly = true;
        $boot->setBaseYiiFile('vendor/yiisoft/yii2/Yii.php');
        $boot->applicationWeb();
        $this->boot = $boot;
        $this->app = $boot->app;
    }

    public function tearDown()
    {
        unset($this->app, $this->boot);
    }
}