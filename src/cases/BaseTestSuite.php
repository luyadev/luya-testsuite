<?php

namespace luya\testsuite\cases;

use luya\Boot;

require_once 'vendor/autoload.php';

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
}
