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
$labels['welcome']   = 'Välkommen till $product';
$labels['username']  = 'Användarnamn';
$labels['password']  = 'Lösenord';
$labels['server']    = 'Server';
$labels['login']     = 'Logga in';

// taskbar
$labels['logout']   = 'Logga ut';
$labels['mail']     = 'Meddelanden';
$labels['settings'] = 'Inställningar';
$labels['addressbook'] = 'Adressbok';

// mailbox names
$labels['inbox']  = 'Inkorg';
$labels['drafts'] = 'Utkast';
$labels['sent']   = 'Skickat';
$labels['trash']  = 'Papperskorg';
$labels['junk']   = 'Skräp';

// message listing
$labels['subject'] = 'Ämne';
$labels['from']    = 'Avsändare';
$labels['sender']  = 'Avsändare';
$labels['to']      = 'Mottagare';
$labels['cc']      = 'Kopia';
$labels['bcc']     = 'Dold kopia';
$labels['replyto'] = 'Svar till';
$labels['followupto'] = 'Uppföljning till';
$labels['date']    = 'Datum';
$labels['size']    = 'Storlek';
$labels['priority'] = 'Prioritet';
$labels['organization'] = 'Organisation';
$labels['readstatus'] = 'Lässtatus';
$labels['listoptions'] = 'Listalternativ...';

$labels['mailboxlist'] = 'Kataloger';
$labels['messagesfromto'] = 'Meddelande $from till $to av $count';
$labels['threadsfromto'] = 'Tråd $from till $to av $count';
$labels['messagenrof'] = 'Meddelande $nr av $count';
$labels['fromtoshort'] = '$from – $to av $count';

$labels['copy']     = 'Kopiera';
$labels['move']     = 'Flytta';
$labels['moveto']   = 'Flytta till:';
$labels['download'] = 'Ladda ner';
$labels['showattachment'] = 'Visa';
$labels['showanyway'] = 'Visa det ändå';

$labels['filename'] = 'Filnamn';
$labels['filesize'] = 'Filstorlek';

$labels['addtoaddressbook'] = 'Lägg till i adressbok';

// weekdays short
$labels['sun'] = 'sön';
$labels['mon'] = 'mån';
$labels['tue'] = 'tis';
$labels['wed'] = 'ons';
$labels['thu'] = 'tor';
$labels['fri'] = 'fre';
$labels['sat'] = 'lör';

// weekdays long
$labels['sunday']    = 'söndag';
$labels['monday']    = 'måndag';
$labels['tuesday']   = 'tisdag';
$labels['wednesday'] = 'onsdag';
$labels['thursday']  = 'torsdag';
$labels['friday']    = 'fredag';
$labels['saturday']  = 'lördag';

// months short
$labels['jan']	= 'jan';
$labels['feb']	= 'feb';
$labels['mar']	= 'mar';
$labels['apr']	= 'apr';
$labels['may']	= 'maj';
$labels['jun']	= 'jun';
$labels['jul'] 	= 'jul';
$labels['aug']	= 'aug';
$labels['sep']	= 'sep';
$labels['oct']	= 'okt';
$labels['nov']	= 'nov';
$labels['dec']	= 'dec';

// months long
$labels['longjan']	= 'januari';
$labels['longfeb']	= 'februari';
$labels['longmar']	= 'mars';
$labels['longapr']	= 'april';
$labels['longmay']	= 'maj';
$labels['longjun']	= 'juni';
$labels['longjul']	= 'juli';
$labels['longaug']	= 'augusti';
$labels['longsep']	= 'september';
$labels['longoct']	= 'oktober';
$labels['longnov']	= 'november';
$labels['longdec']	= 'december';

$labels['today'] = 'idag';

// toolbar buttons
$labels['refresh']          = 'Uppdatera';
$labels['checkmail']        = 'Hämta nya meddelanden';
$labels['compose']          = 'Nytt meddelande';
$labels['writenewmessage']  = 'Nytt meddelande';
$labels['reply']            = 'Svara';
$labels['replytomessage']   = 'Svara avsändaren';
$labels['replytoallmessage'] = 'Svara avsändaren och alla mottagare';
$labels['replyall']         = 'Svara alla';
$labels['replylist']        = 'Svara lista';
$labels['forward']          = 'Vidarebefordra';
$labels['forwardinline']    = 'Vidarebefordra infogat';
$labels['forwardattachment'] = 'Vidarebefordra som bilaga';
$labels['forwardmessage']   = 'Vidarebefordra meddelande';
$labels['deletemessage']    = 'Ta bort meddelande';
$labels['movemessagetotrash'] = 'Flytta meddelande till papperskorgen';
$labels['printmessage']     = 'Skriv ut';
$labels['previousmessage']  = 'Visa föregående meddelande';
$labels['firstmessage']     = 'Visa första meddelandet';
$labels['nextmessage']      = 'Visa nästa meddelande';
$labels['lastmessage']      = 'Visa sista meddelandet';
$labels['backtolist']       = 'Tillbaka till meddelandelistan';
$labels['viewsource']       = 'Visa källkod';
$labels['mark']             = 'Märk';
$labels['markmessages']     = 'Märk meddelande';
$labels['markread']         = 'Läst';
$labels['markunread']       = 'Oläst';
$labels['markflagged']      = 'Flaggat';
$labels['markunflagged']    = 'Oflaggat';
$labels['moreactions']      = 'Fler åtgärder';
$labels['more']             = 'Fler';
$labels['back']             = 'Tillbaka';
$labels['options']          = 'Alternativ';

$labels['select'] = 'Välj';
$labels['all'] = 'Alla';
$labels['none'] = 'Ingen';
$labels['currpage'] = 'Sida';
$labels['unread'] = 'Olästa';
$labels['flagged'] = 'Flaggade';
$labels['unanswered'] = 'Obesvarade';
$labels['deleted'] = 'Borttagna';
$labels['undeleted'] = 'Inte borttaget';
$labels['invert'] = 'Invertera';
$labels['filter'] = 'Filter';
$labels['list'] = 'Lista';
$labels['threads'] = 'Trådar';
$labels['expand-all'] = 'Visa alla';
$labels['expand-unread'] = 'Visa olästa';
$labels['collapse-all'] = 'Dölj alla';
$labels['threaded'] = 'Trådat';

$labels['autoexpand_threads'] = 'Visa trådade meddelanden';
$labels['do_expand'] = 'Alla trådar';
$labels['expand_only_unread'] = 'Trådar med olästa meddelanden';
$labels['fromto'] = 'Avsändare/Mottagare';
$labels['flag'] = 'Flagga';
$labels['attachment'] = 'Bilaga';
$labels['nonesort'] = 'Ingen';
$labels['sentdate'] = 'Datum skickat';
$labels['arrival'] = 'Datum mottaget';
$labels['asc'] = 'Stigande';
$labels['desc'] = 'Fallande';
$labels['listcolumns'] = 'Kolumner';
$labels['listsorting'] = 'Sortering';
$labels['listorder'] = 'Ordning';
$labels['listmode'] = 'Visningsläge';

$labels['folderactions'] = 'Hantera kataloger';
$labels['compact'] = 'Packa';
$labels['empty'] = 'Töm';

$labels['quota'] = 'Diskutrymme';
$labels['unknown']  = 'okänt';
$labels['unlimited']  = 'obegränsat';

$labels['quicksearch']  = 'Snabbsök';
$labels['resetsearch']  = 'Återställ sökning';
$labels['searchmod']  = 'Sökalternativ';
$labels['msgtext']  = 'Hela meddelandet';
$labels['body']  = 'Innehåll';

$labels['openinextwin'] = 'Öppna i nytt fönster';
$labels['emlsave'] = 'Spara (.eml)';

// message compose
$labels['editasnew']      = 'Redigera som nytt';
$labels['send']           = 'Skicka';
$labels['sendmessage']    = 'Skicka meddelande';
$labels['savemessage']    = 'Spara utkast';
$labels['addattachment']  = 'Bifoga fil';
$labels['charset']        = 'Teckenkodning';
$labels['editortype']     = 'Textredigerare';
$labels['returnreceipt']  = 'Mottagarkvitto';
$labels['dsn']            = 'Leveransstatusmeddelande';
$labels['mailreplyintro'] = '$date skrev $sender:';
$labels['originalmessage'] = 'Ursprungligt meddelande';

$labels['editidents']    = 'Ändra identiteter';
$labels['spellcheck']    = 'Rättstava';
$labels['checkspelling'] = 'Kontrollera stavning';
$labels['resumeediting'] = 'Återuppta redigering';
$labels['revertto']      = 'Återgå till';

$labels['attach'] = 'Bifoga';
$labels['attachments'] = 'Bilagor';
$labels['upload'] = 'Bifoga';
$labels['uploadprogress'] = '$percent ($current av $total)';
$labels['close']  = 'Stäng';
$labels['messageoptions']  = 'Meddelandealternativ';

$labels['low']     = 'Låg';
$labels['lowest']  = 'Lägst';
$labels['normal']  = 'Normal';
$labels['high']    = 'Hög';
$labels['highest'] = 'Högst';

$labels['nosubject']  = '(inget ämne)';
$labels['showimages'] = 'Visa bilder';
$labels['alwaysshow'] = 'Visa alltid bilder från $sender';
$labels['isdraft']    = 'Detta meddelande är ett utkast.';
$labels['andnmore']   = '$nr fler...';
$labels['togglemoreheaders'] = 'Visa fler meddelandehuvuden';
$labels['togglefullheaders'] = 'Växla meddelandehuvuden';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Text';
$labels['savesentmessagein'] = 'Spara kopia i';
$labels['dontsave'] = 'Spara inte';
$labels['maxuploadsize'] = 'Tillåten filstorlek är $size';

$labels['addcc'] = 'Kopia';
$labels['addbcc'] = 'Dold kopia';
$labels['addreplyto'] = 'Svar till';
$labels['addfollowupto'] = 'Uppföljning till';

// mdn
$labels['mdnrequest'] = 'Avsändaren har begärt mottagarkvitto för det här meddelandet. Vill du skicka ett mottagarkvitto?';
$labels['receiptread'] = 'Mottagarkvitto (läst)';
$labels['yourmessage'] = 'Det här är ett mottagarkvitto för ditt meddelande';
$labels['receiptnote'] = 'Notera: Mottagarkvittot innebär bara att meddelandet visats på mottagarens dator. Därmed finns ingen garanti att mottagaren har läst och förstått meddelandet.';

// address boook
$labels['name']         = 'Namn';
$labels['firstname']    = 'Förnamn';
$labels['surname']      = 'Efternamn';
$labels['middlename']   = 'Mellannamn';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Smeknamn';
$labels['jobtitle']     = 'Titel';
$labels['department']   = 'Avdelning';
$labels['gender']       = 'Kön';
$labels['maidenname']   = 'Flicknamn';
$labels['email']        = 'E-post';
$labels['phone']        = 'Telefon';
$labels['address']      = 'Adress';
$labels['street']       = 'Gata';
$labels['locality']     = 'Ort';
$labels['zipcode']      = 'Postnummer';
$labels['region']       = 'Region';
$labels['country']      = 'Land';
$labels['birthday']     = 'Födelsedag';
$labels['anniversary']  = 'Årsdag';
$labels['website']      = 'Webbplats';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Anteckningar';
$labels['male']   = 'Man';
$labels['female'] = 'Kvinna';
$labels['manager'] = 'Chef';
$labels['assistant'] = 'Assistent';
$labels['spouse'] = 'Make/Maka';
$labels['allfields'] = 'Alla fält';
$labels['search'] = 'Sök';
$labels['advsearch'] = 'Avancerad sökning';
$labels['advanced'] = 'Avancerad';
$labels['other'] = 'Annat';

$labels['typehome']   = 'Hem';
$labels['typework']   = 'Arbete';
$labels['typeother']  = 'Annat';
$labels['typemobile']  = 'Mobil';
$labels['typemain']  = 'Huvudsakligt';
$labels['typehomefax']  = 'Fax hem';
$labels['typeworkfax']  = 'Fax arbete';
$labels['typecar']  = 'Bil';
$labels['typepager']  = 'Personsökare';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistent';
$labels['typehomepage']  = 'Webbsida';
$labels['typeblog'] = 'Blogg';
$labels['typeprofile'] = 'Profil';

$labels['addfield'] = 'Lägg till fält...';
$labels['addcontact'] = 'Lägg till ny kontakt';
$labels['editcontact'] = 'Ändra kontakt';
$labels['contacts'] = 'Kontakter';
$labels['contactproperties'] = 'Kontaktegenskaper';
$labels['personalinfo'] = 'Personlig information';

$labels['edit']   = 'Redigera';
$labels['cancel'] = 'Avbryt';
$labels['save']   = 'Spara';
$labels['delete'] = 'Ta bort';
$labels['rename'] = 'Ändra namn';
$labels['addphoto'] = 'Lägg till';
$labels['replacephoto'] = 'Ersätt';
$labels['uploadphoto'] = 'Ladda upp foto';

$labels['newcontact']     = 'Ny kontakt';
$labels['deletecontact']  = 'Ta bort vald kontakt';
$labels['composeto']      = 'Nytt meddelande till';
$labels['contactsfromto'] = 'Kontakt $from till $to av $count';
$labels['print']          = 'Skriv ut';
$labels['export']         = 'Exportera';
$labels['exportall']      = 'Exportera alla';
$labels['exportsel']      = 'Exportera markerade';
$labels['exportvcards']   = 'Exportera kontakter';
$labels['newcontactgroup'] = 'Ny kontaktgrupp';
$labels['grouprename']    = 'Ändra gruppnamn';
$labels['groupdelete']    = 'Ta bort grupp';
$labels['groupremoveselected'] = 'Ta bort markerade kontakter från gruppen';

$labels['previouspage']   = 'Visa föregående sida';
$labels['firstpage']      = 'Visa första sidan';
$labels['nextpage']       = 'Visa nästa sida';
$labels['lastpage']       = 'Visa sista sidan';

$labels['group'] = 'Grupp';
$labels['groups'] = 'Kontaktgrupper';
$labels['personaladrbook'] = 'Personliga adresser';

$labels['searchsave'] = 'Lägg till sökning';
$labels['searchdelete'] = 'Ta bort sökning';

$labels['import'] = 'Importera';
$labels['importcontacts'] = 'Importera kontakter';
$labels['importfromfile'] = 'Importera från fil:';
$labels['importtarget'] = 'Lägg till nya kontakter till adressbok:';
$labels['importreplace'] = 'Ersätt samtliga kontakter i adressboken';
$labels['importdesc'] = 'Kontakter kan importeras från en befintlig adressbok.<br/>Programmet stödjer inläsning av kontakter i formatet <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> eller formatet CSV (kommaseparerad data).';
$labels['done'] = 'Klart';

// settings
$labels['settingsfor'] = 'Inställningar för';
$labels['about'] = 'Om';
$labels['preferences'] = 'Inställningar';
$labels['userpreferences'] = 'Användarinställningar';
$labels['editpreferences'] = 'Ändra användarinställningar';

$labels['identities'] = 'Identiteter';
$labels['manageidentities'] = 'Hantera identiteter';
$labels['newidentity'] = 'Ny identitet';

$labels['newitem'] = 'Ny information';
$labels['edititem'] = 'Ändra information';

$labels['preferhtml'] = 'Visa meddelanden i HTML-format';
$labels['defaultcharset'] = 'Förvald teckenkodning';
$labels['htmlmessage'] = 'HTML-meddelande';
$labels['messagepart'] = 'Del';
$labels['digitalsig'] = 'Digital signatur';
$labels['dateformat'] = 'Datumformat';
$labels['timeformat'] = 'Tidformat';
$labels['prettydate'] = 'Fina datum';
$labels['setdefault']  = 'Sätt som förval';
$labels['autodetect']  = 'Automatiskt';
$labels['language']  = 'Språk';
$labels['timezone']  = 'Tidszon';
$labels['pagesize']  = 'Rader per sida';
$labels['signature'] = 'Signatur';
$labels['dstactive']  = 'Sommartid';
$labels['showinextwin'] = 'Öppna i nytt fönster';
$labels['composeextwin'] = 'Skriv i nytt fönster';
$labels['htmleditor'] = 'Skriv meddelanden i HTML-format';
$labels['htmlonreply'] = 'Endast vid svar på meddelande i HTML-format';
$labels['htmlonreplyandforward'] = 'Vid svar på eller vidarebefordran av meddelande i HTML-format';
$labels['htmlsignature'] = 'HTML-signatur';
$labels['previewpane'] = 'Visa meddelandefältet';
$labels['skin'] = 'Stilmall för användargränssnitt';
$labels['logoutclear'] = 'Töm papperskorgen vid utloggning';
$labels['logoutcompact'] = 'Packa inkorgen vid utloggning';
$labels['uisettings'] = 'Användargränssnitt';
$labels['serversettings'] = 'Serverinställningar';
$labels['mailboxview'] = 'Hantering av meddelanden';
$labels['mdnrequests'] = 'Skicka mottagarkvitton';
$labels['askuser'] = 'Fråga';
$labels['autosend'] = 'Automatiskt';
$labels['autosendknown'] = 'Skicka kvitto till mina kontakter, fråga för övriga';
$labels['autosendknownignore'] = 'Skicka kvitto till mina kontakter, ignorera övriga';
$labels['ignore'] = 'Ignorera';
$labels['readwhendeleted'] = 'Märk meddelande som läst vid borttagning';
$labels['flagfordeletion'] = 'Flagga meddelande för borttagning istället för att ta bort direkt';
$labels['skipdeleted'] = 'Visa inte borttagna meddelanden';
$labels['deletealways'] = 'Ta bort meddelande om flyttning till Papperskorg misslyckas';
$labels['deletejunk'] = 'Radera meddelanden i Skräp direkt';
$labels['showremoteimages'] = 'Visa externt länkade bilder i meddelande';
$labels['fromknownsenders'] = 'Från kända avsändare';
$labels['always'] = 'Alltid';
$labels['showinlineimages'] = 'Visa bifogade bilder efter meddelande';
$labels['autosavedraft']  = 'Spara utkast med automatiskt intervall';
$labels['everynminutes']  = '$n min';
$labels['refreshinterval']  = 'Uppdatera (hämta nya meddelanden, etc.)';
$labels['never']  = 'Aldrig';
$labels['immediately']  = 'Direkt';
$labels['messagesdisplaying'] = 'Visning av meddelanden';
$labels['messagescomposition'] = 'Redigering av meddelanden';
$labels['mimeparamfolding'] = 'Kodning av namn på bifogade filer';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (övriga)';
$labels['force7bit'] = 'Använd MIME-kodning för tecken med 8-bitar';
$labels['advancedoptions'] = 'Avancerade inställningar';
$labels['focusonnewmessage'] = 'Fokusera webbläsarens fönster vid nytt meddelande';
$labels['checkallfolders'] = 'Genomsök samtliga kataloger efter nya meddelanden';
$labels['displaynext'] = 'Visa nästa meddelande efter borttagning/flyttning av meddelande';
$labels['defaultfont'] = 'Förvalt typsnitt i HTML-meddelande';
$labels['mainoptions'] = 'Huvudalternativ';
$labels['browseroptions'] = 'Webbläsaralternativ';
$labels['section'] = 'Avdelning';
$labels['maintenance'] = 'Underhåll';
$labels['newmessage'] = 'Nytt meddelande';
$labels['signatureoptions'] = 'Signaturalternativ';
$labels['whenreplying'] = 'Vid svar fortsätt skriv';
$labels['replyempty'] = 'Inkludera inte ursprungligt meddelande';
$labels['replytopposting'] = 'Ovanför befintligt meddelande';
$labels['replybottomposting'] = 'Nedanför befintligt meddelande';
$labels['replyremovesignature'] = 'Ta bort befintlig signatur från meddelandet vid svar';
$labels['autoaddsignature'] = 'Infoga signatur automatiskt';
$labels['newmessageonly'] = 'Vid nytt meddelande';
$labels['replyandforwardonly'] = 'Vid svar och vidarebefordran';
$labels['replysignaturepos'] = 'Vid svar eller vidarebefordran infoga signatur';
$labels['belowquote'] = 'Nedanför befintligt meddelande';
$labels['abovequote'] = 'Ovanför befintligt meddelande';
$labels['insertsignature'] = 'Infoga signatur';
$labels['previewpanemarkread']  = 'Märk meddelande som läst vid visning';
$labels['afternseconds']  = 'Efter $n sekunder';
$labels['reqmdn'] = 'Begär alltid mottagarkvitto';
$labels['reqdsn'] = 'Begär alltid leveransstatusmeddelande';
$labels['replysamefolder'] = 'Placera svar i samma katalog som besvarat meddelande';
$labels['defaultabook'] = 'Förvald adressbok';
$labels['autocompletesingle'] = 'Hoppa över alternativa adresser vid automatisk komplettering';
$labels['listnamedisplay'] = 'Lista kontakter som';
$labels['spellcheckbeforesend'] = 'Kontrollera stavning innan meddelanden skickas';
$labels['spellcheckoptions'] = 'Rättstavningsalternativ';
$labels['spellcheckignoresyms'] = 'Ignorera ord med symboler';
$labels['spellcheckignorenums'] = 'Ignorera ord med siffror';
$labels['spellcheckignorecaps'] = 'Ignorera ord med enbart stora bokstäver';
$labels['addtodict'] = 'Lägg till i ordlista';
$labels['mailtoprotohandler'] = 'Ange program för hantering av mailto-länkar';
$labels['forwardmode'] = 'Vidarebefordra meddelande';
$labels['inline'] = 'Infogat';
$labels['asattachment'] = 'Bilaga';

$labels['folder']  = 'Katalog';
$labels['folders']  = 'Kataloger';
$labels['foldername']  = 'Katalognamn';
$labels['subscribed']  = 'Prenumererad';
$labels['messagecount'] = 'Meddelanden';
$labels['create']  = 'Skapa';
$labels['createfolder']  = 'Ny katalog';
$labels['managefolders']  = 'Hantera kataloger';
$labels['specialfolders'] = 'Speciella kataloger';
$labels['properties'] = 'Egenskaper';
$labels['folderproperties'] = 'Katalogegenskaper';
$labels['parentfolder'] = 'Överliggande katalog';
$labels['location'] = 'Plats';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Beräkna katalogstorlek';
$labels['changesubscription'] = 'Klicka för att ändra prenumeration';
$labels['foldertype'] = 'Katalogtyp';
$labels['personalfolder']  = 'Privat katalog';
$labels['otherfolder']  = 'Annan användares katalog';
$labels['sharedfolder']  = 'Publik katalog';

$labels['sortby'] = 'Sortera på';
$labels['sortasc']  = 'Sortera stigande';
$labels['sortdesc'] = 'Sortera fallande';
$labels['undo'] = 'Ångra';

$labels['installedplugins'] = 'Installerade tillägg';
$labels['plugin'] = 'Tillägg';
$labels['version'] = 'Version';
$labels['source'] = 'Källkod';
$labels['license'] = 'Licens';
$labels['support'] = 'Hjälp';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'Engelsk';
$labels['westerneuropean'] = 'Västeuropeisk';
$labels['easterneuropean'] = 'Östeuropeisk';
$labels['southeasterneuropean'] = 'Sydösteuropeisk';
$labels['baltic'] = 'Baltisk';
$labels['cyrillic'] = 'Kyrillisk';
$labels['arabic'] = 'Arabisk';
$labels['greek'] = 'Grekisk';
$labels['hebrew'] = 'Hebreisk';
$labels['turkish'] = 'Turkisk';
$labels['nordic'] = 'Skandinavisk';
$labels['thai'] = 'Thailändsk';
$labels['celtic'] = 'Keltisk';
$labels['vietnamese'] = 'Vietnamesisk';
$labels['japanese'] = 'Japansk';
$labels['korean'] = 'Koreansk';
$labels['chinese'] = 'Kinesisk';

?>
