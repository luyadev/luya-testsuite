# LUYA TESTCASE

Setup TestCases for your Modules, Components and Classes.

```php

use Yii;

class MyTest extends \luya\testsuite\cases\WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
            'id' => 'mytestapp',
            'basePath' => dirname(__DIR__),
        ];
    }
    
    // add your tests here:
    
    public function testInstance()
    {
        $this->assertInstanceOf('luya\web\Application', Yii::$app);
        $this->assertInstanceOf('luya\base\Boot', $this->boot);
        $this->assertInstanceOf('luya\web\Application', $this->app);
    }
}
```

To run the unit tests while assuming your tests are in directory `tests/` run

```sh
./vendor/bin/phpunit tests/
```

in your shell.

In order to support sqlite Fixture install

```sh
sudo apt-get install php-sqlite3 
```

## Example Test Cases

### Registered Module and Function Test

We're using `getMockBuilder()` as shown in the multiple examples in the [PHPUnit](https://phpunit.de/manual/current/en/test-doubles.html) to setup the DefaultController and assert the registered module `addressbook`. To test the the runtime exception (caused by a database error), we use the mock `method` function:

```php
public function testActionIndex()
    {
        $module = Yii::$app->getModule('addressbook');
        $this->assertInstanceOf('luya\addressbook\frontend\Module', $module);
        $mock = $this->getMockBuilder(DefaultController::class)->setConstructorArgs(["id" => "default", "module" => $module])->getMock();
        $mock->method("actionIndex")->willThrowException(new Exception());
    }
```
