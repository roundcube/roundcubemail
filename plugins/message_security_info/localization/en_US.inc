<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Message security info     |
 | plugin                                                                |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+
*/

$labels['linktitle'] = 'Message Security';

// Settings section
$labels['securitysection'] = 'Message security';
$labels['extraheaders'] = 'Extra headers in the details popup';
$labels['extraheadershint'] = 'One header name per line (e.g. X-Spam-Status). Headers not present on a message are omitted.';

// Authentication-results popup
$labels['authresults'] = 'Authentication results';
$labels['spf'] = 'SPF';
$labels['dkim'] = 'DKIM';
$labels['dmarc'] = 'DMARC';
$labels['notpresent'] = 'not present';
$labels['aligned'] = 'aligned with From';
$labels['notaligned'] = 'does not match From ($from)';
$labels['unverified'] = 'present, not verified by your server';

// Transport encryption (informational; does not affect the icon)
$labels['tls'] = 'Transport (TLS)';
$labels['tlsencrypted'] = 'Encrypted';
$labels['tlsplain'] = 'Not encrypted';
$labels['tlsunknown'] = 'unknown';

// Overall verdict summary (link tooltip / notice bar). Reflects DMARC when
// available, otherwise the combined SPF + DKIM result.
$labels['summarypass'] = 'Sender authentication passed.';
$labels['summarywarn'] = 'Sender authentication is incomplete or not aligned — click for details.';
$labels['summaryfail'] = 'Sender authentication failed — the sender may be forged.';
$labels['summaryunknown'] = 'Sender authentication could not be verified.';
