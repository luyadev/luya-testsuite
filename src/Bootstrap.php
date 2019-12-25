<?php

namespace luya\testsuite;

use luya\testsuite\commands\GenerateFixtureController;
use yii\base\BootstrapInterface;
use yii\console\Application;

/**
 * TestSuite Bootstrap
 * 
 * Allows the injection of console commands.
 * 
 * @since 1.0.25
 * @author Basil Suter <basil@nadar.io>
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * {@inheritDoc}
     */
    public function bootstrap($app)
    {
        if ($app instanceof Application) {
            $app->controllerMap['generatefixture'] = [
                'class' => GenerateFixtureController::class,
            ];
        }
    }
}