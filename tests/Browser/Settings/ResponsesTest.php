<?php

namespace Tests\Browser\Settings;

use Tests\Browser\Components\App;

class ResponsesTest extends \Tests\Browser\TestCase
{
    public function testIdentities()
    {
        $this->browse(function ($browser) {
            $browser->go('settings', 'responses');

            $browser->with(new App(), function ($browser) {
                // check task and action
                $browser->assertEnv('task', 'settings');
                $browser->assertEnv('action', 'responses');

                // these objects should be there always
                $browser->assertObjects(['responseslist']);
            });

            if ($browser->isDesktop()) {
                $browser->assertVisible('#settings-menu li.responses.selected');
            }

            // Responses list
            $browser->assertPresent('#responses-table');
            $browser->assertMissing('#responses-table tr');

            // Toolbar menu
            $browser->assertToolbarMenu(['create'], ['delete']);
        });
    }
}
