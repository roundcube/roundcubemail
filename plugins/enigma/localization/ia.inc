<?php

/**
 +-----------------------------------------------------------------------+
 | plugins/enigma/localization/<lang>.inc                                |
 |                                                                       |
 | Localization file of the Roundcube Webmail ACL plugin                 |
 | Copyright (C) 2012-2016, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/
$labels['encryption'] = 'Cryptographia';
$labels['enigmacerts'] = 'Certificatos S/MIME';
$labels['enigmakeys'] = 'Claves PGP';
$labels['keysfromto'] = 'Claves $from a $to de $count';
$labels['keyname'] = 'Nomine';
$labels['keyid'] = 'ID de clave';
$labels['keyuserid'] = 'ID de usator';
$labels['keytype'] = 'Typo de clave';
$labels['fingerprint'] = 'Impression digital';
$labels['subkeys'] = 'Subclaves';
$labels['basicinfo'] = 'Information basic';
$labels['userids'] = 'Usatores additional';
$labels['typepublickey'] = 'clave public';
$labels['typekeypair'] = 'par de claves';
$labels['keyattfound'] = 'Iste message contine clave(s) PGP attachate.';
$labels['keyattimport'] = 'Importar clave(s)';
$labels['typesign'] = 'Signar';
$labels['typeencrypt'] = 'Cryptar';
$labels['typecert'] = 'Certificar';
$labels['typeauth'] = 'Authentication';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algorithmo';
$labels['subkeycreated'] = 'Create';
$labels['subkeyexpires'] = 'Expira';
$labels['subkeyusage'] = 'Usage';
$labels['expiresnever'] = 'nunquam';
$labels['unknown'] = 'incognite';
$labels['uservalid'] = 'Valide';
$labels['userid'] = 'ID';
$labels['valid'] = 'valide';
$labels['supportencryption'] = 'Activar le cryptation e signatura de messages';
$labels['supportsignatures'] = 'Activar le verification de signaturas de messages';
$labels['supportdecryption'] = 'Activar le decryptation de messages';
$labels['signdefault'] = 'Signar tote le messages per option predefinite';
$labels['encryptdefault'] = 'Cryptar tote le messages per option predefinite';
$labels['attachpubkeydefault'] = 'Attachar mi clave PGP public per option predefinite';
$labels['passwordtime'] = 'Memorar le contrasignos de claves private durante';
$labels['nminutes'] = '$m minuta(s)';
$labels['wholesession'] = 'tote le session';
$labels['createkeys'] = 'Crear un nove par de claves';
$labels['importkeys'] = 'Importar clave(s)';
$labels['exportkeys'] = 'Exportar clave(s)';
$labels['keyactions'] = 'Actiones de clave...';
$labels['keyremove'] = 'Remover';
$labels['keydisable'] = 'Disactivar';
$labels['keyrevoke'] = 'Revocar';
$labels['keysend'] = 'Inviar le clave pubic in un message';
$labels['keychpass'] = 'Cambiar contrasigno';
$labels['newkeypass'] = 'Contrasigno';
$labels['key2048'] = '2048 bits - predefinite';
$labels['key4096'] = '4096 bits - plus secur';
$labels['keygenerating'] = 'Genera claves...';
$labels['encryptionoptions'] = 'Optiones de cryptation...';
$labels['encryptmsg'] = 'Cryptar iste message';
$labels['signmsg'] = 'Signar digitalmente iste message';
$labels['sendunencrypted'] = 'Inviar sin cryptation';
$labels['enterkeypasstitle'] = 'Entra le contrasigno del clave';
$labels['enterkeypass'] = 'Un contrasigno es necessari pro disblocar le clave secrete ($keyid) pro le usator: $user.';
$labels['arialabelkeyexportoptions'] = 'Optiones de exportation de claves';
$labels['attachpubkeymsg'] = 'Attachar mi clave public';
$labels['keyexportprompt'] = 'Includer le claves secrete in le file de claves OpenPGP a salveguardar?';
$labels['onlypubkeys'] = 'Exportar claves public solmente';
$labels['withprivkeys'] = 'Exportar claves secrete';
$labels['findkey'] = 'Cercar in servitor(es) de claves';
$labels['keyimportlabel'] = 'Importar ab file';
$labels['keyimportsearchlabel'] = 'Cercar in servitor(es) de claves';
$messages['sigvalid'] = 'Signatura de $sender verificate.';
$messages['sigvalidpartial'] = 'Signatura de $sender verificate, ma un parte del texto non ha essite signate.';
$messages['siginvalid'] = 'Signatura de $sender invalide.';
$messages['sigunverified'] = 'Signatura non verificate. Certificato non verificate. ID del certificato: $keyid.';
$messages['signokey'] = 'Signatura non verificate. Clave public non trovate. ID del clave: $keyid.';
$messages['sigerror'] = 'Signatura non verificate. Error interne.';
$messages['decryptok'] = 'Message decryptate.';
$messages['decrypterror'] = 'Decryptation fallite.';
$messages['decryptnokey'] = 'Decryptation fallite. Clave private non trovate. ID del clave: $keyid.';
$messages['decryptbadpass'] = 'Decryptation fallite. Contrasigno non valide.';
$messages['decryptnopass'] = 'Decryptation fallite. Contrasigno del clave requirite.';
$messages['decryptpartial'] = 'Le message ha essite decryptate, ma un parte del corpore non esseva cryptate.';
$messages['signerror'] = 'Signatura fallite.';
$messages['signnokey'] = 'Signatura fallite. Clave private non trovate.';
$messages['signbadpass'] = 'Signatura fallite. Contrasigno non valide.';
$messages['signnopass'] = 'Signatura fallite. Contrasigno del clave requirite.';
$messages['encrypterror'] = 'Cryptation fallite.';
$messages['encryptnokey'] = 'Cryptation fallite. Clave public non trovate pro $email.';
$messages['nokeysfound'] = 'Nulle clave trovate';
$messages['keynotfound'] = 'Clave non trovate!';
$messages['keyopenerror'] = 'Incapace de obtener information del clave! Error interne.';
$messages['keylisterror'] = 'Incapace de listar claves! Error interne.';
$messages['keysimportfailed'] = 'Incapace de importar clave(s)! Error interne.';
$messages['keysimportsuccess'] = 'Importation de clave(s) succedite. Importate: $new, non cambiate: $old.';
$messages['keyremoving'] = 'A remover clave(s)...';
$messages['keyremoveconfirm'] = 'Es vos secur de voler deler le clave(s) seligite?';
$messages['keyremovesuccess'] = 'Deletion del clave(s) succedite';
$messages['keyremoveerror'] = 'Incapace de deler le clave(s) seligite.';
$messages['keyimporttext'] = 'Es possibile importar clave(s) private e public o signaturas de revocation in formato "ASCII Armor".';
$messages['keyimportsearchtext'] = 'Es possibile cercar claves public per identificator, nomine de usator o adresse de e-mail e postea importar los directemente.';
$messages['formerror'] = 'Per favor, completa le formularios. Tote le campos es obligatori!';
$messages['passwordsdiffer'] = 'Le contrasignos non corresponde!';
$messages['keygenerateerror'] = 'Generation de par de claves fallite';
$messages['keygeneratesuccess'] = 'Un par de claves ha essite generate e importate con successo.';
$messages['keygennosupport'] = 'Vostre navigator del web non supporta le cryptographia. Incapace de generar un par de claves!';
$messages['noidentselected'] = 'Es necessari seliger al minus un identitate pro le clave!';
$messages['nonameident'] = 'Le identitate debe includer un nomine de usator!';
?>
