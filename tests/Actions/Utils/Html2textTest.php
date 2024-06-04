<?php

namespace Roundcube\Tests\Actions\Utils;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

/**
 * Test class to test rcmail_action_utils_html2text
 */
class Html2textTest extends ActionTestCase
{
    /**
     * Test for run()
     */
    public function test_run()
    {
        $object = new \rcmail_action_utils_html2text();
        $html = '<p>test</p>';
        $object::$source = $this->createTempFile($html);

        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'utils', 'html2text');

        $this->assertInstanceOf(\rcmail_action::class, $object);
        $this->assertTrue($object->checks());

        $this->runAndAssert($object, OutputHtmlMock::E_EXIT);

        $this->assertSame('test', $output->output);
        $this->assertSame(['Content-Type: text/plain; charset=UTF-8'], $output->headers);
    }
}
