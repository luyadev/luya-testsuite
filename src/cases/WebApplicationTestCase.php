<?php

namespace luya\testsuite\cases;

use luya\base\Boot;

/**
 * Web Application Test Case.
 *
 * Provdides basic setup for Script paths in order make the urlManager work.
 *
 * Usage:
 *
 * ```php
 * class MyTestCase extends WebApplicationTestCase
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
abstract class WebApplicationTestCase extends BaseTestSuite
{
    /**
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::beforeSetup()
     */
    public function beforeSetup()
    {
        parent::beforeSetup();
        
        $_SERVER['SCRIPT_FILENAME'] = 'index.php';
        $_SERVER['SCRIPT_NAME'] =  '/index.php';
        $_SERVER['REQUEST_URI'] = '/';
    }
    
    /**
     *
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::bootApplication()
     */
    public function bootApplication(Boot $boot)
    {
        $boot->applicationWeb();
    }
}
