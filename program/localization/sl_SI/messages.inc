<?php

/*
+-----------------------------------------------------------------------+
| language/sl_SI/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2008-2010, RoundQube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author:   Barbara Krasovec -  barbarak@arnes.si                       |
+-----------------------------------------------------------------------+

*/

$messages = array();
$messages['loginfailed'] = 'Prijava ni uspela';
$messages['cookiesdisabled'] = 'Vaš spletni brskalnik ne sprejema piškotkov.';
$messages['sessionerror'] = 'Seja je neveljavna ali je potekla.';
$messages['imaperror'] = 'Povezava z IMAP strežnikom je spodletela.';
$messages['servererror'] = 'Napaka strežnika!';
$messages['servererrormsg'] = 'Napaka strežnikaČ $msg';
$messages['errorreadonly'] = 'Operacije ni bilo mogoče izvršiti. Mapa je na voljo samo za branje.';
$messages['errornoperm'] = 'Operacije ni bilo mogoče izvršiti. Dostop zavrnjen.';
$messages['invalidrequest'] = 'Neveljavna zahteva. Podatki niso bili shranjeni.';
$messages['nomessagesfound'] = 'V poštnem predalu ni sporočil.';
$messages['loggedout'] = 'Odjava je bila uspešna.';
$messages['mailboxempty'] = 'Poštni predal je prazen.';
$messages['loading'] = 'Nalaganje...';
$messages['uploading'] = 'Prenos dokumenta na strežnik...';
$messages['loadingdata'] = 'Nalaganje podatkov...';
$messages['checkingmail'] = 'Preverjanje novih sporočil...';
$messages['sendingmessage'] = 'Pošiljanje sporočil...';
$messages['messagesent'] = 'Sporočilo je bilo uspešno poslano.';
$messages['savingmessage'] = 'Shranjevanje sporočila...';
$messages['messagesaved'] = 'Sporočilo je bilo shranjeno v Osnutke';
$messages['successfullysaved'] = 'Uspešno shranjeno.';
$messages['addedsuccessfully'] = 'Stik je bil uspešno dodan v imenik.';
$messages['contactexists'] = 'Stik s tem elektronskim naslovom že obstaja.';
$messages['blockedimages'] = 'Zaradi zaščite zasebnosti je prikaz slik v sporočilu onemogočen.';
$messages['encryptedmessage'] = 'Sporočilo je šifrirano in ne more biti prikazano.';
$messages['nocontactsfound'] = 'Ni stikov.';
$messages['contactnotfound'] = 'Iskanega stika ni bilo mogoče najti.';
$messages['sendingfailed'] = 'Sporočila ni bilo mogoče poslati.';
$messages['senttooquickly'] = 'Počakajte $sec sekund in nato znova poskusite s pošiljanjem sporočila.';
$messages['errorsavingsent'] = 'Pri shranjevanju poslanega sporočila je prišlo do napake.';
$messages['errorsaving'] = 'Pri shranjevanju je prišlo do napake.';
$messages['errormoving'] = 'Sporočila ni bilo mogoče premakniti.';
$messages['errorcopying'] = 'Sporočila ni bilo mogoče kopirati.';
$messages['errordeleting'] = 'Sporočila ni bilo mogoče izbrisati.';
$messages['errormarking'] = 'Sporočila ni bilo mogoče označiti.';
$messages['deletecontactconfirm'] = 'Ste prepričani, da želite izbrisati označen(e) stik(e)?';
$messages['deletemessagesconfirm'] = 'Ste prepričani, da želite izbrisati označeno(a) sporočilo(a)?';
$messages['deletefolderconfirm'] = 'Ste prepričani, da želite to mapo izbrisati?';
$messages['purgefolderconfirm'] = 'Ste prepričani, da želite izbrisati vsa sporočila v mapi?';
$messages['folderdeleting'] = 'Brisanje mape...';
$messages['foldermoving'] = 'Premikanje mape...';
$messages['foldersubscribing'] = 'Prijavljanje mape...';
$messages['folderunsubscribing'] = 'Odjavljanje mape...';
$messages['formincomplete'] = 'Obrazec ni v celoti izpolnjen';
$messages['noemailwarning'] = 'Vnesite veljaven elektronski naslov';
$messages['nonamewarning'] = 'Vnesite ime';
$messages['nopagesizewarning'] = 'Vnesite velikost strani';
$messages['nosenderwarning'] = 'Vnesite elektronski naslov pošiljatelja';
$messages['norecipientwarning'] = 'Vnesite vsaj enega prejemnika sporočila';
$messages['nosubjectwarning'] = 'Polje "Zadeva" je prazno. Želite dodati tekst v to polje?';
$messages['nobodywarning'] = 'Želite poslati sporočilo brez vsebine?';
$messages['notsentwarning'] = 'Sporočilo ni bilo poslano. Želite zavreči to sporočilo?';
$messages['noldapserver'] = 'Izberite LDAP strežnik, v katerem želite iskati?';
$messages['nocontactsreturned'] = 'Nobenega stika ni bilo mogoče najti';
$messages['nosearchname'] = 'Vnesite ime ali elektronski naslov stika';
$messages['notuploadedwarning'] = 'Priponke se še nalagajo na strežnik. Počakajte ali prekinite prenos.';
$messages['searchsuccessful'] = '$nr najdenih sporočil';
$messages['searchnomatch'] = 'Ni zadetkov';
$messages['searching'] = 'Iskanje...';
$messages['checking'] = 'Preverjanje...';
$messages['nospellerrors'] = 'V tekstu ni bilo nobenih pravopisnih napak';
$messages['folderdeleted'] = 'Mapa je bila uspešno izbrisana.';
$messages['foldersubscribed'] = 'Mapa je bila uspešno prijavljena.';
$messages['folderunsubscribed'] = 'Mapa je bila uspešno odjavljena.';
$messages['folderpurged'] = 'Mapa je bila uspešno izpraznjena';
$messages['folderexpunged'] = 'Mapa je bila uspešno skrčena';
$messages['deletedsuccessfully'] = 'Uspešno izbrisano';
$messages['converting'] = 'Odstranjevanje nastavitev oblikovanja...';
$messages['messageopenerror'] = 'Sporočila ni bilo mogoče prenesti iz strežnika';
$messages['fileuploaderror'] = 'Prenos datoteke je spodletel';
$messages['filesizeerror'] = 'Naložena datoteka presega največjo dovoljeno velikost $size';
$messages['copysuccess'] = '$nr naslovov je bilo uspešno prenešenih';
$messages['copyerror'] = 'Nobenih naslovov ni bilo mogoče kopirati';
$messages['sourceisreadonly'] = 'Ta naslov je na voljo samo za branje';
$messages['errorsavingcontact'] = 'Stika ni bilo mogoče shraniti';
$messages['movingmessage'] = 'Premikanje sporočila...';
$messages['copyingmessage'] = 'Kopiranje sporočila...';
$messages['deletingmessage'] = 'Brisanje sporočil/a';
$messages['markingmessage'] = 'Označevanje sporočil/a';
$messages['receiptsent'] = 'Sporočilo o branju je bilo uspešno poslano';
$messages['errorsendingreceipt'] = 'Sporočila o branju ni bilo mogoče poslati';
$messages['nodeletelastidentity'] = 'Te identitete ni mogoče izbrisati, saj je edina.';
$messages['forbiddencharacter'] = 'Ime mape vsebuje nedovoljen znak';
$messages['selectimportfile'] = 'Izberite datoteko, ki jo želite prenesti';
$messages['addresswriterror'] = 'Izbran imenik ni zapisljiv';
$messages['contactaddedtogroup'] = 'Stik je bil uspešno dodan v skupino';
$messages['contactremovedfromgroup'] = 'Stik je bil uspešno izbrisan iz skupine';
$messages['importwait'] = 'Uvažanje poteka...';
$messages['importerror'] = 'Uvažanje je spodletelo. Naložena datoteka ni veljavna vCard datoteka.';
$messages['importconfirm'] = '<b>Uspešno uvoženi $inserted stiki, $skipped stikov že obstaja v imeniku</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operacija ni dovoljena.';
$messages['nofromaddress'] = 'V izbrani identiteti manjka elektronski naslov.';
$messages['editorwarning'] = 'Preklop v splošen urejevalnik onemogoča uporabo izbranih nastavitev oblikovanja. Želite nadaljevati?';
$messages['httpreceivedencrypterror'] = 'Prišlo je do večje napake v konfiguraciji.Obrnite se na skrbnika sistema.  <b>Sporočila ni bilo mogoče poslati.</b>';
$messages['smtpconnerror'] = 'Napaka pri pošiljanju ($code): Povezava s strežnikom je spodletela';
$messages['smtpautherror'] = 'Napaka pri pošiljanju($code): Avtentikacija je spodletela';
$messages['smtpfromerror'] = 'Napaka pri pošiljanju ($code): Neveljaven elektronski naslov pošiljatelja "$from" ($msg)';
$messages['smtptoerror'] = 'Napaka pri pošiljanju ($code): Neveljaven elektronski naslov prejemnika "$to" ($msg)';
$messages['smtprecipientserror'] = 'Napaka pri pošiljanju: Seznama prejemnikov ni bilo mogoče razčleniti';
$messages['smtpdsnerror'] = 'Napaka pri pošiljanju: Ni podpore za pošiljanje obvestil o statusu dostave';
$messages['smtperror'] = 'Napaka pri pošiljanju:$msg';
$messages['emailformaterror'] = 'Nepravilen elektronski naslov: $email';
$messages['toomanyrecipients'] = 'Navedli ste preveč prejemnikov. Zmanjšajte število prejemnikov na $max';
$messages['maxgroupmembersreached'] = 'Število članov skupine presega največje dovoljeno število $max.';
$messages['internalerror'] = 'Prišlo je do napake. Poskusite znova.';
$messages['contactdelerror'] = 'Stika/ov ni bilo mogoče izbrisati';
$messages['contactdeleted'] = 'Stik/i so bili uspešno izbrisani';
$messages['groupdeleted'] = 'Skupina je bila uspešno izbrisana';
$messages['grouprenamed'] = 'Skupina je bila uspešno preimenovana';
$messages['groupcreated'] = 'Skupina je bila uspešno ustvarjena';
$messages['messagedeleted'] = 'Sporočila so bila uspešno izbrisana';
$messages['messagemoved'] = 'Sporočila so bila uspešno premaknjena';
$messages['messagecopied'] = 'Sporočila so bila uspešno kopirana';
$messages['messagemarked'] = 'Sporočila so bila uspešno označena';
$messages['autocompletechars'] = 'Vnesite vsaj $min znakov za samodejno dokončanje';
$messages['namecannotbeempty'] = 'Imena ni mogoče izbrisati';
$messages['nametoolong'] = 'Ime je predolgo';
$messages['folderupdated'] = 'Mapa je bila uspešno posodobljena';
$messages['foldercreated'] = 'Mapa je bila uspešno ustvarjena';

?>
