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
$labels['welcome']   = '$product សូមស្វាគមន៍';
$labels['username']  = 'ឈ្មោះអ្នកប្រើ';
$labels['password']  = 'ពាក្យសំងាត់';
$labels['server']    = 'ម៉ាស៊ីនមេ';
$labels['login']     = 'ពិនិត្យចូល';

// taskbar
$labels['logout']   = 'ពិនិត្យចេញ';
$labels['mail']     = 'អ៊ីមែល';
$labels['settings'] = 'កំនត់ការប្រើប្រាស់';
$labels['addressbook'] = 'សៀវភៅអាសយដ្ឋាន';

// mailbox names
$labels['inbox']  = 'ប្រអប់សំបុត្រ';
$labels['drafts'] = 'សំបុត្រពង្រៀង';
$labels['sent']   = 'សំបុត្រដែលបានបញ្ជូន';
$labels['trash']  = 'ធុងសំរាម';
$labels['junk']   = 'សំបុត្រមិនល្អ';

// message listing
$labels['subject'] = 'ចំណងជើង';
$labels['from']    = 'អ្នកផ្ញើ';
$labels['sender']  = 'Sender';
$labels['to']      = 'អ្នកទទួល';
$labels['cc']      = 'Cc';
$labels['bcc']     = 'Bcc';
$labels['replyto'] = 'ឆ្លើយតបទៅកាន់';
$labels['followupto'] = 'ឆ្លើយតបទៅកាន់ក្រុម';
$labels['date']    = 'កាលបរិច្ឆេត';
$labels['size']    = 'ទំហំ';
$labels['priority'] = 'អទិភាព';
$labels['organization'] = 'អង្គភាព';
$labels['readstatus'] = 'ស្ថានភាពនៃការអាន';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'ថតទាំងអស់';
$labels['messagesfromto'] = 'សំបុត្រពី $from ទៅដល់ $toក្នុងចំណោម $count សំបុត្រ';
$labels['threadsfromto'] = 'បន្តុំសំបុត្រពី $from ទៅដល់ $toក្នុងចំណោម $count សំបុត្រ';
$labels['messagenrof'] = 'សំបុត្រ $nr នៃ $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'ចំលង';
$labels['move']     = 'ផ្ទេរ';
$labels['moveto']   = 'ផ្ទេរទៅ';
$labels['download'] = 'ទាញយក';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'ឈ្មោះឯកសារ';
$labels['filesize'] = 'ទំហំឯកសារ';

$labels['addtoaddressbook'] = 'រក្សាទុកក្នុងសៀវភៅអាសយដ្ឋាន';

// weekdays short
$labels['sun'] = 'អាទិត្យ';
$labels['mon'] = 'ច័ន្ទ';
$labels['tue'] = 'អង្គារ';
$labels['wed'] = 'ពុធ';
$labels['thu'] = 'ព្រហស្បតិ៍';
$labels['fri'] = 'សុក្រ';
$labels['sat'] = 'សៅរ៍';

// weekdays long
$labels['sunday']    = 'អាទិត្យ';
$labels['monday']    = 'ច័ន្ទ';
$labels['tuesday']   = 'អង្គារ';
$labels['wednesday'] = 'ពុធ';
$labels['thursday']  = 'ព្រហស្បតិ៍';
$labels['friday']    = 'សុក្រ';
$labels['saturday']  = 'សៅរ៍';

// months short
$labels['jan']	= 'មករា';
$labels['feb']	= 'កុម្ភះ';
$labels['mar']	= 'មិនា';
$labels['apr']	= 'មេសា';
$labels['may']	= 'ឧសភា';
$labels['jun']	= 'មិថុនា';
$labels['jul'] 	= 'កក្កដា';
$labels['aug']	= 'សីហា';
$labels['sep']	= 'កញ្ញា';
$labels['oct']	= 'តុលា';
$labels['nov']	= 'វិច្ឆិកា';
$labels['dec']	= 'ធ្នូ';

// months long
$labels['longjan']	= 'មករា';
$labels['longfeb']	= 'កុម្ភះ';
$labels['longmar']	= 'មិនា';
$labels['longapr']	= 'មេសា';
$labels['longmay']	= 'ឧសភា';
$labels['longjun']	= 'មិថុនា';
$labels['longjul']	= 'កក្កដា';
$labels['longaug']	= 'សីហា';
$labels['longsep']	= 'កញ្ញា';
$labels['longoct']	= 'តុលា';
$labels['longnov']	= 'វិច្ឆិកា';
$labels['longdec']	= 'ធ្នូ';

$labels['today'] = 'ថ្ងៃនេះ';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'ពិនិត្យសំបុត្រថ្មី';
$labels['compose']          = 'សរសេរសំបុត្រថ្មី';
$labels['writenewmessage']  = 'សរសេរសំបុត្រថ្មី';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'ឆ្លើយតបទៅអ្នកផ្ញើ';
$labels['replytoallmessage'] = 'ឆ្លើយតបទៅ អ្នកទទួលទាំងអស់និង ក្រុម ឬ អ្នកផ្ញើ';
$labels['replyall']         = 'ឆ្លើយតបទៅទាំងអស់គ្នា';
$labels['replylist']        = 'ឆ្លើយតបទៅក្រុម';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'ផ្ញើសំបុត្របន្តរ';
$labels['deletemessage']    = 'លុបសំបុត្រចោល';
$labels['movemessagetotrash'] = 'ផ្ទេរសំបុត្រទៅក្នុងធុងសំរាម';
$labels['printmessage']     = 'បោះពុម្ភសំបុត្រ';
$labels['previousmessage']  = 'បង្ហាញសំបុត្រចាស់';
$labels['firstmessage']     = 'បង្ហាញសំបុត្រដំបូង';
$labels['nextmessage']      = 'បង្ហាញសំបុត្របន្ទាប់';
$labels['lastmessage']      = 'បង្ហាញសំបុត្រចុងក្រោយ';
$labels['backtolist']       = 'ត្រលប់ទៅកាន់ក្រុមសំបុត្រ';
$labels['viewsource']       = 'បង្ហាញ Source របស់សំបុត្រ';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'កំណត់សំគាល់សំបុត្រ';
$labels['markread']         = 'កំណត់ថាបានអានរួច';
$labels['markunread']       = 'កំណត់ថាមិនទាន់បានអាន';
$labels['markflagged']      = 'កំណត់សំគាល់ដោយផ្កាយ';
$labels['markunflagged']    = 'លុបកំណត់សំគាល់ដោយផ្កាយ';
$labels['moreactions']      = 'More actions...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'ជ្រើសរើស';
$labels['all'] = 'ទាំងអស់';
$labels['none'] = 'មិនតំរៀប';
$labels['currpage'] = 'ទំព័រនេះ';
$labels['unread'] = 'មិនទាន់អាន';
$labels['flagged'] = 'មានកំណត់សំគាល់ដោយផ្កាយ';
$labels['unanswered'] = 'មិនទាន់ឆ្លើយតប';
$labels['deleted'] = 'បានលុបរួច';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'បញ្ច្រស់';
$labels['filter'] = 'លក្ខខ័ណ្ឌ';
$labels['list'] = 'តារាង';
$labels['threads'] = 'បណ្តុំសំបុត្រ';
$labels['expand-all'] = 'បង្ហាញសំបុត្រទាំងអស់';
$labels['expand-unread'] = 'បង្ហាញសំបុត្រមិនទាន់អាន';
$labels['collapse-all'] = 'បិទសំបុត្រទាំងអស់';
$labels['threaded'] = 'បានបែកចែកជាក្រុមរួច';

$labels['autoexpand_threads'] = 'បង្ហាញបណ្តុំសំបុត្រ';
$labels['do_expand'] = 'បណ្តុំទាំងអស់';
$labels['expand_only_unread'] = 'បង្ហាញតែសំបុត្រដែលមិនទាន់អាន';
$labels['fromto'] = 'អ្នកផ្ញើ / អ្នកទទួល';
$labels['flag'] = 'កំណត់សំគាល់ដោយផ្កាយ';
$labels['attachment'] = 'ឯកសារភ្ជាប់';
$labels['nonesort'] = 'មិនតំរៀប';
$labels['sentdate'] = 'កាលបរិច្ឆេតផ្ញើ';
$labels['arrival'] = 'កាលបរិច្ឆេតទទួល';
$labels['asc'] = 'តាមលំដាប់ចុះ';
$labels['desc'] = 'តាមលំដាប់ឡើង';
$labels['listcolumns'] = 'តារាងជួរឈ';
$labels['listsorting'] = 'តំរៀបជួរឈ';
$labels['listorder'] = 'លំដាប់នៃការតំរៀប';
$labels['listmode'] = 'បង្ហាញជាតារាង';

$labels['folderactions'] = 'មុខងារថត';
$labels['compact'] = 'បង្រួម';
$labels['empty'] = 'ទទេរ';

$labels['quota'] = 'ទំហំសំបុត្រទាំងអស់ដែលមាន';
$labels['unknown']  = 'មិនស្គាល់';
$labels['unlimited']  = 'គ្មានកំនត់';

$labels['quicksearch']  = 'ស្វែងរក';
$labels['resetsearch']  = 'កំណត់លក្ខខ័ណ្ឌស្វែងរកជាថ្មី';
$labels['searchmod']  = 'កែសំរួលលក្ខខ័ណ្ឌស្វែងរក';
$labels['msgtext']  = 'សំបុត្រទាំងមូល';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'បើកក្នុងវីនដូវថ្មី';
$labels['emlsave'] = 'រក្សាទុកទិន្នន័យជាឯកសារប្រភេទ(.eml)';

// message compose
$labels['editasnew']      = 'កែហើយរក្សាទុកដូចជាសំបុត្រថ្មី';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'ផ្ញើសំបុត្រនេះ';
$labels['savemessage']    = 'រក្សាទុកសំបុត្រពង្រៀង';
$labels['addattachment']  = 'ឯកសារភ្ជាប់';
$labels['charset']        = 'ប្រភេទអក្សរ';
$labels['editortype']     = 'ប្រភេទ Editor';
$labels['returnreceipt']  = 'អត្ថបទបញ្ជាក់ពីការត្រឡប់';
$labels['dsn']            = 'ប្រាប់អំពីស្ថានភាពផ្ញើ';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'ផ្លាស់ប្តូរអត្តសញ្ញាណ';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'ពិនិត្យអក្ខរាវិរុទ្ធ';
$labels['resumeediting'] = 'បន្តរការកែតំរូវ';
$labels['revertto']      = 'ត្រលប់ទៅ';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'ឯកសារភ្ជាប់';
$labels['upload'] = 'ភ្ជាប់ឯកសារ';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'បិទ';
$labels['messageoptions']  = 'កំណត់ជំរើសរបស់សំបុត្រ';

$labels['low']     = 'ទាប';
$labels['lowest']  = 'ទាបបំផុត';
$labels['normal']  = 'ធម្មតា';
$labels['high']    = 'ខ្ពស់';
$labels['highest'] = 'ខ្ពស់បំផុត';

$labels['nosubject']  = '(គ្មានចំណងជើង)';
$labels['showimages'] = 'បង្ហាញរូបភាព';
$labels['alwaysshow'] = 'បង្ហាញជានិច្ចរាល់រូបភាពបញ្ជូនពី$sender';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'អត្ថបទ HTML';
$labels['plaintoggle'] = 'អត្ថបទធម្មតា';
$labels['savesentmessagein'] = 'រក្សាសំបុត្រដែលបានផ្ញើក្នុង';
$labels['dontsave'] = 'មិនរក្សាទុក';
$labels['maxuploadsize'] = 'ទំហំឯកសារធំបំផុតដែលអាចភ្ជាប់បានគឺ$size';

$labels['addcc'] = 'បន្ថែម Cc';
$labels['addbcc'] = 'បន្ថែម Bcc';
$labels['addreplyto'] = 'បន្ថែម Reply-To';
$labels['addfollowupto'] = 'បន្ថែម Followup-To';

// mdn
$labels['mdnrequest'] = 'The sender of this message has asked to be notified when you read this message. Do you wish to notify the sender?';
$labels['receiptread'] = 'អត្ថបទផ្ញើត្រលប់ (បានអាន)';
$labels['yourmessage'] = 'នេះគឺជាអត្ថបទផ្ញើត្រលប់សំរាប់សំបុត្ររបស់លោកអ្នក។';
$labels['receiptnote'] = 'Note: This receipt only acknowledges that the message was displayed on the recipient\'s computer. There is no guarantee that the recipient has read or understood the message contents.';

// address boook
$labels['name']         = 'ឈ្មោះសំរាប់បង្ហាញ';
$labels['firstname']    = 'នាមត្រកូល';
$labels['surname']      = 'នាមខ្លួន';
$labels['middlename']   = 'ឈ្មោះកណ្តាល';
$labels['nameprefix']   = 'ពាក្យពីមុខឈ្មោះ';
$labels['namesuffix']   = 'ពាក្យពីក្រោយឈ្មោះ';
$labels['nickname']     = 'ឈ្មោះហៅក្រៅ';
$labels['jobtitle']     = 'ឈ្មោះការងារ';
$labels['department']   = 'នាយកដ្ឋាន';
$labels['gender']       = 'ភេទ';
$labels['maidenname']   = 'ឈ្មោះពីកំណើត';
$labels['email']        = 'អ៊ីមែល';
$labels['phone']        = 'ទូរស័ព្ទ';
$labels['address']      = 'អាសយដ្ឋាន';
$labels['street']       = 'ផ្លូវ';
$labels['locality']     = 'ទីក្រុង';
$labels['zipcode']      = 'លេខប៉ុស្តិ៍';
$labels['region']       = 'តំបន់';
$labels['country']      = 'ប្រទេស';
$labels['birthday']     = 'ថ្ងៃខួបកំណើត';
$labels['anniversary']  = 'ខួប';
$labels['website']      = 'គេហទំព័រ';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'កំណត់ហេតុ';
$labels['male']   = 'ប្រុស';
$labels['female'] = 'ស្រី';
$labels['manager'] = 'នាយក';
$labels['assistant'] = 'ជំនួយការ';
$labels['spouse'] = 'គ្រួសារ';
$labels['allfields'] = 'All fields';
$labels['search'] = 'Search';
$labels['advsearch'] = 'Advanced Search';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'ផ្សេងពីនេះ';

$labels['typehome']   = 'គេហដ្ឋាន';
$labels['typework']   = 'កន្លែងធ្វើការ';
$labels['typeother']  = 'ផ្សេងពីនេះ';
$labels['typemobile']  = 'ទូរស័ព្ទដៃ';
$labels['typemain']  = 'ចំបង';
$labels['typehomefax']  = 'ទូរសារនៅគេហដ្ឋាន';
$labels['typeworkfax']  = 'ទូរសារនៅកន្លែងធ្វើការ';
$labels['typecar']  = 'រថយន្ត';
$labels['typepager']  = 'ឧបករណ៍ផ្តល់សារ';
$labels['typevideo']  = 'វីដេអូរ';
$labels['typeassistant']  = 'ជំនួយការ';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'Blog';
$labels['typeprofile'] = 'Profile';

$labels['addfield'] = 'បន្ថែម';
$labels['addcontact'] = 'បន្ថែមអាសយដ្ឋានទាក់ទង';
$labels['editcontact'] = 'កែប្រែអាសយដ្ឋានទាក់ទង';
$labels['contacts'] = 'អាសយដ្ឋានទាក់ទងទាំងអស់';
$labels['contactproperties'] = 'ព័ត៌មានអំពីទំនាក់ទំនង';
$labels['personalinfo'] = 'ព័ត៌មានផ្ទាល់ខ្លួន';

$labels['edit']   = 'កែតំរូវ';
$labels['cancel'] = 'លះបង់';
$labels['save']   = 'រក្សាទុក';
$labels['delete'] = 'លុបចោល';
$labels['rename'] = 'ប្តូរឈ្មោះ';
$labels['addphoto'] = 'បន្ថែមរូបថត';
$labels['replacephoto'] = 'លុបរូបថត';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'បង្កើតអាសយដ្ឋានទាក់ទងថ្មី';
$labels['deletecontact']  = 'លុបអាសយដ្ឋានទាក់ទងដែលបានជ្រើសរើស';
$labels['composeto']      = 'ផ្ញើសំបុត្រទៅ';
$labels['contactsfromto'] = 'អាសយដ្ឋានទាក់ទង $from ដល់ $to នៃ$count';
$labels['print']          = 'បោះពុម្ភ';
$labels['export']         = 'ទាញនិងរក្សាទុក';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'ទាញនិងរក្សាទុកអាសយដ្ឋានជាប្រភេទvCard';
$labels['newcontactgroup'] = 'បង្កើតក្រុមអាសយដ្ឋានថ្មី';
$labels['grouprename']    = 'Rename group';
$labels['groupdelete']    = 'Delete group';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'ទំព័រមុន';
$labels['firstpage']      = 'ទំព័រដំបូង';
$labels['nextpage']       = 'ទំព័របន្ទាប់';
$labels['lastpage']       = 'ទំព័រចុងក្រោយ';

$labels['group'] = 'ក្រុម';
$labels['groups'] = 'ក្រុម';
$labels['personaladrbook'] = 'អាសយដ្ឋានផ្ទាល់ខ្លួន';

$labels['searchsave'] = 'Save search';
$labels['searchdelete'] = 'Delete search';

$labels['import'] = 'បញ្ចូល';
$labels['importcontacts'] = 'បញ្ចូលអាសយដ្ឋាន';
$labels['importfromfile'] = 'បញ្ចូលពី ឯកសារ';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'ផ្លាស់ប្តូរអាសយដ្ឋានទាំងអស់';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'ចប់រួចរាល់';

// settings
$labels['settingsfor'] = 'កំណត់សំរាប់';
$labels['about'] = 'About';
$labels['preferences'] = 'កំណត់តាមការចូលចិត្ត';
$labels['userpreferences'] = 'កំណត់តាមការចូលចិត្តរបស់អ្នកប្រើ';
$labels['editpreferences'] = 'ផ្លាស់ប្តូរការចូលចិត្តរបស់អ្នកប្រើ';

$labels['identities'] = 'អត្តសញ្ញាណ';
$labels['manageidentities'] = 'គ្រប់គ្រងអត្តសញ្ញាណ';
$labels['newidentity'] = 'អត្តសញ្ញាណថ្មី';

$labels['newitem'] = 'វត្ថុថ្មី';
$labels['edititem'] = 'កែប្រែវត្ថុ';

$labels['preferhtml'] = 'បង្ហាញជា HTML';
$labels['defaultcharset'] = 'លេខកូតអក្សរមានស្រាប់';
$labels['htmlmessage'] = 'សំបុត្រប្រភេទ HTML';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'Date format';
$labels['timeformat'] = 'Time format';
$labels['prettydate'] = 'កាលបរិច្ឆេតងាយស្រួលមើល';
$labels['setdefault']  = 'កំណត់ទុក';
$labels['autodetect']  = 'ស្វ័យប្រវត្តិ';
$labels['language']  = 'ភាសា';
$labels['timezone']  = 'ម៉ោង';
$labels['pagesize']  = 'ចំនួនសំបុត្រក្នុងមួយទំព័រ';
$labels['signature'] = 'ហត្ថលេខា';
$labels['dstactive']  = 'ម៉ោងវស្សានរដូវ';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'សរសេរសំបុត្រតាមបែប HTML';
$labels['htmlonreply'] = 'ឆ្លើយតបសំបុត្រតាមបែប HTML';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'ហត្ថលេខាតាមបែប HTML';
$labels['previewpane'] = 'បង្ហាញប្រអប់សំរាប់មើលសំបុត្រ';
$labels['skin'] = 'ការរចនាទំព័រ';
$labels['logoutclear'] = 'សំអាតធុងសំរាមពេលពិនិត្យចេញ';
$labels['logoutcompact'] = 'បង្រួមប្រអប់សំបុត្រពេលពិនិត្យចេញ';
$labels['uisettings'] = 'ការរចនា';
$labels['serversettings'] = 'ការកំណត់ម៉ាស៊ីនមេ';
$labels['mailboxview'] = 'មើលប្រអប់សំបុត្រ';
$labels['mdnrequests'] = 'ប្រាប់អ្នកផ្ញើ';
$labels['askuser'] = 'សួរអ្នកប្រើ';
$labels['autosend'] = 'ផ្ញើស្វ័យប្រវត្តិ';
$labels['autosendknown'] = 'បញ្ជូនអត្ថបទប្រាប់អ្នកផ្ញើទៅកាន់អាសយដ្ឋានទាក់ទងរបស់ខ្ញុំឬក៏សួរខ្ញុំ';
$labels['autosendknownignore'] = 'បញ្ជូនអត្ថបទប្រាប់អ្នកផ្ញើទៅកាន់អាសយដ្ឋានទាក់ទងរបស់ខ្ញុំឬមិនបាច់ធ្វើអ្វីទាំងអស់';
$labels['ignore'] = 'ទុកចោល';
$labels['readwhendeleted'] = 'កំណត់សំបុត្រថាបានអាននៅពេលលុបសំបុត្រនោះ';
$labels['flagfordeletion'] = 'កំណត់សញ្ញាផ្កាយអោយសំបុត្រថាបានលុបដោយមិនចាំបាច់លុបជាក់ស្តែង';
$labels['skipdeleted'] = 'មិនបង្ហាញសំបុត្រដែលបានលុប';
$labels['deletealways'] = 'លុបសំបុត្រចោលប្រសិនជាមិនអាចផ្ទេសំបុត្រនោះទៅធុងសំរាមបាន';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'បង្ហាញរូបភាពដែលគេផ្ញើមក';
$labels['fromknownsenders'] = 'ពីអ្នកផ្ញើដែលស្គាល់';
$labels['always'] = 'គ្រប់ពេល';
$labels['showinlineimages'] = 'បង្ហាញរូបភាពនៅក្រោមអត្ថបទសំបុត្រ';
$labels['autosavedraft']  = 'រក្សាទុកសំបុត្រពង្រៀងដោយស្វ័យប្រវត្តិ';
$labels['everynminutes']  = 'រៀងរាល់ $n នាទី';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'មិនដែល';
$labels['immediately']  = 'ភ្លាមៗ';
$labels['messagesdisplaying'] = 'កំពុងបង្ហាញសំបុត្រ';
$labels['messagescomposition'] = 'កំពុងសរសេរសំបុត្រ';
$labels['mimeparamfolding'] = 'ឈ្មោះឯកសារភ្ជាប់';
$labels['2231folding'] = 'RFC 2231 ពេញ (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'RFC 2047 ពេញ(other)';
$labels['force7bit'] = 'ប្រើ MIME សំរាប់អក្សរទំហំ 8-bit';
$labels['advancedoptions'] = 'ការកំណត់ពឹស្តារ';
$labels['focusonnewmessage'] = 'ផ្តោតលើវីនដូវដែលកំពុងសរសេរសំបុត្រថ្មី';
$labels['checkallfolders'] = 'ពិនិត្យសំបុត្រថ្មីនៅគ្រប់ថតទាំងអស់';
$labels['displaynext'] = 'បន្ទាប់ពីសំបុត្រត្រូវបានលុប​ឬផ្ទេរបង្ហាញសំបុត្របន្ទាប់';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'ជំរើសដែលជាគោល';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'ផ្នែក';
$labels['maintenance'] = 'ការថែរក្សា';
$labels['newmessage'] = 'សំបុត្រថ្មី';
$labels['signatureoptions'] = 'កំណត់ហត្ថលេខា';
$labels['whenreplying'] = 'នៅពេលកំពុងឆ្លើយតប';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'ចាប់ផ្តើម​សរសេរ​ពី​លើ​សំបុត្រ​ដើម';
$labels['replybottomposting'] = 'ចាប់ផ្តើមសរសេរពីក្រោមសំបុត្រដើម';
$labels['replyremovesignature'] = 'នៅ​ពេល​ឆ្លើយ​តប​លុប​ហត្ថលេខា​ចេញ​ពីសំបុត្រ';
$labels['autoaddsignature'] = 'បញ្ចូលហត្តលេខាដោយស្វ័យប្រវត្តិ';
$labels['newmessageonly'] = 'សំរាប់សំបុត្រថ្មីតែប៉ុណ្ណោះ';
$labels['replyandforwardonly'] = 'សំរាប់ឆើ្លយតប​និងផ្ញើបន្តតែ​ប៉ុណ្ណោះ';
$labels['replysignaturepos'] = 'បញ្ចូលហត្តលេខានៅពេលឆើ្លយតប​និងផ្ញើបន្ត';
$labels['belowquote'] = 'ពីក្រោមសម្រង់អត្ថបទ';
$labels['abovequote'] = 'ពីលើសម្រង់អត្ថបទ';
$labels['insertsignature'] = 'បញ្ចូលហត្តលេខា';
$labels['previewpanemarkread']  = 'កំណត់សំបុត្រ​ដែល​បាន​បង្ហាញ​​ជា​សំបុត្របាន​អាន​រួច';
$labels['afternseconds']  = 'ក្រោយពី $n វិនាទី';
$labels['reqmdn'] = 'ក្រោយពី $n វិនាទី';
$labels['reqdsn'] = 'ក្រោយពី $n វិនាទី';
$labels['replysamefolder'] = 'ក្រោយពី $n វិនាទី';
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

$labels['folder']  = 'ថត';
$labels['folders']  = 'ថតទាំងអស់';
$labels['foldername']  = 'ឈ្មោះថត';
$labels['subscribed']  = 'កំណត់យកមកប្រើ';
$labels['messagecount'] = 'សំបុត្រ';
$labels['create']  = 'បង្កើត';
$labels['createfolder']  = 'បង្កើតថតថ្មី';
$labels['managefolders']  = 'រៀបចំថត';
$labels['specialfolders'] = 'ថតពិសេស';
$labels['properties'] = 'ព័ត៌មាន';
$labels['folderproperties'] = 'ព័ត៌មានអំពីថត';
$labels['parentfolder'] = 'ថតមេ';
$labels['location'] = 'ទីកន្លែង';
$labels['info'] = 'ព័ត៌មាន';
$labels['getfoldersize'] = 'ទំហំថត';
$labels['changesubscription'] = 'ផ្លាស់ប្តូរការជាវ';
$labels['foldertype'] = 'Folder Type';
$labels['personalfolder']  = 'Private Folder';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'Public Folder';

$labels['sortby'] = 'តំរៀបតាម';
$labels['sortasc']  = 'តំរៀបតាមលំដាប់ចុះ';
$labels['sortdesc'] = 'តំរៀបតាមលំដាប់ឡើង';
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
$labels['unicode'] = 'យូនីកូដ';
$labels['english'] = 'ភាសាអង់គ្លេស';
$labels['westerneuropean'] = 'អឺរ៉ុបខាងលិច';
$labels['easterneuropean'] = 'អីរ៉ុបខាងកើត';
$labels['southeasterneuropean'] = 'អឺរ៉ុបភាគអាគ្នេយ៍';
$labels['baltic'] = 'ភាសាបាល់តិក';
$labels['cyrillic'] = 'ភាសាស៊ីរីលិក';
$labels['arabic'] = 'ភាសាអារ៉ាប់';
$labels['greek'] = 'ភាសាក្រិក';
$labels['hebrew'] = 'ភាសាហ៊ីបប្រ៊ូ';
$labels['turkish'] = 'ភាសាទួរគី';
$labels['nordic'] = 'ភាសាណរឌីក';
$labels['thai'] = 'ភាសាថៃ';
$labels['celtic'] = 'ភាសាសេលតិច';
$labels['vietnamese'] = 'ភាសាវៀតណាម';
$labels['japanese'] = 'ភាសាជប៉ុន';
$labels['korean'] = 'ភាសាកូរ៉េ';
$labels['chinese'] = 'ភាសាចិន';

?>
