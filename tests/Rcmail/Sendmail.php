<?php

/**
 * Test class to test rcmail_sendmail class
 *
 * @package Tests
 */
class Rcmail_RcmailSendmail extends ActionTestCase
{
    /**
     * Test rcmail_sendmail::headers_input()
     */
    function test_headers_input()
    {
        $_POST = [
            '_subject' => "Test1\nTest2",
            '_from' => 'Sender <test@domain.tld>',
        ];

        $sendmail = new rcmail_sendmail();
        $headers = $sendmail->headers_input();

        $this->assertSame('Test1 Test2', $headers['Subject']);
        $this->assertSame('Sender <test@domain.tld>', $headers['From']);
        $this->assertSame('undisclosed-recipients:;', $headers['To']);
        $this->assertSame('test@domain.tld', $headers['X-Sender']);
    }

    /**
     * Test rcmail_sendmail::set_message_encoding()
     */
    function test_set_message_encoding()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::create_message()
     */
    function test_create_message()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::deliver_message()
     */
    function test_deliver_message()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::save_message()
     */
    function test_save_message()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::header_received()
     */
    function test_header_received()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::get_identity()
     */
    function test_get_identity()
    {
        self::initDB('identities');
        self::initUser();

        $db       = rcmail::get_instance()->get_dbh();
        $query    = $db->query('SELECT * FROM `identities` WHERE `standard` = 1 LIMIT 1');
        $identity = $db->fetch_assoc($query);
        $sendmail = new rcmail_sendmail();

        $result = $sendmail->get_identity($identity['identity_id']);

        $this->assertSame($identity['identity_id'], $result['identity_id']);
        $this->assertSame('test <test@example.com>', $result['string']);
        $this->assertSame('test@example.com', $result['mailto']);
    }

    /**
     * Test rcmail_sendmail::extract_inline_images()
     */
    function test_extract_inline_images()
    {
        $this->markTestIncomplete();
    }

    /**
     * Data for test_convert()
     */
    function data_email_input_format()
    {
        return [
            [
                'name <t@domain.jp>',
                'name <t@domain.jp>',
                'UTF-8'
            ],
            [
                '"first last" <t@domain.jp>',
                'first last <t@domain.jp>',
                'UTF-8'
            ],
            [
                '"first last" <t@domain.jp>, test2@domain.tld,',
                'first last <t@domain.jp>, test2@domain.tld',
                'UTF-8'
            ],
            [
                '<test@domain.tld>',
                'test@domain.tld',
                'UTF-8'
            ],
            [
                'test@domain.tld',
                'test@domain.tld',
                'UTF-8'
            ],
            [
                'test@domain.tld.', // #7899
                'test@domain.tld',
                'UTF-8'
            ],
            [
                'ö <t@test.com>',
                'ö <t@test.com>',
                null
            ],
            [
                base64_decode('GyRCLWo7M3l1OSk2SBsoQg==') . ' <t@domain.jp>',
                '=?ISO-2022-JP?B?GyRCLWo7M3l1OSk2SBsoQg==?= <t@domain.jp>',
                'ISO-2022-JP'
            ],
            [
                'test@тест.рф.', // #8493
                'test@xn--e1aybc.xn--p1ai',
                'UTF-8',
            ],
        ];
    }

    /**
     * @dataProvider data_email_input_format
     */
    function test_email_input_format($input, $output, $charset)
    {
        $sendmail = new rcmail_sendmail();
        $sendmail->options['charset'] = $charset;

        $this->assertEquals($output, $sendmail->email_input_format($input));
    }

    /**
     * Test rcmail_sendmail::generic_message_footer()
     */
    function test_generic_message_footer()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::draftinfo_encode() and draftinfo_decode()
     */
    function test_draftinfo_encode_and_decode()
    {
        $input  = ['test' => 'test'];
        $result = rcmail_sendmail::draftinfo_encode($input);
        $this->assertEquals('test=test', $result);
        $this->assertEquals($input, rcmail_sendmail::draftinfo_decode($result));

        $input  = ['folder' => 'test'];
        $result =  rcmail_sendmail::draftinfo_encode($input);
        $this->assertEquals('folder=B::dGVzdA==', $result);
        $this->assertEquals($input, rcmail_sendmail::draftinfo_decode($result));

        $input  = ['test' => 'test;test'];
        $result = rcmail_sendmail::draftinfo_encode($input);
        $this->assertEquals('test=B::dGVzdDt0ZXN0', $result);
        $this->assertEquals($input, rcmail_sendmail::draftinfo_decode($result));

        $input  = ['test' => 'test;test', 'a' => 'b'];
        $result = rcmail_sendmail::draftinfo_encode($input);
        $this->assertEquals('test=B::dGVzdDt0ZXN0; a=b', $result);
        $this->assertEquals($input, rcmail_sendmail::draftinfo_decode($result));
    }

    /**
     * Test rcmail_sendmail::headers_output()
     */
    function test_headers_output()
    {
        $message = new StdClass;
        $message->headers = new rcube_message_header;
        $message->headers->charset = 'UTF-8';
        $message->headers->to = '';
        $message->headers->from = '';
        $message->headers->cc = '';

        $sendmail = new rcmail_sendmail();
        $sendmail->options['charset'] = RCUBE_CHARSET;
        $sendmail->options['message'] = $message;

        $result = $sendmail->headers_output(['part' => 'to']);
        $this->assertTrue(strpos($result, '<textarea name="_to" spellcheck="false"></textarea>') !== false);

        $result = $sendmail->headers_output(['part' => 'from']);
        $this->assertTrue(strpos($result, '<input name="_from" class="from_address" type="text">') !== false);

        // TODO: Test part=from with identities
        $this->markTestIncomplete();
    }

    /**
     * Test rcmail_sendmail::reply_subject()
     */
    function test_reply_subject()
    {
        $this->assertSame('Re: Test subject', rcmail_sendmail::reply_subject('Test subject'));
        $this->assertSame('Re: Test subject', rcmail_sendmail::reply_subject('Re: Test subject'));
        $this->assertSame('Re: Test subject', rcmail_sendmail::reply_subject('Re: Re: Test subject'));
        $this->assertSame('Re: Test subject', rcmail_sendmail::reply_subject('Re: Test subject (Was: Something else)'));
    }

    /**
     * Test rcmail_sendmail::compose_subject()
     */
    function test_compose_subject()
    {
        $sendmail = new rcmail_sendmail();
        $sendmail->options['charset'] = RCUBE_CHARSET;
        $sendmail->options['mode'] = rcmail_sendmail::MODE_REPLY;

        $_POST = ['_subject' => 'test'];

        $result = $sendmail->compose_subject([]);

        $this->assertTrue(strpos($result, '<input name="_subject" spellcheck="true" value="test" type="text">') !== false);
    }

    /**
     * Test rcmail_sendmail::mdn_checkbox()
     */
    function test_mdn_checkbox()
    {
        $sendmail = new rcmail_sendmail();
        $sendmail->options['charset'] = RCUBE_CHARSET;
        $sendmail->options['mode'] = rcmail_sendmail::MODE_REPLY;

        $result = $sendmail->mdn_checkbox([]);

        $this->assertTrue(strpos($result, '<input id="receipt" name="_mdn" value="1" type="checkbox">') !== false);
    }

    /**
     * Test rcmail_sendmail::dsn_checkbox()
     */
    function test_dsn_checkbox()
    {
        $sendmail = new rcmail_sendmail();
        $sendmail->options['charset'] = RCUBE_CHARSET;
        $sendmail->options['mode'] = rcmail_sendmail::MODE_REPLY;

        $result = $sendmail->dsn_checkbox([]);

        $this->assertTrue(strpos($result, '<input id="dsn" name="_dsn" value="1" type="checkbox">') !== false);
    }

    /**
     * Test rcmail_sendmail::priority_selector()
     */
    function test_priority_selector()
    {
        $sendmail = new rcmail_sendmail();
        $sendmail->options['charset'] = RCUBE_CHARSET;
        $sendmail->options['mode'] = rcmail_sendmail::MODE_REPLY;

        $result = $sendmail->priority_selector([]);

        $expected = '<select name="_priority">' . "\n"
            . '<option value="5">Lowest</option>'
            . '<option value="4">Low</option>'
            . '<option value="0" selected="selected">Normal</option>'
            . '<option value="2">High</option>'
            . '<option value="1">Highest</option>'
            . '</select>';

        $this->assertTrue(strpos($result, $expected) !== false);
    }

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
        $identities = [
            [
                'name' => 'Test',
                'email_ascii' => 'addr@domain.tld',
                'ident' => 'Test <addr@domain.tld>',
            ],
            [
                'name' => 'Test',
                'email_ascii' => 'thing@domain.tld',
                'ident' => 'Test <thing@domain.tld>',
            ],
            [
                'name' => 'Test',
                'email_ascii' => 'other@domain.tld',
                'ident' => 'Test <other@domain.tld>',
            ],
        ];

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
        $identities = [
            [
                'name' => 'Test 1',
                'email_ascii' => 'addr1@domain.tld',
                'ident' => 'Test 1 <addr1@domain.tld>',
            ],
            [
                'name' => 'Test 2',
                'email_ascii' => 'addr2@domain.tld',
                'ident' => 'Test 2 <addr2@domain.tld>',
            ],
            [
                'name' => 'Test 3',
                'email_ascii' => 'addr3@domain.tld',
                'ident' => 'Test 3 <addr3@domain.tld>',
            ],
            [
                'name' => 'Test 4',
                'email_ascii' => 'addr2@domain.tld',
                'ident' => 'Test 4 <addr2@domain.tld>',
            ],
        ];

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

    /**
     * Test rcmail_sendmail::collect_recipients()
     */
    function test_collect_recipients()
    {
        $this->markTestIncomplete();
    }
}
