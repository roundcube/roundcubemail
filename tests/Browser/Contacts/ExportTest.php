<?php

namespace Tests\Browser\Contacts;

class ExportTest extends \Tests\Browser\TestCase
{
    public static function setUpBeforeClass(): void
    {
        \bootstrap::init_db();
    }

    /**
     * Test exporting all contacts
     */
    public function testExportAll()
    {
        $this->browse(function ($browser) {
            $browser->go('addressbook');

            $browser->clickToolbarMenuItem('export');

            // Parse the downloaded vCard file
            $vcard_content = $browser->readDownloadedFile('contacts.vcf');
            $vcard = new \rcube_vcard();
            $contacts = $vcard->import($vcard_content);

            $this->assertCount(2, $contacts);
            $this->assertSame('John Doe', $contacts[0]->displayname);
            $this->assertSame('Jane Stalone', $contacts[1]->displayname);

            $browser->removeDownloadedFile('contacts.vcf');
        });
    }

    /**
     * Test exporting selected contacts
     *
     * @depends testExportAll
     */
    public function testExportSelected()
    {
        $this->browse(function ($browser) {
            $browser->ctrlClick('#contacts-table tbody tr:first-child');

            $browser->clickToolbarMenuItem('export', 'export.select');

            $vcard_content = $browser->readDownloadedFile('contacts.vcf');
            $vcard = new \rcube_vcard();
            $contacts = $vcard->import($vcard_content);

            // Parse the downloaded vCard file
            $this->assertCount(1, $contacts);
            $this->assertSame('John Doe', $contacts[0]->displayname);

            $browser->removeDownloadedFile('contacts.vcf');
        });
    }
}
