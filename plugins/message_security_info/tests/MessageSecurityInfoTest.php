<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function Roundcube\Tests\invokeMethod;

require_once __DIR__ . '/../message_security_info.php';

/**
 * The plugin's outward surface: which headers it asks IMAP for, what it makes
 * of a whole message, and what the popup ends up showing.
 */
class MessageSecurityInfoTest extends TestCase
{
    /** Config keys these tests set, restored after each one. */
    private const CONFIG_KEYS = [
        'message_security_info_check_spf',
        'message_security_info_check_dkim',
        'message_security_info_check_dmarc',
        'message_security_info_check_tls',
        'message_security_info_check_submission',
        'message_security_info_trusted_authserv',
        'message_security_info_extra_headers',
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
     * A plugin instance with its localization loaded, so the rows below are the
     * real label texts rather than placeholders.
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
     * Headers in the shape IMAP hands them over: a repeated header is an array
     * in document order, a single one is a plain string.
     *
     * @param array<string, string|string[]> $headers
     */
    private function headers($headers)
    {
        return \rcube_message_header::from_array($headers);
    }

    /**
     * A message the user submitted to their own server — the case SPF and DMARC
     * are not a check on. Shaped after a real one: iOS Mail to Postfix, which
     * stamped an SPF and a DMARC failure on mail that never left the building.
     *
     * @param array<string, string|string[]> $override
     */
    private function submitted_message($override = [])
    {
        return $this->headers(array_merge([
            'from' => 'Joe User <joe@example.com>',
            'received' => "from smtpclient.apple (unknown [192.168.8.126])\n"
                . "\t(using TLSv1.3 with cipher TLS_AES_256_GCM_SHA384 (256/256 bits))\n"
                . "\t(No client certificate requested)\n"
                . "\t(Authenticated sender: joe.user)\n"
                . "\tby mta-in.example.com (Postfix) with ESMTPSA id 77A101842B06\n"
                . "\tfor <apple@example.com>; Mon, 07 Sep 2026 07:14:07 +0300 (EEST)",
            'authentication-results' => [
                'mta-in.example.com; dmarc=fail (p=quarantine dis=none) header.from=example.com',
                'mta-in.example.com; spf=fail smtp.mailfrom=example.com',
            ],
            'dkim-signature' => 'v=1; a=rsa-sha256; c=relaxed/relaxed; d=example.com; s=mail;'
                . " t=1788754447; h=From:Date:Subject:To:From;\n\tb=DuZUOZaGhGpLSUo1w0wm",
        ], $override));
    }

    /**
     * Test the plugin loads and registers
     */
    public function test_constructor()
    {
        $plugin = $this->plugin();

        $this->assertInstanceOf(\message_security_info::class, $plugin);
        $this->assertInstanceOf(\rcube_plugin::class, $plugin);
    }

    /**
     * Test storage_init() — the headers the core is asked to fetch from IMAP
     *
     * @dataProvider provide_storage_init_cases
     */
    #[DataProvider('provide_storage_init_cases')]
    public function test_storage_init($expected, $missing, $config)
    {
        $plugin = $this->plugin($config);
        $result = invokeMethod($plugin, 'storage_init', [['fetch_headers' => '']]);
        $fetched = explode(' ', $result['fetch_headers']);

        foreach ($expected as $header) {
            $this->assertContains($header, $fetched);
        }
        foreach ($missing as $header) {
            $this->assertNotContains($header, $fetched);
        }

        // Whatever another plugin already asked for has to survive.
        $chained = invokeMethod($plugin, 'storage_init', [['fetch_headers' => 'X-OTHER']]);
        $this->assertContains('X-OTHER', explode(' ', $chained['fetch_headers']));
    }

    /**
     * Data sets for storage_init() test
     */
    public static function provide_storage_init_cases(): iterable
    {
        $base = ['DKIM-SIGNATURE', 'AUTHENTICATION-RESULTS', 'RECEIVED-SPF'];

        return [
            'defaults' => [array_merge($base, ['RECEIVED']), [], []],

            // Received is fetched for either reader, so one switch cannot take
            // it away from the other.
            'submission only' => [
                ['RECEIVED'], [],
                ['message_security_info_check_tls' => false, 'message_security_info_check_submission' => true],
            ],
            'tls only' => [
                ['RECEIVED'], [],
                ['message_security_info_check_tls' => true, 'message_security_info_check_submission' => false],
            ],
            'neither reader' => [
                $base, ['RECEIVED'],
                ['message_security_info_check_tls' => false, 'message_security_info_check_submission' => false],
            ],

            // Extra headers are configured in whatever case the admin likes.
            'extra headers' => [
                ['X-SPAM-STATUS'], [],
                ['message_security_info_extra_headers' => ['X-Spam-Status']],
            ],
        ];
    }

    /**
     * Test security_fields() on mail the user submitted themselves
     *
     * The failures the server stamped are still shown as FAIL — only the
     * severity read from them changes, so nothing is hidden from the reader.
     */
    public function test_security_fields_local_submission()
    {
        $plugin = $this->plugin();
        $headers = $this->submitted_message();
        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);

        $this->assertSame('FAIL', $security['spf']['status']);
        $this->assertSame('none', $security['spf']['verdict']);
        $this->assertSame($plugin->gettext('localsubmission'), $security['spf']['description']);

        $this->assertSame('FAIL', $security['dmarc']['status']);
        $this->assertSame('none', $security['dmarc']['verdict']);

        // The server reported no DKIM result, so the signature stays a claim.
        $this->assertSame('unknown', $security['dkim']['verdict']);
        $this->assertSame('example.com', $security['dkim']['domain']);
        $this->assertFalse($security['dkim']['verified']);

        // Nothing left that was a check on this message: a warning, not a pass.
        $this->assertSame('unknown', invokeMethod($plugin, 'evaluate', [$security])['status']);
    }

    /**
     * Test that the same message fails when submission handling is off
     *
     * This is what the plugin did before, and what a site whose submission path
     * is not its inbound trust boundary still wants.
     */
    public function test_security_fields_submission_check_off()
    {
        $plugin = $this->plugin(['message_security_info_check_submission' => false]);
        $headers = $this->submitted_message();
        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);

        $this->assertSame('fail', $security['spf']['verdict']);
        $this->assertSame('fail', $security['dmarc']['verdict']);
        $this->assertSame('fail', invokeMethod($plugin, 'evaluate', [$security])['status']);
    }

    /**
     * Test security_fields() on relayed mail — a mailing list breaks SPF
     */
    public function test_security_fields_relayed()
    {
        $plugin = $this->plugin();

        $headers = $this->headers([
            'from' => 'Joe User <joe@example.com>',
            'received' => [
                'from lists.example.net (lists.example.net [203.0.113.4]) by mx.example.org'
                    . ' (Postfix) with ESMTPS id AAA; Mon, 07 Sep 2026 07:14:08 +0300',
                'from mail.example.com (mail.example.com [198.51.100.2]) by lists.example.net'
                    . ' (Postfix) with ESMTPS id BBB; Mon, 07 Sep 2026 07:14:00 +0300',
            ],
            'authentication-results' => 'mx.example.org; spf=fail smtp.mailfrom=lists.example.net;'
                . ' dkim=pass header.d=example.com; dmarc=pass header.from=example.com',
        ]);

        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);

        // Demoted, not excused: DMARC weighed the failure and took the message.
        $this->assertSame('FAIL', $security['spf']['status']);
        $this->assertSame('warn', $security['spf']['verdict']);
        $this->assertSame($plugin->gettext('spfrelayed'), $security['spf']['description']);

        $this->assertSame('pass', $security['dkim']['verdict']);
        $this->assertSame('warn', invokeMethod($plugin, 'evaluate', [$security])['status']);
    }

    /**
     * Test security_fields() on ordinary mail that passes everything
     */
    public function test_security_fields_pass()
    {
        $plugin = $this->plugin();

        $headers = $this->headers([
            'from' => 'Joe User <joe@example.com>',
            'received' => 'from mail.example.com (mail.example.com [198.51.100.2]) by mx.example.org'
                . ' (Postfix) with ESMTPS id AAA; Mon, 07 Sep 2026 07:14:08 +0300',
            'authentication-results' => 'mx.example.org; spf=pass smtp.mailfrom=example.com;'
                . ' dkim=pass header.d=example.com; dmarc=pass header.from=example.com',
        ]);

        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);

        $this->assertSame('pass', invokeMethod($plugin, 'evaluate', [$security])['status']);
        $this->assertTrue($security['dkim']['aligned']);

        // It arrived over TLS but was not submitted, so no submission row.
        $this->assertNull(invokeMethod($plugin, 'submission', [$headers]));
    }

    /**
     * Test that a disabled mechanism is dropped from the findings entirely
     */
    public function test_disabled_mechanism_is_dropped()
    {
        $plugin = $this->plugin([
            'message_security_info_check_spf' => false,
            'message_security_info_check_dmarc' => false,
        ]);

        $headers = $this->submitted_message();
        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);

        $this->assertSame(['dkim'], array_keys($security));
    }

    /**
     * Test summary_rows() — the popup's rows, in order
     */
    public function test_summary_rows()
    {
        $plugin = $this->plugin();
        $headers = $this->submitted_message();
        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);
        $rows = invokeMethod($plugin, 'summary_rows', [$headers, $security]);

        $this->assertSame(
            ['From', 'SPF', 'DKIM', 'DMARC', 'Submission', 'Transport (TLS)'],
            array_column($rows, 'label')
        );

        $this->assertSame('Joe User <joe@example.com>', $rows[0]['value']);
        $this->assertSame('FAIL — example.com (submitted from your own server, not relayed)', $rows[1]['value']);
        $this->assertSame('Authenticated as joe.user, from 192.168.8.126', $rows[4]['value']);
        $this->assertSame('Encrypted — TLSv1.3', $rows[5]['value']);

        // Only the mechanism rows carry a glyph; From and Transport have none.
        $this->assertArrayNotHasKey('verdict', $rows[0]);
        $this->assertSame('none', $rows[1]['verdict']);
        $this->assertArrayNotHasKey('verdict', $rows[4]);
        $this->assertArrayNotHasKey('verdict', $rows[5]);
    }

    /**
     * Test that the rows the toggles govern come and go with them
     */
    public function test_summary_rows_optional_rows()
    {
        $headers = $this->submitted_message();

        $plugin = $this->plugin([
            'message_security_info_check_tls' => false,
            'message_security_info_check_submission' => false,
        ]);

        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);
        $security = invokeMethod($plugin, 'security_fields', [$headers, $auth]);
        $labels = array_column(invokeMethod($plugin, 'summary_rows', [$headers, $security]), 'label');

        $this->assertSame(['From', 'SPF', 'DKIM', 'DMARC'], $labels);
    }

    /**
     * Test parse_authresults() — only the server you trust gets a say
     */
    public function test_parse_authresults_trusted_authserv()
    {
        $headers = $this->headers([
            'authentication-results' => [
                'mx.example.org; spf=pass smtp.mailfrom=example.com',
                'evil.example.net; spf=pass smtp.mailfrom=example.com; dkim=pass header.d=example.com',
            ],
        ]);

        // Untrusted hops can stamp whatever they like, so with a trusted id set,
        // only its results survive.
        $plugin = $this->plugin(['message_security_info_trusted_authserv' => ['mx.example.org']]);
        $auth = invokeMethod($plugin, 'parse_authresults', [$headers]);

        $this->assertCount(1, $auth['spf']);
        $this->assertSame([], $auth['dkim']);

        // Empty means "consider them all" — convenient, and spoofable.
        $open = $this->plugin(['message_security_info_trusted_authserv' => []]);
        $all = invokeMethod($open, 'parse_authresults', [$headers]);

        $this->assertCount(2, $all['spf']);
        $this->assertCount(1, $all['dkim']);
    }

    /**
     * Test spf_from_received() — the SPF result many servers only record here
     */
    public function test_spf_from_received()
    {
        $plugin = $this->plugin();

        $headers = $this->headers([
            'received-spf' => 'Pass (mailfrom) identity=mailfrom; client-ip=198.51.100.2;'
                . ' helo=mail.example.com; envelope-from=joe@example.com; receiver=<UNKNOWN>',
        ]);

        $this->assertSame(
            ['result' => 'pass', 'domain' => 'example.com'],
            invokeMethod($plugin, 'spf_from_received', [$headers])
        );

        $this->assertNull(invokeMethod($plugin, 'spf_from_received', [$this->headers([])]));
    }

    /**
     * Test tls_info() and format_tls() — the informational transport line
     *
     * @dataProvider provide_tls_info_cases
     */
    #[DataProvider('provide_tls_info_cases')]
    public function test_tls_info($expected, $received)
    {
        $plugin = $this->plugin();
        $tls = invokeMethod($plugin, 'tls_info', [$this->headers(['received' => $received])]);

        $this->assertSame($expected, invokeMethod($plugin, 'format_tls', [$tls]));
    }

    /**
     * Data sets for tls_info() test
     */
    public static function provide_tls_info_cases(): iterable
    {
        $by = ' by mx.example.org (Postfix) with ';

        return [
            ['Encrypted — TLSv1.3', 'from mail.example.com (using TLSv1.3 cipher X)' . $by . 'ESMTPS id AAA'],
            ['Encrypted', 'from mail.example.com' . $by . 'ESMTPS id AAA'],
            ['Encrypted', 'from mail.example.com' . $by . 'ESMTPSA id AAA'],
            ['Not encrypted', 'from mail.example.com' . $by . 'ESMTP id AAA'],

            // Authenticated without TLS: the "A" is not the "S".
            ['Not encrypted', 'from mail.example.com' . $by . 'ESMTPA id AAA'],

            // A logged version with no recognised transmission type still counts.
            ['Encrypted — TLS1.3', 'from mail.example.com by mx.example.org (version=TLS1_3 cipher=X)'],

            ['unknown', 'from mail.example.com by mx.example.org via local id AAA'],
            ['unknown', null],
        ];
    }

    /**
     * Test from_address() — the address a display name would otherwise hide
     *
     * @dataProvider provide_from_address_cases
     */
    #[DataProvider('provide_from_address_cases')]
    public function test_from_address($expected, $from)
    {
        $plugin = $this->plugin();

        $this->assertSame($expected, invokeMethod($plugin, 'from_address', [$this->headers(['from' => $from])]));
    }

    /**
     * Data sets for from_address() test
     */
    public static function provide_from_address_cases(): iterable
    {
        return [
            ['Joe User <joe@example.com>', 'Joe User <joe@example.com>'],
            ['joe@example.com', 'joe@example.com'],
            ['joe@example.com', '<joe@example.com>'],
            // A quoted display name is unquoted, so the two forms read alike.
            ['Joe User <joe@example.com>', '"Joe User" <joe@example.com>'],
            [null, ''],
        ];
    }

    /**
     * Test security_line() — one finding as the popup prints it
     */
    public function test_security_line()
    {
        $plugin = $this->plugin();

        $line = static fn ($entry) => invokeMethod($plugin, 'security_line', [$entry]);

        // A clean aligned pass needs no comment beside it.
        $this->assertSame('PASS — example.com', $line([
            'status' => 'PASS', 'domain' => 'example.com', 'aligned' => true, 'description' => 'aligned with From',
        ]));

        // An unaligned pass carries its mismatch on a second line.
        $this->assertSame("PASS — esp.example.net\ndoes not match From (example.com)", $line([
            'status' => 'PASS', 'domain' => 'esp.example.net', 'aligned' => false,
            'description' => 'does not match From (example.com)',
        ]));

        // Anything else parenthesises it.
        $this->assertSame('FAIL — example.com (why)', $line([
            'status' => 'FAIL', 'domain' => 'example.com', 'aligned' => null, 'description' => 'why',
        ]));

        // No result at all: the description is the whole line.
        $this->assertSame('not present', $line([
            'status' => null, 'domain' => null, 'aligned' => null, 'description' => 'not present',
        ]));
        $this->assertSame('present, not verified by your server — example.com', $line([
            'status' => null, 'domain' => 'example.com', 'aligned' => null,
            'description' => 'present, not verified by your server',
        ]));
    }
}
