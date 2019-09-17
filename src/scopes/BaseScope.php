<?php

namespace luya\testsuite\scopes;

use luya\testsuite\fixtures\ActiveRecordFixture;
use luya\testsuite\traits\DatabaseTableTrait;
use yii\base\Application;

/**
 */
abstract class BaseScope
{
    private $_fn;

    private $_invoke;

    private $_app;

    /**
     * This method is called before the callback runs in order to prepare and setup the permission scope.
     */
    abstract public function prepare();

    /**
     * Clean up tables and fixtures.
     */
    abstract public function cleanup();

    /**
     * Permission Scope contstructor.
     *
     * @param Application $app
     * @param callable $fn
     * @param callable $invoke
     */
    public function __construct(Application $app, callable $fn, callable $invoke = null)
    {
        $this->_app = $app;
        $this->_invoke = $invoke;
        $this->_fn = $fn;    
    }

    public function getApp()
    {
        return $this->_app;
    }

    /**
     * Run the provided callable function
     *
     * @param PermissionScope $scope
     * @return mixed
     */
    public function runCallable(BaseScope $scope)
    {
        return call_user_func_array($this->_fn, [$scope]);
    }

    public function configure()
    {
        if ($this->_invoke) {
            call_user_func_array($this->_invoke, [$this]);
        }
    }

    public function cleanupFixture($fixture)
    {
        if ($fixture instanceof ActiveRecordFixture) {
            $fixture->cleanup();
        }
    }
    
    /**
     * Run a given function inside a permission scope.
     *
     * @param yii\base\Application $app
     * @param callable $fn The function to run.
     * @param callable $invoke The function to configure the scope.
     * @return mixed
     */
    public static function run(Application $app, callable $fn, callable $invoke = null)
    {
        $scope = new static($app, $fn, $invoke);
        $scope->configure();
        $scope->prepare();
        $response = $scope->runCallable($scope);
        $scope->cleanup();
        return $response;
    }
}