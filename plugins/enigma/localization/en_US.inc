<?php

$labels = array();
$labels['enigmasettings'] = 'Enigma: Settings';
$labels['enigmacerts'] = 'Enigma: Certificates (S/MIME)';
$labels['enigmakeys'] = 'Enigma: Keys (PGP)';
$labels['keysfromto'] = 'Keys $from to $to of $count';
$labels['keyname'] = 'Name';
$labels['keyid'] = 'Key ID';
$labels['keyuserid'] = 'User ID';
$labels['keytype'] = 'Key type';
$labels['fingerprint'] = 'Fingerprint';
$labels['subkeys'] = 'Subkeys';
$labels['basicinfo'] = 'Basic Information';
$labels['userids'] = 'Additional User IDs';
$labels['typepublickey'] = 'public key';
$labels['typekeypair'] = 'key pair';
$labels['keyattfound'] = 'This message contains attached PGP key(s).';
$labels['keyattimport'] = 'Import key(s)';

$labels['createkeys'] = 'Create a new key pair';
$labels['importkeys'] = 'Import key(s)';
$labels['exportkeys'] = 'Export key(s)';
$labels['deletekeys'] = 'Delete key(s)';
$labels['keyactions'] = 'Key actions...';
$labels['keydisable'] = 'Disable key';
$labels['keyrevoke'] = 'Revoke key';
$labels['keysend'] = 'Send public key in a message';
$labels['keychpass'] = 'Change password';

$labels['securityoptions'] = 'Message security options...';
$labels['identdefault'] = 'Use settings of selected identity';
$labels['encryptmsg'] = 'Encrypt this message';
$labels['signmsg'] = 'Digitally sign this message';

$messages = array();
$messages['sigvalid'] = 'Verified signature from $sender.';
$messages['siginvalid'] = 'Invalid signature from $sender.';
$messages['signokey'] = 'Unverified signature. Public key not found. Key ID: $keyid.';
$messages['sigerror'] = 'Unverified signature. Internal error.';
$messages['decryptok'] = 'Message decrypted.';
$messages['decrypterror'] = 'Decryption failed.';
$messages['decryptnokey'] = 'Decryption failed. Private key not found. Key ID: $keyid.';
$messages['decryptbadpass'] = 'Decryption failed. Bad password.';
$messages['nokeysfound'] = 'No keys found';
$messages['keyopenerror'] = 'Unable to get key information! Internal error.';
$messages['keylisterror'] = 'Unable to list keys! Internal error.';
$messages['keysimportfailed'] = 'Unable to import key(s)! Internal error.';
$messages['keysimportsuccess'] = 'Key(s) imported successfully. Imported: $new, unchanged: $old.';
$messages['keyconfirmdelete'] = 'Are you sure, you want to delete selected key(s)?';
$messages['keyimporttext'] = 'You can import private and public key(s) or revocation signatures in ASCII-Armor format.';

?>
