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
$labels['welcome']   = '$product ലേയ്ക്ക് സ്വാഗതം';
$labels['username']  = 'ഉപയോക്തൃനാമം';
$labels['password']  = 'രഹസ്യവാക്ക്';
$labels['server']    = 'സേവകന്‍';
$labels['login']     = 'പ്രവേശിക്കുക';

// taskbar
$labels['logout']   = 'പുറത്ത് കടക്കുക';
$labels['mail']     = 'തപാല്‍';
$labels['settings'] = 'ക്രമീകരണങ്ങള്‍';
$labels['addressbook'] = 'വിലാസ പുസ്തകം';

// mailbox names
$labels['inbox']  = 'ഇന്‍ബോക്സ്';
$labels['drafts'] = 'പൂര്‍ത്തിയാകാത്തവ';
$labels['sent']   = 'അയച്ചവ';
$labels['trash']  = 'ചവറ്റുകുട്ട';
$labels['junk']   = 'ആവശ്യമില്ലാത്തവ';

// message listing
$labels['subject'] = 'വിഷയം';
$labels['from']    = 'പ്രേഷിതന്‍';
$labels['sender']  = 'Sender';
$labels['to']      = 'സ്വീകര്‍ത്താവ്';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'Reply-To';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'തീയതി';
$labels['size']    = 'വലിപ്പം';
$labels['priority'] = 'മുന്‍ഗണന';
$labels['organization'] = 'Organization';
$labels['readstatus'] = 'അവസ്ഥ വായിക്കുക';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'ഫോള്‍ഡറുകള്‍';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'Message $nr of $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'പകര്‍ത്തുക';
$labels['move']     = 'നീക്കുക';
$labels['moveto']   = 'Move to...';
$labels['download'] = 'ഡൗണ്‍ലോഡ്';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'ഫയല്‍ നാമം';
$labels['filesize'] = 'ഫയലിന്റെ വലിപ്പം';

$labels['addtoaddressbook'] = 'മേല്‍വിലാസങ്ങളില്‍ ചേര്‍ക്കുക.';

// weekdays short
$labels['sun'] = 'ഞാ';
$labels['mon'] = 'തി';
$labels['tue'] = 'ചൊ';
$labels['wed'] = 'ബു';
$labels['thu'] = 'വ്യാ';
$labels['fri'] = 'വെ';
$labels['sat'] = 'ശ';

// weekdays long
$labels['sunday']    = 'ഞായര്‍';
$labels['monday']    = 'തിങ്കള്‍';
$labels['tuesday']   = 'ചൊവ്വ';
$labels['wednesday'] = 'ബുധന്‍';
$labels['thursday']  = 'വ്യാഴം';
$labels['friday']    = 'വെള്ളി';
$labels['saturday']  = 'ശനി';

// months short
$labels['jan']	= 'ജനു';
$labels['feb']	= 'ഫെബ്';
$labels['mar']	= 'മാര്‍';
$labels['apr']	= 'ഏപ്ര';
$labels['may']	= 'മെ';
$labels['jun']	= 'ജൂണ്‍';
$labels['jul'] 	= 'ജൂലൈ';
$labels['aug']	= 'ആഗസ്റ്റ്';
$labels['sep']	= 'സെപ്റ്റംബര്‍';
$labels['oct']	= 'ഒക്ടോബര്‍';
$labels['nov']	= 'നവംബര്‍';
$labels['dec']	= 'ഡിസംബര്‍';

// months long
$labels['longjan']	= 'ജനുവരി';
$labels['longfeb']	= 'ഫെബ്രുവരി';
$labels['longmar']	= 'മാര്‍ച്ച്';
$labels['longapr']	= 'ഏപ്രില്‍';
$labels['longmay']	= 'മെ';
$labels['longjun']	= 'ജൂണ്‍';
$labels['longjul']	= 'ജൂലൈ';
$labels['longaug']	= 'ആഗസ്റ്റ്';
$labels['longsep']	= 'സെപ്തംബ‌ര്‍';
$labels['longoct']	= 'ഒക്ടോബര്‍';
$labels['longnov']	= 'നവംബ‌ര്‍';
$labels['longdec']	= 'ഡിസംബ‌ര്‍';

$labels['today'] = 'ഇന്ന്';

// toolbar buttons
$labels['refresh']          = 'പുതുക്കുക';
$labels['checkmail']        = 'പുതിയ സന്ദേശത്തിനായി തിരയുക';
$labels['compose']          = 'രചന';
$labels['writenewmessage']  = 'Create a new message';
$labels['reply']            = 'മറുപടി';
$labels['replytomessage']   = 'Reply to sender';
$labels['replytoallmessage'] = 'Reply to list or to sender and all recipients';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'കൈമാറുക';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'സന്ദേശം കൈമാറുക';
$labels['deletemessage']    = 'സന്ദേശം മായ്ക്കുക';
$labels['movemessagetotrash'] = 'സന്ദേശം ചവറ്റുകുട്ടയിലേക്ക് നീക്കുക';
$labels['printmessage']     = 'ഈ സന്ദേശം അച്ചടിക്കുക';
$labels['previousmessage']  = 'മുന്‍പുളള സന്ദേശം കാണിക്കുക.';
$labels['firstmessage']     = 'ആദ്യത്തെ സന്ദേശം കാണിക്കുക.';
$labels['nextmessage']      = 'അടുത്ത സന്ദേശം കാണിക്കുക.';
$labels['lastmessage']      = 'അവസാനത്തെ സന്ദേശം കാണിക്കുക.';
$labels['backtolist']       = 'Back to message list';
$labels['viewsource']       = 'ഉറവിടം കാണിക്കുക.';
$labels['mark']             = 'അടയാളപ്പെടുത്തു';
$labels['markmessages']     = 'സന്തേശങ്ങള്‍ അടയാളപ്പെടുത്തു';
$labels['markread']         = 'വായിച്ചതായി';
$labels['markunread']       = 'വായിക്കാത്തതായി';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'കൂടുതല്‍ ചെയ്തികള്‍...';
$labels['more']             = 'കൂടുതല്‍';
$labels['back']             = 'പിന്നോട്ട്';
$labels['options']          = 'ഐച്ഛികങ്ങള്‍‌';

$labels['select'] = 'തെരഞ്ഞെടുക്കുക';
$labels['all'] = 'എല്ലാം';
$labels['none'] = 'ഒന്നുമില്ല';
$labels['currpage'] = 'നിലവിലുളള പേജ്';
$labels['unread'] = 'വായിക്കാത്തത്';
$labels['flagged'] = 'അടയാളപ്പെടുത്തിയവ';
$labels['unanswered'] = 'മറുപടി കൊടുക്കാത്ത';
$labels['deleted'] = 'മായ്ച്ചവ';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'തലതിരിക്കുക';
$labels['filter'] = 'അരിപ്പ';
$labels['list'] = 'പട്ടിക';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'എല്ലാം വികസിപ്പിക്കുക';
$labels['expand-unread'] = 'വായിക്കാത്തവ വികസിപ്പിക്കുക';
$labels['collapse-all'] = 'എല്ലാം അടയ്ക്കുക';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'എവിടുന്ന് /എങ്ങൊട്ടു';
$labels['flag'] = 'Flag';
$labels['attachment'] = 'Attachment';
$labels['nonesort'] = 'ഒന്നുമില്ല';
$labels['sentdate'] = 'അയച്ച തീയതി';
$labels['arrival'] = 'വന്ന തീയതി';
$labels['asc'] = 'ആരോഹണം';
$labels['desc'] = 'അവരോഹണം';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'തരംതിരിക്കണ്ട രീതി';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'ചുരുക്കു';
$labels['empty'] = 'ശൂന്യം';

$labels['quota'] = 'ഡിസ്ക്ക് ഉപയോഗം';
$labels['unknown']  = 'അറിയാത്ത';
$labels['unlimited']  = 'പരിമിതികളില്ലാത്ത';

$labels['quicksearch']  = 'പെട്ടെന്ന് തെരയു';
$labels['resetsearch']  = 'Reset search';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'മുഴുവന്‍ സന്ദേശവും';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'പുതിയ വിന്‍ഡോയില്‍ തുറക്കുക';
$labels['emlsave'] = 'Download (.eml)';

// message compose
$labels['editasnew']      = 'പുതിയതായി തിരുത്തുക';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'സന്ദേശം അയയ്ക്കുക';
$labels['savemessage']    = 'പൂര്‍ത്തിയാകാത്തവ ആയി സൂക്ഷിക്കുക';
$labels['addattachment']  = 'ഒരു ഫയല്‍ മെയിലിനൊപ്പം ചേര്‍ക്കുക';
$labels['charset']        = 'Charset';
$labels['editortype']     = 'തിരുത്തല്‍ രീതി';
$labels['returnreceipt']  = 'Return receipt';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'യഥാര്‍ഥ സന്ദേശം';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'അക്ഷരത്തെറ്റു് പരിശോധിക്കുക';
$labels['resumeediting'] = 'തിരുത്തല്‍ തുടരുക';
$labels['revertto']      = 'Revert to';

$labels['attach'] = 'സംയോജിപ്പിക്കു';
$labels['attachments'] = 'അറ്റാച്ച്മെന്റുകള്‍';
$labels['upload'] = 'അപ്‌ലോഡ് ചെയ്യുക';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'അടയ്ക്കുക';
$labels['messageoptions']  = 'സന്ദേശ ​ഐച്ഛികങ്ങള്‍...';

$labels['low']     = 'കുറഞ്ഞ';
$labels['lowest']  = 'ഏറ്റവും കുറഞ്ഞ';
$labels['normal']  = 'സാധാരണ';
$labels['high']    = 'കൂടിയ';
$labels['highest'] = 'ഏറ്റവും കൂടിയത്';

$labels['nosubject']  = '(വിഷയം ഇല്ല)';
$labels['showimages'] = 'ചിത്രങ്ങള്‍ കാണിക്കുക';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'ഇതൊരു കരടുരൂപമാണ്.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Plain text';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'സുക്ഷിക്കരുത്';
$labels['maxuploadsize'] = '$size ആണ് പരമാവധി  ഫയല്‍ വലിപ്പം';

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
$labels['name']         = 'Display Name';
$labels['firstname']    = 'ആദ്യ നാമം';
$labels['surname']      = 'അവസാന നാമം';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'ചെല്ലപേര്';
$labels['jobtitle']     = 'ജോലിയുടെ പേര്';
$labels['department']   = 'വകുപ്പ്';
$labels['gender']       = 'ലിംഗം';
$labels['maidenname']   = 'കന്യകാനാമം';
$labels['email']        = 'ഇമെയില്‍';
$labels['phone']        = 'ഫോണ്‍';
$labels['address']      = 'മേല്‍വിലാസം';
$labels['street']       = 'തെരുവ്';
$labels['locality']     = 'നഗരം';
$labels['zipcode']      = 'പിന്‍ കോഡ്';
$labels['region']       = 'സംസ്ഥാനം / പ്രദേശം';
$labels['country']      = 'രാജ്യം';
$labels['birthday']     = 'പിറന്നാള്‍';
$labels['anniversary']  = 'വാര്‍ഷികം';
$labels['website']      = 'വെബ്സൈറ്റ്';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'കുറിപ്പുകള്‍';
$labels['male']   = 'പുരുഷന്‍';
$labels['female'] = 'സ്‌ത്രീ';
$labels['manager'] = 'പരിപാലകന്‍';
$labels['assistant'] = 'സഹായി';
$labels['spouse'] = 'ഭാര്യ/ഭര്‍ത്താവ്';
$labels['allfields'] = 'All fields';
$labels['search'] = 'തെരയുക';
$labels['advsearch'] = 'വിപുലമായ തിരച്ചില്‍';
$labels['advanced'] = 'സങ്കീര്‍ണ്ണം';
$labels['other'] = 'മറ്റുള്ളത്';

$labels['typehome']   = 'ആസ്ഥാനം';
$labels['typework']   = 'ജോലി';
$labels['typeother']  = 'മറ്റുള്ളത്';
$labels['typemobile']  = 'മോബൈല്‍';
$labels['typemain']  = 'പ്രധാനപ്പെട്ട';
$labels['typehomefax']  = 'വീട്ടിലെ ഫാക്സ്';
$labels['typeworkfax']  = 'കാര്യാലയത്തിലെ ഫാക്സ്';
$labels['typecar']  = 'കാര്‍';
$labels['typepager']  = 'പേജര്‍';
$labels['typevideo']  = 'ചലച്ചിത്രം';
$labels['typeassistant']  = 'സഹായി';
$labels['typehomepage']  = 'ഹോം പേജ്';
$labels['typeblog'] = 'ബ്ലോഗ്';
$labels['typeprofile'] = 'പ്രൊഫൈല്‍';

$labels['addfield'] = 'Add field...';
$labels['addcontact'] = 'Add new contact';
$labels['editcontact'] = 'Edit contact';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'സ്വകാര്യ വിവരങ്ങള്‍';

$labels['edit']   = 'തിരുത്തുക';
$labels['cancel'] = 'റദ്ദാക്കുക';
$labels['save']   = 'സൂക്ഷിക്കുക';
$labels['delete'] = 'നീക്കം ചെയ്യുക';
$labels['rename'] = 'പേരുമാറ്റുക';
$labels['addphoto'] = 'ചേര്‍ക്കുക';
$labels['replacephoto'] = 'പകരം വയ്ക്കുക';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'Create new contact card';
$labels['deletecontact']  = 'Delete selected contacts';
$labels['composeto']      = 'Compose mail to';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = 'അച്ചടി';
$labels['export']         = 'പുറത്തെടുക്കുക';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'കുട്ടത്തിന്റെ പേര് മാറ്റുക';
$labels['groupdelete']    = 'കൂട്ടം മായ്ക്കുക';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'മുമ്പുളള താള്‍ കാണിയ്ക്കുക';
$labels['firstpage']      = 'Show first page';
$labels['nextpage']       = 'Show next page';
$labels['lastpage']       = 'Show last page';

$labels['group'] = 'കൂട്ടം';
$labels['groups'] = 'കൂട്ടങ്ങള്‍';
$labels['personaladrbook'] = 'സ്വകാര്യ വിലാസങ്ങള്‍';

$labels['searchsave'] = 'തിരയല്‍ സൂക്ഷിക്കുക';
$labels['searchdelete'] = 'തിരയല്‍ നീക്കംചെയ്യുക';

$labels['import'] = 'പ്രോഗ്രമ്മിലേക്ക് എടുക്കുക';
$labels['importcontacts'] = 'Import contacts';
$labels['importfromfile'] = 'Import from file:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'Replace the entire address book';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'പൂര്‍ത്തീകരിച്ചു';

// settings
$labels['settingsfor'] = 'Settings for';
$labels['about'] = 'വിവരം';
$labels['preferences'] = 'Preferences';
$labels['userpreferences'] = 'ഉപയൊക്ത മുന്‍ഗണനകള്‍';
$labels['editpreferences'] = 'Edit user preferences';

$labels['identities'] = 'Identities';
$labels['manageidentities'] = 'Manage identities for this account';
$labels['newidentity'] = 'New identity';

$labels['newitem'] = 'New item';
$labels['edititem'] = 'Edit item';

$labels['preferhtml'] = 'HTML കാണിക്കുക';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'HTML Message';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Pretty dates';
$labels['setdefault']  = 'Set default';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'ഭാഷ';
$labels['timezone']  = 'സമയ മേഖല';
$labels['pagesize']  = 'ഒരു താളിലെ വരികള്‍';
$labels['signature'] = 'ഒപ്പ്';
$labels['dstactive']  = 'Daylight saving time';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'HTML സന്ദേശങ്ങള്‍ രചിക്കുക';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML signature';
$labels['previewpane'] = 'Show preview pane';
$labels['skin'] = 'Interface skin';
$labels['logoutclear'] = 'Clear Trash on logout';
$labels['logoutcompact'] = 'Compact Inbox on logout';
$labels['uisettings'] = 'User Interface';
$labels['serversettings'] = 'സേവകന്റെ ക്രമീകരണങ്ങള്‍';
$labels['mailboxview'] = 'Mailbox View';
$labels['mdnrequests'] = 'On request for return receipt';
$labels['askuser'] = 'എന്നൊടു ചോദിക്കുക';
$labels['autosend'] = 'send receipt';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'അവഗണിക്കുക';
$labels['readwhendeleted'] = 'Mark the message as read on delete';
$labels['flagfordeletion'] = 'Flag the message for deletion instead of delete';
$labels['skipdeleted'] = 'Do not show deleted messages';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'Display remote inline images';
$labels['fromknownsenders'] = 'from known senders';
$labels['always'] = 'എപ്പോഴും';
$labels['showinlineimages'] = 'Display attached images below the message';
$labels['autosavedraft']  = 'Automatically save draft';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'ഒരിക്കലുമില്ല';
$labels['immediately']  = 'ഉടനടി';
$labels['messagesdisplaying'] = 'Displaying Messages';
$labels['messagescomposition'] = 'Composing Messages';
$labels['mimeparamfolding'] = 'Attachment names';
$labels['2231folding'] = 'Full RFC 2231 (തണ്ടര്‍ബേട് )';
$labels['miscfolding'] = 'Full RFC 2231 ( മൈക്രൊസൊഫ്റ്റ് ഔട്ട്ലൂക്കു് )';
$labels['2047folding'] = 'Full RFC 2047 (മറ്റുള്ളവ)';
$labels['force7bit'] = '8-bit അക്ഷരങ്ങള്‍ക്കു "MIME encoding" ഉപയോഗിക്കുക';
$labels['advancedoptions'] = 'വിപുലീക്രിതമായ ക്രമീകരണങ്ങള്‍';
$labels['focusonnewmessage'] = 'Focus browser window on new message';
$labels['checkallfolders'] = 'Check all folders for new messages';
$labels['displaynext'] = 'After message delete/move display the next message';
$labels['defaultfont'] = 'HTML സന്ദേശത്തിന്റെ തനതായ ലിപി';
$labels['mainoptions'] = 'Main Options';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'വിഭാഗം';
$labels['maintenance'] = 'Maintenance';
$labels['newmessage'] = 'പുതിയ സന്ദേശം';
$labels['signatureoptions'] = 'Signature Options';
$labels['whenreplying'] = 'When replying';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'start new message above the quote';
$labels['replybottomposting'] = 'start new message below the quote';
$labels['replyremovesignature'] = 'When replying remove original signature from message';
$labels['autoaddsignature'] = 'Automatically add signature';
$labels['newmessageonly'] = 'പുതിയ സന്ദേശം മാത്രം';
$labels['replyandforwardonly'] = 'replies and forwards only';
$labels['replysignaturepos'] = 'When replying or forwarding place signature';
$labels['belowquote'] = 'ഉദ്ധാരണത്തിനു താഴെ';
$labels['abovequote'] = 'above the quote';
$labels['insertsignature'] = 'Insert signature';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = '$n നിമിഷങ്ങള്‍ കഴിഞ്ഞു്';
$labels['reqmdn'] = 'Always request a return receipt';
$labels['reqdsn'] = 'Always request a delivery status notification';
$labels['replysamefolder'] = 'Place replies in the folder of the message being replied to';
$labels['defaultabook'] = 'Default address book';
$labels['autocompletesingle'] = 'Skip alternative email addresses in autocompletion';
$labels['listnamedisplay'] = 'List contacts as';
$labels['spellcheckbeforesend'] = 'Check spelling before sending a message';
$labels['spellcheckoptions'] = 'Spellcheck Options';
$labels['spellcheckignoresyms'] = 'Ignore words with symbols';
$labels['spellcheckignorenums'] = 'അക്കങ്ങള്‍ ഉള്ള വാക്കുകളെ അവഗണിക്കുക';
$labels['spellcheckignorecaps'] = 'Ignore words with all letters capitalized';
$labels['addtodict'] = 'നിഘണ്ടുവില്‍ ചേര്‍ക്കുക';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'Folder';
$labels['folders']  = 'ഫോള്‍ഡറുകള്‍';
$labels['foldername']  = 'Folder name';
$labels['subscribed']  = 'Subscribed';
$labels['messagecount'] = 'Messages';
$labels['create']  = 'Create';
$labels['createfolder']  = 'Create new folder';
$labels['managefolders']  = 'Manage folders';
$labels['specialfolders'] = 'Special Folders';
$labels['properties'] = 'സവിശേഷതകള്‍';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'Location';
$labels['info'] = 'വിവരം';
$labels['getfoldersize'] = 'അറയുടെ വലുപ്പം അരിയാന്‍ ക്ലിക്ക് ചെയ്യുക';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'സ്വകാര്യ അറ';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'പൊതു - അറ';

$labels['sortby'] = 'Sort by';
$labels['sortasc']  = 'Sort ascending';
$labels['sortdesc'] = 'Sort descending';
$labels['undo'] = 'Undo';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'സഹായം തേടുക';

// units
$labels['B'] = 'B';
$labels['KB'] = 'KB';
$labels['MB'] = 'MB';
$labels['GB'] = 'GB';

// character sets
$labels['unicode'] = 'യൂണിക്കോഡ്';
$labels['english'] = 'ഇംഗ്ലീഷ്';
$labels['westerneuropean'] = 'പടിഞ്ഞാറന്‍ യൂറോപ്യന്‍';
$labels['easterneuropean'] = 'കിഴക്കന്‍ യൂറോപ്യന്‍';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'ബാള്‍ട്ടിക്ക്';
$labels['cyrillic'] = 'സിറില്ലിക്ക്';
$labels['arabic'] = 'അറബിക്ക്';
$labels['greek'] = 'ഗ്രീക്ക്';
$labels['hebrew'] = 'ഹീബ്രു';
$labels['turkish'] = 'ടര്‍ക്കിഷ്';
$labels['nordic'] = 'നോര്‍ഡിക്ക്';
$labels['thai'] = 'തായ്';
$labels['celtic'] = 'സെല്‍റ്റിക്ക്';
$labels['vietnamese'] = 'വിയറ്റ്നാമീസ്';
$labels['japanese'] = 'ജാപ്പനീസ്';
$labels['korean'] = 'കൊറിയന്‍';
$labels['chinese'] = 'ചൈനീസ്‌';

?>
