<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class Identities extends \Tests\Browser\TestCase
{
    public function testIdentities()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'identities');

            // check task and action
            $browser->with(new App(), function ($browser) {
                $browser->assertEnv('task', 'settings');
                $browser->assertEnv('action', 'identities');

                // these objects should be there always
                $browser->assertObjects(['identitieslist']);
            });

            if ($browser->isDesktop()) {
                $browser->assertVisible('#settings-menu li.identities.selected');
            }

            // Identities list
            $browser->assertVisible('#identities-table tr:first-child.focused');
            $browser->assertSeeIn('#identities-table tr:first-child td.mail', TESTS_USER);

            // Toolbar menu
            $browser->assertToolbarMenu(['create'], ['delete']);
        });
    }
}
