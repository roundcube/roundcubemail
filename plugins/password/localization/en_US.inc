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

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'Change password';
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
$messages['passwordtooweak'] = 'Password too weak.';
$messages['passwordnoseq'] = 'Password should not be a sequence like 123456 or QWERTY.';
$messages['passwordnocommon'] = 'Password should not be a common word or name.';
$messages['passwordforbidden'] = 'Password contains forbidden characters.';
$messages['firstloginchange'] = 'This is your first login. Please change your password.';
$messages['disablednotice'] = 'The system is currently under maintenance and password change is not possible at the moment. Everything should be back to normal soon. We apologize for any inconvenience.';
$messages['passwinhistory'] = 'This password has already been used previously.';
$messages['samepasswd'] = 'New password have to be different from the old one.';
$messages['passwdexpirewarning'] = 'Warning! Your password will expire soon, change it before $expirationdatetime.';
$messages['passwdexpired'] = 'Your password has expired, you have to change it now!';
$messages['passwdconstraintviolation'] = 'Password constraint violation. Password probably too weak.';
$messages['pwned_mustnotbedisclosed'] = 'Password must not be <a href="$href" target="_blank">commonly known</a>.';
$messages['pwned_isdisclosed'] = 'This password is commonly known.';
$messages['pwned_fetcherror'] = 'Failed to verify the password strength.';
$messages['passwdqc_minlength'] = 'The minimum password length is $N4 characters.';
$messages['passwdqc_maxlength'] = 'The maximum password length is $max characters.';
$messages['passwdqc_4classes'] = 'A password with $N4 to $N3 characters must have at least 4 character classes.';
$messages['passwdqc_3classes'] = 'A password with $N3 to $N1 characters must have at least 3 character classes.';
$messages['passwdqc_2classes'] = 'A password with $N1 to $N0 characters must have at least 2 character classes.';
$messages['passwdqc_1classes'] = 'A password longer than $N0 characters doesn\'t have character class restrictions.';
$messages['passwdqc_passphrase'] = 'You may use a passphrase of at least $passphrase words and $N2 characters without any other restriction.';
$messages['passwdqc_firstupper_lastdigit'] = 'An uppercase at the beggining or a digit at the end of a password don\'t count towards their respective classes.';
