<?php

/*

+-----------------------------------------------------------------------+
| language/et_EE/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Üllar Pajus <yllar.pajus@gmail.com>                           |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Sisselogimine ebaõnnestus';
$messages['cookiesdisabled'] = 'Sinu veebilehitseja ei võta präänikuid vastu';
$messages['sessionerror'] = 'Sinu sessioon on aegunud või vigane';
$messages['imaperror'] = 'Ei õnnestunud IMAP serveriga ühendust luua';
$messages['servererror'] = 'Serveri tõrge!';
$messages['invalidrequest'] = 'Lubamatu päring! Andmeid ei salvestatud.';
$messages['nomessagesfound'] = 'Postkast paistab tühi olevat';
$messages['loggedout'] = 'Sinu sessioon on edukalt lõpetatud. Nägemiseni!';
$messages['mailboxempty'] = 'Postkast on tühi';
$messages['loading'] = 'Laadimine...';
$messages['loadingdata'] = 'Andmete laadimine...';
$messages['checkingmail'] = 'Uute kirjade kontrollimine...';
$messages['sendingmessage'] = 'Kirja saatmine...';
$messages['messagesent'] = 'Kiri edukalt saadetud';
$messages['savingmessage'] = 'Kirja salvestamine...';
$messages['messagesaved'] = 'Kiri salvestatud Mustanditesse';
$messages['successfullysaved'] = 'Edukalt salvestatud';
$messages['addedsuccessfully'] = 'Kontakt lisati edukalt aadressiraamatusse';
$messages['contactexists'] = 'Sama e-maili aadressiga kontakt on juba olemas';
$messages['blockedimages'] = 'Sinu privaatsuse kaitsmiseks on selles kirjas välised pildid blokeeritud.';
$messages['encryptedmessage'] = 'See on krüpteeritud kiri ja kahjuks pole seda võimalik näidata. Andestust!';
$messages['nocontactsfound'] = 'Ei leitud ühtegi kontakti';
$messages['contactnotfound'] = 'Otsitud kontakti ei leitud';
$messages['sendingfailed'] = 'Kirja saatmine ebaõnnestus';
$messages['senttooquickly'] = 'Palun oota enne selle kirja saatmist $sec sekundit.';
$messages['errorsavingsent'] = 'Saadetud kirja salvestamisel ilmnes tõrge';
$messages['errorsaving'] = 'Salvestamise ajal ilmnes viga';
$messages['errormoving'] = 'Ei suutnud seda kirja liigutada';
$messages['errordeleting'] = 'Ei suutnud seda kirja kustutada';
$messages['errormarking'] = 'Kirja märkimine nurjus';
$messages['deletecontactconfirm'] = 'Soovid valitud kontaktid kustutada ?';
$messages['deletemessagesconfirm'] = 'Soovid valitud kirja(d) kustutada ?';
$messages['deletefolderconfirm'] = 'Soovid selle kataloogi kindlasti kustutada ?';
$messages['purgefolderconfirm'] = 'Soovid sellest kaustast kõik kirjad kustutada ?';
$messages['foldercreating'] = 'Kausta loomine...';
$messages['folderdeleting'] = 'Kausta kustutamine...';
$messages['folderrenaming'] = 'Kausta ümber nimetamine...';
$messages['foldermoving'] = 'Kausta liigutamine...';
$messages['formincomplete'] = 'Vormi kõik väljad ei ole täidetud';
$messages['noemailwarning'] = 'Palun sisesta toimiv e-maili aadress';
$messages['nonamewarning'] = 'Palun sisesta nimi';
$messages['nopagesizewarning'] = 'Palun sisesta lehekülje suurus';
$messages['nosenderwarning'] = 'Palun sisesta saatja e-posti aadress';
$messages['norecipientwarning'] = 'Palun sisesta vähemalt üks kirjasaaja';
$messages['nosubjectwarning'] = 'Väli "Pealkiri" on tühi. Soovid selle ära täita ?';
$messages['nobodywarning'] = 'Saadan selle kirja ilma tekstita ?';
$messages['notsentwarning'] = 'Kirja ei suudetud saata. Soovid selle ära visata ?';
$messages['noldapserver'] = 'Palun vali otsinguks LDAPi server';
$messages['nocontactsreturned'] = 'Ühtegi kontakti ei leitud';
$messages['nosearchname'] = 'Palun sisesta kontakti nimi või e-maili aadress';
$messages['searchsuccessful'] = 'Leiti $nr kirja';
$messages['searchnomatch'] = 'Otsingule vastavaid kirju ei leitud';
$messages['searching'] = 'Otsimine...';
$messages['checking'] = 'Kontrollimine...';
$messages['nospellerrors'] = 'Õigekirja vigu ei leitud';
$messages['folderdeleted'] = 'Kausta kustutamine õnnestus';
$messages['deletedsuccessfully'] = 'Edukalt kustutatud';
$messages['converting'] = 'Kirjast vorminduse eemaldamine...';
$messages['messageopenerror'] = 'Kirjade serverist laadimine ebaõnnestus';
$messages['fileuploaderror'] = 'Faili üleslaadimine ebaõnnestus';
$messages['filesizeerror'] = 'Üleslaetava faili suurus ületab maksimaalse suuruse $size';
$messages['copysuccess'] = '$nr aadressi edukalt kopeeritud';
$messages['copyerror'] = 'Ühegi aadressi kopeerimine ei õnnestunud ';
$messages['sourceisreadonly'] = 'Aadressi asukohas puuduvad kirjutusõigused';
$messages['errorsavingcontact'] = 'Kontakti aadressi salvestamine ebaõnnestus';
$messages['movingmessage'] = 'Kirja liigutamine...';
$messages['receiptsent'] = 'Kättesaamiskinnitus saadetud';
$messages['errorsendingreceipt'] = 'Ei õnnestunud kättesaamiskinnitust saata';
$messages['nodeletelastidentity'] = 'See identiteet on viimane ja seda ei saa kustutada.';
$messages['addsubfolderhint'] = 'See kataloog luuakse valitud kausta alamkataloogina.';
$messages['forbiddencharacter'] = 'Kausta nimi sisaldab keelatud sümbolit';
$messages['selectimportfile'] = 'Palun vali üleslaadimiseks fail';
$messages['addresswriterror'] = 'Valitud aadressiraamat pole kirjutatav';
$messages['importwait'] = 'Importimine, palun oota...';
$messages['importerror'] = 'Import nurjus! Üleslaaditud fail ei ole vCard formaadis.';
$messages['importconfirm'] = '<b>Edukalt imporditud $inserted kontakti, $skipped olemasolevat sissekannet vahele jäetud</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Tegevus pole lubatud!';
$messages['nofromaddress'] = 'Valitud identiteedil puudub e-posti aadress';
$messages['editorwarning'] = 'Klaarteksti redaktorile lülitamine kaotab kogu teksti vorminduse. Soovid jätkata?';
$messages['httpreceivedencrypterror'] = 'Ilmnes saatuslik konfiguratsiooniviga. Võta koheselt oma süsteemiadministraatoriga ühendust. <b> Sinu kirja polnud võimalik ära saata. </b>';
$messages['smtpconnerror'] = 'SMTP Tõrge ($code): Serveriga ühendumine nurjus';
$messages['smtpautherror'] = 'SMTP Tõrge ($code): Autentimine nurjus';
$messages['smtpfromerror'] = 'SMTP Tõrge ($code): "$from" saatjaks määramine nurjus';
$messages['smtptoerror'] = 'SMTP Tõrge ($code): "$to" saajaks määramine nurjus';
$messages['smtprecipientserror'] = 'SMTP Tõrge: Saajate nimekirja parsimine nurjus';
$messages['smtperror'] = 'SMTP Tõrge: $msg';

?>
