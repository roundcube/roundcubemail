<?php

namespace Tests\Browser\Mail;

class Compose extends \Tests\Browser\DuskTestCase
{
    public function testCompose()
    {
        $this->browse(function ($browser) {
            $this->go('mail', 'compose');

            // check task and action
            $this->assertEnvEquals('task', 'mail');
            $this->assertEnvEquals('action', 'compose');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('qsearchbox', $objects);
            $this->assertContains('addressbookslist', $objects);
            $this->assertContains('contactslist', $objects);
            $this->assertContains('messageform', $objects);
            $this->assertContains('attachmentlist', $objects);
            $this->assertContains('filedrop', $objects);
            $this->assertContains('uploadform', $objects);
        });
    }
}
