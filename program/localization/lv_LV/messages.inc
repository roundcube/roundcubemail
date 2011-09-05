<?php

/*

+-----------------------------------------------------------------------+
| language/lv/messages.inc                                              |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Rūdolfs Ošiņš <dev.random@gmail.com>                          |
|         Miķelis Zaļais <mik@prog.lv>                                  |
|         Lauris Bukšis-Haberkorns <lauris@nix.lv>                      |
|         Kaspars Tenters <kaspars.tenters@va.lv>                       |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Neizdevās pieslēgties';
$messages['cookiesdisabled'] = 'Jūsu pārlūkprogramma neatbalsta sīkdatnes (cookies)';
$messages['sessionerror'] = 'Jūsu sessija ir beigusies';
$messages['imaperror'] = 'Neizdevās pieslēgties IMAP serverim';
$messages['servererror'] = 'Servera kļūme.';
$messages['servererrormsg'] = 'Servera kļūda: $msg';
$messages['databaserror'] = 'Datubāzes kļūda!';
$messages['errorreadonly'] = 'Neizdevās veikt darbību: Mape ir tikai lasāma (read only).';
$messages['errornoperm'] = 'Neizdevās veikt darbību: Piekļuve liegta.';
$messages['invalidrequest'] = 'Nederīgs pieprasījums. Dati netika saglabāti.';
$messages['nomessagesfound'] = 'Šajā pastkastē nav vēstuļu';
$messages['loggedout'] = 'Jūs esat veiksmīgi atslēdzies no sistēmas';
$messages['mailboxempty'] = 'Pastkaste tukša';
$messages['loading'] = 'Notiek ielāde...';
$messages['uploading'] = 'Notiek faila augšupielāde...';
$messages['uploadingmany'] = 'Augšupielādē failus...';
$messages['loadingdata'] = 'Tiek ielādēti dati...';
$messages['checkingmail'] = 'Notiek pasta pārbaude...';
$messages['sendingmessage'] = 'Tiek sūtīta vēstule...';
$messages['messagesent'] = 'Vēstule nosūtīta veiksmīgi';
$messages['savingmessage'] = 'Vēstule tiek saglabāta...';
$messages['messagesaved'] = 'Vēstule saglabāta pie uzmetumiem';
$messages['successfullysaved'] = 'Iestatījumi veiksmīgi saglabāti';
$messages['addedsuccessfully'] = 'Ieraksts veiksmīgi pievienots adrešu grāmatai';
$messages['contactexists'] = 'Ieraksts ar šādu e-pasta adresi jau eksistē';
$messages['contactnameexists'] = 'Kontakts ar šādu vārdu jau eksistē.';
$messages['blockedimages'] = 'Drošības nolūkos attēli, kas tiek ielādēti no cita servera, šajā vēstulē ir bloķēti';
$messages['encryptedmessage'] = 'Šī ir šifrēta vēstule un diemžēl nevar tikt parādīta';
$messages['nocontactsfound'] = 'Ieraksti netika atrasti';
$messages['contactnotfound'] = 'Prasītais kontakts nav atrasts';
$messages['sendingfailed'] = 'Vēstule netika nosūtīta';
$messages['senttooquickly'] = 'Lūdzu uzgaidiet $sec sekundi(-es) pirms sūtiet šo vēstuli';
$messages['errorsavingsent'] = 'Notika kļūda saglabājot nosūtīto vēstuli';
$messages['errorsaving'] = 'Saglabājot vēstuli notikusi kļūme, vēstule netika saglabāta';
$messages['errormoving'] = 'Vēstule netika pārvietota';
$messages['errorcopying'] = 'Neizdevās pārkopēt vēstules.';
$messages['errordeleting'] = 'Vēstule netika dzēsta';
$messages['errormarking'] = 'Nebija iespējams iezīmēt vēstuli';
$messages['deletecontactconfirm'] = 'Vai tiešām vēlaties dzēst iezīmēto(ās) kontaktpersonas?';
$messages['deletegroupconfirm'] = 'Vai tiešām vēlaties dzēst atzīmēto grupu?';
$messages['deletemessagesconfirm'] = 'Vai tiešām vēlaties dzēst iezīmēto(ās) vēstules?';
$messages['deletefolderconfirm'] = 'Vai tiešām vēlaties dzēst šo mapi?';
$messages['purgefolderconfirm'] = 'Vai tiešām vēlaties dzēst visas vēstules, kas atrodas šajā mapē?';
$messages['groupdeleting'] = 'Dzēš grupu...';
$messages['folderdeleting'] = 'Dzēš mapi...';
$messages['foldermoving'] = 'Pārvietoju mapi...';
$messages['foldersubscribing'] = 'Pieslēdz mapi...';
$messages['folderunsubscribing'] = 'Atslēdz mapi...';
$messages['formincomplete'] = 'Formulārs nav pilnībā aizpildīts';
$messages['noemailwarning'] = 'Lūdzu ievadiet pareizu e-pasta adresi';
$messages['nonamewarning'] = 'Lūdzu ievadiet vārdu';
$messages['nopagesizewarning'] = 'Lūdzu ievadiet lapas izmēru';
$messages['nosenderwarning'] = 'Lūdzu ievadiet saņēmēja e-pasta adresi';
$messages['norecipientwarning'] = 'Lūdzu ievadiet vismaz vienu saņēmēju';
$messages['nosubjectwarning'] = 'Lauks "temats" ir tukšs. Vai vēlaties to aizpildīt tagad?';
$messages['nobodywarning'] = 'Sūtīt vēstuli bez satura teksta?';
$messages['notsentwarning'] = 'Vēstule netika nosūtīta. Vai tiešām vēlaties atcelt vēstules rakstīsanu?';
$messages['noldapserver'] = 'Lūdzu izvēlaties LDAP serveri';
$messages['nocontactsreturned'] = 'Neviena kontaktpersona netika atrasta';
$messages['nosearchname'] = 'Lūdzu ievadiet kontaktpersonas vārdu vai e-pasta adresi';
$messages['notuploadedwarning'] = 'Visi pielikumi vēl nav augšupielādēti. Lūdzu uzgaidiet vai atceļiet augšupielādi!';
$messages['searchsuccessful'] = 'Atrastas $nr vēstules';
$messages['searchnomatch'] = 'Meklēšanā nekas netika atrasts';
$messages['searching'] = 'Notiek meklēšana...';
$messages['checking'] = 'Notiek pārbaude...';
$messages['nospellerrors'] = 'Netika atrastas pareizrakstības kļudas';
$messages['folderdeleted'] = 'Mape veiksmīgi izdzēsta';
$messages['foldersubscribed'] = 'Mape veiksmīgi pieslēgta.';
$messages['folderunsubscribed'] = 'Mape veiksmīgi atslēgta.';
$messages['folderpurged'] = 'Mape tika veiksmīgi iztukšota.';
$messages['folderexpunged'] = 'Mape tika veiksmīgi saspiesta.';
$messages['deletedsuccessfully'] = 'Veiksmīgi izdzēsts';
$messages['converting'] = 'Tiek novākts vēstules formatējums...';
$messages['messageopenerror'] = 'Nevarēja ielādēt vēstuli no servera';
$messages['fileuploaderror'] = 'Neizdevās ielādēt failu';
$messages['filesizeerror'] = 'Ielādētais fails pārsniedz pieļaujamo $size apjomu';
$messages['copysuccess'] = 'Veiksmīgi nokopētas $nr vēstules';
$messages['copyerror'] = 'Nevarēja nokopēt nevienu adresi';
$messages['sourceisreadonly'] = 'Adreses avots ir lasīšanas režīmā tikai';
$messages['errorsavingcontact'] = 'Nevarēja saglabāt kontakta adreses';
$messages['movingmessage'] = 'Pārvietoju vēstules...';
$messages['copyingmessage'] = 'Kopē vēstules...';
$messages['copyingcontact'] = 'Kopē kontaktus...';
$messages['deletingmessage'] = 'Dzēš vēstules...';
$messages['markingmessage'] = 'Atzīmē vēstules...';
$messages['addingmember'] = 'Pievieno kontaktu(s) grupai...';
$messages['removingmember'] = 'Atvieno kontaktu(s) no grupas...';
$messages['receiptsent'] = 'Saņemšanas apstiprinājums nosūtīts';
$messages['errorsendingreceipt'] = 'Neizdevās nosūtīt apstiprinājumu';
$messages['nodeletelastidentity'] = 'Šo identitāti nevar izdzēst, tā ir pati pēdējā.';
$messages['forbiddencharacter'] = 'Mapes nosaukums satur aizliegtus simbolus';
$messages['selectimportfile'] = 'Lūdzu izvēlieties failu, ko vēlaties augšupielādēt';
$messages['addresswriterror'] = 'Izvēlētās adrešu grāmatas datus nevar labot';
$messages['contactaddedtogroup'] = 'Kontakti tika veiksmīgi pievienoti šai grupai.';
$messages['contactremovedfromgroup'] = 'Kontakti tika veiksmīgi atvienoti no šīs grupas.';
$messages['importwait'] = 'Importēju, lūdzu uzgaidiet...';
$messages['importerror'] = 'Imports neizdevās! Augšupielādētais fails nav korekts vCard fails';
$messages['importconfirm'] = '<b>Veiksmīgi ieimportēti $inserted kontakti, netika importēti $skipped esoši ieraksti</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Izlaida $skipped jau eksistējošus ierakstus</b>';
$messages['opnotpermitted'] = 'Darbība nav atļauta!';
$messages['nofromaddress'] = 'Izvēlētajai identitātei nav norādīta e-pasta adrese';
$messages['editorwarning'] = 'Pārslēdzoties uz vienkāršotu teksta redaktoru, tiks pazaudēts esošais teksta formatējums. Vai vēlaties turpināt?';
$messages['httpreceivedencrypterror'] = 'Kļūme. Lūdzu sazinieties ar administratoru. <b>Nav iespējams nosūtīt vēstuli.</b>';
$messages['smtpconnerror'] = 'SMTP kļūme ($code): Neizdevās pieslēgties serverim';
$messages['smtpautherror'] = 'SMTP kļūme ($code): Neizdevās autentificēties';
$messages['smtpfromerror'] = 'SMTP kļūme ($code): Neizdevās iestatīt sūtītāju "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP kļūme ($code): Neizdevās pievienot saņēmēju "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP kļūme: Nav iespējams parsēt saņēmēju sarakstu';
$messages['smtpdsnerror'] = 'SMTP kļūme: Piegādes statusa paziņojumi netiek uzturēti.';
$messages['smtperror'] = 'SMTP kļūme: $msg';
$messages['emailformaterror'] = 'Nepareiza e-pasta adrese: $email';
$messages['toomanyrecipients'] = 'Pārāk daudz saņēmēju. Samaziniet skaitu līdz $max.';
$messages['maxgroupmembersreached'] = 'Grupas dalībnieku skaits pārsniedz limitu $max.';
$messages['internalerror'] = 'Servera iekšējā kļūda. Lūdzu mēģiniet vēlreiz.';
$messages['contactdelerror'] = 'Neizdevās izdzēst kontaktu(s).';
$messages['contactdeleted'] = 'Kontakti veiksmīgi izdzēsti.';
$messages['contactrestoreerror'] = 'Neizdevās atjaunot izdzēstos kontaktus.';
$messages['contactrestored'] = 'Kontakti veiksmīgi atjaunoti.';
$messages['groupdeleted'] = 'Grupa veiksmīgi izdzēsta.';
$messages['grouprenamed'] = 'Grupa veiksmīgi pārdēvēta.';
$messages['groupcreated'] = 'Grupa veiksmīgi izveidota.';
$messages['messagedeleted'] = 'Vēstule(s) veiksmīgi izdzēsta(s).';
$messages['messagemoved'] = 'Vēstule(s) veiksmīgi pārvietota(s).';
$messages['messagecopied'] = 'Vēstule(s) veiksmīgi pārkopēta(s).';
$messages['messagemarked'] = 'Vēstule(s) veiksmīgi iezīmēta(s).';
$messages['autocompletechars'] = 'Ievadiet vismaz $min burtus, lai meklētu automātiski.';
$messages['namecannotbeempty'] = 'Lūdzu ievadiet vārdu.';
$messages['nametoolong'] = 'Vārds ir par garu.';
$messages['folderupdated'] = 'Mape vieksmīgi atjaunota.';
$messages['foldercreated'] = 'Mape veiksmīgi izveidota.';
$messages['invalidimageformat'] = 'Nederīgs attēla formāts.';
$messages['mispellingsfound'] = 'Vēstulē atrastas pareizrakstības kļūdas.';
$messages['parentnotwritable'] = 'Neizdevās izveidot/pārvietot mapi uz atzīmēto virsmapi. Nav piekļuves tiesību.';

?>
