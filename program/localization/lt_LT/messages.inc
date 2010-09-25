<?php

/*

+-----------------------------------------------------------------------+
| language/lt_LT/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, Roundcube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Authors: Martynas Bendorius <martynas@evanet.lt>                      |
|          Rimas Kudelis <rq@akl.lt>                                    |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Prisijungti nepavyko';
$messages['cookiesdisabled'] = 'Jūsų naršyklė nepriima slapukų';
$messages['sessionerror'] = 'Jūsų sesija negaliojanti';
$messages['imaperror'] = 'Nepavyko prisijungti prie IMAP serverio';
$messages['servererror'] = 'Serverio klaida!';
$messages['invalidrequest'] = 'Netinkama užklausa! Duomenys neišsaugoti.';
$messages['nomessagesfound'] = 'Šioje pašto dėžutėje laiškų nėra ';
$messages['loggedout'] = 'Jūs sėkmingai atsijungėte. Iki kito karto!';
$messages['mailboxempty'] = 'Pašto dėžutė tuščia';
$messages['loading'] = 'Įkeliama…';
$messages['uploading'] = 'Įkeliamas failas…';
$messages['loadingdata'] = 'Įkeliami duomenys…';
$messages['checkingmail'] = 'Tikrinama ar nėra naujų pranešimų…';
$messages['sendingmessage'] = 'Siunčiamas laiškas…';
$messages['messagesent'] = 'Laiškas sėkmingai nusiųstas';
$messages['savingmessage'] = 'Laiškas įrašomas…';
$messages['messagesaved'] = 'Laiškas įrašytas į Juodraščių aplanką';
$messages['successfullysaved'] = 'Sėkmingai įrašyta';
$messages['addedsuccessfully'] = 'Kontaktas įtrauktas į adresų knygą';
$messages['contactexists'] = 'Kontaktas, turintis šį el. pašto adresą, jau egzistuoja';
$messages['blockedimages'] = 'Siekiant apsaugoti Jūsų privatumą, paveikslėliai iš interneto neįkelti';
$messages['encryptedmessage'] = 'Apgailestaujame, tačiau šis laiškas užšifruotas ir negali būti parodytas.';
$messages['nocontactsfound'] = 'Kontaktų nerasta';
$messages['contactnotfound'] = 'Ieškotas kontaktas nerastas';
$messages['sendingfailed'] = 'Laiško išsiųsti nepavyko';
$messages['senttooquickly'] = 'Turite luktelėti $sec sek., kad galėtumėte išsiųsti laišką';
$messages['errorsavingsent'] = 'Įrašant išsiųstą laišką įvyko klaida';
$messages['errorsaving'] = 'Įrašant įvyko klaida';
$messages['errormoving'] = 'Laiško(-ų) perkelti nepavyko';
$messages['errorcopying'] = 'Laiško(-ų) nukopijuoti nepavyko';
$messages['errordeleting'] = 'Laiško(-ų) pašalinti nepavyko';
$messages['errormarking'] = 'Laiško(-ų) pažymėti nepavyko';
$messages['deletecontactconfirm'] = 'Ar tikrai pašalinti pažymėtą(-us) kontaktą(-us)?';
$messages['deletemessagesconfirm'] = 'Ar tikrai pašalinti pažymėtą(-us) laišką(-us)?';
$messages['deletefolderconfirm'] = 'Ar tikrai pašalinti šį aplanką?';
$messages['purgefolderconfirm'] = 'Ar tikrai pašalinti visus šiame aplanke esančius laiškus?';
$messages['foldercreating'] = 'Aplankas kuriamas…';
$messages['folderdeleting'] = 'Aplankas šalinamas…';
$messages['folderrenaming'] = 'Aplankas pervardinamas…';
$messages['foldermoving'] = 'Aplankas perkeliamas…';
$messages['formincomplete'] = 'Formos laukai nepilnai užpildyti';
$messages['noemailwarning'] = 'Prašom įvesti teisingą el. pašto adresą';
$messages['nonamewarning'] = 'Prašom įvesti vardą';
$messages['nopagesizewarning'] = 'Prašom įvesti puslapio dydį';
$messages['nosenderwarning'] = 'Prašom įvesti siuntėjo el. pašto adresą';
$messages['norecipientwarning'] = 'Prašom įvesti bent vieną gavėją';
$messages['nosubjectwarning'] = 'Temos laukelis tuščias. Ar norite temą įvesti dabar?';
$messages['nobodywarning'] = 'Išsiųsti šį laišką be teksto?';
$messages['notsentwarning'] = 'Laiškas neišsiųstas. Ar jūs tikrai norite jo atsikratyti?';
$messages['noldapserver'] = 'Prašome pasirinkti LDAP serverį paieškai';
$messages['nocontactsreturned'] = 'Kontaktas(-ai) nerastas(-i)';
$messages['nosearchname'] = 'Prašom įvesti vardą arba el. pašto adresą';
$messages['notuploadedwarning'] = 'Įkelti dar ne visi priedai. Prašom palaukti arba atšaukti jų įkėlimą.';
$messages['searchsuccessful'] = 'Rasta laiškų: $nr';
$messages['searchnomatch'] = 'Paiešką atitinkančių rezultatų nėra';
$messages['searching'] = 'Ieškoma…';
$messages['checking'] = 'Tikrinama…';
$messages['nospellerrors'] = 'Rašybos klaidų nerasta';
$messages['folderdeleted'] = 'Aplankas sėkmingai pašalintas';
$messages['deletedsuccessfully'] = 'Sėkmingai pašalinta';
$messages['converting'] = 'Šalinamas laiško formatavimas…';
$messages['messageopenerror'] = 'Nepavyko įkelti laiško iš serverio';
$messages['fileuploaderror'] = 'Nepavyko įkelti failo';
$messages['filesizeerror'] = 'Įkeltas failas viršija maksimalų leistiną dydį – $size';
$messages['copysuccess'] = 'Nukopijuota adresų: $nr';
$messages['copyerror'] = 'Adresų nukopijuoti nepavyko';
$messages['sourceisreadonly'] = 'Šis adresų šaltinis prieinamas tik skaitymui';
$messages['errorsavingcontact'] = 'Kontakto adreso įrašyti nepavyko';
$messages['movingmessage'] = 'Laiškas perkeliamas…';
$messages['copyingmessage'] = 'Laiškas kopijuojamas…';
$messages['receiptsent'] = 'Laiško perskaitymo pažyma sėkmingai išsiųsta';
$messages['errorsendingreceipt'] = 'Laiško perskaitymo pažymos išsiųsti nepavyko';
$messages['nodeletelastidentity'] = 'Jūs negalite ištrinti šios tapatybės, nes ji yra vienintelė likusi.';
$messages['addsubfolderhint'] = 'Šis aplankas bus sukurtas kaip pažymėtojo aplanko poaplankis';
$messages['forbiddencharacter'] = 'Aplanko varde rasta neleistinų simbolių';
$messages['selectimportfile'] = 'Parinkite įkelti norimą failą';
$messages['addresswriterror'] = 'Pasirinktoji adresų knyga prieinama tik skaitymui';
$messages['contactaddedtogroup'] = 'Į šią grupę sėkmingai įtraukti kontaktai';
$messages['contactremovedfromgroup'] = 'Iš šios grupės sėkmingai pašalinti kontaktai';
$messages['importwait'] = 'Importuojama, prašome palaukti…';
$messages['importerror'] = 'Importavimas nepavyko! Įkeltasis failas nėra vCard formato.';
$messages['importconfirm'] = '<b>Sėkmingai importuoti $inserted kontaktai, $skipped egzistuojantys įrašai praleisti</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Veiksmas neleistinas!';
$messages['nofromaddress'] = 'Nenurodytas pasirinktosios tapatybės el. pašto adresas';
$messages['editorwarning'] = 'Pereinant į grynojo teksto redagavimą, visas laiško formatavimas bus prarastas. Ar tęsti?';
$messages['httpreceivedencrypterror'] = 'Įvyko lemtinga konfigūracijos klaida. Nedelsiant susisiekite su administratoriumi. <b>Jūsų laiškas negali būti išsiųstas.</b>';
$messages['smtpconnerror'] = 'SMTP klaida ($code): nepavyko prisijungti prie serverio';
$messages['smtpautherror'] = 'SMTP klaida ($code): nepavyko autentikuotis';
$messages['smtpfromerror'] = 'SMTP klaida ($code): nepavyko nustatyti siuntėjo „$from“ ($msg)';
$messages['smtptoerror'] = 'SMTP klaida ($code): nepavyko pridėti gavėjo „$to“ ($msg)';
$messages['smtprecipientserror'] = 'SMTP klaida: nepavyko apdoroti gavėjų sąrašo';
$messages['smtperror'] = 'SMTP klaida: $msg';
$messages['emailformaterror'] = 'Netinkamas el. pašto adresas: $email';
$messages['toomanyrecipients'] = 'Per daug gavėjų. Sumažinkite jų bent iki $max.';
$messages['maxgroupmembersreached'] = 'Grupės narių skaičius viršijo maksimalų leistiną ($max)';

?>
