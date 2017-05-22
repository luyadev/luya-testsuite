# LUYA Test Suite

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-testsuite/downloads)](https://packagist.org/packages/luyadev/luya-testsuite)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-testsuite/v/stable)](https://packagist.org/packages/luyadev/luya-testsuite)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

Providing PHPUnit Testcases to test your Application, Modules, Components or Classes.

## Install

Add the `luyadev/luya-testsuite` package to the require-dev section of your composer.json file:

```
composer require luyadev/luya-testsuite:~1.0.0 --dev
```

Create a new folder `tests` inside your appliation folder and create a test classes:

```php
namepsace app\tests;

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
    
    public function testInstance()
    {
        $this->assertInstanceOf('luya\web\Application', Yii::$app);
        $this->assertInstanceOf('luya\base\Boot', $this->boot);
        $this->assertInstanceOf('luya\web\Application', $this->app);
    }
}
```

To run the unit tests while (assuming your tests are in directory `tests/`) run in your terminal:

```sh
./vendor/bin/phpunit tests/
```

In order to support sqlite fixtures install:

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
