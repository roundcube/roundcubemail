<?php

/*

+-----------------------------------------------------------------------+
| language/nl_NL/messages.inc                                           |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Mark Moses <mark@katmoda.com>                                 |
|         Lazlo Westerhof <roundcube@lazlo.me>                          |
|         Frits Letteboer <f.letteboer@radiotwenterand.nl>              |
|         Justin van Beusekom <j.v.beusekom@beus-it.nl>                 |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Inloggen mislukt';
$messages['cookiesdisabled'] = 'Uw browser accepteert geen cookies';
$messages['sessionerror'] = 'Uw sessie is verlopen of ongeldig';
$messages['imaperror'] = 'Connectie met IMAP server mislukt';
$messages['servererror'] = 'Server Fout!';
$messages['invalidrequest'] = 'Ongeldige aanvraag! Er zijn geen gegevens opgeslagen.';
$messages['nomessagesfound'] = 'Geen berichten gevonden in deze mailbox';
$messages['loggedout'] = 'Succesvol uitgelogd. Tot ziens!';
$messages['mailboxempty'] = 'Mailbox is leeg';
$messages['loading'] = 'Laden...';
$messages['uploading'] = 'Bezig met uploaden...';
$messages['loadingdata'] = 'Laden van data...';
$messages['checkingmail'] = 'Controleren op nieuwe berichten...';
$messages['sendingmessage'] = 'Bezig met bericht versturen...';
$messages['messagesent'] = 'Bericht succesvol verstuurd';
$messages['savingmessage'] = 'Bezig met bericht opslaan...';
$messages['messagesaved'] = 'Bericht bewaard als concept';
$messages['successfullysaved'] = 'Succesvol opgeslagen';
$messages['addedsuccessfully'] = 'Contact is succesvol toegevoegd aan het adresboek';
$messages['contactexists'] = 'Er bestaat al een contactpersoon met dit e-mailadres';
$messages['blockedimages'] = 'Uit privacyoverwegingen zijn niet bijgevoegde afbeeldingen geblokkeerd in dit bericht.';
$messages['encryptedmessage'] = 'Dit is een gecodeerd bericht en kan niet weergegeven worden. Excuses!';
$messages['nocontactsfound'] = 'Geen contactpersonen gevonden';
$messages['contactnotfound'] = 'Contactpersoon niet gevonden';
$messages['sendingfailed'] = 'Verzenden van bericht is mislukt';
$messages['senttooquickly'] = 'Je moet $sec seconden wachten om het bericht te versturen.';
$messages['errorsavingsent'] = 'Er is een fout opgetreden tijdens het opslaan van het verzonden bericht';
$messages['errorsaving'] = 'Er is een fout opgetreden tijdens het opslaan';
$messages['errormoving'] = 'Kan het bericht niet verplaatsen';
$messages['errorcopying'] = 'Kan de bericht(en) niet kopiëren';
$messages['errordeleting'] = 'Kan het bericht niet verwijderen';
$messages['errormarking'] = 'Kon bericht niet markeren';
$messages['deletecontactconfirm'] = 'Weet u zeker dat u de geselecteerde contactperso(o)n/en wilt verwijderen?';
$messages['deletemessagesconfirm'] = 'Weet u zeker dat u de geselecteerde bericht(en) wilt verwijderen?';
$messages['deletefolderconfirm'] = 'Weet u zeker dat u deze map wilt verwijderen?';
$messages['purgefolderconfirm'] = 'Weet u zeker dat u alle berichten in deze map wilt verwijderen?';
$messages['foldercreating'] = 'Map aanmaken...';
$messages['folderdeleting'] = 'Map verwijderen...';
$messages['folderrenaming'] = 'Mapnaam wijzigen...';
$messages['foldermoving'] = 'Map verplaatsen...';
$messages['formincomplete'] = 'Het formulier was niet volledig ingevuld';
$messages['noemailwarning'] = 'Geef een geldig e-mailadres op';
$messages['nonamewarning'] = 'Vul een naam in';
$messages['nopagesizewarning'] = 'Geef een paginagrootte op';
$messages['nosenderwarning'] = 'Geef een e-mailadres van de afzender op';
$messages['norecipientwarning'] = 'Geef tenminste één ontvanger op';
$messages['nosubjectwarning'] = 'Het \'Onderwerp\' veld is leeg. Wilt u nu alsnog een onderwerp opgeven?';
$messages['nobodywarning'] = 'Dit bericht zonder inhoud versturen?';
$messages['notsentwarning'] = 'Bericht is niet verstuurd. Weet u zeker dat u het niet wilt bewaren?';
$messages['noldapserver'] = 'Geef een LDAP adresboek server op om te doorzoeken';
$messages['nocontactsreturned'] = 'Er zijn geen contactpersonen gevonden';
$messages['nosearchname'] = 'Geef de naam of e-mail op van de contactpersoon';
$messages['notuploadedwarning'] = 'Nog niet alle bijlagen zijn geüpload. Wacht even of annuleer de upload.';
$messages['searchsuccessful'] = '$nr berichten gevonden';
$messages['searchnomatch'] = 'Zoekopdracht heeft geen resultaten opgeleverd';
$messages['searching'] = 'Zoeken...';
$messages['checking'] = 'Controleren...';
$messages['nospellerrors'] = 'Geen spelfouten gevonden';
$messages['folderdeleted'] = 'Map successvol verwijderd';
$messages['deletedsuccessfully'] = 'Successvol verwijderd';
$messages['converting'] = 'Opmaak van bericht verwijderen...';
$messages['messageopenerror'] = 'Kan het bericht niet van de server laden';
$messages['fileuploaderror'] = 'Bestand uploaden mislukt';
$messages['filesizeerror'] = 'Het bestand overschrijdt de maximale grootte van $size';
$messages['copysuccess'] = '$nr adressen succesvol gekopieerd';
$messages['copyerror'] = 'Kan geen adressen kopiëren';
$messages['sourceisreadonly'] = 'Het adres is alleen-lezen';
$messages['errorsavingcontact'] = 'Kan het ontvanger-adres niet opslaan';
$messages['movingmessage'] = 'Bericht verplaatsen...';
$messages['copyingmessage'] = 'Kopiëren bericht...';
$messages['receiptsent'] = 'Ontvangstbevestiging is verstuurd';
$messages['errorsendingreceipt'] = 'Kan de ontvangstbevestiging niet versturen';
$messages['nodeletelastidentity'] = 'U kunt uw enige identiteit niet verwijderen';
$messages['addsubfolderhint'] = 'Deze map zal worden aangemaakt onder de geselecteerde map';
$messages['forbiddencharacter'] = 'De naam van de map bevat een karakter dat niet is toegestaan';
$messages['selectimportfile'] = 'Selecteer een bestand om de uploaden';
$messages['addresswriterror'] = 'Het geselecteerde adresboek is alleen lezen';
$messages['contactaddedtogroup'] = 'Contact(en) met succes toegevoegd aan de groep';
$messages['contactremovedfromgroup'] = 'Contact(en) met succes verwijderd aan de groep';
$messages['importwait'] = 'Importeren, even geduld...';
$messages['importerror'] = 'Importeren mislukt! Het verstuurde bestand is geen geldig vCard bestand.';
$messages['importconfirm'] = '<b>Er zijn $inserted contactpersonen succesvol geïmporteerd, en $skipped bestaande overgeslagen</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Deze verrichting is niet toegestaan!';
$messages['nofromaddress'] = 'Het e-mailadres mist in de geselecteerde identiteit';
$messages['editorwarning'] = 'Door het overschakelen naar de platte tekst editor gaat alle opmaak verloren. Weet je zeker dat je verder wil gaan?';
$messages['httpreceivedencrypterror'] = 'Er is een fatale fout opgetreden. Neem direct contact op met uw systeembeheerder. <b>Uw bericht kon niet worden verzonden.</b>';
$messages['smtpconnerror'] = 'SMTP Fout ($code): Verbinding met server mislukt';
$messages['smtpautherror'] = 'SMTP Fout ($code): Autenticatie mislukt';
$messages['smtpfromerror'] = 'SMTP Fout ($code): Kon afzender "$from" niet instellen';
$messages['smtptoerror'] = 'SMTP Fout ($code): Kon ontvanger "$to" niet toevoegen';
$messages['smtprecipientserror'] = 'SMTP Fout: Kon lijst van ontvangers niet verwerken';
$messages['smtperror'] = 'SMTP Fout: $msg';
$messages['emailformaterror'] = 'Ongeldig e-mailadres: $email';
$messages['toomanyrecipients'] = 'Te veel afzender. Verminder de hoeveelheid afzenders tot $max.';
$messages['maxgroupmembersreached'] = 'Het aantal leden van de groep groter is dan de maximale van $max';

?>
