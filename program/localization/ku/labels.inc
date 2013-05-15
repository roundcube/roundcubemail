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
$labels['welcome']   = 'Bixêr hatî $product \'ê';
$labels['username']  = 'Bikarhêner';
$labels['password']  = 'Nasnav';
$labels['server']    = 'Pêşkêşkar';
$labels['login']     = 'Têkevê';

// taskbar
$labels['logout']   = 'Derkeve';
$labels['mail']     = 'E-Peyam';
$labels['settings'] = 'Mîhengên Takekesî';
$labels['addressbook'] = 'Lênûska Navnîşanan';

// mailbox names
$labels['inbox']  = 'Hatî';
$labels['drafts'] = 'Hilanînî';
$labels['sent']   = 'Şandî';
$labels['trash']  = 'Çop';
$labels['junk']   = 'Biikêrnehatî';

// message listing
$labels['subject'] = 'Mijar';
$labels['from']    = 'Şandyar';
$labels['sender']  = 'Sender';
$labels['to']      = 'Standyar';
$labels['cc']      = 'Kopiya Karbon';
$labels['bcc']     = 'Kopiya karbon a Veşartî';
$labels['replyto'] = 'Navnîşanên Standyar';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'Dîrok';
$labels['size']    = 'Mezinahî';
$labels['priority'] = 'Girîngî';
$labels['organization'] = 'Sazî';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'Peldank';
$labels['messagesfromto'] = 'Peyam $count ên di navbera $from - $to';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = '$nr a peyama $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'Kopiya Karbon';
$labels['move']     = 'Move';
$labels['moveto']   = 'bibe...';
$labels['download'] = 'daxe';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'Navê pelê';
$labels['filesize'] = 'Mezinahiya pelê';

$labels['addtoaddressbook'] = 'Têxe lênûska navnîşanan';

// weekdays short
$labels['sun'] = 'Yş';
$labels['mon'] = 'Dş';
$labels['tue'] = 'Sş';
$labels['wed'] = 'Çş';
$labels['thu'] = 'Pş';
$labels['fri'] = 'În';
$labels['sat'] = 'Şe';

// weekdays long
$labels['sunday']    = 'Yekşem';
$labels['monday']    = 'Duşem';
$labels['tuesday']   = 'Sêşem';
$labels['wednesday'] = 'Çarşem';
$labels['thursday']  = 'Pêncşem';
$labels['friday']    = 'În';
$labels['saturday']  = 'Şemî';

// months short
$labels['jan']	= 'Jan';
$labels['feb']	= 'Feb';
$labels['mar']	= 'Mar';
$labels['apr']	= 'Apr';
$labels['may']	= 'May';
$labels['jun']	= 'Jun';
$labels['jul'] 	= 'Jul';
$labels['aug']	= 'Aug';
$labels['sep']	= 'Sep';
$labels['oct']	= 'Oct';
$labels['nov']	= 'Nov';
$labels['dec']	= 'Dec';

// months long
$labels['longjan']	= 'January';
$labels['longfeb']	= 'February';
$labels['longmar']	= 'March';
$labels['longapr']	= 'April';
$labels['longmay']	= 'May';
$labels['longjun']	= 'June';
$labels['longjul']	= 'July';
$labels['longaug']	= 'August';
$labels['longsep']	= 'September';
$labels['longoct']	= 'October';
$labels['longnov']	= 'November';
$labels['longdec']	= 'December';

$labels['today'] = 'Îro';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'Li peyamên nû venihêre';
$labels['compose']          = 'Peyamekê biafirîne';
$labels['writenewmessage']  = 'Peyameke nû biafirîne';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'Bersivê bide peyamê';
$labels['replytoallmessage'] = 'Bersivê ji bo hemû kesên ku ev peyan standine bişîne';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'Bersivê bide peyamê';
$labels['deletemessage']    = 'Peyamê jê bibe';
$labels['movemessagetotrash'] = 'Peyamê dake çopê';
$labels['printmessage']     = 'Vê peyamê çap bike';
$labels['previousmessage']  = 'Peyama berê nîşan bide';
$labels['firstmessage']     = 'Peyama yekemîn nîşan bide';
$labels['nextmessage']      = 'Peyama di rêzê de nîşan bide';
$labels['lastmessage']      = 'Peyama dawî nîşan bide';
$labels['backtolist']       = 'Vegere lîsteya peyaman';
$labels['viewsource']       = 'Çavkaniyê nîşan bide';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'Peyamê nîşan bike';
$labels['markread']         = 'Wekî xwendî';
$labels['markunread']       = 'Wekî nexwendî';
$labels['markflagged']      = 'As flagged';
$labels['markunflagged']    = 'As unflagged';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'Hilbijêre';
$labels['all'] = 'Hemû';
$labels['none'] = 'Ne yek jî';
$labels['currpage'] = 'Current page';
$labels['unread'] = 'Nexwendî';
$labels['flagged'] = 'Flagged';
$labels['unanswered'] = 'Unanswered';
$labels['withattachment'] = 'With attachment';
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
$labels['nonesort'] = 'Ne yek jî';
$labels['sentdate'] = 'Sent date';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ascending';
$labels['desc'] = 'descending';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'Sorting order';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'Kompakt';
$labels['empty'] = 'Vala Bike';

$labels['quota'] = 'Bikaranîna dîskê';
$labels['unknown']  = 'nayê zanîn';
$labels['unlimited']  = 'bêsînor';

$labels['quicksearch']  = 'Lêgerîna bilez';
$labels['resetsearch']  = 'Lêgerînê reset bike';
$labels['searchmod']  = 'Search modifiers';
$labels['msgtext']  = 'Entire message';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'Open in new window';
$labels['emlsave'] = 'Download (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'Edit as new';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'Peyamê niha bişîne';
$labels['savemessage']    = 'Hilîne';
$labels['addattachment']  = 'Pelekê lê zêde bike';
$labels['charset']        = 'Curetîp';
$labels['editortype']     = 'Cureyê serastkat';
$labels['returnreceipt']  = 'Rapora Vegerînê';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'Kontrola rastnivîsê';
$labels['resumeediting'] = 'Serastkirinê bidomîne';
$labels['revertto']      = 'Şûnde bîne';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'Pêvek';
$labels['upload'] = 'Bar bike';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'Bigire';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'Nizm';
$labels['lowest']  = 'Nizmtirîn';
$labels['normal']  = 'Asayî';
$labels['high']    = 'Bilind';
$labels['highest'] = 'Bilintirîn';

$labels['nosubject']  = '(bê mijar)';
$labels['showimages'] = 'Xuyakirina wêneyan';
$labels['alwaysshow'] = 'Always show images from $sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'Deqa tazî';
$labels['savesentmessagein'] = 'Save sent message in';
$labels['dontsave'] = 'don\'t save';
$labels['maxuploadsize'] = 'Maximum allowed file size is $size';

$labels['addcc'] = 'Cc Têxê';
$labels['addbcc'] = 'Bcc Têxê';
$labels['addreplyto'] = 'Bibersivîne têxê';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'Şandyara/ê vê peyamê xwest dema ku te peyam vekir, jê re hişyarî here. Dixwazî jê re hişyarî here?';
$labels['receiptread'] = 'Rapora Vegerînê (xwendî)';
$labels['yourmessage'] = 'Ev rapora vegerîna peyama te ye';
$labels['receiptnote'] = 'Nîşe: Ev dide zanîn ka peyama te şand hat xwendin an na. Naveroka peyamê bi mîgogerî nayê zanîn.';

// address boook
$labels['name']         = 'Navê xuya dibe';
$labels['firstname']    = 'Nav';
$labels['surname']      = 'Paşnav';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'Department';
$labels['gender']       = 'Gender';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'E-Peyam';
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
$labels['addcontact'] = 'Têkiliyeke nû têxê';
$labels['editcontact'] = 'Têkiliyê serast bike';
$labels['contacts'] = 'Contacts';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'Personal information';

$labels['edit']   = 'Serast bike';
$labels['cancel'] = 'Betal';
$labels['save']   = 'Tomar bike';
$labels['delete'] = 'Jê Bibe';
$labels['rename'] = 'Rename';
$labels['addphoto'] = 'Add';
$labels['replacephoto'] = 'Replace';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'kardeke nû a têkiliyê biafirîne';
$labels['deletecontact']  = 'Têkiliyên hilbijartî jê bibe';
$labels['composeto']      = 'Ji têkiliya hilbijartî re peyam bişîne';
$labels['contactsfromto'] = 'Têkiliyên $count ên di navbera $from - $to de';
$labels['print']          = 'çap bike';
$labels['export']         = 'Derxe';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'Export contacts in vCard format';
$labels['newcontactgroup'] = 'Create new contact group';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'Seta berê nîşan bide';
$labels['firstpage']      = 'Seta yekemîn nîşan bide';
$labels['nextpage']       = 'Seta di rêzê de nîşan bide';
$labels['lastpage']       = 'Seta dawî nîşan bide';

$labels['group'] = 'Group';
$labels['groups'] = 'Kom';
$labels['personaladrbook'] = 'Navnîşanên Takekesî';

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
$labels['settingsfor'] = 'Mîheng ji bo';
$labels['about'] = 'About';
$labels['preferences'] = 'Vebijêrk';
$labels['userpreferences'] = 'Vebijêrkên bikarhêner';
$labels['editpreferences'] = 'Vebijêrkên bikarhêner serast bike';

$labels['identities'] = 'Nasname';
$labels['manageidentities'] = 'Nasnameyên vê hesabê serast bike';
$labels['newidentity'] = 'Nasnameya nû';

$labels['newitem'] = 'Parçeya nû';
$labels['edititem'] = 'Parçeyê serast bike';

$labels['preferhtml'] = 'Wekî HTML nîşan bide';
$labels['defaultcharset'] = 'Default Character Set';
$labels['htmlmessage'] = 'Peyama HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'Kurtenivîsa Dîrokê';
$labels['setdefault']  = 'Wekî standard tomar bike';
$labels['autodetect']  = 'Auto';
$labels['language']  = 'Ziman';
$labels['timezone']  = 'Herêma demê';
$labels['pagesize']  = 'Hejmara peyamên her rûpelekê';
$labels['signature'] = 'Şanenav';
$labels['dstactive']  = 'Demjimêra havînî';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'Peyameke HTML biafirîne';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'Şanenava HTML';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'Panela berê nîşan bide';
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
$labels['autosavedraft']  = 'Xweber hilîne';
$labels['everynminutes']  = 'every $n minute(s)';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'qet';
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

$labels['folder']  = 'Peldank';
$labels['folders']  = 'Peldank';
$labels['foldername']  = 'Navê peldankê';
$labels['subscribed']  = 'Ni şanenav';
$labels['messagecount'] = 'Peyam';
$labels['create']  = 'Biafirîne';
$labels['createfolder']  = 'Peldankeke nû biafirîne';
$labels['managefolders']  = 'Rêveberiya peldankan';
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

$labels['sortby'] = 'Şêweyê rêzkirinê';
$labels['sortasc']  = 'Wekî zêde dibe';
$labels['sortdesc'] = 'Wekî kêm dibe';
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
