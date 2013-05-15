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
$messages['loginfailed']  = 'Innskráning mistókst';
$messages['cookiesdisabled'] = 'Vafrinn þinn vill ekki taka við kökum (cookies)';
$messages['sessionerror'] = 'Innskráningin þín ógild eða útrunnin';
$messages['storageerror'] = 'Tengin við IMAP-miðlara mistókst';
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
$messages['nomessagesfound'] = 'Engin skeyti eru í þessu pósthólfi';
$messages['loggedout'] = 'Þú hefur útskráðst. Bless!';
$messages['mailboxempty'] = 'Pósthólfið er tómt';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'Hleð...';
$messages['uploading'] = 'Uploading file...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'Hleð gögn...';
$messages['checkingmail'] = 'Gái að nýjum skeytum...';
$messages['sendingmessage'] = 'Sending message...';
$messages['messagesent'] = 'Skeyti var sent';
$messages['savingmessage'] = 'Vista skeyti...';
$messages['messagesaved'] = 'Skeytið vistað í Drög';
$messages['successfullysaved'] = 'Vistun heppnaðist!';
$messages['addedsuccessfully'] = 'Netfangi var bætt í netfangaskrá';
$messages['contactexists'] = 'Þetta netfang er þegar skráð í netfangaskrána';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'Til verndar hafa allar myndir í þessu skeyti verið blokkaðar.';
$messages['encryptedmessage'] = 'Þetta er dulkóðað skeyti og því miður er ekki hægt að birta það';
$messages['nocontactsfound'] = 'Engin netföng';
$messages['contactnotfound'] = 'The requested contact was not found.';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'Sending misheppnaðist';
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message.';
$messages['errorsavingsent'] = 'An error occured while saving sent message.';
$messages['errorsaving'] = 'Villa kom upp þegar var verið að vista';
$messages['errormoving'] = 'Gat ekki fært skeytið';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'Gat ekki eytt skeytinu';
$messages['errormarking'] = 'Could not mark the message(s).';
$messages['deletecontactconfirm']  = 'Ertu viss um að þú viljir eyða völdum netföngum?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'Do you really want to delete selected message(s)?';
$messages['deletefolderconfirm']  = 'Ertu viss um að vilja eyða þessari möppu?';
$messages['purgefolderconfirm']  = 'Do you really want to delete all messages in this folder?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'Deleting folder...';
$messages['foldermoving'] = 'Moving folder...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'Formið var ekki fyllt út að fullu';
$messages['noemailwarning'] = 'Sláðu inn gildt netfang';
$messages['nonamewarning']  = 'Sláðu inn nafn';
$messages['nopagesizewarning'] = 'Sláðu inn síðustærð';
$messages['nosenderwarning'] = 'Please enter sender e-mail address.';
$messages['norecipientwarning'] = 'Í það minnsta einn viðtakandi verður að vera';
$messages['nosubjectwarning']  = 'Efnislínan er tóm. Viltu slá eitthvað inn í hana?';
$messages['nobodywarning'] = 'Viltu senda tómt skeyti?';
$messages['notsentwarning'] = 'Skeytið hefur ekki verið send. Viltu henda skeytinu?';
$messages['noldapserver'] = 'Please select an ldap server to search';
$messages['nosearchname'] = 'Sláðu inn nafn eða netfang';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = '$nr skeyti fundust';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'Leit skilaði engu';
$messages['searching'] = 'Leita...';
$messages['checking'] = 'Gái...';
$messages['nospellerrors'] = 'Engar innsláttarvillur fundust';
$messages['folderdeleted'] = 'Möppu var eytt';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'Successfully deleted.';
$messages['converting'] = 'Removing formatting...';
$messages['messageopenerror'] = 'Could not load message from server.';
$messages['fileuploaderror'] = 'File upload failed.';
$messages['filesizeerror'] = 'The uploaded file exceeds the maximum size of $size.';
$messages['copysuccess'] = 'Successfully copied $nr addresses.';
$messages['copyerror'] = 'Could not copy any addresses.';
$messages['sourceisreadonly'] = 'This address source is read only.';
$messages['errorsavingcontact'] = 'Could not save the contact address.';
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
