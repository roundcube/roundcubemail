<?php

/*
 +-----------------------------------------------------------------------+
 | language/ro/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Daniel Anechitoaie - danieLs  <daniels@safereaction.ro>       |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Welcome to Roundcube|Mail';
$labels['username']  = 'Utilizator';
$labels['password']  = 'Parola';
$labels['server']    = 'Server';
$labels['login']     = 'Autentificare';

// taskbar
$labels['logout']   = 'Deconectare';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Setari Personale';
$labels['addressbook'] = 'Agenda';

// mailbox names
$labels['inbox']  = 'Primite';
$labels['sent']   = 'Trimise';
$labels['trash']  = 'Gunoi';
$labels['drafts'] = 'Ciorne';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Subiect';
$labels['from']    = 'Expeditor';
$labels['to']      = 'Destinatar';
$labels['cc']      = 'Copie';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Raspunde-La';
$labels['date']    = 'Data';
$labels['size']    = 'Marime';
$labels['priority'] = 'Prioritate';
$labels['organization'] = 'Organizatie';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Dosare';
$labels['messagesfromto'] = 'Mesaje de la $from pana la $to din $count';
$labels['messagenrof'] = '$nr mesaje din $count';

$labels['moveto']   = 'muta in...';
$labels['download'] = 'descarca';

$labels['filename'] = 'Nume fisier';
$labels['filesize'] = 'Marime fisier';

$labels['preferhtml'] = 'Prefer HTML';
$labels['htmlmessage'] = 'Mesaj HTML';
$labels['prettydate'] = 'Data formatata';

$labels['addtoaddressbook'] = 'Adauga in agenda';

// weekdays short
$labels['sun'] = 'Dum';
$labels['mon'] = 'Lun';
$labels['tue'] = 'Mar';
$labels['wed'] = 'Mie';
$labels['thu'] = 'Joi';
$labels['fri'] = 'Vin';
$labels['sat'] = 'Sam';

// weekdays long
$labels['sunday']    = 'Duminica';
$labels['monday']    = 'Luni';
$labels['tuesday']   = 'Marti';
$labels['wednesday'] = 'Miercuri';
$labels['thursday']  = 'Joi';
$labels['friday']    = 'Vineri';
$labels['saturday']  = 'Sambata';

$labels['today'] = 'Astazi';

// toolbar buttons
$labels['writenewmessage']  = 'Creaza mesaj nou';
$labels['replytomessage']   = 'Raspunde la mesaj';
$labels['replytoallmessage'] = 'Raspunde la toti';
$labels['forwardmessage']   = 'Trimite mesajul mai departe';
$labels['deletemessage']    = 'Trimmite mesajul la gunoi';
$labels['printmessage']     = 'Listeaza mesajul';
$labels['previousmessages'] = 'Afiseaza setul anterior de mesaje';
$labels['nextmessages']     = 'Afiseaza setul urmator de mesaje';
$labels['backtolist']       = 'Inapoi la lista cu mesaje';
$labels['viewsource']       = 'Afiseaza sursa';

$labels['select'] = 'Selecteaza';
$labels['all'] = 'Toate';
$labels['none'] = 'Nici unul';
$labels['unread'] = 'Necitite';

$labels['compact'] = 'Compreseaza';
$labels['empty'] = 'Goleste';
$labels['purge'] = 'Curata';

$labels['quota'] = 'Spatiu folosit';


// message compose
$labels['compose']  = 'Compune mesaj';
$labels['sendmessage']  = 'Trimite mesaj';
$labels['addattachment']  = 'Ataseaza fisier';
$labels['charset']  = 'Set de caractere';

$labels['attachments'] = 'Atasamente';
$labels['upload'] = 'Incarca';
$labels['close']  = 'Inchide';

$labels['low']     = 'Mica';
$labels['lowest']  = 'Cea mai mica';
$labels['normal']  = 'Normala';
$labels['high']    = 'Mare';
$labels['highest'] = 'Cea mai mare';

$labels['nosubject']  = '(fara subiect)';
$labels['showimages'] = 'Afiseaza imagini';


// address boook
$labels['name']      = 'Nume de afisat';
$labels['firstname'] = 'Nume';
$labels['surname']   = 'Prenume';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Adauga contact nou';
$labels['editcontact'] = 'Modifica contact';

$labels['edit']   = 'Editeaza';
$labels['cancel'] = 'Renunta';
$labels['save']   = 'Salveaza';
$labels['delete'] = 'Sterge';

$labels['newcontact']     = 'Creaza contact nou';
$labels['addcontact']     = 'Adauga contactul selectat in agenda';
$labels['deletecontact']  = 'Sterge contactul selectat';
$labels['composeto']      = 'Compune e-mail pentru';
$labels['contactsfromto'] = 'Contacte de la $from pana la $to din $count';
$labels['print']          = 'Listeaza';
$labels['export']         = 'Exporta';

// LDAP search
$labels['ldapsearch'] = 'Cautare director LDAP';

$labels['ldappublicsearchname']    = 'Nume contact';
$labels['ldappublicsearchtype'] = 'Potrivire exacta?';
$labels['ldappublicserverselect'] = 'Selecteaza server';
$labels['ldappublicsearchfield'] = 'Cauta dupa';
$labels['ldappublicsearchform'] = 'Cauta contact';
$labels['ldappublicsearch'] = 'Cauta';


// settings
$labels['settingsfor']  = 'Setari pentru';

$labels['preferences']  = 'Preferinte';
$labels['userpreferences']  = 'Preferinte utilizator';
$labels['editpreferences']  = 'Modifica preferinte utilizator';

$labels['identities']  = 'Identitati';
$labels['manageidentities']  = 'Administreaza identitati pentru acest cont';
$labels['newidentity']  = 'Identitate noua';

$labels['newitem']  = 'Item nou';
$labels['edititem']  = 'Editeaza item';

$labels['setdefault']  = 'Seteaza implicit';
$labels['language']  = 'Limba';
$labels['timezone']  = 'Fus orar';
$labels['pagesize']  = 'Randuri pe pagina';
$labels['signature'] = 'Semnatura';

$labels['folder']  = 'Dosar';
$labels['folders']  = 'Dosare';
$labels['foldername']  = 'Nume dosar';
$labels['subscribed']  = 'Inscris';
$labels['create']  = 'Creaza';
$labels['createfolder']  = 'Creaza dosar nou';
$labels['deletefolder']  = 'Sterge dosar';
$labels['managefolders']  = 'Administreaza dosare';

$labels['sortby'] = 'Sorteaza dupa';
$labels['sortasc']  = 'Sorteaza ascendent';
$labels['sortdesc'] = 'Sorteaza descendent';

?>
