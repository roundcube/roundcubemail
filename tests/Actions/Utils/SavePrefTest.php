<?php

namespace Roundcube\Tests\Actions\Utils;

use rcmail as rcmail;
use rcmail_action as rcmail_action;
use rcmail_action_utils_save_pref as rcmail_action_utils_save_pref;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputHtmlMock;

/**
 * Test class to test rcmail_action_utils_save_pref
 */
class SavePrefTest extends ActionTestCase
{
    /**
     * Test for run()
     */
    public function test_run()
    {
        $action = new \rcmail_action_utils_save_pref();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'utils', 'save_pref');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        $rcmail = \rcmail::get_instance();
        $rcmail->user->save_prefs(['list_cols' => []]);

        $_POST = [
            '_name' => 'list_cols',
            '_value' => ['date'],
        ];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $user = new \rcube_user($rcmail->user->ID);
        $prefs = $user->get_prefs();

        $this->assertSame(['date'], $prefs['list_cols']);

        // TODO: Test writing to session, test whitelist
    }
}
