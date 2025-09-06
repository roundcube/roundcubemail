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

// Hestia driver messages.
$labels['hestia_err_policy_short']   = 'Password too short (minimum length: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia endpoint is not configured or invalid.';
$labels['hestia_err_identity']       = 'Cannot parse username into local@domain%hint.';
$labels['hestia_err_owner_unknown']  = 'Unable to determine domain owner for %domain.';
$labels['hestia_err_unknown']        = 'Unknown error from Hestia API.';
$labels['hestia_err_default']        = 'Hestia returned error code %code.';

// Hestia/Vesta return codes
$labels['hestia_err_1']  = 'Not enough arguments provided.';
$labels['hestia_err_2']  = 'Invalid object or argument.';
$labels['hestia_err_3']  = 'Object does not exist (mailbox or domain not found).';
$labels['hestia_err_4']  = 'Object already exists.';
$labels['hestia_err_5']  = 'Object is already suspended.';
$labels['hestia_err_6']  = 'Object is already unsuspended.';
$labels['hestia_err_7']  = 'Object in use; cannot modify.';
$labels['hestia_err_8']  = 'Operation blocked by package limits.';
$labels['hestia_err_9']  = 'Wrong or invalid password.';
$labels['hestia_err_10'] = 'Permission denied by Hestia (API identity lacks rights or IP not allowed).';
$labels['hestia_err_11'] = 'Feature disabled by system policy.';
$labels['hestia_err_12'] = 'Parsing error.';
$labels['hestia_err_13'] = 'Disk error.';
$labels['hestia_err_14'] = 'Server load too high.';
$labels['hestia_err_15'] = 'Connection error.';
$labels['hestia_err_16'] = 'FTP error.';
$labels['hestia_err_17'] = 'Database error.';
$labels['hestia_err_18'] = 'RRD error.';
$labels['hestia_err_19'] = 'Update error.';
$labels['hestia_err_20'] = 'Restart error.';
