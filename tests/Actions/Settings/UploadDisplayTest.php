<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_settings_upload_display
 */
class UploadDisplayTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_settings_upload_display();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }
}
