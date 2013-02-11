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
$labels['welcome']   = 'நல்வரவு';
$labels['username']  = 'பயனர்';
$labels['password']  = 'கடவுச்சொல்';
$labels['server']    = 'சேவையகம்';
$labels['login']     = 'நுழை';

// taskbar
$labels['logout']   = 'வெளியேறு';
$labels['mail']     = 'மின்னஞ்சல்';
$labels['settings'] = 'அமைப்புகள்';
$labels['addressbook'] = 'தொடர்புகள்';

// mailbox names
$labels['inbox']  = 'அகப்பெட்டி';
$labels['drafts'] = 'வரைவுகள்';
$labels['sent']   = 'அனுப்பிய அஞ்சல்';
$labels['trash']  = 'குப்பை';
$labels['junk']   = 'எரிதம்';

// message listing
$labels['subject'] = 'தலைப்பு';
$labels['from']    = 'அனுப்புனர்';
$labels['sender']  = 'Sender';
$labels['to']      = 'பெறுனர்';
$labels['cc']      = 'நகல்';
$labels['bcc']     = 'மறை நகல்';
$labels['replyto'] = 'பதிலளி';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'தேதி';
$labels['size']    = 'அளவு';
$labels['priority'] = 'முக்கியத்துவம்';
$labels['organization'] = 'நிறுவனம்';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'அடைவுகள்';
$labels['messagesfromto'] = 'செய்திகள் $countல் $from லிருந்து $to வரை';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = '$countல் $nr வது செய்தி';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'நகல்';
$labels['move']     = 'Move';
$labels['moveto']   = 'இதற்கு அனுப்பு...';
$labels['download'] = 'பதிவிறக்கு';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'கோப்பு பெயர்';
$labels['filesize'] = 'கோப்பு அளவு';

$labels['addtoaddressbook'] = 'தொடர்புகளுக்கு சேர்க்கவும்';

// weekdays short
$labels['sun'] = 'ஞாயிறு';
$labels['mon'] = 'திங்கள்';
$labels['tue'] = 'செவ்வாய்';
$labels['wed'] = 'புதன்';
$labels['thu'] = 'வியாழன்';
$labels['fri'] = 'வெள்ளி';
$labels['sat'] = 'சனி';

// weekdays long
$labels['sunday']    = 'ஞாயிறு';
$labels['monday']    = 'திங்கள்';
$labels['tuesday']   = 'செவ்வாய்';
$labels['wednesday'] = 'புதன்';
$labels['thursday']  = 'வியாழன்';
$labels['friday']    = 'வெள்ளி';
$labels['saturday']  = 'சனி';

// months short
$labels['jan']	= 'சனவரி';
$labels['feb']	= 'பிப்ரவரி';
$labels['mar']	= 'மார்ச்';
$labels['apr']	= 'ஏப்ரல்';
$labels['may']	= 'மே';
$labels['jun']	= 'சூன்';
$labels['jul'] 	= 'சூலை';
$labels['aug']	= 'ஆகஸ்ட்';
$labels['sep']	= 'செப்டம்பர்';
$labels['oct']	= 'அக்டோபர்';
$labels['nov']	= 'நவம்பர்';
$labels['dec']	= 'டிசம்பர்';

// months long
$labels['longjan']	= 'சனவரி';
$labels['longfeb']	= 'பிப்ரவரி';
$labels['longmar']	= 'மார்ச்';
$labels['longapr']	= 'ஏப்ரல்';
$labels['longmay']	= 'மே';
$labels['longjun']	= 'சூன்';
$labels['longjul']	= 'சூலை';
$labels['longaug']	= 'ஆகஸ்ட்';
$labels['longsep']	= 'செப்டம்பர்';
$labels['longoct']	= 'அக்டோபர்';
$labels['longnov']	= 'நவம்பர்';
$labels['longdec']	= 'டிசம்பர்';

$labels['today'] = 'இன்று';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'புது அஞ்சல்களை பார்க்கவும்';
$labels['compose']          = 'அஞ்சல் எழுது';
$labels['writenewmessage']  = 'புது அஞ்சல் எழுதவும்';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'அனுப்புனருக்கு பதிலளி';
$labels['replytoallmessage'] = 'அனுப்புனர் மற்றும் எல்லா பெறுனர்களுக்கும் பதிலளி';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'செய்தியை முன்அனுப்பு';
$labels['deletemessage']    = 'செய்தியை நீக்கு';
$labels['movemessagetotrash'] = 'செய்தியை குப்பைக்கு நகர்த்து';
$labels['printmessage']     = 'செய்தியை அச்சிடவும்';
$labels['previousmessage']  = 'முந்திய செய்தியை காட்டவும்';
$labels['firstmessage']     = 'முதல் செய்தியை காட்டவும்';
$labels['nextmessage']      = 'அடுத்த செய்தியை காட்டு';
$labels['lastmessage']      = 'கடைசி செய்தியை காட்டு';
$labels['backtolist']       = 'செய்தி பட்டியலுக்கு செல்லவும்';
$labels['viewsource']       = 'மூலத்தை காட்டு';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'செய்திகளை குறியிடு';
$labels['markread']         = 'படித்ததாக குறியிடு';
$labels['markunread']       = 'படிக்காததாக குறியிடு';
$labels['markflagged']      = 'நட்சத்திரமிட்டதாக குறியிடு';
$labels['markunflagged']    = 'நட்சத்திரமிடாததாக குறியிடு';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'தேர்ந்தெடு';
$labels['all'] = 'எல்லாம்';
$labels['none'] = 'எதுவுமில்லை';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'படிக்காதது';
$labels['flagged'] = 'நட்சத்திரமிட்டது';
$labels['unanswered'] = 'பதிலளிக்காதது';
$labels['deleted'] = 'நீக்கியது';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'தலைகீழ்';
$labels['filter'] = 'வடிப்பான்';
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
$labels['nonesort'] = 'எதுவுமில்லை';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'குறுகிய';
$labels['empty'] = 'காலி';

$labels['quota'] = 'பயன்பாட்டு அளவு';
$labels['unknown']  = 'தெரியாத';
$labels['unlimited']  = 'அளவில்லா';

$labels['quicksearch']  = 'விரைவு தேடல்';
$labels['resetsearch']  = 'தேடலை மறுஅமை';
$labels['searchmod']  = 'மாற்றிகளை தேடு';
$labels['msgtext']  = 'முழு செய்தி';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'புது சாளரத்தில் திற';
$labels['emlsave'] = 'பதிவிறக்கு (.eml)';

// message compose
$labels['editasnew']      = 'புதியதாக திருத்து';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'இப்போது அனுப்பு';
$labels['savemessage']    = 'இந்த வரைவை சேமி';
$labels['addattachment']  = 'ஒரு கோப்பை இணைக்கவும்';
$labels['charset']        = 'எழுத்து குறிமுறை';
$labels['editortype']     = 'திருத்தும் வகை';
$labels['returnreceipt']  = 'திரும்பு சீட்டு';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'எழுத்துப்பிழை சரிபார்';
$labels['resumeediting'] = 'திருத்துவதை தொடரவும்';
$labels['revertto']      = 'முந்திய நிலைக்கு திரும்பவும்';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'கோப்பு இணைப்புகள்';
$labels['upload'] = 'பதிவேற்று';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'மூடு';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'குறைந்த';
$labels['lowest']  = 'மிக குறைந்த';
$labels['normal']  = 'சாதாரண';
$labels['high']    = 'அதிக';
$labels['highest'] = 'மிக அதிக';

$labels['nosubject']  = '(தலைப்பில்லா)';
$labels['showimages'] = 'படங்களை காட்டு';
$labels['alwaysshow'] = '$-னிடமிருந்து வரும் படங்களை காட்டு';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'எளிய உரை';
$labels['savesentmessagein'] = 'அனுப்பிய அஞ்சலை இங்கு சேமிக்கவும்';
$labels['dontsave'] = 'சேமிக்காதே';
$labels['maxuploadsize'] = 'அனுமதிக்கப்படும் அதிகபட்ச கோப்பு அளவு $';

$labels['addcc'] = 'நகல் சேர்';
$labels['addbcc'] = 'மறை நகல் சேர்';
$labels['addreplyto'] = 'பதிலளிக்க வேண்டியவர்களை சேர்';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'இந்த செய்தியின் அனுப்புனர் இதை படித்தவுடன் அறிவிக்கும்படி கேட்டார். அறிவிக்க விரும்புகிறீர்களா ?';
$labels['receiptread'] = 'திரும்பு சீட்டு (படி)';
$labels['yourmessage'] = 'இது உங்கள் செய்தியின் திரும்பு சீட்டு';
$labels['receiptnote'] = 'குறிப்பு: இந்த ஒப்புகை பெறுனரிடம் செய்தியை காட்டியதற்காக வருகிறது. அவர் அதை படித்ததற்கு எந்த உத்திரவாதமும் இல்லை';

// address boook
$labels['name']         = 'காட்சி பெயர்';
$labels['firstname']    = 'முதற் பெயர்';
$labels['surname']      = 'கடைசி பெயர்';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'மின்னஞ்சல்';
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
$labels['addcontact'] = 'புது தொடர்பை சேர்';
$labels['editcontact'] = 'தொடர்பை திருத்து';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'திருத்து';
$labels['cancel'] = 'ரத்து';
$labels['save']   = 'சேமி';
$labels['delete'] = 'நீக்கு';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'புது தொடர்பு அட்டையை உருவாக்கு';
$labels['deletecontact']  = 'தேர்ந்தெடுத்த தொடர்புகளை நீக்கு';
$labels['composeto']      = 'இவர்-கு அஞ்சல் எழுது';
$labels['contactsfromto'] = 'தொடர்புகள் $countல் $from லிருந்து $to வரை';
$labels['print']          = 'அச்சிடு';
$labels['export']         = 'ஏற்றுமதி';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'தொடர்புகளை vCard வடிவத்தில் ஏற்றுமதி செய்யவும்';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'முந்திய குழுவை காட்டு';
$labels['firstpage']      = 'முதல் குழுவை காட்டு';
$labels['nextpage']       = 'அடுத்த குழுவை காட்டு';
$labels['lastpage']       = 'கடைசி குழுவை காட்டு';

$labels['group'] = 'Group';
$labels['groups'] = 'குழுக்கள்';
$labels['personaladrbook'] = 'சுய முகவரி';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'இறக்குமதி';
$labels['importcontacts'] = 'தொடர்புகளை இறக்குமதி செய்யவும்';
$labels['importfromfile'] = 'இந்த கோப்பிலிருந்து இறக்குமதி செய்யவும்:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'எல்லா தொடர்புகளையும் மாற்றி அமை';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'முடிந்தது';

// settings
$labels['settingsfor'] = 'இதற்கான அமைப்புகள்';
$labels['about'] = 'About';
$labels['preferences'] = 'விருப்பங்கள்';
$labels['userpreferences'] = 'பயனர் விருப்பங்கள்';
$labels['editpreferences'] = 'பயனர் விருப்பங்களை திருத்து';

$labels['identities'] = 'அடையாளங்கள்';
$labels['manageidentities'] = 'இந்த கணக்கிற்கான அடையாளங்களை நிற்வாகிக்கவும்';
$labels['newidentity'] = 'புது அடையாளம்';

$labels['newitem'] = 'புது உருப்படி';
$labels['edititem'] = 'உருப்படியை திருத்து';

$labels['preferhtml'] = 'HTML காட்டு';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML செய்தி';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'நல்ல தேதி';
$labels['setdefault']  = 'இயல்பை அமை';
$labels['autodetect']  = 'தானியங்கி';
$labels['language']  = 'மொழி';
$labels['timezone']  = 'நேர மண்டலம்';
$labels['pagesize']  = 'ஒரு பக்கத்திற்கான வரிகள்';
$labels['signature'] = 'கையொப்பம்';
$labels['dstactive']  = 'பகல் ஒளி சேமிப்பு நேரம்';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'HTML செய்திகளை எழுது';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML கையொப்பம்';
$labels['previewpane'] = 'முந்திய பாத்தியை காட்டு';
$labels['skin'] = 'முகப்பு தோற்றம்';
$labels['logoutclear'] = 'வெளியேறும் போது குப்பையை காலி செய்';
$labels['logoutcompact'] = 'வெளியேறும் போது இன்பாக்ஸை குறுக்கவும்';
$labels['uisettings'] = 'பயனர் முகப்பு';
$labels['serversettings'] = 'சேவையகம் அமைப்புகள்';
$labels['mailboxview'] = 'அஞ்சல் பெட்டி காட்சி';
$labels['mdnrequests'] = 'அனுப்புனர் அறிவிப்புகள்';
$labels['askuser'] = 'பயனரை கேள்';
$labels['autosend'] = 'தானாக அனுப்பு';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'தவிர்';
$labels['readwhendeleted'] = 'செய்தியை நீக்கும் போது படித்ததாக குறி';
$labels['flagfordeletion'] = 'நீக்குவதற்கு பதிலாக நீக்குவதாக நட்சத்திரமிடு';
$labels['skipdeleted'] = 'நீக்கிய செய்திகளை காட்டாதே';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'தொலை தூர துணை படங்களை காட்டு';
$labels['fromknownsenders'] = 'தெரிந்த அனுப்புனரிடமிருந்து';
$labels['always'] = 'எப்போதும்';
$labels['showinlineimages'] = 'இணைத்த படங்களை செய்திகளுக்கு கீழே காட்டவும்';
$labels['autosavedraft']  = 'தானாக வரைவுகளை சேமி';
$labels['everynminutes']  = 'ஒவ்வொரு $n நிமிடங்களுக்கும்';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'எப்போதுமில்லை';
$labels['immediately']  = 'immediately';
$labels['messagesdisplaying'] = 'செய்திகளை காட்டுகிறது';
$labels['messagescomposition'] = 'செய்திகளை எழுதுகிறது';
$labels['mimeparamfolding'] = 'இணைப்பு பெயர்கள்';
$labels['2231folding'] = 'முழு RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'முழு RFC 2047 (மற்ற)';
$labels['force7bit'] = 'Use MIME encoding for 8-bit characters';
$labels['advancedoptions'] = 'மேம்பட்ட விருப்பங்கள்';
$labels['focusonnewmessage'] = 'உலாவி சாளரத்தை புது செய்திக்காக கவனி';
$labels['checkallfolders'] = 'புது செய்திகளுக்காக எல்லா அடைவுகளையும் சரிபார்';
$labels['displaynext'] = 'செய்தியை நீக்கு/நகர் பிறகு அடுத்த செய்தியை காட்டு';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'முக்கிய விருப்பங்கள்';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'பிரிவு';
$labels['maintenance'] = 'பராமரிப்பு';
$labels['newmessage'] = 'புது செய்தி';
$labels['signatureoptions'] = 'கையொப்ப விருப்பங்கள்';
$labels['whenreplying'] = 'பதிலளிக்கும் போது';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'புது செய்தியை அசலின் மேல் துவக்கு';
$labels['replybottomposting'] = 'புது செய்தியை அசலின் கீழ் துவக்கு';
$labels['replyremovesignature'] = 'பதிலளிக்கும் போது அசல் கையெழுத்தை செய்தியிலிருந்து நீக்கவும்';
$labels['autoaddsignature'] = 'தானாக கையொப்பத்தை சேர்';
$labels['newmessageonly'] = 'புது செய்தி மட்டும்';
$labels['replyandforwardonly'] = 'பதிலளிப்பு முன்அனுப்பு மட்டும்';
$labels['replysignaturepos'] = 'பதிலளிக்கும் போதும் முன்அனுப்பும் போதும் கையோப்பமிடு';
$labels['belowquote'] = 'மேற்கோளுக்கு கீழே';
$labels['abovequote'] = 'மேற்கோளுக்கு மேலே';
$labels['insertsignature'] = 'கையோப்பமிடு';
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

$labels['folder']  = 'அடைவு';
$labels['folders']  = 'அடைவுகள்';
$labels['foldername']  = 'அடைவு பெயர்';
$labels['subscribed']  = 'சந்தா';
$labels['messagecount'] = 'செய்திகள்';
$labels['create']  = 'உருவாக்கு';
$labels['createfolder']  = 'புது அடைவு உருவாக்கு';
$labels['managefolders']  = 'அடைவுகளை நிற்வகி';
$labels['specialfolders'] = 'சிறப்பு அடைவுகள்';
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

$labels['sortby'] = 'வாரியாக அடுக்கு';
$labels['sortasc']  = 'ஏறு வரிசையாக அடுக்கு';
$labels['sortdesc'] = 'இறங்கு வரிசையாக அடுக்கு';
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
