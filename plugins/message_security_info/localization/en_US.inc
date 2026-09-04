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
$labels['from'] = 'From';
$labels['spf'] = 'SPF';
$labels['dkim'] = 'DKIM';
$labels['dmarc'] = 'DMARC';
$labels['notpresent'] = 'not present';
$labels['aligned'] = 'aligned with From';
$labels['notaligned'] = 'does not match From ($from)';
$labels['unverified'] = 'present, not verified by your server';
$labels['spfrelayed'] = 'DMARC accepted the message anyway — it was probably forwarded';

// Verdict glyph beside each SPF / DKIM / DMARC row in the popup (tooltip)
$labels['verdictpass'] = 'Passed';
$labels['verdictwarn'] = 'Needs attention';
$labels['verdictfail'] = 'Failed';
$labels['verdictunknown'] = 'Not verified by your server';
$labels['verdictnone'] = 'No result reported';

// DKIM marker on the message's From header (tooltip). One sentence per value
// the DKIM verdict can take, so the marker always says exactly what its glyph
// means.
$labels['frommarkerpass'] = 'DKIM: signed and matches the sender address.';
$labels['frommarkerwarn'] = 'DKIM: signed, but the signature does not match the sender address.';
$labels['frommarkerfail'] = 'DKIM: signature verification failed.';
$labels['frommarkerunknown'] = 'DKIM: signed, but your server did not verify the signature.';
$labels['frommarkernone'] = 'DKIM: this message is not signed.';

// Transport encryption (informational; does not affect the icon)
$labels['tls'] = 'Transport (TLS)';
$labels['tlsencrypted'] = 'Encrypted';
$labels['tlsplain'] = 'Not encrypted';
$labels['tlsunknown'] = 'unknown';

// Overall verdict summary (link tooltip / notice bar). The worst of the
// per-mechanism verdicts.
$labels['summarypass'] = 'Sender authentication passed.';
$labels['summarywarn'] = 'Sender authentication is incomplete or not aligned — click for details.';
$labels['summaryfail'] = 'Sender authentication failed — the sender may be forged.';
$labels['summaryunknown'] = 'Sender authentication could not be verified.';
