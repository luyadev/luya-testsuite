<?php

namespace luya\testsuite\tests;

use Yii;
use luya\testsuite\cases\BaseTestSuite;

class WebApplicationTestCaseTest extends BaseTestSuite
{
    public function getConfigArray()
    {
        return [
            'id' => 'webapptestcase',
            'basePath' => dirname(__DIR__),
        ];
    }

    public function testInstance()
    {
        $this->assertInstanceOf('luya\web\Application', Yii::$app);
        $this->assertInstanceOf('luya\base\Boot', $this->boot);
        $this->assertInstanceOf('luya\web\Application', $this->app);
    }
}