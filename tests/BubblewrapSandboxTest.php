<?php

namespace Greenn\Libs\Tests;

use Greenn\Libs\BubblewrapSandbox;
use Greenn\Libs\Exceptions\BubblewrapUnavailableException;
use InvalidArgumentException;
use Symfony\Component\Process\Process;

class BubblewrapSandboxTest extends TestCase
{
    /**
     * Simple test double that exposes internal helpers.
     */
    protected function makeExposedSandbox()
    {
        return new class(
            PHP_BINARY,
            BubblewrapSandbox::defaultBaseArgs(),
            BubblewrapSandbox::defaultReadOnlyBinds(),
            BubblewrapSandbox::defaultWritableBinds()
        ) extends BubblewrapSandbox {
            public function normalizePublic(array $binds)
            {
                return $this->normalizeBinds($binds);
            }

            public static function binaryExistsInPathPublic($binary)
            {
                return parent::binaryExistsInPath($binary);
            }

            protected function assertBubblewrapIsExecutable()
            {
                // Skip to avoid relying on bwrap in tests.
            }
        };
    }

    /**
     * @return BubblewrapSandbox
     */
    protected function makeSandbox()
    {
        $binary = PHP_BINARY; // ensure executable exists for tests

        return new BubblewrapSandbox(
            $binary,
            BubblewrapSandbox::defaultBaseArgs(),
            BubblewrapSandbox::defaultReadOnlyBinds(),
            BubblewrapSandbox::defaultWritableBinds()
        );
    }

    public function testBuildCommandIncludesBaseAndBinds()
    {
        $sandbox = $this->makeSandbox();

        $command = array('echo', 'hello');
        $extraBinds = array(
            array('from' => '/tmp/in.txt', 'to' => '/tmp/in.txt', 'read_only' => true),
            array('from' => '/tmp/out', 'to' => '/tmp/out', 'read_only' => false),
        );

        $result = $sandbox->buildCommand($command, $extraBinds);

        $this->assertSame($command[0], $result[count($result) - 2]);
        $this->assertSame($command[1], $result[count($result) - 1]);
        $this->assertContains('--unshare-all', $result);
        $this->assertContains('--ro-bind', $result);
        $this->assertContains('/tmp/out', $result);
    }

    public function testEmptyCommandThrows()
    {
        $sandbox = $this->makeSandbox();

        $this->expectExceptionCompat(InvalidArgumentException::class);
        $sandbox->buildCommand(array());
    }

    public function testThrowsWhenBubblewrapIsMissing()
    {
        $sandbox = new BubblewrapSandbox(
            'non-existent-bwrap-binary',
            BubblewrapSandbox::defaultBaseArgs(),
            BubblewrapSandbox::defaultReadOnlyBinds(),
            BubblewrapSandbox::defaultWritableBinds()
        );

        $this->expectExceptionCompat(BubblewrapUnavailableException::class);
        $sandbox->buildCommand(array('echo', 'test'));
    }

    public function testProcessBuildsProcessInstance()
    {
        $sandbox = new BubblewrapSandbox(PHP_BINARY, array(), array(), array());
        $process = $sandbox->process(array('echo', 'hi'), array(), null, null, 10);

        $this->assertInstanceOf(Process::class, $process);
        $this->assertEquals(10, $process->getTimeout());
        $this->assertNotFalse(strpos($process->getCommandLine(), 'echo'));
    }

    public function testRunUsesOverriddenProcess()
    {
        $sandbox = new class(PHP_BINARY, array(), array(), array()) extends BubblewrapSandbox {
            public $called = false;

            public function process(array $command, array $extraBinds = array(), $workingDirectory = null, array $env = null, $timeout = 60)
            {
                $this->called = true;
                return new Process(array(PHP_BINARY, '-r', 'echo "ok";'), null, null, null, 5);
            }

            protected function assertBubblewrapIsExecutable()
            {
                // Skip parent validation for test.
            }
        };

        $process = $sandbox->run(array('ignored'));

        $this->assertTrue($sandbox->called);
        $this->assertSame('ok', trim($process->getOutput()));
    }

    public function testFromConfigBuildsWithProvidedValues()
    {
        $config = array(
            'binary' => PHP_BINARY,
            'base_args' => array('--foo'),
            'read_only_binds' => array('/etc/ssl'),
            'write_binds' => array('/tmp/custom'),
        );

        $sandbox = BubblewrapSandbox::fromConfig($config);
        $built = $sandbox->buildCommand(array('echo', 'x'));

        $this->assertContains('--foo', $built);
        $this->assertContains('/etc/ssl', $built);
        $this->assertContains('/tmp/custom', $built);
    }

    public function testDefaultsExposeExpectedMounts()
    {
        $defaults = BubblewrapSandbox::defaultBaseArgs();
        $this->assertContains('--unshare-all', $defaults);
        $this->assertContains('--die-with-parent', $defaults);
        $this->assertContains('/proc', $defaults);

        $readOnly = BubblewrapSandbox::defaultReadOnlyBinds();
        $this->assertContains('/usr', $readOnly);

        $write = BubblewrapSandbox::defaultWritableBinds();
        $this->assertContains('/tmp', $write);
    }

    public function testNormalizeBindsHandlesStringsAndArrays()
    {
        $sandbox = $this->makeExposedSandbox();

        $normalized = $sandbox->normalizePublic(array(
            '/tmp/file',
            array('from' => '/a', 'to' => '/b'),
            123,
        ));

        $this->assertCount(2, $normalized);
        $this->assertTrue($normalized[0]['read_only']);
        $this->assertSame('/tmp/file', $normalized[0]['from']);
        $this->assertSame('/b', $normalized[1]['to']);
        $this->assertTrue($normalized[1]['read_only']);
    }

    public function testBinaryExistsInPathDetectsExecutable()
    {
        $sandbox = $this->makeExposedSandbox();

        $dir = sys_get_temp_dir() . '/bwrap_guard_' . uniqid();
        mkdir($dir);
        $binary = $dir . '/dummybin';
        file_put_contents($binary, "#!/bin/sh\necho dummy");
        @chmod($binary, 0755);

        $originalPath = getenv('PATH');
        putenv('PATH=' . $dir);

        $this->assertTrue($sandbox::binaryExistsInPathPublic('dummybin'));

        putenv('PATH=' . $originalPath);
        @unlink($binary);
        @rmdir($dir);
    }
}
