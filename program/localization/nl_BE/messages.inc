<?php

/*

+-----------------------------------------------------------------------+
| language/nl_BE/messages.inc                                           |
|                                                                       |
| Language file of the Roundcube Webmail client                         |
| Copyright (C) 2005-2010, The Roundcube Dev Team                       |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Dennis Heeren <dennis.heeren@gmail.com>                       |
|         Simon De Schoenmaeker <sds@simonds.be>                        |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Aanmelden mislukt...';
$messages['cookiesdisabled'] = 'Uw browser accepteert geen cookies...';
$messages['sessionerror'] = 'Sessie is reeds beëindigd of is ongeldig...';
$messages['imaperror'] = 'Kon geen verbinding maken met de IMAP server...';
$messages['servererror'] = 'Server fout!';
$messages['servererrormsg'] = 'Server fout: $msg';
$messages['errorreadonly'] = 'Kon opdracht niet uitvoeren. Map is alleen-lezen';
$messages['errornoperm'] = 'Kon opdracht niet uitvoeren. Toegang geweigerd';
$messages['invalidrequest'] = 'Ongeldige aanvraag! Er zijn geen gegevens opgeslagen.';
$messages['nomessagesfound'] = 'Er zijn geen berichten in deze map...';
$messages['loggedout'] = 'U bent nu afgemeld. Tot ziens!';
$messages['mailboxempty'] = 'Mailbox is leeg...';
$messages['loading'] = 'Bezig met ophalen gegevens...';
$messages['uploading'] = 'Bezig met uploaden...';
$messages['loadingdata'] = 'Bezig met gegevens ophalen...';
$messages['checkingmail'] = 'Controleer op nieuwe berichten...';
$messages['sendingmessage'] = 'Bericht wordt verstuurd...';
$messages['messagesent'] = 'Het bericht is verzonden...';
$messages['savingmessage'] = 'Bericht wordt bewaard...';
$messages['messagesaved'] = 'Bericht bewaard in Concepten';
$messages['successfullysaved'] = 'Succesvol opgeslagen...';
$messages['addedsuccessfully'] = 'Contactpersoon is toegevoegd...';
$messages['contactexists'] = 'Er bestaat reeds een contactpersoon met dit e-mailadres...';
$messages['blockedimages'] = 'Om uw privacy te beschermen worden de afbeeldingen in dit bericht niet getoond...';
$messages['encryptedmessage'] = 'Dit bericht is geëncrypteerd en kan niet worden weergegeven, sorry...';
$messages['nocontactsfound'] = 'Geen contactpersonen gevonden...';
$messages['contactnotfound'] = 'De gevraagde contactpersoon was niet gevonden';
$messages['sendingfailed'] = 'Kon het bericht niet versturen...';
$messages['senttooquickly'] = 'U moet $sec seconden wachten om het bericht te versturen.';
$messages['errorsavingsent'] = 'Er is een fout opgetreden tijdens het opslaan van het verzonden bericht';
$messages['errorsaving'] = 'Er is een fout opgetreden bij het opslaan van het bericht...';
$messages['errormoving'] = 'Kon de bericht(en) niet verplaatsen...';
$messages['errorcopying'] = 'Kon de bericht(en) niet kopiëren';
$messages['errordeleting'] = 'Kon de bericht(en) niet verwijderen...';
$messages['errormarking'] = 'Kon de bericht(en) niet markeren...';
$messages['deletecontactconfirm'] = 'Bent u zeker dat u de geselecteerde contactpers(o)on(en) wilt verwijderen?';
$messages['deletemessagesconfirm'] = 'Bent u zeker dat u de geselecteerde bericht(en) wilt verwijderen?';
$messages['deletefolderconfirm'] = 'Weet u zeker dat u deze map wilt verwijderen?';
$messages['purgefolderconfirm'] = 'Bent u zeker dat u alle berichten in deze map wilt verwijderen?';
$messages['folderdeleting'] = 'Map verwijderen...';
$messages['foldermoving'] = 'Map verplaatsen...';
$messages['formincomplete'] = 'Het formulier is niet volledig ingevuld...';
$messages['noemailwarning'] = 'Geef een geldig e-mailadres op...';
$messages['nonamewarning'] = 'Geef een naam op...';
$messages['nopagesizewarning'] = 'Geef een geldig aantal berichten per pagina op...';
$messages['nosenderwarning'] = 'Geef een e-mailadres van de afzender op';
$messages['norecipientwarning'] = 'Geef minstens één bestemmeling op...';
$messages['nosubjectwarning'] = 'Geen onderwerp opgegeven. Wenst u alsnog een onderwerp te geven?';
$messages['nobodywarning'] = 'Wilt u dit lege bericht versturen?';
$messages['notsentwarning'] = 'Het bericht is niet verzonden. Wilt u het bericht verwijderen?';
$messages['noldapserver'] = 'Kies een LDAP server waarop gezocht moet worden...';
$messages['nocontactsreturned'] = 'Geen contactpersonen gevonden';
$messages['nosearchname'] = 'Geen een contacpersoon of e-mailadres op...';
$messages['notuploadedwarning'] = 'Nog niet alle bijlagen zijn geüpload. Wacht even of annuleer de upload.';
$messages['searchsuccessful'] = '$nr berichten gevonden';
$messages['searchnomatch'] = 'Zoekopdracht leverde geen resultaten';
$messages['searching'] = 'Bezig met zoeken...';
$messages['checking'] = 'Bezig met controleren...';
$messages['nospellerrors'] = 'Geen spelfouten gevonden';
$messages['folderdeleted'] = 'Map met succes verwijderd';
$messages['folderexpunged'] = 'Map met succes geleegd';
$messages['deletedsuccessfully'] = 'Succesvol verwijderd';
$messages['converting'] = 'Opmaak van het bericht wordt gewist...';
$messages['messageopenerror'] = 'Kon het bericht niet ophalen van de server';
$messages['fileuploaderror'] = 'Bestandupload mislukt';
$messages['filesizeerror'] = 'Het bestand overschrijdt de maximum grootte van $size';
$messages['copysuccess'] = '$nr adressen met succes gekopieerd';
$messages['copyerror'] = 'Kon de adressen niet kopiëren';
$messages['sourceisreadonly'] = 'Het adres kan niet worden opgeslagen';
$messages['errorsavingcontact'] = 'Kon de contactpersoon niet bewaren';
$messages['movingmessage'] = 'Bericht wordt verplaatst...';
$messages['copyingmessage'] = 'Bericht wordt gekopieerd...';
$messages['deletingmessage'] = 'Bericht wordt verwijderd...';
$messages['markingmessage'] = 'Bericht wordt gemarkeerd...';
$messages['receiptsent'] = 'Ontvangstbevestiging verstuurd';
$messages['errorsendingreceipt'] = 'Kon de ontvangstbevestigign niet versturen';
$messages['nodeletelastidentity'] = 'U kunt dit laatste afzendadres niet verwijderen.';
$messages['forbiddencharacter'] = 'De naam van de map bevat een karakter dat niet is toegestaan';
$messages['selectimportfile'] = 'Selecteer een bestand om te uploaden';
$messages['addresswriterror'] = 'Het geselecteerde adresboek is alleen-lezen';
$messages['contactaddedtogroup'] = 'Contact(en) met succes toegevoegd aan de groep';
$messages['contactremovedfromgroup'] = 'Contact(en) met succes verwijderd aan de groep';
$messages['importwait'] = 'Importeren, even geduld...';
$messages['importerror'] = 'Importeren mislukt! Het verstuurde bestand is geen geldig vCard bestand.';
$messages['importconfirm'] = '<b>Er zijn $inserted contactpersonen succesvol geïmporteerd, en $skipped bestaande overgeslagen</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Deze actie is niet toegestaan!';
$messages['nofromaddress'] = 'Er is geen e-mailadres in de geselecteerde identiteit';
$messages['editorwarning'] = 'Door het overschakelen naar de platte tekst editor gaat alle opmaak verloren. Weet je zeker dat je verder wil gaan?';
$messages['httpreceivedencrypterror'] = 'Er is een fatale fout opgetreden. Neem direct contact op met uw systeembeheerder. <b>Uw bericht kon niet worden verzonden.</b>';
$messages['smtpconnerror'] = 'SMTP Fout ($code): Verbinding met server mislukt';
$messages['smtpautherror'] = 'SMTP Fout ($code): Authenticatie mislukt';
$messages['smtpfromerror'] = 'SMTP Fout ($code): Kon afzender "$from" niet instellen ($msg)';
$messages['smtptoerror'] = 'SMTP Fout ($code): Kon ontvanger "$to" niet toevoegen ($msg)';
$messages['smtprecipientserror'] = 'SMTP Fout: Kon lijst van ontvangers niet verwerken';
$messages['smtpdsnerror'] = 'SMTP Fout: Delivery Status Notifications worden niet ondersteund';
$messages['smtperror'] = 'SMTP Fout: $msg';
$messages['emailformaterror'] = 'Ongeldig e-mailadres: $email';
$messages['toomanyrecipients'] = 'Te veel geadresseerden. Verminder de hoeveelheid geadresseerden tot $max';
$messages['maxgroupmembersreached'] = 'Het aantal leden van de groep groter is dan de maximale van $max';
$messages['internalerror'] = 'Er is een interne fout opgetreden. Probeer het nogmaals';
$messages['contactdelerror'] = 'Kon contactpersoon niet verwijderen';
$messages['contactdeleted'] = 'Contactpersoon succesvol verwijderd';
$messages['groupdeleted'] = 'Groep succesvol verwijderd';
$messages['grouprenamed'] = 'Groep succesvol hernoemd';
$messages['groupcreated'] = 'Groep succesvol aangemaakt';
$messages['messagedeleted'] = 'Bericht(en) succesvol verwijderd';
$messages['messagemoved'] = 'Bericht(en) succesvol verplaatst';
$messages['messagecopied'] = 'Bericht(en) succesvol gekopieerd';
$messages['messagemarked'] = 'Bericht(en) succesvol gemarkeerd';
$messages['autocompletechars'] = 'Geef ten minste $min karakters voor auto aanvullen';
$messages['namecannotbeempty'] = 'Naam kan niet leeg zijn';
$messages['nametoolong'] = 'Naam is te lang';
$messages['folderupdated'] = 'Map succesvol geüpdatet';
$messages['foldercreated'] = 'Map succesvol aangemaakt';

?>
