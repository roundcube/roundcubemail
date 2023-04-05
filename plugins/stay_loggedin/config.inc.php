<?php
#
#     Stay-Logged-In plugin for the Roundcube Elastic skin.
#
#     Set the number of days users will stay logged in, if they turn on the toggle switch.
#     The maximum number of days that can be set is 365 (1 year).
#     Setting to '0' will disable plugin and remove toggle switch from the login page.
#

$config['stay_loggedin_days'] = 7;

#
#     Don't forget to add plugin to config/config.inc.php
#     $config['plugins'] = [
#         ...
#         'stay_loggedin',
#         ...
#     ];
#
#     Notice; This plugin will over-ride $config['session_lifetime']
#     (which sets the session garbage collection max lifetime in PHP)
#     to match the number of days set above.
#
