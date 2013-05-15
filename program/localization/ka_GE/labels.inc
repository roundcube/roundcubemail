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
$labels['welcome']   = 'მოგესალმებათ $product';
$labels['username']  = 'მომხმარებელი';
$labels['password']  = 'პაროლი';
$labels['server']    = 'სერვერი';
$labels['login']     = 'შესვლა';

// taskbar
$labels['logout']   = 'გამოსვლა';
$labels['mail']     = 'ელფოსტა';
$labels['settings'] = 'პარამეტრები';
$labels['addressbook'] = 'მისამართები';

// mailbox names
$labels['inbox']  = 'მიღებული';
$labels['drafts'] = 'დროებითი';
$labels['sent']   = 'გაგზავნილი';
$labels['trash']  = 'წაშლილი';
$labels['junk']   = 'სპამი';

// message listing
$labels['subject'] = 'სათაური';
$labels['from']    = 'გამგზავნი';
$labels['sender']  = 'Sender';
$labels['to']      = 'ვის';
$labels['cc']      = 'ასლი';
$labels['bcc']     = 'ფარული';
$labels['replyto'] = 'პასუხი';
$labels['followupto'] = 'Followup-To';
$labels['date']    = 'თარიღი';
$labels['size']    = 'ზომა';
$labels['priority'] = 'პრიორიტეტი';
$labels['organization'] = 'ორგანიზაცია';
$labels['readstatus'] = 'Read status';
$labels['listoptions'] = 'List options...';

$labels['mailboxlist'] = 'საქაღალდეები';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['threadsfromto'] = 'Threads $from to $to of $count';
$labels['messagenrof'] = 'შეტყობინება $nr დან $count';
$labels['fromtoshort'] = '$from – $to of $count';

$labels['copy']     = 'ასლი';
$labels['move']     = 'გადატანა';
$labels['moveto']   = 'გადატანა...';
$labels['download'] = 'გადმოწერა';
$labels['open']     = 'Open';
$labels['showattachment'] = 'Show';
$labels['showanyway'] = 'Show it anyway';

$labels['filename'] = 'ფაილის სახელი';
$labels['filesize'] = 'ფაილის ზომა';

$labels['addtoaddressbook'] = 'მისამართებში დამატება';

// weekdays short
$labels['sun'] = 'კვი';
$labels['mon'] = 'ორშ';
$labels['tue'] = 'სამ';
$labels['wed'] = 'ოთხ';
$labels['thu'] = 'ხუთ';
$labels['fri'] = 'პარ';
$labels['sat'] = 'შაბ';

// weekdays long
$labels['sunday']    = 'კვირა';
$labels['monday']    = 'ორშაბათი';
$labels['tuesday']   = 'სამშაბათი';
$labels['wednesday'] = 'ოთხშაბათი';
$labels['thursday']  = 'ხუთშაბათი';
$labels['friday']    = 'პარასკევი';
$labels['saturday']  = 'შაბათი';

// months short
$labels['jan']	= 'იან';
$labels['feb']	= 'თებ';
$labels['mar']	= 'მარ';
$labels['apr']	= 'აპრ';
$labels['may']	= 'მაისი';
$labels['jun']	= 'ივნ';
$labels['jul'] 	= 'ივლ';
$labels['aug']	= 'აგვ';
$labels['sep']	= 'სექ';
$labels['oct']	= 'ოქტ';
$labels['nov']	= 'ნოე';
$labels['dec']	= 'დეკ';

// months long
$labels['longjan']	= 'იანვარი';
$labels['longfeb']	= 'თებერვალი';
$labels['longmar']	= 'მარტი';
$labels['longapr']	= 'აპრილი';
$labels['longmay']	= 'მაისი';
$labels['longjun']	= 'ივნისი';
$labels['longjul']	= 'ივლისი';
$labels['longaug']	= 'აგვისტო';
$labels['longsep']	= 'სექტემბერი';
$labels['longoct']	= 'ოქტომბერი';
$labels['longnov']	= 'ნოემბერი';
$labels['longdec']	= 'დეკემბერი';

$labels['today'] = 'დღეს';

// toolbar buttons
$labels['refresh']          = 'Refresh';
$labels['checkmail']        = 'შემოწმება ახალ შეტყობინებაზე';
$labels['compose']          = 'შეტყობინების დაწერა';
$labels['writenewmessage']  = 'ახალი შეტყობინება';
$labels['reply']            = 'Reply';
$labels['replytomessage']   = 'პასუხი';
$labels['replytoallmessage'] = 'პასუხი ყველას';
$labels['replyall']         = 'Reply all';
$labels['replylist']        = 'Reply list';
$labels['forward']          = 'Forward';
$labels['forwardinline']    = 'Forward inline';
$labels['forwardattachment'] = 'Forward as attachment';
$labels['forwardmessage']   = 'გადაგზავნა';
$labels['deletemessage']    = 'წაშლა';
$labels['movemessagetotrash'] = 'შეტყობინების წაშლილებში გადატანა';
$labels['printmessage']     = 'შეტყობინების ამობეჭვდა';
$labels['previousmessage']  = 'წინა შეტყობინების ნახვა';
$labels['firstmessage']     = 'პირველი შეტყობინების ნახვა';
$labels['nextmessage']      = 'შემდეგი შეტყობინების ნახვა';
$labels['lastmessage']      = 'ბოლო შეტყობინების ნახვა';
$labels['backtolist']       = 'უკან ჩამონათვალში';
$labels['viewsource']       = 'შიგთავსი';
$labels['mark']             = 'Mark';
$labels['markmessages']     = 'მონიშნეთ შეტყობინებები';
$labels['markread']         = 'როგორც წაკითხული';
$labels['markunread']       = 'როგორც წაუკითხავი';
$labels['markflagged']      = 'როგორც მონიშნული';
$labels['markunflagged']    = 'როგორც მოუნიშნავი';
$labels['moreactions']      = 'მეტი მოქმედება...';
$labels['more']             = 'More';
$labels['back']             = 'Back';
$labels['options']          = 'Options';

$labels['select'] = 'მონიშვნა';
$labels['all'] = 'ყველა';
$labels['none'] = 'არცერთი';
$labels['currpage'] = 'მიმდინარე გვერდი';
$labels['unread'] = 'წაუკითხავი';
$labels['flagged'] = 'მონიშნული';
$labels['unanswered'] = 'უპასუხო';
$labels['withattachment'] = 'With attachment';
$labels['deleted'] = 'წაშლილი';
$labels['undeleted'] = 'Not deleted';
$labels['invert'] = 'შებრუნებული';
$labels['filter'] = 'ფილტრი';
$labels['list'] = 'სია';
$labels['threads'] = 'Threads';
$labels['expand-all'] = 'ყველას გაშლა';
$labels['expand-unread'] = 'წაუკითხავის გაშლა';
$labels['collapse-all'] = 'ყველას დაკეცვა';
$labels['threaded'] = 'Threaded';

$labels['autoexpand_threads'] = 'Expand message threads';
$labels['do_expand'] = 'all threads';
$labels['expand_only_unread'] = 'only with unread messages';
$labels['fromto'] = 'ვისგან/ვის';
$labels['flag'] = 'დროშა';
$labels['attachment'] = 'მიმაგრებული ფაილი';
$labels['nonesort'] = 'არცერთი';
$labels['sentdate'] = 'გაგზავნის თარიღი';
$labels['arrival'] = 'Arrival date';
$labels['asc'] = 'ზრდადობით';
$labels['desc'] = 'კლებადობით';
$labels['listcolumns'] = 'List columns';
$labels['listsorting'] = 'Sorting column';
$labels['listorder'] = 'დალაგება';
$labels['listmode'] = 'List view mode';

$labels['folderactions'] = 'Folder actions...';
$labels['compact'] = 'შეკუმშვა';
$labels['empty'] = 'გაცარიელება';

$labels['quota'] = 'შეზღუდვა';
$labels['unknown']  = 'უცნობი';
$labels['unlimited']  = 'შეუზღუდავი';

$labels['quicksearch']  = 'სწრაფი ძიება';
$labels['resetsearch']  = 'ძიების გასუფთავება';
$labels['searchmod']  = 'ძებნის ვარიანტები';
$labels['msgtext']  = 'ყველა შეტყობინება';
$labels['body']  = 'Body';

$labels['openinextwin'] = 'გახსნა ახალ ფანჯარაში';
$labels['emlsave'] = 'გადმოწერა (.eml)';
$labels['changeformattext'] = 'Display in plain text format';
$labels['changeformathtml'] = 'Display in HTML format';

// message compose
$labels['editasnew']      = 'რედაქტირება როგორც ახლის';
$labels['send']           = 'Send';
$labels['sendmessage']    = 'შეტყობინების გაგზავნა';
$labels['savemessage']    = 'დროებით შენახვა';
$labels['addattachment']  = 'ფაილის ატვირთვა';
$labels['charset']        = 'კოდირება';
$labels['editortype']     = 'რედაქტორი';
$labels['returnreceipt']  = 'პასუხის მოთხოვნა';
$labels['dsn']            = 'Delivery status notification';
$labels['mailreplyintro'] = 'On $date, $sender wrote:';
$labels['originalmessage'] = 'Original Message';

$labels['editidents']    = 'Edit identities';
$labels['spellcheck']    = 'Spell';
$labels['checkspelling'] = 'ორთოგრაფიული შემოწმება';
$labels['resumeediting'] = 'რედაქტირების გაგრძელება';
$labels['revertto']      = 'გაუქმება';

$labels['attach'] = 'Attach';
$labels['attachments'] = 'გზავნილები';
$labels['upload'] = 'ატვირთვა';
$labels['uploadprogress'] = '$percent ($current from $total)';
$labels['close']  = 'დახურვა';
$labels['messageoptions']  = 'Message options...';

$labels['low']     = 'დაბალი';
$labels['lowest']  = 'უდაბლესი';
$labels['normal']  = 'საშუალო';
$labels['high']    = 'მაღალი';
$labels['highest'] = 'უმაღლესი';

$labels['nosubject']  = '(უსათაურო)';
$labels['showimages'] = 'გამოსახულების ჩვენება';
$labels['alwaysshow'] = 'ყოველთვის გამოაჩინე სურათები $sender –გან';
$labels['isdraft']    = 'This is a draft message.';
$labels['andnmore']   = '$nr more...';
$labels['togglemoreheaders'] = 'Show more message headers';
$labels['togglefullheaders'] = 'Toggle raw message headers';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'ტექსტური';
$labels['savesentmessagein'] = 'შეინახე შეტყობინება';
$labels['dontsave'] = 'არ შეინახო';
$labels['maxuploadsize'] = 'მაქსიმალური დაშვებული ფაილის ზომა $size';

$labels['addcc'] = 'დაამატე კოპიო';
$labels['addbcc'] = 'დაამატე ფარული კოპიო';
$labels['addreplyto'] = 'დაამატე გამომგზავნი';
$labels['addfollowupto'] = 'Add Followup-To';

// mdn
$labels['mdnrequest'] = 'გამომგზავნს სურს შეიტყოს წერილის მიღება. გსურთ შეატყობინოთ გამომგზავნს?';
$labels['receiptread'] = 'გზავნილის დაბრუნება';
$labels['yourmessage'] = 'ეს არის მიღების დასტური თქვენს შეტყობინებაზე';
$labels['receiptnote'] = 'განმარტება: ეს ნიშნავს რომ ადრესატმა მიიღო შეტყობინება. მაგრამ, არ ნიშნავს რომ წაიკითხა ან გაიგო ტექსტი';

// address boook
$labels['name']         = 'სრული სახელი';
$labels['firstname']    = 'სახელი';
$labels['surname']      = 'გვარი';
$labels['middlename']   = 'Middle Name';
$labels['nameprefix']   = 'Prefix';
$labels['namesuffix']   = 'Suffix';
$labels['nickname']     = 'Nickname';
$labels['jobtitle']     = 'Job Title';
$labels['department']   = 'დეპარტამენტი';
$labels['gender']       = 'სქესი';
$labels['maidenname']   = 'Maiden Name';
$labels['email']        = 'ელ–ფოსტა';
$labels['phone']        = 'ტელეფონი';
$labels['address']      = 'მისამართი';
$labels['street']       = 'ქუჩა';
$labels['locality']     = 'ქალაქი';
$labels['zipcode']      = 'ZIP კოდი';
$labels['region']       = 'შტატი/პროვინცია';
$labels['country']      = 'ქვეყანა';
$labels['birthday']     = 'დაბადების დღე';
$labels['anniversary']  = 'Anniversary';
$labels['website']      = 'ვებგვერდი';
$labels['instantmessenger'] = 'IM';
$labels['notes'] = 'Notes';
$labels['male']   = 'მამრობითი';
$labels['female'] = 'მდედრობით';
$labels['manager'] = 'მენეჯერი';
$labels['assistant'] = 'Assistant';
$labels['spouse'] = 'Spouse';
$labels['allfields'] = 'ყველა ველი';
$labels['search'] = 'ძებნა';
$labels['advsearch'] = 'გაფართოებული ძებნა';
$labels['advanced'] = 'Advanced';
$labels['other'] = 'სხვა';

$labels['typehome']   = 'სახლი';
$labels['typework']   = 'სამსახური';
$labels['typeother']  = 'სხვა';
$labels['typemobile']  = 'მობილური';
$labels['typemain']  = 'ძირითადი';
$labels['typehomefax']  = 'სახლის ფაქსი';
$labels['typeworkfax']  = 'სამსახურის ფაქსი';
$labels['typecar']  = 'მანქანა';
$labels['typepager']  = 'პეიჯერი';
$labels['typevideo']  = 'ვიდეო';
$labels['typeassistant']  = 'Assistant';
$labels['typehomepage']  = 'Home Page';
$labels['typeblog'] = 'ბლოგი';
$labels['typeprofile'] = 'პროფილი';

$labels['addfield'] = 'ველის დამატება...';
$labels['addcontact'] = 'კონტაქტებში დამატება';
$labels['editcontact'] = 'კონტაქტის რედაქტირება';
$labels['contacts'] = 'კონტაქტები';
$labels['contactproperties'] = 'Contact properties';
$labels['personalinfo'] = 'პირადი ინფორმაცია';

$labels['edit']   = 'რედაქტირება';
$labels['cancel'] = 'გაუქმება';
$labels['save']   = 'შენახვა';
$labels['delete'] = 'წაშლა';
$labels['rename'] = 'გადარქმევა';
$labels['addphoto'] = 'დამატება';
$labels['replacephoto'] = 'გამოცვლა';
$labels['uploadphoto'] = 'Upload photo';

$labels['newcontact']     = 'კონტაქტის შექმნა';
$labels['deletecontact']  = 'მონიშნელი კონტაქტის წაშლა';
$labels['composeto']      = 'ფოსტის გაგზავნა შერჩეულ ადრესატებთან';
$labels['contactsfromto'] = 'კონტაქტები $from დან $to ში $count';
$labels['print']          = 'დაბეჭვდა';
$labels['export']         = 'ექსპორტი';
$labels['exportall']      = 'Export all';
$labels['exportsel']      = 'Export selected';
$labels['exportvcards']   = 'კონტაქტების ექსპორტი vCard ფორმატში';
$labels['newcontactgroup'] = 'ახალი საკონტაქტო ჯგუფის შექმნა';
$labels['grouprename']    = 'ჯგუფის სახელის შეცვლა';
$labels['groupdelete']    = 'ჯგუფის წაშლა';
$labels['groupremoveselected'] = 'Remove selected contacts from group';

$labels['previouspage']   = 'წინას ჩვენება';
$labels['firstpage']      = 'პირველის ჩვენება';
$labels['nextpage']       = 'შემდეგის ჩვენება';
$labels['lastpage']       = 'ბოლოს ჩვენება';

$labels['group'] = 'ჯგუფი';
$labels['groups'] = 'ჯგუფები';
$labels['personaladrbook'] = 'პერსონალური მისამართები';

$labels['searchsave'] = 'ძებნის შენახვა';
$labels['searchdelete'] = 'ძებნის წაშლა';

$labels['import'] = 'შემოტანა';
$labels['importcontacts'] = 'კონტაქტების შემოტანა';
$labels['importfromfile'] = 'შემოტანა ფაილიდან:';
$labels['importtarget'] = 'Add new contacts to address book:';
$labels['importreplace'] = 'ჩაანაცვლეთ არსებული მისამართები';
$labels['importdesc'] = 'You can upload contacts from an existing address book.<br/>We currently support importing addresses from the <a href="http://en.wikipedia.org/wiki/VCard">vCard</a> or CSV (comma-separated) data format.';
$labels['done'] = 'დასრულებულია';

// settings
$labels['settingsfor'] = 'პარამეტრები';
$labels['about'] = 'About';
$labels['preferences'] = 'პარამეტრები';
$labels['userpreferences'] = 'მომხმარებლის პარამეტრები';
$labels['editpreferences'] = 'მომხმარებლის პარამეტრების რედაქტირება';

$labels['identities'] = 'პროფილები';
$labels['manageidentities'] = 'პროფილების მართვა';
$labels['newidentity'] = 'ახალი პროფილი';

$labels['newitem'] = 'ახალი';
$labels['edititem'] = 'რედაქტირება';

$labels['preferhtml'] = 'როგორც HTML';
$labels['defaultcharset'] = 'ნაგულისხმები სიმბოლოთა ნაკრები';
$labels['htmlmessage'] = 'HTML შეტყობინება';
$labels['messagepart'] = 'Part';
$labels['digitalsig'] = 'Digital Signature';
$labels['dateformat'] = 'თარიღის ფორმატი';
$labels['timeformat'] = 'დროის ფორმატი';
$labels['prettydate'] = 'კარგი თარიღები';
$labels['setdefault']  = 'როგორც ზოგაგად';
$labels['autodetect']  = 'ავტომატური';
$labels['language']  = 'ენა';
$labels['timezone']  = 'დროითი ზონა';
$labels['pagesize']  = 'სტრიქონი გვერდზე';
$labels['signature'] = 'ხელმოწერა';
$labels['dstactive']  = 'დროის გადაყვანა';
$labels['showinextwin'] = 'Open message in a new window';
$labels['composeextwin'] = 'Compose in a new window';
$labels['htmleditor'] = 'შეტყობინების შექმნა HTML –ში';
$labels['htmlonreply'] = 'on reply to HTML message';
$labels['htmlonreplyandforward'] = 'on forward or reply to HTML message';
$labels['htmlsignature'] = 'HTML ხელმოწერა';
$labels['showemail'] = 'Show email address with display name';
$labels['previewpane'] = 'ჩვენება';
$labels['skin'] = 'ინტერფეისი';
$labels['logoutclear'] = 'წაშლილების გასუფთავება გამოსვლისას';
$labels['logoutcompact'] = 'მიღებულების შეკუმშვა გამოსვლისას';
$labels['uisettings'] = 'მომხმარებლის ინტერფეისი';
$labels['serversettings'] = 'სერვერის პარამეტრები';
$labels['mailboxview'] = 'საფოსტო ყუთის ჩვენება';
$labels['mdnrequests'] = 'მგზავნელის შეტყობინებები';
$labels['askuser'] = 'ჰკითხე მომხმარებელს';
$labels['autosend'] = 'გაგზავნე ავტომატურად';
$labels['autosendknown'] = 'send receipt to my contacts, otherwise ask me';
$labels['autosendknownignore'] = 'send receipt to my contacts, otherwise ignore';
$labels['ignore'] = 'იგნორირება';
$labels['readwhendeleted'] = 'მონიშნე გზავნილი წაკითხულად წაშლის დროს';
$labels['flagfordeletion'] = 'აღნიშნეთ გზავნილები როგორც წასაშლელი წაშლის ნაცვლად';
$labels['skipdeleted'] = 'არ გამოაჩინო წაშლილი გზავნილები';
$labels['deletealways'] = 'If moving messages to Trash fails, delete them';
$labels['deletejunk'] = 'Directly delete messages in Junk';
$labels['showremoteimages'] = 'გამოაჩინე მოშორებული სურათები';
$labels['fromknownsenders'] = 'ნაცნობი მგზავნელებისგან';
$labels['always'] = 'ყოველთვის';
$labels['showinlineimages'] = 'ატვირთული სურათები გამოაჩინე გზავნილის ქვემოთ';
$labels['autosavedraft']  = 'ავტომატური შენახვა';
$labels['everynminutes']  = 'ყოველ $n წუთში';
$labels['refreshinterval']  = 'Refresh (check for new messages, etc.)';
$labels['never']  = 'არასდროს';
$labels['immediately']  = 'დაუყონებლივ';
$labels['messagesdisplaying'] = 'გამოაჩინე შეტყობინებები';
$labels['messagescomposition'] = 'შეტყობინებების შექმნა';
$labels['mimeparamfolding'] = 'ბმულის სახელები';
$labels['2231folding'] = 'Full RFC 2231 (Thunderbird)';
$labels['miscfolding'] = 'RFC 2047/2231 (MS Outlook)';
$labels['2047folding'] = 'Full RFC 2047 (other)';
$labels['force7bit'] = 'MIME კოდირების გამოყენება 8 ბიტიანი სიმბოლოებისთვის';
$labels['advancedoptions'] = 'დამატებითი პარამეტრები';
$labels['focusonnewmessage'] = 'ფოკუსირება ახალ შეტყობინებებზე';
$labels['checkallfolders'] = 'შეამოწმე ყველა საქაღალდე ახალ შეტყობინებაზე';
$labels['displaynext'] = 'შეტყობინების წაშლის ან გადატანის შემდეგ გამოჩნდეს შემდეგი შეტყობინება';
$labels['defaultfont'] = 'Default font of HTML message';
$labels['mainoptions'] = 'ძირითადი პარამეტრები';
$labels['browseroptions'] = 'Browser Options';
$labels['section'] = 'განყოფილება';
$labels['maintenance'] = 'მომსახურება';
$labels['newmessage'] = 'ახალი შეტყობინება';
$labels['signatureoptions'] = 'ხელმოწერის ოფციები';
$labels['whenreplying'] = 'პასუხის შემთხვევაში';
$labels['replyempty'] = 'do not quote the original message';
$labels['replytopposting'] = 'დაიწყოს ახალი შეტყობინება ორიგინალის ზემოთ';
$labels['replybottomposting'] = 'დაიწყოს ახალი შეტყობინება ორიგინალის ქვემოთ';
$labels['replyremovesignature'] = 'წაიშალოს ხელმოწერა პასუხის შემთხვევაში';
$labels['autoaddsignature'] = 'ავტომატურად დაემატოს ხელმოწერა';
$labels['newmessageonly'] = 'მხოლოდ ახალი შეტყობინება';
$labels['replyandforwardonly'] = 'მხოლოდ გადაგზავნის და პასუხის შემთხვევაში';
$labels['insertsignature'] = 'ხელმოცერის ჩასმა';
$labels['previewpanemarkread']  = 'Mark previewed messages as read';
$labels['afternseconds']  = '$n წამის შემდეგ';
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
$labels['addtodict'] = 'ლექსიკონში დამატება';
$labels['mailtoprotohandler'] = 'Register protocol handler for mailto: links';
$labels['forwardmode'] = 'Messages forwarding';
$labels['inline'] = 'inline';
$labels['asattachment'] = 'as attachment';

$labels['folder']  = 'საქაღალდე';
$labels['folders']  = 'საქაღალდეები';
$labels['foldername']  = 'საქაღალდის დასახელება';
$labels['subscribed']  = 'გამოწერილია';
$labels['messagecount'] = 'შეტყობინებები';
$labels['create']  = 'შექმნა';
$labels['createfolder']  = 'საქაღალდის შექმნა';
$labels['managefolders']  = 'საქაღალდეების მართვა';
$labels['specialfolders'] = 'სპეციალური საქაღალდე';
$labels['properties'] = 'Properties';
$labels['folderproperties'] = 'Folder properties';
$labels['parentfolder'] = 'Parent folder';
$labels['location'] = 'მდებარეობა';
$labels['info'] = 'ინფორმაცია';
$labels['getfoldersize'] = 'Click to get folder size';
$labels['changesubscription'] = 'Click to change subscription';
$labels['foldertype'] = 'დასტის ტიპი';
$labels['personalfolder']  = 'კერძო დასტა';
$labels['otherfolder']  = 'Other User\'s Folder';
$labels['sharedfolder']  = 'საჯარო დასტა';

$labels['sortby'] = 'დალაგება';
$labels['sortasc']  = 'დალაგება ზრდადობით';
$labels['sortdesc'] = 'დალაგება კლებადობით';
$labels['undo'] = 'დაბრუნება';

$labels['installedplugins'] = 'Installed plugins';
$labels['plugin'] = 'Plugin';
$labels['version'] = 'Version';
$labels['source'] = 'Source';
$labels['license'] = 'License';
$labels['support'] = 'Get support';

// units
$labels['B'] = 'ბტ';
$labels['KB'] = 'კბტ';
$labels['MB'] = 'მბტ';
$labels['GB'] = 'გბტ';

// character sets
$labels['unicode'] = 'უნიკოდი';
$labels['english'] = 'ინსგლისური';
$labels['westerneuropean'] = 'დასავლურევროპული';
$labels['easterneuropean'] = 'აღმოსავლეთევროპული';
$labels['southeasterneuropean'] = 'South-Eastern European';
$labels['baltic'] = 'ბალტიური';
$labels['cyrillic'] = 'კირილიცა';
$labels['arabic'] = 'არაბული';
$labels['greek'] = 'ბერძნული';
$labels['hebrew'] = 'ებრაული';
$labels['turkish'] = 'თურქული';
$labels['nordic'] = 'Nordic';
$labels['thai'] = 'ტაი';
$labels['celtic'] = 'კელტური';
$labels['vietnamese'] = 'ვიეტნამური';
$labels['japanese'] = 'იაპონური';
$labels['korean'] = 'კორეული';
$labels['chinese'] = 'ჩინური';

?>
