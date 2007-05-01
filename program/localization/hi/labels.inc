<?php

/*
 +-----------------------------------------------------------------------+
 | localization/hi/labels.inc                                            |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2007, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Hindi Translation by:                                                 |
 | Aniruddha "Karim" Shankar <hoipolloi@gmail.com>, <karim@sarai.net>    |
 | RoundCubeAuthor: Thomas Bruederli <roundcube@gmail.com>               |
 +-----------------------------------------------------------------------+
*/

$labels = array();

// login page
$labels['welcome']   = '$product में स्वागत है';
$labels['username']  = 'यूसरनाम';
$labels['password']  = 'पासवर्ड';
$labels['server']    = 'सर्वर';
$labels['login']     = 'लॉगिन';

// taskbar
$labels['logout']   = 'लॉगआऊट';
$labels['mail']     = 'ई-मेल';
$labels['settings'] = 'निजी सैटिंग';
$labels['addressbook'] = 'पता खाता';

// mailbox names
$labels['inbox']  = 'इनबॉक्स';
$labels['drafts'] = 'ड्राफ़ट';
$labels['sent']   = 'भेजा गया';
$labels['trash']  = 'रद्दी';
$labels['junk']   = 'स्पैम';

// message listing
$labels['subject'] = 'विशय';
$labels['from']    = 'भेजने वाला';
$labels['to']      = 'पाने वाला';
$labels['cc']      = 'कार्बन';
$labels['bcc']     = 'अंधा कार्बन';
$labels['replyto'] = 'जवाब यहाँ';
$labels['date']    = 'तारीख';
$labels['size']    = 'माप';
$labels['priority'] = 'मेल की महत्वपूर्णता';
$labels['organization'] = 'संस्था';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'फ़ोलडर';
$labels['messagesfromto'] = '$from से ले कर $to, $count मेल में से';
$labels['messagenrof'] = '$nr नम्बर मेल, $count में से';

$labels['moveto']   = 'खीस्काऐं...';
$labels['download'] = 'डाऊनलोड';

$labels['filename'] = 'फ़ाईल नाम';
$labels['filesize'] = 'फ़ाईल माप';

$labels['preferhtml'] = 'ऐच-टी-ऐम-ऐल(HTML) दिखाऐं';
$labels['htmlmessage'] = 'ऐच-टी-ऐम-ऐल (HTML) रूप मेल करें';
$labels['prettydate'] = 'सुनदर तारीखें';

$labels['addtoaddressbook'] = 'पता खाते में जोडें';

// weekdays short
$labels['sun'] = 'रवि';
$labels['mon'] = 'सोम';
$labels['tue'] = 'मंग';
$labels['wed'] = 'बुध';
$labels['thu'] = 'गुरु';
$labels['fri'] = 'शुक्र';
$labels['sat'] = 'शनि';

// weekdays long
$labels['sunday']    = 'रविवार';
$labels['monday']    = 'सोमवार';
$labels['tuesday']   = 'मंगलवार';
$labels['wednesday'] = 'बुधवार';
$labels['thursday']  = 'गुरुवार';
$labels['friday']    = 'शुक्रवार';
$labels['saturday']  = 'शनिवार';

$labels['today'] = 'आज';

// toolbar buttons
$labels['checkmail']        = 'नये मेल लाऐं';
$labels['writenewmessage']  = 'मेल लिखें';
$labels['replytomessage']   = 'मेल का जवाब दें';
$labels['replytoallmessage'] = 'सभी को जवाब लिखें';
$labels['forwardmessage']   = 'आगे भेजें';
$labels['deletemessage']    = 'रद्द करें';
$labels['printmessage']     = 'मेल छापें';
$labels['previousmessage']	= 'पीछे के मेल दिखाऐं';
$labels['previousmessages']	= 'पीछे के मेल का बन्डल दिखाऐं';
$labels['firstmessage']		= 'पहला मेल दिखाऐं';
$labels['firstmessages']	= 'पहला मेल का बन्डल दिखाऐं';
$labels['nextmessage']		= 'अगला मेल दिखाऐं';
$labels['nextmessages']		= 'अगले मेल का बन्डल दिखाऐं';
$labels['lastmessage']		= 'आखीरी मेल दिखाऐं';
$labels['lastmessages']		= 'आखिरी मेल का बन्डल दिखाऐं';
$labels['backtolist']		= 'मेल सूची देखें';
$labels['viewsource']		= 'सोर्स दिखाऐं';

$labels['select']		= 'चुनें';
$labels['all']			= 'सभी';
$labels['none']			= 'कोई नहीं';
$labels['unread']		= 'अनदेखी';

$labels['compact']		= 'छोटा करें';
$labels['empty']		= 'मेल रद्दी में डालें';
$labels['purge']		= 'मिटाऐं';

$labels['quota']		= 'डिस्क उपयोग';
$labels['unknown']		= 'अज्ञात';
$labels['unlimited']		= 'असीम';

$labels['quicksearch']		= 'तेज़ खोज';
$labels['resetsearch']		= 'खोज खाली करें';


// message compose
$labels['compose']		= 'मेल लिखें';
$labels['sendmessage']		= 'मेल भेजें';
$labels['savemessage']		= 'ड्राफ़ट सहेजें';
$labels['addattachment']	= 'फ़ाईल जोडें';
$labels['charset']		= 'कैरेक्टर सैट';
$labels['editortype']		= 'सम्पादक प्राकार';
$labels['returnreceipt']	= 'मेल रसीद';

$labels['checkspelling']	= 'स्पैलिंग जाँचें';
$labels['resumeediting']	= 'सम्पादन फिर शुरू करें';
$labels['revertto']		= 'पहले की स्थिती पर जाऐं';

$labels['attachments']		= 'जुडे फाईल';
$labels['upload']		= 'अपलोड';
$labels['close']		= 'बंद';

$labels['low']			= 'कम';
$labels['lowest']		= 'सबसे कम';
$labels['normal']		= 'साधारण';
$labels['high']			= 'उच्च';
$labels['highest']		= 'उच्चतम';

$labels['nosubject']		= '(कोई विशय नहीं)';
$labels['showimages']		= 'चित्र दिखाऐं';

$labels['htmltoggle']		= 'ऐच-टी-ऐम-एल (HTML)';
$labels['plaintoggle']		= 'सादा पाठ';


// address boook
$labels['name']			= 'दिखने वाला नाम';
$labels['firstname']		= 'पहला नाम';
$labels['surname']		= 'उपनाम';
$labels['email']		= 'मेल';

$labels['addcontact']		= 'नया पता जोडें';
$labels['editcontact']		= 'पता सम्पाद';

$labels['edit']			= 'सम्पाद';
$labels['cancel']		= 'रद्द';
$labels['save']			= 'सहेज';
$labels['delete']		= 'मिटा';

$labels['newcontact']		= 'नया पता कार्ड बनाऐं';
$labels['deletecontact']	= 'चुने पते मिटाऐं';
$labels['composeto']		= 'को मेल भेजें';
$labels['contactsfromto']	= '$from से ले कर $to, $count पते में से';
$labels['print']		= 'छापें';
$labels['export']		= 'ऐक्स्पोर्ट';

$labels['previouspage']		= 'इससे पहले के बन्डल को दिखाऐं';
$labels['firstpage']		= 'पहला बन्डल दिखाऐं';
$labels['nextpage']		= 'अगला बन्डल दिखाऐं';
$labels['lastpage']		= 'आखिरी बन्डल दिखाऐं';


// LDAP search
$labels['ldapsearch']		= 'ऐलडैप (LDAP) डाईरेक्टरी खोजें';

$labels['ldappublicsearchname']	= 'पता का नाम';
$labels['ldappublicsearchtype']	= 'सख़त मैच?';
$labels['ldappublicserverselect']	= 'सर्वर चुनें';
$labels['ldappublicsearchfield']	= 'यहाँ खोजें';
$labels['ldappublicsearchform']	= 'पता के लिये खोजें';
$labels['ldappublicsearch']	= 'खोज';


// settings
$labels['settingsfor']		= 'इनके विन्यास:';

$labels['preferences']		= 'पसंद';
$labels['userpreferences']	= 'यूसर पसंद';
$labels['editpreferences']	= 'यूसर पसंद सम्पादें';

$labels['identities']		= 'पहचान';
$labels['manageidentities']	= 'इस खाते के पहचान व्यवस्थित करें';
$labels['newidentity']		= 'नया पहचान';

$labels['newitem']		= 'नया चीज़';
$labels['edititem']		= 'चीज़ सम्पादें';

$labels['setdefault']		= 'तयशुदा चुनें';
$labels['language']		= 'भाषा';
$labels['timezone']		= 'समय क्षेत्र';
$labels['pagesize']		= 'हर पन्ने पर इतने';
$labels['signature']		= 'दस्तखत';
$labels['dstactive']		= 'रौशनी बचाव';
$labels['htmleditor']		= 'मेल ऐच-टी-ऐम-एल (HTML) मे लिखें';
$labels['htmlsignature']	= 'ऐच-टी-ऐम-एल (HTML) दस्तखत';
$labels['previewpane']		= 'झलक पट्टी दिखाऐं';

$labels['autosavedraft']	= 'ड्राफ़ट अपने आप सहेजते जाऐं';
$labels['everynminutes']	= 'हर $n मिनट';
$labels['never']		= 'कभी नहाीं';

$labels['folder']		= 'फ़ोल्डर';
$labels['folders']		= 'फ़ोल्डर';
$labels['foldername']		= 'फ़ोल्डर नाम';
$labels['subscribed']		= 'सबस्क्राईब किया गया';
$labels['create']		= 'बनाऐं';
$labels['createfolder']		= 'फ़ोलडर बनाऐं';
$labels['rename']		= 'नाम बदलें';
$labels['renamefolder']		= 'फ़ोलडर का नाम बदलें';
$labels['deletefolder']		= 'फ़ोलडर मिटाऐं';
$labels['managefolders']	= 'फ़ोलडर को व्यवस्थित करें';

$labels['sortby']		= 'ऐसे तय करें';
$labels['sortasc']		= 'पहले से आखीरी तय करें';
$labels['sortdesc']		= 'आखीरी से पहले तय करें';

?>
