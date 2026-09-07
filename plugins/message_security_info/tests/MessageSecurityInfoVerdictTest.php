<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

require_once __DIR__ . '/../message_security_info.php';

/**
 * The verdict logic: how one mechanism's result becomes a severity, how the
 * severities combine, and the two cases where a result is not taken at face
 * value (relayed mail and mail the user submitted themselves).
 */
class MessageSecurityInfoVerdictTest extends TestCase
{
    /** Config keys these tests set, restored after each one. */
    private const CONFIG_KEYS = [
        'message_security_info_check_spf',
        'message_security_info_check_dkim',
        'message_security_info_check_dmarc',
        'message_security_info_check_tls',
        'message_security_info_check_submission',
        'message_security_info_trusted_authserv',
    ];

    /** @var array<string, mixed> */
    private $config_backup = [];

    #[\Override]
    protected function setUp(): void
    {
        $config = \rcube::get_instance()->config;

        foreach (self::CONFIG_KEYS as $key) {
            $this->config_backup[$key] = $config->get($key);
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $config = \rcube::get_instance()->config;

        // rcube is a singleton, so anything set here would leak into the next test.
        foreach ($this->config_backup as $key => $value) {
            $config->set($key, $value);
        }
    }

    /**
     * A plugin instance with its localization loaded, so the descriptions the
     * findings carry are the real label texts rather than placeholders.
     *
     * @param array<string, mixed> $config
     */
    private function plugin($config = [])
    {
        $rcube = \rcube::get_instance();

        foreach ($config as $key => $value) {
            $rcube->config->set($key, $value);
        }

        $plugin = new \message_security_info($rcube->plugins);
        $plugin->init();
        $plugin->add_texts('localization/');

        return $plugin;
    }

    /**
     * Test method_status() — one protocol result mapped to a severity
     *
     * @dataProvider provide_method_status_cases
     */
    #[DataProvider('provide_method_status_cases')]
    public function test_method_status($expected, $method, $result, $domain, $from)
    {
        $plugin = $this->plugin();
        $entry = ['result' => $result, 'domain' => $domain];

        $this->assertSame($expected, invokeMethod($plugin, 'method_status', [$method, $entry, $from]));
    }

    /**
     * Data sets for method_status() test
     */
    public static function provide_method_status_cases(): iterable
    {
        return [
            // A DKIM pass is only a pass while it speaks for the visible From.
            ['pass', 'dkim', 'pass', 'example.com', 'example.com'],
            ['pass', 'dkim', 'pass', 'mail.example.com', 'example.com'],
            ['warn', 'dkim', 'pass', 'esp.example.net', 'example.com'],
            ['warn', 'dkim', 'pass', null, 'example.com'],

            // SPF and DMARC are not judged on alignment here.
            ['pass', 'spf', 'pass', 'other.example.net', 'example.com'],
            ['pass', 'dmarc', 'pass', 'example.com', 'example.com'],

            ['fail', 'spf', 'fail', 'example.com', 'example.com'],
            ['fail', 'dmarc', 'fail', 'example.com', 'example.com'],

            ['warn', 'spf', 'softfail', 'example.com', 'example.com'],
            ['warn', 'spf', 'neutral', 'example.com', 'example.com'],
            ['warn', 'spf', 'policy', 'example.com', 'example.com'],
            ['warn', 'spf', 'permerror', 'example.com', 'example.com'],

            ['unknown', 'spf', 'temperror', 'example.com', 'example.com'],

            // 'none' says the mechanism is not in use, which is not a finding.
            ['none', 'spf', 'none', 'example.com', 'example.com'],
            ['none', 'dmarc', 'nonsense', 'example.com', 'example.com'],
        ];
    }

    /**
     * Test combine_statuses() — the worst of the per-mechanism severities
     *
     * @dataProvider provide_combine_statuses_cases
     */
    #[DataProvider('provide_combine_statuses_cases')]
    public function test_combine_statuses($expected, $statuses)
    {
        $plugin = $this->plugin();

        $this->assertSame($expected, invokeMethod($plugin, 'combine_statuses', [$statuses]));
    }

    /**
     * Data sets for combine_statuses() test
     */
    public static function provide_combine_statuses_cases(): iterable
    {
        return [
            ['pass', ['pass', 'pass', 'pass']],
            ['warn', ['pass', 'warn', 'pass']],
            ['fail', ['pass', 'warn', 'fail']],

            // A DMARC pass does not excuse a weaker result beside it.
            ['fail', ['fail', 'pass', 'pass']],
            ['warn', ['pass', 'pass', 'warn']],

            // 'none' contributes nothing, and is dropped before the comparison.
            ['pass', ['pass', 'none', 'none']],
            ['unknown', ['none', 'unknown']],

            // Nothing left to judge is a visible warning, never silence.
            ['warn', []],
            ['warn', ['none', 'none', 'none']],
        ];
    }

    /**
     * Test aligned() — relaxed alignment between two domains
     *
     * @dataProvider provide_aligned_cases
     */
    #[DataProvider('provide_aligned_cases')]
    public function test_aligned($expected, $domain, $from)
    {
        $plugin = $this->plugin();

        $this->assertSame($expected, invokeMethod($plugin, 'aligned', [$domain, $from]));
    }

    /**
     * Data sets for aligned() test
     */
    public static function provide_aligned_cases(): iterable
    {
        return [
            [true, 'example.com', 'example.com'],
            [true, 'Example.COM', 'example.com'],
            [true, 'example.com', 'mail.example.com'],
            [true, 'mail.example.com', 'example.com'],
            [false, 'example.com', 'example.net'],
            [false, 'notexample.com', 'example.com'],
            [false, null, 'example.com'],
            [false, 'example.com', null],
        ];
    }

    /**
     * Test best_dkim() — several signatures are alternatives, so the best wins
     */
    public function test_best_dkim()
    {
        $plugin = $this->plugin();
        $from = 'example.com';

        $aligned = ['result' => 'pass', 'domain' => 'example.com'];
        $unaligned = ['result' => 'pass', 'domain' => 'esp.example.net'];
        $failed = ['result' => 'fail', 'domain' => 'example.com'];

        // The aligned pass authenticates the message whatever the others say,
        // and its position among them does not matter.
        $this->assertSame($aligned, invokeMethod($plugin, 'best_dkim', [[$unaligned, $aligned], $from, null]));
        $this->assertSame($aligned, invokeMethod($plugin, 'best_dkim', [[$aligned, $unaligned], $from, null]));
        $this->assertSame($aligned, invokeMethod($plugin, 'best_dkim', [[$failed, $aligned], $from, null]));

        // A tie keeps the earliest, so a singly-signed message is unaffected.
        $first = ['result' => 'pass', 'domain' => 'one.example.net'];
        $second = ['result' => 'pass', 'domain' => 'two.example.net'];
        $this->assertSame($first, invokeMethod($plugin, 'best_dkim', [[$first, $second], $from, null]));

        $this->assertNull(invokeMethod($plugin, 'best_dkim', [[], $from, null]));
    }

    /**
     * Test demote_relayed_spf() — an SPF failure the sender's own DMARC forgave
     */
    public function test_demote_relayed_spf()
    {
        $plugin = $this->plugin();

        $relayed = [
            'spf' => ['verdict' => 'fail', 'description' => null],
            'dmarc' => ['verdict' => 'pass', 'description' => null],
        ];

        $result = invokeMethod($plugin, 'demote_relayed_spf', [$relayed]);

        $this->assertSame('warn', $result['spf']['verdict']);
        $this->assertSame($plugin->gettext('spfrelayed'), $result['spf']['description']);
        $this->assertSame('pass', $result['dmarc']['verdict']);

        // Without the DMARC pass behind it, an SPF failure is just a failure.
        $unforgiven = [
            'spf' => ['verdict' => 'fail', 'description' => null],
            'dmarc' => ['verdict' => 'fail', 'description' => null],
        ];

        $this->assertSame($unforgiven, invokeMethod($plugin, 'demote_relayed_spf', [$unforgiven]));
    }

    /**
     * Test submission_info() — recognising mail the user submitted themselves
     *
     * @dataProvider provide_submission_info_cases
     */
    #[DataProvider('provide_submission_info_cases')]
    public function test_submission_info($expected, $received)
    {
        $plugin = $this->plugin();
        $headers = \rcube_message_header::from_array(['received' => $received]);

        $this->assertSame($expected, invokeMethod($plugin, 'submission_info', [$headers]));
    }

    /**
     * Data sets for submission_info() test
     */
    public static function provide_submission_info_cases(): iterable
    {
        $submitted = 'from smtpclient.apple (unknown [192.168.8.126])'
            . " (using TLSv1.3 with cipher TLS_AES_256_GCM_SHA384 (256/256 bits))\n"
            . "\t(Authenticated sender: joe.user)\n"
            . "\tby mta-in.example.com (Postfix) with ESMTPSA id 77A101842B06\n"
            . "\tfor <joe@example.com>; Mon, 07 Sep 2026 07:14:07 +0300 (EEST)";

        $relayed = 'from mx.example.net (mx.example.net [203.0.113.9])'
            . ' by mta-in.example.com (Postfix) with ESMTPS id AAA'
            . ' for <joe@example.com>; Mon, 07 Sep 2026 07:14:08 +0300 (EEST)';

        return [
            'authenticated submission' => [
                ['user' => 'joe.user', 'client' => '192.168.8.126'], $submitted,
            ],

            // A single Received is a string, not an array, when it comes off IMAP.
            'single hop as an array' => [
                ['user' => 'joe.user', 'client' => '192.168.8.126'], [$submitted],
            ],

            // Authentication without TLS is still a submission (RFC 3848 ESMTPA).
            'ESMTPA' => [
                ['user' => 'joe.user', 'client' => '192.168.8.126'],
                str_replace('with ESMTPSA', 'with ESMTPA', $submitted),
            ],

            // Other MTAs log less than Postfix does.
            'no authenticated-sender clause' => [
                ['user' => null, 'client' => '192.168.8.126'],
                str_replace("\t(Authenticated sender: joe.user)\n", '', $submitted),
            ],
            'no client address' => [
                ['user' => 'joe.user', 'client' => null],
                str_replace('smtpclient.apple (unknown [192.168.8.126])', 'smtpclient.apple', $submitted),
            ],
            'IPv6 client' => [
                ['user' => 'joe.user', 'client' => '2001:db8::1'],
                str_replace('unknown [192.168.8.126]', 'unknown [IPv6:2001:db8::1]', $submitted),
            ],

            // Not a submission: the client did not authenticate.
            'ESMTPS is TLS, not authentication' => [null, $relayed],
            'plain ESMTP' => [null, str_replace('with ESMTPS ', 'with ESMTP ', $relayed)],

            // Not a submission: it travelled. A client's "redirect" back through
            // your own server keeps the hops that brought it, and lands here.
            'two hops' => [null, [$relayed, $submitted]],
            'no Received at all' => [null, null],
        ];
    }

    /**
     * Test that the submission check reads the server's own address, not the
     * client's, from the wrong side of the "by" clause
     */
    public function test_submission_info_ignores_server_address()
    {
        $plugin = $this->plugin();

        // No address on the client side; the server's must not be picked up.
        $received = 'from smtpclient.apple (Authenticated sender: joe.user)'
            . ' by mta-in.example.com (Postfix [198.51.100.7]) with ESMTPSA id AAA';

        $headers = \rcube_message_header::from_array(['received' => $received]);
        $result = invokeMethod($plugin, 'submission_info', [$headers]);

        $this->assertNull($result['client']);
        $this->assertSame('joe.user', $result['user']);
    }

    /**
     * Test excuse_local_submission() — SPF and DMARC stop counting on mail that
     * never travelled, and nothing is ever promoted by it
     */
    public function test_excuse_local_submission()
    {
        $plugin = $this->plugin();
        $submission = ['user' => 'joe.user', 'client' => '192.168.8.126'];

        $findings = [
            'spf' => ['verdict' => 'fail', 'description' => null],
            'dkim' => ['verdict' => 'unknown', 'description' => 'x'],
            'dmarc' => ['verdict' => 'fail', 'description' => null],
        ];

        $result = invokeMethod($plugin, 'excuse_local_submission', [$findings, $submission]);

        $this->assertSame('none', $result['spf']['verdict']);
        $this->assertSame('none', $result['dmarc']['verdict']);
        $this->assertSame($plugin->gettext('localsubmission'), $result['spf']['description']);
        $this->assertSame($plugin->gettext('localsubmission'), $result['dmarc']['description']);

        // DKIM is a statement about the message itself, so it still counts.
        $this->assertSame($findings['dkim'], $result['dkim']);

        // A softfail is adverse too, and goes the same way.
        $soft = invokeMethod($plugin, 'excuse_local_submission', [
            ['spf' => ['verdict' => 'warn', 'description' => null]], $submission,
        ]);
        $this->assertSame('none', $soft['spf']['verdict']);

        // Only adverse verdicts are dropped: what held speaks for itself.
        $passed = [
            'spf' => ['verdict' => 'pass', 'description' => null],
            'dmarc' => ['verdict' => 'pass', 'description' => null],
        ];
        $this->assertSame($passed, invokeMethod($plugin, 'excuse_local_submission', [$passed, $submission]));

        // Not a submission: nothing is touched.
        $this->assertSame($findings, invokeMethod($plugin, 'excuse_local_submission', [$findings, null]));
    }

    /**
     * Test that authenticating never promotes a verdict to 'pass'
     *
     * A server that does not bind the login to the From address lets an
     * authenticated user write any sender they like, and nothing in the message
     * says whether yours does. So a message with nothing left to judge has to
     * come back as a visible warning.
     */
    public function test_local_submission_is_never_promoted()
    {
        $plugin = $this->plugin();
        $submission = ['user' => 'joe.user', 'client' => '192.168.8.126'];

        $findings = [
            'spf' => ['verdict' => 'fail', 'description' => null],
            'dmarc' => ['verdict' => 'fail', 'description' => null],
        ];

        $excused = invokeMethod($plugin, 'excuse_local_submission', [$findings, $submission]);
        $verdict = invokeMethod($plugin, 'evaluate', [$excused]);

        $this->assertSame('warn', $verdict['status']);
        $this->assertSame($plugin->gettext('summarywarn'), $verdict['summary']);
    }

    /**
     * Test that the submission check can be switched off
     *
     * The flag is read in one place, so the row and the verdict cannot disagree
     * about whether the message was submitted.
     */
    public function test_submission_check_disabled()
    {
        $received = 'from smtpclient.apple (unknown [192.168.8.126])'
            . ' (Authenticated sender: joe.user)'
            . ' by mta-in.example.com (Postfix) with ESMTPSA id AAA';

        $headers = \rcube_message_header::from_array(['received' => $received]);

        $on = $this->plugin(['message_security_info_check_submission' => true]);
        $this->assertNotNull(invokeMethod($on, 'submission', [$headers]));

        $off = $this->plugin(['message_security_info_check_submission' => false]);
        $this->assertNull(invokeMethod($off, 'submission', [$headers]));

        // The detection itself is unchanged — only whether it is consulted.
        $this->assertNotNull(invokeMethod($off, 'submission_info', [$headers]));
    }

    /**
     * Test format_submission() — who authenticated, and from where
     */
    public function test_format_submission()
    {
        $plugin = $this->plugin();

        $this->assertSame(
            'Authenticated as joe.user, from 192.168.8.126',
            invokeMethod($plugin, 'format_submission', [['user' => 'joe.user', 'client' => '192.168.8.126']])
        );
        $this->assertSame(
            'Authenticated client, from 192.168.8.126',
            invokeMethod($plugin, 'format_submission', [['user' => null, 'client' => '192.168.8.126']])
        );
        $this->assertSame(
            'Authenticated as joe.user',
            invokeMethod($plugin, 'format_submission', [['user' => 'joe.user', 'client' => null]])
        );
        $this->assertSame(
            'Authenticated client',
            invokeMethod($plugin, 'format_submission', [['user' => null, 'client' => null]])
        );
    }
}
