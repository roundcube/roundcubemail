<?php

namespace Roundcube\Mail\Tests\Actions\Settings;

use Roundcube\Mail\Tests\ActionTestCase;

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

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
