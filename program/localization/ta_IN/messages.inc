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
$messages['loginfailed']  = 'நுழைவு தோல்வியடைந்தது';
$messages['cookiesdisabled'] = 'உங்கள் உலாவி நினைவிகளை அனுமதிப்பதில்லை';
$messages['sessionerror'] = 'உங்கள் அமர்வு செல்லாது அல்லது முடிவுற்றது';
$messages['storageerror'] = 'IMAP சேவையகத்திற்கான இணைப்பு தோல்வியடைந்தது';
$messages['servererror'] = 'சேவையக பிழை!';
$messages['servererrormsg'] = 'Server Error: $msg';
$messages['dberror'] = 'Database Error!';
$messages['requesttimedout'] = 'Request timed out';
$messages['errorreadonly'] = 'Unable to perform operation. Folder is read-only.';
$messages['errornoperm'] = 'Unable to perform operation. Permission denied.';
$messages['invalidrequest'] = 'செல்லாத வேண்டுகோள்! எந்த தரவும் சேமிக்கப்படவில்லை.';
$messages['invalidhost'] = 'Invalid server name.';
$messages['nomessagesfound'] = 'இந்த தபால் பெட்டியில் எந்த தரவும் சேமிக்கப்படவில்லை';
$messages['loggedout'] = 'நீங்கள் அமர்வை வெற்றிகரமாக முடித்தீர்கள். வணக்கம்';
$messages['mailboxempty'] = 'தபால் பெட்டி காலியாக உள்ளது';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'ஏற்றுகிறது...';
$messages['uploading'] = 'கோப்பை ஏற்றுகிறது...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'தரவை ஏற்றுகிறது...';
$messages['checkingmail'] = 'புது செய்திகளை சரிபார்க்கிறது...';
$messages['sendingmessage'] = 'செய்தியை அனுப்புகிறது...';
$messages['messagesent'] = 'செய்தி வெற்றிகரமாக அனுப்பப்பட்டது';
$messages['savingmessage'] = 'செய்தியை சேமிக்கிறது...';
$messages['messagesaved'] = 'செய்தி வரைவில் சேமிக்கப்பட்டது';
$messages['successfullysaved'] = 'வெற்றிகரமாக சேமிக்கப்பட்டது';
$messages['addedsuccessfully'] = 'தொடர்பு சேமிக்கப்பட்டது';
$messages['contactexists'] = 'இந்த மின்னஞ்சல் முகவரியுடன் ஒரு தொடர்பு ஏற்கனவே உள்ளது';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'உங்கள் தனிப்பட்ட தகவலை பாதுகாக்க, தொலை தூரப் படங்கள் இந்த செய்தியில் தடுக்கப்பட்டது';
$messages['encryptedmessage'] = 'மன்னிக்கவும்! இந்த செய்தி குறிமுறையாக்கப்பட்டது அதனால் காட்ட முடியாது.';
$messages['nocontactsfound'] = 'எந்த தொடர்புகளும் காணப்படவில்லை';
$messages['contactnotfound'] = 'நீங்கள் வேண்டிய தொடர்பு காணப்படவில்லை';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'செய்தி அனுப்புவது தோல்வியுற்றது';
$messages['senttooquickly'] = 'இந்த செய்தி அனுப்புவதற்கு முன்பு தயவு செய்து $sec வினாடி(கள்) காத்திருக்கவும்.';
$messages['errorsavingsent'] = 'அனுப்பிய அஞ்சலை சேமிக்கும் போது பிழை ஏற்பட்டது';
$messages['errorsaving'] = 'சேமிக்கும் போது பிழை ஏற்பட்டது';
$messages['errormoving'] = 'செய்தியை நகர்த்த முடியவில்லை';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'செய்தியை நீக்க முடியவில்லை';
$messages['errormarking'] = 'செய்தியை நட்சத்திரமிட முடியவில்லை';
$messages['deletecontactconfirm']  = 'நீங்கள் தேர்ந்தெடுத்த தொடர்புகளை நீக்க விரும்புகிறீர்களா ?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'நீங்கள் தேர்ந்தெடுத்த செய்திகளை நீக்க விரும்புகிறீர்களா ?';
$messages['deletefolderconfirm']  = 'நீங்கள் இந்த அடைவை நீக்க விரும்புகிறீர்களா ?';
$messages['purgefolderconfirm']  = 'நீங்கள் இந்த அடைவிலிருக்கும் எல்லா செய்திகளையும் நீக்க விரும்புகிறீர்களா ?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'அடைவை நீக்குகிறது...';
$messages['foldermoving'] = 'அடைவை நகர்த்துகிறது...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'விண்ணப்பத்தை முழுமையாக நிறைவு செய்யவில்லை';
$messages['noemailwarning'] = 'தயவு செய்து உண்மையான மின்னஞ்சலை எழுதுங்கள்';
$messages['nonamewarning']  = 'தயவு செய்து பெயரை எழுதுங்கள்';
$messages['nopagesizewarning'] = 'தயவு செய்து தாழின் அளவை எழுதுங்கள்';
$messages['nosenderwarning'] = 'தயவு செய்து ஒரு அனுப்புனரின் மின்னஞ்சல் முகவரியாவது எழுதுங்கள்';
$messages['norecipientwarning'] = 'தயவு செய்து ஒரு பெறுனரையாவது எழுதுங்கள்';
$messages['nosubjectwarning']  = 'தலைப்பு காலியாக உள்ளது. இப்போது எழுத விரும்புகிறீர்களா?';
$messages['nobodywarning'] = 'உரையில்லாமல் இந்த செய்தியை அனுப்ப வேண்டுமா?';
$messages['notsentwarning'] = 'செய்தி அனுப்பப்படவில்லை. உங்கள் செய்தியை நிராகரிக்க விரும்புகிறீர்களா?';
$messages['noldapserver'] = 'தயவு செய்து தேடுவதற்கு Idap சேவையகத்தை தேர்ந்தெடுங்கள்';
$messages['nosearchname'] = 'தயவு செய்து தொடர்பின் பெயரோ மின்னஞ்சல் முகவரியோ எழுதுங்கள்';
$messages['notuploadedwarning'] = 'எல்லா இணைப்புகளும் ஏற்றப்படவில்லை. தயவு செய்து காத்திருங்கள் அல்லது ஏற்றத்தை ரத்து செய்யுங்கள்.';
$messages['searchsuccessful'] = '$nr செய்திகள் கண்டுபிடிக்கப்பட்டது';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'தேடுதல் எந்த பொருத்தத்தையும் அனுப்பவில்லை';
$messages['searching'] = 'தேடுகிறது...';
$messages['checking'] = 'சரி பார்க்கிறது...';
$messages['nospellerrors'] = 'எழுத்து பிழைகள் இல்லை';
$messages['folderdeleted'] = 'அடைவு வெற்றிகரமாக நீக்கப்பட்டது';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'வெற்றிகரமாக நீக்கப்பட்டது';
$messages['converting'] = 'வடிவத்தை நீக்குகிறது...';
$messages['messageopenerror'] = 'சேவையகத்திலிருந்து செய்தியை ஏற்ற முடியவில்லை';
$messages['fileuploaderror'] = 'கோப்பு ஏற்றம் தோல்வியடாந்தது';
$messages['filesizeerror'] = 'ஏற்றிய கோப்பு அதிகபட்ச அளவான $size-ஐ மீறியது';
$messages['copysuccess'] = 'வெற்றிகரமாக $nr முகவரிக்களை நகலெடுத்தது';
$messages['copyerror'] = 'எந்த முகவரியையும் நகலெடுக்க முடியவில்லை';
$messages['sourceisreadonly'] = 'இந்த முகவரியின் மூலத்தை படிக்க மட்டும்தான் முடியும்';
$messages['errorsavingcontact'] = 'தொடர்பு முகவரியை சேமிக்க முடியவில்லை';
$messages['movingmessage'] = 'செய்தியை நகர்த்துகிறது...';
$messages['copyingmessage'] = 'Copying message(s)...';
$messages['copyingcontact'] = 'Copying contact(s)...';
$messages['deletingmessage'] = 'Deleting message(s)...';
$messages['markingmessage'] = 'Marking message(s)...';
$messages['addingmember'] = 'Adding contact(s) to the group...';
$messages['removingmember'] = 'Removing contact(s) from the group...';
$messages['receiptsent'] = 'படித்த சீட்டு வெற்றிகரமாக அனுப்பப்பட்டது';
$messages['errorsendingreceipt'] = 'சீட்டை அனுப்ப முடியவில்லை';
$messages['deleteidentityconfirm'] = 'Do you really want to delete this identity?';
$messages['nodeletelastidentity'] = 'நீங்கள் இந்த அடையாளத்தை நீக்க முடியாது இது தான் கடைசி';
$messages['forbiddencharacter'] = 'அடைவு பெயரில் தடை செய்யப்பட்ட எழுத்துக்கள் உள்ளது';
$messages['selectimportfile'] = 'தயவு செய்து ஏற்றுவதற்கு ஒரு கோப்பை தேர்ந்தெடுங்கள்';
$messages['addresswriterror'] = 'நீங்கள் தேர்ந்தெடுத்த தொடர்புகளில் எழுத முடியாது';
$messages['contactaddedtogroup'] = 'Successfully added the contacts to this group.';
$messages['contactremovedfromgroup'] = 'Successfully removed contacts from this group.';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = 'ஏற்றுகிறது, தயவு செய்து காத்திருங்கள்...';
$messages['importformaterror'] = 'Import failed! The uploaded file is not a valid import data file.';
$messages['importconfirm'] = '<b>வெற்றிகரமாக $inserted தொடர்புகள் ஏற்றப்பட்டது, ஏற்கனவே உள்ள $skipped தொடர்புகள் தவிர்க்கப்பட்டது</b>: <p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Skipped $skipped existing entries</b>';
$messages['opnotpermitted'] = 'பணி அனுமதிக்கப்படவில்லை!';
$messages['nofromaddress'] = 'நீங்கள் தேர்ந்தெடுத்த அடையாளத்தில் மின்னஞ்சல் இல்லை';
$messages['editorwarning'] = 'எளிய உரை திருத்திக்கு மாறுவதன் மூலம் எல்லா உரை வடிவங்களும் தொலைந்து போகக் கூடும். தொடர விரும்புகிறீர்களா ?';
$messages['httpreceivedencrypterror'] = 'அமைப்பில் பெரிய பிழை ஏற்பட்டது. உங்கள் நிர்வாகியை உடனே தொடர்பு கொள்ளவும். <b>உங்கள் செய்தியை அனுப்ப முடியாது.</b>';
$messages['smtpconnerror'] = 'SMTP பிழை ($code): சேவையகத்தின் இணைப்பு தோல்வியடைந்தது';
$messages['smtpautherror'] = 'SMTP பிழை ($code): நம்பகத்தன்மை தோல்வியடைந்தது';
$messages['smtpfromerror'] = 'SMTP பிழை ($code): அனுப்புனரை "$from" ஆக வைப்பதில் தோல்வி';
$messages['smtptoerror'] = 'SMTP பிழை ($code): "$to" பெறுனரை சேர்பதில் தோல்வி';
$messages['smtprecipientserror'] = 'SMTP பிழை : பெறுனர் பட்டியலை பிரிக்க முடியவில்லை';
$messages['smtperror'] = 'SMTP பிழை: $msg';
$messages['emailformaterror'] = 'தவறான மின்னஞ்சல் முகவரி: $email';
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
