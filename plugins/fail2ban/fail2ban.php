<?php
/**
 * RoundCube Fail2Ban Plugin
 *
 * @version 1.1
 * @author Matt Rude [m@mattrude.com]
 * @url http://mattrude.com/plugins/roundcube-fail2ban-plugin/
 * @license GPLv3
 */
class fail2ban extends rcube_plugin
{
  function init()
  {
    $this->add_hook('login_failed', array($this, 'log'));
  }

  function log($args)
  {
    $log_entry = '[roundcube] FAILED login for ' .$args['user']. ' from ' .getenv('REMOTE_ADDR');
    $log_config = rcmail::get_instance()->config->get('log_driver');
    $log_dir = rcmail::get_instance()->config->get('log_dir');

    if ($log_config == 'syslog'){
       syslog(LOG_WARNING, $log_entry);
    } elseif ($log_config == 'file'){
       error_log('['.date('d-M-Y H:i:s O')."]: ".$log_entry."\n", 3, $log_dir."/userlogins");
    } else {
       echo 'WARNING!! The RoundCube Fail2Ban Plugin was unable to retrieve the log driver from the config, please check your config file for log_driver.';
    }
  }

}

?>
