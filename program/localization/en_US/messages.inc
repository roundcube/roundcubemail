<?php

/*

 +-----------------------------------------------------------------------+
 | language/en_US/messages.inc                                           |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$messages = array();
$messages['loginfailed']  = 'Login failed';
$messages['cookiesdisabled'] = 'Your browser does not accept cookies';
$messages['sessionerror'] = 'Your session is invalid or expired';
$messages['imaperror'] = 'Connection to IMAP server failed';
$messages['nomessagesfound'] = 'No messages found in this mailbox';
$messages['loggedout'] = 'You have successfully terminated the session. Good bye!';
$messages['mailboxempty'] = 'Mailbox is empty';
$messages['loading'] = 'Loading...';
$messages['loadingdata'] = 'Loading data...';
$messages['checkingmail'] = 'Checking for new messages...';
$messages['sendingmessage'] = 'Sending message...';
$messages['messagesent'] = 'Message sent successfully';
$messages['savingmessage'] = 'Saving message...';
$messages['messagesaved'] = 'Message saved to Drafts';
$messages['successfullysaved'] = 'Successfully saved';
$messages['addedsuccessfully'] = 'Contact added successfully to address book';
$messages['contactexists'] = 'A contact with this e-mail address already exists';
$messages['blockedimages'] = 'To protect your privacy, remote images are blocked in this message.';
$messages['encryptedmessage'] = 'This is an encrypted message and can not be displayed. Sorry!';
$messages['nocontactsfound'] = 'No contacts found';
$messages['contactnotfound'] = 'The requested contact was not found';
$messages['sendingfailed'] = 'Failed to send message';
$messages['errorsavingsent'] = 'An error occured while saving sent message';
$messages['errorsaving'] = 'An error occured while saving';
$messages['errormoving'] = 'Could not move the message';
$messages['errordeleting'] = 'Could not delete the message';
$messages['deletecontactconfirm']  = 'Do you really want to delete the selected contact(s)?';
$messages['deletemessagesconfirm'] = 'Do you really want to delete the selected message(s)?';
$messages['deletefolderconfirm']  = 'Do you really want to delete this folder?';
$messages['purgefolderconfirm']  = 'Do you really want to delete all messages in this folder?';
$messages['formincomplete'] = 'The form was not completely filled out';
$messages['noemailwarning'] = 'Please enter a valid email address';
$messages['nonamewarning']  = 'Please enter a name';
$messages['nopagesizewarning'] = 'Please enter a page size';
$messages['norecipientwarning'] = 'Please enter at least one recipient';
$messages['nosubjectwarning']  = 'The "Subject" field is empty. Would you like to enter one now?';
$messages['nobodywarning'] = 'Send this message without text?';
$messages['notsentwarning'] = 'Message has not been sent. Do you want to discard your message?';
$messages['noldapserver'] = 'Please select an ldap server to search';
$messages['nocontactsreturned'] = 'No contacts were found';
$messages['nosearchname'] = 'Please enter a contact name or email address';
$messages['searchsuccessful'] = '$nr messages found';
$messages['searchnomatch'] = 'Search returned no matches';
$messages['searching'] = 'Searching...';
$messages['checking'] = 'Checking...';
$messages['nospellerrors'] = 'No spelling errors found';
$messages['folderdeleted'] = 'Folder successfully deleted';
$messages['deletedsuccessfully'] = "Successfully deleted";
$messages['converting'] = 'Removing formatting from message...';
$messages['messageopenerror'] = 'Could not load message from server';
$messages['fileuploaderror'] = 'File upload failed';
$messages['filesizeerror'] = 'The uploaded file exceeds the maximum size of $size';
$messages['copysuccess'] = 'Successfully copied $nr addresses';
$messages['copyerror'] = 'Could not copy any addresses';
$messages['sourceisreadonly'] = 'This address source is read only';
$messages['errorsavingcontact'] = 'Could not save the contact address';
$messages['movingmessage'] = 'Moving message...';
$messages['receiptsent'] = 'Successfully sent a read receipt';
$messages['errorsendingreceipt'] = 'Could not send the receipt';
$messages['nodeletelastidentity'] = 'You cannot delete this identity, it\'s your last one.';
$messages['addsubfolderhint'] = 'This folder will be created as subfolder of the currently selected one';
$messages['forbiddencharacter'] = 'Folder name contains a forbidden character';
$messages['selectimportfile'] = 'Please select a file to upload';
$messages['addresswriterror'] = 'The selected address book is not writeable';
$messages['importwait'] = 'Importing, please wait...';
$messages['importerror'] = 'Import failed! The uploaded file is not a valid vCard file.';
$messages['importconfirm'] = '<b>Successfully imported $inserted contacts, $skipped existing entries skipped</b>:<p><em>$names</em></p>';

?>