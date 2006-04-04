<?php

/*
 +-----------------------------------------------------------------------+
 | language/lt/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Oleg Andrejev <ins@ins.lt>                                    |
 +-----------------------------------------------------------------------+
*/

$labels = array();

// login page
$labels['username']  = 'Vartotojas';
$labels['password']  = 'Slaptažodis';
$labels['server']    = 'Serveris';
$labels['login']     = 'Prisijungti';

// taskbar
$labels['logout']   = 'Atsijungti';
$labels['mail']     = 'El. paštas';
$labels['settings'] = 'Nustatymai';
$labels['addressbook'] = 'Adresų knygutė';

// mailbox names
$labels['inbox']  = 'Gauti laiškai';
$labels['sent']   = 'Išsiųsti laiškai';
$labels['trash']  = 'Šiūkšliadėžė';
$labels['drafts'] = 'Juodraščiai';
$labels['junk']   = 'Šlamštas';

// message listing
$labels['subject'] = 'Tema';
$labels['from']    = 'Siuntejas';
$labels['to']      = 'Gavėjas';
$labels['cc']      = 'Kopija';
$labels['bcc']     = 'Nematoma kopija';
$labels['replyto'] = 'Atsakyti į';
$labels['date']    = 'Data';
$labels['size']    = 'Dydis';
$labels['priority'] = 'Svarbumas';
$labels['organization'] = 'Organizacija';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Aplankai';
$labels['messagesfromto'] = 'Laiškai nuo $from iki $to iš $count';
$labels['messagenrof'] = 'Laiškas $nr iš $count';

$labels['moveto']   = 'perkelti į ..';
$labels['download'] = 'persisiųsti';

$labels['filename'] = 'Bylos vardas';
$labels['filesize'] = 'Bylos dydis';

$labels['preferhtml'] = 'Teikti pirmenybę HTML';
$labels['htmlmessage'] = 'HTML Laiškas';
$labels['prettydate'] = 'Gražios datos';

$labels['addtoaddressbook'] = 'Įtraukti į adresų knygą';

// weekdays short
$labels['sun'] = 'Sek';
$labels['mon'] = 'Pir';
$labels['tue'] = 'Ant';
$labels['wed'] = 'Tre';
$labels['thu'] = 'Ket';
$labels['fri'] = 'Pen';
$labels['sat'] = 'Šeš';

// weekdays long
$labels['sunday']    = 'Sekmadienis';
$labels['monday']    = 'Pirmadienis';
$labels['tuesday']   = 'Antradienis';
$labels['wednesday'] = 'Trečiadienis';
$labels['thursday']  = 'Ketvirtadienis';
$labels['friday']    = 'Penktadienis';
$labels['saturday']  = 'Šeštadienis';

$labels['today'] = 'Šiandien';

// toolbar buttons
$labels['writenewmessage']  = 'Rašyti naują laišką';
$labels['replytomessage']   = 'Atsakyti į lašką';
$labels['replytoallmessage'] = 'Aksakyti visiems';
$labels['forwardmessage']   = 'Persiųsti laiška kitiems';
$labels['deletemessage']    = 'Ištrinti laišką';
$labels['printmessage']     = 'Atspausdinti laišką';
$labels['previousmessages'] = 'Rodyti ankstesius';
$labels['nextmessages']     = 'Rodyti vėlesnius';
$labels['backtolist']       = 'Grižti į laiškų saraša';
$labels['viewsource']       = 'Rodyti šaltinį';

$labels['select'] = 'Pažymėti';
$labels['all'] = 'Viską';
$labels['none'] = 'Nieko';
$labels['unread'] = 'Neskaitytas';

$labels['compact'] = 'Suspausti';
$labels['empty'] = 'Ištuštinti';
$labels['purge'] = 'Išvalyti';

$labels['quota'] = 'Disko naudojimas';


// message compose
$labels['compose']  = 'Rašyti nauja laiška';
$labels['sendmessage']  = 'Išsiųsti laiską';
$labels['addattachment']  = 'Prisegti failą';
$labels['charset']  = 'Koduotė';

$labels['attachments'] = 'Prisegtos bylos';
$labels['upload'] = 'Įkelti';
$labels['close']  = 'Uždaryti';

$labels['low']     = 'Žemas';
$labels['lowest']  = 'Žemiausias';
$labels['normal']  = 'Normalus';
$labels['high']    = 'Aukštas';
$labels['highest'] = 'Aukščiausias';

$labels['nosubject']  = '(nėra temos)';
$labels['showimages'] = 'Rodyti paveikslėlius';


// address boook
$labels['name']      = 'Rodomas vardas';
$labels['firstname'] = 'Vardas';
$labels['surname']   = 'Pavardė';
$labels['email']     = 'El. paštas';

$labels['addcontact'] = 'Pridėti naują kontaktą';
$labels['editcontact'] = 'Redaguoti kontaktą';

$labels['edit']   = 'Redaguoti';
$labels['cancel'] = 'Atšaukti';
$labels['save']   = 'Išsaugoti';
$labels['delete'] = 'Ištrinti';

$labels['newcontact']     = 'Sukurti naują kontaktą';
$labels['addcontact']     = 'Pažymėtus kontaktus pridėti prie adresu knygutės';
$labels['deletecontact']  = 'Ištrinti pažymėtus kontaktus';
$labels['composeto']      = 'Rašyti laišką';
$labels['contactsfromto'] = 'Kontaktai nuo $from iki $to iš $count';
$labels['print']          = 'Atspausdinti';
$labels['export']         = 'Eksportuoti';

// LDAP search
$labels['ldapsearch'] = 'Paieška LDAP direktorijoi';

$labels['ldappublicsearchname']    = 'Kontakto vardas';
$labels['ldappublicsearchtype'] = 'Tikslus atitikimas?';
$labels['ldappublicserverselect'] = 'Pasirinkti serverius';
$labels['ldappublicsearchfield'] = 'Ieškoti iš';
$labels['ldappublicsearchform'] = 'Ieškoti kontakto';
$labels['ldappublicsearch'] = 'Ieškoti';


// settings
$labels['settingsfor']  = 'Nustatymai prie';

$labels['preferences']  = 'Nustatymai';
$labels['userpreferences']  = 'Vartotojo nustatymai';
$labels['editpreferences']  = 'Redaguoti vartotojo nustatymus';

$labels['identities']  = 'Tapatybės';
$labels['manageidentities']  = 'Tvarkyti tapatybės šitam abonentui';
$labels['newidentity']  = 'Nauja tapatybė';

$labels['newitem']  = 'Naujas įrašas';
$labels['edititem']  = 'Redaguoti įrašą';

$labels['setdefault']  = 'Pagrindinis pagal nutylėjimą';
$labels['language']  = 'Kalba';
$labels['timezone']  = 'Laiko juosta';
$labels['pagesize']  = 'Eilučiu kiekis per puslapį';
$labels['signature'] = 'Parašas';

$labels['folder']  = 'Aplankas';
$labels['folders']  = 'Aplankai';
$labels['foldername']  = 'Aplanko vardas';
$labels['subscribed']  = 'Užsakytas';
$labels['create']  = 'Sukurti';
$labels['createfolder']  = 'Sukurti naują aplanką';
$labels['deletefolder']  = 'Ištrinti aplanką';
$labels['managefolders']  = 'Tvarkyti aplankus';

$labels['sortby'] = 'Rūšiuoti pagal';
$labels['sortasc']  = 'Rūšiuoti didėjančia tvarka';
$labels['sortdesc'] = 'Rūšiuoti mažėjančia tvarka';

?>