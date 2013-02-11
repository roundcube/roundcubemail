<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/labels.inc                                        |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/labels/
*/

$labels = array();

// login page
$labels['welcome']   = '$product में स्वागत है';
$labels['username']  = 'यूसरनाम';
$labels['password']  = 'पासवर्ड';
$labels['server']    = 'सर्वर';
$labels['login']     = 'लॉगिन';

// taskbar
$labels['logout']   = 'लॉगआऊट';
$labels['mail']     = 'ई-मेल';
$labels['settings'] = 'निजी सैटिंग';
$labels['addressbook'] = 'पता खाता';

// mailbox names
$labels['inbox']  = 'इनबॉक्स';
$labels['drafts'] = 'ड्राफ़ट';
$labels['sent']   = 'भेजा गया';
$labels['trash']  = 'रद्दी';
$labels['junk']   = 'स्पैम';

// message listing
$labels['subject'] = 'विशय';
$labels['from']    = 'भेजने वाला';
$labels['sender']  = 'Sender';
$labels['to']      = 'पाने वाला';
$labels['cc']      = 'कार्बन';
$labels['bcc']     = 'अंधा कार्बन';
$labels['replyto'] = 'जवाब यहाँ';
$labels['followupto'] = 'को- फॉलोअप';
$labels['date']    = 'तारीख';
$labels['size']    = 'माप';
$labels['priority'] = 'मेल की महत्वपूर्णता';
$labels['organization'] = 'संस्था';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'फ़ोल्डर';
$labels['messagesfromto'] = '$from से ले कर $to, $count मेल में से';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = '$nr नम्बर मेल, $count में से';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'कार्बन';
$labels['move']     = 'स्थानांतरित करें';
$labels['moveto']   = 'खीस्काऐं...';
$labels['download'] = 'डाऊनलोड';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'फ़ाईल नाम';
$labels['filesize'] = 'फ़ाईल माप';

$labels['addtoaddressbook'] = 'पता खाते में जोडें';

// weekdays short
$labels['sun'] = 'रवि';
$labels['mon'] = 'सोम';
$labels['tue'] = 'मंग';
$labels['wed'] = 'बुध';
$labels['thu'] = 'गुरु';
$labels['fri'] = 'शुक्र';
$labels['sat'] = 'शनि';

// weekdays long
$labels['sunday']    = 'रविवार';
$labels['monday']    = 'सोमवार';
$labels['tuesday']   = 'मंगलवार';
$labels['wednesday'] = 'बुधवार';
$labels['thursday']  = 'गुरुवार';
$labels['friday']    = 'शुक्रवार';
$labels['saturday']  = 'शनिवार';

// months short
$labels['jan']	= 'जनवरी';
$labels['feb']	= 'फ़रवरी';
$labels['mar']	= 'मार्च';
$labels['apr']	= 'अप्रेल';
$labels['may']	= 'मई';
$labels['jun']	= 'जून';
$labels['jul'] 	= 'जुलाई';
$labels['aug']	= 'Aug';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Oct';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dec';

// months long
$labels['longjan']	= 'जनवरी';
$labels['longfeb']	= 'फ़रवरी';
$labels['longmar']	= 'मार्च';
$labels['longapr']	= 'अप्रैल';
$labels['longmay']	= 'मई';
$labels['longjun']	= 'जून';
$labels['longjul']	= 'जुलाई';
$labels['longaug']	= 'अगस्त';
$labels['longsep']	= 'सितंबर';
$labels['longoct']	= 'अक्टूबर';
$labels['longnov']	= 'नवम्बर';
$labels['longdec']	= 'दिसंबर';

$labels['today'] = 'आज';

// toolbar buttons
$labels['refresh']          = 'ताज़ा करें';
$labels['checkmail']        = 'नये मेल लाऐं';
$labels['compose']          = 'मेल लिखें';
$labels['writenewmessage']  = 'मेल लिखें';
$labels['reply']            = 'जवाब';
$labels['replytomessage']   = 'मेल का जवाब दें';
$labels['replytoallmessage'] = 'सभी को जवाब लिखें';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'अग्रेषित करें';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'आगे भेजें';
$labels['deletemessage']    = 'रद्द करें';
$labels['movemessagetotrash'] = 'Move message to trash';
$labels['printmessage']     = 'मेल छापें';
$labels['previousmessage']  = 'पीछे के मेल दिखाऐं';
$labels['firstmessage']     = 'पहला मेल दिखाऐं';
$labels['nextmessage']      = 'अगला मेल दिखाऐं';
$labels['lastmessage']      = 'आखीरी मेल दिखाऐं';
$labels['backtolist']       = 'मेल सूची देखें';
$labels['viewsource']       = 'सोर्स दिखाऐं';
$labels['mark']             = 'चिह्नित';
$labels['markmessages']     = 'Mark messages';
$labels['markread']         = 'As read';
$labels['markunread']       = 'As unread';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'चुनें';
$labels['all'] = 'सभी';
$labels['none'] = 'कोई नहीं';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'अनदेखी';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'Unanswered';
$labels['deleted'] = 'Deleted';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Invert';
$labels['filter'] = 'Filter';
$labels['list'] = 'List';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'Expand All';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = 'Collapse All';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'From/To';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'Attachment';
$labels['nonesort'] = 'कोई नहीं';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'छोटा करें';
$labels['empty'] = 'मेल रद्दी में डालें';

$labels['quota'] = 'डिस्क उपयोग';
$labels['unknown']  = 'अज्ञात';
$labels['unlimited']  = 'असीम';

$labels['quicksearch']  = 'तेज़ खोज';
$labels['resetsearch']  = 'खोज खाली करें';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'मेल भेजें';
$labels['savemessage']    = 'ड्राफ़ट सहेजें';
$labels['addattachment']  = 'फ़ाईल जोडें';
$labels['charset']        = 'कैरेक्टर सैट';
$labels['editortype']     = 'सम्पादक प्राकार';
$labels['returnreceipt']  = 'मेल रसीद';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'स्पैलिंग जाँचें';
$labels['resumeediting'] = 'सम्पादन फिर शुरू करें';
$labels['revertto']      = 'पहले की स्थिती पर जाऐं';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'जुडे फाईल';
$labels['upload'] = 'अपलोड';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'बंद';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'कम';
$labels['lowest']  = 'सबसे कम';
$labels['normal']  = 'साधारण';
$labels['high']    = 'उच्च';
$labels['highest'] = 'उच्चतम';

$labels['nosubject']  = '(कोई विशय नहीं)';
$labels['showimages'] = 'चित्र दिखाऐं';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'ऐच-टी-ऐम-एल (HTML)';
$labels['plaintoggle'] = 'सादा पाठ';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'don\'t save';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Add Cc';
$labels['addbcc'] = 'Add Bcc';
$labels['addreplyto'] = 'Add Reply-To';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$labels['receiptread'] = 'Return Receipt (read)';
$labels['yourmessage'] = 'This is a Return Receipt for your message';
$labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$labels['name']         = 'दिखने वाला नाम';
$labels['firstname']    = 'पहला नाम';
$labels['surname']      = 'उपनाम';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'मेल';
$labels['phone']        = 'Phone';
$labels['address']      = 'Address';
$labels['street']       = 'Street';
$labels['locality']     = 'City';
$labels['zipcode']      = 'ZIP Code';
$labels['region']       = 'State/Province';
$labels['country']      = 'Country';
$labels['birthday']     = 'Birthday';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'Website';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'male';
$labels['female'] = 'female';
$labels['manager'] = 'Manager';
$labels['assistant'] = 'Assistant';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'All fields';
$labels['search'] = 'Search';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'Other';

$labels['typehome']   = 'Home';
$labels['typework']   = 'Work';
$labels['typeother']  = 'Other';
$labels['typemobile']  = 'Mobile';
$labels['typemain']  = 'Main';
$labels['typehomefax']  = 'Home Fax';
$labels['typeworkfax']  = 'Work Fax';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'Pager';
$labels['typevideo']  = 'Video';
$labels['typeassistant']  = 'Assistant';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profile';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'नया पता जोडें';
$labels['editcontact'] = 'पता सम्पाद';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'सम्पाद';
$labels['cancel'] = 'रद्द';
$labels['save']   = 'सहेज';
$labels['delete'] = 'मिटा';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'नया पता कार्ड बनाऐं';
$labels['deletecontact']  = 'चुने पते मिटाऐं';
$labels['composeto']      = 'को मेल भेजें';
$labels['contactsfromto'] = '$from से ले कर $to, $count पते में से';
$labels['print']          = 'छापें';
$labels['export']         = 'ऐक्स्पोर्ट';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'इससे पहले के बन्डल को दिखाऐं';
$labels['firstpage']      = 'पहला बन्डल दिखाऐं';
$labels['nextpage']       = 'अगला बन्डल दिखाऐं';
$labels['lastpage']       = 'आखिरी बन्डल दिखाऐं';

$labels['group'] = 'Group';
$labels['groups'] = 'Groups';
$labels['personaladrbook'] = 'Personal Addresses';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'Import';
$labels['importcontacts'] = 'Import contacts';
$labels['importfromfile'] = 'Import from file:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'Done';

// settings
$labels['settingsfor'] = 'इनके विन्यास:';
$labels['about'] = 'About';
$labels['preferences'] = 'पसंद';
$labels['userpreferences'] = 'यूसर पसंद';
$labels['editpreferences'] = 'यूसर पसंद सम्पादें';

$labels['identities'] = 'पहचान';
$labels['manageidentities'] = 'इस खाते के पहचान व्यवस्थित करें';
$labels['newidentity'] = 'नया पहचान';

$labels['newitem'] = 'नया चीज़';
$labels['edititem'] = 'चीज़ सम्पादें';

$labels['preferhtml'] = 'ऐच-टी-ऐम-ऐल(HTML) दिखाऐं';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'ऐच-टी-ऐम-ऐल (HTML) रूप मेल करें';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'सुनदर तारीखें';
$labels['setdefault']  = 'तयशुदा चुनें';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'भाषा';
$labels['timezone']  = 'समय क्षेत्र';
$labels['pagesize']  = 'हर पन्ने पर इतने';
$labels['signature'] = 'दस्तखत';
$labels['dstactive']  = 'रौशनी बचाव';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'मेल ऐच-टी-ऐम-एल (HTML) मे लिखें';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'ऐच-टी-ऐम-एल (HTML) दस्तखत';
$labels['previewpane'] = 'झलक पट्टी दिखाऐं';
$labels['skin'] = 'Interface skin';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'User Interface';
$labels['serversettings'] = 'Server Settings';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'ask me';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'ignore';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'always';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'ड्राफ़ट अपने आप सहेजते जाऐं';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'कभी नहाीं';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'Displaying Messages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Attachment names';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Advanced options';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'Section';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'New Message';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'Automatically add signature';
$labels['newmessageonly'] = 'new message only';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['replysignaturepos'] = 'When replying or forwarding place signature';
$labels['belowquote'] = 'below the quote';
$labels['abovequote'] = 'above the quote';
$labels['insertsignature'] = 'Insert signature';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = 'after $n seconds';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'Ignore words with numbers';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'Add to dictionary';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'फ़ोल्डर';
$labels['folders']  = 'फ़ोल्डर';
$labels['foldername']  = 'फ़ोल्डर नाम';
$labels['subscribed']  = 'सबस्क्राईब किया गया';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'बनाऐं';
$labels['createfolder']  = 'फ़ोलडर बनाऐं';
$labels['managefolders']  = 'फ़ोलडर को व्यवस्थित करें';
$labels['specialfolders'] = 'Special Folders';
$labels['properties'] = 'Properties';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'Information';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'Private Folder';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Public Folder';

$labels['sortby'] = 'ऐसे तय करें';
$labels['sortasc']  = 'पहले से आखीरी तय करें';
$labels['sortdesc'] = 'आखीरी से पहले तय करें';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'Unicode';
$labels['english'] = 'English';
$labels['westerneuropean'] = 'Western European';
$labels['easterneuropean'] = 'Eastern European';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'Baltic';
$labels['cyrillic'] = 'Cyrillic';
$labels['arabic'] = 'Arabic';
$labels['greek'] = 'Greek';
$labels['hebrew'] = 'Hebrew';
$labels['turkish'] = 'Turkish';
$labels['nordic'] = 'Nordic';
$labels['thai'] = 'Thai';
$labels['celtic'] = 'Celtic';
$labels['vietnamese'] = 'Vietnamese';
$labels['japanese'] = 'Japanese';
$labels['korean'] = 'Korean';
$labels['chinese'] = 'Chinese';

?>
