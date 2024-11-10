<?php

/*
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

$labels['encryption'] = 'Cifraxe';
$labels['enigmacerts'] = 'Certificados S/MIME';
$labels['enigmakeys'] = 'Claves PGP';
$labels['keysfromto'] = 'Claves $from para $to de $count';
$labels['keyname'] = 'Nome';
$labels['keyid'] = 'Clave de identificación';
$labels['keyuserid'] = 'Identificación do usuario';
$labels['keytype'] = 'Tipo de clave';
$labels['fingerprint'] = 'Impresión dixital';
$labels['subkeys'] = 'Subclaves';
$labels['keyprops'] = 'Propiedades das claves';
$labels['basicinfo'] = 'Información básica';
$labels['userids'] = 'Usuarios adicionais';
$labels['typepublickey'] = 'clave pública';
$labels['typekeypair'] = 'par de claves';
$labels['keyattfound'] = 'Esta mensaxe contén clave(s) PGP anexa(s).';
$labels['keyattimport'] = 'Importar clave(s)';
$labels['typesign'] = 'Asinar';
$labels['typeencrypt'] = 'Cifrar';
$labels['typecert'] = 'Certificar';
$labels['typeauth'] = 'Autenticación';
$labels['subkeyid'] = 'Identificación';
$labels['subkeyalgo'] = 'Algoritmo';
$labels['subkeycreated'] = 'Creado';
$labels['subkeyexpires'] = 'Caduca';
$labels['subkeyusage'] = 'Uso';
$labels['expiresnever'] = 'nunca';
$labels['unknown'] = 'descoñecido';
$labels['uservalid'] = 'Válido';
$labels['userid'] = 'Identificación';
$labels['valid'] = 'válido';
$labels['supportencryption'] = 'Activar a cifraxe e a sinatura de mensaxes';
$labels['supportsignatures'] = 'Activar a verificación das sinaturas das mensaxes';
$labels['supportdecryption'] = 'Activar a descifraxe das mensaxes';
$labels['signdefault'] = 'Asinar todas as mensaxes por defecto';
$labels['encryptdefault'] = 'Cifrar todas as mensaxes por defecto';
$labels['attachpubkeydefault'] = 'Anexar a miña clave PGP pública por defecto';
$labels['passwordtime'] = 'Manter os contrasinais de clave privada durante';
$labels['nminutes'] = '$m minuto(s)';
$labels['wholesession'] = 'toda a sesión';
$labels['createkeys'] = 'Crear un novo par de claves';
$labels['importkeys'] = 'Importar clave(s)';
$labels['exportkeys'] = 'Exportar clave(s)';
$labels['keyactions'] = 'Accións das claves...';
$labels['keyremove'] = 'Eliminar';
$labels['keydisable'] = 'Desactivar';
$labels['keyrevoke'] = 'Cancelar';
$labels['keysend'] = 'Enviar clave pública nunha mensaxe';
$labels['keychpass'] = 'Cambiar o contrasinal';
$labels['keyadd'] = 'Engadir unha clave';
$labels['newkeyident'] = 'Identidade';
$labels['newkeypass'] = 'Contrasinal';
$labels['newkeypassconfirm'] = 'Confirmar o contrasinal';
$labels['newkeytype'] = 'Tipo de clave';
$labels['rsa2048'] = 'RSA - 2048 bits';
$labels['rsa4096'] = 'RSA - 4096 bits';
$labels['ecckeypair'] = 'ECC (Criptografía de Curva Elíptica)';
$labels['keygenerating'] = 'Xerando claves...';
$labels['encryptionoptions'] = 'Opcións de cifraxe...';
$labels['encryptmsg'] = 'Cifrar esta mensaxe';
$labels['signmsg'] = 'Firmar esta mensaxe dixitalmente';
$labels['sendunencrypted'] = 'Enviar sen cifrar';
$labels['enterkeypasstitle'] = 'Introducir o contrasinal da clave';
$labels['enterkeypass'] = 'É preciso un contrasinal para desbloquear a clave secreta ($keyid) para o usuario: $user.';
$labels['attachpubkeymsg'] = 'Anexar a miña clave pública';
$labels['keyexportprompt'] = 'Queres incluír claves secretas no arquivo de claves OpenPGP gardado?';
$labels['onlypubkeys'] = 'Exportar só claves públicas';
$labels['withprivkeys'] = 'Exportar claves secretas';
$labels['findkey'] = 'Busca no(s) servidor(es) de claves';
$labels['keyimportlabel'] = 'Importar desde arquivo';
$labels['keyimportsearchlabel'] = 'Busca no(s) servidor(es) de claves';
$labels['managekeys'] = 'Xestionar claves PGP';
$labels['identitymatchingprivkeys'] = 'Tes $nr claves PGP privadas coincidentes almacenadas no teu almacén de claves:';
$labels['identitynoprivkeys'] = 'Esta identidade de remitente aínda non ten unha clave PGP privada almacenada no teu almacén de claves.';
$labels['arialabelkeyexportoptions'] = 'Opcións de exportación de claves';
$labels['arialabelkeysearchform'] = 'Formulario de busca de claves';
$labels['arialabelkeyoptions'] = 'Opcións de claves';
$messages['sigvalid'] = 'Sinatura de $sender verificada.';
$messages['sigvalidpartial'] = 'Sinatura de $sender verificada, pero parte do corpo non está asinado.';
$messages['siginvalid'] = 'Sinatura de $sender non válida.';
$messages['sigunverified'] = 'Sinatura sen verificar. Certificado non verificado. Certificado de identificación: $keyid.';
$messages['signokey'] = 'Sinatura sen verificar. Non se atopou unha clave pública. Clave de identificación: $keyid.';
$messages['sigerror'] = 'Sinatura sen verificar. Erro interno.';
$messages['decryptok'] = 'Mensaxe descifrada.';
$messages['decrypterror'] = 'Erro ao descifrar.';
$messages['decryptnokey'] = 'Erro ao descifrar. Non se atopou unha clave privada. Clave de identificación: $keyid.';
$messages['decryptnomdc'] = 'Descifraxe omitida. A integridade da mensaxe non está protexida.';
$messages['decryptbadpass'] = 'Erro ao descifrar. Contrasinal non válido.';
$messages['decryptnopass'] = 'Erro ao descifrar. Requírese o contrasinal da clave.';
$messages['decryptpartial'] = 'Mensaxe descifrada, pero parte do corpo non estaba cifrada.';
$messages['signerror'] = 'Erro ao asinar.';
$messages['signnokey'] = 'Erro ao asinar. Non se atopou unha clave privada.';
$messages['signbadpass'] = 'Erro ao asinar. Contrasinal non válido.';
$messages['signnopass'] = 'Erro ao asinar. Requírese o contrasinal da clave.';
$messages['encrypterror'] = 'Erro na cifraxe.';
$messages['encryptnokey'] = 'Erro na cifraxe. Non se atopou clave pública para $email.';
$messages['encryptnoprivkey'] = 'Erro na cifraxe. Non se atopou clave privada.';
$messages['nokeysfound'] = 'Non se atopou ningunha clave';
$messages['keynotfound'] = 'Non se atopou a clave!';
$messages['keyopenerror'] = 'Non é posible obter a información da clave! Erro interno.';
$messages['keylisterror'] = 'Non é posible amosar as claves! Erro interno.';
$messages['keysimportfailed'] = 'Non é posible importar a(s) clave(s)! Erro interno.';
$messages['keysimportsuccess'] = 'Clave(s) importada(s) correctamente. Importada: $new, sen cambios: $old.';
$messages['keyremoving'] = 'Eliminando clave(s)...';
$messages['keyremoveconfirm'] = 'Seguro/a que queres eliminar a(s) clave(s) seleccionada(s)?';
$messages['keyremovesuccess'] = 'Clave(s) eliminada(s) correctamente.';
$messages['keyremoveerror'] = 'Non é posible eliminar a(s) clave(s) seleccionada(s).';
$messages['keyimporttext'] = 'Podes importar claves privadas e públicas ou sinaturas de revogación en formato ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Podes buscar claves públicas co identificador de claves, co nome de usuario ou co enderezo electrónico e despois importalas directamente.';
$messages['keystoragenotice'] = 'Todas as claves públicas e privadas se almacenan no servidor.';
$messages['formerror'] = 'Por favor, cubre o formulario. Todos os campos son obrigatorios!';
$messages['passwordsdiffer'] = 'Os contrasinais non coinciden!';
$messages['keygenerateerror'] = 'Erro ao xerar un par de claves';
$messages['keygeneratesuccess'] = 'Par de claves xerado e importado correctamente.';
$messages['keygennosupport'] = 'O teu navegador non acepta criptografía. Non é posible xerar un par de claves!';
$messages['noidentselected'] = 'Tes que seleccionar polo menos unha identidade para a clave!';
$messages['nonameident'] = 'A identidade debe ter un nome de usuario definido!';
$labels['newkeysize'] = 'Tamaño da clave';
$labels['key2048'] = '2048 bits - por defecto';
$labels['key4096'] = '4096 bits - maior seguridade';
