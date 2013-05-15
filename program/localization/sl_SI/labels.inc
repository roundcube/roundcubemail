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
$labels['welcome']   = 'Dobrodošli v $product';
$labels['username']  = 'Uporabniško ime';
$labels['password']  = 'Geslo';
$labels['server']    = 'Strežnik';
$labels['login']     = 'Prijava';

// taskbar
$labels['logout']   = 'Odjava';
$labels['mail']     = 'E-Pošta';
$labels['settings'] = 'Osebne nastavitve';
$labels['addressbook'] = 'Stiki';

// mailbox names
$labels['inbox']  = 'Prejeto';
$labels['drafts'] = 'Osnutki';
$labels['sent']   = 'Poslano';
$labels['trash']  = 'Smeti';
$labels['junk']   = 'Nezaželena sporočila';

// message listing
$labels['subject'] = 'Zadeva';
$labels['from']    = 'Pošiljatelj';
$labels['sender']  = 'Pošiljatelj';
$labels['to']      = 'Prejemnik';
$labels['cc']      = 'Kp';
$labels['bcc']     = 'Skp';
$labels['replyto'] = 'Odgovor na';
$labels['followupto'] = 'Odgovor na';
$labels['date']    = 'Datum';
$labels['size']    = 'Velikost';
$labels['priority'] = 'Prioriteta';
$labels['organization'] = 'Organizacija';
$labels['readstatus'] = 'Status';
$labels['listoptions'] = 'Možnosti prikaza...';

$labels['mailboxlist'] = 'Mape';
$labels['messagesfromto'] = 'Sporočila $from do $to od $count';
$labels['threadsfromto'] = 'Nit $from do $to od $count';
$labels['messagenrof'] = 'Sporočilo $nr od $count';
$labels['fromtoshort'] = '$from – $to od $count';

$labels['copy']     = 'Kopiraj';
$labels['move']     = 'Premakni';
$labels['moveto']   = 'Premakni v...';
$labels['download'] = 'Prenesi';
$labels['open']     = 'Odpri';
$labels['showattachment'] = 'Prikaži';
$labels['showanyway'] = 'Prikaži';

$labels['filename'] = 'Ime datoteke';
$labels['filesize'] = 'Velikost datoteke';

$labels['addtoaddressbook'] = 'Dodaj med stike';

// weekdays short
$labels['sun'] = 'ned';
$labels['mon'] = 'pon';
$labels['tue'] = 'tor';
$labels['wed'] = 'sre';
$labels['thu'] = 'čet';
$labels['fri'] = 'pet';
$labels['sat'] = 'sob';

// weekdays long
$labels['sunday']    = 'nedelja';
$labels['monday']    = 'ponedeljek';
$labels['tuesday']   = 'torek';
$labels['wednesday'] = 'sreda';
$labels['thursday']  = 'četrtek';
$labels['friday']    = 'petek';
$labels['saturday']  = 'sobota';

// months short
$labels['jan']	= 'jan';
$labels['feb']	= 'feb';
$labels['mar']	= 'mar';
$labels['apr']	= 'Apr';
$labels['may']	= 'maj';
$labels['jun']	= 'jun';
$labels['jul'] 	= 'jul';
$labels['aug']	= 'avg';
$labels['sep']	= 'sep';
$labels['oct']	= 'okt';
$labels['nov']	= 'nov';
$labels['dec']	= 'dec';

// months long
$labels['longjan']	= 'januar';
$labels['longfeb']	= 'februar';
$labels['longmar']	= 'marec';
$labels['longapr']	= 'April';
$labels['longmay']	= 'maj';
$labels['longjun']	= 'junij';
$labels['longjul']	= 'julij';
$labels['longaug']	= 'avgust';
$labels['longsep']	= 'september';
$labels['longoct']	= 'oktober';
$labels['longnov']	= 'november';
$labels['longdec']	= 'december';

$labels['today'] = 'Danes';

// toolbar buttons
$labels['refresh']          = 'Osveži';
$labels['checkmail']        = 'Preglej nova sporočila';
$labels['compose']          = 'Sestavi sporočilo';
$labels['writenewmessage']  = 'Novo sporočilo';
$labels['reply']            = 'Odgovori';
$labels['replytomessage']   = 'Odgovori';
$labels['replytoallmessage'] = 'Odgovori listi ali vsem prejemnikom';
$labels['replyall']         = 'Odgovori vsem';
$labels['replylist']        = 'Odgovori listi';
$labels['forward']          = 'Posreduj';
$labels['forwardinline']    = 'Posreduj medvrstično';
$labels['forwardattachment'] = 'Posreduj kot priponko';
$labels['forwardmessage']   = 'Posreduj';
$labels['deletemessage']    = 'Izbriši';
$labels['movemessagetotrash'] = 'Premakni v smeti';
$labels['printmessage']     = 'Natisni';
$labels['previousmessage']  = 'Prikaži prejšnje sporočilo';
$labels['firstmessage']     = 'Prikaži prvo sporočilo';
$labels['nextmessage']      = 'Prikaži naslednje sporočilo';
$labels['lastmessage']      = 'Prikaži zadnje sporočilo';
$labels['backtolist']       = 'Nazaj na seznam sporočil';
$labels['viewsource']       = 'Prikaži izvirno kodo';
$labels['mark']             = 'Označi';
$labels['markmessages']     = 'Označi sporočilo';
$labels['markread']         = 'Kot prebrano';
$labels['markunread']       = 'Kot neprebrano';
$labels['markflagged']      = 'Kot označeno';
$labels['markunflagged']    = 'Kot neoznačeno';
$labels['moreactions']      = 'Več možnosti...';
$labels['more']             = 'Več';
$labels['back']             = 'Nazaj';
$labels['options']          = 'Možnosti';

$labels['select'] = 'Izberi';
$labels['all'] = 'Vse';
$labels['none'] = 'Brez';
$labels['currpage'] = 'Trenutna stran';
$labels['unread'] = 'Neprebrano';
$labels['flagged'] = 'Označeno';
$labels['unanswered'] = 'Neoznačeno';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Izbrisano';
$labels['undeleted'] = 'Ni izbrisano';
$labels['invert'] = 'Zamenjaj';
$labels['filter'] = 'Prikaži';
$labels['list'] = 'Seznam';
$labels['threads'] = 'Niti';
$labels['expand-all'] = 'Razširi vse';
$labels['expand-unread'] = 'Razširi neprebrano';
$labels['collapse-all'] = 'Zloži vse';
$labels['threaded'] = 'Nitno';

$labels['autoexpand_threads'] = 'Razširi niti';
$labels['do_expand'] = 'vse niti';
$labels['expand_only_unread'] = 'samo niti z neprebranimi sporočili';
$labels['fromto'] = 'Pošiljatelj/Prejemnik';
$labels['flag'] = 'Oznaka';
$labels['attachment'] = 'Priponka';
$labels['nonesort'] = 'Brez';
$labels['sentdate'] = 'Datum pošiljanja';
$labels['arrival'] = 'Datum prejema';
$labels['asc'] = 'naraščajoče';
$labels['desc'] = 'padajoče';
$labels['listcolumns'] = 'Seznam stolpcev';
$labels['listsorting'] = 'Urejanje stolpcev';
$labels['listorder'] = 'Vrstni red';
$labels['listmode'] = 'Način prikaza seznama';

$labels['folderactions'] = 'Upravljanje map...';
$labels['compact'] = 'Stisni';
$labels['empty'] = 'Izprazni';

$labels['quota'] = 'Poraba prostora';
$labels['unknown']  = 'neznana';
$labels['unlimited']  = 'neomejena';

$labels['quicksearch']  = 'Hitri iskalnik';
$labels['resetsearch']  = 'Prekliči iskanje';
$labels['searchmod']  = 'Spremembe iskanja';
$labels['msgtext']  = 'Celotno sporočilo';
$labels['body']  = 'Vsebina sporočila';

$labels['openinextwin'] = 'Odpri v novem oknu';
$labels['emlsave'] = 'Prenos datoteke (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Uredi kot novo';
$labels['send']           = 'Pošlji';
$labels['sendmessage']    = 'Pošlji sporočilo';
$labels['savemessage']    = 'Shrani kot osnutek';
$labels['addattachment']  = 'Pripni datoteko';
$labels['charset']        = 'Nabor znakov';
$labels['editortype']     = 'Vrsta urejevalnika';
$labels['returnreceipt']  = 'Potrdilo prejemnika';
$labels['dsn']            = 'Status dostave sporočila';
$labels['mailreplyintro'] = '$date, je $sender napisal';
$labels['originalmessage'] = 'Izvorno sporočilo';

$labels['editidents']    = 'Uredi identitete';
$labels['spellcheck']    = 'Črkovanje';
$labels['checkspelling'] = 'Preglej pravopis';
$labels['resumeediting'] = 'Nadaljuj z urejanjem';
$labels['revertto']      = 'Razveljavi';

$labels['attach'] = 'Pripni';
$labels['attachments'] = 'Priponke';
$labels['upload'] = 'Naloži';
$labels['uploadprogress'] = '$percent ($current od $total)';
$labels['close']  = 'Zapri';
$labels['messageoptions']  = 'Možnosti';

$labels['low']     = 'Nizka';
$labels['lowest']  = 'Najnižja';
$labels['normal']  = 'Običajna';
$labels['high']    = 'Visoka';
$labels['highest'] = 'Najvišja';

$labels['nosubject']  = '(brez naslova)';
$labels['showimages'] = 'Prikaži slike';
$labels['alwaysshow'] = 'Vedno prikaži slike od $sender';
$labels['isdraft']    = 'To je osnutek sporočila.';
$labels['andnmore']   = '$nr več...';
$labels['togglemoreheaders'] = 'Prikaži več glav sporočila';
$labels['togglefullheaders'] = 'Preklopi na neobdelan zapis glave sporočila';

$labels['htmltoggle'] = 'Obogateno besedilo (HTML)';
$labels['plaintoggle'] = 'Samo besedilo';
$labels['savesentmessagein'] = 'Shrani poslana sporočila v';
$labels['dontsave'] = 'Ne shrani';
$labels['maxuploadsize'] = 'Največja dovoljena velikost datoteke je $size';

$labels['addcc'] = 'Dodaj Kp';
$labels['addbcc'] = 'Dodaj Skp';
$labels['addreplyto'] = 'Dodaj naslov za odgovor';
$labels['addfollowupto'] = 'Dodaj naslov za odgovor';

// mdn
$labels['mdnrequest'] = 'Pošiljatelj tega sporočila je zaprosil za potrdilo o branju. Želite o tem obvestiti pošiljatelja?';
$labels['receiptread'] = 'Potrdilo o branju';
$labels['yourmessage'] = 'To je potrdilo o branju.';
$labels['receiptnote'] = 'Pozor: to je samo potrdilo, da je bilo vaše sporočilo prikazano na prejemnikovem računalniku. To še ne pomeni, da je prejemnik prebral ali razumel vsebino vašega sporočila.';

// address boook
$labels['name']         = 'Ime za prikaz';
$labels['firstname']    = 'Ime';
$labels['surname']      = 'Priimek';
$labels['middlename']   = 'Drugo ime';
$labels['nameprefix']   = 'Predpona';
$labels['namesuffix']   = 'Pripona';
$labels['nickname']     = 'Vzdevek';
$labels['jobtitle']     = 'Naziv';
$labels['department']   = 'Oddelek';
$labels['gender']       = 'Spol';
$labels['maidenname']   = 'Dekliški priimek';
$labels['email']        = 'E-Pošta';
$labels['phone']        = 'Telefon';
$labels['address']      = 'Naslov';
$labels['street']       = 'Ulica';
$labels['locality']     = 'Mesto';
$labels['zipcode']      = 'Poštna številka';
$labels['region']       = 'Regija';
$labels['country']      = 'Država';
$labels['birthday']     = 'Rojstni dan';
$labels['anniversary']  = 'Rojstni dan';
$labels['website']      = 'Spletna stran';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Opombe';
$labels['male']   = 'moški';
$labels['female'] = 'ženska';
$labels['manager'] = 'Vodja';
$labels['assistant'] = 'Pomoč';
$labels['spouse'] = 'Zakonec';
$labels['allfields'] = 'Vsa polja';
$labels['search'] = 'Iskanje';
$labels['advsearch'] = 'Napredno iskanje';
$labels['advanced'] = 'Napredno';
$labels['other'] = 'Ostalo';

$labels['typehome']   = 'Doma';
$labels['typework']   = 'Služba';
$labels['typeother']  = 'Ostalo';
$labels['typemobile']  = 'Mobilni telefon';
$labels['typemain']  = 'Osnovno';
$labels['typehomefax']  = 'Domači fax';
$labels['typeworkfax']  = 'Službeni fax';
$labels['typecar']  = 'Avto';
$labels['typepager']  = 'Pozivnik';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Pomoč';
$labels['typehomepage']  = 'Domača stran';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profil';

$labels['addfield'] = 'Dodaj polje...';
$labels['addcontact'] = 'Dodaj izbrane stike v imenik';
$labels['editcontact'] = 'Uredi vizitko';
$labels['contacts'] = 'Stiki';
$labels['contactproperties'] = 'Lastnosti stika';
$labels['personalinfo'] = 'Osebni podatki';

$labels['edit']   = 'Uredi';
$labels['cancel'] = 'Prekliči';
$labels['save']   = 'Shrani';
$labels['delete'] = 'Izbriši';
$labels['rename'] = 'Preimenuj';
$labels['addphoto'] = 'Dodaj';
$labels['replacephoto'] = 'Zamenjaj';
$labels['uploadphoto'] = 'Naloži sliko';

$labels['newcontact']     = 'Dodaj vizitko';
$labels['deletecontact']  = 'Izbriši izbrane vizitke';
$labels['composeto']      = 'Sestavi sporočilo za';
$labels['contactsfromto'] = 'Vizitke $from od $to do $count';
$labels['print']          = 'Natisni';
$labels['export']         = 'Izvozi';
$labels['exportall']      = 'Izvozi vse';
$labels['exportsel']      = 'Izvozi označeno';
$labels['exportvcards']   = 'Izvozi stike v formatu vCard';
$labels['newcontactgroup'] = 'Ustvari novo skupino stikov';
$labels['grouprename']    = 'Preimenuj skupino';
$labels['groupdelete']    = 'Izbriši skupino';
$labels['groupremoveselected'] = 'Odstrani izbrane stike iz skupine';

$labels['previouspage']   = 'Prejšnja stran';
$labels['firstpage']      = 'Prva stran';
$labels['nextpage']       = 'Naslednja stran';
$labels['lastpage']       = 'Zadnja stran';

$labels['group'] = 'Skupina';
$labels['groups'] = 'Skupine';
$labels['personaladrbook'] = 'Stiki';

$labels['searchsave'] = 'Shrani iskanje';
$labels['searchdelete'] = 'Izbriši iskanje';

$labels['import'] = 'Uvozi';
$labels['importcontacts'] = 'Uvozi stike';
$labels['importfromfile'] = 'Uvozi iz datoteke:';
$labels['importtarget'] = 'Dodaj nove stike v imenik:';
$labels['importreplace'] = 'Zamenjaj celoten imenik';
$labels['importdesc'] = 'Stike lahko naložite iz obstoječega imenika. <br/>Trenutno je podprt uvoz stikov v zapisu  <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> ali v CSV (z vejico ločene vrednosti) zapisu.';
$labels['done'] = 'Dokončano';

// settings
$labels['settingsfor'] = 'Nastavitve za';
$labels['about'] = 'Vizitka';
$labels['preferences'] = 'Nastavitve';
$labels['userpreferences'] = 'Uporabniške nastavitve';
$labels['editpreferences'] = 'Uredi uporabniške nastavitve';

$labels['identities'] = 'Identitete';
$labels['manageidentities'] = 'Upravljaj identitete za ta račun';
$labels['newidentity'] = 'Nova identiteta';

$labels['newitem'] = 'Nov predmet';
$labels['edititem'] = 'Uredi predmet';

$labels['preferhtml'] = 'Prednostno HTML';
$labels['defaultcharset'] = 'Privzeto kodiranje znakov';
$labels['htmlmessage'] = 'HTML sporočilo';
$labels['messagepart'] = 'Del';
$labels['digitalsig'] = 'Digitalni podpis';
$labels['dateformat'] = 'Prikaz datuma';
$labels['timeformat'] = 'Prikaz časa';
$labels['prettydate'] = 'Bolj čitljivi datumi';
$labels['setdefault']  = 'Nastavi privzeto';
$labels['autodetect']  = 'Samodejno';
$labels['language']  = 'Jezik';
$labels['timezone']  = 'Časovni pas';
$labels['pagesize']  = 'Vrstic na stran';
$labels['signature'] = 'Podpis';
$labels['dstactive']  = 'Samodejna nastavitev zimskega/letnega časa';
$labels['showinextwin'] = 'Odpri v novem oknu';
$labels['composeextwin'] = 'Sestavi sporočilo v novem oknu';
$labels['htmleditor'] = 'Sestavi sporočila z obogatenim besedilom';
$labels['htmlonreply'] = 'le pri odgovoru na sporočila z obogatenim besedilom';
$labels['htmlonreplyandforward'] = 'le pri posredovanju ali odgovoru na sporočila z obogatenim besedilom';
$labels['htmlsignature'] = 'Podpis z obogatenim besedilom';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Prikaži predogled';
$labels['skin'] = 'Tema uporabniškega vmesnika';
$labels['logoutclear'] = 'Izprazni mapo Smeti ob odjavi';
$labels['logoutcompact'] = 'Stisni sporočila v mapi Inbox ob odjavi';
$labels['uisettings'] = 'Uporabniški vmesnik';
$labels['serversettings'] = 'Nastavitve strežnika';
$labels['mailboxview'] = 'Pogled poštnega predala';
$labels['mdnrequests'] = 'Obvestila pošiljatelja';
$labels['askuser'] = 'vprašaj uporabnika';
$labels['autosend'] = 'pošlji povratnico samodejno';
$labels['autosendknown'] = 'samodejno pošlji povratnico samo mojim stikom';
$labels['autosendknownignore'] = 'pošlji potrdilo le mojim stikom';
$labels['ignore'] = 'ne upoštevaj';
$labels['readwhendeleted'] = 'Označi izbrisano sporočilo kot prebrano';
$labels['flagfordeletion'] = 'Ne izbriši sporočila, le označi kot izbrisano';
$labels['skipdeleted'] = 'Ne prikaži izbrisanih sporočil';
$labels['deletealways'] = 'Če premikanje sporočil v Smeti spodleti, naj bodo izbrisana.';
$labels['deletejunk'] = 'Neposredno izbriši sporočila v mapi Nezaželena sporočila';
$labels['showremoteimages'] = 'Prikaži medvrstične slike';
$labels['fromknownsenders'] = 'od znanih pošiljateljev';
$labels['always'] = 'vedno';
$labels['showinlineimages'] = 'Prikaži priponke na dnu sporočila';
$labels['autosavedraft']  = 'Samodejno shrani osnutke';
$labels['everynminutes']  = '$n minut';
$labels['refreshinterval']  = 'Osveži (preglej nova sporočila)';
$labels['never']  = 'nikoli';
$labels['immediately']  = 'takoj';
$labels['messagesdisplaying'] = 'Prikazovanje sporočil';
$labels['messagescomposition'] = 'Sestavljanje sporočil';
$labels['mimeparamfolding'] = 'Imena priponk';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (ostali)';
$labels['force7bit'] = 'Uporabi MIME kodiranje za 8-bitne znake';
$labels['advancedoptions'] = 'Dodatne možnosti';
$labels['focusonnewmessage'] = 'Usmeri okno brskalnika na novo sporočilo';
$labels['checkallfolders'] = 'Preglej nova sporočila za vse mape';
$labels['displaynext'] = 'Po izbrisu/premikanju sporočila prikaži naslednje sporočilo';
$labels['defaultfont'] = 'Privzeta pisava HTML sporočil';
$labels['mainoptions'] = 'Osnovne nastavitve';
$labels['browseroptions'] = 'Možnosti brskalnika';
$labels['section'] = 'Nastavitve';
$labels['maintenance'] = 'Vzdrževanje';
$labels['newmessage'] = 'Novo sporočilo';
$labels['signatureoptions'] = 'Nastavitve podpisa';
$labels['whenreplying'] = 'Pri odgovoru';
$labels['replyempty'] = 'ne navajaj izvornega sporočila v odgovoru';
$labels['replytopposting'] = 'začni z novim sporočilom nad izvornim sporočilom';
$labels['replybottomposting'] = 'začni z novim sporočilom pod izvornim sporočilom';
$labels['replyremovesignature'] = 'Pri odgovoru na sporočilo odstrani izvorni podpis iz sporočila';
$labels['autoaddsignature'] = 'Samodejno dodaj podpis';
$labels['newmessageonly'] = 'samo novim sporočilom';
$labels['replyandforwardonly'] = 'samo k odgovorom in posredovanim sporočilom';
$labels['insertsignature'] = 'Vstavi podpis';
$labels['previewpanemarkread']  = 'Označi predogledana sporočila kot prebrana';
$labels['afternseconds']  = 'po $n sekundah';
$labels['reqmdn'] = 'Vedno zahtevaj povratnico';
$labels['reqdsn'] = 'Vedno zahtevaj obvestilo o statusu dostave';
$labels['replysamefolder'] = 'Shrani odgovore na sporočilo v mapo izvornega sporočila';
$labels['defaultabook'] = 'Privzeti stiki';
$labels['autocompletesingle'] = 'Preskoči nadomestni e-naslov pri samodejnem dokončanju';
$labels['listnamedisplay'] = 'Prikaži seznam stikov kot';
$labels['spellcheckbeforesend'] = 'Preveri črkovanje pred pošiljanjem sporočila';
$labels['spellcheckoptions'] = 'Možnosti črkovanja';
$labels['spellcheckignoresyms'] = 'Ne upoštevaj besed, ki vsebujejo simbole';
$labels['spellcheckignorenums'] = 'Ne upoštevaj besed, ki vsebujejo številke';
$labels['spellcheckignorecaps'] = 'Ne upoštevaj besed, ki vsebujejo samo velike črke';
$labels['addtodict'] = 'Dodaj v slovar';
$labels['mailtoprotohandler'] = 'Registriraj upravljavca protokola za e-naslov: povezave';
$labels['forwardmode'] = 'Posredovanje sporočil';
$labels['inline'] = 'medvrstično';
$labels['asattachment'] = 'Kot priponka';

$labels['folder']  = 'Mapa';
$labels['folders']  = 'Mape';
$labels['foldername']  = 'Ime mape';
$labels['subscribed']  = 'Vpisan';
$labels['messagecount'] = 'Sporočila';
$labels['create']  = 'Ustvari';
$labels['createfolder']  = 'Ustvari novo mapo';
$labels['managefolders']  = 'Uredi mape';
$labels['specialfolders'] = 'Posebne mape';
$labels['properties'] = 'Lastnosti';
$labels['folderproperties'] = 'Lastnosti mape';
$labels['parentfolder'] = 'Glavna mapa';
$labels['location'] = 'Lokacija';
$labels['info'] = 'Podatki';
$labels['getfoldersize'] = 'Klikni za prikaz velikosti mape';
$labels['changesubscription'] = 'Klikni za spremembo prijave mape';
$labels['foldertype'] = 'Tip mape';
$labels['personalfolder']  = 'Zasebna mapa';
$labels['otherfolder']  = 'Mapa drugega uporabnika';
$labels['sharedfolder']  = 'Javna mapa';

$labels['sortby'] = 'Uredi po';
$labels['sortasc']  = 'Uredi naraščajoče';
$labels['sortdesc'] = 'Uredi padajoče';
$labels['undo'] = 'Prekliči';

$labels['installedplugins'] = 'Nameščeni vtičniki';
$labels['plugin'] = 'Vtičnik';
$labels['version'] = 'Različica';
$labels['source'] = 'Vir';
$labels['license'] = 'Licenca';
$labels['support'] = 'Podpora';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Angleško';
$labels['westerneuropean'] = 'Zahodnoevropsko';
$labels['easterneuropean'] = 'Vzhodnoevropsko';
$labels['southeasterneuropean'] = 'JV Evropsko';
$labels['baltic'] = 'Baltsko';
$labels['cyrillic'] = 'Cirilica';
$labels['arabic'] = 'Arabsko';
$labels['greek'] = 'Grško';
$labels['hebrew'] = 'Hebrejsko';
$labels['turkish'] = 'Turško';
$labels['nordic'] = 'Nordijsko';
$labels['thai'] = 'Tajsko';
$labels['celtic'] = 'Keltsko';
$labels['vietnamese'] = 'Vietnamsko';
$labels['japanese'] = 'Japonsko';
$labels['korean'] = 'Korejsko';
$labels['chinese'] = 'Kitajsko';

?>
