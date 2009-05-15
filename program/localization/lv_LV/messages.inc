<?php

/*

+-----------------------------------------------------------------------+
| language/lv/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Rudolfs Osins <dev.random@gmail.com>                          |
|         Mikelis Zalais <mik@prog.lv>                                  |
|         Lauris Bukšis-Haberkorns <lauris@nix.lv>                      |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Neizdevās pieslēgties';
$messages['cookiesdisabled'] = 'Jūsu pārlūkprogramma neatbalsta sīkdatnes (cookies)';
$messages['sessionerror'] = 'Jūsu sessija ir beigusies';
$messages['imaperror'] = 'Neizdevās pieslēgties IMAP serverim';
$messages['nomessagesfound'] = 'Šajā pastkastē nav vēstuļu';
$messages['loggedout'] = 'Jūs esat veiksmīgi atslēdzies';
$messages['mailboxempty'] = 'Pastkaste tukša';
$messages['loading'] = 'Notiek ielāde...';
$messages['loadingdata'] = 'Tiek ielādēti dati...';
$messages['checkingmail'] = 'Notiek pasta pārbaude...';
$messages['sendingmessage'] = 'Tiek sūtīta vēstule...';
$messages['messagesent'] = 'Vēstule nosūtīta veiksmīgi';
$messages['savingmessage'] = 'Vēstule tiek saglabāta...';
$messages['messagesaved'] = 'Vēstule saglabāta pie uzmetumiem';
$messages['successfullysaved'] = 'Saglabāts veiksmīgi';
$messages['addedsuccessfully'] = 'Ieraksts veiksmīgi pievienots adrešu grāmatai';
$messages['contactexists'] = 'Ieraksts ar šādu e-pasta adresi jau eksistē';
$messages['blockedimages'] = 'Drošības nolūkos attēli, kas tiek ielādēti no cita servera, šajā vēstulē ir bloķēti';
$messages['encryptedmessage'] = 'Šī ir šifrēta vēstule un diemžēl nevar tikt parādīta';
$messages['nocontactsfound'] = 'Ieraksti netika atrasti';
$messages['contactnotfound'] = 'Prasītais kontakts nav atrasts';
$messages['sendingfailed'] = 'Vēstule netika nosūtīta';
$messages['senttooquickly'] = 'Lūdzu uzgaidiet $sec sekundi(-es) pirms sūtiet šo vēstuli';
$messages['errorsavingsent'] = 'Notika kļūda saglabājot nosūtīto vēstuli';
$messages['errorsaving'] = 'Saglabājot vēstuli notikusi kļūme, vēstule netika saglabāta';
$messages['errormoving'] = 'Vēstule netika pārvietota';
$messages['errordeleting'] = 'Vēstule netika dzēsta';
$messages['deletecontactconfirm'] = 'Vai tiešām vēlaties dzēst iezīmēto(ās) kontaktpersonas?';
$messages['deletemessagesconfirm'] = 'Vai tiešām vēlaties dzēst iezīmēto(ās) vēstules?';
$messages['deletefolderconfirm'] = 'Vai tiešām vēlaties dzēst šo mapi?';
$messages['purgefolderconfirm'] = 'Vai tiešām vēlaties dzēst visas vēstules, kas atrodas šajā mapē?';
$messages['foldercreating'] = 'Izveidoju mapi...';
$messages['folderdeleting'] = 'Dzēšu mapi...';
$messages['folderrenaming'] = 'Pārsaucu mapi...';
$messages['foldermoving'] = 'Pārvietoju mapi...';
$messages['formincomplete'] = 'Formulārs nav pilnībā aizpildīts';
$messages['noemailwarning'] = 'Lūdzu ievadiet pareizu e-pasta adresi';
$messages['nonamewarning'] = 'Lūdzu ievadiet vārdu';
$messages['nopagesizewarning'] = 'Lūdzu ievadiet lapas izmēru';
$messages['nosenderwarning'] = 'Lūdzu ievadiet saņēmēja e-pasta adresi';
$messages['norecipientwarning'] = 'Lūdzu ievadiet vismaz vienu saņēmēju';
$messages['nosubjectwarning'] = 'Lauks "temats" ir tukšs. Vai vēlaties to aizpildīt tagad?';
$messages['nobodywarning'] = 'Sūtīt vēstuli bez satura teksta?';
$messages['notsentwarning'] = 'Vēstule netika nosūtīta. Vai vēlaties atmest savu vēstuli?';
$messages['noldapserver'] = 'Lūdzu izvēlaties LDAP serveri';
$messages['nocontactsreturned'] = 'Neviena kontaktpersona netika atrasta';
$messages['nosearchname'] = 'Lūdzu ievadiet kontaktpersonas vārdu vai e-pasta adresi';
$messages['searchsuccessful'] = 'Atrastas $nr vēstules';
$messages['searchnomatch'] = 'Meklēšanā nekas netika atrasts';
$messages['searching'] = 'Notiek meklēšana...';
$messages['checking'] = 'Notiek pārbaude...';
$messages['nospellerrors'] = 'Netika atrastas pareizrakstības kļudas';
$messages['folderdeleted'] = 'Mape veiksmīgi izdzēsta';
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
$messages['receiptsent'] = 'Saņemšanas apstiprinājums nosūtīts';
$messages['errorsendingreceipt'] = 'Neizdevās nosūtīt apstiprinājumu';
$messages['nodeletelastidentity'] = 'Šo identitāti nevar izdzēst, tā ir pati pēdējā.';
$messages['addsubfolderhint'] = 'Šis folderis tiks izveidots kā apakšfolderis tam, kurš patreiz ir izvēlēts';
$messages['forbiddencharacter'] = 'Mapes nosaukums satur aizliegtus simbolus';
$messages['selectimportfile'] = 'Lūdzu izvēlieties failu, ko vēlaties augšupielādēt';
$messages['addresswriterror'] = 'Izvēlētās adrešu grāmatas datus nevar labot';
$messages['importwait'] = 'Importēju, lūdzu uzgaidiet...';
$messages['importerror'] = 'Imports neizdevās! Augšupielādētais fails nav korekts vCard fails';
$messages['importconfirm'] = '<b>Veiksmīgi ieimportēti $inserted kontakti, netika importēti $skipped esoši ieraksti</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Darbība nav atļauta!';
$messages['nofromaddress'] = 'Izvēlētajai identitātei nav norādīta e-pasta adrese';
$messages['editorwarning'] = 'Pārslēdzoties uz vienkāršotu teksta redaktoru, tiks pazaudēts esošais teksta formatējums. Vai vēlaties turpināt?';

?>
