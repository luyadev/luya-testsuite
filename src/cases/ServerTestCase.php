<?php

namespace luya\testsuite\cases;

use Curl\Curl;
use Exception;
use Yii;
use luya\base\Boot;
use yii\helpers\Json;

/**
 * Generates a local Server in order to Test URLs.
 * 
 * An example usage:
 * 
 * ```php
 * class MyWebsite extends ServerTestCase
 * {
 *    public function getConfigArray()
 *    {
 *       return [
 *           'id' => 'mytestapp',
 *           'basePath' => dirname(__DIR__),
 *       ];
 *   }
 *   
 *   public function testSites()
 *   {
 *       $this->assertUrlHomepageIsOk();
 *       $this->assertUrlIsOk('about');
 *       $this->assertUrlGetResponseContains('about/me', 'Hello World');
 *       $this->assertUrlIsError('errorpage');
 *   }
 * }
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.2
 */
abstract class ServerTestCase extends BaseTestSuite
{
    /**
     * @var string
     */
    public $host = 'localhost';
   
    /**
     * @var integer
     */
    public $port = 1549;
    
    /**
     * 
     * @var string
     */
    public $documentRoot = '@app/public_html';
    
    /**
     * @var boolean
     */
    public $debug = false;
    
    private $_pid = 0;
    
    /**
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::bootApplication()
     */
    public function bootApplication(Boot $boot)
    {
        $boot->applicationConsole();
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::afterSetup()
     */
    public function afterSetup()
    {
        $this->_pid = $this->bootstrapServer($this->host, $this->port, $this->documentRoot);
    }
    
    /**
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::beforeTearDown()
     */
    public function beforeTearDown()
    {
        $this->killServer($this->_pid);
        $this->waitForServerShutdown($this->host, $this->port);
    }
    
    /**
     * Check whether homage is online and OK response.
     * @since 1.0.3
     */
    public function assertUrlHomepageIsOk()
    {
        $this->assertUrlIsOk(null);
    }
    
    /**
     * Test an URL whether a page has response code 200
     *
     * @param string $url
     * @param array $params Optional params to build the http queries for the given url.
     * @since 1.0.3
     */
    public function assertUrlIsOk($url, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertTrue($curl->isSuccess(), "GET URL '{$url}' return {$curl->http_status_code} instead of 200 (OK).");
    }
    
    /**
     * Test an URL whether a page has response code 400
     *
     * @param string $url
     * @param array $params Optional params to build the http queries for the given url.
     * @since 1.0.3
     */
    public function assertUrlIsError($url, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertTrue($curl->isError(), "GET URL '{$url}' return {$curl->http_status_code} instead of 400 (Error).");
    }

    /**
     * Test whether url is redirect.
     * @param unknown $url
     * @param array $params 
     * @since 1.0.3
     */
    public function assertUrlIsRedirect($url, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertTrue($curl->isRedirect(), "GET URL '{$url}' return {$curl->http_status_code} instead of 300 (Error).");
    }
    
    /**
     * 
     * @param unknown $url
     * @param string|array $contains If its an array it will be json encoded by default and the first and last char (wrapping)
     * brackets are cute off, so you can easy search for a key value parining inside the json response.
     * @param array $params
     * @since 1.0.3
     */
    public function assertUrlGetResponseContains($url, $contains, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertContains($this->buildPartialJson($contains, true), $curl->response);
    }
    
    /**
     * 
     * @param unknown $url
     * @param unknown $same
     * @param array $params
     * @since 1.0.3
     */
    public function assertUrlGetResponseSame($url, $same, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertSame($this->buildPartialJson($same), $curl->response);
    }
    
    /**
     * 
     * @param unknown $url
     * @param string|array $contains If its an array it will be json encoded by default and the first and last char (wrapping)
     * brackets are cute off, so you can easy search for a key value parining inside the json response.
     * @param array $data
     * @since 1.0.3
     */
    public function assertUrlPostResponseContains($url, $contains, array $data = [])
    {
        $curl = $this->createPostCurl($url, $data);
        $this->assertContains($this->buildPartialJson($contains, true), $curl->response);
    }

    /**
     * 
     * @param unknown $url
     * @param unknown $same
     * @param array $data
     * @since 1.0.3
     */
    public function assertUrlPostResponseSame($url, $same, array $data = [])
    {
        $curl = $this->createPostCurl($url, $data);
        $this->assertSame($this->buildPartialJson($same), $curl->response);
    }
    
    /**
     * 
     * @param unknown $contains
     * @param string $removeBrackets
     * @return string
     * @since 1.0.3
     */
    protected function buildPartialJson($contains, $removeBrackets = false)
    {
        if (is_array($contains)) {
            $contains = Json::encode($contains);
            if ($removeBrackets) {
                $contains = substr(substr($contains, 1), 0, -1);
            }
        }
        
        return $contains;
    }
    
    /**
     * 
     * @param unknown $url
     * @return string
     * @since 1.0.3
     */
    protected function buildCallUrl($url, array $params = [])
    {
        $url = "{$this->host}:{$this->port}/" . ltrim($url, '/');
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        
        return $url;
    }
    
    /**
     * @param unknown $url
     * @return \Curl\Curl
     * @since 1.0.3
     */
    protected function createGetCurl($url, array $params = [])
    {
        $curl = (new Curl())->get($this->buildCallUrl($url, $params));
        
        if ($this->debug) {
            echo "GET DEBUG '$url': " . $curl->response;
        }
        
        return $curl;
    }
    
    /**
     * 
     * @param unknown $url
     * @param array $data
     * @return \Curl\Curl
     * @since 1.0.3
     */
    protected function createPostCurl($url, array $data = [])
    {
        $curl = (new Curl())->post($url, $data);
        
        if ($this->debug) {
            echo "POST DEBUG '$url': " . $curl->response;
        }
        
        return $curl;
    }
    
    /**
     * 
     * @param unknown $host
     * @param unknown $port
     * @param unknown $documentRoot
     * @throws Exception
     * @return number
     * @since 1.0.2
     */
    protected function bootstrapServer($host, $port, $documentRoot)
    {
        $documentRoot = Yii::getAlias($documentRoot);
        if ($this->connectToServer($host, $port)) {
            throw new Exception("The $host:$port is already taken, choose another host and/or port.");
        }
        
        $pid = $this->createServer($host, $port, $documentRoot);
        
        $this->waitForServer($host, $port);
        
        return $pid;
    }
    
    /**
     * 
     * @param unknown $host
     * @param unknown $port
     * @param unknown $documentRoot
     * @param integer PID (process id)
     * @since 1.0.2
     */
    protected function createServer($host, $port, $documentRoot)
    {
        $command = sprintf(PHP_BINARY . ' -S %s:%d -t %s >/dev/null 2>&1 & echo $!', $host, $port,  $documentRoot);
        
        // Execute the command and store the process ID
        $output = [];
        exec($command, $output);
        
        return (int) $output[0];
    }
    
    /**
     * 
     * @param unknown $host
     * @param unknown $port
     * @return boolean
     * @since 1.0.2
     */
    protected function waitForServer($host, $port)
    {
        $start = microtime(true);
        while (microtime(true) - $start <= (int) 200) {
            if ($this->connectToServer($host, $port)) {
                break;
            }
        }
        
        return true;
    }
    
    /**
     * 
     * @param unknown $host
     * @param unknown $port
     * @return boolean
     * @since 1.0.3
     */
    protected function waitForServerShutdown($host, $port)
    {
        $start = microtime(true);
        while (microtime(true) - $start <= (int) 200) {
            if (!$this->connectToServer($host, $port)) {
                break;
            }
        }
        
        return true;
    }
    
    /**
     * 
     * @param unknown $host
     * @param unknown $port
     * @return boolean
     * @since 1.0.2
     */
    protected function connectToServer($host, $port)
    {
        $fp = @fsockopen($host, $port, $errno, $errstr, 3);
        if ($fp === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
    
    /**
     * 
     * @param unknown $pid
     * @since 1.0.2
     */
    protected function killServer($pid)
    {
        exec('kill -9 ' . (int) $pid);
    }
}