<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$labels['curpasswd']  = 'Current Password:';
$labels['newpasswd']  = 'New Password:';
$labels['confpasswd']  = 'Confirm New Password:';
$messages['nopassword'] = 'Please input new password.';
$messages['nocurpassword'] = 'Please input current password.';
$messages['passwordincorrect'] = 'Current password incorrect.';
$messages['passwordinconsistency'] = 'Passwords do not match, please try again.';
$messages['crypterror'] = 'Could not save new password. Encryption function missing.';
$messages['connecterror'] = 'Could not save new password. Connection error.';
$messages['internalerror'] = 'Could not save new password.';
$messages['passwordshort'] = 'Password must be at least $length characters long.';
$messages['passwordweak'] = 'Password must include at least one number and one punctuation character.';
$messages['passwordforbidden'] = 'Password contains forbidden characters.';
$messages['passwdqc_minlength'] = 'The minimum password length is $N4 characters.';
$messages['passwdqc_maxlength'] = 'The maximum password length is $max characters.';
$messages['passwdqc_4classes'] = 'A password with $N4 to $N3 characters must have at least 4 character classes.';
$messages['passwdqc_3classes'] = 'A password with $N3 to $N1 characters must have at least 3 character classes.';
$messages['passwdqc_2classes'] = 'A password with $N1 to $N0 characters must have at least 2 character classes.';
$messages['passwdqc_1classes'] = 'A password longer than $N0 characters doesn\'t have character class restrictions.';
$messages['passwdqc_passphrase'] = 'You may use a passphrase of at least $passphrase words and $N2 characters without any other restriction.';
$messages['passwdqc_firstupper_lastdigit'] = 'An uppercase at the beggining or a digit at the end of a password don\'t count towards their respective classes.';
