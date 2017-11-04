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
     * @var string
     */
    public $documentRoot = '@app/public_html';
    
    /**
     * @var boolean
     */
    public $debug = false;
    
    /**
     * {@inheritDoc}
     * @see \luya\testsuite\cases\BaseTestSuite::bootApplication()
     */
    public function bootApplication(Boot $boot)
    {
        $boot->applicationConsole();
    }
    
    private $_pid = 0;
    
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
     *
     * @since 1.0.3
     */
    public function assertUrlHomepageIsOk()
    {
        $this->assertUrlIsOk(null);
    }
    
    /**
     * Test an URL whether a page has response code 200
     *
     * @param string|array $url The base path on the current server. If array provided the first key is used as path and other values
     * are merged with $params attribte.
     * @param array $params Optional parameters to bind url with.
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
     * @param string|array $url The base path on the current server. If array provided the first key is used as path and other values
     * are merged with $params attribte.
     * @param array $params Optional parameters to bind url with.
     * @since 1.0.3
     */
    public function assertUrlIsError($url, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertTrue($curl->isError(), "GET URL '{$url}' return {$curl->http_status_code} instead of 400 (Error).");
    }

    /**
     * Test whether url is redirect.
     *
     * @param string|array $url The base path on the current server. If array provided the first key is used as path and other values
     * are merged with $params attribte.
     * @param array $params Optional parameters to bind url with.
     * @since 1.0.3
     */
    public function assertUrlIsRedirect($url, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertTrue($curl->isRedirect(), "GET URL '{$url}' return {$curl->http_status_code} instead of 300 (Error).");
    }
    
    /**
     * Test an url and see if the response contains.
     *
     * @param string|array $url The base path on the current server. If array provided the first key is used as path and other values
     * are merged with $params attribte.
     * @param string|array $contains If its an array it will be json encoded by default and the first and last char (wrapping)
     * brackets are cute off, so you can easy search for a key value parining inside the json response.
     * @param array $params Optional parameters to bind url with
     * @since 1.0.3
     */
    public function assertUrlGetResponseContains($url, $contains, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertContains($this->buildPartialJson($contains, true), $curl->response);
    }
    
    /**
     * Make a GET request and see if the response is the same as.
     *
     * @param string|array $url The base path on the current server. If array provided the first key is used as path and other values
     * are merged with $params attribte.
     * @param string|array $contains If its an array it will be json encoded by default and the first and last char (wrapping).a
     * brackets are cute off, so you can easy search for a key value parining inside the json response.
     * @param array $data The data to post on the $url ($_POST data).
     * @param array $params Optional parameters to bind url with.
     * @since 1.0.3
     */
    public function assertUrlGetResponseSame($url, $same, array $params = [])
    {
        $curl = $this->createGetCurl($url, $params);
        $this->assertSame($this->buildPartialJson($same), $curl->response);
    }
    
    /**
     * Make a POST request and see if the response contains in.
     *
     * @param string|array $url The base path on the current server. If array provided the first key is used as path and other values
     * are merged with $params attribte.
     * @param string|array $contains If its an array it will be json encoded by default and the first and last char (wrapping)
     * brackets are cute off, so you can easy search for a key value parining inside the json response.
     * @param array $data The data to post on the $url ($_POST data)
     * @param array $params Optional parameters to bind url with
     * @since 1.0.3
     */
    public function assertUrlPostResponseContains($url, $contains, array $data = [], array $params = [])
    {
        $curl = $this->createPostCurl($url, $data, $params);
        $this->assertContains($this->buildPartialJson($contains, true), $curl->response);
    }

    /**
     *
     * @param unknown $url
     * @param unknown $same
     * @param array $data
     * @since 1.0.3
     */
    public function assertUrlPostResponseSame($url, $same, array $data = [], array $params = [])
    {
        $curl = $this->createPostCurl($url, $data, $params);
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
     * Build the url to call with current local host and port.
     *
     * If the url is an array the key is the path and the later key value paired are used for params.
     *
     * ```php
     * buildCallUrl(['path/to/api', 'access-token' => 123]);
     * ```
     *
     * is equals to:
     *
     * ```php
     * buildCallUrl('path/to/api', ['access-tokne' => 123]);
     * ```
     *
     * @param string|array $url The local base path to build the url from. If an array the first key is used for the path defintion.
     * @param array $params Optional key value paired arguments to build the url from.
     * @return string
     * @since 1.0.3
     */
    protected function buildCallUrl($url, array $params = [])
    {
        if (is_array($url)) {
            $path = $url[0];
            unset($url[0]);
            $params = array_merge($url, $params);
        } else {
            $path = $url;
        }
        
        $url = "{$this->host}:{$this->port}/" . ltrim($path, '/');
        
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
        $callUrl = $this->buildCallUrl($url, $params);
        $curl = (new Curl())->get($callUrl);
        
        if ($this->debug) {
            echo "GET DEBUG '$callUrl': " . $curl->response;
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
    protected function createPostCurl($url, array $data = [], array $params = [])
    {
        $callUrl = $this->buildCallUrl($url, $params);
        $curl = (new Curl())->post($callUrl, $data);
        
        if ($this->debug) {
            echo "POST DEBUG '$callUrl': " . $curl->response;
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
        $command = sprintf(PHP_BINARY . ' -S %s:%d -t %s >/dev/null 2>&1 & echo $!', $host, $port, $documentRoot);
        
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
