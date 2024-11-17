<?php

namespace Roundcube\Tests\Actions\Utils;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_utils_spell_html
 */
class SpellHtmlTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_utils_spell_html();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
