<?php

namespace Roundcube\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Masterminds\HTML5;

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Environment initialization script for unit tests                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

error_reporting(\E_ALL);

if (\PHP_SAPI != 'cli') {
    exit('Not in shell mode (php-cli)');
}

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');
}

define('ROUNDCUBE_TEST_MODE', true);
define('ROUNDCUBE_TEST_SESSION', microtime(true));
define('TESTS_DIR', __DIR__ . '/');

if (@is_dir(TESTS_DIR . 'config')) {
    define('RCUBE_CONFIG_DIR', TESTS_DIR . 'config');
}

// Some tests depend on the way phpunit is executed
$_SERVER['SCRIPT_NAME'] = 'vendor/bin/phpunit';

require_once INSTALL_PATH . 'program/include/iniset.php';

\rcmail::get_instance(0, 'test')->config->set('devel_mode', false);

// Initialize database and environment
ActionTestCase::init();

/**
 * Call protected/private method of a object.
 *
 * @param object $object     Object instance
 * @param string $method     Method name to call
 * @param array  $parameters array of parameters to pass into method
 * @param string $class      Object class
 *
 * @return mixed method return
 */
function invokeMethod($object, $method, array $parameters = [], $class = null)
{
    $reflection = new \ReflectionClass($class ?: get_class($object));

    $method = $reflection->getMethod($method);
    $method->setAccessible(true);

    return $method->invokeArgs($object, $parameters);
}

/**
 * Get value of a protected/private property of a object.
 *
 * @param \rcube_sieve_vacation $object Object
 * @param string                $name   Property name
 * @param string                $class  Object  class
 *
 * @return mixed Property value
 */
function getProperty($object, $name, $class = null)
{
    $reflection = new \ReflectionClass($class ?: get_class($object));

    $property = $reflection->getProperty($name);
    $property->setAccessible(true);

    return $property->getValue($object);
}

/**
 * Set protected/private property of a object.
 *
 * @param \rcube_sieve_vacation $object Object
 * @param string                $name   Property name
 * @param mixed                 $value  Property value
 * @param string                $class  Object  class
 */
function setProperty($object, $name, $value, $class = null): void
{
    $reflection = new \ReflectionClass($class ?: get_class($object));

    $property = $reflection->getProperty($name);
    $property->setAccessible(true);

    $property->setValue($object, $value);
}

/**
 * Parse HTML content and extract nodes by XPath query
 *
 * @param string $html        HTML content
 * @param string $xpath_query XPath query
 *
 * @return \DOMNodeList List of nodes found
 */
function getHTMLNodes($html, $xpath_query)
{
    $html5 = new HTML5(['disable_html_ns' => true]);
    $doc = $html5->loadHTML($html);

    $xpath = new \DOMXPath($doc);

    return $xpath->query($xpath_query);
}

/**
 * Mock Guzzle HTTP Client
 */
function setHttpClientMock(array $responses)
{
    foreach ($responses as $idx => $response) {
        if (is_array($response)) {
            $responses[$idx] = new Response(
                $response['code'] ?? 200,
                $response['headers'] ?? [],
                $response['response'] ?? ''
            );
        }
    }

    $mock = new MockHandler($responses);
    $handler = HandlerStack::create($mock);
    $rcube = \rcube::get_instance();

    $rcube->config->set('http_client', ['handler' => $handler]);
}
