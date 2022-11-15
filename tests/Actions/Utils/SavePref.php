<?php

/**
 * Test class to test rcmail_action_utils_save_pref
 *
 * @package Tests
 */
class Actions_Utils_SavePref extends ActionTestCase
{
    /**
     * Test for run()
     */
    function test_run()
    {
        $action = new rcmail_action_utils_save_pref;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'utils', 'save_pref');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $rcmail = rcmail::get_instance();
        $rcmail->user->save_prefs(['list_cols' => []]);

        $_POST = [
            '_name' => 'list_cols',
            '_value' => ['date']
        ];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $user  = new rcube_user($rcmail->user->ID);
        $prefs = $user->get_prefs();

        $this->assertSame(['date'], $prefs['list_cols']);

        // TODO: Test writing to session, test whitelist
    }
}
