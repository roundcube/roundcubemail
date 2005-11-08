<?php

/*
 +-----------------------------------------------------------------------+
 | language/fi/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Ville Alatalo <ville@alatalo.org>                             |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = 'Käyttäjätunnus';
$labels['password']  = 'Salasana';
$labels['server']    = 'Palvelin';
$labels['login']     = 'Kirjaudu';

// taskbar
$labels['logout']   = 'Kirjaudu ulos';
$labels['mail']     = 'Sähköposti';
$labels['settings'] = 'Omat asetukset';
$labels['addressbook'] = 'Osoitekirja';

// mailbox names
$labels['inbox']  = 'Saapuneet';
$labels['sent']   = 'Lähetetyt';
$labels['trash']  = 'Roskakori';
$labels['drafts'] = 'Drafts';
$labels['junk']   = 'Roskaposti';

// message listing
$labels['subject'] = 'Aihe';
$labels['from']    = 'Lähettäjä';
$labels['to']      = 'Vastaanottaja';
$labels['cc']      = 'Kopio';
$labels['bcc']     = 'Piilokopio';
$labels['replyto'] = 'Reply-To';
$labels['date']    = 'Päiväys';
$labels['size']    = 'Koko';
$labels['priority'] = 'Tärkeys';
$labels['organization'] = 'Organisaatio';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Kansiot';
$labels['messagesfromto'] = 'Viestit $from-$to/$count';
$labels['messagenrof'] = 'Viesti $nr/$count';

$labels['moveto']   = 'siirrä...';
$labels['download'] = 'lataa';

$labels['filename'] = 'Tiedoston nimi';
$labels['filesize'] = 'Tiedoston koko';

$labels['preferhtml'] = 'Käytä HTML:aa';
$labels['htmlmessage'] = 'HTML-viesti';
$labels['prettydate'] = 'Nätit päiväykset';

$labels['addtoaddressbook'] = 'Lisää osoitekirjaan';

// weekdays short
$labels['sun'] = 'Su';
$labels['mon'] = 'Ma';
$labels['tue'] = 'Ti';
$labels['wed'] = 'Ke';
$labels['thu'] = 'To';
$labels['fri'] = 'Pe';
$labels['sat'] = 'La';

// weekdays long
$labels['sunday']    = 'Sunnuntai';
$labels['monday']    = 'Maanantai';
$labels['tuesday']   = 'Tiistai';
$labels['wednesday'] = 'Keskiviikko';
$labels['thursday']  = 'Torstai';
$labels['friday']    = 'Perjantai';
$labels['saturday']  = 'Lauantai';

$labels['today'] = 'Tänään';

// toolbar buttons
$labels['writenewmessage']  = 'Kirjoita uusi viesti';
$labels['replytomessage']   = 'Vastaa viestiin';
$labels['replytoallmessage'] = 'Vastaa kaikille';
$labels['forwardmessage']   = 'Välitä viesti';
$labels['deletemessage']    = 'Siirrä viesti roskakoriin';
$labels['printmessage']     = 'Tulosta viesti';
$labels['previousmessages'] = 'Näytä edelliset viestit';
$labels['nextmessages']     = 'Näytä seuraavat viestit';
$labels['backtolist']       = 'Takaisin viesteihin';
$labels['viewsource']       = 'Näytä lähdekoodi';

$labels['select'] = 'Valitse';
$labels['all'] = 'Kaikki';
$labels['none'] = 'Ei mitään';
$labels['unread'] = 'Lukemattomat';

// message compose
$labels['compose']  = 'Viestin kirjoitus';
$labels['sendmessage']  = 'Lähetä viesti';
$labels['addattachment']  = 'Liitetiedosto';

$labels['attachments'] = 'Liitetiedostot';
$labels['upload'] = 'Lisää';
$labels['close']  = 'Sulje';

$labels['low']     = 'Matala';
$labels['lowest']  = 'Matalin';
$labels['normal']  = 'Normaali';
$labels['high']    = 'Korkea';
$labels['highest'] = 'Korkein';

$labels['showimages'] = 'Näytä kuvat';


// address boook
$labels['name']      = 'Näkyvä nimi';
$labels['firstname'] = 'Etunimi';
$labels['surname']   = 'Sukunimi';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Lisää kontakti';
$labels['editcontact'] = 'Muokkaa kontaktia';

$labels['edit']   = 'Muokkaa';
$labels['cancel'] = 'Peruuta';
$labels['save']   = 'Tallenna';
$labels['delete'] = 'Poista';

$labels['newcontact']     = 'Luo uusi kontakti';
$labels['deletecontact']  = 'Poista valitut kontaktit';
$labels['composeto']      = 'Kirjoita viesti kontaktille';
$labels['contactsfromto'] = 'Kontaktit $from-$to/$count';
$labels['print']          = 'Tulosta';
$labels['export']         = 'Vie (export)';


// settings
$labels['settingsfor']  = 'Asetukset';

$labels['preferences']  = 'Asetukset';
$labels['userpreferences']  = 'Käyttäjän asetukset';
$labels['editpreferences']  = 'Muokkaa käyttäjän asetuksia';

$labels['identities']  = 'Identiteetit';
$labels['manageidentities']  = 'Muokkaa tunnuksen identiteettejä';
$labels['newidentity']  = 'Uusi identiteetti';

$labels['newitem']  = 'Uusi';
$labels['edititem']  = 'Muokkaa';

$labels['setdefault']  = 'Aseta vakioksi';
$labels['language']  = 'Kieli';
$labels['timezone']  = 'Aikavyöhyke';
$labels['pagesize']  = 'Rivejä sivulla';


$labels['folders']  = 'Kansiot';
$labels['foldername']  = 'Kansion nimi';
$labels['subscribed']  = 'Näytetään';
$labels['create']  = 'Luo uusi';
$labels['createfolder']  = 'Luo uusi kansio';
$labels['deletefolder']  = 'Poista kansio';
$labels['managefolders']  = 'Kansioiden ylläpito';


?>