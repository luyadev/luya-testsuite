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
 * class MyTestCase extends ConsoleApplicationTestCase
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
 * @since 1.0.2
 */
abstract class ConsoleApplicationTestCase extends BaseTestSuite
{
    /**
     *
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::bootApplication()
     */
    public function bootApplication(Boot $boot)
    {
        $boot->applicationConsole();
    }
}
