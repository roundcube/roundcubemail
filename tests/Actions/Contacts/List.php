<?php

/**
 * Test class to test rcmail_action_contacts_list
 */
class Actions_Contacts_List extends ActionTestCase
{
    /**
     * Test listing contacts
     */
    public function test_list()
    {
        $action = new rcmail_action_contacts_list();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'contacts', 'list');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('contacts');

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('list', $result['action']);
        self::assertSame(1, $result['env']['pagecount']);

        $commands = explode("\n", trim($result['exec']));

        self::assertCount(8, $commands);
        self::assertSame('this.set_group_prop(null);', $commands[0]);
        self::assertSame('this.set_rowcount("Contacts 1 to 6 of 6");', $commands[1]);
        self::assertStringMatchesFormat(
            'this.add_contact_row("%i",{"name":"George Bush"},"person",'
            . '{"name":"George Bush","email":"g.bush@gov.com","ID":"%i"});',
            $commands[2]
        );
    }
}
