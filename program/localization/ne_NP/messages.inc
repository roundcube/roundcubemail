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
$messages['loginfailed']  = 'लागईन हुन पाएन';
$messages['cookiesdisabled'] = 'तपाईको ब्राऊसरले कुकी स्वीकार गारिरहेको छैन';
$messages['sessionerror'] = 'सत्र समाप्त वा अवैद्य';
$messages['storageerror'] = 'सर्वर संग सम्पर्क हुन पाएन';
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
$messages['nomessagesfound'] = 'यो मेलबक्स मा कुनै मेल छैन';
$messages['loggedout'] = 'सत्र समाप्त । नमास्कार!';
$messages['mailboxempty'] = 'मेल बक्स खाली छ';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'लोड भईरहेको छ...';
$messages['uploading'] = 'Uploading file...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'डाटा लोड भईरहेको छै...';
$messages['checkingmail'] = 'नया मेलको खोजी भईरहेको छ...';
$messages['sendingmessage'] = 'मेल पठाईदै रहेको छ...';
$messages['messagesent'] = 'मेल सफल्तापूर्वक पठाईयो';
$messages['savingmessage'] = 'मेल जोगाईदै छ...';
$messages['messagesaved'] = 'मेल ड्राफ्ट फोल्डरमा जोगाईयो';
$messages['successfullysaved'] = 'सफल्तापूर्वक जोगाईयो';
$messages['addedsuccessfully'] = 'सम्पर्क पुस्तिकामा सफल्तापूर्वक सम्पर्क प्रविष्ट गारियो';
$messages['contactexists'] = 'यो ई-मेल पहिलै लिईसकिएको छ';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'तपाईको प्राईवेसी को लागि तथा अन्य स्पामबाट बच्नकोलागि यो मेलमा चित्रहुरु देखाईएका छैनन्।';
$messages['encryptedmessage'] = 'माफ गर्नुहोसे, यो मेल ईन्क्रिफ्ट गरिएको रूपमा छ, त्यसैले देखाउन सकिदैन!';
$messages['nocontactsfound'] = 'कुनै सम्पर्क भेटिएन';
$messages['contactnotfound'] = 'अनुरोध गारिएको सम्पर्क भेटिएन';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'मेल पठाउन सकिएन';
$messages['senttooquickly'] = 'Please wait $sec sec(s). before sending this message.';
$messages['errorsavingsent'] = 'An error occured while saving sent message.';
$messages['errorsaving'] = 'जोगाउदा-जोगाउदै गडबड भायो';
$messages['errormoving'] = 'मेल सार्न सकिएन';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'मेल मेटाउन सकिएन';
$messages['errormarking'] = 'Could not mark the message(s).';
$messages['deletecontactconfirm']  = 'तापाईले छान्नुभएको सम्पर्कहरु, साँचै मेट्न चहानुहुन्छ?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'तापाईले छान्नु भएको मेलहरु, साँचै मेट्न चहानुहुन्छ?';
$messages['deletefolderconfirm']  = 'तापाईले साँचै यो फोल्डर मेट्न चहानु हुन्छ?';
$messages['purgefolderconfirm']  = 'तापाईले साँचै यो फोल्डरमा भएका सबै मेलहरु मेट्न चहानुहुन्छ?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'Deleting folder...';
$messages['foldermoving'] = 'Moving folder...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'फर्म पूर्ण रुपमा भरिएको छैन';
$messages['noemailwarning'] = 'कृपया वैद्य ई-मेल भर्नुहोस';
$messages['nonamewarning']  = 'कृपया नाम भर्नुहोस';
$messages['nopagesizewarning'] = 'कृपया पन्नाको नाप भर्नुहोस';
$messages['nosenderwarning'] = 'Please enter sender e-mail address.';
$messages['norecipientwarning'] = 'कृपया कम-से-कम एउटा प्रापकको विवरण भर्नुहोस';
$messages['nosubjectwarning']  = 'मेलको कुनै विषय तोकिएको छैनै। केहि तोक्न चहानुहुन्छ ?';
$messages['nobodywarning'] = 'बिना केहि लेखि पठाउने?';
$messages['notsentwarning'] = 'मेल पठएको छैन.. मेल रद्द गर्न चहानुहुन्छ?';
$messages['noldapserver'] = 'कृपया खोजको निम्ति एउटा ऐलडाप सर्वर छान्नहोस';
$messages['nosearchname'] = 'कृपया सम्पर्कको नाम या ई-मेल भर्नुहोस';
$messages['notuploadedwarning'] = 'Not all attachments have been uploaded yet. Please wait or cancel the upload.';
$messages['searchsuccessful'] = '$nr मेलहरु पाइयो';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'खोज असफल रहयो';
$messages['searching'] = 'खोजि भईरहेको छ...';
$messages['checking'] = 'जाँच भईरहेको छ...';
$messages['nospellerrors'] = 'कुनै गलत हिज्जे फेला परेनन्';
$messages['folderdeleted'] = 'फोल्डर सफल्तापूर्वक मेटियो';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'सफल्तापूर्वक मेटियो';
$messages['converting'] = 'मेलबाट फोर्माटिङ हटाईदै छ...';
$messages['messageopenerror'] = 'सर्वरबाट मेल लोड हुन सकेन';
$messages['fileuploaderror'] = 'फाईल अपलोड हुन असफल';
$messages['filesizeerror'] = 'अपलोड गरिएको फाईल हद $size भन्दा ठूलो छ';
$messages['copysuccess'] = 'सफल्तापूर्वक $nr ठेगानाहरु उतारियो';
$messages['copyerror'] = 'कुनै पनि ठेगानाहरु उतार्न सकिएन';
$messages['sourceisreadonly'] = 'यो ठेगाना को श्रोत पढ्नको लागि मात्र उपलब्ध छ';
$messages['errorsavingcontact'] = 'स्मपर्कको ठेगाना जोगाउन सकिएन';
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
