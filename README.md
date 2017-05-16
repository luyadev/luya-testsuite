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