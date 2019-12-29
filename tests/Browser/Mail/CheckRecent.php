<?php

namespace Tests\Browser\Mail;

class CheckRecent extends \Tests\Browser\DuskTestCase
{
    public function testCheckRecent()
    {
        $this->browse(function ($browser) {
            $this->go('mail');

            // TODO
        });
    }
}
