<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = 'Dobrodošli na $product';
$labels['username']  = 'Korisničko ime';
$labels['password']  = 'Lozinka';
$labels['server']    = 'Poslužitelj';
$labels['login']     = 'Prijava';

// taskbar
$labels['logout']   = 'Odjava';
$labels['mail']     = 'Pošta';
$labels['settings'] = 'Postavke';
$labels['addressbook'] = 'Imenik';

// mailbox names
$labels['inbox']  = 'Pristigla pošta';
$labels['drafts'] = 'Predlošci';
$labels['sent']   = 'Poslano';
$labels['trash']  = 'Smeće';
$labels['junk']   = 'Spam';

// message listing
$labels['subject'] = 'Naslov';
$labels['from']    = 'Od';
$labels['sender']  = 'Sender';
$labels['to']      = 'Za';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Odgovori';
$labels['followupto'] = 'Nastavi-na';
$labels['date']    = 'Datum';
$labels['size']    = 'Veličina';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organizacija';
$labels['readstatus'] = 'Pročitano';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Mape';
$labels['messagesfromto'] = 'Poruke od $from do $to od ukupno $count';
$labels['threadsfromto'] = 'Teme od $from do $to od ukupno $count';
$labels['messagenrof'] = 'Poruka broj $nr od $count';
$labels['fromtoshort'] = '$from - $to od $count';

$labels['copy']     = 'Kopiraj';
$labels['move']     = 'Premjesti';
$labels['moveto']   = 'Premjesti u...';
$labels['download'] = 'Preuzmi (download)';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Ime datoteke';
$labels['filesize'] = 'Veličina datoteke';

$labels['addtoaddressbook'] = 'Dodaj u imenik';

// weekdays short
$labels['sun'] = 'Ned';
$labels['mon'] = 'Pon';
$labels['tue'] = 'Uto';
$labels['wed'] = 'Sri';
$labels['thu'] = 'Čet';
$labels['fri'] = 'Pet';
$labels['sat'] = 'Sub';

// weekdays long
$labels['sunday']    = 'Nedjelja';
$labels['monday']    = 'Ponedjeljak';
$labels['tuesday']   = 'Utorak';
$labels['wednesday'] = 'Srijeda';
$labels['thursday']  = 'Četvrtak';
$labels['friday']    = 'Petak';
$labels['saturday']  = 'Subota';

// months short
$labels['jan']	= 'Sij';
$labels['feb']	= 'Velj';
$labels['mar']	= 'Ožu';
$labels['apr']	= 'Tra';
$labels['may']	= 'Svibanj';
$labels['jun']	= 'Lip';
$labels['jul'] 	= 'Srp';
$labels['aug']	= 'Kol';
$labels['sep']	= 'Ruj';
$labels['oct']	= 'Lis';
$labels['nov']	= 'Stu';
$labels['dec']	= 'Pro';

// months long
$labels['longjan']	= 'Siječanj';
$labels['longfeb']	= 'Veljača';
$labels['longmar']	= 'Ožujak';
$labels['longapr']	= 'Travanj';
$labels['longmay']	= 'Svibanj';
$labels['longjun']	= 'Lipanj';
$labels['longjul']	= 'Srpanj';
$labels['longaug']	= 'Kolovoz';
$labels['longsep']	= 'Rujan';
$labels['longoct']	= 'Listopad';
$labels['longnov']	= 'Studeni';
$labels['longdec']	= 'Prosinac';

$labels['today'] = 'Danas';

// toolbar buttons
$labels['refresh']          = 'Osvježi';
$labels['checkmail']        = 'Provjera novih poruka';
$labels['compose']          = 'Nova poruka';
$labels['writenewmessage']  = 'Stvori novu poruku';
$labels['reply']            = 'Odgovori';
$labels['replytomessage']   = 'Odgovori na poruku';
$labels['replytoallmessage'] = 'Odgovori pošiljaocu i svim primateljima';
$labels['replyall']         = 'Odgovori svima';
$labels['replylist']        = 'Odgovori listi';
$labels['forward']          = 'Proslijedi';
$labels['forwardinline']    = 'Proslijedi citirano';
$labels['forwardattachment'] = 'Proslijedi kao privitak';
$labels['forwardmessage']   = 'Prosljedi poruku';
$labels['deletemessage']    = 'Obriši poruku';
$labels['movemessagetotrash'] = 'Preseli poruke u smeće';
$labels['printmessage']     = 'Ispiši ovu poruku';
$labels['previousmessage']  = 'Prikaži prethodnu poruku';
$labels['firstmessage']     = 'Prikaži prvu poruku';
$labels['nextmessage']      = 'Prikaži slijedeću poruku';
$labels['lastmessage']      = 'Prikaži zadnju poruku';
$labels['backtolist']       = 'Povratak na listu poruka';
$labels['viewsource']       = 'Prikaži poruku u izvornom obliku';
$labels['mark']             = 'Označi';
$labels['markmessages']     = 'Označi poruke';
$labels['markread']         = 'Kao pročitano';
$labels['markunread']       = 'Kao nepročitano';
$labels['markflagged']      = 'Kao označenu';
$labels['markunflagged']    = 'Kao neoznačenu';
$labels['moreactions']      = 'Više akcija';
$labels['more']             = 'Više';
$labels['back']             = 'Natrag';
$labels['options']          = 'Postavke';

$labels['select'] = 'Odaberi';
$labels['all'] = 'Sve';
$labels['none'] = 'Ništa';
$labels['currpage'] = 'Trenutna stranica';
$labels['unread'] = 'Nepročitane';
$labels['flagged'] = 'Označene';
$labels['unanswered'] = 'Neodgovrene';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Obrisano';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Obrni';
$labels['filter'] = 'Filtriraj';
$labels['list'] = 'Lista';
$labels['threads'] = 'Teme';
$labels['expand-all'] = 'Proširi sve';
$labels['expand-unread'] = 'Proširi nepročitane';
$labels['collapse-all'] = 'Zatvori sve';
$labels['threaded'] = 'Tematski prikaz';

$labels['autoexpand_threads'] = 'Proširi teme poruka';
$labels['do_expand'] = 'Sve teme';
$labels['expand_only_unread'] = 'samo s nepročitanim porukama';
$labels['fromto'] = 'Pošiljatelj/Primatelj';
$labels['flag'] = 'Oznaka';
$labels['attachment'] = 'Privitak';
$labels['nonesort'] = 'Ništa';
$labels['sentdate'] = 'Datum slanja';
$labels['arrival'] = 'Datum primanja';
$labels['asc'] = 'uzlazno';
$labels['desc'] = 'silazno';
$labels['listcolumns'] = 'Kolone';
$labels['listsorting'] = 'Sortirajuća kolona';
$labels['listorder'] = 'Sortirajući redoslijed';
$labels['listmode'] = 'Model pregleda listi';

$labels['folderactions'] = 'Akcije mapa';
$labels['compact'] = 'Kompresiranje';
$labels['empty'] = 'Isprazni';

$labels['quota'] = 'Kvota';
$labels['unknown']  = 'nepoznato';
$labels['unlimited']  = 'beskonačna';

$labels['quicksearch']  = 'Brza pretraga';
$labels['resetsearch']  = 'Prikaži sve poruke';
$labels['searchmod']  = 'Postavke pretrage';
$labels['msgtext']  = 'Cijela poruka';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Otvori u novom prozoru';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Uredi kao novo';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Pošalji poruku';
$labels['savemessage']    = 'Spremi u \'Predlošci\'';
$labels['addattachment']  = 'Priloži datoteku';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'Tip editora';
$labels['returnreceipt']  = 'Potvrda o primitku poruke';
$labels['dsn']            = 'Potvrda o poslanoj poruci';
$labels['mailreplyintro'] = 'Dana $date, $sender je napisao(la):';
$labels['originalmessage'] = 'Izvorna Poruka';

$labels['editidents']    = 'Promijeni identitete';
$labels['spellcheck']    = 'Piši';
$labels['checkspelling'] = 'Provjera pravopisa';
$labels['resumeediting'] = 'Povratak u pisanje';
$labels['revertto']      = 'Vrati na';

$labels['attach'] = 'Priloži';
$labels['attachments'] = 'Privitci (Attachments)';
$labels['upload'] = 'Dodaj';
$labels['uploadprogress'] = '$percent ($current od $total)';
$labels['close']  = 'Zatvori';
$labels['messageoptions']  = 'Opcije poruka...';

$labels['low']     = 'Nizak';
$labels['lowest']  = 'Najniži';
$labels['normal']  = 'Srednji';
$labels['high']    = 'Visok';
$labels['highest'] = 'Najviši';

$labels['nosubject']  = '(bez naslova)';
$labels['showimages'] = 'Prikaži slike';
$labels['alwaysshow'] = 'Uvijek prikaži slike od $sender';
$labels['isdraft']    = 'Ova poruka je skica (draft)';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Samo tekst';
$labels['savesentmessagein'] = 'Spremi poslane poruke u';
$labels['dontsave'] = 'ne spremaj';
$labels['maxuploadsize'] = 'Maksimalna dozvoljena velicina datoteke je $size';

$labels['addcc'] = 'Dodaj Cc';
$labels['addbcc'] = 'Dodaj Bcc';
$labels['addreplyto'] = 'Dodaj Odgovori-na';
$labels['addfollowupto'] = 'Dodaj Nastavak-na';

// mdn
$labels['mdnrequest'] = 'Pošiljaoc ove poruke je tražio da bude obaviješten o njenom primitku. Želite li obavijestiti pošiljaoca?';
$labels['receiptread'] = 'Vrati potvrdu (pročitano)';
$labels['yourmessage'] = 'Ovo je vraćena potvrda vaše poruke';
$labels['receiptnote'] = 'Info: Ova poruka samo potvrđuje da se poruka prikazala na računalu primaoca. Nema garancije da je primaoc pročitao ili razumio sadržaj poruke.';

// address boook
$labels['name']         = 'Puno ime';
$labels['firstname']    = 'Ime';
$labels['surname']      = 'Prezime';
$labels['middlename']   = 'Srednje ime';
$labels['nameprefix']   = 'Prefiks';
$labels['namesuffix']   = 'Sufiks';
$labels['nickname']     = 'Nadimak';
$labels['jobtitle']     = 'Titula';
$labels['department']   = 'Odjel';
$labels['gender']       = 'Rod';
$labels['maidenname']   = 'Djevojačko ime';
$labels['email']        = 'E-Mail';
$labels['phone']        = 'Telefon';
$labels['address']      = 'Adresa';
$labels['street']       = 'Ulica';
$labels['locality']     = 'Grad';
$labels['zipcode']      = 'Poštanski broj';
$labels['region']       = 'Oblast';
$labels['country']      = 'Zemlja';
$labels['birthday']     = 'Rođendan';
$labels['anniversary']  = 'Jubilej';
$labels['website']      = 'Web stranica';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Bilješke';
$labels['male']   = 'muško';
$labels['female'] = 'žensko';
$labels['manager'] = 'Menadžer';
$labels['assistant'] = 'Asistent';
$labels['spouse'] = 'Suprug/a';
$labels['allfields'] = 'Sva polja';
$labels['search'] = 'Pretraga';
$labels['advsearch'] = 'Napredna Pretraga';
$labels['advanced'] = 'Napredno';
$labels['other'] = 'Ostalo';

$labels['typehome']   = 'Kućni broj';
$labels['typework']   = 'Poslovni broj';
$labels['typeother']  = 'Ostalo';
$labels['typemobile']  = 'Mobitel';
$labels['typemain']  = 'Glavni broj';
$labels['typehomefax']  = 'Fax kuća';
$labels['typeworkfax']  = 'Fax posao';
$labels['typecar']  = 'Auto';
$labels['typepager']  = 'Pejdžer';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Asistent';
$labels['typehomepage']  = 'Početna stranica';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profil';

$labels['addfield'] = 'Dodaj polje...';
$labels['addcontact'] = 'Dodaj novi kontakt';
$labels['editcontact'] = 'Izmjeni kontakt';
$labels['contacts'] = 'Kontakti';
$labels['contactproperties'] = 'Svojstva kontakta';
$labels['personalinfo'] = 'Osobni podaci';

$labels['edit']   = 'Izmjeni';
$labels['cancel'] = 'Odustani';
$labels['save']   = 'Spremi';
$labels['delete'] = 'Obriši';
$labels['rename'] = 'Preimenuj';
$labels['addphoto'] = 'Dodaj';
$labels['replacephoto'] = 'Smijeniti';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Dodaj novi kontakt';
$labels['deletecontact']  = 'Obriši odabrane kontakte';
$labels['composeto']      = 'Napiši mail...';
$labels['contactsfromto'] = 'Kontakti od $from do $to od ukupno $count';
$labels['print']          = 'Print';
$labels['export']         = 'Export';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Izvezi kontakte u vCard formatu';
$labels['newcontactgroup'] = 'Napravi novu kontaktnu grupu';
$labels['grouprename']    = 'Promijeni ime grupe';
$labels['groupdelete']    = 'Izbriši grupu';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Prethodna strana';
$labels['firstpage']      = 'Prva strana';
$labels['nextpage']       = 'Slijedeća strana';
$labels['lastpage']       = 'Zadnja strana';

$labels['group'] = 'Grupa';
$labels['groups'] = 'Grupe';
$labels['personaladrbook'] = 'Privatna adresa';

$labels['searchsave'] = 'Pohrani pretragu';
$labels['searchdelete'] = 'Obriši pretragu';

$labels['import'] = 'Uvoz';
$labels['importcontacts'] = 'Uvoz kontakta';
$labels['importfromfile'] = 'Uvezi iz datoteke:';
$labels['importtarget'] = 'Dodaj nove kontakte u imenik:';
$labels['importreplace'] = 'Prepiši cijeli adresar';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Završeno';

// settings
$labels['settingsfor'] = 'Postavke za';
$labels['about'] = 'O programu';
$labels['preferences'] = 'Postavke';
$labels['userpreferences'] = 'Korisničke postavke';
$labels['editpreferences'] = 'Izmjena postavki';

$labels['identities'] = 'Identiteti';
$labels['manageidentities'] = 'Podesi identitete za ovaj nalog';
$labels['newidentity'] = 'Dodaj identitet';

$labels['newitem'] = 'Novo';
$labels['edititem'] = 'Uredi';

$labels['preferhtml'] = 'HTML format u prednosti';
$labels['defaultcharset'] = 'Zadani skup znakova';
$labels['htmlmessage'] = 'HTML format poruke';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Format datuma';
$labels['timeformat'] = 'Format vremena';
$labels['prettydate'] = 'Formatiran datum';
$labels['setdefault']  = 'Postavi predodređeno';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Jezik';
$labels['timezone']  = 'Vremenska zona';
$labels['pagesize']  = 'Redova po stranici';
$labels['signature'] = 'Potpis';
$labels['dstactive']  = 'Automatska promjena vremena';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Sastavi HTML poruke';
$labels['htmlonreply'] = 'odgovor samo na HTML poruke';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML potpis';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Prikaži područje pregleda';
$labels['skin'] = 'Tema izgleda';
$labels['logoutclear'] = 'Očisti smeće pri izlazu';
$labels['logoutcompact'] = 'Komprimiraj Inbox pri izlazu';
$labels['uisettings'] = 'Korisnički interfejs';
$labels['serversettings'] = 'Postavke servera';
$labels['mailboxview'] = 'Pregled Mailboxa';
$labels['mdnrequests'] = 'Obavijest pošiljatelja';
$labels['askuser'] = 'pitaj korisnika';
$labels['autosend'] = 'šalji automatski';
$labels['autosendknown'] = 'Dodaj primatelja u moje kontakte, u suprotnom me pitaj';
$labels['autosendknownignore'] = 'Dodaj primatelja u moje kontakte, u suprotnom zanemari';
$labels['ignore'] = 'zanemari';
$labels['readwhendeleted'] = 'Označi poruku kao pročitanu pri brisanju';
$labels['flagfordeletion'] = 'Označi poruku za brisanje umjesto brisanja';
$labels['skipdeleted'] = 'Ne prikazivaj pobrisane poruke';
$labels['deletealways'] = 'Ukoliko premještanje poruka u mapu otpad ne uspije, izbrši poruku';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Prikaži slike s interneta';
$labels['fromknownsenders'] = 'od poznatih pošiljatelja';
$labels['always'] = 'uvijek';
$labels['showinlineimages'] = 'Prikaži slike ispod poruke';
$labels['autosavedraft']  = 'Automatski spremi draft';
$labels['everynminutes']  = 'svakih $n minuta';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'nikada';
$labels['immediately']  = 'odmah';
$labels['messagesdisplaying'] = 'Prikazujem poruke';
$labels['messagescomposition'] = 'Komponiram poruke';
$labels['mimeparamfolding'] = 'Nazivi privitaka';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (ostali)';
$labels['force7bit'] = 'Koristi MIME postavke za 8-bitne znakove';
$labels['advancedoptions'] = 'Napredne postavke';
$labels['focusonnewmessage'] = 'Fokusiraj browser pri novoj poruci';
$labels['checkallfolders'] = 'Provjeri nove poruke u svim mapama';
$labels['displaynext'] = 'Nakon brisanja/micanja poruke, prikaži slijedeću';
$labels['defaultfont'] = 'Predodređeni font HTML poruke';
$labels['mainoptions'] = 'Glavne postavke';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Sekcija';
$labels['maintenance'] = 'Održavanje';
$labels['newmessage'] = 'Nova poruka';
$labels['signatureoptions'] = 'Postavke potpisa';
$labels['whenreplying'] = 'Prilikom odgovora';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'započni novu poruku iznad originala';
$labels['replybottomposting'] = 'započni novu poruku ispod originala';
$labels['replyremovesignature'] = 'Kod odgovaranja, makni originalni potpis iz poruke';
$labels['autoaddsignature'] = 'Automatski dodaj potpis';
$labels['newmessageonly'] = 'samo nova poruka';
$labels['replyandforwardonly'] = 'samo odgovori i proslijeđivanja';
$labels['insertsignature'] = 'Umetni potpis';
$labels['previewpanemarkread']  = 'Obilježi pregledane poruke kao pročitane';
$labels['afternseconds']  = 'nakon $n sekundi';
$labels['reqmdn'] = 'Uvijek zatraži potvrdu o primitku poruke';
$labels['reqdsn'] = 'Uvijek zatraži potvrdu o slanju poruke';
$labels['replysamefolder'] = 'Spremi odgovore u mapu gdje se nalazi poruka';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Preskoči alternativne email adrese u autocomplete prijedlozima';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Provjeri pravopis prije slanja poruke';
$labels['spellcheckoptions'] = 'Postavke provjere pravopisa';
$labels['spellcheckignoresyms'] = 'Ignoriraj riječi koje sadrže simbole';
$labels['spellcheckignorenums'] = 'Ignoriraj riječi koje sadrže brojeve';
$labels['spellcheckignorecaps'] = 'Ignoriraj riječi sa sa svim velikim slovima';
$labels['addtodict'] = 'Dodaj u rječnik';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Mapa';
$labels['folders']  = 'Mape';
$labels['foldername']  = 'Ime mape';
$labels['subscribed']  = 'Pretplata';
$labels['messagecount'] = 'Poruke';
$labels['create']  = 'Napravi';
$labels['createfolder']  = 'Napravi novu mapu';
$labels['managefolders']  = 'Podesi mapu';
$labels['specialfolders'] = 'Specijalna mapa';
$labels['properties'] = 'Svojstva';
$labels['folderproperties'] = 'Svojstva mape';
$labels['parentfolder'] = 'Glavna fascikla';
$labels['location'] = 'Lokacija';
$labels['info'] = 'Informacija';
$labels['getfoldersize'] = 'Kliknite da biste dobili veličinu mape';
$labels['changesubscription'] = 'Kliknite da biste promjenili pretplatu';
$labels['foldertype'] = 'Vrsta mape';
$labels['personalfolder']  = 'Privatna mapa';
$labels['otherfolder']  = 'Mapa drugih korisnika';
$labels['sharedfolder']  = 'Javna mapa';

$labels['sortby'] = 'Sortiraj po';
$labels['sortasc']  = 'Sortiraj rastućim nizom';
$labels['sortdesc'] = 'Sortiraj opadajućim nizom';
$labels['undo'] = 'Poništi';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Inačica';
$labels['source'] = 'Izvor';
$labels['license'] = 'Licenca';
$labels['support'] = 'Podrška';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Engleski';
$labels['westerneuropean'] = 'Zapadno Evropski';
$labels['easterneuropean'] = 'Istočno Evropski';
$labels['southeasterneuropean'] = 'Južno-Istočni Evropski';
$labels['baltic'] = 'Balitčki';
$labels['cyrillic'] = 'Čirilčni';
$labels['arabic'] = 'Arapski';
$labels['greek'] = 'Grčki';
$labels['hebrew'] = 'Hibru';
$labels['turkish'] = 'Turski';
$labels['nordic'] = 'Nordski';
$labels['thai'] = 'Tajlandski';
$labels['celtic'] = 'Keltski';
$labels['vietnamese'] = 'Vijetnamski';
$labels['japanese'] = 'Japanski';
$labels['korean'] = 'Korejski';
$labels['chinese'] = 'Kineski';

?>
