<?php

namespace Tests\Browser\Settings;

class Identities extends \Tests\Browser\DuskTestCase
{
    public function testIdentities()
    {
        $this->browse(function ($browser) {
            $this->go('settings', 'identities');

            // check task and action
            $this->assertEnvEquals('task', 'settings');
            $this->assertEnvEquals('action', 'identities');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('identitieslist', $objects);

            if ($this->isDesktop()) {
                $browser->assertVisible('#settings-menu li.identities.selected');
            }

            // Identities list
            $browser->assertVisible('#identities-table tr:first-child.focused');
            $browser->assertSeeIn('#identities-table tr:first-child td.mail', TESTS_USER);

            // Toolbar menu
            $this->assertToolbarMenu(['create'], ['delete']);
        });
    }
}
