<?php

/**
 * Test class to test rcmail_action_contacts_list
 *
 * @package Tests
 */
class Actions_Contacts_List extends ActionTestCase
{
    /**
     * Test listing contacts
     */
    function test_list()
    {
        $action = new rcmail_action_contacts_list;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'list');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('list', $result['action']);
        $this->assertSame(1, $result['env']['pagecount']);

        $commands = explode("\n", trim($result['exec']));

        $this->assertCount(8, $commands);
        $this->assertSame('this.set_group_prop(null);', $commands[0]);
        $this->assertSame('this.set_rowcount("Contacts 1 to 6 of 6");', $commands[1]);
        $this->assertStringMatchesFormat(
            'this.add_contact_row("%i",{"name":"George Bush"},"person",'
            . '{"name":"George Bush","email":"g.bush@gov.com","ID":"%i"});',
            $commands[2]
        );
    }
}
