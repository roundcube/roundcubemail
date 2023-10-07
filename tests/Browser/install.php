<?php

/*
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Chrome WebDriver download tool                                      |
 +-----------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

if (php_sapi_name() != 'cli') {
    die("Not in shell mode (php-cli)");
}

if (!defined('INSTALL_PATH')) {
    define('INSTALL_PATH', realpath(__DIR__ . '/../../') . '/' );
}

require_once(INSTALL_PATH . 'program/include/iniset.php');

class Installer extends Laravel\Dusk\Console\ChromeDriverCommand
{
    /**
     * Execute the console command.
     *
     * @param string $version
     *
     * @return void
     */
    public function install($version = '')
    {
        $os = Laravel\Dusk\OperatingSystem::id();
        $version = trim($version);
        $archive = $this->directory . 'chromedriver.zip';

        $url = $this->resolveChromeDriverDownloadUrl($version, $os);

        $client = new \GuzzleHttp\Client();

        $response = $client->get($url);

        $data = file_put_contents($archive, $response->getBody());

        $binary = $this->extract($version, $archive);

        $this->rename($binary, $os);

        echo "ChromeDriver binary successfully installed for version $version.\n";
    }

    /**
     * Get the contents of a URL
     *
     * @param string $url URL
     *
     * @return string|bool
     */
    protected function getUrl(string $url)
    {
        return file_get_contents($url);
    }
}

if (empty($argv[1])) {
    rcube::raise_error("Chrome driver version is a required argument of this script.", false, true);
}

$installer = new Installer;
$installer->install($argv[1]);
