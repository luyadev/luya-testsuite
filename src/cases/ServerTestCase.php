<?php

namespace luya\testsuite\cases;

use Curl\Curl;
use Exception;
use Yii;
use luya\base\Boot;

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
 *       $this->isHomepageOK();
 *       $this->isUrlOK('about');
 *       $this->isUrlOK('about/me');
 *       $this->isUrlNOK('errorpage');
 *   }
 * }
 * ```
 * 
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.2
 */
abstract class ServerTestCase extends BaseTestSuite
{
    public $host = 'localhost';
    
    public $port = '1549';
    
    public $documentRoot = '@app/public_html';
    
    private $_pid = 0;
    
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
    }
    
    /**
     * Check whether homage is online and OK response.
     */
    public function isHomepageOK()
    {
        $this->isUrlOK(null);
    }
    
    /**
     * Test an URL whether a page has response code 200
     *
     * @param string $url
     */
    public function isUrlOK($url)
    {
        $this->assertTrue($this->curlUrl($url)->isSuccess(), "URL '{$url}' does not return OK (200).");
    }
    
    /**
     * Test an URL whether a page has response code 400
     * 
     * @param string $url
     */
    public function isUrlNOK($url)
    {
        $this->assertTrue($this->curlUrl($url)->isError(), "URL '{$url}' does not return NOK (400).");
    }

    /**
     * @param unknown $url
     * @return \Curl\Curl
     */
    protected function curlUrl($url)
    {
        $url = "{$this->host}:{$this->port}/" . ltrim($url, '/');
        $curl = new Curl();
        $curl->get($url);
        
        return $curl;
    }
    
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
     */
    protected function createServer($host, $port, $documentRoot)
    {
        $command = sprintf(PHP_BINARY . ' -S %s:%d -t %s >/dev/null 2>&1 & echo $!', $host, $port,  $documentRoot);
        
        // Execute the command and store the process ID
        $output = [];
        exec($command, $output);
        
        return (int) $output[0];
    }
    
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
    
    protected function connectToServer($host, $port)
    {
        $fp = @fsockopen($host, $port, $errno, $errstr, 3);
        if ($fp === false) {
            return false;
        }
        fclose($fp);
        return true;
    }
    
    protected function killServer($pid)
    {
        exec('kill -9 ' . (int) $pid);
    }
}