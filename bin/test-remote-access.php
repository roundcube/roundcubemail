<?php

function say($msg)
{
    echo wordwrap("{$msg}\n");
}

function error($msg, $exitcode = 1)
{
    say($msg);
    exit($exitcode);
}

function get_http_data($url)
{
    $ch = curl_init();
    curl_setopt($ch, \CURLOPT_URL, $url);
    curl_setopt($ch, \CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, \CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, \CURLOPT_HEADER, false);
    return [
        'body' => curl_exec($ch),
        'headers' => curl_getinfo($ch),
    ];
}

function test_dir($dir_name, $base_url)
{
    try {
        $rcmail = rcmail::get_instance();
        $temp_folder_path = rtrim($rcmail->config->get($dir_name, ''), '/');
        if ($temp_folder_path === '') {
            error("Something's wrong: couldn't get config value for '{$dir_name}'!");
        }

        $temp_folder_url_path = str_replace(RCUBE_INSTALL_PATH, '', $temp_folder_path);

        // Write something into a random file name.
        $random_string = bin2hex(random_bytes(10));
        $test_filename = "{$random_string}.txt";
        $test_path = $temp_folder_path . \DIRECTORY_SEPARATOR . $test_filename;
        fwrite(fopen($test_path, 'w'), 'test');

        // Test access to that file via HTTP.
        $url = rtrim($base_url, '/') . '/' . $temp_folder_url_path . '/' . $test_filename;
        $data = get_http_data($url);
        $status_code = $data['headers']['http_code'];
        switch (intdiv($status_code, 100)) {
            case 2:
                say("\n\n❌ ATTENTION: Your configuration is vulnerable to massive information leakage of sensitive data from the configured '{$dir_name}' folder!\nWe strongly recommend to change this option to a directory outside of the document root (it must still be accessible for the PHP process).\n");
                return 1;
            case 4:
                say("✅ Ok, you're safe. Your '{$dir_name}' directory is not accessible from the outside world");
                return 0;
            default:
                say("⚠️ Could not determine if your server is vulnerable or not. It returned status code {$status_code} for our test URL {$url}");
                return 1;
        }
    } finally {
        if (isset($test_path)) {
            unlink($test_path);
        }
    }
}

if (!isset($argv[1]) || in_array($argv[1], ['', '-h', '--help'])) {
    error('Usage: ' . basename(__FILE__) . ' your_roundcubemail_base_url');
}

$base_url = filter_var($argv[1], \FILTER_VALIDATE_URL);

if ($base_url === false) {
    error('⚠️ The given argument not a valid base URL!');
}

$data = get_http_data($base_url);
// Check if this URL actually serves a Roundcubemail. Note: This check must work
// for very old versions of Roundcubemail, too!
if (!strpos($data['body'], 'program/js/app.') !== false) {
    error('⚠️ Error: The given URL is not serving Roundcubemail!');
}

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/');
require_once INSTALL_PATH . 'program/include/clisetup.php';

$return_values = [];
$return_values[] = test_dir('temp_dir', $base_url);
$return_values[] = test_dir('log_dir', $base_url);

exit(max($return_values));
