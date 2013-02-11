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
$labels['welcome']   = '$product मा स्वागत छ';
$labels['username']  = 'प्रयोगकर्ताको नाम';
$labels['password']  = 'प्रवेश शब्द';
$labels['server']    = 'सर्भर';
$labels['login']     = 'लगइन गर्नुहोस्';

// taskbar
$labels['logout']   = 'लगआउट गर्नुहोस्';
$labels['mail']     = 'ई-मेल';
$labels['settings'] = 'व्यक्तिगत रोजाईहरु';
$labels['addressbook'] = 'ठेगाना पुस्तिका';

// mailbox names
$labels['inbox']  = 'प्राप्ति मञ्जूषा';
$labels['drafts'] = 'ड्राफ्टहरू';
$labels['sent']   = 'पठईएका मेलहरु';
$labels['trash']  = 'रद्दी टोकरी';
$labels['junk']   = 'स्पाम';

// message listing
$labels['subject'] = 'विषय';
$labels['from']    = 'प्रेषक';
$labels['sender']  = 'Sender';
$labels['to']      = 'प्रापक';
$labels['cc']      = 'बोधार्थ';
$labels['bcc']     = 'गुप्त बोधार्थ';
$labels['replyto'] = 'लाई-जवाफ';
$labels['followupto'] = 'यसलाई अनुसरण गर्नुहोस्';
$labels['date']    = 'तारिख';
$labels['size']    = 'नाप';
$labels['priority'] = 'प्राथमिकता';
$labels['organization'] = 'संस्था';
$labels['readstatus'] = 'पढेको अवस्था';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'फोल्डरहरु';
$labels['messagesfromto'] = 'मेलहरु $from बाट $to सम्म, $count मध्यबाट';
$labels['threadsfromto'] = 'थ्रेडहरु $from बाट $to सम्म, $count मध्यबाट';
$labels['messagenrof'] = '$nr नम्बर मेल, $count मध्यबाट';
$labels['fromtoshort'] = '$from - $to, $count मध्यबाट';

$labels['copy']     = 'प्रतिलिपि गर्नुहोस्';
$labels['move']     = 'सार्नुहोस';
$labels['moveto']   = 'यहाँ सार्नुहोस';
$labels['download'] = 'डाऊनलोड';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'फाईलको नाम';
$labels['filesize'] = 'फाईलको नाप';

$labels['addtoaddressbook'] = 'ठेगाना पुस्तिकामा भर्नुहोस';

// weekdays short
$labels['sun'] = 'आइत';
$labels['mon'] = 'सोम';
$labels['tue'] = 'मंगल';
$labels['wed'] = 'बुध';
$labels['thu'] = 'विहि';
$labels['fri'] = 'शुक';
$labels['sat'] = 'शनि';

// weekdays long
$labels['sunday']    = 'आइतवार';
$labels['monday']    = 'सोमवार';
$labels['tuesday']   = 'मंगलवार';
$labels['wednesday'] = 'बुधवार';
$labels['thursday']  = 'विहिवार';
$labels['friday']    = 'शुक्रवार';
$labels['saturday']  = 'शनिवार';

// months short
$labels['jan']	= 'जनवरी';
$labels['feb']	= 'फेब्रुवरी';
$labels['mar']	= 'मार्च';
$labels['apr']	= 'अप्रिल';
$labels['may']	= 'मई';
$labels['jun']	= 'जुन';
$labels['jul'] 	= 'जुलाई';
$labels['aug']	= 'अगस्ट';
$labels['sep']	= 'सेप्टेम्बर';
$labels['oct']	= 'अक्टोबर';
$labels['nov']	= 'नोभेम्बर';
$labels['dec']	= 'डिसेम्बर';

// months long
$labels['longjan']	= 'जनवरी';
$labels['longfeb']	= 'फेब्रुवरी';
$labels['longmar']	= 'मार्च';
$labels['longapr']	= 'अप्रिल';
$labels['longmay']	= 'मई';
$labels['longjun']	= 'जुन';
$labels['longjul']	= 'जुलाई';
$labels['longaug']	= 'अगस्ट';
$labels['longsep']	= 'सेप्टेम्बर';
$labels['longoct']	= 'अक्टोबर';
$labels['longnov']	= 'नोभेम्बर';
$labels['longdec']	= 'डिसेम्बर';

$labels['today'] = 'आज';

// toolbar buttons
$labels['refresh']          = 'ताजा पार्नुहोस्';
$labels['checkmail']        = 'नया मेल खोज्नुहोस';
$labels['compose']          = 'रचना गर्नुहोस्';
$labels['writenewmessage']  = 'नया मेल लेख्नुहोस';
$labels['reply']            = 'जवाफ दिनुहोस्';
$labels['replytomessage']   = 'प्रेषकलाई जवाफ दिनुहोस';
$labels['replytoallmessage'] = 'प्रेषक लगायत सबैलाई जवाफ दिनुहोस';
$labels['replyall']         = 'सबैलाई जवाफ दिनुहोस';
$labels['replylist']        = 'जवाफ तालिका';
$labels['forward']          = 'फरवार्ड गर्नुहोस्';
$labels['forwardinline']    = 'आन्तरिक पंक्तिमा फरवार्ड गर्नुहोस';
$labels['forwardattachment'] = 'एट्याचमेन्ट जसरी फरवार्ड गर्नुहोस';
$labels['forwardmessage']   = 'मेल अगाडि पठाउनुहोस';
$labels['deletemessage']    = 'रद्दी टोकरीमा हाल्नुहोस';
$labels['movemessagetotrash'] = 'सन्देशलाई रद्दी टोकरीमा सार्नुहोस';
$labels['printmessage']     = 'यो सन्देशलाई मुद्रण गर्नुहोस्';
$labels['previousmessage']  = 'पछाडिको सन्देश देखाउनुहोस';
$labels['firstmessage']     = 'पहलो सन्देश देखाउनुहोस';
$labels['nextmessage']      = 'अगाडिको सन्देश देखाउनुहोस';
$labels['lastmessage']      = 'अन्तिम सन्देश देखाउनुहोस';
$labels['backtolist']       = 'सन्देश सूची देखाउनुहोस';
$labels['viewsource']       = 'श्रोत देखाउनुहोस';
$labels['mark']             = 'चिन्ह लगाउनुहोस्';
$labels['markmessages']     = 'सन्देशमा चिन्ह लगाउनुहोस्';
$labels['markread']         = 'As read';
$labels['markunread']       = 'As unread';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'अझ धेरै';
$labels['back']             = 'पछाडि';
$labels['options']          = 'विकल्पहरू';

$labels['select'] = 'चयन गर्नुहोस्';
$labels['all'] = 'सबै';
$labels['none'] = 'हैन';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'नपढिएका |';
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
$labels['nonesort'] = 'हैन';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'छोटो गर्नुहोस';
$labels['empty'] = 'खाली गर्ने';

$labels['quota'] = 'डिस्कको उपभोग';
$labels['unknown']  = 'अज्ञात';
$labels['unlimited']  = 'अनगिन्ति';

$labels['quicksearch']  = 'स्फुर्ति खोज';
$labels['resetsearch']  = 'पन: खोज';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'तुरुन्तै मेल पठाउनुहोस';
$labels['savemessage']    = 'ड्राफ्ट जोगाउनुहोस';
$labels['addattachment']  = 'फाइल टाँस्नुहोस';
$labels['charset']        = 'क्यारेक्टर सेट';
$labels['editortype']     = 'सम्पादकको प्रकार';
$labels['returnreceipt']  = 'फिर्ति रसीद';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'हिज्जे जाँच';
$labels['resumeediting'] = 'पुन: सम्पादन गर्नुहोस';
$labels['revertto']      = 'पहिलेको स्थितीमा जानुहोस';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'टाँसिएका फाईलहरु';
$labels['upload'] = 'अपलोड';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'बन्द';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'कम';
$labels['lowest']  = 'सबै भन्दा कम';
$labels['normal']  = 'साधारण';
$labels['high']    = 'उच्च';
$labels['highest'] = 'उच्चतम';

$labels['nosubject']  = '(बिना विषयको)';
$labels['showimages'] = 'चित्र देखाउनुहोस';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'ऐच-टी-ऐम-एल (HTML)';
$labels['plaintoggle'] = 'सादा अक्षर';
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
$labels['name']         = 'देखाउने नाम';
$labels['firstname']    = 'पहिलो नाम';
$labels['surname']      = 'थर';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'ई-मेल';
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
$labels['addcontact'] = 'नया सम्पर्क जोड्नुहोस';
$labels['editcontact'] = 'सम्पर्क सच्याउनुहोस';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'सच्याउनुहोस';
$labels['cancel'] = 'खारेज गर्नुहोस';
$labels['save']   = 'जोगाउनुहोस';
$labels['delete'] = 'मेटाउनुहोस';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'नया सम्पर्क कार्ड बनाउनुहोस';
$labels['deletecontact']  = 'छानिएका सम्पर्कहरु मेटाउनुहोस';
$labels['composeto']      = 'मेल लेखी पठाउनुहोस';
$labels['contactsfromto'] = 'सम्पर्कहरु $from बाट $to सम्म, $count मध्यबाट';
$labels['print']          = 'छाप्नुहोस';
$labels['export']         = 'ऐक्स्पोर्ट गर्नुहोस';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'पहिलेको सेट देखाउनुहोस';
$labels['firstpage']      = 'पहिलो सेट देखाउनुहोस';
$labels['nextpage']       = 'अर्को सेट देखाउनुहोस';
$labels['lastpage']       = 'अन्तिम सेट देखाउनुहोस';

$labels['group'] = 'Group';
$labels['groups'] = 'समूहहरु';
$labels['personaladrbook'] = 'व्यक्तिगत ठेगानाहरु';

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
$labels['settingsfor'] = 'तय';
$labels['about'] = 'About';
$labels['preferences'] = 'रोजाइहरु';
$labels['userpreferences'] = 'यूसरका रोजाइहरु';
$labels['editpreferences'] = 'यूसरका रोजाइहर सम्पादन गर्नुहोस';

$labels['identities'] = 'पहिचानहरु';
$labels['manageidentities'] = 'यो खाताको पहिचानहरको व्यवस्था गर्नुहोस';
$labels['newidentity'] = 'नया पहिचान';

$labels['newitem'] = 'नया प्रकार';
$labels['edititem'] = 'प्रकार सम्पादन गर्नुहोस';

$labels['preferhtml'] = 'ऐच-टी-ऐम-ऐल(HTML) देखाउनुहोस';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'ऐच-टी-ऐम-ऐल (HTML) रूपका मेल';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'सुन्दर तारिख';
$labels['setdefault']  = 'तय गर्नुहोस';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'भाषा';
$labels['timezone']  = 'समय क्षेत्र';
$labels['pagesize']  = 'हरेक पन्नामा यति';
$labels['signature'] = 'हस्ताक्षर';
$labels['dstactive']  = 'प्रकाश बचाव';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'ऐच-टी-ऐम-एल (HTML) मेल लखन गर्नुहोस';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'ऐच-टी-ऐम-एल (HTML) हस्ताक्षर';
$labels['previewpane'] = 'पूर्व दृष्य देखाउनुहोस';
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
$labels['autosavedraft']  = 'स्वत ड्राफ्ट जोगाउनुहोस';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'काहिलै नहुने';
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

$labels['folder']  = 'फोल्डर';
$labels['folders']  = 'फोल्डरहरु';
$labels['foldername']  = 'फोल्डरको नाम';
$labels['subscribed']  = 'सबस्क्राईब गरियो';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'बनाउनुहोस';
$labels['createfolder']  = 'नया फोलडर बनाउनुहोस';
$labels['managefolders']  = 'फोलडर व्यवस्थित गर्नुहोस';
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

$labels['sortby'] = 'यसरी तय गर्नुहोस';
$labels['sortasc']  = 'पहिलो देखि अन्तिम सम्म तय गर्नुहोस';
$labels['sortdesc'] = 'अन्तिम देखि पहिलो सम्म तय गर्नुहोस';
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
