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
$messages['loginfailed']  = 'लॉगिन नहीं हो पाया';
$messages['cookiesdisabled'] = 'आपका ब्राऊसर कुकी स्वीकार नहीं कर रहा है';
$messages['sessionerror'] = 'सत्र समाप्त या अवैद्य';
$messages['storageerror'] = 'आईमैप (IMAP) सर्वर से सम्पर्क नहीं हो पाया';
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
$messages['nomessagesfound'] = 'इस मेलबॉक्स में कोई मेल नहीं है';
$messages['loggedout'] = 'सत्र समाप्त. टा टा!';
$messages['mailboxempty'] = 'मेलबॉक्स खाली है';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'लोड हो रहा है';
$messages['uploading'] = 'Uploading file...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'डाटा लोड हो रहा है...';
$messages['checkingmail'] = 'क्या नया मेल है?...';
$messages['sendingmessage'] = 'मेल भेजा जा रहा है...';
$messages['messagesent'] = 'मेल भेजा गया है';
$messages['savingmessage'] = 'मेल सहेजा जा रहा है...';
$messages['messagesaved'] = 'मेल ड्राफ़्ट फोल्डर में जमा हो रहा है';
$messages['successfullysaved'] = 'सहेजा गया है';
$messages['addedsuccessfully'] = 'पता खाते में पता जोडा गया है';
$messages['contactexists'] = 'इस ई-मेल पता से एक पता पेहले से है';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'आप के प्राईवेसी के लिये और स्पैम से बचने के लिये इस मेल में सदूर चित्र नहीं दिखाये गये हैं।';
$messages['encryptedmessage'] = 'माफ़ कीजिये, यह मेल कूट रूप में है, इस कारण दिखाया नहीं जा सकता।';
$messages['nocontactsfound'] = 'कोई पता नहीं मिला';
$messages['contactnotfound'] = 'The requested contact was not found.';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'मेल नहीं भिज पाया';
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message.';
$messages['errorsavingsent'] = 'An error occured while saving sent message.';
$messages['errorsaving'] = 'सहेजते-सहेजते गडबड हो गया';
$messages['errormoving'] = 'मेल खिस्का नहीं';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'मेल मिटा नहीं';
$messages['errormarking'] = 'Could not mark the message(s).';
$messages['deletecontactconfirm']  = 'चुने गये पते को सचमुच मिटाना है ?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'चुने गये मेल को सचमुच मिटाना है ?';
$messages['deletefolderconfirm']  = 'फ़ोल्डर को सचमुच मिटाऐं ?';
$messages['purgefolderconfirm']  = 'फ़ोल्डर में पडे सारे मेल को सचमुच मिटाना है?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'Deleting folder...';
$messages['foldermoving'] = 'Moving folder...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'फ़ॉर्म पूरी तरह से भरा नहीं';
$messages['noemailwarning'] = 'वैद्य मेल पता भरें';
$messages['nonamewarning']  = 'नाम भरें';
$messages['nopagesizewarning'] = 'पन्ना माप भरें';
$messages['nosenderwarning'] = 'Please enter sender e-mail address.';
$messages['norecipientwarning'] = 'कम-से-कम एक पाने वाले का पता भरें';
$messages['nosubjectwarning']  = 'मेल का कोई विशय नहीं बताया गया है। क्या आप विशय डालना चाहते हैं ?';
$messages['nobodywarning'] = 'बिना कुछ लिखे मेल भेजें?';
$messages['notsentwarning'] = 'मेल भिजा नहीं. मेल रद्द करें ?';
$messages['noldapserver'] = 'खोज के लिये एक ऐलडैप सर्वर चुनें';
$messages['nosearchname'] = 'पता या पते का नाम भरें';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = '$nr मेल मिले';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'खोज असफ़ल';
$messages['searching'] = 'खोजा रहे हैं...';
$messages['checking'] = 'चाँचा चा रहा है...';
$messages['nospellerrors'] = 'कोई स्पैलिंग गलती नहीं मिली';
$messages['folderdeleted'] = 'फ़ोल्डर मिट गया';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'मिट गया';
$messages['converting'] = 'मेल से फ़ॉर्मैटिंग निकाला जा रहा है...';
$messages['messageopenerror'] = 'सर्वर से मेल लोड नहीं हो पाया';
$messages['fileuploaderror'] = 'फ़ाईल अपलोड नहीं हो पाया';
$messages['filesizeerror'] = 'अपलोड के लिये फ़ाईल हद $size से बड़ा है';
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
