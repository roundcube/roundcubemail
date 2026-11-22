<?php

namespace Roundcube\Tests\Actions\Contacts;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\ExitException;
use Roundcube\Tests\OutputHtmlMock;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_contacts_photo
 */
class PhotoTest extends ActionTestCase
{
    public const PHOTO_URL = 'https://photos.example.com/contact.png';
    public const PNG = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    /**
     * Test run() method - no photo case
     */
    public function test_no_photo()
    {
        $action = new \rcmail_action_contacts_photo();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'photo');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: image/gif', $output->headers);
        $this->assertSame(base64_decode(\rcmail_output::BLANK_GIF, true), $result);

        $_GET = ['_error' => 1];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('HTTP/1.0 204 Photo not found', $output->headers);
        $this->assertSame('', $result);
    }

    /**
     * Test run() method - a contact with real photo
     */
    public function test_photo()
    {
        $action = new \rcmail_action_contacts_photo();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'photo');

        self::initDB('contacts');
        $cid = self::import_contact('inline.photo@example.com', 'PHOTO;ENCODING=b;TYPE=PNG:' . self::PNG);

        // A photo stored in the contact does not depend on the show_photo_urls option
        foreach ([false, true] as $show_photo_urls) {
            \rcmail::get_instance()->config->set('show_photo_urls', $show_photo_urls);

            $_GET = ['_email' => 'inline.photo@example.com', '_error' => 1];
            $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

            $this->assertContains('Content-Type: image/png', $output->headers);
            $this->assertContains('Cache-Control: max-age=86400', $output->headers);
            $this->assertSame(base64_decode(self::PNG, true), $output->getOutput());

            $_GET = ['_cid' => $cid, '_source' => '0'];
            $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

            $this->assertContains('Content-Type: image/png', $output->headers);
            $this->assertSame(base64_decode(self::PNG, true), $output->getOutput());
        }

        \rcmail::get_instance()->config->set('show_photo_urls', false);
    }

    /**
     * Test run() method - a contact with a photo URL, external photos not allowed
     */
    public function test_photo_url_not_allowed()
    {
        $action = new \rcmail_action_contacts_photo();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'photo');

        self::initDB('contacts');
        $cid = self::import_contact('url.photo@example.com', 'PHOTO;VALUE=uri:' . self::PHOTO_URL);

        \rcmail::get_instance()->config->set('show_photo_urls', false);

        // The answer is the same as for a contact without a photo
        $_GET = ['_email' => 'url.photo@example.com'];
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertContains('Content-Type: image/gif', $output->headers);
        $this->assertSame(base64_decode(\rcmail_output::BLANK_GIF, true), $output->getOutput());
        // not cached, so that allowing external photos takes effect at once
        $this->assertNotContains('Cache-Control: max-age=86400', $output->headers);

        $_GET = ['_email' => 'url.photo@example.com', '_error' => 1];
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertContains('HTTP/1.0 204 Photo not found', $output->headers);
        $this->assertSame('', $output->getOutput());

        $_GET = ['_cid' => $cid, '_source' => '0'];
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertContains('Content-Type: image/gif', $output->headers);
        $this->assertSame(base64_decode(\rcmail_output::BLANK_GIF, true), $output->getOutput());

        // Like for a contact without a photo, the photo of another contact with the same address is used
        self::import_contact('url.photo@example.com', 'PHOTO;ENCODING=b;TYPE=PNG:' . self::PNG, 'Zz');

        $_GET = ['_email' => 'url.photo@example.com', '_error' => 1];
        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $this->assertContains('Content-Type: image/png', $output->headers);
        $this->assertSame(base64_decode(self::PNG, true), $output->getOutput());
    }

    /**
     * Test run() method - a contact with a photo URL, external photos allowed
     */
    public function test_photo_url_allowed()
    {
        $action = new \rcmail_action_contacts_photo();
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'contacts', 'photo');

        self::initDB('contacts');
        $cid = self::import_contact('url.photo@example.com', 'PHOTO;VALUE=uri:' . self::PHOTO_URL);

        \rcmail::get_instance()->config->set('show_photo_urls', true);

        foreach ([['_email' => 'url.photo@example.com', '_error' => 1], ['_cid' => $cid, '_source' => '0']] as $get) {
            $_GET = $get;
            $output->reset(true);

            try {
                $action->run();
                $this->fail('Expected a redirect to the photo URL');
            } catch (ExitException $e) {
                $this->assertSame(OutputHtmlMock::E_REDIRECT, $e->getCode());
                $this->assertSame('Location: ' . self::PHOTO_URL, $e->getMessage());
            }
        }

        \rcmail::get_instance()->config->set('show_photo_urls', false);
    }

    /**
     * Store a contact in the personal address book, the same way as the vCard import does
     *
     * @param string $email   Email address
     * @param string $photo   PHOTO line of the vCard
     * @param string $surname Surname
     *
     * @return string Contact ID
     */
    public static function import_contact($email, $photo, $surname = 'Aa')
    {
        $vcard = new \rcube_vcard("BEGIN:VCARD\r\nVERSION:3.0\r\nFN:Photo {$surname}\r\nN:{$surname};Photo;;;\r\n"
            . "EMAIL;TYPE=INTERNET:{$email}\r\n{$photo}\r\nEND:VCARD");

        $record = $vcard->get_assoc();
        $record['vcard'] = $vcard->export();

        return \rcmail::get_instance()->get_address_book('0')->insert($record);
    }
}
