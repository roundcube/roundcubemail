<?php

/*

+-----------------------------------------------------------------------+
| language/et_EE/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Üllar Pajus <yllar.pajus@gmail.com>                           |
|         Raivo Hool <raivo.hool@gmail.com>                             |
|         Elan Ruusamäe <glen@delfi.ee>                                 |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Sisselogimine ebaõnnestus';
$messages['cookiesdisabled'] = 'Sinu veebilehitseja ei võta präänikuid vastu';
$messages['sessionerror'] = 'Sinu seanss on aegunud või vigane';
$messages['imaperror'] = 'Ei õnnestunud IMAP serveriga ühendust luua';
$messages['servererror'] = 'Serveri tõrge!';
$messages['servererrormsg'] = 'Serveri tõrge: $msg';
$messages['databaserror'] = 'Andmebaasi tõrge!';
$messages['errorreadonly'] = 'Operatsioon nurjus. Kaustale on vaid lugemisõigus';
$messages['errornoperm'] = 'Operatsioon nurjus. Ligipääsu õigused puuduvad';
$messages['invalidrequest'] = 'Lubamatu päring! Andmeid ei salvestatud.';
$messages['nomessagesfound'] = 'Postkast paistab tühi olevat';
$messages['loggedout'] = 'Sinu seanss on edukalt lõpetatud. Nägemiseni!';
$messages['mailboxempty'] = 'Postkast on tühi';
$messages['loading'] = 'Laadin...';
$messages['uploading'] = 'Laadin faili üles...';
$messages['uploadingmany'] = 'Failide üleslaadimine...';
$messages['loadingdata'] = 'Andmete laadimine...';
$messages['checkingmail'] = 'Kontrollin uusi kirju...';
$messages['sendingmessage'] = 'Saadan kirja...';
$messages['messagesent'] = 'Kiri edukalt saadetud';
$messages['savingmessage'] = 'Salvestan kirja...';
$messages['messagesaved'] = 'Kiri salvestatud mustanditesse';
$messages['successfullysaved'] = 'Edukalt salvestatud';
$messages['addedsuccessfully'] = 'Kontakt lisati edukalt aadressiraamatusse';
$messages['contactexists'] = 'Sellise e-posti aadressiga kontakt on juba olemas';
$messages['blockedimages'] = 'Sinu privaatsuse kaitsmiseks on selles kirjas välised pildid blokeeritud.';
$messages['encryptedmessage'] = 'See on krüpteeritud kiri ja kahjuks pole seda võimalik näidata. Andestust!';
$messages['nocontactsfound'] = 'Ei leitud ühtegi kontakti';
$messages['contactnotfound'] = 'Otsitud kontakti ei leitud';
$messages['sendingfailed'] = 'Kirja saatmine ebaõnnestus';
$messages['senttooquickly'] = 'Palun oota enne selle kirja saatmist $sec sekundit.';
$messages['errorsavingsent'] = 'Saadetud kirja salvestamisel ilmnes tõrge';
$messages['errorsaving'] = 'Salvestamise ajal ilmnes viga';
$messages['errormoving'] = 'Ei suutnud seda kirja liigutada';
$messages['errorcopying'] = 'Kirju ei õnnestunud kopeerida';
$messages['errordeleting'] = 'Ei suutnud seda kirja kustutada';
$messages['errormarking'] = 'Kirja märkimine nurjus';
$messages['deletecontactconfirm'] = 'Soovid valitud kontaktid kustutada?';
$messages['deletemessagesconfirm'] = 'Soovid valitud kirja(d) kustutada?';
$messages['deletefolderconfirm'] = 'Soovid selle kataloogi kindlasti kustutada?';
$messages['purgefolderconfirm'] = 'Soovid sellest kaustast kõik kirjad kustutada?';
$messages['folderdeleting'] = 'Kustutan kausta...';
$messages['foldermoving'] = 'Liigutan kausta...';
$messages['foldersubscribing'] = 'Tellin kausta...';
$messages['folderunsubscribing'] = 'Peatan kausta tellimuse...';
$messages['formincomplete'] = 'Vormi kõik väljad ei ole täidetud';
$messages['noemailwarning'] = 'Palun sisesta toimiv e-posti aadress';
$messages['nonamewarning'] = 'Palun sisesta nimi';
$messages['nopagesizewarning'] = 'Palun sisesta lehekülje suurus';
$messages['nosenderwarning'] = 'Palun sisesta saatja e-posti aadress';
$messages['norecipientwarning'] = 'Palun sisesta vähemalt üks adressaat';
$messages['nosubjectwarning'] = 'Väli „Pealkiri“ on tühi. Soovid selle ära täita?';
$messages['nobodywarning'] = 'Saadan selle kirja ilma tekstita?';
$messages['notsentwarning'] = 'Kirja saatmine nurjus. Soovid selle ära visata?';
$messages['noldapserver'] = 'Palun vali otsinguks LDAPi server';
$messages['nocontactsreturned'] = 'Ühtegi kontakti ei leitud';
$messages['nosearchname'] = 'Palun sisesta kontakti nimi või e-posti aadress';
$messages['notuploadedwarning'] = 'Kõiki manuseid ei ole veel üles laaditud. Palun oota või tühista üleslaadimine.';
$messages['searchsuccessful'] = 'Leiti $nr kirja';
$messages['searchnomatch'] = 'Otsingule vastavaid kirju ei leitud';
$messages['searching'] = 'Otsin...';
$messages['checking'] = 'Kontrollin...';
$messages['nospellerrors'] = 'Õigekirjavigu ei leitud';
$messages['folderdeleted'] = 'Kausta kustutamine õnnestus';
$messages['foldersubscribed'] = 'Kausta tellimine õnnestus';
$messages['folderunsubscribed'] = 'Kausta tellimise peatamine õnnestus';
$messages['folderpurged'] = 'Kaust puhastatud edukalt';
$messages['folderexpunged'] = 'Kaust tühjendatud edukalt';
$messages['deletedsuccessfully'] = 'Edukalt kustutatud';
$messages['converting'] = 'Eemaldan kirjast vormindust...';
$messages['messageopenerror'] = 'Kirjade serverist laadimine ebaõnnestus';
$messages['fileuploaderror'] = 'Faili üleslaadimine ebaõnnestus';
$messages['filesizeerror'] = 'Üleslaetava faili suurus ületab maksimaalse suuruse $size';
$messages['copysuccess'] = '$nr aadressi edukalt kopeeritud';
$messages['copyerror'] = 'Ühegi aadressi kopeerimine ei õnnestunud ';
$messages['sourceisreadonly'] = 'Aadressi asukohas puuduvad kirjutusõigused';
$messages['errorsavingcontact'] = 'Kontakti aadressi salvestamine ebaõnnestus';
$messages['movingmessage'] = 'Liigutan kirja...';
$messages['copyingmessage'] = 'Kopeerin kirja...';
$messages['deletingmessage'] = 'Kustutan kirja...';
$messages['markingmessage'] = 'Märgin kirja...';
$messages['receiptsent'] = 'Kättesaamiskinnitus saadetud';
$messages['errorsendingreceipt'] = 'Ei õnnestunud kättesaamiskinnitust saata';
$messages['nodeletelastidentity'] = 'See identiteet on viimane ja seda ei saa kustutada.';
$messages['forbiddencharacter'] = 'Kausta nimi sisaldab keelatud sümbolit';
$messages['selectimportfile'] = 'Palun vali üleslaadimiseks fail';
$messages['addresswriterror'] = 'Valitud aadressiraamat pole kirjutatav';
$messages['contactaddedtogroup'] = 'Kontaktide lisamine gruppi õnnestus';
$messages['contactremovedfromgroup'] = 'Kontaktide eemaldamine grupist õnnestus';
$messages['importwait'] = 'Impordin, palun oota...';
$messages['importerror'] = 'Import nurjus! Üleslaaditud fail ei ole vCard formaadis.';
$messages['importconfirm'] = '<b>Edukalt imporditud $inserted kontakti, $skipped olemasolevat sissekannet vahele jäetud</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b> $skipped olemasolevat kirjet jäeti vahele</b>';
$messages['opnotpermitted'] = 'Tegevus pole lubatud!';
$messages['nofromaddress'] = 'Valitud identiteedil puudub e-posti aadress';
$messages['editorwarning'] = 'Klaarteksti redaktorile lülitamine kaotab kogu teksti vorminduse. Soovid jätkata?';
$messages['httpreceivedencrypterror'] = 'Viga seadistustes. Võta kohe oma süsteemiadministraatoriga ühendust. <b>Sinu kirja polnud võimalik ära saata.</b>';
$messages['smtpconnerror'] = 'SMTP tõrge ($code): serveriga ühendumine nurjus';
$messages['smtpautherror'] = 'SMTP tõrge ($code): autentimine nurjus';
$messages['smtpfromerror'] = 'SMTP tõrge ($code): "$from" saatjaks määramine nurjus ($msg)';
$messages['smtptoerror'] = 'SMTP tõrge ($code): "$to" saajaks määramine nurjus ($msg)';
$messages['smtprecipientserror'] = 'SMTP tõrge: saajate nimekirja parsimine nurjus';
$messages['smtpdsnerror'] = 'SMTP tõrge: puudub kohalejõudmise teavituse toetus';
$messages['smtperror'] = 'SMTP tõrge: $msg';
$messages['emailformaterror'] = 'Lubamatu e-posti aadress: $email';
$messages['toomanyrecipients'] = 'Liiga palju adressaate. Vähenda kirja saajate hulka $max-ni.';
$messages['maxgroupmembersreached'] = 'Grupi liikmete arv ületab suurima lubatud $max';
$messages['internalerror'] = 'Ilmnes sisemine tõrge. Palun proovi uuesti.';
$messages['contactdelerror'] = 'Kontaktide kustutamine nurjus';
$messages['contactdeleted'] = 'Kontaktide kustutamine õnnestus';
$messages['groupdeleted'] = 'Grupi kustutamine õnnestus';
$messages['grouprenamed'] = 'Grupi ümbernimetamine õnnestus';
$messages['groupcreated'] = 'Grupi loomine õnnestus';
$messages['messagedeleted'] = 'Kiri edukalt kustutatud';
$messages['messagemoved'] = 'Kiri edukalt liigutatud';
$messages['messagecopied'] = 'Kiri edukalt kopeeritud';
$messages['messagemarked'] = 'Kiri edukalt märgitud';
$messages['autocompletechars'] = 'Sisesta vähemalt $min märki autom. lõpetuseks';
$messages['namecannotbeempty'] = 'Nimi ei saa olla tühi';
$messages['nametoolong'] = 'Nimi liiga pikk';
$messages['folderupdated'] = 'Kaust uuendatud edukalt';
$messages['foldercreated'] = 'Kaust loodud edukalt';
$messages['invalidimageformat'] = 'Pole õige pildi formaat';

?>
