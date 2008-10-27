#!/usr/bin/php
<?php

define('INSTALL_PATH', realpath(dirname(__FILE__) . '/..') . '/' );

require_once INSTALL_PATH . 'program/include/iniset.php';
require_once INSTALL_PATH . 'installer/rcube_install.php';

$RCI = rcube_install::get_instance();
$RCI->load_config();

if ($RCI->configured) {
  if ($messages = $RCI->check_config()) {
    $err = 0;

    // list missing config options
    if (is_array($messages['missing'])) {
      echo "WARNING: Missing config options:\n";
      echo "(These config options should be present in the current configuration)\n";

      foreach ($messages['missing'] as $msg) {
        echo '- ' . $msg['prop'] . ($msg['name'] ? ': ' . $msg['name'] : '') . "\n";
        $err++;
      }
      echo "\n";
    }

    // list old/replaced config options
    if (is_array($messages['replaced'])) {
      echo "WARNING: Replaced config options:\n";
      echo "(These config options have been replaced or renamed)\n";

      foreach ($messages['replaced'] as $msg) {
        echo "- " . $msg['prop'] . "\t\t was replaced by " . $msg['replacement'] . "\n";
        $err++;
      }
      echo "\n";
    }

    // list obsolete config options (just a notice)
    if (is_array($messages['obsolete'])) {
      echo "NOTICE: Obsolete config options:\n";
      echo "(You still have some obsolete or inexistent properties set. This isn't a problem but should be noticed)\n";

      foreach ($messages['obsolete'] as $msg) {
        echo "- " . $msg['prop'] . ($msg['name'] ? ': ' . $msg['name'] : '') . "\n";
        $err++;
      }
      echo "\n";
    }

    // ask user to update config files
    if ($err) {
      echo "Do you want me to fix your local configuration? (y/N)\n";
      $input = trim(fgets(STDIN));

      // positive: let's merge the local config with the defaults
      if (strtolower($input) == 'y') {
        $copy1 = $copy2 = $write1 = $write2 = false;
        
        // backup current config
        echo ". backing up the current config files...\n";
        $copy1 = copy(RCMAIL_CONFIG_DIR . '/main.inc.php', RCMAIL_CONFIG_DIR . '/main.old.php');
        $copy2 = copy(RCMAIL_CONFIG_DIR . '/db.inc.php', RCMAIL_CONFIG_DIR . '/db.old.php');
        
        if ($copy1 && $copy2) {
          $RCI->merge_config();
        
          echo ". writing " . RCMAIL_CONFIG_DIR . "/main.inc.php...\n";
          $write1 = file_put_contents(RCMAIL_CONFIG_DIR . '/main.inc.php', $RCI->create_config('main', true));
          echo ". writing " . RCMAIL_CONFIG_DIR . "/main.db.php...\n";
          $write2 = file_put_contents(RCMAIL_CONFIG_DIR . '/db.inc.php', $RCI->create_config('db', true));
        }
        
        // Success!
        if ($write1 && $write2) {
          echo "Done.\n";
          echo "Your configuration files are now up-tp-date!\n";
        }
        else {
          echo "Failed to write config files!\n";
          echo "Grant write privileges to the current user or update the files manually according to the above messages.\n";
        }
      }
      else {
        echo "Please update your config files manually according to the above messages.\n";
      }
    }

    // check dependencies based on the current configuration
    if (is_array($messages['dependencies'])) {
      echo "WARNING: Dependency check failed!\n";
      echo "(Some of your configuration settings require other options to be configured or additional PHP modules to be installed)\n";

      foreach ($messages['dependencies'] as $msg) {
        echo "- " . $msg['prop'] . ': ' . $msg['explain'] . "\n";
      }
      echo "Please fix your config files and run this script again!\n";
      echo "See ya.\n";
    }

  }
  else {
    echo "This instance of RoundCube is up-to-date.\n";
    echo "Have fun!\n";
  }
}
else {
  echo "This instance of RoundCube is not yet configured!\n";
  echo "Open http://url-to-roundcube/installer/ in your browser and follow the instuctions.\n";
}

echo "\n";

?>