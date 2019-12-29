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
        });
    }
}
