<?php

namespace luya\testsuite\tests\cases;

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
            'components' => [
                'urlManager' => [
                    'cache' => null,
                ],
            ]
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

        $this->assertSame([], $this->fixtures());
        $this->assertFalse($this->fixture('foobar'));
    }
    
    public function testTrimContent()
    {
        $this->assertSame('Foo Bar Bar Foo', $this->trimContent('
Foo
    Bar

Bar Foo
'));
        
        $this->assertSameTrimmed('Key Value', 'Key             Value');
        $this->assertContainsTrimmed('Key Value', 'Start Key    Value     End');
    }
    
    public function testTrimSpaces()
    {
        $this->assertSame('Hello
World
It\'s LUYA
', $this->trimSpaces('
Hello
        World
            It\'s LUYA
'));
        
        $this->assertSameNoSpace('
Yes
    YES! YES!
', '
Yes
    YES! YES!
');
        
        $this->assertContainsNoSpace('
Foo Bar
    Bar Foo
', '
BEFORE
Foo Bar
Bar Foo
AFTER
');
    }



    public function testInvokeMthod()
    {
        $object = new TestInvokeClass;
        $this->assertSame('bar', $this->invokeMethod($object, 'bar', ['bar']));
    }
}


class TestInvokeClass
{
    protected function bar($foo)
    {
        return $foo;
    }
}
