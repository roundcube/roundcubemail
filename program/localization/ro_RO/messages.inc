<?php

/*
+-----------------------------------------------------------------------+
| language/ro_RO/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Daniel Anechitoaie - danieLs <daniels@safereaction.ro>        |
|         Zeno Popovici <zeno.popovici at ulbsibiu.ro>                  |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Autentificare esuata';
$messages['cookiesdisabled'] = 'Browserul dumneavoastra nu accepta cookies';
$messages['sessionerror'] = 'Sesiunea dumneavoastra este invalida sau a expirat';
$messages['imaperror'] = 'Conectarea la serverul IMAP a esuat';
$messages['nomessagesfound'] = 'Nu a fost gasit nici un mesaj in aceasta casuta postala';
$messages['loggedout'] = 'Sesiune incheiata cu succes. La revedere!';
$messages['mailboxempty'] = 'Casuta postala este goala';
$messages['loading'] = 'Se incarca...';
$messages['loadingdata'] = 'Se incarca informatiile...';
$messages['checkingmail'] = 'Verifica pentru mesaje noi...';
$messages['sendingmessage'] = 'Trimitere mesaj...';
$messages['messagesent'] = 'Mesajul a fost trimis cu succes!';
$messages['savingmessage'] = 'Salvare mesaj...';
$messages['messagesaved'] = 'Mesajul a fost salvat in Ciorne';
$messages['successfullysaved'] = 'Salvarea s-a efectuat cu succes';
$messages['addedsuccessfully'] = 'Contactul a fost adaugat cu succes in agenda';
$messages['contactexists'] = 'Mai exista un contact cu aceasta adresa de e-mail';
$messages['blockedimages'] = 'Pentru a va proteja intimitatea imaginile externe au fost blocate.';
$messages['encryptedmessage'] = 'Acesta este un mesaj criptat si nu poate fi afisat. Ne pare rau.';
$messages['nocontactsfound'] = 'Nu s-a gasit nici un contact';
$messages['contactnotfound'] = 'Contactul solicitat nu a fost gasit.';
$messages['sendingfailed'] = 'Nu s-a reusit trimiterea mesajului';
$messages['senttooquickly'] = 'Va rugam asteptati $sec sec. inainte de a trimite acest mesaj';
$messages['errorsavingsent'] = 'A intervenit o eroare in timp ce se efectua salvarea mesajului trimis';
$messages['errorsaving'] = 'A intervenit o eroare in timp ce se efectua salvarea';
$messages['errormoving'] = 'Nu s-a putut muta mesajul';
$messages['errordeleting'] = 'Nu s-a putut sterge mesajul';
$messages['deletecontactconfirm'] = 'Sunteti sigur ca doriti sa stergeti contactul(ele) selectate?';
$messages['deletemessagesconfirm'] = 'Chiar doriti sa stergeti mesajele selectate ?';
$messages['deletefolderconfirm'] = 'Sunteti sigur ca doriti sa stergeti acest dosar?';
$messages['purgefolderconfirm'] = 'Sunteti sigur ca doriti sa stergeti toate mesajele din acest dosar?';
$messages['foldercreating'] = 'Se creaza dosarul...';
$messages['folderdeleting'] = 'Se sterge dosarul...';
$messages['folderrenaming'] = 'Se redenumeste dosarul...';
$messages['foldermoving'] = 'Se muta dosarul...';
$messages['formincomplete'] = 'Formularul nu a fost completat corect';
$messages['noemailwarning'] = 'Va rugam introduceti o adresa de e-mail valida';
$messages['nonamewarning'] = 'Va rugam introduceti un nume';
$messages['nopagesizewarning'] = 'Va rugam introduceti marimea paginii';
$messages['nosenderwarning'] = 'Va rugam introduceti adresa expeditorului';
$messages['norecipientwarning'] = 'Va rugam introduceti cel putin un destinatar';
$messages['nosubjectwarning'] = 'Mesajul nu are subiect. Vreti sa introduceti unul acum?';
$messages['nobodywarning'] = 'Trimiteti acest mesaj fara text?';
$messages['notsentwarning'] = 'Mesajul nu a fost trimis. Vreti sa renuntatati la el?';
$messages['noldapserver'] = 'Va rugam alegeti un server ldap pentru cautare';
$messages['nocontactsreturned'] = 'Nu s-a gasit nici un contact';
$messages['nosearchname'] = 'Va rugam introduceti un nume de contact sau adresa de e-mail';
$messages['searchsuccessful'] = '$nr mesaje gasite';
$messages['searchnomatch'] = 'Cautarea nu a returnat nici un rezultat';
$messages['searching'] = 'Se cauta...';
$messages['checking'] = 'Se verifica...';
$messages['nospellerrors'] = 'Nu s-a gasit nicio greseala de ortografie';
$messages['folderdeleted'] = 'Dosarul a fost sters cu succes';
$messages['deletedsuccessfully'] = 'Mesaj sters cu succes !';
$messages['converting'] = 'Resetez mesajul la parametrii initiali';
$messages['messageopenerror'] = 'Nu am putut incarca mesajul din server';
$messages['fileuploaderror'] = 'Incarcarea pe server a esuat';
$messages['filesizeerror'] = 'Fisierul incarcat depaseste marimea de $size';
$messages['copysuccess'] = 'Am copiat cu succes $nr de adrese';
$messages['copyerror'] = 'Nu am putut copia nici o adresa';
$messages['sourceisreadonly'] = 'Sursa adrese este "read-only"(se poate doar citi)';
$messages['errorsavingcontact'] = 'Nu am putut salva adresa de contact';
$messages['movingmessage'] = 'Mutare mesaj....';
$messages['receiptsent'] = 'Confirmarea a fost trimisa cu succes';
$messages['errorsendingreceipt'] = 'Confirmarea nu a putut fi trimisa';
$messages['nodeletelastidentity'] = 'Nu putesti sterge aceasta identitate, este ultima.';
$messages['addsubfolderhint'] = 'Acest dosar va fi creat ca subdosar al celui selectat.';
$messages['forbiddencharacter'] = 'Numele dosarului contine un caracter nepermis.';
$messages['selectimportfile'] = 'Va rugam selectati fisierul pentru incarcare';
$messages['addresswriterror'] = 'Agenda selectata nu poate fi actualizata';
$messages['importwait'] = 'Se importeaza, va rugam asteptati...';
$messages['importerror'] = 'Importarea a esuat! Fisierul incarcat nu este un fisier vCard valid.';
$messages['importconfirm'] = '<b>Contactele $inserted au fost importate cu succes, $skipped intrari au fost ignorate deoarece ele exista deja</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operatia nu este permisa!';
$messages['nofromaddress'] = 'Nu exista o adresa de e-mail in identitatea selectata';
$messages['editorwarning'] = 'Trecerea in mod text a editorului va cauza pierderea formatarii textului. Doriti sa continuati?';

?>
