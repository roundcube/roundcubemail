<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcube_session class
 */
class SessionTest extends TestCase
{
    /**
     * Test factory method
     */
    public function test_factory()
    {
        $rcube = \rcube::get_instance();

        // We cannot test DB session handler as it's initialization
        // will collide with already sent headers. Let's try php session.
        $rcube->config->set('session_storage', 'php');

        $session = \rcube_session::factory($rcube->config);

        $this->assertInstanceOf(\rcube_session_php::class, $session);

        // This method should not do any harm, just call it and expect no errors
        $session->reload();
    }

    public function test_concurrent_writes_preserve_independent_compose_sessions()
    {
        $rcube = \rcube::get_instance();
        if (!in_array($rcube->get_dbh()->db_provider, ['mysql', 'sqlite'], true) || $rcube->get_dbh()->is_replicated()) {
            $this->markTestSkipped('Requires a single MySQL or SQLite session database.');
        }

        $first = new class ($rcube->config) extends \rcube_session_db {
            public function register_session_handler()
            {
                // Exercise the DB handler directly without registering it with PHP.
            }

            public function begin($id)
            {
                $this->start = microtime(true);
                $this->set_lifetime(600);

                return $this->read($id);
            }
        };
        $second = clone $first;
        $id = bin2hex(random_bytes(16));
        try {
            $first->begin($id);
            $this->assertTrue($first->write($id, 'language|s:2:"en";'));
            $a = $first->begin($id);
            $b = $second->begin($id);
            $this->assertTrue($first->write($id, $a . 'compose_data_first|a:0:{}'));
            $this->assertTrue($second->write($id, $b . 'compose_data_second|a:0:{}'));
            $state = $first->unserialize($first->begin($id));
            $this->assertArrayHasKey('compose_data_first', $state);
            $this->assertArrayHasKey('compose_data_second', $state);

            // A background write must also respect a completed editor's removal.
            $b = $second->begin($id);
            $first->remove('compose_data_first');
            $this->assertTrue($first->write($id, $a . 'compose_data_second|a:0:{}'));
            $this->assertTrue($second->write($id, $b . 'page|i:2;'));
            $state = $first->unserialize($first->begin($id));
            $this->assertArrayNotHasKey('compose_data_first', $state);
            $this->assertSame(2, $state['page']);
        } finally {
            $first->destroy($id);
        }
    }

    /**
     * Test unserialize() method
     */
    public function test_unserialize()
    {
        $rcube = \rcube::get_instance();

        $rcube->config->set('session_storage', 'php');

        $session = \rcube_session::factory($rcube->config);

        $this->assertSame([], $session->unserialize(''));
        $this->assertSame(
            ['ok' => true, 'name' => 'me', 'int' => 34],
            $session->unserialize('ok|b:1;name|s:2:"me";int|i:34;')
        );

        // Infinit loop fix
        $this->expectException(\Exception::class);
        $this->assertSame([], $session->unserialize('k1|s:-100:"x";'));
    }
}
