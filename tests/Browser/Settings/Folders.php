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

            $browser->assertVisible('#settings-menu li.folders.selected');

            // Folders list
            $browser->assertVisible('#subscription-table li.mailbox.inbox');

            // Toolbar menu
            $browser->assertVisible('#toolbar-menu a.create:not(.disabled)');
            $browser->assertVisible('#toolbar-menu a.delete.disabled');
            $browser->assertVisible('#toolbar-menu a.purge.disabled');
        });
    }
}
