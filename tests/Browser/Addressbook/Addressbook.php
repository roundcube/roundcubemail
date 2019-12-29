<?php

namespace Tests\Browser\Addressbook;

class Addressbook extends \Tests\Browser\DuskTestCase
{
    public function testAddressbook()
    {
        $this->browse(function ($browser) {
            $this->go('addressbook');

            // check task
            $this->assertEnvEquals('task', 'addressbook');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('qsearchbox', $objects);
            $this->assertContains('folderlist', $objects);
            $this->assertContains('contactslist', $objects);
            $this->assertContains('countdisplay', $objects);
        });
    }
}
