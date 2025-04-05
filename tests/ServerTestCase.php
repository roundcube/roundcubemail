<?php

namespace Roundcube\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Test class to test HTTP requests to Roundcube
 */
class ServerTestCase extends TestCase
{
    protected static $phpProcess;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        $path = realpath(__DIR__ . '/../public_html');
        $cmd = ['php', '-S', 'localhost:8000', '-t', $path];
        $env = [];

        static::$phpProcess = new Process($cmd, null, $env);
        static::$phpProcess->setWorkingDirectory($path);
        static::$phpProcess->start();
        usleep(100 * 1000); // give the server some time before we start testing
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        static::$phpProcess->stop();
    }

    /**
     * HTTP client request
     */
    protected function request($method, $path, $options = [])
    {
        $config = [
            'base_uri' => 'http://localhost:8000',
            'http_errors' => false, // no exceptions for HTTP error codes
            'handler' => null, // reset Mock state from other tests
        ];

        $client = \rcmail::get_instance()->get_http_client($config);

        return $client->request($method, $path, $options);
    }
}
