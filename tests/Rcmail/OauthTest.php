<?php

namespace Roundcube\Tests\Rcmail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\ExitException;
use Roundcube\Tests\HttpClientMock;
use Roundcube\Tests\OutputHtmlMock;
use Roundcube\Tests\StderrMock;

use function Roundcube\Tests\getProperty;
use function Roundcube\Tests\setProperty;

/**
 * Test class to test rcmail_oauth class
 */
class OauthTest extends ActionTestCase
{
    // created a valid and enabled oauth instance
    private $config = [
        'provider' => 'test',
        'token_uri' => 'https://test/token',
        'auth_uri' => 'https://test/auth',
        'identity_uri' => 'https://test/ident',
        'issuer' => 'https://test/',
        // Do not set JWKS
        'client_id' => 'some-client',
        'client_secret' => 'very-secure',
        'scope' => 'plop',
    ];

    private $identity = [
        'sub' => '82c8f487-df95-4960-972c-4e680c3c72f5',
        'name' => 'John Doe',
        'preferred_username' => 'John D',
        'given_name' => 'John',
        'family_name' => 'Doe',
        'email' => 'j.doe@test.fake',
        'email_verified' => true,
        'locale' => 'en',
    ];

    private function generate_fake_id_token()
    {
        $id_token_payload = (array) [
            'typ' => 'ID', // this is a token id
            'exp' => (time() + 600),
            'iat' => time(),
            'auth_time' => time(),
            'jti' => 'uniq-id',
            'iss' => $this->config['issuer'],
            'aud' => $this->config['client_id'],
            'azp' => $this->config['client_id'],
            'session_state' => 'fake-session',
            'acr' => '1',
            'nonce' => 'fake-nonce',
            'sid' => '65f8d42c-dbbd-4f76-b5f3-44b540e4253a',
        ] + $this->identity;

        // Right now our code does not check signature
        $jwt_header = strtr(base64_encode(json_encode(['alg' => 'NONE', 'typ' => 'JWT'])), '+/', '-_');
        $jwt_body = strtr(base64_encode(json_encode($id_token_payload)), '+/', '-_');
        $jwt_signature = ''; // NONE alg

        return implode('.', [$jwt_header, $jwt_body, $jwt_signature]);
    }

    /**
     * Test jwt_decode() method with an invalid token
     */
    public function test_jwt_decode_invalid()
    {
        $jwt = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.EkN-DOsnsuRjRO6BxXemmJDm3HbxrbRzXglbN2S4sOkopdU4IsDxTI8jO19W_A4K8ZPJijNLis4EZsHeY559a4DFOd50_OqgHGuERTqYZyuhtF39yxJPAjUESwxk2J5k_4zM3O-vtd1Ghyo4IbqKKSy6J9mTniYJPenn5-HIirE';

        $oauth = \rcmail_oauth::get_instance();

        // We can't use expectException until we drop support for phpunit 4.8 (i.e. PHP 5.4)
        // $this->expectException(RuntimeException::class);

        try {
            $oauth->jwt_decode($jwt);
        } catch (\RuntimeException $e) {
        }

        $this->assertTrue(isset($e));
    }

    /**
     * Test jwt_decode() method with an array aud
     */
    public function test_jwt_decode_array()
    {
        $jwt = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImF1ZCI6WyJzb21lLWNsaWVudCJdfQ.signature';

        $oauth = new \rcmail_oauth([
            'client_id' => 'some-client',
        ]);
        $body = $oauth->jwt_decode($jwt);
        $this->assertSame($body['aud'], ['some-client']);
    }

    /**
     * Test jwt_decode() method with a string aud
     */
    public function test_jwt_decode_string()
    {
        $jwt = 'eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImF1ZCI6InNvbWUtY2xpZW50In0.signature';

        $oauth = new \rcmail_oauth([
            'client_id' => 'some-client',
        ]);
        $body = $oauth->jwt_decode($jwt);
        $this->assertSame($body['aud'], 'some-client');
    }

    /**
     * Test is_enabled() method
     */
    public function test_is_enabled()
    {
        $oauth = \rcmail_oauth::get_instance();

        $this->assertFalse($oauth->is_enabled());
    }

    /**
     * Test is_enabled() method
     */
    public function test_is_enabled_with_token_url()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        $this->assertTrue($oauth->is_enabled());
    }

    /**
     * Test discovery method
     */
    public function test_discovery()
    {
        // fake discovery response
        $config_answer = [
            'issuer' => 'https://test/issuer',
            'authorization_endpoint' => 'https://test/auth',
            'token_endpoint' => 'https://test/token',
            'userinfo_endpoint' => 'https://test/userinfo',
            'end_session_endpoint' => 'https://test/logout',
            'jwks_uri' => 'https://test/jwks',
        ];

        HttpClientMock::setResponses([
            [200, ['Content-Type' => 'application/json'], json_encode($config_answer)],
        ]);

        // provide only the config
        $oauth = new \rcmail_oauth([
            'provider' => 'example',
            'config_uri' => 'https://test/config',
            'client_id' => 'some-client',
        ]);
        $oauth->init();

        // if discovery succeed, should be enabled
        $this->assertTrue($oauth->is_enabled());
    }

    /**
     * Test get_redirect_uri() method
     */
    public function test_get_redirect_uri()
    {
        $oauth = \rcmail_oauth::get_instance();

        $this->assertMatchesRegularExpression('|^http://.*/index.php/login/oauth$|', $oauth->get_redirect_uri());
    }

    /**
     * Test login_redirect() method
     */
    public function test_login_redirect()
    {
        $output = $this->initOutput(\rcmail_action::MODE_HTTP, 'login', '');

        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        try {
            $oauth->login_redirect();
            $result = null;
            $ecode = null;
        } catch (ExitException $e) {
            $result = $e->getMessage();
            $ecode = $e->getCode();
        }

        $this->assertSame(OutputHtmlMock::E_REDIRECT, $ecode);
        $this->assertMatchesRegularExpression('|^Location: https://test/auth\?.*|', $result);

        [$base, $query] = explode('?', substr($result, 10));
        parse_str($query, $map);

        $this->assertSame($this->config['scope'], $map['scope']);
        $this->assertSame($this->config['client_id'], $map['client_id']);
        $this->assertSame('code', $map['response_type']);
        $this->assertSame($_SESSION['oauth_state'], $map['state']);
        $this->assertSame($_SESSION['oauth_nonce'], $map['nonce']);
        $this->assertMatchesRegularExpression('!http.*/login/oauth!', $map['redirect_uri']);
    }

    /**
     * Test request_access_token() method with a wrong state
     */
    public function test_request_access_token_with_wrong_state()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        $_SESSION['oauth_state'] = 'random-state';

        StderrMock::start();
        $response = $oauth->request_access_token('fake-code', 'mismatch-state');
        StderrMock::stop();

        // should be false as state do not match
        $this->assertFalse($response);

        $this->assertSame('ERROR: OAuth token request failed: state parameter mismatch', trim(StderrMock::$output));
    }

    /**
     * Test request_access_token()
     */
    public function test_request_access_token_with_wrong_nonce()
    {
        $payload = [
            'token_type' => 'Bearer',
            'access_token' => 'FAKE-ACCESS-TOKEN',
            'expires_in' => 300,
            'refresh_token' => 'FAKE-REFRESH-TOKEN',
            'refresh_expires_in' => 1800,
            'id_token' => $this->generate_fake_id_token(), // inject a generated identity
            'not-before-policy' => 0,
            'session_state' => 'fake-session',
            'scope' => 'openid profile email',
        ];

        HttpClientMock::setResponses([
            [200, ['Content-Type' => 'application/json'], json_encode($payload)],
        ]);

        $oauth = new \rcmail_oauth((array) $this->config);
        $oauth->init();

        $_SESSION['oauth_state'] = 'random-state'; // ensure state identiquals
        $_SESSION['oauth_nonce'] = 'wrong-nonce';

        StderrMock::start();
        $response = $oauth->request_access_token('fake-code', 'random-state');
        StderrMock::stop();

        $this->assertFalse($response);
        $this->assertStringContainsString('identity\'s nonce mismatch', StderrMock::$output);
    }

    /**
     * Test request_access_token() method
     */
    public function test_request_access_token()
    {
        $payload = [
            'token_type' => 'Bearer',
            'access_token' => 'FAKE-ACCESS-TOKEN',
            'expires_in' => 300,
            'refresh_token' => 'FAKE-REFRESH-TOKEN',
            'refresh_expires_in' => 1800,
            'id_token' => $this->generate_fake_id_token(), // inject a generated identity
            'not-before-policy' => 0,
            'session_state' => 'fake-session',
            'scope' => 'openid profile email',
        ];

        HttpClientMock::setResponses([
            [200, ['Content-Type' => 'application/json'], json_encode($payload)],
        ]);

        $oauth = new \rcmail_oauth((array) $this->config);
        $oauth->init();

        $_SESSION['oauth_state'] = 'random-state'; // ensure state identiquals
        $_SESSION['oauth_nonce'] = 'fake-nonce';
        $response = $oauth->request_access_token('fake-code', 'random-state');

        $this->assertTrue($response);

        $login_phase = getProperty($oauth, 'login_phase');

        $this->assertSame('Bearer FAKE-ACCESS-TOKEN', $login_phase['authorization']);
        $this->assertSame($this->identity['email'], $login_phase['username']);
        $this->assertTrue(isset($login_phase['token']));
        $this->assertFalse(isset($login_phase['token']['access_token']));
    }

    /**
     * Test request_access_token() method without identity, code will have to fetch the identity using the access token
     */
    public function test_request_access_token_without_id_token()
    {
        $payload = [
            'token_type' => 'Bearer',
            'access_token' => 'FAKE-ACCESS-TOKEN',
            'expires_in' => 300,
            'refresh_token' => 'FAKE-REFRESH-TOKEN',
            'refresh_expires_in' => 1800,
            'not-before-policy' => 0,
            'session_state' => 'fake-session',
            'scope' => 'openid profile email',
        ];

        // TODO should create a specific Mock to check request and validate it
        HttpClientMock::setResponses([
            [200, ['Content-Type' => 'application/json'], json_encode($payload)],        // the request access
            [200, ['Content-Type' => 'application/json'], json_encode($this->identity)], // call to userinfo
        ]);

        $oauth = new \rcmail_oauth((array) $this->config);
        $oauth->init();

        $_SESSION['oauth_state'] = 'random-state'; // ensure state identiquals
        $_SESSION['oauth_nonce'] = 'fake-nonce'; // ensure nonce identiquals
        $response = $oauth->request_access_token('fake-code', 'random-state');

        $this->assertTrue($response);
        $login_phase = getProperty($oauth, 'login_phase');

        $this->assertSame('Bearer FAKE-ACCESS-TOKEN', $login_phase['authorization']);
        $this->assertSame($this->identity['email'], $login_phase['username']);
        $this->assertTrue(isset($login_phase['token']));
        $this->assertFalse(isset($login_phase['token']['access_token']));
    }

    /**
     * Test user_create() method
     */
    public function test_valid_user_create()
    {
        $oauth = new \rcmail_oauth();
        $oauth->init();

        // fake identity
        setProperty($oauth, 'login_phase', [
            'token' => [
                'identity' => [
                    'email' => 'jdoe@faké.dômain',
                    'name' => 'John Doe',
                    'locale' => 'en-US',
                ],
            ],
        ]);
        $answer = $oauth->user_create([]);

        $this->assertSame($answer, [
            'user_name' => 'John Doe',
            'user_email' => 'jdoe@xn--fak-dma.xn--dmain-6ta',
            'language' => 'en_US',
        ]);
    }

    /**
     * Test invalid properties in user_create
     */
    public function test_invalid_user_create()
    {
        $oauth = new \rcmail_oauth();
        $oauth->init();

        // fake identity
        setProperty($oauth, 'login_phase', [
            'token' => [
                'identity' => [
                    'email' => 'bad-domain',
                    'name' => 'John Doe',
                    'locale' => '/martian',
                ],
            ],
        ]);

        StderrMock::start();
        $answer = $oauth->user_create([]);
        StderrMock::stop();

        // only user_name can be defined
        $this->assertSame($answer, ['user_name' => 'John Doe']);
        $this->assertStringContainsString('ignoring invalid email', StderrMock::$output);
        $this->assertStringContainsString('ignoring language', StderrMock::$output);
    }

    /**
     * Test refresh_access_token() method
     */
    public function test_refresh_access_token()
    {
        // FIXME
        $this->markTestIncomplete();
    }

    /**
     * Test unauthenticated() hook stores original query string
     */
    public function test_unauthenticated_stores_request_uri()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // Simulate a request to compose window
        $_SERVER['QUERY_STRING'] = '_task=mail&_action=compose&_extwin=1';

        // Call the unauthenticated hook
        $oauth->unauthenticated([]);

        // Verify the query string was stored in session
        $this->assertSame('_task=mail&_action=compose&_extwin=1', $_SESSION['oauth_redirect_uri']);
    }

    /**
     * Test unauthenticated() hook with array parameters
     */
    public function test_unauthenticated_stores_request_uri_with_array_params()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // Simulate a request with array parameters
        $_SERVER['QUERY_STRING'] = '_task=mail&_action=compose&_extwin=1&_files[]=file1.txt&_files[]=file2.txt';

        // Call the unauthenticated hook
        $oauth->unauthenticated([]);

        // Verify the query string with array params was stored
        $this->assertSame(
            '_task=mail&_action=compose&_extwin=1&_files[]=file1.txt&_files[]=file2.txt',
            $_SESSION['oauth_redirect_uri']
        );
    }

    /**
     * Test request_access_token() preserves redirect URI from session
     */
    public function test_request_access_token_preserves_redirect_uri()
    {
        $payload = [
            'token_type' => 'Bearer',
            'access_token' => 'FAKE-ACCESS-TOKEN',
            'expires_in' => 300,
            'refresh_token' => 'FAKE-REFRESH-TOKEN',
            'refresh_expires_in' => 1800,
            'id_token' => $this->generate_fake_id_token(),
            'not-before-policy' => 0,
            'session_state' => 'fake-session',
            'scope' => 'openid profile email',
        ];

        HttpClientMock::setResponses([
            [200, ['Content-Type' => 'application/json'], json_encode($payload)],
        ]);

        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        $_SESSION['oauth_state'] = 'random-state';
        $_SESSION['oauth_nonce'] = 'fake-nonce';
        $_SESSION['oauth_redirect_uri'] = '_task=mail&_action=compose&_extwin=1';

        $response = $oauth->request_access_token('fake-code', 'random-state');

        $this->assertTrue($response);

        // Verify redirect_uri was preserved in login_phase
        $login_phase = getProperty($oauth, 'login_phase');
        $this->assertSame('_task=mail&_action=compose&_extwin=1', $login_phase['redirect_uri']);
    }

    /**
     * Test authenticate() hook sets $_POST['_url'] for redirect
     */
    public function test_authenticate_sets_post_url()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // Simulate preserved redirect URI in login_phase
        setProperty($oauth, 'login_phase', [
            'redirect_uri' => '_task=mail&_action=compose&_extwin=1',
            'username' => 'test@example.com',
            'authorization' => 'Bearer token',
        ]);

        // Initialize $_POST with known shape (value will be overwritten by authenticate)
        $_POST = ['_url' => null];

        $result = $oauth->authenticate([]);

        // Verify $_POST['_url'] was set
        $this->assertSame('_task=mail&_action=compose&_extwin=1', $_POST['_url']);
    }

    /**
     * Test authenticate() hook with array parameters in URL
     */
    public function test_authenticate_sets_post_url_with_array_params()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // Simulate preserved redirect URI with array parameters
        setProperty($oauth, 'login_phase', [
            'redirect_uri' => '_task=mail&_action=compose&_extwin=1&_files[]=file1.txt&_files[]=file2.txt',
            'username' => 'test@example.com',
            'authorization' => 'Bearer token',
        ]);

        // Initialize $_POST with known shape (value will be overwritten by authenticate)
        $_POST = ['_url' => null];

        $result = $oauth->authenticate([]);

        // Verify $_POST['_url'] was set with array params
        $this->assertSame('_task=mail&_action=compose&_extwin=1&_files[]=file1.txt&_files[]=file2.txt', $_POST['_url']);
    }

    /**
     * Test login_after() hook without redirect handling (now done in authenticate)
     */
    public function test_login_after_returns_input_unchanged()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // Simulate login_phase with required fields
        setProperty($oauth, 'login_phase', [
            'token' => ['identity' => ['sub' => 'test-sub']],
            'nonce' => 'test-nonce',
            'username' => 'test@example.com',
        ]);

        $result = $oauth->login_after(['_task' => 'mail']);

        // login_after should return input unchanged (redirect handled via $_POST['_url'] in authenticate)
        $this->assertSame('mail', $result['_task']);
    }

    /**
     * Test login_after() hook without stored redirect URI
     */
    public function test_login_after_without_redirect_uri()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // No login_phase means hook returns input unchanged
        setProperty($oauth, 'login_phase', null);

        $result = $oauth->login_after(['_task' => 'mail']);

        // Should return the input unchanged
        $this->assertSame(['_task' => 'mail'], $result);
    }

    /**
     * Integration test: Verify no redirect loop when accessing login page
     */
    public function test_oauth_redirect_flow_prevents_login_loop()
    {
        $oauth = new \rcmail_oauth($this->config);
        $oauth->init();

        // Clear any session variable from previous tests
        unset($_SESSION['oauth_redirect_uri']);

        // Step 1: User somehow ends up with a login URL stored (shouldn't happen, but test it)
        $_SERVER['QUERY_STRING'] = '_task=login&_action=oauth';

        // Step 2: unauthenticated hook should NOT store login URLs
        $oauth->unauthenticated([]);
        $this->assertArrayNotHasKey('oauth_redirect_uri', $_SESSION);
    }
}
