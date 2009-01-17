<?php

/*

+-----------------------------------------------------------------------+
| language/fi/messages.inc                                              |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Ville Alatalo <ville@alatalo.org>                             |
|         Veljo Velling <veve@utu.fi>                                   |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = 'Sisäänkirjautuminen epäonnistui';
$messages['cookiesdisabled'] = 'Selaimesi ei hyväksy evästeitä';
$messages['sessionerror'] = 'Sessio ei kelpaa tai ei ole enää voimassa';
$messages['imaperror'] = 'Yhteys IMAP-palvelimeen epäonnistui';
$messages['nomessagesfound'] = 'Kansiossa ei ole sähköpostiviestejä';
$messages['loggedout'] = 'Sinut on kirjattu ulos järjestelmästä.';
$messages['mailboxempty'] = 'Kansio on tyhjä';
$messages['loading'] = 'Ladataan...';
$messages['loadingdata'] = 'Ladataan tietoja...';
$messages['checkingmail'] = 'Tarkistetaan saapuneita viestejä...';
$messages['sendingmessage'] = 'Lähetetään viestiä...';
$messages['messagesent'] = 'Viesti lähetetty';
$messages['savingmessage'] = 'Tallennetaan viestiä...';
$messages['messagesaved'] = 'Viesti tallennettu "Luonnokset" -kansioon';
$messages['successfullysaved'] = 'Tallennus onnistui';
$messages['addedsuccessfully'] = 'Kontakti lisätty osoitekirjaan';
$messages['contactexists'] = 'Samalla sähköpostiosoitteella on jo olemassa kontakti';
$messages['blockedimages'] = 'Turvallisuussyistä viestin kuvia ei näytetty.';
$messages['encryptedmessage'] = 'Viesti on salattu, eikä sitä voida näyttää.';
$messages['nocontactsfound'] = 'Ei kontakteja';
$messages['contactnotfound'] = 'Pyydettyä yhteystietoa ei löytynyt';
$messages['sendingfailed'] = 'Viestin lähetys epäonnistui';
$messages['senttooquickly'] = 'Odota $sec sekunti(a) ennen viestin lähettämistä';
$messages['errorsavingsent'] = 'Lähetetyn viestin tallennuksessa tapahtui virhe';
$messages['errorsaving'] = 'Tallennuksessa tapahtui virhe';
$messages['errormoving'] = 'Viestiä ei voitu siirtää';
$messages['errordeleting'] = 'Viestiä ei voitu poistaa';
$messages['deletecontactconfirm'] = 'Haluatko varmasti poistaa valitut kontaktit?';
$messages['deletemessagesconfirm'] = 'Haluatko varmasti poistaa valitut viestit?';
$messages['deletefolderconfirm'] = 'Haluatko varmasti poistaa kansion?';
$messages['purgefolderconfirm'] = 'Haluatko varmasti poistaa kaikki viestit kansiosta?';
$messages['foldercreating'] = 'Luodaan kansiota...';
$messages['folderdeleting'] = 'Poistetaan kansiota...';
$messages['folderrenaming'] = 'Nimetään kansiota...';
$messages['foldermoving'] = 'Siirretään kansiota...';
$messages['formincomplete'] = 'Lomakkeen tiedot olivat puutteelliset';
$messages['noemailwarning'] = 'Anna sähköpostiosoite';
$messages['nonamewarning'] = 'Anna nimi';
$messages['nopagesizewarning'] = 'Anna sivukoko';
$messages['nosenderwarning'] = 'Anna lähettäjän sähköpostiosoite';
$messages['norecipientwarning'] = 'Anna ainakin yksi vastaanottaja';
$messages['nosubjectwarning'] = '"Otsikko" -kenttä on tyhjä. Haluaisitko kirjoittaa viestillesi otsikon?';
$messages['nobodywarning'] = 'Lähetetäänkö viesti ilman tekstiä?';
$messages['notsentwarning'] = 'Viestiä ei lähetetty. Haluatko poistaa viestin?';
$messages['noldapserver'] = 'Valitse LDAP -palvelin';
$messages['nocontactsreturned'] = 'Yhtään kontaktia ei löytynyt';
$messages['nosearchname'] = 'Anna kontaktin nimi tai sähköpostiosoite';
$messages['searchsuccessful'] = 'Löydetty $nr viestiä';
$messages['searchnomatch'] = 'Haku ei tuottanut tuloksia';
$messages['searching'] = 'Etsitään...';
$messages['checking'] = 'Tarkistetaan...';
$messages['nospellerrors'] = 'Kirjoitusvirheitä ei löytynyt';
$messages['folderdeleted'] = 'Kansio poistettu onnistuneesti';
$messages['deletedsuccessfully'] = 'Poistettu onnistuneesti';
$messages['converting'] = 'Poistetaan asettelu viestistä...';
$messages['messageopenerror'] = 'Virhe kopioitaessa viestiä palvelimelta';
$messages['fileuploaderror'] = 'Tiedoston lähettäminen epäonnistui';
$messages['filesizeerror'] = 'Lähetettävä tiedosto ylittää sallitun enimmäiskoon $size';
$messages['copysuccess'] = 'Kopioitu $nr osoitetta onnistuneesti';
$messages['copyerror'] = 'Yhtään osoitetta ei voitu kopioida';
$messages['sourceisreadonly'] = 'Tämän osoitteen lähde on kirjoitussuojattu';
$messages['errorsavingcontact'] = 'Yhteystietoa ei voitu tallentaa';
$messages['movingmessage'] = 'Siirretään viestiä...';
$messages['receiptsent'] = 'Lukukuittaus lähetetty onnistuneesti';
$messages['errorsendingreceipt'] = 'Lukukuittausta ei voitu lähettää';
$messages['nodeletelastidentity'] = 'Et voi poistaa tätä identiteettiä, koska se on ainoa jäljelläoleva';
$messages['addsubfolderhint'] = 'Uusi kansio luodaan valitun kansion alikansiona';
$messages['forbiddencharacter'] = 'Kansion nimessä on kiellettyjä merkkejä';
$messages['selectimportfile'] = 'Valitse lähetettävä tiedosto';
$messages['addresswriterror'] = 'Valittuun osoitekirjaan ei voi kirjoittaa';
$messages['importwait'] = 'Tuodaan, odota...';
$messages['importerror'] = 'Tuonti epäonnistui! Lähetetty tiedosto ei ole vCard -muodossa.';
$messages['importconfirm'] = '<b>$inserted kontaktia tuotu onnistuneesti $inserted kontaktioa, $skipped olemassaolevaa kontaktia hypättiin yli</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Toiminto ei ole sallittu!';
$messages['nofromaddress'] = 'Valittu identiteetti ei sisällä sähköpostiosoitetta';

?>
