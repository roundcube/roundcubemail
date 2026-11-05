<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Passkey login plugin       |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$labels['next'] = 'Next';
$labels['changeuser'] = 'Not you? Change';
$labels['usepassword'] = 'Use password instead';
$labels['usepasskey'] = 'Use a passkey instead';
$labels['signinpasskey'] = 'Sign in with a passkey';
$labels['enrolllabel'] = 'Set up a passkey on this device';
$labels['checking'] = 'Checking…';
$labels['passkeyprompt'] = 'Follow your device prompt to sign in…';
$labels['passkeyok'] = 'Signing in…';
$labels['passkeyfailed'] = 'Passkey sign-in failed. Please use your password.';
$labels['enrolling'] = 'Setting up your passkey…';
$labels['enrollfailed'] = 'Could not set up a passkey on this device. You can still sign in with your password.';
$labels['prfunsupported'] = 'This browser or device does not support the passkey encryption feature (WebAuthn PRF) this requires. Try a different browser, or sign in with your password.';

// Settings: passkey management section
$labels['passkeys'] = 'Passkeys';
$labels['passkeysnone'] = 'You have no passkeys yet.';
$labels['passkeysdescription'] = 'Description';
$labels['passkeyscreated'] = 'Added';
$labels['passkeysdelete'] = 'Delete';
$labels['passkeysdeleteconfirm'] = 'Delete this passkey? You will no longer be able to sign in with it.';
$labels['passkeysadd'] = 'Add a passkey';
$labels['passkeysaddtitle'] = 'Add a passkey';
$labels['passkeyspassword'] = 'Your account password';
$labels['passkeyspasswordhint'] = 'Confirm your account password so this device can encrypt it for the new passkey. It is checked, never stored in the clear.';
$labels['passkeyssaved'] = 'Passkey updated.';
$labels['passkeyssavefailed'] = 'Could not save the change.';
$labels['passkeysaddfailed'] = 'Could not add the passkey.';
$labels['passkeysbadpassword'] = 'That password is not correct.';

// Default description derived from the enrolling device
$labels['passkeyon'] = '$browser on $os';
$labels['passkeydefaultname'] = 'Passkey';
