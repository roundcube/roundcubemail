<?php

/**
 * Test class to test rcmail_action_mail_index
 *
 * @package Tests
 */
class Actions_Mail_Index extends ActionTestCase
{
    /**
     * Test run() method in HTTP mode
     */
    function test_run_http()
    {
        $action = new rcmail_action_mail_index;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $_GET = ['_uid' => 10];

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
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

        $this->assertSame([], $output->headers);
        $this->assertNull($output->getOutput());
        $this->assertSame('Inbox', $output->getProperty('pagetitle'));
        $this->assertSame('INBOX', $output->get_env('mailbox'));
        $this->assertSame(10, $output->get_env('pagesize'));
        $this->assertSame('/', $output->get_env('delimiter'));
        $this->assertSame('widescreen', $output->get_env('layout'));
        $this->assertSame('Drafts', $output->get_env('drafts_mailbox'));
        $this->assertSame('Trash', $output->get_env('trash_mailbox'));
        $this->assertSame('Junk', $output->get_env('junk_mailbox'));
        $this->assertSame(10, $output->get_env('list_uid'));
    }

    /**
     * Test run() method in AJAX mode
     */
    function test_run_ajax()
    {
        $action = new rcmail_action_mail_index;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'list');

        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('set_options')
            ->registerFunction('get_pagesize')
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

        $this->assertSame([], $output->headers);
        $this->assertNull($output->getOutput());
        $this->assertSame('', $output->getProperty('pagetitle'));
        $this->assertSame('INBOX', $output->get_env('mailbox'));
        $this->assertSame(10, $output->get_env('pagesize'));
        $this->assertSame(1, $output->get_env('current_page'));
        $this->assertSame('/', $output->get_env('delimiter'));
        $this->assertSame('widescreen', $output->get_env('layout'));
        $this->assertSame('Drafts', $output->get_env('drafts_mailbox'));
        $this->assertSame('Trash', $output->get_env('trash_mailbox'));
        $this->assertSame('Junk', $output->get_env('junk_mailbox'));
    }

    /**
     * Test message_list_smart_column_name() method
     */
    function test_message_list_smart_column_name()
    {
        $action = new rcmail_action_mail_index;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'list');

        $output->set_env('mailbox', 'INBOX');
        $this->assertSame('from', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Drafts');
        $this->assertSame('to', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Drafts/Subfolder');
        $this->assertSame('to', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Sent');
        $this->assertSame('to', $action->message_list_smart_column_name());

        $output->set_env('mailbox', 'Sent/Subfolder');
        $this->assertSame('to', $action->message_list_smart_column_name());
    }

    /**
     * Test message_list() method
     */
    function test_message_list()
    {
        $action = new rcmail_action_mail_index;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', 'list');

        rcmail::get_instance()->storage->registerFunction('get_folder', 'INBOX');

        $result = $action->message_list([]);

        $this->assertMatchesRegularExpression('/^<table id="rcubemessagelist".*<\/table>$/', $result);
        $listcols = ['threads', 'subject', 'status', 'fromto', 'date', 'size', 'flag', 'attachment'];
        $this->assertSame($listcols, $output->get_env('listcols'));
    }

    /**
     * Test js_message_list() method
     */
    function test_js_message_list()
    {
        $action = new rcmail_action_mail_index;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'list');

        rcmail::get_instance()->storage
            ->registerFunction('get_search_set', null)
            ->registerFunction('get_threading', true)
            ->registerFunction('get_folder', 'INBOX')
            ->registerFunction('get_folder', 'INBOX');

        $action->js_message_list([]);

        $this->assertSame(false, $output->get_env('multifolder_listing'));
        $commands = $output->getProperty('commands');
        $this->assertCount(1, $commands);
        $this->assertSame('set_message_coltypes', $commands[0][0]);
    }

    /**
     * Test options_menu_link() method
     */
    function test_options_menu_link()
    {
        $action = new rcmail_action_mail_index;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $link = $action->options_menu_link(['icon' => 'ico.png']);

        $expected = '<a href="#list-options" onclick="return rcmail.command(\'menu-open\', \'messagelistmenu\', this, event)"'
            . ' class="listmenu" id="listmenulink" title="List options..." tabindex="0"><img src="ico.png" alt="List options..."></a>';

        $this->assertSame($expected, $link);
    }

    /**
     * Test messagecount_display() method
     */
    function test_messagecount_display()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test get_messagecount_text() method
     */
    function test_get_messagecount_text()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test send_unread_count() method
     */
    function test_send_unread_count()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test check_safe() method
     */
    function test_check_safe()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test part_image_type() method
     */
    function test_part_image_type()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test address_string() method
     */
    function test_address_string()
    {
        $action = new rcmail_action_mail_index;

        $this->assertSame(null, $action->address_string(''));

        $result = $action->address_string('test@domain.com');
        $expected = '<span class="adr"><span title="test@domain.com" class="rcmContactAddress">test@domain.com</span></span>';

        $this->assertSame($expected, $result);

        $result = $action->address_string('test@domain.com', null, true, true);
        $expected = '<span class="adr"><a href="mailto:test@domain.com" class="rcmContactAddress" '
            . 'onclick="return rcmail.command(\'compose\',\'test@domain.com\',this)" title="test@domain.com">'
            . 'test@domain.com</a><a href="#add" title="Add to address book" class="rcmaddcontact" '
            . 'onclick="return rcmail.command(\'add-contact\',\'test@domain.com\',this)"></a></span>';

        $this->assertSame($expected, $result);

        setProperty($action, 'PRINT_MODE', true);

        $result = $action->address_string('test@domain.com');
        $expected = '<span class="adr">&lt;test@domain.com&gt;</span>';

        $this->assertSame($expected, $result);
    }

    /**
     * Test attachment_name() method
     */
    function test_attachment_name()
    {
        $action = new rcmail_action_mail_index;
        $part = new rcube_message_part();
        $part->mime_id = 1;

        $part->mimetype = 'text/html';
        $this->assertSame('HTML Message', $action->attachment_name($part));

        $part->mimetype = 'application/pdf';
        $this->assertSame('Part 1.pdf', $action->attachment_name($part));

        $part->filename = 'test.pdf';
        $this->assertSame('test.pdf', $action->attachment_name($part));
    }

    /**
     * Test search_filter() method
     */
    function test_search_filter()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test search_interval() method
     */
    function test_search_interval()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test message_error() method
     */
    function test_message_error()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test message_import_form() method
     */
    function test_message_import_form()
    {
        $this->markTestIncomplete();
    }

    /**
     * Helper method to create a HTML message part object
     */
    protected function get_html_part($body = null)
    {
        $part = new rcube_message_part;
        $part->ctype_primary   = 'text';
        $part->ctype_secondary = 'html';
        $part->body = $body ? file_get_contents(TESTS_DIR . $body) : null;
        $part->replaces = [];

        return $part;
    }

    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_index;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test sanitization of a "normal" html message
     */
    function test_html()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/htmlbody.txt');
        $part->replaces = ['ex1.jpg' => 'part_1.2.jpg', 'ex2.jpg' => 'part_1.2.jpg'];

        $params = ['container_id' => 'foo', 'safe' => false];

        // render HTML in normal mode
        $html = \rcmail_action_mail_index::print_body($part->body, $part, $params);
 
        $this->assertMatchesRegularExpression('/src="'.$part->replaces['ex1.jpg'].'"/', $html, "Replace reference to inline image");
        $this->assertMatchesRegularExpression('#background="program/resources/blocked.gif"#', $html, "Replace external background image");
        $this->assertDoesNotMatchRegularExpression('/ex3.jpg/', $html, "No references to external images");
        $this->assertDoesNotMatchRegularExpression('/<meta [^>]+>/', $html, "No meta tags allowed");
        $this->assertDoesNotMatchRegularExpression('/<form [^>]+>/', $html, "No form tags allowed");
        $this->assertMatchesRegularExpression('/Subscription form/', $html, "Include <form> contents");
        $this->assertMatchesRegularExpression('/<!-- link ignored -->/', $html, "No external links allowed");
        $this->assertMatchesRegularExpression('/<a[^>]+ target="_blank"/', $html, "Set target to _blank");
//        $this->assertTrue($GLOBALS['REMOTE_OBJECTS'], "Remote object detected");

        // render HTML in safe mode
        $params['safe'] = true;
        $html = \rcmail_action_mail_index::print_body($part->body, $part, $params);

        $this->assertMatchesRegularExpression('/<style [^>]+>/', $html, "Allow styles in safe mode");
        $this->assertMatchesRegularExpression('#src="http://evilsite.net/mailings/ex3.jpg"#', $html, "Allow external images in HTML (safe mode)");
        $this->assertMatchesRegularExpression("#url\('?http://evilsite.net/newsletter/image/bg/bg-64.jpg'?\)#", $html, "Allow external images in CSS (safe mode)");
        $css = '<link rel="stylesheet" .+_action=modcss.+_u=tmp-[a-z0-9]+\.css';
        $this->assertMatchesRegularExpression('#'.$css.'#Ui', $html, "Filter (anonymized) external stylesheets with utils/modcss.php");
    }

    /**
     * Test the elimination of some trivial XSS vulnerabilities
     */
    function test_html_xss()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part   = $this->get_html_part('src/htmlxss.txt');
        $params = ['container_id' => 'foo', 'safe' => true];
        $html = \rcmail_action_mail_index::print_body($part->body, $part, $params);

        $this->assertDoesNotMatchRegularExpression('/src="skins/', $html, 'Remove local references');
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+/', $html, 'Remove on* attributes');
        $this->assertStringNotContainsString('onload', $html, 'Handle invalid style');
        $this->assertDoesNotMatchRegularExpression('/onclick="return rcmail.command(\'compose\',\'xss@somehost.net\',this)"/', $html, "Clean mailto links");
        $this->assertDoesNotMatchRegularExpression('/alert/', $html, "Remove alerts");
    }

    /**
     * Test HTML sanitization to fix the CSS Expression Input Validation Vulnerability
     * reported at http://www.securityfocus.com/bid/26800/
     */
    function test_html_xss2()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part   = $this->get_html_part('src/BID-26800.txt');
        $params = ['container_id' => 'dabody', 'safe' => true];
        $washed = \rcmail_action_mail_index::print_body($part->body, $part, $params);

        $this->assertDoesNotMatchRegularExpression('/alert|expression|javascript|xss/', $washed, "Remove evil style blocks");
        $this->assertDoesNotMatchRegularExpression('/font-style:italic/', $washed, "Allow valid styles");
    }

    /**
     * Test the elimination of some XSS vulnerabilities
     */
    function test_html_xss3()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        // #1488850
        $html = '<p><a href="data:text/html,&lt;script&gt;alert(document.cookie)&lt;/script&gt;">Firefox</a>'
            .'<a href="vbscript:alert(document.cookie)">Internet Explorer</a></p>';
        $washed = rcmail_action_mail_index::wash_html($html, ['safe' => true], []);

        $this->assertDoesNotMatchRegularExpression('/data:text/', $washed, "Remove data:text/html links");
        $this->assertDoesNotMatchRegularExpression('/vbscript:/', $washed, "Remove vbscript: links");
    }

    /**
     * Test that HTML sanitization does not change attribute (evil) values
     */
    public function test_html_body_attributes()
    {
        $part = $this->get_html_part();
        $part->body = '<body title="bgcolor=foo" name="bar style=animation-name:progress-bar-stripes onanimationstart=alert(origin) foo=bar">Foo</body>';

        $params = ['safe' => true, 'add_comments' => false];
        $washed = \rcmail_action_mail_index::print_body($part->body, $part, $params);

        $this->assertSame(str_replace('body', 'div', $part->body), $washed);

        $params['inline_html'] = false;
        $washed = \rcmail_action_mail_index::print_body($part->body, $part, $params);

        $this->assertSame('<html><head></head>' . $part->body . '</html>', $washed);
    }

    /**
     * Test handling of body style attributes
     */
    public function test_wash_html_body_style()
    {
        $html = '<body background="http://test.com/image" bgcolor="#fff" style="font-size: 11px" text="#000"><p>test</p></body>';
        $params = ['container_id' => 'foo', 'add_comments' => false, 'safe' => false];
        $washed = \rcmail_action_mail_index::wash_html($html, $params, []);

        $this->assertSame('<div id="foo" style="font-size: 11px; background-image: url(program/resources/blocked.gif); background-color: #fff; color: #000"><p>test</p></div>', $washed);

        $params['safe'] = true;
        $washed = \rcmail_action_mail_index::wash_html($html, $params, []);
 
        $this->assertSame('<div id="foo" style="font-size: 11px; background-image: url(http://test.com/image); background-color: #fff; color: #000"><p>test</p></div>', $washed);
    }

    /**
     * Test washtml class on non-unicode characters (#1487813)
     * @group mbstring
     */
    function test_washtml_utf8()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part   = $this->get_html_part('src/invalidchars.html');
        $washed = rcmail_action_mail_index::print_body($part->body, $part);

        $this->assertMatchesRegularExpression('/<p>(символ|симол)<\/p>/', $washed, "Remove non-unicode characters from HTML message body");
    }

    /**
     * Test inserting meta tag with required charset definition
     */
    function test_meta_insertion()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $meta = '<meta charset="'.RCUBE_CHARSET.'" />';
        $args = [
            'inline_html' => false,
            'html_elements' => ['html', 'body', 'meta', 'head'],
            'html_attribs'  => ['charset'],
        ];

        $body   = '<html><head><meta charset="iso-8859-1_X"></head><body>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        $this->assertStringContainsString("<html><head>$meta</head><body>Test1", $washed, "Meta tag insertion (1)");

        $body   = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" /></head><body>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        $this->assertStringContainsString("<html><head>$meta</head><body>Test1", $washed, "Meta tag insertion (2)");

        $body   = 'Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        $this->assertTrue(strpos($washed, "<html><head>$meta</head>") === 0, "Meta tag insertion (3)");

        $body   = '<html>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        $this->assertTrue(strpos($washed, "<html><head>$meta</head>") === 0, "Meta tag insertion (4)");

        $body   = '<html><head></head>Test1<br>Test2';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        $this->assertTrue(strpos($washed, "<html><head>$meta</head>") === 0, "Meta tag insertion (5)");

        $body   = '<html><head></head><body>Test1<br>Test2<meta charset="utf-8"></body>';
        $washed = rcmail_action_mail_index::wash_html($body, $args);
        $this->assertTrue(strpos($washed, "<html><head>$meta</head>") === 0, "Meta tag insertion (6)");
        $this->assertTrue(strpos($washed, "Test2</body>") > 0, "Meta tag insertion (7)");
    }

    /**
     * Test links pattern replacements in plaintext messages
     */
    function test_plaintext()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = new rcube_message_part;
        $part->ctype_primary   = 'text';
        $part->ctype_secondary = 'plain';
        $part->body = quoted_printable_decode(file_get_contents(TESTS_DIR . 'src/plainbody.txt'));
        $html = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);

        $this->assertMatchesRegularExpression(
            '/<a href="mailto:nobody@roundcube.net" onclick="return rcmail.command\(\'compose\',\'nobody@roundcube.net\',this\)">nobody@roundcube.net<\/a>/',
            $html,
            "Mailto links with onclick"
        );
        $this->assertMatchesRegularExpression(
            '#<a rel="noreferrer" target="_blank" href="http://www.apple.com/legal/privacy">http://www.apple.com/legal/privacy</a>#',
            $html,
            "Links with target=_blank"
        );
        $this->assertMatchesRegularExpression(
            '#\\[<a rel="noreferrer" target="_blank" href="http://example.com/\\?tx\\[a\\]=5">http://example.com/\\?tx\\[a\\]=5</a>\\]#',
            $html,
            "Links with square brackets"
        );
    }

    /**
     * Test mailto links in html messages
     */
    function test_mailto()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part   = $this->get_html_part('src/mailto.txt');
        $params = ['container_id' => 'foo', 'safe' => false];

        // render HTML in normal mode
        $html = \rcmail_action_mail_index::print_body($part->body, $part, $params);

        $mailto = '<a href="mailto:me@me.com"'
            .' onclick="return rcmail.command(\'compose\',\'me@me.com?subject=this is the subject&amp;body=this is the body\',this)" rel="noreferrer">e-mail</a>';

        $this->assertMatchesRegularExpression('|'.preg_quote($mailto, '|').'|', $html, "Extended mailto links");
    }

    /**
     * Test the elimination of HTML comments
     */
    function test_html_comments()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $part = $this->get_html_part('src/htmlcom.txt');
        $washed = rcmail_action_mail_index::print_body($part->body, $part, ['safe' => true]);

        // #1487759
        $this->assertMatchesRegularExpression('|<p>test1</p>|', $washed, "Buggy HTML comments");
        // but conditional comments (<!--[if ...) should be removed
        $this->assertDoesNotMatchRegularExpression('|<p>test2</p>|', $washed, "Conditional HTML comments");
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

        $this->assertStringNotContainsString('href="/"', $body);
        $this->assertStringContainsString('<a>', $body);

        $html = '<a href="https://roundcube.net">test</a>';
        $body = rcmail_action_mail_index::print_body($html, $this->get_html_part(), ['safe' => false, 'plain' => false]);

        // allow external links, add target and noreferrer
        $this->assertStringContainsString('<a href="https://roundcube.net"', $body);
        $this->assertStringContainsString(' target="_blank"', $body);
        $this->assertStringContainsString(' rel="noreferrer"', $body);
    }

    /**
     * Test potential XSS with invalid attributes
     */
    public function test_html_link_xss()
    {
        $this->initOutput(rcmail_action::MODE_HTTP, 'mail', '');

        $html = '<a style="x:><img src=x onerror=alert(1)//">test</a>';
        $body = rcmail_action_mail_index::print_body($html, $this->get_html_part(), ['safe' => false, 'plain' => false]);

        $this->assertStringNotContainsString('onerror=alert(1)//">test', $body);
        $this->assertStringContainsString('<a style="x: &gt;"', $body);
    }

    /**
     * Test supported_mimetypes() method
     */
    function test_supported_mimetypes()
    {
        $this->markTestIncomplete();
    }
}
