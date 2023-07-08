#!/usr/bin/env php
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
 |   Check local configuration and database schema after upgrading       |
 |   to a new version                                                    |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

define('INSTALL_PATH', realpath(__DIR__ . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/clisetup.php';

// get arguments
$opts = rcube_utils::get_opt(['v' => 'version', 'y' => 'accept:bool']);

// ask user if no version is specified
if (empty($opts['version'])) {
    echo "What version are you upgrading from? Type '?' if you don't know.\n";

    if (($input = trim(fgets(STDIN))) && preg_match('/^[0-9.]+[a-z0-9-]*$/', $input)) {
        $opts['version'] = $input;
    }
    else {
        $opts['version'] = RCMAIL_VERSION;
    }
}

$RCI = rcmail_install::get_instance();
$RCI->load_config();

if ($RCI->configured) {
    $success = true;

    if (($messages = $RCI->check_config($opts['version'])) || $RCI->legacy_config) {
        $success = false;
        $err = 0;

        // list old/replaced config options
        if (!empty($messages['replaced'])) {
            echo "WARNING: Replaced config options:\n";
            echo "(These config options have been replaced or renamed)\n";

            foreach ($messages['replaced'] as $msg) {
                echo "- '" . $msg['prop'] . "' was replaced by '" . $msg['replacement'] . "'\n";
                $err++;
            }
        }

        // list obsolete config options (just a notice)
        if (!empty($messages['obsolete'])) {
            echo "NOTICE: Obsolete config options:\n";
            echo "(You still have some obsolete or inexistent properties set."
                . " This isn't a problem but should be noticed)\n";

            foreach ($messages['obsolete'] as $msg) {
                echo "- '" . $msg['prop'] . (!empty($msg['explain']) ? "': " . $msg['explain'] : "'") . "\n";
                $err++;
            }
        }

        if (!$err && $RCI->legacy_config) {
            echo "WARNING: Your configuration needs to be migrated!\n";
            echo "We changed the configuration files structure and your two config files "
                . "main.inc.php and db.inc.php have to be merged into one single file.\n";
            $err++;
        }

        // ask user to update config files
        if ($err) {
            if (empty($opts['accept'])) {
                echo "Do you want me to fix your local configuration? (y/N)\n";
                $input = trim(fgets(STDIN));
            }

            // positive: merge the local config with the defaults
            if (!empty($opts['accept']) || strtolower($input) == 'y') {
                $error = $written = false;

                echo "- backing up the current config file(s)...\n";

                foreach (['config', 'main', 'db'] as $file) {
                    if (file_exists(RCMAIL_CONFIG_DIR . '/' . $file . '.inc.php')) {
                        if (!copy(RCMAIL_CONFIG_DIR . '/' . $file . '.inc.php', RCMAIL_CONFIG_DIR . '/' . $file . '.old.php')) {
                            $error = true;
                        }
                    }
                }

                if (!$error) {
                    $RCI->merge_config();
                    echo "- writing " . RCMAIL_CONFIG_DIR . "/config.inc.php...\n";
                    $written = $RCI->save_configfile($RCI->create_config(false));
                }

                // Success!
                if ($written) {
                    echo "Done.\n";
                    echo "Your configuration files are now up-to-date!\n";

                    if (!empty($messages['missing'])) {
                        echo "But you still need to add the following missing options:\n";
                        foreach ($messages['missing'] as $msg) {
                            echo "- '" . $msg['prop'] . ($msg['name'] ? "': " . $msg['name'] : "'") . "\n";
                        }
                    }

                    if ($RCI->legacy_config) {
                        foreach (['main', 'db'] as $file) {
                            @unlink(RCMAIL_CONFIG_DIR . '/' . $file . '.inc.php');
                        }
                    }
                }
                else {
                    echo "Failed to write config file(s)!\n";
                    echo "Grant write privileges to the current user or update the files manually "
                        . "according to the above messages.\n";
                }
            }
            else {
                echo "Please update your config files manually according to the above messages.\n";
            }
        }

        // list of config options with changed default (just a notice)
        if (!empty($messages['defaults'])) {
            echo "WARNING: Changed defaults (These config options have new default values):\n";

            foreach ($messages['defaults'] as $opt) {
                echo "- '{$opt}'\n";
            }
        }

        // check dependencies based on the current configuration
        if (!empty($messages['dependencies'])) {
            echo "WARNING: Dependency check failed!\n";
            echo "(Some of your configuration settings require other options to be configured "
                . "or additional PHP modules to be installed)\n";

            foreach ($messages['dependencies'] as $msg) {
                echo "- " . $msg['prop'] . ': ' . $msg['explain'] . "\n";
            }

            echo "Please fix your config files and run this script again!\n";
            echo "See ya.\n";
        }
    }

    // check file type detection
    if ($RCI->check_mime_detection()) {
        echo "WARNING: File type detection doesn't work properly!\n";
        echo "Please check the 'mime_magic' config option or the finfo functions of PHP and run this script again.\n";
    }
    if ($RCI->check_mime_extensions()) {
        echo "WARNING: Mimetype to file extension mapping doesn't work properly!\n";
        echo "Please check the 'mime_types' config option and run this script again.\n";
    }

    // check database schema
    if (!empty($RCI->config['db_dsnw'])) {
        echo "Executing database schema update.\n";
        $success = rcmail_utils::db_update(INSTALL_PATH . 'SQL', 'roundcube', $opts['version'], ['errors' => true]);
    }

    // update composer dependencies
    if (is_file(INSTALL_PATH . 'composer.json') && is_readable(INSTALL_PATH . 'composer.json-dist')) {
        $composer_data     = json_decode(file_get_contents(INSTALL_PATH . 'composer.json'), true);
        $composer_template = json_decode(file_get_contents(INSTALL_PATH . 'composer.json-dist'), true);
        $composer_json    = null;

        // update the require section with the new dependencies
        if (!empty($composer_data['require']) && !empty($composer_template['require'])) {
            $composer_data['require'] = array_merge($composer_data['require'], $composer_template['require']);

            // remove obsolete packages
            $old_packages = [
                'pear-pear.php.net/net_socket',
                'pear-pear.php.net/auth_sasl',
                'pear-pear.php.net/net_idna2',
                'pear-pear.php.net/mail_mime',
                'pear-pear.php.net/net_smtp',
                'pear-pear.php.net/crypt_gpg',
                'pear-pear.php.net/net_sieve',
                'pear/mail_mime-decode',
                'roundcube/net_sieve',
                'endroid/qrcode',
                'endroid/qr-code',
            ];

            foreach ($old_packages as $pkg) {
                if (array_key_exists($pkg, $composer_data['require'])) {
                    unset($composer_data['require'][$pkg]);
                }
            }
        }

        // update the repositories section with the new dependencies
        if (!empty($composer_template['repositories'])) {
            if (empty($composer_data['repositories'])) {
                $composer_data['repositories'] = [];
            }

            foreach ($composer_template['repositories'] as $repo) {
                $rkey = repo_key($repo);
                $existing = false;

                foreach ($composer_data['repositories'] as $k =>  $_repo) {
                    if ($rkey == repo_key($_repo)) {
                        // switch to https://
                        if (isset($_repo['url']) && strpos($_repo['url'], 'http://') === 0) {
                            $composer_data['repositories'][$k]['url'] = 'https:' . substr($_repo['url'], 5);
                        }

                        $existing = true;
                        break;
                    }

                    // remove old repos
                    if (isset($_repo['url']) && strpos($_repo['url'], 'git://git.kolab.org') === 0) {
                        unset($composer_data['repositories'][$k]);
                    }
                    else if (
                        $_repo['type'] == 'package'
                        && !empty($_repo['package']['name'])
                        && $_repo['package']['name'] == 'Net_SMTP'
                    ) {
                        unset($composer_data['repositories'][$k]);
                    }
                }

                if (!$existing) {
                    $composer_data['repositories'][] = $repo;
                }
            }

            $composer_data['repositories'] = array_values($composer_data['repositories']);
        }

        $composer_json = json_encode($composer_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // write updated composer.json back to disk
        if ($composer_json && is_writeable(INSTALL_PATH . 'composer.json')) {
            $success &= (bool)file_put_contents(INSTALL_PATH . 'composer.json', $composer_json);
        }
        else {
            echo "WARNING: unable to update composer.json!\n";
            echo "Please replace the 'require' section in your composer.json with the following:\n";

            $require_json = '';
            foreach ($composer_data['require'] as $pkg => $ver) {
                $require_json .= sprintf('        "%s": "%s",'."\n", $pkg, $ver);
            }

            echo '    "require": {'."\n";
            echo rtrim($require_json, ",\n");
            echo "\n    }\n\n";
        }

        if (!rcmail_install::vendor_dir_untouched(INSTALL_PATH)) {
            $exit_code = 1;
            if ($composer_bin = find_composer()) {
                echo "Executing " . $composer_bin . " to update dependencies...\n";
                echo system("$composer_bin update -d " . escapeshellarg(INSTALL_PATH) . " --no-dev", $exit_code);
            }
            if ($exit_code != 0) {
                echo "-----------------------------------------------------------------------------\n";
                echo "ATTENTION: Update dependencies by running `php composer.phar update --no-dev`\n";
                echo "-----------------------------------------------------------------------------\n";
            }
        }
    }

    // index contacts for fulltext searching
    if ($opts['version'] && version_compare(version_parse($opts['version']), '0.6.0', '<')) {
        rcmail_utils::indexcontacts();
    }

    if ($success) {
        echo "This instance of Roundcube is up-to-date.\n";
        echo "Have fun!\n";
    }
}
else {
    echo "This instance of Roundcube is not yet configured!\n";
    echo "Open http://url-to-roundcube/installer/ in your browser and follow the instructions.\n";
}

function repo_key($repo)
{
    $key = $repo['type'];

    if (!empty($repo['url'])) {
        $key .= preg_replace('/^https?:/', '', $repo['url']);
    }

    if (!empty($repo['package']['name'])) {
        $key .= $repo['package']['name'];
    }

    return $key;
}

function find_composer()
{
    if (is_file(INSTALL_PATH . 'composer.phar')) {
        return 'php composer.phar';
    }

    foreach (['composer', 'composer.phar'] as $check_file) {
        $which = trim(system("which $check_file"));
        if (!empty($which)) {
            return $which;
        }
    }

    return null;
}
