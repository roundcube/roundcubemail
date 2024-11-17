<?php

namespace Roundcube\Tests\Actions\Utils;

use rcmail_action as rcmail_action;
use rcmail_action_utils_spell_html as rcmail_action_utils_spell_html;
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
