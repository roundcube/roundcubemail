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

$labels['encryption'] = 'Encriptación';
$labels['enigmacerts'] = 'Certificados S/MIME';
$labels['enigmakeys'] = 'Claves PGP';
$labels['keysfromto'] = 'Claves $from a $to de $count';
$labels['keyname'] = 'Nombre';
$labels['keyid'] = 'ID de clave';
$labels['keyuserid'] = 'ID de usuario';
$labels['keytype'] = 'Tipo de clave';
$labels['fingerprint'] = 'Huella';
$labels['subkeys'] = 'Subclave';
$labels['keyprops'] = 'Propiedades de la clave';
$labels['basicinfo'] = 'Información básica';
$labels['userids'] = 'Usuarios adicionales';
$labels['typepublickey'] = 'clave pública';
$labels['typekeypair'] = 'par de claves';
$labels['keyattfound'] = 'Este mensaje contiene clave(s) PGP adjunta(s)';
$labels['keyattimport'] = 'Importar clave(s)';
$labels['typesign'] = 'Firmar';
$labels['typeencrypt'] = 'Encriptar';
$labels['typecert'] = 'Certificar';
$labels['typeauth'] = 'Autenticación';
$labels['subkeyid'] = 'ID';
$labels['subkeyalgo'] = 'Algoritmo';
$labels['subkeycreated'] = 'Creado';
$labels['subkeyexpires'] = 'Expira';
$labels['subkeyusage'] = 'Uso';
$labels['expiresnever'] = 'nunca';
$labels['unknown'] = 'desconocido';
$labels['uservalid'] = 'Válido';
$labels['userid'] = 'ID';
$labels['valid'] = 'válido';
$labels['supportencryption'] = 'Habilita encriptación y firma de mensajes';
$labels['supportsignatures'] = 'Habilitar la verificación de firmas de mensaje';
$labels['supportdecryption'] = 'Habilitar descifrado de mensaje';
$labels['signdefault'] = 'Firmar todos los mensajes por defecto';
$labels['encryptdefault'] = 'Cifrar todos los mensajes por defecto';
$labels['attachpubkeydefault'] = 'Adjuntar mi clave PGP pública por defecto';
$labels['passwordtime'] = 'Guardar las contraseñas de clave privada durante';
$labels['nminutes'] = '$m minuto(s)';
$labels['wholesession'] = 'toda la sesión';
$labels['createkeys'] = 'Crear un nuevo par de claves';
$labels['importkeys'] = 'Importar clave(s)';
$labels['exportkeys'] = 'Exportar clave(s)';
$labels['keyactions'] = 'Acciones sobre la clave...';
$labels['keyremove'] = 'Eliminar';
$labels['keydisable'] = 'Deshabilitar';
$labels['keyrevoke'] = 'Revocar';
$labels['keysend'] = 'Enviar clave pública en un mensaje';
$labels['keychpass'] = 'Cambiar contraseña';
$labels['keyadd'] = 'Agregar clave';
$labels['newkeyident'] = 'Identidad';
$labels['newkeypass'] = 'Contraseña';
$labels['newkeypassconfirm'] = 'Confirmar contraseña';
$labels['newkeytype'] = 'Tipo de clave';
$labels['rsa2048'] = 'RSA - 2048 bits';
$labels['rsa4096'] = 'RSA - 4096 bits';
$labels['ecckeypair'] = 'ECC (Curva elíptica)';
$labels['keygenerating'] = 'Generando claves...';
$labels['encryptionoptions'] = 'Opciones de cifrado...';
$labels['encryptmsg'] = 'Cifrar este mensaje';
$labels['signmsg'] = 'Firmar digitalmente este mensaje';
$labels['sendunencrypted'] = 'Enviar sin cifrar';
$labels['enterkeypasstitle'] = 'Escriba la frase de contraseña';
$labels['enterkeypass'] = 'Se necesita una contraseña para desbloquear la clave secreta ($keyid) para el usuario: $user.';
$labels['attachpubkeymsg'] = 'Adjuntar mi clave pública';
$labels['keyexportprompt'] = '¿Desea incluir claves secretas en el archivo guardado de claves OpenPGP?';
$labels['onlypubkeys'] = 'Exportar Sólo las Claves Públicas';
$labels['withprivkeys'] = 'Exportar las Claves Secretas';
$labels['findkey'] = 'Buscar en servidor(es) de claves';
$labels['keyimportlabel'] = 'Importar desde archivo';
$labels['keyimportsearchlabel'] = 'Buscar en servidor(es) de claves';
$labels['managekeys'] = 'Gestionar claves PGP';
$labels['identitymatchingprivkeys'] = 'Tiene $nr claves privadas PGP coincidentes en el anillo de claves:';
$labels['identitynoprivkeys'] = 'Esta identidad remitente no tiene aún una clave privada almacenada en el anillo de claves.';
$labels['arialabelkeyexportoptions'] = 'Opciones de claves de exportación';
$labels['arialabelkeysearchform'] = 'Formulario de búsqueda de claves';
$labels['arialabelkeyoptions'] = 'Opciones de clave';
$messages['sigvalid'] = 'Firma verificada de $sender.';
$messages['sigvalidpartial'] = 'Firma verificada de $sender, pero parte parte del cuerpo no estaba firmado.';
$messages['siginvalid'] = 'Firma inválida de $sender.';
$messages['sigunverified'] = 'Firma no verificada. Certificado no verificado. ID del Certificado: $keyid.';
$messages['signokey'] = 'Firma no verificada. Clave pública no encontrada. ID de Clave: $keyid.';
$messages['sigerror'] = 'Firma no verificada. Error interno.';
$messages['decryptok'] = 'Mensaje descifrado.';
$messages['decrypterror'] = 'Descifrado fallido.';
$messages['decryptnokey'] = 'Descifrado fallido. Clave privada no encontrada. ID de Clave: $keyid.';
$messages['decryptnomdc'] = 'Se ha saltado el descifrado. La integridad del mensaje no está protegida.';
$messages['decryptbadpass'] = 'Descifrado fallido. Contraseña no válida.';
$messages['decryptnopass'] = 'Descifrado fallido. Contraseña requerida.';
$messages['decryptpartial'] = 'Mensaje descifrado, pero parte del cuerpo no estaba cifrado.';
$messages['signerror'] = 'Falló la firma.';
$messages['signnokey'] = 'Falló la firma. Clave privada no encontrada.';
$messages['signbadpass'] = 'Falló la firma. Contraseña no válida.';
$messages['signnopass'] = 'Falló la firma. Contraseña requerida.';
$messages['encrypterror'] = 'Cifrado fallido.';
$messages['encryptnokey'] = 'Cifrado fallido. Clave pública no encontrada para $email.';
$messages['encryptnoprivkey'] = 'Cifrado fallido. No se encontró la clave privada.';
$messages['nokeysfound'] = 'No se encontraron claves';
$messages['keynotfound'] = '¡Clave no encontrada!';
$messages['keyopenerror'] = '¡No se puede obtener la información de clave! Error interno.';
$messages['keylisterror'] = '¡No es posible enumerar las claves! Error interno.';
$messages['keysimportfailed'] = '¡No se puede(n) importar la(s) clave(s)! Error interno.';
$messages['keysimportsuccess'] = 'Clave(s) importada(s) correctamente. Importado: $new, sin cambios: $old.';
$messages['keyremoving'] = 'Eliminando clave(s)...';
$messages['keyremoveconfirm'] = '¿Seguro que quiere eliminar la(s) clave(s) seleccionada(s)?';
$messages['keyremovesuccess'] = 'Clave(s) eliminada(s) correctamente.';
$messages['keyremoveerror'] = 'No se han podido borrar las clave(s) seleccionadas.';
$messages['keyimporttext'] = 'Puede importar la(s) clave(s) privada(s) y pública(s) o firmas de revocación en formato ASCII-Armor.';
$messages['keyimportsearchtext'] = 'Puede buscar claves por identificador de clave, nombre de usuario o dirección de email y después importarlas directamente.';
$messages['keystoragenotice'] = 'Todas las claves públicas y privadas son guardadas en el servidor.';
$messages['formerror'] = 'Por favor rellene el formulario. ¡Todos los campos son obligatorios!';
$messages['passwordsdiffer'] = '¡Las contraseñas no coinciden!';
$messages['keygenerateerror'] = 'No se ha podido generar un par de claves';
$messages['keygeneratesuccess'] = 'Un par de claves generadas e importadas correctamente.';
$messages['keygennosupport'] = 'Su navegador web no soporta criptografía. ¡No se puede generar un par de claves!';
$messages['noidentselected'] = '¡Tiene que seleccionar al menos una identidad para la clave!';
$messages['nonameident'] = '¡La identidad debe de tener un nombre de usuario definido!';
$labels['newkeysize'] = 'Tamaño de clave';
$labels['key2048'] = '2048 bits - por defecto';
$labels['key4096'] = '4096 bits - más seguro';
