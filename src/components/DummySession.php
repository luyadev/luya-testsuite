<?php

namespace luya\testsuite\components;

use yii\web\Session;

/**
 * Dummy Session handles set, remove and get based on an array.
 * 
 * @since 1.0.24
 * @author Basil Suter <basil@nadar.io>
 */
class DummySession extends Session
{
    public $handler = ['class' => DummySessionHandler::class];

    public function open()
    {
        // do nothing!
    }
}