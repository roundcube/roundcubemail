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
$labels['welcome']   = '$product වෙත පිළිගනිමු';
$labels['username']  = 'පරිශීලක නාමය';
$labels['password']  = 'මුරපදය';
$labels['server']    = 'පද්ධතිය';
$labels['login']     = 'පිවිසුම';

// taskbar
$labels['logout']   = 'පිටවීම';
$labels['mail']     = 'විද්‍යුත් තැපැල';
$labels['settings'] = 'සැකසුම්';
$labels['addressbook'] = 'ලිපින පොත';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'කටු සටහන්';
$labels['sent']   = 'යැවූ';
$labels['trash']  = 'ඉවතලන බඳුන';
$labels['junk']   = 'සුන්බුන්';

// message listing
$labels['subject'] = 'මාතෘකාව';
$labels['from']    = 'වෙතින්';
$labels['sender']  = 'Sender';
$labels['to']      = 'වෙත';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'පිළිතුරු සඳහා';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'දිනය';
$labels['size']    = 'ප්‍රමාණය';
$labels['priority'] = 'ප්‍රමුඛත්වය';
$labels['organization'] = 'සංවිධානය';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'බහාලුම්';
$labels['messagesfromto'] = '$from වෙතින් $to වෙත පණිවිඩ $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'පණිවිඩ $count න් $nr';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'පිටපත් කරන්න';
$labels['move']     = 'ගෙනයන්න';
$labels['moveto']   = 'ගෙන යන්න...';
$labels['download'] = 'බාගත කරන්න';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'ගොනුවේ නම';
$labels['filesize'] = 'ගොනුවේ ප්‍රමාණය';

$labels['addtoaddressbook'] = 'ලිපින පොතට එක් කරන්න';

// weekdays short
$labels['sun'] = 'ඉ';
$labels['mon'] = 'ස';
$labels['tue'] = 'අ';
$labels['wed'] = 'බ';
$labels['thu'] = 'බ්‍ර';
$labels['fri'] = 'සි';
$labels['sat'] = 'සෙ';

// weekdays long
$labels['sunday']    = 'ඉරිදා';
$labels['monday']    = 'සඳුදා';
$labels['tuesday']   = 'අඟහරුවාදා';
$labels['wednesday'] = 'බදාදා';
$labels['thursday']  = 'බ්‍රහස්පතින්දා';
$labels['friday']    = 'සිකුරාදා';
$labels['saturday']  = 'සෙනසුරාදා';

// months short
$labels['jan']	= 'ජන';
$labels['feb']	= 'පෙබ';
$labels['mar']	= 'මාර්';
$labels['apr']	= 'අප්‍රේ';
$labels['may']	= 'මැයි';
$labels['jun']	= 'ජූනි';
$labels['jul'] 	= 'ජූලි';
$labels['aug']	= 'අගෝ';
$labels['sep']	= 'සැප්';
$labels['oct']	= 'ඔක්';
$labels['nov']	= 'නොවැ';
$labels['dec']	= 'දෙසැ';

// months long
$labels['longjan']	= 'ජනවාරි';
$labels['longfeb']	= 'පෙබරවාරි';
$labels['longmar']	= 'මාර්තු';
$labels['longapr']	= 'අප්‍රේල්';
$labels['longmay']	= 'මැයි';
$labels['longjun']	= 'ජූනි';
$labels['longjul']	= 'ජූලි';
$labels['longaug']	= 'අගෝස්තු';
$labels['longsep']	= 'සැප්තැම්බර්';
$labels['longoct']	= 'ඔක්තෝම්බර්';
$labels['longnov']	= 'නොවැම්බර්';
$labels['longdec']	= 'දෙසැම්බර්';

$labels['today'] = 'අද';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'අඵත් පණිවිඩ සඳහා පිරික්සීම';
$labels['compose']          = 'පණිවිඩයක් සෑදීම';
$labels['writenewmessage']  = 'අඵත් පණිවිඩයක්';
$labels['reply']            = 'පිළිතුරු දෙන්න';
$labels['replytomessage']   = 'පණිවිඩයට පිළිතුරු';
$labels['replytoallmessage'] = 'එවන්නාට සහ සැමට පිළිතුරු';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'ඉදිරියට';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'පණිවිඩය ඉදිරියට';
$labels['deletemessage']    = 'පණිවිඩය මකන්න';
$labels['movemessagetotrash'] = 'පණිවිඩය ලටපට වෙත ගෙන යන්න';
$labels['printmessage']     = 'මෙම පණිවිඩය මුද්‍රණය කරන්න';
$labels['previousmessage']  = 'කලින් පණිවිඩය පෙන්නන්න';
$labels['firstmessage']     = 'පළමුවන පණිවිඩය පෙන්නන්න';
$labels['nextmessage']      = 'මීළඟ පණිවිඩය පෙන්නන්න';
$labels['lastmessage']      = 'අවසාන පණිවිඩය පෙන්නන්න';
$labels['backtolist']       = 'නැවතත් පණිවිඩ ලැයිස්තුවට';
$labels['viewsource']       = 'මූලාශ්‍රය පෙන්වන්න';
$labels['mark']             = 'සලකුණු කරන්න';
$labels['markmessages']     = 'පණිවිඩ සලකුණු කරන්න';
$labels['markread']         = 'කියවු ලෙස';
$labels['markunread']       = 'නොකියවු ලෙස';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'තවත් ක්‍රියා...';
$labels['more']             = 'තවත්';
$labels['back']             = 'ආපසු';
$labels['options']          = 'විකල්ප';

$labels['select'] = 'තෝරන්න';
$labels['all'] = 'සියල්ල';
$labels['none'] = 'කිසිවක් නැත';
$labels['currpage'] = 'දැනට ඇති පිටුව';
$labels['unread'] = 'නොකියවූ';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'Unanswered';
$labels['deleted'] = 'මකා දැමූ';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'Invert';
$labels['filter'] = 'පෙරන්න';
$labels['list'] = 'ලැයිස්තුව';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'සියල්ල දිගහරින්න';
$labels['expand-unread'] = 'Expand Unread';
$labels['collapse-all'] = 'සියල්ල හකුළන්න';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'වෙතින්/වෙත';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'ඇමුණුම';
$labels['nonesort'] = 'කිසිවක් නැත';
$labels['sentdate'] = 'යැවූ දිනය';
$labels['arrival'] = 'ලගාවූ දිනය';
$labels['asc'] = 'ආරෝහණ';
$labels['desc'] = 'අවරෝහණ';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'බහාලුම් ක්‍රියා...';
$labels['compact'] = 'සංයුක්ත';
$labels['empty'] = 'හිස්';

$labels['quota'] = 'Disk usage';
$labels['unknown']  = 'නොදනී';
$labels['unlimited']  = 'අසීමිත';

$labels['quicksearch']  = 'ඉක්මන් සෙවුම';
$labels['resetsearch']  = 'සෙවුම යලි පිහිටවීම';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'සම්පූර්ණ පණිවිඩය';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'නව කවුළුවක විවෘත කරන්න';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'පණිවිඩය යවන්න';
$labels['savemessage']    = 'කටු සටහනක් ලෙස සුරකින්න';
$labels['addattachment']  = 'ගොනුවක් අමුණන්න';
$labels['charset']        = 'අකුරැ කට්ටලය';
$labels['editortype']     = 'සම්පාදන ආකාරය';
$labels['returnreceipt']  = 'නැවත ලබන්නා';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'අක්‍ෂර වින්‍යාසය පරික්‍ෂාකරන්න';
$labels['resumeediting'] = 'සම්පාදන නැවත ඇරඹීම';
$labels['revertto']      = 'ආපසු ඒම';

$labels['attach'] = 'අමුණන්න';
$labels['attachments'] = 'ඇමුණුම්';
$labels['upload'] = 'ඇතුල් කරීම';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'වසන්න';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'අඩු';
$labels['lowest']  = 'අඩුම';
$labels['normal']  = 'සාමාන්‍ය';
$labels['high']    = 'ඉහළ';
$labels['highest'] = 'ඉහළම';

$labels['nosubject']  = '(මාතෘකාව නොමැත)';
$labels['showimages'] = 'රූප පෙන්වන්න';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'පැහැදිලි අකුරු';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'don\'t save';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Cc එක් කරන්න';
$labels['addbcc'] = 'Bcc එක් කරන්න';
$labels['addreplyto'] = '\'පිළිතුරු සඳහා\' එක් කරන්න';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'මෙම පණිවුඩය එවන්නා ඔබ එය කියවු බව අසයි. එය දැන්වීමට ඔබ කැමතිද?';
$labels['receiptread'] = 'ලැබීම් ලදුපත (කියවීම)';
$labels['yourmessage'] = 'මෙය ඔබගේ පණිවුඩයේ ලැබීම් ලදුපතයි';
$labels['receiptnote'] = 'සටහන: ලැබීම් ලදුපත මගින් මෙම පණිවුඩය කියවූ බව හෝ තේරුම් ගත් බව ගම්‍ය නොවේ. පණිවුඩය ලබන්නාට පෙන්නුම් කල බවටට සාක්ෂියක් පමණි';

// address boook
$labels['name']         = 'ප්‍රදර්ශනය වන නම';
$labels['firstname']    = 'මුල් නම';
$labels['surname']      = 'අවසන් නම';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'සුරතල් නම';
$labels['jobtitle']     = 'රැකියා තනතුර';
$labels['department']   = 'දෙපාර්තමේන්තුව';
$labels['gender']       = 'ස්ත්‍රි/පුරුෂ භාවය';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'විද්‍යුත් තැපෑල';
$labels['phone']        = 'දුරකථනය';
$labels['address']      = 'ලිපිනය';
$labels['street']       = 'Street';
$labels['locality']     = 'නගරය';
$labels['zipcode']      = 'ZIP Code';
$labels['region']       = 'ප්‍රාන්තය/පළාත';
$labels['country']      = 'රට';
$labels['birthday']     = 'උපන්දිනය';
$labels['anniversary']  = 'සංවත්සරය';
$labels['website']      = 'වෙබ් අඩවිය';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'සටහන්';
$labels['male']   = 'පුරුෂ';
$labels['female'] = 'ස්ත්‍රී';
$labels['manager'] = 'කළමණාකරු';
$labels['assistant'] = 'සහායක';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'All fields';
$labels['search'] = 'සොයන්න';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'සංකීර්ණ‍';
$labels['other'] = 'වෙනත්';

$labels['typehome']   = 'නිවස';
$labels['typework']   = 'රක්ෂාව';
$labels['typeother']  = 'වෙනත්';
$labels['typemobile']  = 'ජංගම';
$labels['typemain']  = 'ප්‍රධාන';
$labels['typehomefax']  = 'ගෘහස්ථ ෆැක්ස්';
$labels['typeworkfax']  = 'Work Fax';
$labels['typecar']  = 'Car';
$labels['typepager']  = 'පේජරය';
$labels['typevideo']  = 'වීඩියෝ';
$labels['typeassistant']  = 'සහායක';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'බ්ලොග්';
$labels['typeprofile'] = 'පැතිකඩ';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'නව සබඳතාවය එක් කරන්න';
$labels['editcontact'] = 'ලිපිනය වෙනස් කිරීම';
$labels['contacts'] = 'සබඳතා';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'පුද්ගලික තොරතුරු';

$labels['edit']   = 'සංස්කරණය';
$labels['cancel'] = 'අවලංගු කරන්න';
$labels['save']   = 'සුරකින්න';
$labels['delete'] = 'මකන්න';
$labels['rename'] = 'නම වෙනස් කරන්න';
$labels['addphoto'] = 'එක් කරන්න';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'අඵත් ලිපින පතක් සෑදීම';
$labels['deletecontact']  = 'තෝරාගත් ලිපින මකාදමන්න';
$labels['composeto']      = 'පනිවුඩයක් සෑදීම';
$labels['contactsfromto'] = '$from ගෙන් $to ලිපින $count';
$labels['print']          = 'මුද්‍රණය කරන්න';
$labels['export']         = 'අපනයනය';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'පෙර පිටුව පෙන්වන්න';
$labels['firstpage']      = 'පළමුවන පිටුව පෙන්වන්න';
$labels['nextpage']       = 'මීළඟ පිටුව පෙන්වන්න';
$labels['lastpage']       = 'අවසාන පිටුව පෙන්වන්න';

$labels['group'] = 'කණ්ඩායම';
$labels['groups'] = 'කණ්ඩායම්';
$labels['personaladrbook'] = 'පුද්ගලික ලිපිනය';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'ආනයනය කරන්න';
$labels['importcontacts'] = 'Import contacts';
$labels['importfromfile'] = 'Import from file:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'අවසානයි';

// settings
$labels['settingsfor'] = 'සඳහා සැකසුම්';
$labels['about'] = 'පිළිබඳ';
$labels['preferences'] = 'අභිමතය';
$labels['userpreferences'] = 'පරිශීලක අභිමතය';
$labels['editpreferences'] = 'පරිශීලක අභිමතය වෙනස් කිරීම්';

$labels['identities'] = 'අනන්‍යතාවන්';
$labels['manageidentities'] = 'මෙම ගිණුමෙ අනන්නයතාව හැසිරවීම';
$labels['newidentity'] = 'අඵත් අනන්නයතාව';

$labels['newitem'] = 'අඵත් අයිතම';
$labels['edititem'] = 'අයිතම වෙනස් කිරීම්';

$labels['preferhtml'] = 'HTML විදහා පෙන්වීම';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML පණිවිඩය';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'සුරුබුහුටි දින';
$labels['setdefault']  = 'පළමු තත්වයට';
$labels['autodetect']  = 'ස්වයංක්‍රීය';
$labels['language']  = 'භාෂාව';
$labels['timezone']  = 'කාල කලාපය';
$labels['pagesize']  = 'Rows per page';
$labels['signature'] = 'අත්සන';
$labels['dstactive']  = 'දිවා එළිය ඉතිරි කිරීම';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'HTML පණිවිඩයක් සෑදීම';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML අත්සන';
$labels['previewpane'] = 'පූර්වදර්ශන කොටස පෙන්නන්න';
$labels['skin'] = 'Interface skin';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'පරිශීලක අතුරුමුහුණත';
$labels['serversettings'] = 'Server Settings';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'මගෙන් විමසන්න';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'නොසලකා හරින්න';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'සැමවිට';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'ස්වයංක්‍රීයව කටු සටහන සුරකින්න';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'කිසි විටෙකත් නැති';
$labels['immediately']  = 'හැකි ඉක්මනින්';
$labels['messagesdisplaying'] = 'Displaying Messages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Attachment names';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (වෙනත්)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'Advanced options';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'අංශය';
$labels['maintenance'] = 'නඩත්තු කිරීම';
$labels['newmessage'] = 'අලුත් පණිවිඩයක්';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'ස්වයංක්‍රීයව අත්සන ඇතුලත් කරන්න';
$labels['newmessageonly'] = 'අලුත් පණිවිඩය පමණක්';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['replysignaturepos'] = 'When replying or forwarding place signature';
$labels['belowquote'] = 'below the quote';
$labels['abovequote'] = 'above the quote';
$labels['insertsignature'] = 'අත්සන ඇතුලත් කරන්න';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = 'තත්පර $nකට පසු';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'සලකුණු සහිත වචන නොසලකා හරින්න';
$labels['spellcheckignorenums'] = 'අංක සහිත වචන නොසලකා හරින්න';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'ශබ්ද කෝෂයට එක් කරන්න';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'බහාලුම';
$labels['folders']  = 'බහාලුම්';
$labels['foldername']  = 'බහාලුමේ නම';
$labels['subscribed']  = 'සාමාජික';
$labels['messagecount'] = 'පණිවිඩ';
$labels['create']  = 'තනන්න';
$labels['createfolder']  = 'නව බහාලුමක් තනන්න';
$labels['managefolders']  = 'බහාලුම් කළමනාකරණය කරන්න';
$labels['specialfolders'] = 'විශේෂ බහාලුම්';
$labels['properties'] = 'වත්කම්';
$labels['folderproperties'] = 'බහාලුමේ වත්කම්';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'ස්ථානය';
$labels['info'] = 'තොරතුරු';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'බහාලුම් වර්ගය';
$labels['personalfolder']  = 'පුද්ගලික බහාලුම';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'පොදු බහාලුම';

$labels['sortby'] = 'වර්ග කරන්න';
$labels['sortasc']  = 'ආරෝහණ වර්ග කිරීම';
$labels['sortdesc'] = 'අවරෝහණ වර්ග කිරීම';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'ප්ලගිනය';
$labels['version'] = 'අනුවාදය';
$labels['source'] = 'මූලාශ්‍රය';
$labels['license'] = 'බලපත්‍රය';
$labels['support'] = 'සහය ලබාගන්න';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'යුනිකේත';
$labels['english'] = 'ඉංග්‍රීසි';
$labels['westerneuropean'] = 'බටහිර යුරෝපීය';
$labels['easterneuropean'] = 'Eastern European';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'බෝල්ටික්';
$labels['cyrillic'] = 'Cyrillic';
$labels['arabic'] = 'අරාබි';
$labels['greek'] = 'ග්‍රීක';
$labels['hebrew'] = 'හීබෲ';
$labels['turkish'] = 'තුර්කි';
$labels['nordic'] = 'නොර්ඩික්';
$labels['thai'] = 'තායි';
$labels['celtic'] = 'සෙල්ටික්';
$labels['vietnamese'] = 'වියට්නාම';
$labels['japanese'] = 'ජපන්';
$labels['korean'] = 'කොරියානු';
$labels['chinese'] = 'චීන';

?>
