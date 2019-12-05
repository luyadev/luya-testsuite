<?php

namespace luya\testsuite\components;

use SessionHandlerInterface;

/**
 * Dummy Session Handler implements SessionHandlerInterface.
 * 
 * @since 1.0.24
 * @author Basil Suter <basil@nadar.io>
 */
class DummySessionHandler implements SessionHandlerInterface
{
    public $store = [];

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return array_key_exists($id, $this->store) ? $this->store[$id] : false;
    }

    public function write($id, $data)
    {
        $this->store[$id] = $data;
        return true;
    }

    public function destroy($id)
    {
        unset($this->store[$id]);
        return true;
    }

    public function gc($maxlifetime)
    {
        return true;
    }
}