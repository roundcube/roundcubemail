<?php

/*

 +-----------------------------------------------------------------------+
 | language/en_GB/labels.inc                                             |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Weiran Zhang (weiran@weiran.co.uk)                            |
 +-----------------------------------------------------------------------+

 @version $Id: labels.inc 543 2007-04-28 18:07:12Z thomasb $

*/

$labels = array();

// login page
$labels['welcome']   = 'Vefpóstur Vodafone';
$labels['username']  = 'Netfang';
$labels['password']  = 'Lykilorð';
$labels['server']    = 'Server';
$labels['login']     = 'Innskrá';

// taskbar
$labels['logout']   = 'Útskrá';
$labels['mail']     = 'Tölvupóstur';
$labels['settings'] = 'Stillingar';
$labels['addressbook'] = 'Netfangaskrá';

// mailbox names
$labels['inbox']  = 'Innhólf';
$labels['drafts'] = 'Drög';
$labels['sent']   = 'Sent';
$labels['trash']  = 'Eyddir hlutir';
$labels['junk']   = 'Ruslpóstur';

// message listing
$labels['subject'] = 'Efni';
$labels['from']    = 'Sendandi';
$labels['to']      = 'Viðtakandi';
$labels['cc']      = 'Afrit';
$labels['bcc']     = 'Blint afrit';
$labels['replyto'] = 'Reply-To';
$labels['date']    = 'Dags';
$labels['size']    = 'Stærð';
$labels['priority'] = 'Forgangur';
$labels['organization'] = 'Stofnun';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Möppur';
$labels['messagesfromto'] = 'Skeyti $from til $to af $count';
$labels['messagenrof'] = 'Skeyti $nr af $count';

$labels['moveto']   = 'færa í...';
$labels['download'] = 'sækja';

$labels['filename'] = 'Skrárnafn';
$labels['filesize'] = 'Skrárstærð';

$labels['preferhtml'] = 'Vil HTML';
$labels['htmlmessage'] = 'HTML skeyti';
$labels['prettydate'] = 'Fallegar dagsetningar';

$labels['addtoaddressbook'] = 'Bæta í netfangaskrá';

// weekdays short
$labels['sun'] = 'sun';
$labels['mon'] = 'mán';
$labels['tue'] = 'þri';
$labels['wed'] = 'mið';
$labels['thu'] = 'fim';
$labels['fri'] = 'fös';
$labels['sat'] = 'lau';

// weekdays long
$labels['sunday']    = 'sunnudagur';
$labels['monday']    = 'mánudagur';
$labels['tuesday']   = 'þriðjudagur';
$labels['wednesday'] = 'miðvikudagur';
$labels['thursday']  = 'fimmtudagur';
$labels['friday']    = 'föstudagur';
$labels['saturday']  = 'laugardagur';

$labels['today'] = 'í dag';

// toolbar buttons
$labels['checkmail']        = 'Gá að nýjum skeytum';
$labels['writenewmessage']  = 'Búa til nýtt skeyti';
$labels['replytomessage']   = 'Svara skeyti';
$labels['replytoallmessage'] = 'Svara sendanda og öllum viðtakendum';
$labels['forwardmessage']   = 'Framsenda skeyti';
$labels['deletemessage']    = 'Færa skeyti í rusl';
$labels['printmessage']     = 'Prenta skeyti';
$labels['previousmessages'] = 'Sýna fyrri skammt af skeytum';
$labels['nextmessages']     = 'Sýna næsta skammt af skeytum';
$labels['backtolist']       = 'Til baka í skeytalista';
$labels['viewsource']       = 'Skoða kóða';

$labels['select'] = 'Velja';
$labels['all'] = 'Allt';
$labels['none'] = 'Ekkert';
$labels['unread'] = 'Ólesið';

$labels['compact'] = 'Pakka';
$labels['empty'] = 'Tæma';
$labels['purge'] = 'Hreinsa';

$labels['quota'] = 'Gagnamagn';


// message compose
$labels['compose']  = 'Semja skeyti';
$labels['savemessage']  = 'Vista drög';
$labels['sendmessage']  = 'Senda skeytið núna';
$labels['addattachment']  = 'Hengja við';
$labels['charset']  = 'Stafasett';

$labels['returnreceipt']  = 'Kvittun við lestur';

$labels['attachments'] = 'Viðhengi';
$labels['upload'] = 'Senda';
$labels['close']  = 'Loka';

$labels['low']     = 'Lágt';
$labels['lowest']  = 'Lægst';
$labels['normal']  = 'Venjulegt';
$labels['high']    = 'Hátt';
$labels['highest'] = 'Hæst';

$labels['nosubject']  = '(ekkert efni)';
$labels['showimages'] = 'Birta myndir';

$labels['editortype'] = 'Gerð ritils';
$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Einfaldur';

$labels['checkspelling'] = 'Villuleita';
$labels['resumeediting'] = 'Aftur í ritham';
$labels['revertto']      = 'Hverfa til';

$labels['addcc'] = 'Bæta við Cc';
$labels['addbcc'] = 'Bæta við Bcc';
$labels['addreplyto'] = 'Bæta við Reply-To';


// address boook
$labels['name']      = 'Nafn til birtingar';
$labels['firstname'] = 'Fornafn';
$labels['surname']   = 'Eftirnafn';
$labels['email']     = 'Netfang';
$labels['mobile']    = 'Farsími';

$labels['addcontact'] = 'Bæta við nýju';
$labels['editcontact'] = 'Lagfæra';

$labels['edit']   = 'Laga';
$labels['cancel'] = 'Hætta við';
$labels['save']   = 'Vista';
$labels['delete'] = 'Eyða';

$labels['newcontact']     = 'Búa til nýtt nafnspjald';
$labels['deletecontact']  = 'Eyða völdum netföngum';
$labels['composeto']      = 'Skrifa skeyti til';
$labels['contactsfromto'] = 'Netföng $from til $to af $count';
$labels['print']          = 'Prenta';
$labels['export']         = 'Flytja út';


// settings
$labels['settingsfor']  = 'Stillingar fyrir';

$labels['preferences']  = 'Stillingar';
$labels['userpreferences']  = 'Notendastillingar';
$labels['editpreferences']  = 'Breyta notendastillingum';

$labels['identities']  = 'Identities';
$labels['manageidentities']  = 'Manage identities for this account';
$labels['newidentity']  = 'New identity';

$labels['newitem']  = 'Nýtt';
$labels['edititem']  = 'Laga';

$labels['setdefault']  = 'Gera sjálfgilt';
$labels['language']  = 'Tungumál';
$labels['timezone']  = 'Tímabelti';
$labels['pagesize']  = 'Línur á síðu';
$labels['signature'] = 'Undirskrift';
$labels['dstactive']  = 'Sumar-/vetratími';
$labels['htmleditor'] = 'Skrifa HTML-skeyti';
$labels['htmlsignature'] = 'HTML-undirskrift';
$labels['previewpane'] = 'Sýna forskoðunarglugga';

$labels['autosavedraft']  = 'Vista sjálfkrafa sem drög';
$labels['everynminutes']  = 'á $n mínútna fresti';
$labels['never']  = 'aldrei';

$labels['folder']  = 'Mappa';
$labels['folders']  = 'Möppur';
$labels['foldername']  = 'Nafn möppu';
$labels['subscribed']  = 'Valin';
$labels['create']  = 'Búa til';
$labels['createfolder']  = 'Búa til nýja möppu';
$labels['deletefolder']  = 'Eyða möppu';
$labels['managefolders']  = 'Sýsla með möppum';
$labels['renamefolder'] = 'Breyta nafni möppu';



$labels['sortby'] = 'Raða eftir';
$labels['sortasc']  = 'Raða vaxandi';
$labels['sortdesc'] = 'Raða minnkandi';

$labels['changepassword'] = 'Breyta lykilorði';
$labels['changepasswordinfo'] = 'Sláðu inn núverandi lykilorð þitt og svo nýtt lykilorð. Nýja lykilorðið þarf að slá inn tvisvar í staðfestingarskyni';
$labels['oldpass'] = 'Núverandi lykilorð';
$labels['newpass'] = 'Nýtt lykilorð';
$labels['newpassconfirm'] = 'Staðfesting nýs lykilorðs';
$labels['oldpasserror'] = 'Lykilorðið sem slegið var inn er ekki rétt';
$labels['passlengtherror'] = 'Lykilorðið þarf að vera 6 stafir eða lengra';
$labels['confirmpasserror'] = 'Nýja lykilorðið og staðfestingin eru ekki eins';


?>