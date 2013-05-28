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
$messages['errortitle']  = 'An error occurred!';
$messages['loginfailed']  = 'Theip an iarraidh chun logáil tú isteach';
$messages['cookiesdisabled'] = 'Níor glac do sracléitheoir an fianán seisiúin';
$messages['sessionerror'] = 'Tá an seisiúin neamhbhailí nó as feidhm';
$messages['storageerror'] = 'Theip an iarraidh chun nasc go dtí an freastalaí Prótacal Rochtana Teachtaireachtaí Idirlín (IMAP)';
$messages['servererror'] = 'Server Error!';
$messages['servererrormsg'] = 'Server Error: $msg';
$messages['dberror'] = 'Database Error!';
$messages['requesttimedout'] = 'Request timed out';
$messages['errorreadonly'] = 'Unable to perform operation. Folder is read-only.';
$messages['errornoperm'] = 'Unable to perform operation. Permission denied.';
$messages['erroroverquota'] = 'Unable to perform operation. No free disk space.';
$messages['erroroverquotadelete'] = 'No free disk space. Use SHIFT+DEL to delete a message.';
$messages['invalidrequest'] = 'Invalid request! No data was saved.';
$messages['invalidhost'] = 'Invalid server name.';
$messages['nomessagesfound'] = 'Ní bhfuair mé aon teachtaireacht i bosca postas seo';
$messages['loggedout'] = 'D\'éirigh tú logáil amach. Slán Leat!';
$messages['mailboxempty'] = 'Tá an bosca postas folamh';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'Fan nóiméad, ag lódáil...';
$messages['uploading'] = 'Uploading file...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'Ag lódáil sonraí...';
$messages['checkingmail'] = 'Ag seiceáil chun teachtaireacht nua...';
$messages['sendingmessage'] = 'Ag seol mo teachtaireacht láithreach bonn...';
$messages['messagesent'] = 'Sheol mé an teachtaireacht';
$messages['savingmessage'] = 'Ag sábháil an teachtaireacht...';
$messages['messagesaved'] = 'Sábháil mé an teachtaireacht i dréachtaí';
$messages['successfullysaved'] = 'D\'éirigh mé agus sábháil mé';
$messages['addedsuccessfully'] = 'D\'éirigh mé agus shábháil mé duine nua go dtí an Leabhair Seoltaí';
$messages['contactexists'] = 'Tá duine i do Leabhair Seoltaí agus tá an seoladh ríomhphost seo aige';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'Chun do príobháide a cosain, choiscinn na híomhánna san teachtaireacht seo';
$messages['encryptedmessage'] = 'Tá an teachtaireacht seo i criptiúchán, Níl an cumas agam chun é a tionscail';
$messages['nocontactsfound'] = 'Ní raibh aon buine san Leabhair Seoltaí seo';
$messages['contactnotfound'] = 'Ní bhfuair mé an duine seo';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'Theip an iarraidh chun an teachtaireacht a seol';
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message.';
$messages['errorsavingsent'] = 'An error occured while saving sent message.';
$messages['errorsaving'] = 'Theip mé agus ní shábháil';
$messages['errormoving'] = 'Theip mé agus ní bog an teachtaireacht';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'Theip mé agus níor scrios me an teachtaireacht';
$messages['errormarking'] = 'Could not mark the message(s).';
$messages['deletecontactconfirm']  = 'Ar mhaith leat scrios na daoine seo?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'Ar mhaith leat scrios na teachtaireacht seo?';
$messages['deletefolderconfirm']  = 'Ar mhaith leat scrios an fillteán seo?';
$messages['purgefolderconfirm']  = 'Ar mhaith leat scrios gach teachtaireacht san fillteán seo?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'Deleting folder...';
$messages['foldermoving'] = 'Moving folder...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'Níor chríochnaigh tú an foirm';
$messages['noemailwarning'] = 'Abair liom seoladh ríomhphost nach bfhuil neamhbhaillí';
$messages['nonamewarning']  = 'Abair liom do ainm';
$messages['nopagesizewarning'] = 'Abair liom saghas leathanaigh';
$messages['nosenderwarning'] = 'Please enter sender e-mail address.';
$messages['norecipientwarning'] = 'Abair liom faighteoir amhain ar a laghad';
$messages['nosubjectwarning']  = 'Níl aon ábhair ar an teachtaireacht, ba mhaith tú déarfaidh mé ábair?';
$messages['nobodywarning'] = 'Níl aon teacs san teachtaireacht, ba mhaith tú déarfaidh mé teachtaireacht ar bith?';
$messages['notsentwarning'] = 'Theip an iarraidh chun an teachtaireacht a seo. ba mhaith tú scrios do teachtaireacht?';
$messages['noldapserver'] = 'Abair liom eolaire LDAP chun é a cuardaigh';
$messages['nosearchname'] = 'Abair liom ainm duine nó seoladh ríomhphost';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = 'Fuair mé $n teachtaireacht';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'Ní bhfuair mé aon rud as an ceist seo';
$messages['searching'] = 'Ag tástáil...';
$messages['checking'] = 'Bím ag dhéanamh na mhaoirseachtí...';
$messages['nospellerrors'] = 'Ní bhfuair mé aon earráidí i teachtaireacht seo';
$messages['folderdeleted'] = 'D\'éirigh mé, Scrios mé an fillteán';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'D\'éirigh mé, Scrios mé';
$messages['converting'] = 'Scrios mé formáidithe as an teachtaireacht seo';
$messages['messageopenerror'] = 'Theip orm chun an teachtaireacht seo a tarrtháil as an freastalaí';
$messages['fileuploaderror'] = 'Theip an suas-luchtú';
$messages['filesizeerror'] = 'Beigh an comhad ró-mhor. Is ea $size uas-saghas chun admháil léite';
$messages['copysuccess'] = 'D\'éirigh mé agus chuir mé $nr seolagh i do Leabhair Seoltaí';
$messages['copyerror'] = 'Theip mé agus níor dfhag mé aon seolagh';
$messages['sourceisreadonly'] = 'Tá an foinse seolaigh seo inléite amháin';
$messages['errorsavingcontact'] = 'Theip mé, Níl an cumas agam an seolagh seo a sábháil';
$messages['movingmessage'] = 'Moving message(s)...';
$messages['copyingmessage'] = 'Copying message(s)...';
$messages['copyingcontact'] = 'Copying contact(s)...';
$messages['deletingmessage'] = 'Deleting message(s)...';
$messages['markingmessage'] = 'Marking message(s)...';
$messages['addingmember'] = 'Adding contact(s) to the group...';
$messages['removingmember'] = 'Removing contact(s) from the group...';
$messages['receiptsent'] = 'Successfully sent a read receipt.';
$messages['errorsendingreceipt'] = 'Could not send the receipt.';
$messages['deleteidentityconfirm'] = 'Do you really want to delete this identity?';
$messages['nodeletelastidentity'] = 'You cannot delete this identity, it\'s your last one.';
$messages['forbiddencharacter'] = 'Folder name contains a forbidden character.';
$messages['selectimportfile'] = 'Please select a file to upload.';
$messages['addresswriterror'] = 'The selected address book is not writeable.';
$messages['contactaddedtogroup'] = 'Successfully added the contacts to this group.';
$messages['contactremovedfromgroup'] = 'Successfully removed contacts from this group.';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = 'Importing, please wait...';
$messages['importformaterror'] = 'Import failed! The uploaded file is not a valid import data file.';
$messages['importconfirm'] = '<b>Successfully imported $inserted contacts</b>';
$messages['importconfirmskipped'] = '<b>Skipped $skipped existing entries</b>';
$messages['opnotpermitted'] = 'Operation not permitted!';
$messages['nofromaddress'] = 'Missing e-mail address in selected identity.';
$messages['editorwarning'] = 'Switching to the plain text editor will cause all text formatting to be lost. Do you wish to continue?';
$messages['httpreceivedencrypterror'] = 'A fatal configuration error occurred. Contact your administrator immediately. <b>Your message can not be sent.</b>';
$messages['smtpconnerror'] = 'SMTP Error ($code): Connection to server failed.';
$messages['smtpautherror'] = 'SMTP Error ($code): Authentication failed.';
$messages['smtpfromerror'] = 'SMTP Error ($code): Failed to set sender "$from" ($msg).';
$messages['smtptoerror'] = 'SMTP Error ($code): Failed to add recipient "$to" ($msg).';
$messages['smtprecipientserror'] = 'SMTP Error: Unable to parse recipients list.';
$messages['smtperror'] = 'SMTP Error: $msg';
$messages['emailformaterror'] = 'Invalid e-mail address: $email';
$messages['toomanyrecipients'] = 'Too many recipients. Reduce the number of recipients to $max.';
$messages['maxgroupmembersreached'] = 'The number of group members exceeds the maximum of $max.';
$messages['internalerror'] = 'An internal error occured. Please try again.';
$messages['contactdelerror'] = 'Could not delete contact(s).';
$messages['contactdeleted'] = 'Contact(s) deleted successfully.';
$messages['contactrestoreerror'] = 'Could not restore deleted contact(s).';
$messages['contactrestored'] = 'Contact(s) restored successfully.';
$messages['groupdeleted'] = 'Group deleted successfully.';
$messages['grouprenamed'] = 'Group renamed successfully.';
$messages['groupcreated'] = 'Group created successfully.';
$messages['savedsearchdeleted'] = 'Saved search deleted successfully.';
$messages['savedsearchdeleteerror'] = 'Could not delete saved search.';
$messages['savedsearchcreated'] = 'Saved search created successfully.';
$messages['savedsearchcreateerror'] = 'Could not create saved search.';
$messages['messagedeleted'] = 'Message(s) deleted successfully.';
$messages['messagemoved'] = 'Message(s) moved successfully.';
$messages['messagecopied'] = 'Message(s) copied successfully.';
$messages['messagemarked'] = 'Message(s) marked successfully.';
$messages['autocompletechars'] = 'Enter at least $min characters for autocompletion.';
$messages['autocompletemore'] = 'More matching entries found. Please type more characters.';
$messages['namecannotbeempty'] = 'Name cannot be empty.';
$messages['nametoolong'] = 'Name is too long.';
$messages['folderupdated'] = 'Folder updated successfully.';
$messages['foldercreated'] = 'Folder created successfully.';
$messages['invalidimageformat'] = 'Not a valid image format.';
$messages['mispellingsfound'] = 'Spelling errors detected in the message.';
$messages['parentnotwritable'] = 'Unable to create/move folder into selected parent folder. No access rights.';
$messages['messagetoobig'] = 'The message part is too big to process it.';
$messages['attachmentvalidationerror'] = 'WARNING! This attachment is suspicious because its type doesn\'t match the type declared in the message. If you do not trust the sender, you shouldn\'t open it in the browser because it may contain malicious contents.<br/><br/><em>Expected: $expected; found: $detected</em>';
$messages['noscriptwarning'] = 'Warning: This webmail service requires Javascript! In order to use it please enable Javascript in your browser\'s settings.';

?>
