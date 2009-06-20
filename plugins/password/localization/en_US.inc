<?php

$labels = array();
$labels['changepasswd']  = 'Change Password';
$labels['curpasswd']  = 'Current Password:';
$labels['newpasswd']  = 'New Password:';
$labels['confpasswd']  = 'Confirm New Password:';

$messages = array();
$messages['nopassword'] = 'Please input new password.';
$messages['nocurpassword'] = 'Please input current password.';
$messages['passwordincorrect'] = 'Current password incorrect.';
$messages['passwordinconsistency'] = 'Passwords do not match, please try again.';
$messages['nocryptfunction'] = 'The server is missing a function to encrypt your password. Contact your system adminstrator.';
$messages['internalerror'] = 'Could not save new password. Contact your system adminstrator.';

?>
