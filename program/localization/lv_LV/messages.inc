<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = 'Radās kļūda!';
$messages['loginfailed']  = 'Pieslēgties neizdevās';
$messages['cookiesdisabled'] = 'Jūsu pārlūkprogramma neatbalsta sīkdatnes (cookies)';
$messages['sessionerror'] = 'Jūsu sessija ir beigusies.';
$messages['storageerror'] = 'Neizdevās pieslēgties IMAP serverim';
$messages['servererror'] = 'Servera kļūda!';
$messages['servererrormsg'] = 'Servera kļūda: $msg';
$messages['dberror'] = 'Datubāzes kļūda!';
$messages['requesttimedout'] = 'Pieprasījumam iestājās noilgums';
$messages['errorreadonly'] = 'Neizdevās veikt darbību: mape ir tikai lasāma (read only)';
$messages['errornoperm'] = 'Neizdevās veikt darbību: piekļuve liegta';
$messages['erroroverquota'] = 'Operāciju nav iespējams veikt. Uz diska nav brīvas veitas.';
$messages['erroroverquotadelete'] = 'Uz diska nav brīvas vietas. Lai dzēstu vēstuli, lietojiet SHIFT+DEL.';
$messages['invalidrequest'] = 'Nederīgs pieprasījums! Dati netika saglabāti ...';
$messages['invalidhost'] = 'Nederīgs servera nosaukums';
$messages['nomessagesfound'] = 'Šajā pastkastē nav vēstuļu';
$messages['loggedout'] = 'Jūs esat veiksmīgi atslēdzies no sistēmas. Uz redzēšanos!';
$messages['mailboxempty'] = 'Pastkaste ir tukša';
$messages['refreshing'] = 'Atjauno...';
$messages['loading'] = 'Notiek ielāde...';
$messages['uploading'] = 'Augšupielāde failu...';
$messages['uploadingmany'] = 'Augšupielādē failus...';
$messages['loadingdata'] = 'Ielādē datus...';
$messages['checkingmail'] = 'Notiek pasta pārbaude ...';
$messages['sendingmessage'] = 'Tiek sūtīta vēstule ...';
$messages['messagesent'] = 'Vēstule nosūtīta veiksmīgi';
$messages['savingmessage'] = 'Vēstule tiek saglabāta ...';
$messages['messagesaved'] = 'Vēstule saglabāta Uzmetumos';
$messages['successfullysaved'] = 'Vēstule veiksmīgi saglabāta';
$messages['addedsuccessfully'] = 'Kontakts veiksmīgi pievienots adrešu grāmatai';
$messages['contactexists'] = 'Kontakts ar šādu e-pasta adresi jau eksistē';
$messages['contactnameexists'] = 'Kontakts ar šādu vārdu jau eksistē.';
$messages['blockedimages'] = 'Drošības nolūkos attēli, kuri tiek ielādēti no cita servera, šajā vēstulē ir bloķēti';
$messages['encryptedmessage'] = 'Atvainojiet - šī ir šifrēta vēstule un diemžēl nevar tikt parādīta ...';
$messages['nocontactsfound'] = 'Kontakti netika atrasti';
$messages['contactnotfound'] = 'Pieprasītais kontakts nav atrasts';
$messages['contactsearchonly'] = 'Lai atrastu kontaktus, ievadiet meklēšanas kritērijus';
$messages['sendingfailed'] = 'Vēstule netika nosūtīta';
$messages['senttooquickly'] = 'Lūdzu uzgaidiet $sec sekundi(-es) pirms sūtiet šo vēstuli';
$messages['errorsavingsent'] = 'Saglabājot vēstuli notika kļūda - vēstule netika saglabāta';
$messages['errorsaving'] = 'Saglabājot notika kļūda';
$messages['errormoving'] = 'Vēstule(s) netika pārvietota(s)';
$messages['errorcopying'] = 'Vēstules pārkopēt neizdevās';
$messages['errordeleting'] = 'Vēstules izdzēst neizdevās';
$messages['errormarking'] = 'Iezīmēt vēstules nebija iespējams';
$messages['deletecontactconfirm']  = 'Vai tiešām vēlaties dzēst iezīmētās kontaktpersonas?';
$messages['deletegroupconfirm']  = 'Vai tiešām vēlaties dzēst atzīmēto grupu?';
$messages['deletemessagesconfirm'] = 'Vai tiešām vēlaties dzēst iezīmētās vēstules?';
$messages['deletefolderconfirm']  = 'Vai tiešām vēlaties dzēst šo mapi?';
$messages['purgefolderconfirm']  = 'Vai tiešām vēlaties dzēst visas vēstules, kuras atrodas šajā mapē?';
$messages['contactdeleting'] = 'Dzēš kontaktus...';
$messages['groupdeleting'] = 'Dzēš grupu...';
$messages['folderdeleting'] = 'Dzēš mapi...';
$messages['foldermoving'] = 'Pārvieto mapi...';
$messages['foldersubscribing'] = 'Pieslēdz mapi...';
$messages['folderunsubscribing'] = 'Atslēdz mapi...';
$messages['formincomplete'] = 'Forma nav pilnībā aizpildīta';
$messages['noemailwarning'] = 'Lūdzu ievadiet korektu e-pasta adresi';
$messages['nonamewarning']  = 'Lūdzu ievadiet vārdu';
$messages['nopagesizewarning'] = 'Lūdzu ievadiet lapas izmēru';
$messages['nosenderwarning'] = 'Lūdzu ievadiet sūtītāja e-pasta adresi';
$messages['norecipientwarning'] = 'Lūdzu ievadiet vismaz vienu saņēmēju';
$messages['nosubjectwarning']  = 'Lauks "temats" ir tukšs. Vai vēlaties to aizpildīt tagad?';
$messages['nobodywarning'] = 'Sūtīt vēstuli bez teksta?';
$messages['notsentwarning'] = 'Vēstule netika nosūtīta. Vai tiešām vēlaties atcelt vēstules rakstīšanu?';
$messages['noldapserver'] = 'Lai meklētu, lūdzu izvēlaties LDAP serveri';
$messages['nosearchname'] = 'Lūdzu ievadiet kontaktpersonas vārdu vai e-pasta adresi';
$messages['notuploadedwarning'] = 'Visi pielikumi vēl nav augšupielādēti - lūdzu uzgaidiet vai atceļiet augšupielādi';
$messages['searchsuccessful'] = 'Atrastas $nr vēstules';
$messages['contactsearchsuccessful'] = 'Atrasti $nr kontakti';
$messages['searchnomatch'] = 'Meklējot nekas netika atrasts';
$messages['searching'] = 'Meklē...';
$messages['checking'] = 'Pārbauda...';
$messages['nospellerrors'] = 'Pareizrakstības kļūdas netika atrastas';
$messages['folderdeleted'] = 'Mape veiksmīgi izdzēsta';
$messages['foldersubscribed'] = 'Mape veiksmīgi pieslēgta.';
$messages['folderunsubscribed'] = 'Mape veiksmīgi atslēgta.';
$messages['folderpurged'] = 'Mape tika veiksmīgi iztukšota.';
$messages['folderexpunged'] = 'Mape saspiesta veiksmīgi';
$messages['deletedsuccessfully'] = 'Veiksmīgi izdzēsts';
$messages['converting'] = 'Tiek noņemts vēstules formatējums...';
$messages['messageopenerror'] = 'Nevarēja ielādēt vēstuli no servera';
$messages['fileuploaderror'] = 'Faila augšupielāde neveiksmīga';
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
$messages['deleteidentityconfirm'] = 'Vai tiešām vēlaties dzēst šo identitāti?';
$messages['nodeletelastidentity'] = 'Šo identitāti nevar izdzēst, tā ir pati pēdējā.';
$messages['forbiddencharacter'] = 'Mapes nosaukums satur aizliegtus simbolus';
$messages['selectimportfile'] = 'Lūdzu izvēlieties failu, ko vēlaties augšupielādēt';
$messages['addresswriterror'] = 'Izvēlētās adrešu grāmatas datus nevar labot';
$messages['contactaddedtogroup'] = 'Kontakti tika veiksmīgi pievienoti šai grupai.';
$messages['contactremovedfromgroup'] = 'Kontakti tika veiksmīgi atvienoti no šīs grupas.';
$messages['nogroupassignmentschanged'] = 'Grupā nekas netika mainīts.';
$messages['importwait'] = 'Importēju, lūdzu uzgaidiet...';
$messages['importformaterror'] = 'Imports neizdevās! Augšupielādētais fails nav derīgs importam.';
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
$messages['savedsearchdeleted'] = 'Saglabātais meklēšanas pieprasījums veiksmīgi dzēsts.';
$messages['savedsearchdeleteerror'] = 'Neizdevās nodzēst saglabāto meklēšanas pieprasījumu.';
$messages['savedsearchcreated'] = 'Saglabātais meklēšanas pieprasījums veiksmīgi saglabāts.';
$messages['savedsearchcreateerror'] = 'Neizdevās saglabāt meklēšanas pieprasījumu.';
$messages['messagedeleted'] = 'Vēstule(s) veiksmīgi izdzēsta(s).';
$messages['messagemoved'] = 'Vēstule(s) veiksmīgi pārvietota(s).';
$messages['messagecopied'] = 'Vēstule(s) veiksmīgi pārkopēta(s).';
$messages['messagemarked'] = 'Vēstule(s) veiksmīgi iezīmēta(s).';
$messages['autocompletechars'] = 'Ievadiet vismaz $min burtus, lai meklētu automātiski.';
$messages['autocompletemore'] = 'Atrasti vairāki ieraksti. Papildiniet meklēšanas kritēriju.';
$messages['namecannotbeempty'] = 'Lūdzu ievadiet vārdu.';
$messages['nametoolong'] = 'Vārds ir par garu.';
$messages['folderupdated'] = 'Mape vieksmīgi atjaunota.';
$messages['foldercreated'] = 'Mape veiksmīgi izveidota.';
$messages['invalidimageformat'] = 'Nederīgs attēla formāts.';
$messages['mispellingsfound'] = 'Vēstulē atrastas pareizrakstības kļūdas.';
$messages['parentnotwritable'] = 'Neizdevās izveidot/pārvietot mapi uz atzīmēto virsmapi. Nav piekļuves tiesību.';
$messages['messagetoobig'] = 'Vēstule ir pārāk liela, lai to varētu apstrādāt.';
$messages['attachmentvalidationerror'] = 'BRĪDINĀJUMS! Šis pielikums ir aizdomīgs, jo tā tips neatbilst tipam, kurš ir uzrādīts e-pasta ziņojumā. Ja jūs neuzticaties sūtītājam, šo failu Jums vaļā vērt nevajadzētu, jo tas var saturēt ļaunprātīgu saturu. <br/><br/><em>Būtu jābūt: $expected; Bet ir: $detected</em>';
$messages['noscriptwarning'] = 'Uzmanību: lai lasītu e-pastus, Jūsu tīmekļa pārlūkā jābūt iespējotiem JavaScript.';

?>
