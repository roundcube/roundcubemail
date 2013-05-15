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
$labels['welcome']   = 'স্বাগতম';
$labels['username']  = 'গ্রাহক নাম (username)';
$labels['password']  = 'গোপোন শব্দ (password)';
$labels['server']    = 'সারভার';
$labels['login']     = 'প্রবেশ';

// taskbar
$labels['logout']   = 'প্রস্থান';
$labels['mail']     = 'ই-মেইল';
$labels['settings'] = 'ব্যাক্তিগত খুটিনাটি';
$labels['addressbook'] = 'ঠিকানার বই';

// mailbox names
$labels['inbox']  = 'প্রধান বাক্স';
$labels['drafts'] = 'খসড়া';
$labels['sent']   = 'প্রেরিত(পাঠানো মেইল)';
$labels['trash']  = 'ডাস্টবিন';
$labels['junk']   = 'আজেবাজে মেইল';

// message listing
$labels['subject'] = 'বিষয়';
$labels['from']    = 'প্রেরক';
$labels['sender']  = 'Sender';
$labels['to']      = 'প্রাপক';
$labels['cc']      = 'অনুলিপির ঠিকানা';
$labels['bcc']     = 'নাজানিয়ে অনুলিপির ঠিকানা';
$labels['replyto'] = 'উত্তর পাঠানোর ঠিকানা';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'তারিখ';
$labels['size']    = 'সাইজ';
$labels['priority'] = 'গুরুত্ব';
$labels['organization'] = 'কোম্পানি';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'ফোল্ডার/মেইলবক্স';
$labels['messagesfromto'] = '$count মেইলের মধ্যে, $from হতে $to দেখানো হচ্ছে';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = '$nr #মেইল/চিঠি , $count এর মধ্যে';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'অনুলিপির ঠিকানা';
$labels['move']     = 'Move';
$labels['moveto']   = 'সরিয়ে ফেলুন..';
$labels['download'] = 'ডাউনলোড';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'ফাইলের নাম';
$labels['filesize'] = 'ফাইলের সাইজ';

$labels['addtoaddressbook'] = 'ঠিকানার বইতে ঢুকান';

// weekdays short
$labels['sun'] = 'রবি';
$labels['mon'] = 'সোম';
$labels['tue'] = 'মঙ্গল';
$labels['wed'] = 'বুধ';
$labels['thu'] = 'বৃহ:';
$labels['fri'] = 'শুক্র';
$labels['sat'] = 'শনি';

// weekdays long
$labels['sunday']    = 'রবিবার';
$labels['monday']    = 'সোমবার';
$labels['tuesday']   = 'মঙ্গলবার';
$labels['wednesday'] = 'বুধবার';
$labels['thursday']  = 'বৃহস্পতিবার';
$labels['friday']    = 'শুক্রবার';
$labels['saturday']  = 'শনিবার';

// months short
$labels['jan']	= 'জানুয়ারি';
$labels['feb']	= 'ফেব্রুয়ারি';
$labels['mar']	= 'মার্চ';
$labels['apr']	= 'এপ্রিল';
$labels['may']	= 'মে';
$labels['jun']	= 'জুন';
$labels['jul'] 	= 'জুলাই';
$labels['aug']	= 'আগস্ট';
$labels['sep']	= 'সেপ্তেমবার';
$labels['oct']	= 'অষ্টবার';
$labels['nov']	= 'নবেমবার';
$labels['dec']	= 'দিশেমবার';

// months long
$labels['longjan']	= 'জানুয়ারি';
$labels['longfeb']	= 'ফেব্রুয়ারি';
$labels['longmar']	= 'মার্চ';
$labels['longapr']	= 'এপ্রিল';
$labels['longmay']	= 'মে';
$labels['longjun']	= 'জুন';
$labels['longjul']	= 'জুলাই';
$labels['longaug']	= 'আগস্ট';
$labels['longsep']	= 'সেপ্তেমবার (September)';
$labels['longoct']	= 'অষ্টবার (October)';
$labels['longnov']	= 'নবেমবার (November)';
$labels['longdec']	= 'দিশেমবার (December)';

$labels['today'] = 'আজকে';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'দেখুন নতুন কোনো মেইল/চিঠি এলো কিনা';
$labels['compose']          = 'নতুন মেইল/চিঠি লিখুন';
$labels['writenewmessage']  = 'নতুন মেইল/চিঠি লিখুন';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'শুধু প্রেরকের কাছে উত্তর পাঠান';
$labels['replytoallmessage'] = 'প্রেরক ও এই চিঠির অন্যান্য প্রাপকদের কাছে উত্তর পাঠান';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'মেইল/চিঠি এগিয়ে দিন';
$labels['deletemessage']    = 'মেইল/চিঠি ছিড়ে ফেলুন';
$labels['movemessagetotrash'] = 'মেইল/চিঠি ডাস্টবিনে ফেলুন';
$labels['printmessage']     = 'মেইল/চিঠি প্রিন্ট করুন';
$labels['previousmessage']  = 'আগের মেইল/চিঠিটি দেখুন';
$labels['firstmessage']     = 'প্রথম মেইল/চিঠিটি দেখুন';
$labels['nextmessage']      = 'পরের মেইল/চিঠিটি দেখুন';
$labels['lastmessage']      = 'শেষ মেইল/চিঠিটি দেখুন';
$labels['backtolist']       = 'মেইল/চিঠির তালিকায় ফিরে যান';
$labels['viewsource']       = 'মূল উৎসদেখুন';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'মেইল/চিঠি (গুলো)';
$labels['markread']         = 'পড়া শেষ (read)';
$labels['markunread']       = 'নাদেখা (unread)';
$labels['markflagged']      = 'দাগানো (flagged)';
$labels['markunflagged']    = 'দাগহীন (unflagged)';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'বেছেনিন';
$labels['all'] = 'সবগুলো';
$labels['none'] = 'কোনোটাই না';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'নাদেখা (unread)';
$labels['flagged'] = 'দাগানো (flagged)';
$labels['unanswered'] = 'উত্তর না দেওয়া গুলো';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'Deleted';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Invert';
$labels['filter'] = 'ছাকনি';
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
$labels['nonesort'] = 'কোনোটাই না';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'টাইটকরুন';
$labels['empty'] = 'খালিকরুন';

$labels['quota'] = 'ডিস্ক এ খালি যায়গা';
$labels['unknown']  = 'অজানা';
$labels['unlimited']  = 'সীমাহীন';

$labels['quicksearch']  = 'ঝটকরে খোজা';
$labels['resetsearch']  = 'নতুনকরে খুজুন';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'নতুন উইন্ডো তে দেখুন';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'এখুনি মেইল / চিঠিটি পাঠান';
$labels['savemessage']    = 'খসড়া হিসাবে জমা করে রাখুন';
$labels['addattachment']  = 'একটা ফাইল জুড়ে দিন চিঠির সাথে';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'Editor type';
$labels['returnreceipt']  = 'ফিরতি রসিদ';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Check spelling';
$labels['resumeediting'] = 'Resume editing';
$labels['revertto']      = 'Revert to';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'জুড়ে দেওয়া ফাইল (Attachments)';
$labels['upload'] = 'উঠায় দেন';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'বন্ধ করুন';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Low';
$labels['lowest']  = 'Lowest';
$labels['normal']  = 'Normal';
$labels['high']    = 'High';
$labels['highest'] = 'Highest';

$labels['nosubject']  = '(বিষয়হীন)';
$labels['showimages'] = 'ছবি দেখান';
$labels['alwaysshow'] = 'সবসময় $sender এর কাছে থেকে পাওয়া ছবিগুলো দেখানো হোক';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'খালি লেখা দেখুন';
$labels['savesentmessagein'] = 'পাঠানো চিঠি/মেইল জমা করুন >>';
$labels['dontsave'] = 'জমিয়ে রাখা লাগবে না';
$labels['maxuploadsize'] = 'ফাইল উঠানোর সর্বোচ্চ সাইজ $size';

$labels['addcc'] = 'অনুলিপির ঠিকানা ঢুকান';
$labels['addbcc'] = 'নাজানিয়ে অনুলিপির ঠিকানা ঢুকান';
$labels['addreplyto'] = 'উত্তর পাঠানোর ঠিকানা ঢুকান';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'প্রাপক আপনার কাছে মেইল/চিঠিটা যে পেয়েছেন তার ফিরতি রসিদ চেয়েছেন। আপনি কি ফিরতি রসিদ টা পাঠাবেন?';
$labels['receiptread'] = 'মেইল/চিঠি যে পেয়েছেন তার ফিরতি রসিদ';
$labels['yourmessage'] = 'নিচে রয়েছে আপনার চিঠিটা যে প্রাপক পেয়েছেন তার রসিদ';
$labels['receiptnote'] = 'এই রসিদ খালি এতটুকু প্রমান করে যে প্রাপক চিঠিটি পেয়েছেন। কিন্তু তিনি ঠিকমতো পড়তে পেরেছেন কিনা অথবা তিনি পড়ে বুঝতে পেরেছেন কিনা তার কোনো প্রমান নেই।';

// address boook
$labels['name']         = 'নাম';
$labels['firstname']    = 'নামের প্রথমাংশ';
$labels['surname']      = 'নামের শেষাংশ';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'ই-মেইল/চিঠি';
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
$labels['addcontact'] = 'নতুন ঠিকানা ঢুকান';
$labels['editcontact'] = 'একটা ঠিকানা বদলান';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'বদলান';
$labels['cancel'] = 'বাতিল';
$labels['save']   = 'জমা দিন';
$labels['delete'] = 'ফেলে দিন';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'একটা নতুন ঠিকানার কার্ড বানান';
$labels['deletecontact']  = 'বেছে নেওয়া ঠিকানা গুলো ফেলে দিন';
$labels['composeto']      = 'চিঠি পাঠাবেন কার কাছে?';
$labels['contactsfromto'] = '$count ঠিকানার মধ্যে $from থেকে $to টা';
$labels['print']          = 'প্রিন্ট করুন';
$labels['export']         = 'রপ্তানি করুন';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'রপ্তানি করুন vCard আকারে';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'আগের গুলো দেখান';
$labels['firstpage']      = 'প্রথম গুলো দেখান';
$labels['nextpage']       = 'পরের গুলো দেখান';
$labels['lastpage']       = 'শেষের গুলো দেখান';

$labels['group'] = 'Group';
$labels['groups'] = 'গ্রুপ';
$labels['personaladrbook'] = 'নিজের ঠিকানা';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'আমদানি করুন';
$labels['importcontacts'] = 'ঠিকানা আমদানি করুন';
$labels['importfromfile'] = 'ফাইলের থেকে ঠিকানা আমদানি করুন';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'পুরো ঠিকানার বইটি খালি করে নতুন ঠিকানা গুলো ভরুন';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'শেষ';

// settings
$labels['settingsfor'] = 'খুটিনাটি';
$labels['about'] = 'About';
$labels['preferences'] = 'পছন্দ';
$labels['userpreferences'] = 'গ্রাহক পছন্দ';
$labels['editpreferences'] = 'গ্রাহক পছন্দ বদলান';

$labels['identities'] = 'পরিচিতি';
$labels['manageidentities'] = 'এই অ্যাকউন্টের "পরচিতি" বদলান';
$labels['newidentity'] = 'নতুন পরিচিতি';

$labels['newitem'] = 'নতুন জিনিষ';
$labels['edititem'] = 'জিনিষ বদলান';

$labels['preferhtml'] = 'HTML এ দেখি';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML মেইল/চিঠি';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'তারিখ ভালো করে দেখা যাবে';
$labels['setdefault']  = 'এইটাই আসল';
$labels['autodetect']  = 'কম্পুটার নিজেনিজি বেছে নেবে';
$labels['language']  = 'ভাষা';
$labels['timezone']  = 'সময়ভিত্তিক এলাকা';
$labels['pagesize']  = 'প্রতি পৃষ্ঠায় লাইন সংখ্যা';
$labels['signature'] = 'স্বাক্ষর';
$labels['dstactive']  = 'Daylight saving time';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'HTML এ মেইল/চিঠি লিখুন';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML এ স্বাক্ষর';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'ছোটোকরে দেখার জায়গা';
$labels['skin'] = 'মেইলবক্স এর পুরোটার চেহারা';
$labels['logoutclear'] = 'বেরহবার সময় ডাস্টবিন অটোমেটিক খালি হবে';
$labels['logoutcompact'] = 'বেরহবার সময় প্রধান বাক্স অটোমেটিক টাইট হবে';
$labels['uisettings'] = 'কম্পুটারের চেহারা';
$labels['serversettings'] = 'সারভার এর খুটিনাটি';
$labels['mailboxview'] = 'মেইলবক্স দেখার কায়দা';
$labels['mdnrequests'] = 'প্রেরক নোটিশ পাঠালে কি করা হবে?';
$labels['askuser'] = 'আমি গ্রাহক, আমাকে জিজ্ঞাসা করুন';
$labels['autosend'] = 'কম্পুটার নিজেনিজি ফিরতি নোটিশ পাঠায় দেবে';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'পাত্তা দেওয়া হবেনা';
$labels['readwhendeleted'] = 'ফেলে দেবার সময় মেইল/চিঠি পড়া হয়ে গেছে হিসাবে ধরে নেওয়া হবে';
$labels['flagfordeletion'] = 'একেবারেই ফেলে না দিয়ে মেইল/চিঠি গুলোকে দাগিয়ে রাখুন ফেলে দেবার জন্যে';
$labels['skipdeleted'] = 'ফেলে দেওয়া মেইল/চিঠি দেখানোর দরকার নেই';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'মেইলে অন্য ওয়েবসাইট থেকে আসা ছবি থাকলে তা দেখা যাবে';
$labels['fromknownsenders'] = 'পরিচিত প্রেরক এর ক্ষেত্রে';
$labels['always'] = 'সবসময়';
$labels['showinlineimages'] = 'মেইলের সাথে জোড়া লাগানো ছবি, মেইলের নিচে দেখা যাবে';
$labels['autosavedraft']  = 'নিজেনিজি খসড়া জমা হয়ে যাবে';
$labels['everynminutes']  = 'প্রতি $n মিনিটে';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'কখোনোই না';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'মেইল/চিঠি দেখা যাচ্ছে';
$labels['messagescomposition'] = 'মেইল/চিঠি লেখা হচ্ছে';
$labels['mimeparamfolding'] = 'জুড়ে দেওয়া জিনিষের নাম';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'জটিল বিষয়';
$labels['focusonnewmessage'] = 'নতুন মেইল এলেই তা হাইলাইট হয়ে যাবে';
$labels['checkallfolders'] = 'সবগুলো মেইলবক্সে নতুন চিঠি খোজা হবে';
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

$labels['folder']  = 'ফোল্ডার/মেইলবক্স';
$labels['folders']  = 'ফোল্ডার/মেইলবক্স';
$labels['foldername']  = 'ফোল্ডার/মেইলবক্স এর নাম';
$labels['subscribed']  = 'গ্রহনকারি';
$labels['messagecount'] = 'মেইল/চিঠি';
$labels['create']  = 'প্রস্তুত করুন';
$labels['createfolder']  = 'নতুন ফোল্ডার / মেইলবক্স প্রস্তুত করুন';
$labels['managefolders']  = 'ফোল্ডার/মেইলবক্স গুলো কে গুছান';
$labels['specialfolders'] = 'বিশেষ ফোল্ডার/মেইলবক্স';
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

$labels['sortby'] = 'সাজান';
$labels['sortasc']  = 'কম থেকে বেশি হিসাবে সাজান';
$labels['sortdesc'] = 'বেশি থেকে কম হিসাবে সাজান';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'বাইট';
$labels['KB'] = 'কিলোবাইট';
$labels['MB'] = 'মেগাবাইট';
$labels['GB'] = 'গিগাবাইট';

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
