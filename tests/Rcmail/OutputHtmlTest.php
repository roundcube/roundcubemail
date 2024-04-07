<?php

use PHPUnit\Framework\TestCase;

/**
 * Test class to test rcmail_output_html class
 */
class Rcmail_RcmailOutputHtml extends TestCase
{
    /**
     * Test check_skin()
     */
    public function test_check_skin()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertTrue($output->check_skin('elastic'));
        self::assertFalse($output->check_skin('unknown'));
    }

    /**
     * Test get_skin_file()
     */
    public function test_get_skin_file()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        $output->set_skin('elastic');

        self::assertSame('skins/elastic/ui.js', $output->get_skin_file('ui.js'));
        self::assertFalse($output->get_skin_file('unknown'));
    }

    /**
     * Test get_template_logo()
     */
    public function test_logo()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();
        $reflection = new ReflectionClass('rcmail_output_html');
        $set_skin = $reflection->getProperty('skin_name');
        $set_template = $reflection->getProperty('template_name');
        $get_template_logo = $reflection->getMethod('get_template_logo');

        $set_skin->setAccessible(true);
        $set_template->setAccessible(true);
        $get_template_logo->setAccessible(true);

        $set_skin->setValue($output, 'elastic');

        $rcmail->config->set('skin_logo', 'img00');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img00', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img00', $result);
        $result = $get_template_logo->invokeArgs($output, ['link']);
        self::assertNull($result);

        $rcmail->config->set('skin_logo', [
            'elastic:login[small]' => 'img01',
            'elastic:login' => 'img02',
            'elastic:*[small]' => 'img03',
            'larry:*' => 'img04',
            '*:login[small]' => 'img05',
            '*:login' => 'img06',
            '*[print]' => 'img07',
            '*' => 'img08',
        ]);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon']);
        self::assertNull($result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon', 'template']);
        self::assertSame('img02', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon', 'all']);
        self::assertSame('img02', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img01', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img02', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img03', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img08', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img08', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print']);
        self::assertSame('img07', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print', 'template']);
        self::assertSame('img07', $result);

        $set_skin->setValue($output, 'larry');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon']);
        self::assertNull($result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon', 'template']);
        self::assertSame('img06', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon', 'all']);
        self::assertSame('img04', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img05', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img04', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img04', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print', 'template']);
        self::assertSame('img07', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print']);
        self::assertSame('img07', $result);

        $set_skin->setValue($output, '_test_');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['favicon']);
        self::assertNull($result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['print', 'template']);
        self::assertSame('img06', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img05', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img06', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print']);
        self::assertSame('img07', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['_test_']);
        self::assertNull($result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img08', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print', 'template']);
        self::assertSame('img07', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print']);
        self::assertSame('img07', $result);

        $rcmail->config->set('skin_logo', [
            'elastic:login[small]' => 'img09',
            'elastic:login' => 'img10',
            'larry:*' => 'img11',
            'elastic[small]' => 'img12',
            'login[small]' => 'img13',
            'login' => 'img14',
            '[print]' => 'img15',
            '*' => 'img16',
        ]);

        $set_skin->setValue($output, 'elastic');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img09', $result);

        $set_template->setValue($output, 'mail');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertNull($result);

        $set_skin->setValue($output, '_test_');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['small']);
        self::assertSame('img13', $result);

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img14', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['print']);
        self::assertSame('img15', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['_test_', 'all']);
        self::assertSame('img16', $result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['_test_', 'template']);
        self::assertNull($result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, ['_test_']);
        self::assertNull($result);

        $set_template->setValue($output, '_test_');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img16', $result);

        $rcmail->config->set('skin_logo', [
            'elastic:[print]' => 'img17',
            'elastic:messageprint' => 'img18',
            'elastic:*' => 'img19',
        ]);

        $set_skin->setValue($output, 'elastic');

        $set_template->setValue($output, 'login');
        $result = $get_template_logo->invokeArgs($output, ['print']);
        self::assertSame('img17', $result);

        $set_template->setValue($output, 'messageprint');
        $result = $get_template_logo->invokeArgs($output, ['_test_', 'template']);
        self::assertSame('img18', $result);

        $set_template->setValue($output, 'contactprint');
        $result = $get_template_logo->invokeArgs($output, ['print', 'template']);
        self::assertSame('img17', $result);

        $set_template->setValue($output, 'contactprint');
        $result = $get_template_logo->invokeArgs($output, ['_test_', 'template']);
        self::assertNull($result);

        $set_template->setValue($output, 'contactprint');
        $result = $get_template_logo->invokeArgs($output, ['_test_', 'all']);
        self::assertSame('img19', $result);

        $set_template->setValue($output, 'contactprint');
        $result = $get_template_logo->invokeArgs($output, []);
        self::assertSame('img19', $result);
    }

    /**
     * Data for test_conditions()
     */
    public static function provide_conditions_cases(): iterable
    {
        $txt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt '
            . 'ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco '
            . 'laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in '
            . 'voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat '
            . 'non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';

        return [
            ["_start_<roundcube:if condition='1' />A<roundcube:endif />_end_", '_start_A_end_'],
            ["_start_<roundcube:if condition='0' />A<roundcube:else />B<roundcube:endif />_end_", '_start_B_end_'],
            ["_start_<roundcube:if condition='0'/>A<roundcube:else/>B<roundcube:endif/>_end_", '_start_B_end_'],
            ["_start_<roundcube:if condition='0'>A<roundcube:else>B<roundcube:endif>_end_", '_start_B_end_'],
            ["_start_<roundcube:if condition='0' />A<roundcube:elseif condition='1' />B<roundcube:else />C<roundcube:endif />_end_", '_start_B_end_'],
            ["_start_<roundcube:if condition='1' /><roundcube:if condition='0' />A<roundcube:else />B<roundcube:endif />C<roundcube:else />D<roundcube:endif />_end_", '_start_BC_end_'],
            ["_start_<roundcube:if condition='1' /><roundcube:if condition='1' />A<roundcube:else />B<roundcube:endif />C<roundcube:else />D<roundcube:endif />_end_", '_start_AC_end_'],
            ["_start_<roundcube:if condition='1' /><roundcube:if condition='0' />A<roundcube:elseif condition='1' />B<roundcube:else />C<roundcube:endif />D<roundcube:else />E<roundcube:endif />_end_", '_start_BD_end_'],
            ["_start_<roundcube:if condition='0' />A<roundcube:elseif condition='1' /><roundcube:if condition='0' />B<roundcube:else /><roundcube:if condition='1' />C<roundcube:endif />D<roundcube:endif /><roundcube:else />E<roundcube:endif />_end_", '_start_CD_end_'],
            ["_start_<roundcube:if condition='0'>A<roundcube:elseif condition='1'><roundcube:if condition='0'>B<roundcube:else><roundcube:if condition='1'>C<roundcube:endif>D<roundcube:endif><roundcube:else>E<roundcube:endif>_end_", '_start_CD_end_'],
            ["_start_<roundcube:if condition='1'>A<roundcube:elseif condition='1'>B<roundcube:elseif condition='1'>C<roundcube:endif>_end_", '_start_A_end_'],
            ["_start_<roundcube:if condition='0'>A<roundcube:elseif condition='1'>B<roundcube:elseif condition='1'>C<roundcube:endif>_end_", '_start_B_end_'],
            ["_start_<roundcube:if condition='0'>A<roundcube:elseif condition='0'>B<roundcube:elseif condition='1'>C<roundcube:endif>_end_", '_start_C_end_'],
            // #8065
            [
                "_start_<roundcube:if condition='0'>Condition 1 {$txt} {$txt}<roundcube:elseif condition='1'>Condition 2 {$txt} {$txt}"
                    . "<roundcube:elseif condition='0'>Condition 3 {$txt} {$txt}<roundcube:elseif condition='0'>Condition 4 {$txt} {$txt}"
                    . "<roundcube:elseif condition='0'>Condition 5 {$txt} {$txt}<roundcube:elseif condition='0'>Condition 6 {$txt} {$txt}"
                    . '<roundcube:endif>_end_',
                "_start_Condition 2 {$txt} {$txt}_end_",
            ],
            // some invalid code
            ["_start_<roundcube:if condition='1' />_end_", '_start__end_'],
            ["_start_<roundcube:if condition='0' />_end_", '_start_'],
            ["_start_<roundcube:if condition='1' />A<roundcube:else />_end_", '_start_A'],
            ["_start_<roundcube:if condition='1' />A<roundcube:elseif condition='1' />_end_", '_start_A'],
            ['_start_<roundcube:if />A<roundcube:endif />_end_', '_start__end_'],
        ];
    }

    /**
     * Test text to html conversion
     *
     * @dataProvider provide_conditions_cases
     */
    public function test_conditions($input, $output)
    {
        $object = new rcmail_output_html();
        $result = $object->just_parse($input);

        self::assertSame($output, $result);
    }

    /**
     * Test reset()
     */
    public function test_reset()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertNull($output->reset());
    }

    /**
     * Test abs_url()
     */
    public function test_abs_url()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertSame('test', $output->abs_url('test'));
        self::assertSame('skins/elastic/ui.js', $output->abs_url('/ui.js'));
    }

    /**
     * Test asset_url()
     */
    public function test_asset_url()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertSame('http://test', $output->asset_url('http://test'));
        self::assertSame('/ui.js', $output->asset_url('/ui.js'));
        self::assertSame('skins/elastic/ui.js', $output->asset_url('/ui.js', true));
    }

    /**
     * Test button()
     */
    public function test_button()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertSame('', $output->button([]));

        // TODO: Test more cases
        self::markTestIncomplete();
    }

    /**
     * Test form_tag()
     */
    public function test_form_tag()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertSame('<form action="vendor/bin/phpunit?_task=cli" method="get">test</form>', $output->form_tag([], 'test'));
    }

    /**
     * Test request_form()
     */
    public function test_request_form()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        self::assertSame('<form action="./" method="get">test</form>', $output->request_form([], 'test'));
    }

    /**
     * Test search_form()
     */
    public function test_search_form()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        $expected = '<form name="rcmqsearchform" onsubmit="rcmail.command(\'search\'); return false"'
            . ' action="vendor/bin/phpunit?_task=cli" method="get"><label for="rcmqsearchbox" class="voice">Search terms</label>'
            . '<input name="_q" class="no-bs" id="rcmqsearchbox" placeholder="Search..." type="text"></form>';

        self::assertSame($expected, $output->search_form([]));
    }

    /**
     * Test charset_selector()
     */
    public function test_charset_selector()
    {
        $rcmail = rcube::get_instance();
        $output = new rcmail_output_html();

        $result = $output->charset_selector([]);

        self::assertTrue(strpos($result, '<select name="_charset">') === 0);
        self::assertTrue(strpos($result, '<option value="UTF-8" selected="selected">UTF-8 (Unicode)</option>') !== false);
    }
}
