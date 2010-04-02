<?php

/*

 +-----------------------------------------------------------------------+
 | language/en_US/messages.inc                                           |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
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
$messages['servererror'] = 'Server Error!';
$messages['invalidrequest'] = 'Invalid request! No data was saved.';
$messages['nomessagesfound'] = 'No messages found in this mailbox';
$messages['loggedout'] = 'You have successfully terminated the session. Good bye!';
$messages['mailboxempty'] = 'Mailbox is empty';
$messages['loading'] = 'Loading...';
$messages['uploading'] = 'Uploading file...';
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
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message';
$messages['errorsavingsent'] = 'An error occured while saving sent message';
$messages['errorsaving'] = 'An error occured while saving';
$messages['errormoving'] = 'Could not move the message(s)';
$messages['errorcopying'] = 'Could not copy the message(s)';
$messages['errordeleting'] = 'Could not delete the message(s)';
$messages['errormarking'] = 'Could not mark the message(s)';
$messages['deletecontactconfirm']  = 'Do you really want to delete the selected contact(s)?';
$messages['deletemessagesconfirm'] = 'Do you really want to delete the selected message(s)?';
$messages['deletefolderconfirm']  = 'Do you really want to delete this folder?';
$messages['purgefolderconfirm']  = 'Do you really want to delete all messages in this folder?';
$messages['foldercreating'] = 'Creating folder...';
$messages['folderdeleting'] = 'Deleting folder...';
$messages['folderrenaming'] = 'Renaming folder...';
$messages['foldermoving'] = 'Moving folder...';
$messages['formincomplete'] = 'The form was not completely filled out';
$messages['noemailwarning'] = 'Please enter a valid email address';
$messages['nonamewarning']  = 'Please enter a name';
$messages['nopagesizewarning'] = 'Please enter a page size';
$messages['nosenderwarning'] = 'Please enter sender e-mail address';
$messages['norecipientwarning'] = 'Please enter at least one recipient';
$messages['nosubjectwarning']  = 'The "Subject" field is empty. Would you like to enter one now?';
$messages['nobodywarning'] = 'Send this message without text?';
$messages['notsentwarning'] = 'Message has not been sent. Do you want to discard your message?';
$messages['noldapserver'] = 'Please select an ldap server to search';
$messages['nocontactsreturned'] = 'No contacts were found';
$messages['nosearchname'] = 'Please enter a contact name or email address';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = '$nr messages found';
$messages['searchnomatch'] = 'Search returned no matches';
$messages['searching'] = 'Searching...';
$messages['checking'] = 'Checking...';
$messages['nospellerrors'] = 'No spelling errors found';
$messages['folderdeleted'] = 'Folder successfully deleted';
$messages['deletedsuccessfully'] = "Successfully deleted";
$messages['converting'] = 'Removing formatting...';
$messages['messageopenerror'] = 'Could not load message from server';
$messages['fileuploaderror'] = 'File upload failed';
$messages['filesizeerror'] = 'The uploaded file exceeds the maximum size of $size';
$messages['copysuccess'] = 'Successfully copied $nr addresses';
$messages['copyerror'] = 'Could not copy any addresses';
$messages['sourceisreadonly'] = 'This address source is read only';
$messages['errorsavingcontact'] = 'Could not save the contact address';
$messages['movingmessage'] = 'Moving message...';
$messages['copyingmessage'] = 'Copying message...';
$messages['receiptsent'] = 'Successfully sent a read receipt';
$messages['errorsendingreceipt'] = 'Could not send the receipt';
$messages['nodeletelastidentity'] = 'You cannot delete this identity, it\'s your last one.';
$messages['addsubfolderhint'] = 'This folder will be created as subfolder of the currently selected one';
$messages['forbiddencharacter'] = 'Folder name contains a forbidden character';
$messages['selectimportfile'] = 'Please select a file to upload';
$messages['addresswriterror'] = 'The selected address book is not writeable';
$messages['contactaddedtogroup'] = 'Successfully added the contacts to this group';
$messages['contactremovedfromgroup'] = 'Successfully remove contacts from this group';
$messages['importwait'] = 'Importing, please wait...';
$messages['importerror'] = 'Import failed! The uploaded file is not a valid vCard file.';
$messages['importconfirm'] = '<b>Successfully imported $inserted contacts, $skipped existing entries skipped</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = 'Operation not permitted!';
$messages['nofromaddress'] = 'Missing e-mail address in selected identity';
$messages['editorwarning'] = 'Switching to the plain text editor will cause all text formatting to be lost. Do you wish to continue?';
$messages['httpreceivedencrypterror'] = 'A fatal configuration error occurred. Contact your administrator immediately. <b>Your message can not be sent.</b>';
$messages['smtpconnerror'] = 'SMTP Error ($code): Connection to server failed';
$messages['smtpautherror'] = 'SMTP Error ($code): Authentication failed';
$messages['smtpfromerror'] = 'SMTP Error ($code): Failed to set sender "$from"';
$messages['smtptoerror'] = 'SMTP Error ($code): Failed to add recipient "$to"';
$messages['smtprecipientserror'] = 'SMTP Error: Unable to parse recipients list';
$messages['smtperror'] = 'SMTP Error: $msg';
$messages['emailformaterror'] = 'Incorrect e-mail address: $email';
$messages['toomanyrecipients'] = 'Too many recipients. Reduce the number of recipients to $max.';

?>
