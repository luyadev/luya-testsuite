<?php

namespace luya\testsuite\tests\cases;

use Curl\Curl;
use luya\testsuite\cases\ServerTestCase;

final class ServerTestCaseTest extends ServerTestCase
{
    public $port = 1337;
    
    public function getConfigArray()
    {
        return [
            'id' => 'servertest',
            'basePath' => dirname(__DIR__),
            'components' => [
                'urlManager' => [
                    'cache' => null,
                ],
            ]
        ];
    }
    
    public function afterSetup()
    {
        // disable server boot by overwriting this method
    }
    
    public function beforeTearDown()
    {
        // disable server shutdown by overwriting this method
    }
    
    public function testBuildCallUrl()
    {
        $this->assertSame('localhost:1337/api/path', $this->buildCallUrl('api/path'));
        $this->assertSame('localhost:1337/api/path?foo=bar', $this->buildCallUrl('api/path', ['foo' => 'bar']));
        $this->assertSame('localhost:1337/api/path?foo=bar', $this->buildCallUrl(['api/path', 'foo' => 'bar']));
        $this->assertSame('localhost:1337/api/path?foo=baz', $this->buildCallUrl(['api/path', 'foo' => 'bar'], ['foo' => 'baz']));
    }

    public function testDebug()
    {
        $this->debugMessage('url', (new Curl())->get('url'));
    }
}
