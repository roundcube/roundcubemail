<?php

namespace Tests\Browser\Mail;

class Mail extends \Tests\Browser\DuskTestCase
{
    public function testMail()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            // check task
            $this->assertEnvEquals('task', 'mail');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('qsearchbox', $objects);
            $this->assertContains('mailboxlist', $objects);
            $this->assertContains('messagelist', $objects);
            $this->assertContains('quotadisplay', $objects);
            $this->assertContains('search_filter', $objects);
            $this->assertContains('countdisplay', $objects);
        });
    }
}
