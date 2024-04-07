<?php

use PHPUnit\Framework\TestCase;

class VcardAttachments_Plugin extends TestCase
{
    /**
     * Plugin object construction test
     */
    public function test_constructor()
    {
        $rcube = rcube::get_instance();
        $plugin = new vcard_attachments($rcube->plugins);

        self::assertInstanceOf('vcard_attachments', $plugin);
        self::assertInstanceOf('rcube_plugin', $plugin);
    }

    /**
     * Test is_vcard()
     */
    public function test_is_vcard()
    {
        $rcube = rcube::get_instance();
        $plugin = new vcard_attachments($rcube->plugins);

        $part = new rcube_message_part();
        self::assertFalse(invokeMethod($plugin, 'is_vcard', [$part]));

        $part->mimetype = 'text/vcard';
        self::assertTrue(invokeMethod($plugin, 'is_vcard', [$part]));

        $part->mimetype = 'text/x-vcard';
        self::assertTrue(invokeMethod($plugin, 'is_vcard', [$part]));

        $part->mimetype = 'text/directory';
        self::assertFalse(invokeMethod($plugin, 'is_vcard', [$part]));

        $part->ctype_parameters['profile'] = 'vcard';
        self::assertTrue(invokeMethod($plugin, 'is_vcard', [$part]));

        $part->ctype_parameters['profile'] = 'unknown';
        $part->filename = 'vcard.vcf';
        self::assertTrue(invokeMethod($plugin, 'is_vcard', [$part]));
    }
}
