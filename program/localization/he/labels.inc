<?php

/*

 +-----------------------------------------------------------------------+
 | language/he/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2007, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Shimi <roundcube@shimi.net>                                   |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'ברוכים הבאים ל-$product';
$labels['username']  = 'שם משתמש';
$labels['password']  = 'סיסמא';
$labels['server']    = 'שרת';
$labels['login']     = 'התחבר';

// taskbar
$labels['logout']   = 'התנתק';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'הגדרות אישיות';
$labels['addressbook'] = 'ספר כתובות';

// mailbox names
$labels['inbox']  = 'דואר נכנס';
$labels['drafts'] = 'טיוטות';
$labels['sent']   = 'נשלח';
$labels['trash']  = 'סל מחזור';
$labels['junk']   = 'דואר זבל';

// message listing
$labels['subject'] = 'נושא';
$labels['from']    = 'שולח';
$labels['to']      = 'נמען';
$labels['cc']      = 'העתק';
$labels['bcc']     = 'העתק חבוי';
$labels['replyto'] = 'הגב ל';
$labels['date']    = 'תאריך';
$labels['size']    = 'גודל';
$labels['priority'] = 'דחיפות';
$labels['organization'] = 'ארגון';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'תיקיות';
$labels['messagesfromto'] = 'הודעות מ$from אל $to מתוך $count';
$labels['messagenrof'] = 'הודעה $nr מתוך $count';

$labels['moveto']   = 'העבר ל...';
$labels['download'] = 'הורד';

$labels['filename'] = 'שם קובץ';
$labels['filesize'] = 'גודל קובץ';

$labels['preferhtml'] = 'הצג HTML';
$labels['htmlmessage'] = 'הודעת HTML';
$labels['prettydate'] = 'תאריכים יפים';

$labels['addtoaddressbook'] = 'הוסף לספר  הכתובות';

// weekdays short
$labels['sun'] = 'א\'';
$labels['mon'] = 'ב\'';
$labels['tue'] = 'ג\'';
$labels['wed'] = 'ד\'';
$labels['thu'] = 'ה\'';
$labels['fri'] = 'ו\'';
$labels['sat'] = 'ז\'';

// weekdays long
$labels['sunday']    = 'ראשון';
$labels['monday']    = 'שני';
$labels['tuesday']   = 'שלישי';
$labels['wednesday'] = 'רביעי';
$labels['thursday']  = 'חמישי';
$labels['friday']    = 'שישי';
$labels['saturday']  = 'שבת';

$labels['today'] = 'היום';

// toolbar buttons
$labels['checkmail']        = 'בדוק אם הגיעו הודעות חדשות';
$labels['writenewmessage']  = 'כתוב הודעה חדשה';
$labels['replytomessage']   = 'הגב להודעה';
$labels['replytoallmessage'] = 'הגב לשולח ולכל הנמענים';
$labels['forwardmessage']   = 'העבר הודעה זו';
$labels['deletemessage']    = 'מחק הודעה';
$labels['movemessagetotrash'] = 'העבר הודעה לסל המחזור';
$labels['printmessage']     = 'הדפס הודעה זו';
$labels['previousmessage']  = 'הצג את ההודעה הקודמת';
$labels['previousmessages'] = 'הצג את סט ההודעות הקודמות';
$labels['firstmessage']     = 'הצג את ההודעה הראשונה';
$labels['firstmessages']    = 'הצג את סט ההודעות הראשונות';
$labels['nextmessage']      = 'הצג את ההודעה הבאה';
$labels['nextmessages']     = 'הצג את סט ההודעות הבאות';
$labels['lastmessage']      = 'הצג את ההודעה האחרונה';
$labels['lastmessages']     = 'הצג את סט ההודעות האחרונות';
$labels['backtolist']       = 'חזור לרשימת ההודעות';
$labels['viewsource']       = 'הצג מקור';

$labels['select'] = 'בחר';
$labels['all'] = 'הכל';
$labels['none'] = 'כלום';
$labels['unread'] = 'לא נקראו';

$labels['compact'] = 'הקטן נפח';
$labels['empty'] = 'רוקן';
$labels['purge'] = 'הסר לחלוטין';

$labels['quota'] = 'שימוש בדיסק';
$labels['unknown']  = 'לא ידוע';
$labels['unlimited']  = 'לא מוגבל';

$labels['quicksearch']  = 'חיפוש מהיר';
$labels['resetsearch']  = 'אפס חיפוש';


// message compose
$labels['compose']        = 'צור הודעה';
$labels['savemessage']    = 'שמור טיוטא זו';
$labels['sendmessage']    = 'שלח את ההודעה עכשיו';
$labels['addattachment']  = 'צרף קובץ';
$labels['charset']        = 'קידוד';
$labels['editortype']     = 'סוג עורך';
$labels['returnreceipt']  = 'אישור מסירה';

$labels['checkspelling'] = 'בדוק איות';
$labels['resumeediting'] = 'המשך בעריכה';
$labels['revertto']      = 'חזור אל';

$labels['attachments'] = 'קבצים מצורפים';
$labels['upload'] = 'העלה';
$labels['close']  = 'סגור';

$labels['low']     = 'נמוכה';
$labels['lowest']  = 'הכי נמוכה';
$labels['normal']  = 'רגילה';
$labels['high']    = 'גבוהה';
$labels['highest'] = 'הכי גבוהה';

$labels['nosubject']  = '(ללא נושא)';
$labels['showimages'] = 'הצג תמונות';

$labels['htmltoggle'] = 'HTML';
$labels['plaintoggle'] = 'טקסט פשוט';

$labels['addcc'] = 'הוסף העתק (Cc)';
$labels['addbcc'] = 'הוסף העתק חבוי (Bcc)';
$labels['addreplyto'] = 'הוסף תגובה-אל (Reply-To)';


// address boook
$labels['name']      = 'שם תצוגה';
$labels['firstname'] = 'שם פרטי';
$labels['surname']   = 'שם משפחה';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'הוסף איש קשר חדש';
$labels['editcontact'] = 'ערוך איש קשר';

$labels['edit']   = 'ערוך';
$labels['cancel'] = 'בטל';
$labels['save']   = 'שמור';
$labels['delete'] = 'מחק';

$labels['newcontact']     = 'צור כרטיס איש קשר חדש';
$labels['deletecontact']  = 'מחק את אנשי הקשר שנבחרו';
$labels['composeto']      = 'צור הודעה חדשה אל';
$labels['contactsfromto'] = 'אנשי קשר $from עד $to מתוך $count';
$labels['print']          = 'הדפס';
$labels['export']         = 'ייצא';

$labels['previouspage']   = 'הצג סט קודם';
$labels['firstpage']      = 'הצג סט ראשון';
$labels['nextpage']       = 'הצג את הסט הבא';
$labels['lastpage']       = 'הצג את הסט האחרון';

$labels['groups'] = 'קבוצות';
$labels['personaladrbook'] = 'ספר כתובות';


// settings
$labels['settingsfor']  = 'הגדרות עבור';

$labels['preferences']  = 'העדפות';
$labels['userpreferences']  = 'העדפות משתמש';
$labels['editpreferences']  = 'ערוך העדפות משתמש';

$labels['identities']  = 'זהויות';
$labels['manageidentities']  = 'ערוך זהויות עבור חשבון זה';
$labels['newidentity']  = 'זהות חדשה';

$labels['newitem']  = 'ערך חדש';
$labels['edititem']  = 'ערך ערך';

$labels['setdefault']  = 'קבע ברירת מחדל';
$labels['language']  = 'שפה';
$labels['timezone']  = 'איזור זמן';
$labels['pagesize']  = 'שורות בדף';
$labels['signature'] = 'חתימה';
$labels['dstactive']  = 'שעון קיץ';
$labels['htmleditor'] = 'צור הודעות HTML';
$labels['htmlsignature'] = 'חתימת HTML';
$labels['previewpane'] = 'הצג חלונית תצוגה מקדימה';

$labels['autosavedraft']  = 'שמור טיוטות באופן אוטומטי';
$labels['everynminutes']  = 'כל $n דקות';
$labels['never']  = 'לעולם';

$labels['folder']  = 'תיקיה';
$labels['folders']  = 'תיקיות';
$labels['foldername']  = 'שם תיקיה';
$labels['subscribed']  = 'רשום';
$labels['create']  = 'צור';
$labels['createfolder']  = 'צור תיקיה חדשה';
$labels['rename'] = 'שנה שם';
$labels['renamefolder'] = 'שנה שם תיקיה';
$labels['deletefolder']  = 'מחק תיקיה';
$labels['managefolders']  = 'נהל תיקיות';

$labels['sortby'] = 'מיין לפי';
$labels['sortasc']  = 'מיון בסדר עולה';
$labels['sortdesc'] = 'מיון בסדר יורד';

?>