<?php

/*

 +-----------------------------------------------------------------------+
 | language/ge/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Zaza Zviadadze <zviadadze@gmail.com>                          |
 +-----------------------------------------------------------------------+

 @version $Id: labels.inc 842 2007-10-20 13:55:02Z  zaza$

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
$labels['mail']     = 'ელ–ფოსტა';
$labels['settings'] = 'პარამეტრები';
$labels['addressbook'] = 'მისამართები';

// mailbox names
$labels['inbox']  = 'მიღებული';
$labels['drafts'] = 'დროებითი';
$labels['sent']   = 'გაგზავნილები';
$labels['trash']  = 'წაშლილები';
$labels['junk']   = 'სპამი';

// message listing
$labels['subject'] = 'სათაური';
$labels['from']    = 'გამგზავნი';
$labels['to']      = 'მიმღები';
$labels['cc']      = 'კოპია';
$labels['bcc']     = 'ფარული';
$labels['replyto'] = 'დაბრუნება';
$labels['date']    = 'თარიღი';
$labels['size']    = 'ზომა';
$labels['priority'] = 'პრიორიტეტი';
$labels['organization'] = 'ორგანიზაცია';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'საქაღალდეები';
$labels['messagesfromto'] = 'შეტყობინება $from  $to დან $count';
$labels['messagenrof'] = 'შეტყობინება $nr დან $count';

$labels['moveto']   = 'გადატანა...';
$labels['download'] = 'გადმოწერა';

$labels['filename'] = 'ფაილის სახელი';
$labels['filesize'] = 'ფაილის ზომა';

$labels['preferhtml'] = 'როგორც HTML';
$labels['htmlmessage'] = 'HTML შეტყობინება';
$labels['prettydate'] = 'კარგი თარიღები';

$labels['addtoaddressbook'] = 'მისამართებში დამატება';

// weekdays short
$labels['sun'] = 'კვ';
$labels['mon'] = 'ორ';
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

$labels['today'] = 'დღეს';

// toolbar buttons
$labels['checkmail']        = 'შემოწმება ახალ შეტყობინებაზე';
$labels['writenewmessage']  = 'ახალი შეტყობინება';
$labels['replytomessage']   = 'პასუხი';
$labels['replytoallmessage'] = 'პასუხი ყველას';
$labels['forwardmessage']   = 'გადაგზავნა';
$labels['deletemessage']    = 'წაშლა';
$labels['movemessagetotrash'] = 'შეტყობინების წაშლილებში გადატანა';
$labels['printmessage']     = 'შეტყობინების ამობეჭვდა';
$labels['previousmessage']  = 'წინა შეტყობინების ნახვა';
$labels['previousmessages'] = 'წინა შეტყობინებების ნახვა';
$labels['firstmessage']     = 'პირველი შეტყობინების ნახვა';
$labels['firstmessages']    = 'პრველი შეტყობინებები';
$labels['nextmessage']      = 'შემდეგი შეტყობინების ნახვა';
$labels['nextmessages']     = 'შემდეგი შეტყობინებების ნახვა';
$labels['lastmessage']      = 'ბოლო შეტყობინების ნახვა';
$labels['lastmessages']     = 'ბოლო შეტყობინებების ნახვა';
$labels['backtolist']       = 'უკან ჩამონათვალში';
$labels['viewsource']       = 'შიგთავსი';

$labels['select'] = 'მონიშვნა';
$labels['all'] = 'ყველა';
$labels['none'] = 'არცერთი';
$labels['unread'] = 'წაუკითხავი';

$labels['compact'] = 'შეკუმშვა';
$labels['empty'] = 'გაცარიელება';
$labels['purge'] = 'გასუფთავება';

$labels['quota'] = 'შეზღუდვა';
$labels['unknown']  = 'უცნობი';
$labels['unlimited']  = 'შეუზღუდავი';

$labels['quicksearch']  = 'სწრაფი ძიება';
$labels['resetsearch']  = 'ძიების გასუფთავება';


// message compose
$labels['compose']        = 'შეტყობინების დაწერა';
$labels['sendmessage']    = 'შეტყობინების გაგზავნა';
$labels['savemessage']  = 'დროებით შენახვა';
$labels['addattachment']  = 'ფაილის ატვირთვა';
$labels['charset']        = 'კოდირება';
$labels['editortype']     = 'რედაქტორი';
$labels['returnreceipt']  = 'პასუხის მოთხოვნა';

$labels['checkspelling'] = 'ორთოგრაფიული შემოწმება';
$labels['resumeediting'] = 'რედაქტირების გაგრძელება';
$labels['revertto']      = 'გაუქმება';

$labels['attachments'] = 'გზავნილები';
$labels['upload'] = 'ატვირთვა';
$labels['close']  = 'დახურვა';

$labels['low']     = 'დაბალი';
$labels['lowest']  = 'უდაბლესი';
$labels['normal']  = 'საშუალო';
$labels['high']    = 'მაღალი';
$labels['highest'] = 'უმაღლესი';

$labels['nosubject']  = '(უსათაურო)';
$labels['showimages'] = 'გამოსახულების ჩვენება';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'ტექსტური';

$labels['addcc'] = 'დაამატე კოპიო';
$labels['addbcc'] = 'დაამატე ფარული კოპიო';
$labels['addreplyto'] = 'დაამატე გამომგზავნი';


// address boook
$labels['name']      = 'სრული სახელი';
$labels['firstname'] = 'სახელი';
$labels['surname']   = 'გვარი';
$labels['email']     = 'ელ–ფოსტა';

$labels['addcontact'] = 'კონტაქტებში დამატება';
$labels['editcontact'] = 'კონტაქტის რედაქტირება';

$labels['edit']   = 'რედაქტირება';
$labels['cancel'] = 'გაუქმება';
$labels['save']   = 'შენახვა';
$labels['delete'] = 'წაშლა';

$labels['newcontact']     = 'კონტაქტის შექმნა';
$labels['deletecontact']  = 'მონიშნელი კონტაქტის წაშლა';
$labels['composeto']      = 'ფოსტის გაგზავნა შერჩეულ ადრესატებთან';
$labels['contactsfromto'] = 'კონტაქტები $from დან $to ში $count';
$labels['print']          = 'დაბეჭვდა';
$labels['export']         = 'ექსპორტი';

$labels['previouspage']   = 'წინას ჩვენება';
$labels['firstpage']      = 'პირველის ჩვენება';
$labels['nextpage']       = 'შემდეგის ჩვენება';
$labels['lastpage']       = 'ბოლოს ჩვენება';

$labels['groups'] = 'ჯგუფები';
$labels['personaladrbook'] = 'პერსონალური მისამართები';


// settings
$labels['settingsfor']  = 'პარამეტრები';

$labels['preferences']  = 'პარამეტრები';
$labels['userpreferences']  = 'მომხმარებლის პარამეტრები';
$labels['editpreferences']  = 'მომხმარებლის პარამეტრების რედაქტირება';

$labels['identities']  = 'პროფილები';
$labels['manageidentities']  = 'პროფილების მართვა';
$labels['newidentity']  = 'ახალი პროფილი';

$labels['newitem']  = 'ახალი';
$labels['edititem']  = 'რედაქტირება';

$labels['setdefault']  = 'როგორც ზოგაგად';
$labels['language']  = 'ენა';
$labels['timezone']  = 'დროითი ზონა';
$labels['pagesize']  = 'სტრიქონი გვერდზე';
$labels['signature'] = 'ხელმოწერა';
$labels['dstactive']  = 'დროის გადაყვანა';
$labels['htmleditor'] = 'შეტყობინების შექმნა HTML –ში';
$labels['htmlsignature'] = 'HTML ხელმოწერა';
$labels['previewpane'] = 'ჩვენება';

$labels['autosavedraft']  = 'ავტომატური შენახვა';
$labels['everynminutes']  = 'ყოველ $n წუთში';
$labels['never']  = 'არასდროს';

$labels['folder']  = 'საქაღალდე';
$labels['folders']  = 'საქაღალდეები';
$labels['foldername']  = 'საქაღალდის დასახელება';
$labels['subscribed']  = 'გამოცერილია';
$labels['create']  = 'შექმნა';
$labels['createfolder']  = 'საქაღალდის შექმნა';
$labels['rename'] = 'სახელის გადარქმევა';
$labels['renamefolder'] = 'სახელის გადარქმევა საქაღალდისთვის';
$labels['deletefolder']  = 'საქაღალდის წაშლა';
$labels['managefolders']  = 'საქაღალდეების მართვა';

$labels['sortby'] = 'დალაგება';
$labels['sortasc']  = 'დალაგება ზრდადობით';
$labels['sortdesc'] = 'დალაგება კლებადობით';

?>
