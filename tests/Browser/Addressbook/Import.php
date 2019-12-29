<?php

namespace Tests\Browser\Addressbook;

class Import extends \Tests\Browser\DuskTestCase
{
    public function testImport()
    {
        $this->browse(function ($browser) {
            $this->go('addressbook', 'import');

            // check task and action
            $this->assertEnvEquals('task', 'addressbook');
            $this->assertEnvEquals('action', 'import');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('importform', $objects);
        });
    }

    public function testImport2()
    {
        $this->browse(function ($browser) {
            $this->go('addressbook', 'import');

            $objects = $this->getObjects();

            // these objects should be there always
            $this->assertContains('importform', $objects);
        });
    }
}
