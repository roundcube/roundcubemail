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

$labels['changepasswd']  = 'Mainīt paroli';
$labels['curpasswd']  = 'Pašreizējā parole:';
$labels['newpasswd']  = 'Jaunā parole:';
$labels['confpasswd']  = 'Apstiprināt jauno paroli:';
$messages['nopassword'] = 'Lūdzu ievadiet jauno paroli.';
$messages['nocurpassword'] = 'Lūdzu ievadiet pašreizējo paroli.';
$messages['passwordincorrect'] = 'Pašreizējā parole nav pareiza.';
$messages['passwordinconsistency'] = 'Paroles nesakrīt. Lūdzu, ievadiet vēlreiz.';
$messages['crypterror'] = 'Nevarēja saglabāt jauno paroli. Trūkst kriptēšanas funkcijas.';
$messages['connecterror'] = 'Nevarēja saglabāt jauno paroli. Savienojuma kļūda.';
$messages['internalerror'] = 'Nevarēja saglabāt jauno paroli.';
$messages['passwordshort'] = 'Jaunajai parolei jābūt vismaz $length simbolu garai.';
$messages['passwordweak'] = 'Jaunajai parolei jāsatur vismaz viens cipars un speciālais simbols.';
$messages['passwordtooweak'] = 'Parole ir pārāk nedroša.';
$messages['passwordnoseq'] = 'Parolei nevajadzētu būt vienkāršai secībai kā, piemēram, 123456 vai QWERTY.';
$messages['passwordnocommon'] = 'Parolei nevajadzētu būt parastam vārdam vai nosaukumam.';
$messages['passwordforbidden'] = 'Parole satur neatļautus simbolus.';
$messages['firstloginchange'] = 'Jūs autorizējaties pirmo reizi. Lūdzu nomainite savu paroli.';
$messages['disablednotice'] = 'Sistēma uz doto brīdi atrodas apkopes režīmā un paroles maiņa nav iespējama. Lūdzu mēginiet vēlāk. Atvainojamies par sagādātajām neērtībām.';
$messages['passwinhistory'] = 'Šī parole agrāk jau ir tikusi izmantota.';
$messages['samepasswd'] = 'Jaunā parole nedrīkst būt tāda pati kā vecā.';
$messages['passwdexpirewarning'] = 'UZMANĪBU! Jūsu parolei drīz beigsies derīguma termiņš, lūdzu nomainiet to pirms $expirationdatetime.';
$messages['passwdexpired'] = 'Jūsu parolei ir beidzies derīguma termiņš un Jums tā tagad ir jānomaina.';
$messages['passwdconstraintviolation'] = 'Paroles veidošanas nosacījumu pārkāpums. Parole, iespējams, pārāk nedroša.';
$messages['pwned_isdisclosed'] = 'Šī ir vispārzināma parole.';
$messages['pwned_fetcherror'] = 'Neizdevās pārbaudīt paroles drošumu.';
