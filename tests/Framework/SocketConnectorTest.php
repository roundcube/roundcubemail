<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/** Deterministic connection scheduling without network access. */
class SocketConnectorTest extends TestCase
{
    public function testSchedulingAndCleanup(): void
    {
        foreach ([
            ['delayed-ipv6', [\INF, 0.01], true, 0.26, 2],
            ['fast-ipv6', [0.01, 0.01], true, 0.01, 1],
            ['immediate-failure', [-1, 0.01], true, 0.01, 2],
            ['overall-timeout', [\INF, \INF], false, 1.0, 2],
        ] as [$label, $delays, $expected, $elapsed, $attempts]) {
            $connector = new SocketConnectorSimulator($delays);
            $stream = $connector->run();
            $this->assertSame($expected, is_resource($stream), $label);
            $this->assertEqualsWithDelta($elapsed, $connector->clock, 0.001, $label);
            $this->assertCount($attempts, $connector->started, $label);
            $this->assertCount($attempts - (int) $expected, $connector->closed, $label);
            if ($stream) {
                fclose($stream);
            }
        }
    }

    public function testInterleavingPreservesResolverPreference(): void
    {
        $this->assertSame(['::1', '127.0.0.1', '::2', '127.0.0.2'],
            \rcube_socket_connector::interleave(['::1', '::2', '127.0.0.1', '127.0.0.2']));
        $this->assertSame(['127.0.0.1', '::1', '127.0.0.2'],
            \rcube_socket_connector::interleave(['127.0.0.1', '127.0.0.2', '::1']));
    }

    public function testExplicitBindingKeepsLegacyTransport(): void
    {
        $connector = new \rcube_socket_connector();
        $this->assertNull($connector->connect('example.invalid', 993, 1,
            ['socket' => ['bindto' => '127.0.0.1:0']], $errno, $errstr));
    }

    public function testRealSocketConnection(): void
    {
        if (!extension_loaded('sockets')) {
            $this->markTestSkipped('The sockets extension is optional.');
        }
        $server = stream_socket_server('tcp://127.0.0.1:0', $errno, $errstr);
        $this->assertIsResource($server);
        $port = (int) substr(strrchr(stream_socket_get_name($server, false), ':'), 1);
        $connector = new \rcube_socket_connector();
        try {
            $client = $connector->connect('localhost', $port, 1, [], $errno, $errstr);
            $this->assertIsResource($client);
            $this->assertSame(0, $errno);
            $this->assertSame('', $errstr);
            fclose($client);
        } finally {
            fclose($server);
        }
    }
}

class SocketConnectorSimulator extends \rcube_socket_connector
{
    public $clock = 0.0;
    public $started = [];
    public $closed = [];
    private $delays;
    private $events = [];

    public function __construct(array $delays)
    {
        $this->delays = $delays;
    }

    public function run()
    {
        return $this->race(['::1', '127.0.0.1'], 993, 1.0, [], $errno, $errstr);
    }

    #[\Override]
    protected function now()
    {
        return $this->clock;
    }

    #[\Override]
    protected function start($address, $port, array $options, &$errno, &$errstr)
    {
        $this->started[] = [$address, $this->clock];
        $stream = fopen('php://temp', 'r+');
        $delay = array_shift($this->delays);
        $this->events[(int) $stream] = [$this->clock + max(0, $delay), $delay >= 0];
        return $stream;
    }

    #[\Override]
    protected function ready(array $streams, $timeout)
    {
        $this->clock = min($this->clock + $timeout,
            min(array_map(fn ($s) => $this->events[(int) $s][0], $streams)));
        return array_values(array_filter($streams,
            fn ($s) => $this->events[(int) $s][0] <= $this->clock));
    }

    #[\Override]
    protected function complete($stream, &$errno)
    {
        return $this->events[(int) $stream][1];
    }

    #[\Override]
    protected function close($stream)
    {
        $this->closed[] = (int) $stream;
        fclose($stream);
    }
}
