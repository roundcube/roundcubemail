<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail DKIM display plugin        |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$labels['dkimstatus'] = 'DKIM: ';
$labels['dkimpassaligned'] = 'Signature is valid and matches the sender\'s domain ($domain).';
$labels['dkimpassunaligned'] = 'Signature is valid but was signed by "$domain", which does not match the From address ("$from"). The sender may be impersonated.';
$labels['dkimfail'] = 'Signature verification failed (result: $result). The sender may be forged.';
$labels['dkimunverified'] = 'A DKIM signature is present (d=$domain), but your mail server did not verify it.';
$labels['dkimunverifiedunaligned'] = 'A DKIM signature is present (d=$domain), but it was not verified by your mail server and does not match the From address ("$from").';
$labels['dkimnone'] = 'This message has no DKIM signature — its sender cannot be verified.';
