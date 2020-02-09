<?php

/**
 * Test class to test rcmail_sendmail class
 *
 * @package Tests
 */
class Rcmail_RcmailSendmail extends PHPUnit\Framework\TestCase
{
    /**
     * Test rcmail_sendmail::identity_select()
     */
    function test_identity_select()
    {
        $message = new StdClass;
        $message->headers = new rcube_message_header;
        $message->headers->charset = 'UTF-8';
        $message->headers->to = '';
        $message->headers->from = '';
        $message->headers->cc = '';
        $message->headers->other = [];

        $result = rcmail_sendmail::identity_select($message, []);
        $this->assertSame(null, $result);

        $identities = [
            [
                'identity_id' => 1,
                'user_id' => 1,
                'standard' => 1,
                'name' => 'Default',
                'email' => 'default@domain.tld',
                'email_ascii' => 'default@domain.tld',
                'ident' => 'Default <default@domain.tld>',
            ],
            [
                'identity_id' => 2,
                'user_id' => 1,
                'standard' => 0,
                'name' => 'Identity One',
                'email' => 'ident1@domain.tld',
                'email_ascii' => 'ident1@domain.tld',
                'ident' => '"Identity One" <ident1@domain.tld>',
            ],
            [
                'identity_id' => 3,
                'user_id' => 1,
                'standard' => 0,
                'name' => 'Identity Two',
                'email' => 'ident2@domain.tld',
                'email_ascii' => 'ident2@domain.tld',
                'ident' => '"Identity Two" <ident2@domain.tld>',
            ],
        ];

        $message->headers->to = 'ident2@domain.tld';
        $message->headers->from = 'from@other.domain.tld';

        $result = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[2], $result);

        $message->headers->to = 'ident1@domain.tld';
        $message->headers->from = 'from@other.domain.tld';

        $result = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[1], $result);

        // #7211
        $message->headers->to = 'ident1@domain.tld';
        $message->headers->from = 'ident2@domain.tld';

        $result = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[1], $result);

        $message->headers->to = 'ident2@domain.tld';
        $message->headers->from = 'ident1@domain.tld';

        $result = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[2], $result);
    }

    /**
     * Test identities selection using Return-Path header
     */
    function test_identity_select_return_path()
    {
        $identities = array(
            array(
                'name' => 'Test',
                'email_ascii' => 'addr@domain.tld',
                'ident' => 'Test <addr@domain.tld>',
            ),
            array(
                'name' => 'Test',
                'email_ascii' => 'thing@domain.tld',
                'ident' => 'Test <thing@domain.tld>',
            ),
            array(
                'name' => 'Test',
                'email_ascii' => 'other@domain.tld',
                'ident' => 'Test <other@domain.tld>',
            ),
        );

        $message = new stdClass;
        $message->headers = new rcube_message_header;
        $message->headers->set('Return-Path', '<some_thing@domain.tld>');
        $res = rcmail_sendmail::identity_select($message, $identities);

        $this->assertSame($identities[0], $res);

        $message->headers->set('Return-Path', '<thing@domain.tld>');
        $res = rcmail_sendmail::identity_select($message, $identities);

        $this->assertSame($identities[1], $res);
    }

    /**
     * Test identities selection (#1489378)
     */
    function test_identity_select_more()
    {
        $identities = array(
            array(
                'name' => 'Test 1',
                'email_ascii' => 'addr1@domain.tld',
                'ident' => 'Test 1 <addr1@domain.tld>',
            ),
            array(
                'name' => 'Test 2',
                'email_ascii' => 'addr2@domain.tld',
                'ident' => 'Test 2 <addr2@domain.tld>',
            ),
            array(
                'name' => 'Test 3',
                'email_ascii' => 'addr3@domain.tld',
                'ident' => 'Test 3 <addr3@domain.tld>',
            ),
            array(
                'name' => 'Test 4',
                'email_ascii' => 'addr2@domain.tld',
                'ident' => 'Test 4 <addr2@domain.tld>',
            ),
        );

        $message = new stdClass;
        $message->headers = new rcube_message_header;

        $message->headers->set('From', '<addr2@domain.tld>');
        $res = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[1], $res);

        $message->headers->set('From', 'Test 2 <addr2@domain.tld>');
        $res = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[1], $res);

        $message->headers->set('From', 'Other <addr2@domain.tld>');
        $res = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[1], $res);

        $message->headers->set('From', 'Test 4 <addr2@domain.tld>');
        $res = rcmail_sendmail::identity_select($message, $identities);
        $this->assertSame($identities[3], $res);
    }
}
