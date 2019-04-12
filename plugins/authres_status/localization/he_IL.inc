<?php

$labels = array();
$labels['nosignature'] = 'אין מידע חתימה';
$labels['noauthresults'] = 'חתימה נמצאה אך לא תקורת תוצאת אימות. בעיית תצורת שרת?';
$labels['signaturepass'] = 'חתימה תקפה מתוך מתחם ממען. אומתה על ידי';
$labels['partialpass'] = 'חתימות מסוימות אינן תקפות אך לפחות אחת הינה תקפה עבור המתחם של הממען. אומתה על ידי';
$labels['thirdparty'] = 'נחתמה על ידי צד שלישי, חתימה הינה קיימת אך עבור מתחם שונה מזה של הכתובת. אומתה על ידי';
$labels['temporaryinvalid'] = 'חתימה (זמנית) אינה תקפה! אומתה על ידי';
$labels['invalidsignature'] = 'חתימה אינה תקפה! אומתה על ידי';

$labels['for'] = 'עבור';
$labels['by'] = 'על ידי';

$labels['section_title']  = 'מצב אימות';
$labels['column_title']  = 'תוצאת אימות';

$labels['title_enable_column']  = 'אפשר טור';
$labels['label_enable_column']  = 'השתמש בטור תוצאת אימות בתוך רשימת הודעה';
$labels['label_fallback_verifier'] = 'השתמש במאמת DKIM פנימי (חתימה נמצאה אך לא תקורת תוצאת אימות)';

$labels['title_trusted_mtas'] = 'MTA שניתן לבטוח בו';
$labels['label_trusted_mtas'] = 'רשימה מופרדת בפסיקים של AUTHSERV-ID של MTA אשר ניתן לבטוח בהם. רק תוצאות מרשימה זו יוצגו.';

$labels['title_include_status'] = 'הצג רק את המצבים הבאים';
$labels['label_include_status1']  = 'לא נמצאה חתימה';
$labels['label_include_status2']  = 'חתימה נמצאה אך לא תקורת תוצאת אימות';
$labels['label_include_status4']  = 'כל החתימות עברו אימות';
$labels['label_include_status8']  = 'חתימה אחת לפחות עברה אימות';
$labels['label_include_status16']  = 'חתימה צד שלישי';
$labels['label_include_status32']  = 'חתימה נכשלה זמנית';
$labels['label_include_status64']  = 'חתימה נכשלה תמידית';

?>
