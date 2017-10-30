<?php

/*
 +-----------------------------------------------------------------------+
 | plugins/password/localization/<lang>.inc                              |
 |                                                                       |
 | Localization file of the Roundcube Webmail Password plugin            |
 | Copyright (C) 2012-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/
$labels['changepasswd']  = 'Change password';
$labels['curpasswd']  = 'Current Password:';
$labels['newpasswd']  = 'New Password:';
$labels['confpasswd']  = 'Confirm New Password:';
$messages['nopassword'] = 'Please enter a new password.';
$messages['nocurpassword'] = 'Please enter the current password.';
$messages['passwordincorrect'] = 'Current password is incorrect.';
$messages['passwordinconsistency'] = 'Passwords do not match.  Please try again.';
$messages['crypterror'] = 'New password could not be saved.  The encryption function is missing.';
$messages['connecterror'] = 'New password could not be saved.  There is a connection error.';
$messages['internalerror'] = 'New password could not be saved.';
$messages['passwordshort'] = 'Password must be at least $length characters long.';
$messages['passwordweak'] = 'Password must include at least one number and one symbol.';
$messages['passwordforbidden'] = 'Password contains forbidden characters.';
$messages['firstloginchange'] = 'This is your first login. Please change your password.';
$messages['disablednotice'] = 'The system is currently under maintenance and password change is not possible at the moment. Everything should be back to normal soon. We apologise for any inconvenience.';
$messages['passwinhistory'] = 'This password has already been used previously.';
$messages['samepasswd'] = 'The new password has to be different from the old one.';
$messages['passwdexpirewarning'] = 'Warning! Your password will expire soon, change it before $expirationdatetime.';
$messages['passwdexpired'] = 'Your password has expired, you have to change it now!';
$messages['passwdconstraintviolation'] = 'Password constraint violation. Password probably too weak.';
