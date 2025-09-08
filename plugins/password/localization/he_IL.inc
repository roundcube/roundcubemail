<?php

/*
 +-----------------------------------------------------------------------+
 | Localization file of the Roundcube Webmail Password plugin            |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/plugin-password/
*/

$labels['changepasswd']  = 'שנה סיסמה';
$labels['curpasswd']  = 'סיסמה נוכחית:';
$labels['newpasswd']  = 'סיסמה חדשה:';
$labels['confpasswd']  = 'אימות הסיסמה החדשה:';
$messages['nopassword'] = 'נא להקליד סיסמה חדשה';
$messages['nocurpassword'] = 'נא להקיש הסיסמה הנוכחית';
$messages['passwordincorrect'] = 'הוקשה סיסמה נוכחית שגויה';
$messages['passwordinconsistency'] = 'הסיסמאות שהוקשו אינן תואמות, נא לנסות שנית.';
$messages['crypterror'] = 'לא נשמרה הסיסמה החדשה. חסר מנגנון הצפנה.';
$messages['connecterror'] = 'לא נשמרה הסיסמה החדשה. שגיאת תקשורת.';
$messages['internalerror'] = 'לא ניתן לשמור על הסיסמה החדשה.';
$messages['passwordshort'] = 'הסיסמה צריכה להיות לפחות בעלת $length תווים';
$messages['passwordweak'] = 'הסיסמה חייבת לכלול לפחות סיפרה אחת ולפחות סימן פיסוק אחד.';
$messages['passwordtooweak'] = 'הסיסמה חלשה מדי';
$messages['passwordnoseq'] = 'אסור שהסיסמה תהיה סדרה של תוים קשורים או בעלי משמעות כמו למשל 123456 או QUWERTY';
$messages['passwordnocommon'] = 'אסור שהסיסמה תהיה מילה נפוצה או שם/כינוי.';
$messages['passwordforbidden'] = 'הסיסמה מכילה תווים אסורים.';
$messages['firstloginchange'] = 'זוהי כניסתך הראשונה. אנא שנה את סיסמתך.';
$messages['disablednotice'] = 'לא ניתן לשנות סיסמה כעת כי המערכת התחזוקה.  המערכת תחזור בקרוב לפעולה רגילה. אנו מתנצלים על אי הנוחות.';
$messages['passwinhistory'] = 'הסיסמה היתה בשימוש מקודם';
$messages['samepasswd'] = 'הסיסמה החדשה צריכה להיות שונה מהישנה';
$messages['passwdexpirewarning'] = 'אזהרה! הסיסמה תפוג בקרוב. יש לשנותה לפני expirationdatetime$.';
$messages['passwdexpired'] = 'פג תוקפה של הסיסמה. יש לשנותה כעת!';
$messages['passwdconstraintviolation'] = 'סיסמה לא חוקית. כנראה חלשה מדי.';
$messages['pwned_mustnotbedisclosed'] = 'אסור שהסיסמה תהיה כזו שהרבה נוהגים להשתמש בה למשל תאריך לידה או 1234.';
$messages['pwned_isdisclosed'] = 'הרבה משתמשים נוהגים להשתמש בסיסמה זו';
$messages['pwned_fetcherror'] = 'לא ניתן לודא שחוזק הסיסמה תואם לנדרש';

$labels['hestia_err_policy_short'] = 'הסיסמה קצרה מדי (אורך מינימלי: %min).';
$labels['hestia_err_endpoint_invalid'] = 'נקודת הקצה של Hestia אינה מוגדרת או אינה חוקית.';
$labels['hestia_err_identity'] = 'לא ניתן לנתח את שם המשתמש ל-local@domain%hint.';
$labels['hestia_err_owner_unknown'] = 'לא ניתן לקבוע את בעל הדומיין עבור %domain.';
$labels['hestia_err_unknown'] = 'שגיאה לא ידועה מ-Hestia API.';
$labels['hestia_err_default'] = 'Hestia החזירה קוד שגיאה %code.';
$labels['hestia_err_1'] = 'לא הוצגו מספיק טיעונים.';
$labels['hestia_err_2'] = 'אובייקט או ארגומנט לא חוקי.';
$labels['hestia_err_3'] = 'האובייקט אינו קיים (תיבת דואר או דומיין לא נמצאו).';
$labels['hestia_err_4'] = 'האובייקט כבר קיים.';
$labels['hestia_err_5'] = 'האובייקט כבר מושעה.';
$labels['hestia_err_6'] = 'האובייקט כבר לא מושעה.';
$labels['hestia_err_7'] = 'אובייקט בשימוש; לא ניתן לשנות.';
$labels['hestia_err_8'] = 'הפעולה נחסמה על ידי מגבלות החבילה.';
$labels['hestia_err_9'] = 'סיסמה שגויה או לא חוקית.';
$labels['hestia_err_10'] = 'הרשאה נדחתה על ידי Hestia (זהות API חסרת זכויות או IP לא מורשה).';
$labels['hestia_err_11'] = 'תכונה מושבתת על ידי מדיניות המערכת.';
$labels['hestia_err_12'] = 'שגיאת ניתוח.';
$labels['hestia_err_13'] = 'שגיאת דיסק.';
$labels['hestia_err_14'] = 'עומס השרת גבוה מדי.';
$labels['hestia_err_15'] = 'שגיאת חיבור.';
$labels['hestia_err_16'] = 'שגיאת FTP.';
$labels['hestia_err_17'] = 'שגיאה במסד הנתונים.';
$labels['hestia_err_18'] = 'שגיאת RRD.';
$labels['hestia_err_19'] = 'שגיאת עדכון.';
$labels['hestia_err_20'] = 'שגיאת הפעלה מחדש.';
?>
