<?php

namespace luya\testsuite\cases;

use luya\base\Boot;
use luya\helpers\ArrayHelper;
use luya\testsuite\fixtures\NgRestModelFixture;

/**
 * A Test Case for Models.
 * 
 * Setup sqlite for model access and runs ngrest model methods.
 * 
 * ```php
 * class CityTest extends NgRestModelTestCase
 * {
 *   public $modelClass = City::class;
 * 
 *   public function getConfigArray()
 *   {
 *        return [
 *           'id' => 'testmodelapp',
 *           'basePath' => dirname(__DIR__),
 *        ];
 *   }
 * 
 *   public function testCreate()
 *   {
 *      $model = $this->getModel();
 *      $model->title = 'foobar';
 *      $this->assertTrue($model->save());
 *   }
 * }
 * ```
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

    /**
     * Get New model
     *
     * @return NgRestModelFixture
     */
    public function getModel()
    {
        return $this->getFixture()->newModel;
    }

    /**
     * Get the current modelClass Fixture
     *
     * @return NgRestModelFixture
     */
    public function getFixture()
    {
        if ($this->fixture($this->modelClass)) {
            return $this->fixture($this->modelClass);
        }

        return new NgRestModelFixture([
            'modelClass' => $this->modelClass,
        ]);
    }
}