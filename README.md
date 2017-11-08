# LUYA Test Suite

[![LUYA](https://img.shields.io/badge/Powered%20by-LUYA-brightgreen.svg)](https://luya.io)
[![Total Downloads](https://poser.pugx.org/luyadev/luya-testsuite/downloads)](https://packagist.org/packages/luyadev/luya-testsuite)
[![Latest Stable Version](https://poser.pugx.org/luyadev/luya-testsuite/v/stable)](https://packagist.org/packages/luyadev/luya-testsuite)
[![Join the chat at https://gitter.im/luyadev/luya](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/luyadev/luya)

Providing PHPUnit Testcases and a built in Webserver to test your Application, Modules, Components, APIs or Classes.

Whats included?

Test Cases
+ Web application test case
+ Console application test case
+ Server (for APIs) test case
+ CMS Block test case

Traits
+ Message file compare trait
+ Migration file check trait

Fixtures
+ Dummy fixture model

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
        // add your phpunit tests here, like:
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

Some example in how to use the LUYA Testsuite for different scenarios.

### Testing API and Application

When working with APIs or Customer Websites somtimes you just want to test the Website itself, what is the response, does all the pages still work after my update? Therefore we have `luya\testsuite\cases\ServerTestCase`.

This example will run your LUYA application within a PHP Webserver and test the response status codes or contents for a set of given pages. In order to run this example create a `MyWebsiteTest.php` file inside the `tests` folder.

```php
namespace app\tests;

class MyWebsiteTest extends ServerTestCase
{
   public function getConfigArray()
   {
      return [
          'id' => 'mytestapp',
          'basePath' => dirname(__DIR__),
      ];
  }
  
  public function testSites()
  {
      $this->assertUrlHomepageIsOk(); // checks the root url like: http://localhost/mywebsite.com
      $this->assertUrlIsOk('about'); // checks: http://localhost/mywebsite.com/about
      $this->assertUrlGetResponseContains('about/me', 'Hello World'); // checks: http://localhost/mywebsite.com/about/me
      $this->assertUrlIsError('errorpage'); // checks: http://localhost/mywebsite.com/errorpage
  }
}
```

> As the Webserver process may run from a different permission level you have to ensure the assets/runtime folder has the required permissions.

### Controller Function Test

We're using `getMockBuilder()` as shown in the multiple examples in the [PHPUnit](https://phpunit.de/manual/current/en/test-doubles.html) to setup the DefaultController and assert the registered module `addressbook`. To test the the runtime exception (caused by a database error), we use the mock `method` function:

```php
public function testActionIndex()
{
    $module = Yii::$app->getModule('addressbook');
    
    $this->assertInstanceOf('luya\addressbook\frontend\Module', $module);
    $mock = $this->getMockBuilder(DefaultController::class)
        ->setConstructorArgs(["id" => "default", "module" => $module])
        ->getMock();
        
    $mock->method("actionIndex");
}
```
