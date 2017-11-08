<?php

namespace luya\testsuite\tests;

use Yii;
use luya\base\Boot;
use luya\testsuite\cases\BaseTestSuite;

final class BaseTestSuiteTest extends BaseTestSuite
{
    public function getConfigArray()
    {
        return [
            'id' => 'basetestcase',
            'basePath' => dirname(__DIR__),
        ];
    }
    
    public function bootApplication(Boot $boot)
    {
        $boot->applicationWeb();
    }
    
    public function testInstance()
    {
        $this->assertInstanceOf('luya\web\Application', Yii::$app);
        $this->assertInstanceOf('luya\base\Boot', $this->boot);
        $this->assertInstanceOf('luya\web\Application', $this->app);
    }
}
