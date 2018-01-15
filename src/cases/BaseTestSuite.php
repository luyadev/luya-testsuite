<?php

namespace luya\testsuite\cases;

use luya\Boot;

require_once 'vendor/autoload.php';

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

/**
 * Base Test Suite.
 *
 * Usage:
 *
 * ```php
 * class MyTestCase extends BaseTestSuite
 * {
 *     public function getConfigArray()
 *     {
 *         return [
 *            'id' => 'mytestapp',
 *            'basePath' => dirname(__DIR__),
 *         ];
 *     }
 *
 *     public function bootApplication(Boot $boot)
 *     {
 *          $boot->applicationWeb();
 *     }
 * }
 * ```
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
abstract class BaseTestSuite extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \luya\Boot
     */
    public $boot;
    
    /**
     * @var \luya\web\Application
     */
    public $app;

    /**
     * Provide Configurtion Array.
     */
    abstract public function getConfigArray();
    
    /**
     * @param \luya\base\Boot $boot
     * @since 1.0.2
     */
    abstract public function bootApplication(\luya\base\Boot $boot);
    
    /**
     * Method which is executed before the setUp() method in order to inject data on before Setup.
     *
     * Make sure to call the parent beforeSetup() method.
     */
    public function beforeSetup()
    {
    }
    
    /**
     * Method which is executed after the setUp() metho in order to trigger post setup functions.
     *
     * Make sure to call the parent afterSetup() method.
     *
     * @since 1.0.2
     */
    public function afterSetup()
    {
    }

    /**
     *
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->beforeSetup();
        
        $boot = new Boot();
        $boot->setConfigArray($this->getConfigArray());
        $boot->mockOnly = true;
        $boot->setBaseYiiFile('vendor/yiisoft/yii2/Yii.php');
        $this->bootApplication($boot);
        $this->boot = $boot;
        $this->app = $boot->app;
        
        $this->afterSetup();
    }

    /**
     * This methode is triggered before the application test case tearDown() method is running.
     *
     * @since 1.0.2
     */
    public function beforeTearDown()
    {
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    protected function tearDown()
    {
        $this->beforeTearDown();
        
        unset($this->app, $this->boot);
    }
    
    /**
     * Trims the given text. Remove whitespaces, tabs and other chars in order to compare readable formated texts.
     * 
     * @param string $text
     * @return string The trimmed text.
     */
    protected function trimContent($text)
    {
        return str_replace(['> ', ' <'], ['>', '<'], trim(preg_replace('/\s+/', ' ', $text)));
    }
    
    /**
     * Same as assertContains but trims the needle and haystack content in order to compare.
     * 
     * @param string $needle
     * @param string $haystack
     * @return boolean
     */
    public function assertContainsTrimmed($needle, $haystack)
    {
        return $this->assertContains($this->trimContent($needle), $this->trimContent($haystack));
    }
    
    /**
     * Call a private or protected method from an object and return the value.
     * 
     * ```php
     * public function testProtectedMethod()
     * {
     *     // assuming MyObject as a protected method like:
     *     // protected function hello($title)
     *     // {
     *     //     return $title;
     *     // }
     *     $object = new MyObject();
     *     
     *     $this->assertSame('Hello World', $this->invokeMethod($object, 'hello', ['Hello World']));
     * }
     * ```
     * 
     * @param object $object The object the method exists from.
     * @param string $methodName  The name of the method which should be called.
     * @param array $parameters An array of paremters which should be passed to the method.
     * @return mixed
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
