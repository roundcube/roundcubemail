<?php

/**
 * Test class to test rcube_user class
 */
class Framework_User extends ActionTestCase
{
    /**
     * Test class constructor
     */
    public function test_constructor()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        self::assertSame(1, $user->ID);
        self::assertNull($user->language);
    }

    /**
     * Test get_username()
     */
    public function test_get_username()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        self::assertSame('test@example.com', $user->get_username());
        self::assertSame('test', $user->get_username('local'));
        self::assertSame('example.com', $user->get_username('domain'));
    }

    /**
     * Test save_prefs() and get_prefs() and get_hash()
     */
    public function test_save_prefs()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        self::assertSame([], $user->get_prefs());

        $user->save_prefs(['test' => 'test'], true);

        $user = new rcube_user(1);

        self::assertSame(['test' => 'test'], $user->get_prefs());

        $hash = $user->get_hash();

        self::assertMatchesRegularExpression('/^[a-zA-Z0-9]{16}$/', $hash);

        $user = new rcube_user(1);

        $prefs = $user->get_prefs();

        self::assertSame('test', $prefs['test']);
        self::assertSame($hash, $prefs['client_hash']);
        self::assertSame($hash, $user->get_hash());
    }

    /**
     * Test identities related methods
     */
    public function test_list_emails_and_identities()
    {
        self::initDB('init');
        self::initDB('identities');

        $user = new rcube_user(1);

        $all = $user->list_emails();

        self::assertCount(2, $all);
        self::assertSame('test@example.com', $all[0]['email']);
        self::assertSame('test@example.org', $all[1]['email']);

        $ident = $user->list_emails(true);

        self::assertSame('test@example.com', $ident['email']);

        $ident = $user->get_identity();

        self::assertSame('test@example.com', $ident['email']);

        $idents = $user->list_identities('', true);

        self::assertCount(2, $idents);
        self::assertSame('test@example.com', $idents[0]['email_ascii']);
        self::assertSame('test <test@example.com>', $idents[0]['ident']);
        self::assertSame('test@example.org', $idents[1]['email_ascii']);
        self::assertSame('test <test@example.org>', $idents[1]['ident']);

        $default = $idents[0]['identity_id'];

        $res = $user->update_identity($idents[1]['identity_id'], ['name' => 'test-new']);

        self::assertTrue($res);

        $ident = $user->get_identity($idents[1]['identity_id']);

        self::assertSame('test-new', $ident['name']);

        $id = $user->insert_identity([
            'name' => 'name',
            'email' => 'add@ident.com',
        ]);

        self::assertTrue(is_numeric($id));

        $ident = $user->get_identity($id);

        self::assertSame('name', $ident['name']);
        self::assertSame('add@ident.com', $ident['email']);

        $idents = $user->list_identities();

        self::assertCount(3, $idents);

        $ident = $user->set_default($id);

        $idents = $user->list_identities();

        self::assertCount(3, $idents);
        self::assertSame('add@ident.com', $idents[0]['email']);
        $this->{'assertEquals'}(1, $idents[0]['standard']);
        self::assertSame('test@example.com', $idents[1]['email']);
        $this->{'assertEquals'}(0, $idents[1]['standard']);
        self::assertSame('test@example.org', $idents[2]['email']);
        $this->{'assertEquals'}(0, $idents[2]['standard']);

        $ident = $user->delete_identity($default);

        $idents = $user->list_identities();

        self::assertCount(2, $idents);
    }

    /**
     * Test failed_login() and is_locked()
     */
    public function test_failed_login()
    {
        self::initDB('init');

        $user = new rcube_user(1);

        $user->failed_login();

        $user = new rcube_user(1);

        $this->{'assertEquals'}(1, $user->data['failed_login_counter']);

        self::assertFalse($user->is_locked());
    }

    /**
     * Test query()
     */
    public function test_query()
    {
        self::initDB('init');

        self::assertNull(rcube_user::query('test', 'localhost'));

        $user = rcube_user::query('test@example.com', 'localhost');

        self::assertSame(1, $user->ID);
    }

    /**
     * Test create()
     */
    public function test_create()
    {
        self::initDB('init');

        $user = rcube_user::create('new@example.com', 'localhost');

        self::assertSame('new@example.com', $user->get_username());

        $user = new rcube_user($user->ID);

        $idents = $user->list_identities();

        self::assertCount(1, $idents);
        self::assertSame('new@example.com', $idents[0]['email']);
        $this->{'assertEquals'}(1, $idents[0]['standard']);
    }

    /**
     * Test saved searches
     */
    public function test_saved_searches()
    {
        self::markTestIncomplete();
    }
}
