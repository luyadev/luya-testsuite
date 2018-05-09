# LUYA Testsuite Documentation

The LUYA Testsuites has TestCases, Fixtures and Traits.

## Testsuites

Each testsuite is like a specific test which predefined application settings. All test cases have in common as you have to define a `getConfigArray()` method which returns an array with the application configuration for this test case.

Example:

```php
class MyTestCase extends WebApplicationTestCase
{
    public function getConfigArray()
    {
        return [
           'id' => 'mytestapp',
           'basePath' => dirname(__DIR__),
        ];
    }
    
    // add your tests here
    // ...
}
```

## Traits

We have some easy to use traits which can help you in given situation to do repeatitiv jobs like checking if migration files have a createTable and dropTable state or see if any translations message keys are missing.

## Fixtures

Fixtures are used to work Database ActiveRecord or Models.

TBD