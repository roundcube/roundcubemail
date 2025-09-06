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

$labels['hestia_err_policy_short'] = 'Parole ir pārāk īsa (minimālais garums: %min).';
$labels['hestia_err_endpoint_invalid'] = 'Hestia galapunkts nav konfigurēts vai ir nederīgs.';
$labels['hestia_err_identity'] = 'Lietotājvārdu nav iespējams analizēt local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'Nevar noteikt %domēna īpašnieku.';
$labels['hestia_err_unknown'] = 'Nezināma kļūda no Hestia API.';
$labels['hestia_err_default'] = 'Hestia atgrieza kļūdas kodu %kods.';
$labels['hestia_err_1'] = 'Nav sniegts pietiekami daudz argumentu.';
$labels['hestia_err_2'] = 'Nederīgs objekts vai arguments.';
$labels['hestia_err_3'] = 'Objekts neeksistē (nav atrasta pastkastīte vai domēns).';
$labels['hestia_err_4'] = 'Objekts jau eksistē.';
$labels['hestia_err_5'] = 'Objekts jau ir apturēts.';
$labels['hestia_err_6'] = 'Objekts jau ir atslēgts.';
$labels['hestia_err_7'] = 'Objekts lietošanā; nevar modificēt.';
$labels['hestia_err_8'] = 'Darbību bloķē paketes ierobežojumi.';
$labels['hestia_err_9'] = 'Nepareiza vai nederīga parole.';
$labels['hestia_err_10'] = 'Hestia atteica atļauju (API identitātei nav tiesību vai IP nav atļauts).';
$labels['hestia_err_11'] = 'Sistēmas politikā funkcija ir atspējota.';
$labels['hestia_err_12'] = 'Kļūda lasīšanā.';
$labels['hestia_err_13'] = 'Diska kļūda.';
$labels['hestia_err_14'] = 'Pārāk liela servera slodze.';
$labels['hestia_err_15'] = 'Savienojuma kļūda.';
$labels['hestia_err_16'] = 'FTP kļūda.';
$labels['hestia_err_17'] = 'Datubāzes kļūda.';
$labels['hestia_err_18'] = 'RRD kļūda.';
$labels['hestia_err_19'] = 'Atjaunināšanas kļūda.';
$labels['hestia_err_20'] = 'Restartēšanas kļūda.';
?>
