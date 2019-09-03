<?php

namespace luya\testsuite\tests\traits;

use luya\testsuite\traits\CommandStdStreamTrait;
use PHPUnit\Framework\TestCase;

class CommandStdStreamTraitTest extends TestCase
{
    public function testReadOutput()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->outputStream[] = 'foo';
        $this->assertEquals('foo', $cmdTrait->readOutput());
    }

    public function testStdin()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->inputStream[] = 'foo';
        $this->assertEquals('foo', $cmdTrait->stdin());
    }

    public function testStdout()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->stdout('foo');
        $this->assertEquals(['foo'], $cmdTrait->outputStream);
    }

    public function testPrompt()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->inputStream[] = 'foo';
        $this->assertEquals('foo', $cmdTrait->prompt('test'));
    }

    public function testSendInput()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->sendInput('foo');
        $this->assertEquals(['foo'], $cmdTrait->inputStream);
    }

    public function testReadError()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->errorStream[] = 'foo';
        $this->assertEquals('foo', $cmdTrait->readError());
    }

    public function testSelect()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->inputStream[] = 'foo';
        $this->assertEquals('foo', $cmdTrait->select('test'));
    }

    public function testStderr()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->stderr('foo');
        $this->assertEquals(['foo'], $cmdTrait->errorStream);
    }

    public function testConfirm()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->inputStream[] = 'yes';
        $this->assertTrue($cmdTrait->confirm('test'));
    }

    public function testTruncateStreams()
    {
        $cmdTrait = new CommandStdStreamTraitStub();
        $cmdTrait->inputStream[] = 'foo';
        $cmdTrait->outputStream[] = 'foo';
        $cmdTrait->errorStream[] = 'foo';

        $this->assertEquals(['foo'], $cmdTrait->inputStream);
        $this->assertEquals(['foo'], $cmdTrait->outputStream);
        $this->assertEquals(['foo'], $cmdTrait->errorStream);

        $cmdTrait->truncateStreams();

        $this->assertEmpty($cmdTrait->inputStream);
        $this->assertEmpty($cmdTrait->outputStream);
        $this->assertEmpty($cmdTrait->errorStream);
    }
}

class CommandStdStreamTraitStub
{
    use CommandStdStreamTrait;
}