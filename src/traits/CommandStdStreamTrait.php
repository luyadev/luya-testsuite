<?php

namespace luya\testsuite\traits;

/**
 * Console trait for {{\luya\console\Command}} stubs to replacement STDIN/STDOUT/STDERR with a array stack.
 *
 * Example
 *
 * ```php
 * class ControllerStub extend Controller
 * {
 *      use CommandStdStreamTrait;
 * 
 *      public function testUserAbort()
 *      {
 *          $controller = new ControllerStub();
 *
 *          // send a user input for the first prompt
 *          $controller->sendInput('yes');
 *          // send a user input for the second prompt
 *          $controller->sendInput('no');
 *
 *          $controller->actionIndex();
 *
 *          $this->assertEquals('User answer with yes.', $controller->readOutput());
 *          $this->assertEquals('User answer with no.', $controller->readOutput());
 *      }
 * }
 * ```
 *
 * @author Bennet Klarhoelter <boehsermoe@me.com>
 * @since 1.0.19
 */
trait CommandStdStreamTrait
{
    /**
     * @var array input stream
     */
    public $inputStream = [];
    
    /**
     * @var array output stream
     */
    public $outputStream = [];
    
    /**
     * @var array error stream
     */
    public $errorStream = [];
    
    /**
     * Replace the {{\yii\console\Controller::stdin}} function.
     *
     * @param bool $raw
     * @return mixed|string
     */
    public function stdin($raw = false)
    {
        return $raw ? array_shift($this->inputStream) : rtrim(array_shift($this->inputStream), PHP_EOL);
    }
    
    /**
     * Replace the {{\yii\console\Controller::stdout}} function.
     * @param $string
     */
    public function stdout($string)
    {
        $this->outputStream[] = $string;
    }
    
    /**
     * Replace the {{\yii\console\Controller::stderr}} function.
     *
     * @param $string
     */
    public function stderr($string)
    {
        $this->errorStream[] = $string;
    }
    
    /**
     * Replace the {{\luya\console\Controller::output}} function.
     *
     * @param      $message
     * @param null $color
     */
    protected function output($message, $color = null)
    {
        $this->outputStream[] = $message;
    }
    
    /**
     * Replace {{\yii\console\Controller::prompt}} function.
     *
     * @param       $text
     * @param array $options
     *
     * @return mixed|string
     */
    public function prompt($text, $options = [])
    {
        return $this->stdin();
    }
    
    /**
     * Replace {{\yii\console\Controller::confirm}} function.
     *
     * @param      $message
     * @param bool $default
     *
     * @return bool
     */
    public function confirm($message, $default = false)
    {
        return $this->stdin() == 'yes';
    }
    
    /**
     * Replace {{\yii\console\Controller::select}} function.
     *
     * @param       $prompt
     * @param array $options
     *
     * @return mixed|string
     */
    public function select($prompt, $options = [])
    {
        return $this->stdin();
    }
    
    /**
     * Clean the console streams
     */
    public function truncateStreams()
    {
        $this->inputStream = [];
        $this->outputStream = [];
        $this->errorStream = [];
    }
    
    /**
     * Read data from console output stream
     *
     * @return string
     */
    public function readOutput()
    {
        return array_shift($this->outputStream);
    }
    
    /**
     * Read data from console error outpur stream
     *
     * @return string
     */
    public function readError()
    {
        return array_shift($this->errorStream);
    }
    
    /**
     * Write passed input to the console input stream.
     *
     * @param string $input
     */
    public function sendInput(string $input)
    {
        $this->inputStream[] = $input;
    }
}
