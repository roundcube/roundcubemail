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
$labels['welcome']   = '$product ته ښه راغلاست';
$labels['username']  = 'کارن نوم';
$labels['password']  = 'پټنوم';
$labels['server']    = 'برابرونکی';
$labels['login']     = 'ننوتون';

// taskbar
$labels['logout']   = 'وتون';
$labels['mail']     = 'برېښناليک';
$labels['settings'] = 'ځاني برابرونې';
$labels['addressbook'] = 'پتې کتاب';

// mailbox names
$labels['inbox']  = 'راغلي ليکونه';
$labels['drafts'] = 'بارليک';
$labels['sent']   = 'لېږل شوي ليکونه';
$labels['trash']  = 'کثافت دانۍ';
$labels['junk']   = 'جنک';

// message listing
$labels['subject'] = 'مضمون';
$labels['from']    = 'استوونکی';
$labels['sender']  = 'Sender';
$labels['to']      = 'ترلاسه کوونکی';
$labels['cc']      = 'لمېسل';
$labels['bcc']     = 'پټليکی';
$labels['replyto'] = 'ځوابول';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'نېټه';
$labels['size']    = 'کچه';
$labels['priority'] = 'لومړيتوب';
$labels['organization'] = 'ټولنه';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'پوښۍ';
$labels['messagesfromto'] = 'له ټاکلي استوزو څخه ټاکلي شمېرل شوو استوزو ته';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'له $nr ځخه استوزو $count استوزو ته';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'لمېسل';
$labels['move']     = 'Move';
$labels['moveto']   = 'ته خوځوي';
$labels['download'] = 'ښکته کول';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'فايل نوم';
$labels['filesize'] = 'فايل کچه';

$labels['addtoaddressbook'] = 'د پتو کتاب ته يې ورزيات کړئ';

// weekdays short
$labels['sun'] = 'يوه نۍ';
$labels['mon'] = 'دوه نۍ';
$labels['tue'] = 'درې نۍ';
$labels['wed'] = 'څلورنۍ';
$labels['thu'] = 'پنځنۍ';
$labels['fri'] = 'جمعه';
$labels['sat'] = 'نۍ';

// weekdays long
$labels['sunday']    = 'يوه نۍ';
$labels['monday']    = 'دوه نۍ';
$labels['tuesday']   = 'درې نۍ';
$labels['wednesday'] = 'منځنۍ';
$labels['thursday']  = 'پنځنۍ';
$labels['friday']    = 'جمعه';
$labels['saturday']  = 'نۍ';

// months short
$labels['jan']	= 'حنوري';
$labels['feb']	= 'فبروري';
$labels['mar']	= 'مارچ';
$labels['apr']	= 'اپريل';
$labels['may']	= 'مئ';
$labels['jun']	= 'جون';
$labels['jul'] 	= 'جولاي';
$labels['aug']	= 'اګست';
$labels['sep']	= 'سپتمپر';
$labels['oct']	= 'اکتوبر';
$labels['nov']	= 'نومبر';
$labels['dec']	= 'ډسمبر';

// months long
$labels['longjan']	= 'جنوري';
$labels['longfeb']	= 'فبروري';
$labels['longmar']	= 'مارچ';
$labels['longapr']	= 'اپريل';
$labels['longmay']	= 'مئ';
$labels['longjun']	= 'جون';
$labels['longjul']	= 'جولای';
$labels['longaug']	= 'اګست';
$labels['longsep']	= 'سپتمبر';
$labels['longoct']	= 'اکتوبر';
$labels['longnov']	= 'نومبر';
$labels['longdec']	= 'ډسمبر';

$labels['today'] = 'نن';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'نوي استوزي وګوره';
$labels['compose']          = 'استوزه وليکه';
$labels['writenewmessage']  = 'نوي استوزي جوړول';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'استوونکي ته ځواب ورکړئ';
$labels['replytoallmessage'] = 'استوونکي او ټولو ترلاسه کوونکو ته ځواب لېږل';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'استوزه بل ته استول';
$labels['deletemessage']    = 'استوزه ړنګول';
$labels['movemessagetotrash'] = 'استوزه کثافت داني ته واستوه';
$labels['printmessage']     = 'دا استوزه چاپ کړه';
$labels['previousmessage']  = 'پخوانۍ استوزه وښيه';
$labels['firstmessage']     = 'لومړی استوزه وښيه';
$labels['nextmessage']      = 'ورپسې استوزه وښيه';
$labels['lastmessage']      = 'پای /آخري استوزه وښيه';
$labels['backtolist']       = 'بيرته د استوزو لست ته';
$labels['viewsource']       = 'سرچينه وښيه';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'استوزي په نښه کړئ';
$labels['markread']         = 'لوستل شوي په توګه';
$labels['markunread']       = 'د نالوستل شوي په توګه';
$labels['markflagged']      = 'د بي کاره په توګه';
$labels['markunflagged']    = 'د کارونې په توګه';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'ټاکل';
$labels['all'] = 'ټول';
$labels['none'] = 'هېڅ';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'نالوستي';
$labels['flagged'] = 'بې کاره';
$labels['unanswered'] = 'ناځواب شوي';
$labels['deleted'] = 'Deleted';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Invert';
$labels['filter'] = 'چڼول';
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
$labels['nonesort'] = 'هېڅ';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'کېښکلی';
$labels['empty'] = 'تش';

$labels['quota'] = 'ټيکلي لارښود';
$labels['unknown']  = 'نامعلوم';
$labels['unlimited']  = 'بي کچه';

$labels['quicksearch']  = 'ژر پلټنه';
$labels['resetsearch']  = 'د پلټنې بياسمون';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'په نوي کړکۍ کې پرانيزه';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'اوس يې واستوه';
$labels['savemessage']    = 'دا ګارليک خوندي کړه';
$labels['addattachment']  = 'يو فايل ونښلوه';
$labels['charset']        = 'چيرست';
$labels['editortype']     = 'د ټايب سمونګر';
$labels['returnreceipt']  = 'د رسليک راګرځېدل';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'ليکدود کتنه';
$labels['resumeediting'] = 'کارمخينې سمونه';
$labels['revertto']      = 'ته بېرته اړول';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'نښلني';
$labels['upload'] = 'پورته کول';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'بندول';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'ټيټ';
$labels['lowest']  = 'خورا ټيټ';
$labels['normal']  = 'نورمال';
$labels['high']    = 'لوړ';
$labels['highest'] = 'خورا لوړ';

$labels['nosubject']  = 'هېڅ موضوع';
$labels['showimages'] = 'انځورونه وښيه';
$labels['alwaysshow'] = 'تل له $استوونکي څخه انځورونه وښيه';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'ايچ ټی ايمل ال';
$labels['plaintoggle'] = 'ساده متن';
$labels['savesentmessagein'] = 'استول شوي استوزه په خوندي کړه';
$labels['dontsave'] = 'مه ساته';
$labels['maxuploadsize'] = 'لږترلږه د اجازه شوي فايل کچه $وي';

$labels['addcc'] = 'څرګندليک';
$labels['addbcc'] = 'پټليک';
$labels['addreplyto'] = 'ته ورزيات کړه';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'ددې استوزي استوونکی پوښتي چېد استوزي لوستلو سره سم تاسې خبر کړي،.    غواړئ چې استوونکی خبرشي؟';
$labels['receiptread'] = 'رسليک راګرځېدل (لوستل';
$labels['yourmessage'] = 'دا رسليک راګرځېدل ستاسې استوزي لپاره دی';
$labels['receiptnote'] = 'يادونه: دا رسليک تاسې ته يوازې ددې خبرتيا درکوي چې کله استوزی د ترلاسه کوونکي په کمپيوټر کې څرګند شي،  ددې هيڅ ډاډګيرنه نشته چې ترلاسه کوونکي د  استوزي متن لوستی او يا پرې پوه شوی دی';

// address boook
$labels['name']         = 'نوم ښوونه';
$labels['firstname']    = 'نوم';
$labels['surname']      = 'کورنی نوم';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'برېښناليک';
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
$labels['addcontact'] = 'نوی اړيکلوری ورزيات کړه';
$labels['editcontact'] = 'اړيکلوري سمون';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'سمون';
$labels['cancel'] = 'لري کول';
$labels['save']   = 'ساتل';
$labels['delete'] = 'ړنګول';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'نوي اړيکلوري کارټ جوړ کړه';
$labels['deletecontact']  = 'ټاکل شوي اړيکلوري ړنګ کړه';
$labels['composeto']      = 'ته ليک وليکه';
$labels['contactsfromto'] = 'له دومره اړيکلورو څخه دومره شمېرل شوي Contacts $from to $to of $count';
$labels['print']          = 'چاپ';
$labels['export']         = 'بهرول';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'اړيکلوري د ويکارډ په بڼه واستوئ';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'پخوانی غونډ وښيه';
$labels['firstpage']      = 'لومړی غونډ وښيه';
$labels['nextpage']       = 'ورپسې غونډ وښيه';
$labels['lastpage']       = 'پای غونډ وښيه';

$labels['group'] = 'Group';
$labels['groups'] = 'ډلې';
$labels['personaladrbook'] = 'ځاني پتې';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'رانقلول';
$labels['importcontacts'] = 'اړيکلوري نقل کړئ';
$labels['importfromfile'] = 'له فايل څخه رانقل کړئ';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'ټول ادرس کتاب بدل کړئ';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'ترسره شو';

// settings
$labels['settingsfor'] = 'لپاره برابرونې';
$labels['about'] = 'About';
$labels['preferences'] = 'غورونې';
$labels['userpreferences'] = 'کارن غورونې';
$labels['editpreferences'] = 'د کارن غورونو سمون';

$labels['identities'] = 'پېژندنې';
$labels['manageidentities'] = 'ددې حساب پېژندنو برابرول';
$labels['newidentity'] = 'نوي پېژندنه';

$labels['newitem'] = 'نوی توک';
$labels['edititem'] = 'توک سمون';

$labels['preferhtml'] = 'ايچ ټي ام ايل ښوون';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'ايچ ټي ام ايل استوزه';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'په زړه پورې نېټې';
$labels['setdefault']  = 'تلوالي برابرول';
$labels['autodetect']  = 'په خپلواکه توګه';
$labels['language']  = 'ژبه';
$labels['timezone']  = 'سيميز وخت';
$labels['pagesize']  = 'په هر مخ پرتې کښې';
$labels['signature'] = 'لاسليک';
$labels['dstactive']  = 'ورځنيو کارونو لنډيز ساتل';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'ايچ ټي‌ام ايل استوزي وليکه';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'ايچ ټي ام ايل لاسليک';
$labels['previewpane'] = 'پخوانی چوکاټ وښييه';
$labels['skin'] = 'انټرفيس بڼه';
$labels['logoutclear'] = 'کثافت داني د وتنې په وخت کې پاکه کړه';
$labels['logoutcompact'] = 'د وتنې په وخت کې ليکبکس ونښلوه';
$labels['uisettings'] = 'د کاروونکي برسېر';
$labels['serversettings'] = 'سرور برابرونې';
$labels['mailboxview'] = 'ليکبکس ښکار کړه';
$labels['mdnrequests'] = 'د استوونکي خبرتياوې';
$labels['askuser'] = 'له کاروونکي څخه وپوښته';
$labels['autosend'] = 'په خپلواکه توګه واستوه';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'لرې کړه';
$labels['readwhendeleted'] = 'په ړنګ شوو کې استوزي د لوستل شوي  په توګه په نښه کړه';
$labels['flagfordeletion'] = 'استوزه د ړنګ شوي پرځای بېکاره کړه';
$labels['skipdeleted'] = 'ړنګ شوي استوزي مه ښکاره کوه';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'انځورونه له لري په ليکه کې ښکاره کړه';
$labels['fromknownsenders'] = 'له اشنا استوونکو څخه';
$labels['always'] = 'تل';
$labels['showinlineimages'] = 'نښلول شوي انځورونه تر استوزې لاندې ښکاره کړه';
$labels['autosavedraft']  = 'بارليک په خپل سري توګه  خوندي‌کړه';
$labels['everynminutes']  = 'هر ه ګڼه په دقيقو کې';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'هېڅ';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'استوزي ښيي';
$labels['messagescomposition'] = 'استوزي ليکي';
$labels['mimeparamfolding'] = 'د نښلول شوو نومونه';
$labels['2231folding'] = 'بشپړ RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (نور)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'پرمختللي لارې';
$labels['focusonnewmessage'] = 'فوکاس براوسر کړکۍ په نوي استوزي کې';
$labels['checkallfolders'] = 'په ټولو پوښيو کې نوي استوزي وګورئ';
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

$labels['folder']  = 'پوښۍ';
$labels['folders']  = 'پوښۍ';
$labels['foldername']  = 'پوښۍ نوم';
$labels['subscribed']  = 'ګډ شو';
$labels['messagecount'] = 'استوزي';
$labels['create']  = 'جوړول، رامنځ ته کول';
$labels['createfolder']  = 'نوې پوښۍ جوړ کړه';
$labels['managefolders']  = 'پوښيو څارل';
$labels['specialfolders'] = 'ځانګړي پوښۍۍ';
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

$labels['sortby'] = 'اڼل';
$labels['sortasc']  = 'له پورته نه ښکته خوا ته برابرول';
$labels['sortdesc'] = 'له ښکته نه پورته خوا ته برابرول';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'بي';
$labels['KB'] = 'کي بي';
$labels['MB'] = 'ام بي';
$labels['GB'] = 'جي بي';

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
