<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class Folders extends \Tests\Browser\TestCase
{
    public function testFolders()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'folders');

            // task should be set to 'settings' and action to 'folders'
            $browser->with(new App(), function ($browser) {
                $browser->assertEnv('task', 'settings');
                $browser->assertEnv('action', 'folders');

                // these objects should be there always
                $browser->assertObjects(['quotadisplay', 'subscriptionlist']);
            });

            if ($browser->isDesktop()) {
                $browser->assertVisible('#settings-menu li.folders.selected');
            }

            // Folders list
            $browser->assertVisible('#subscription-table li.mailbox.inbox');

            // Toolbar menu
            $browser->assertToolbarMenu(['create'], ['delete', 'purge']);
        });
    }
}
