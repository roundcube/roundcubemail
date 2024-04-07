<?php

/**
 * Test class to test rcmail_action_mail_index
 */
class Actions_Mail_Index extends ActionTestCase
{
    /**
     * Test run() method in HTTP mode
     */
    public function test_run_http()
    {
        $action = new rcmail_action_mail_index();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $_GET = ['_uid' => 10];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('set_options')
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('set_charset')
            ->registerFunction('is_connected', true)
            ->registerFunction('set_folder')
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('get_threading', false)
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('get_capability', false)
            ->registerFunction('get_capability', false)
            ->registerFunction('set_folder')
            ->registerFunction('set_page')
            ->registerFunction('set_threading');

        $action->run();

        self::assertSame([], $output->headers);
        self::assertNull($output->getOutput());
        self::assertSame('Inbox', $output->getProperty('pagetitle'));
        self::assertSame('INBOX', $output->get_env('mailbox'));
        self::assertSame(10, $output->get_env('pagesize'));
        self::assertSame('/', $output->get_env('delimiter'));
        self::assertSame('widescreen', $output->get_env('layout'));
        self::assertSame('Drafts', $output->get_env('drafts_mailbox'));
        self::assertSame('Trash', $output->get_env('trash_mailbox'));
        self::assertSame('Junk', $output->get_env('junk_mailbox'));
        self::assertSame(10, $output->get_env('list_uid'));
    }

    /**
     * Test run() method in AJAX mode
     */
    public function test_run_ajax()
    {
        $action = new rcmail_action_mail_index();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'list');

        self::assertTrue($action->checks());

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('set_options')
            ->registerFunction('get_pagesize', 10)
            ->registerFunction('set_charset')
            ->registerFunction('is_connected', true)
            ->registerFunction('set_folder')
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('get_threading', false)
            ->registerFunction('get_pagesize')
            ->registerFunction('get_capability', false)
            ->registerFunction('get_capability', false)
            ->registerFunction('set_folder')
            ->registerFunction('set_page')
            ->registerFunction('set_threading');

        $action->run();

        self::assertSame([], $output->headers);
        self::assertNull($output->getOutput());
        self::assertSame('', $output->getProperty('pagetitle'));
        self::assertSame('INBOX', $output->get_env('mailbox'));
        self::assertSame(10, $output->get_env('pagesize'));
        self::assertSame(1, $output->get_env('current_page'));
        self::assertSame('/', $output->get_env('delimiter'));
        self::assertSame('widescreen', $output->get_env('layout'));
        self::assertSame('Drafts', $output->get_env('drafts_mailbox'));
        self::assertSame('Trash', $output->get_env('trash_mailbox'));
        self::assertSame('Junk', $output->get_env('junk_mailbox'));
    }

    /**
     * Test message_list_smart_column_name() method
     */
    public function test_message_list_smart_column_name()
    {
        $action = new rcmail_action_mail_index();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'list');

        $output->set_env('mailbox', 'INBOX');
        self::assertSame('from', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Drafts');
        self::assertSame('to', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Drafts/Subfolder');
        self::assertSame('to', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Sent');
        self::assertSame('to', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Sent/Subfolder');
        self::assertSame('to', $action->message_list_smart_column_name());
    }

    /**
     * Test message_list() method
     */
    public function test_message_list()
    {
        $action = new rcmail_action_mail_index();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', 'list');

        self::mockStorage()->registerFunction('get_folder', 'INBOX');

        $result = $action->message_list([]);

        self::assertMatchesRegularExpression('/^<table id="rcubemessagelist".*<\/table>$/', $result);
        $listcols = ['threads', 'subject', 'status', 'fromto', 'date', 'size', 'flag', 'attachment'];
        self::assertSame($listcols, $output->get_env('listcols'));
    }

    /**
     * Test js_message_list() method
     */
    public function test_js_message_list()
    {
        $action = new rcmail_action_mail_index();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'list');

        self::mockStorage()
            ->registerFunction('get_search_set', null)
            ->registerFunction('get_threading', true)
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('get_folder', 'INBOX');

        $action->js_message_list([]);

        self::assertFalse($output->get_env('multifolder_listing'));
        $commands = $output->getProperty('commands');
        self::assertCount(1, $commands);
        self::assertSame('set_message_coltypes', $commands[0][0]);
    }

    /**
     * Test options_menu_link() method
     */
    public function test_options_menu_link()
    {
        $action = new rcmail_action_mail_index();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $link = $action->options_menu_link(['icon' => 'ico.png']);

        $expected = '<a href="#list-options" onclick="return rcmail.command(\'menu-open\', \'messagelistmenu\', this, event)"'
            . ' class="listmenu" id="listmenulink" title="List options..." tabindex="0"><img src="ico.png" alt="List options..."></a>';

        self::assertSame($expected, $link);
    }

    /**
     * Test messagecount_display() method
     */
    public function test_messagecount_display()
    {
        self::markTestIncomplete();
    }

    /**
     * Test get_messagecount_text() method
     */
    public function test_get_messagecount_text()
    {
        self::markTestIncomplete();
    }

    /**
     * Test send_unread_count() method
     */
    public function test_send_unread_count()
    {
        self::markTestIncomplete();
    }

    /**
     * Test check_safe() method
     */
    public function test_check_safe()
    {
        self::markTestIncomplete();
    }

    /**
     * Test part_image_type() method
     */
    public function test_part_image_type()
    {
        self::markTestIncomplete();
    }

    /**
     * Test address_string() method
     */
    public function test_address_string()
    {
        $action = new rcmail_action_mail_index();

        self::assertNull($action->address_string(''));

        $result = $action->address_string('test@domain.com');
        $expected = '<span class="adr"><span title="test@domain.com" class="rcmContactAddress">test@domain.com</span></span>';

        self::assertSame($expected, $result);

        $result = $action->address_string('test@domain.com', null, true, true);
        $expected = '<span class="adr"><a href="mailto:test@domain.com" class="rcmContactAddress" '
            . 'onclick="return rcmail.command(\'compose\',\'test@domain.com\',this)" title="test@domain.com">'
            . 'test@domain.com</a><a href="#add" title="Add to address book" class="rcmaddcontact" '
            . 'onclick="return rcmail.command(\'add-contact\',\'test@domain.com\',this)"></a></span>';

        self::assertSame($expected, $result);

        setProperty($action, 'PRINT_MODE', true);

        $result = $action->address_string('test@domain.com');
        $expected = '<span class="adr">&lt;test@domain.com&gt;</span>';

        self::assertSame($expected, $result);
    }

    /**
     * Test attachment_name() method
     */
    public function test_attachment_name()
    {
        $action = new rcmail_action_mail_index();
        $part = new rcube_message_part();
        $part->mime_id = '1';

        $part->mimetype = 'text/html';
        self::assertSame('HTML Message', $action->attachment_name($part));

        $part->mimetype = 'application/pdf';
        self::assertSame('Part 1.pdf', $action->attachment_name($part));

        $part->filename = 'test.pdf';
        self::assertSame('test.pdf', $action->attachment_name($part));
    }

    /**
     * Test search_filter() method
     */
    public function test_search_filter()
    {
        self::markTestIncomplete();
    }

    /**
     * Test search_interval() method
     */
    public function test_search_interval()
    {
        self::markTestIncomplete();
    }

    /**
     * Test message_error() method
     */
    public function test_message_error()
    {
        self::markTestIncomplete();
    }

    /**
     * Test message_import_form() method
     */
    public function test_message_import_form()
    {
        self::markTestIncomplete();
    }

    /**
     * Helper method to create a HTML message part object
     */
    protected function get_html_part($body = null)
    {
        $part = new rcube_message_part();
        $part->ctype_primary = 'text';
        $part->ctype_secondary = 'html';
        $part->body = $body ? file_get_contents(TESTS_DIR . $body) : null;
        $part->replaces = [];

        return $part;
    }

    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_index();

        self::assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test sanitization of a "normal" html message
     */
    public function test_html()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/htmlbody.txt');
        $part->replaces = ['ex1.jpg' => 'part_1.2.jpg', 'ex2.jpg' => 'part_1.2.jpg'];

        $params = ['container_id' => 'foo'];

        // render HTML in normal mode
        $body = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => false]);
        $html = rcmail_action_mail_index::html4inline($body, $params);

        self::assertMatchesRegularExpression('/src="' . $part->replaces['ex1.jpg'] . '"/', $html, 'Replace reference to inline image');
        self::assertMatchesRegularExpression('#background="program/resources/blocked.gif"#', $html, 'Replace external background image');
        self::assertDoesNotMatchRegularExpression('/ex3.jpg/', $html, 'No references to external images');
        self::assertDoesNotMatchRegularExpression('/<meta [^>]+>/', $html, 'No meta tags allowed');
        self::assertDoesNotMatchRegularExpression('/<form [^>]+>/', $html, 'No form tags allowed');
        self::assertMatchesRegularExpression('/Subscription form/', $html, 'Include <form> contents');
        self::assertMatchesRegularExpression('/<!-- link ignored -->/', $html, 'No external links allowed');
        self::assertMatchesRegularExpression('/<a[^>]+ target="_blank"/', $html, 'Set target to _blank');
        // self::assertTrue($GLOBALS['REMOTE_OBJECTS'], "Remote object detected");

        // render HTML in safe mode
        $body = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);
        $html = rcmail_action_mail_index::html4inline($body, $params);

        self::assertMatchesRegularExpression('/<style [^>]+>/', $html, 'Allow styles in safe mode');
        self::assertMatchesRegularExpression('#src="http://evilsite.net/mailings/ex3.jpg"#', $html, 'Allow external images in HTML (safe mode)');
        self::assertMatchesRegularExpression("#url\\('?http://evilsite.net/newsletter/image/bg/bg-64.jpg'?\\)#", $html, 'Allow external images in CSS (safe mode)');
        $css = '<link rel="stylesheet" .+_action=modcss.+_u=tmp-[a-z0-9]+\.css';
        self::assertMatchesRegularExpression('#' . $css . '#Ui', $html, 'Filter (anonymized) external stylesheets with utils/modcss.php');
    }

    /**
     * Test the elimination of some trivial XSS vulnerabilities
     */
    public function test_html_xss()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/htmlxss.txt');
        $washed = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);

        self::assertDoesNotMatchRegularExpression('/src="skins/', $washed, 'Remove local references');
        self::assertDoesNotMatchRegularExpression('/\son[a-z]+/', $washed, 'Remove on* attributes');
        self::assertStringNotContainsString('onload', $washed, 'Handle invalid style');

        $params = ['container_id' => 'foo'];
        $html = rcmail_action_mail_index::html4inline($washed, $params);

        self::assertDoesNotMatchRegularExpression('/onclick="return rcmail.command(\'compose\',\'xss@somehost.net\',this)"/', $html, 'Clean mailto links');
        self::assertDoesNotMatchRegularExpression('/alert/', $html, 'Remove alerts');
    }

    /**
     * Test HTML sanitization to fix the CSS Expression Input Validation Vulnerability
     * reported at http://www.securityfocus.com/bid/26800/
     */
    public function test_html_xss2()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/BID-26800.txt');
        $params = ['container_id' => 'dabody', 'safe' => true];
        $body = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);
        $washed = rcmail_action_mail_index::html4inline($body, $params);

        self::assertDoesNotMatchRegularExpression('/alert|expression|javascript|xss/', $washed, 'Remove evil style blocks');
        self::assertDoesNotMatchRegularExpression('/font-style:italic/', $washed, 'Allow valid styles');
    }

    /**
     * Test the elimination of some XSS vulnerabilities
     */
    public function test_html_xss3()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        // #1488850
        $html = '<p><a href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">Firefox</a>'
            . '<a href="vbscript:alert(document.cookie)">Internet Explorer</a></p>';
        $washed = rcmail_action_mail_index::wash_html($html, ['safe' => true], []);

        self::assertDoesNotMatchRegularExpression('/data:text/', $washed, 'Remove data:text/html links');
        self::assertDoesNotMatchRegularExpression('/vbscript:/', $washed, 'Remove vbscript: links');
    }

    /**
     * Test handling of body style attributes
     */
    public function test_html4inline_body_style()
    {
        $html = '<body background="test" bgcolor="#fff" style="font-size:11px" text="#000"><p>test</p></body>';
        $params = ['container_id' => 'foo'];
        $html = rcmail_action_mail_index::html4inline($html, $params);

        self::assertMatchesRegularExpression('/<div style="font-size:11px">/', $html, 'Body attributes');
        self::assertArrayHasKey('container_attrib', $params, "'container_attrib' param set");
        self::assertMatchesRegularExpression('/background-color: #fff;/', $params['container_attrib']['style'], 'Body style (bgcolor)');
        self::assertMatchesRegularExpression('/background-image: url\(test\)/', $params['container_attrib']['style'], 'Body style (background)');
        self::assertMatchesRegularExpression('/color: #000/', $params['container_attrib']['style'], 'Body style (text)');
    }

    /**
     * Test washtml class on non-unicode characters (#1487813)
     *
     * @group mbstring
     */
    public function test_washtml_utf8()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/invalidchars.html');
        $washed = rcmail_action_mail_index::print_body($part->body, $part);

        self::assertMatchesRegularExpression('/<p>(символ|симол)<\/p>/', $washed, 'Remove non-unicode characters from HTML message body');
    }

    /**
     * Test inserting meta tag with required charset definition
     */
    public function test_meta_insertion()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $meta = '<meta charset="' . RCUBE_CHARSET . '" />';
        $args = [
            'html_elements' => ['html', 'body', 'meta', 'head'],
            'html_attribs' => ['charset'],
        ];

        $body = '<html><head><meta charset="iso-8859-1_X"></head><body>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        self::assertStringContainsString("<html><head>{$meta}</head><body>Test1", $washed, 'Meta tag insertion (1)');

        $body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /></head><body>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        self::assertStringContainsString("<html><head>{$meta}</head><body>Test1", $washed, 'Meta tag insertion (2)');

        $body = 'Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        self::assertTrue(strpos($washed, "<html><head>{$meta}</head>") === 0, 'Meta tag insertion (3)');

        $body = '<html>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        self::assertTrue(strpos($washed, "<html><head>{$meta}</head>") === 0, 'Meta tag insertion (4)');

        $body = '<html><head></head>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        self::assertTrue(strpos($washed, "<html><head>{$meta}</head>") === 0, 'Meta tag insertion (5)');

        $body = '<html><head></head><body>Test1<br>Test2<meta charset="utf-8"></body>';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        self::assertTrue(strpos($washed, "<html><head>{$meta}</head>") === 0, 'Meta tag insertion (6)');
        self::assertTrue(strpos($washed, 'Test2</body>') > 0, 'Meta tag insertion (7)');
    }

    /**
     * Test links pattern replacements in plaintext messages
     */
    public function test_plaintext()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = new rcube_message_part();
        $part->ctype_primary = 'text';
        $part->ctype_secondary = 'plain';
        $part->body = quoted_printable_decode(file_get_contents(TESTS_DIR . 'src/plainbody.txt'));
        $html = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);

        self::assertMatchesRegularExpression(
            '/<a href="mailto:nobody@roundcube.net" onclick="return rcmail.command\(\'compose\',\'nobody@roundcube.net\',this\)">nobody@roundcube.net<\/a>/',
            $html,
            'Mailto links with onclick'
        );
        self::assertMatchesRegularExpression(
            '#<a rel="noreferrer" target="_blank" href="http://www.apple.com/legal/privacy">http://www.apple.com/legal/privacy</a>#',
            $html,
            'Links with target=_blank'
        );
        self::assertMatchesRegularExpression(
            '#\[<a rel="noreferrer" target="_blank" href="http://example.com/\?tx\[a\]=5">http://example.com/\?tx\[a\]=5</a>\]#',
            $html,
            'Links with square brackets'
        );
    }

    /**
     * Test mailto links in html messages
     */
    public function test_mailto()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/mailto.txt');
        $params = ['container_id' => 'foo'];

        // render HTML in normal mode
        $body = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => false]);
        $html = rcmail_action_mail_index::html4inline($body, $params);

        $mailto = '<a href="mailto:me@me.com"'
            . ' onclick="return rcmail.command(\'compose\',\'me@me.com?subject=this is the subject&amp;body=this is the body\',this)" rel="noreferrer">e-mail</a>';

        self::assertMatchesRegularExpression('|' . preg_quote($mailto, '|') . '|', $html, 'Extended mailto links');
    }

    /**
     * Test the elimination of HTML comments
     */
    public function test_html_comments()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/htmlcom.txt');
        $washed = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);

        // #1487759
        self::assertMatchesRegularExpression('|<p>test1</p>|', $washed, 'Buggy HTML comments');
        // but conditional comments (<!--[if ...) should be removed
        self::assertDoesNotMatchRegularExpression('|<p>test2</p>|', $washed, 'Conditional HTML comments');
    }

    /**
     * Test link attribute modifications
     */
    public function test_html_links()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        // disable relative links
        $html = '<a href="/">test</a>';
        $body = rcmail_action_mail_index::print_body($html, $this->get_html_part(), ['safe' => false, 'plain' => false]);

        self::assertStringNotContainsString('href="/"', $body);
        self::assertStringContainsString('<a>', $body);

        $html = '<a href="https://roundcube.net">test</a>';
        $body = rcmail_action_mail_index::print_body($html, $this->get_html_part(), ['safe' => false, 'plain' => false]);

        // allow external links, add target and noreferrer
        self::assertStringContainsString('<a href="https://roundcube.net"', $body);
        self::assertStringContainsString(' target="_blank"', $body);
        self::assertStringContainsString(' rel="noreferrer"', $body);
    }

    /**
     * Test potential XSS with invalid attributes
     */
    public function test_html_link_xss()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $html = '<a style="x:><img src=x onerror=alert(1)//">test</a>';
        $body = rcmail_action_mail_index::print_body($html, $this->get_html_part(), ['safe' => false, 'plain' => false]);

        self::assertStringNotContainsString('onerror=alert(1)//">test', $body);
        self::assertStringContainsString('<a style="x: &gt;"', $body);
    }

    /**
     * Test supported_mimetypes() method
     */
    public function test_supported_mimetypes()
    {
        self::markTestIncomplete();
    }
}
