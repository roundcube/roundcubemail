<?php

/**
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Enigma plugin              |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/roundcube/roundcube-webmail/plugin-enigma/
*/

$labels['encryption'] = 'Encriptació';
$labels['enigmacerts'] = 'Certificats S/MIME';
$labels['enigmakeys'] = 'Claus PGP';
$labels['keysfromto'] = 'Claus $from a $to de $count';
$labels['keyname'] = 'Nom';
$labels['keyid'] = 'ID de la clau';
$labels['keyuserid'] = 'ID de l\'usuari';
$labels['keytype'] = 'Tipus de clau';
$labels['fingerprint'] = 'Empremta digital';
$labels['subkeys'] = 'Subclaus';
$labels['keyprops'] = 'Propietats de la clau';
$labels['basicinfo'] = 'Informació bàsica';
$labels['userids'] = 'Usuaris addicionals';
$labels['typepublickey'] = 'clau pública';
$labels['typekeypair'] = 'parella de claus';
$labels['keyattfound'] = 'Aquest missatge conté clau(s) PGP adjunt(s).';
$labels['keyattimport'] = 'Importa clau(s)';
$labels['typesign'] = 'Signa';
$labels['typeencrypt'] = 'Xifra';
$labels['typecert'] = 'Certifica';
$labels['typeauth'] = 'Autenticació';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algorisme';
$labels['subkeycreated'] = 'Creat';
$labels['subkeyexpires'] = 'Caduca';
$labels['subkeyusage'] = 'Ús';
$labels['expiresnever'] = 'mai';
$labels['unknown'] = 'desconegut';
$labels['uservalid'] = 'Vàlid';
$labels['userid'] = 'ID';
$labels['valid'] = 'vàlid';
$labels['supportencryption'] = 'Activa l\'encriptació i signat del missatge';
$labels['supportsignatures'] = 'Activa la verificació de les signatures del missatge';
$labels['supportdecryption'] = 'Activa la desencriptació del missatge';
$labels['signdefault'] = 'Signa tots els missatges per defecte';
$labels['encryptdefault'] = 'Xifra tots els missatges per defecte';
$labels['attachpubkeydefault'] = 'Adjunta per defecte la meva clau pública PGP';
$labels['passwordtime'] = 'Conserva les contrasenyes de les claus privades per';
$labels['nminutes'] = '$m minut(s)';
$labels['wholesession'] = 'a tota la sessió';
$labels['createkeys'] = 'Crea un nou parell de claus';
$labels['importkeys'] = 'Importa clau(s)';
$labels['exportkeys'] = 'Exporta clau(s)';
$labels['keyactions'] = 'Accions de les claus...';
$labels['keyremove'] = 'Suprimeix';
$labels['keydisable'] = 'Desactiva';
$labels['keyrevoke'] = 'Revoca';
$labels['keysend'] = 'Envia la clau pública en un missatge';
$labels['keychpass'] = 'Canvia la contrasenya';
$labels['keyadd'] = 'Afegeix una clau';
$labels['newkeyident'] = 'Identitat';
$labels['newkeypass'] = 'Contrasenya';
$labels['newkeypassconfirm'] = 'Confirma la contrasenya';
$labels['newkeytype'] = 'Tipus de clau';
$labels['rsa2048'] = 'RSA - 2048 bits';
$labels['rsa4096'] = 'RSA - 4096 bits';
$labels['ecckeypair'] = 'ECC (Corba El·líptica)';
$labels['keygenerating'] = 'S\'estan generant les claus...';
$labels['encryptionoptions'] = 'Opcions d\'encriptació...';
$labels['encryptmsg'] = 'Encripta aquest missatge';
$labels['signmsg'] = 'Signa digitalment aquest missatge';
$labels['sendunencrypted'] = 'Envia-ho desxifrat';
$labels['enterkeypasstitle'] = 'Introduïu la contrasenya de la clau';
$labels['enterkeypass'] = 'Cal una contrasenya per desblocar la clau secreta ($keyid) per l\'usuari: $user.';
$labels['attachpubkeymsg'] = 'Adjunta la meva clau pública';
$labels['keyexportprompt'] = 'Voleu incloure claus secretes al fitxer de claus OpenPGP desat?';
$labels['onlypubkeys'] = 'Exporta només claus públiques';
$labels['withprivkeys'] = 'Exporta claus secretes';
$labels['findkey'] = 'Cerca al(s) servidor(s) de claus';
$labels['keyimportlabel'] = 'Importa des de fitxer';
$labels['keyimportsearchlabel'] = 'Cerca al(s) servidor(s) de claus';
$labels['managekeys'] = 'Gestiona les claus PGP';
$labels['identitymatchingprivkeys'] = 'Teniu $nr claus privades PGP coincidents emmagatzemades al vostre anell de claus:';
$labels['identitynoprivkeys'] = 'Aquesta identitat del remitent encara no té cap clau privada PGP emmagatzemada al vostre anell de claus.';
$labels['arialabelkeyexportoptions'] = 'Opcions d’exportació de claus';
$labels['arialabelkeysearchform'] = 'Formulari de cerca de claus';
$labels['arialabelkeyoptions'] = 'Opcions de clau';
$messages['sigvalid'] = 'Signatura verificada des de $sender.';
$messages['sigvalidpartial'] = 'Signatura verificada des de $sender, però no s\'ha signat una part del cos.';
$messages['siginvalid'] = 'Signatura no vàlida des de $sender.';
$messages['sigunverified'] = 'Signatura no verificada. No s\'ha verificat el certificat. Identificador del certificat: $keyid.';
$messages['signokey'] = 'Signatura no verificada. No s\'ha trobat la clau pública. Identificador de clau: $keyid.';
$messages['sigerror'] = 'Signatura no verificada. Error intern.';
$messages['decryptok'] = 'Missatge desxifrat.';
$messages['decrypterror'] = 'Ha fallat el desxifratge.';
$messages['decryptnokey'] = 'Ha fallat el desxifratge. No s\'ha trobat la clau privada. Identificador de clau: $keyid.';
$messages['decryptnomdc'] = 'S\'ha omès el desxifratge. El missatge no està íntegrament protegit.';
$messages['decryptbadpass'] = 'Ha fallat el desxifratge. Contrasenya no vàlida.';
$messages['decryptnopass'] = 'Ha fallat el desxifratge. Es requereix una contrasenya clau.';
$messages['decryptpartial'] = 'El missatge s\'ha desxifrat, però una part del cos no estava encriptada.';
$messages['signerror'] = 'No s\'ha pogut signar.';
$messages['signnokey'] = 'No s\'ha pogut signar. No s\'ha trobat la clau privada.';
$messages['signbadpass'] = 'No s\'ha pogut signar. Contrasenya no vàlida.';
$messages['signnopass'] = 'No s\'ha pogut signar. Es requereix una contrasenya clau.';
$messages['encrypterror'] = 'Ha fallat el xifratge.';
$messages['encryptnokey'] = 'Ha fallat el xifratge. No s\'ha trobat la clau pública per a $email.';
$messages['encryptnoprivkey'] = 'Ha fallat el xifratge. No s\'ha trobat la clau privada.';
$messages['nokeysfound'] = 'No s\'ha trobat cap clau';
$messages['keynotfound'] = 'No s\'ha trobat la clau!';
$messages['keyopenerror'] = 'No es pot obtenir la informació clau!. Error intern.';
$messages['keylisterror'] = 'No es poden mostrar les claus!. Error intern.';
$messages['keysimportfailed'] = 'No es poden importar les claus!. Error intern.';
$messages['keysimportsuccess'] = 'Clau(s) importada/es correctament. Importades: $nou, sense canvis: $antic.';
$messages['keyremoving'] = 'S\'estan suprimint la/es clau(s)...';
$messages['keyremoveconfirm'] = 'Esteu segur de voler suprimir la/es clau(s) seleccionada/es?';
$messages['keyremovesuccess'] = 'La/es clau(s) s\'ha(n) suprimit correctament';
$messages['keyremoveerror'] = 'No es poden suprimir les claus seleccionades.';
$messages['keyimporttext'] = 'Podeu importar claus públiques i privades o signatures de revocació en format ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Podeu cercar claus públiques per l\'identificador de la clau, nom d’usuari o adreça de correu electrònic i després importar-les directament.';
$messages['keystoragenotice'] = 'Totes les claus públiques i privades s’emmagatzemen al servidor.';
$messages['formerror'] = 'Si us plau ompliu el formulari. Tots els camps són obligatoris!';
$messages['passwordsdiffer'] = 'Les contrasenyes no coincideixen!';
$messages['keygenerateerror'] = 'No s\'ha pogut generar un parell de claus';
$messages['keygeneratesuccess'] = 'S\'ha generat i importat correctament un parell de claus.';
$messages['keygennosupport'] = 'El vostre navegador web no admet la criptografia. No es pot generar un parell de claus.';
$messages['noidentselected'] = 'Heu de seleccionar almenys una identitat per a la clau!';
$messages['nonameident'] = 'La identitat ha de tenir un nom d\'usuari definit!';
$labels['newkeysize'] = 'Mida de la clau';
$labels['key2048'] = '2048 bits - per defecte';
$labels['key4096'] = '4096 bits - més segur';
