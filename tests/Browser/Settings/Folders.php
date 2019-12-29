<?php

namespace Tests\Browser\Settings;

class Folders extends \Tests\Browser\DuskTestCase
{
    public function testFolders()
    {
        $this->browse(function ($browser) {
            $this->go('settings', 'folders');

            // task should be set to 'settings' and action to 'folders'
            $this->assertEnvEquals('task', 'settings');
            $this->assertEnvEquals('action', 'folders');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('quotadisplay', $objects);
            $this->assertContains('subscriptionlist', $objects);
        });
    }
}
