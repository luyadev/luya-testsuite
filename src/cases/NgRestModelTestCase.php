<?php

namespace luya\testsuite\cases;

use luya\base\Boot;
use luya\helpers\ArrayHelper;

/**
 * A Test Case for Models.
 * 
 * Setup sqlite for model access and runs ngrest model methods.
 * 
 * @author Basil Suter <git@nadar.io>
 * @since 1.1.0
 */
abstract class NgRestModelTestCase extends WebApplicationTestCase
{
    /**
     * @var string The path to the ngrest model.
     */
    public $modelClass;

    /**
     * {@inheritDoc}
     */
    public function bootApplication(Boot $boot)
    {
        // ensure the admin module is registered, if not do so.
        $config = ArrayHelper::merge([
            'components' => [
                'session' => ['class' => 'luya\testsuite\components\DummySession'],
                'cache' => ['class' => 'yii\caching\DummyCache'],
                'db' => ['class' => 'yii\db\Connection', 'dsn' => 'sqlite::memory:'],
            ]
        ], $boot->getConfigArray());
        
        // set the new config.
        $boot->setConfigArray($config);
        
        // boot the application
        $boot->applicationWeb();
    }
}