<?php

/**
 * Test class to test rcube_user class
 *
 * @package Tests
 */
class Framework_User extends ActionTestCase
{
    /**
     * Test class constructor
     */
    function test_constructor()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        $this->assertSame(1, $user->ID);
        $this->assertSame(null, $user->language);
    }

    /**
     * Test get_username()
     */
    function test_get_username()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        $this->assertSame('test@example.com', $user->get_username());
        $this->assertSame('test', $user->get_username('local'));
        $this->assertSame('example.com', $user->get_username('domain'));
    }

    /**
     * Test save_prefs() and get_prefs() and get_hash()
     */
    function test_save_prefs()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        $this->assertSame([], $user->get_prefs());

        $user->save_prefs(['test' => 'test'], true);

        $user = new rcube_user(1);

        $this->assertSame(['test' => 'test'], $user->get_prefs());

        $hash = $user->get_hash();

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{16}$/', $hash);

        $user = new rcube_user(1);

        $prefs = $user->get_prefs();

        $this->assertSame('test', $prefs['test']);
        $this->assertSame($hash, $prefs['client_hash']);
        $this->assertSame($hash, $user->get_hash());
    }

    /**
     * Test identities related methods
     */
    function test_list_emails_and_identities()
    {
        self::initDB('init');
        self::initDB('identities');

        $user = new rcube_user(1);

        $all = $user->list_emails();

        $this->assertCount(2, $all);
        $this->assertSame('test@example.com', $all[0]['email']);
        $this->assertSame('test@example.org', $all[1]['email']);

        $ident = $user->list_emails(true);

        $this->assertSame('test@example.com', $ident['email']);

        $ident = $user->get_identity();

        $this->assertSame('test@example.com', $ident['email']);

        $idents = $user->list_identities('', true);

        $this->assertCount(2, $idents);
        $this->assertSame('test@example.com', $idents[0]['email_ascii']);
        $this->assertSame('test <test@example.com>', $idents[0]['ident']);
        $this->assertSame('test@example.org', $idents[1]['email_ascii']);
        $this->assertSame('test <test@example.org>', $idents[1]['ident']);

        $default = $idents[0]['identity_id'];

        $res = $user->update_identity($idents[1]['identity_id'], ['name' => 'test-new']);

        $this->assertTrue($res);

        $ident = $user->get_identity($idents[1]['identity_id']);

        $this->assertSame('test-new', $ident['name']);

        $id = $user->insert_identity([
                'name' => 'name',
                'email' => 'add@ident.com',
        ]);

        $this->assertTrue(is_numeric($id));

        $ident = $user->get_identity($id);

        $this->assertSame('name', $ident['name']);
        $this->assertSame('add@ident.com', $ident['email']);

        $idents = $user->list_identities();

        $this->assertCount(3, $idents);

        $ident = $user->set_default($id);

        $idents = $user->list_identities();

        $this->assertCount(3, $idents);
        $this->assertSame('add@ident.com', $idents[0]['email']);
        $this->assertEquals(1, $idents[0]['standard']);
        $this->assertSame('test@example.com', $idents[1]['email']);
        $this->assertEquals(0, $idents[1]['standard']);
        $this->assertSame('test@example.org', $idents[2]['email']);
        $this->assertEquals(0, $idents[2]['standard']);

        $ident = $user->delete_identity($default);

        $idents = $user->list_identities();

        $this->assertCount(2, $idents);
    }

    /**
     * Test failed_login() and is_locked()
     */
    function test_failed_login()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        $user->failed_login();

        $user = new rcube_user(1);

        $this->assertEquals(1, $user->data['failed_login_counter']);

        $this->assertFalse($user->is_locked());
    }

    /**
     * Test query()
     */
    function test_query()
    {
        self::initDB('init');

        $this->assertNull(rcube_user::query('test', 'localhost'));

        $user = rcube_user::query('test@example.com', 'localhost');

        $this->assertEquals(1, $user->ID);
    }

    /**
     * Test create()
     */
    function test_create()
    {
        self::initDB('init');

        $user = rcube_user::create('new@example.com', 'localhost');

        $this->assertSame('new@example.com', $user->get_username());

        $user = new rcube_user($user->ID);

        $idents = $user->list_identities();

        $this->assertCount(1, $idents);
        $this->assertSame('new@example.com', $idents[0]['email']);
        $this->assertEquals(1, $idents[0]['standard']);
    }

    /**
     * Test saved searches
     */
    function test_saved_searches()
    {
        $this->markTestIncomplete();
    }
}
