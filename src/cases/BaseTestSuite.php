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
     * Method which is executed beofre Setup in order to inject data on before Setup.
     * 
     * Make sure to call the parent before Setup.
     */
    public function beforeSetup()
    {  
    }

    /**
     * 
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    public function setUp()
    {
        $this->beforeSetup();
        
        $boot = new Boot();
        $boot->setConfigArray($this->getConfigArray());
        $boot->mockOnly = true;
        $boot->setBaseYiiFile('vendor/yiisoft/yii2/Yii.php');
        $boot->applicationWeb();
        $this->boot = $boot;
        $this->app = $boot->app;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::tearDown()
     */
    public function tearDown()
    {
        unset($this->app, $this->boot);
    }
}