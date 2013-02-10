<?php

/*
 +-----------------------------------------------------------------------+
 | localization/<lang>/messages.inc                                      |
 |                                                                       |
 | Localization file of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 +-----------------------------------------------------------------------+

 For translation see https://www.transifex.com/projects/p/roundcube-webmail/resource/messages/
*/

$messages = array();
$messages['errortitle']  = 'An error occurred!';
$messages['loginfailed']  = 'Најавата не успеа';
$messages['cookiesdisabled'] = 'Вашиот прелистувач не прифаќа колачиња';
$messages['sessionerror'] = 'Сесијата е неважечка или е истечена';
$messages['storageerror'] = 'Поврзувањето со IMAP не успеа';
$messages['servererror'] = 'Грешка во серверот!';
$messages['servererrormsg'] = 'Server Error: $msg';
$messages['dberror'] = 'Database Error!';
$messages['requesttimedout'] = 'Request timed out';
$messages['errorreadonly'] = 'Unable to perform operation. Folder is read-only.';
$messages['errornoperm'] = 'Unable to perform operation. Permission denied.';
$messages['invalidrequest'] = 'Неважечко барање! Податоците не се зачувани.';
$messages['invalidhost'] = 'Invalid server name.';
$messages['nomessagesfound'] = 'Немате писма во ова сандаче';
$messages['loggedout'] = 'Успешно се одјавивте. Ви благодариме и довидување!';
$messages['mailboxempty'] = 'Поштенското сандаче е празно.';
$messages['refreshing'] = 'Refreshing...';
$messages['loading'] = 'Вчитувам...';
$messages['uploading'] = 'Ја подигам податотеката...';
$messages['uploadingmany'] = 'Uploading files...';
$messages['loadingdata'] = 'Ги вчитувам податоците...';
$messages['checkingmail'] = 'Проверувам нова пошта...';
$messages['sendingmessage'] = 'Ја испраќам пораката...';
$messages['messagesent'] = 'Пораката е успешно испратена';
$messages['savingmessage'] = 'Ја зачувувам пораката...';
$messages['messagesaved'] = 'Пораката е зачувана во Непратени';
$messages['successfullysaved'] = 'Успешно зачувано';
$messages['addedsuccessfully'] = 'Името е успешно додадено во Именикот';
$messages['contactexists'] = 'Веќе постои име со зададената Е-поштенска адреса';
$messages['contactnameexists'] = 'A contact with the same name already exists.';
$messages['blockedimages'] = 'За да се заштити вашата приватност, сликите во оваа порака не се прикажани.';
$messages['encryptedmessage'] = 'Оваа порака е кодирана и не може да се прикаже. Жалиме!';
$messages['nocontactsfound'] = 'Нема пронајдено имиња.';
$messages['contactnotfound'] = 'Бараното име не е пронајдено';
$messages['contactsearchonly'] = 'Enter some search terms to find contacts';
$messages['sendingfailed'] = 'Не упеав да го испратам писмото';
$messages['senttooquickly'] = 'Почекајте $sec секунда/и пред да го испратите писмото';
$messages['errorsavingsent'] = 'Грешка при зачувувањето на пратеното писмо';
$messages['errorsaving'] = 'Грешка при зачувувањето';
$messages['errormoving'] = 'Не можев да го преместам писмото';
$messages['errorcopying'] = 'Could not copy the message(s).';
$messages['errordeleting'] = 'Не можев да го избришам писмото';
$messages['errormarking'] = 'Не можев да го обележам писмото';
$messages['deletecontactconfirm']  = 'Дали сте сигурни дека сакате да го избришете одбраното(те) име(ња)?';
$messages['deletegroupconfirm']  = 'Do you really want to delete selected group?';
$messages['deletemessagesconfirm'] = 'Дали сте сигурни дека сакате да го(и) избришете обележаниот(те) писмо(а)?';
$messages['deletefolderconfirm']  = 'Дали сте сигурни дека сакате да ја избришете оваа папка?';
$messages['purgefolderconfirm']  = 'Дали сте сигурни дека сакате да ги избршете сите писма во оваа папка?';
$messages['contactdeleting'] = 'Deleting contact(s)...';
$messages['groupdeleting'] = 'Deleting group...';
$messages['folderdeleting'] = 'Ја бришам папката...';
$messages['foldermoving'] = 'Ја преместувам папката...';
$messages['foldersubscribing'] = 'Subscribing folder...';
$messages['folderunsubscribing'] = 'Unsubscribing folder...';
$messages['formincomplete'] = 'Имате изоставено непополнети информации';
$messages['noemailwarning'] = 'Внесете валидна е-поштенска адреса';
$messages['nonamewarning']  = 'Внесете име';
$messages['nopagesizewarning'] = 'Внесете големина на страницата';
$messages['nosenderwarning'] = 'Внесете адреса на праќачот';
$messages['norecipientwarning'] = 'Внесете барем еден примач';
$messages['nosubjectwarning']  = 'Немате внесено „Наслов“. Дали сакате да внесете?';
$messages['nobodywarning'] = 'Писмото нема текст во него. Дали сакате да го испратите?';
$messages['notsentwarning'] = 'Писмото не е пратено. Дали сакате да го отфрлите?';
$messages['noldapserver'] = 'Изберете LDAP сервер за пребарување';
$messages['nosearchname'] = 'Внесете име или е-поштенска адреса';
$messages['notuploadedwarning'] = 'Сè уште нема подигнати прилози. Почекајте или откажете го подигањето';
$messages['searchsuccessful'] = 'Пронајадени се $nr пораки';
$messages['contactsearchsuccessful'] = '$nr contacts found.';
$messages['searchnomatch'] = 'Пребарувањето не даде резлутати';
$messages['searching'] = 'Пребарувам....';
$messages['checking'] = 'Проверувам...';
$messages['nospellerrors'] = 'Нема грешки во спелувањето';
$messages['folderdeleted'] = 'Папката е успешно избришана';
$messages['foldersubscribed'] = 'Folder successfully subscribed.';
$messages['folderunsubscribed'] = 'Folder successfully unsubscribed.';
$messages['folderpurged'] = 'Folder has successfully been emptied.';
$messages['folderexpunged'] = 'Folder has successfully been compacted.';
$messages['deletedsuccessfully'] = 'Успешно избришано';
$messages['converting'] = 'Форматирањето на писмото е отстрането...';
$messages['messageopenerror'] = 'Не можев да го вчитам писното од серверот';
$messages['fileuploaderror'] = 'Прикачувањето е неуспешно';
$messages['filesizeerror'] = 'Подигнатата податотека го надминува ограничувањето од $size';
$messages['copysuccess'] = 'Успешно се копирани $nr имиња';
$messages['copyerror'] = 'Не можам да ги копирам адресите';
$messages['sourceisreadonly'] = 'Изворот на оваа адреса неможе да се промени';
$messages['errorsavingcontact'] = 'Името неможе да се сними';
$messages['movingmessage'] = 'Пораката се преместува...';
$messages['copyingmessage'] = 'Copying message(s)...';
$messages['copyingcontact'] = 'Copying contact(s)...';
$messages['deletingmessage'] = 'Deleting message(s)...';
$messages['markingmessage'] = 'Marking message(s)...';
$messages['addingmember'] = 'Adding contact(s) to the group...';
$messages['removingmember'] = 'Removing contact(s) from the group...';
$messages['receiptsent'] = 'Известувањето е успешно пратено';
$messages['errorsendingreceipt'] = 'Известувањето не е пратено';
$messages['deleteidentityconfirm'] = 'Do you really want to delete this identity?';
$messages['nodeletelastidentity'] = 'Овој идентитет неможе да се избрише, тој е последниот.';
$messages['forbiddencharacter'] = 'Името на папката содржи несоодветен формат';
$messages['selectimportfile'] = 'Изберете ја податотеката која сакате да ја прикачите';
$messages['addresswriterror'] = 'Одбраниот именик не може да се менува';
$messages['contactaddedtogroup'] = 'Successfully added the contacts to this group.';
$messages['contactremovedfromgroup'] = 'Successfully removed contacts from this group.';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = 'Убезувам,, почекајте...';
$messages['importformaterror'] = 'Import failed! The uploaded file is not a valid import data file.';
$messages['importconfirm'] = '<b>Успешно се увезени $inserted имиња, $skipped веќе постојат и се прескокнати</b>:<p><em>$names</em></p>';
$messages['importconfirmskipped'] = '<b>Skipped $skipped existing entries</b>';
$messages['opnotpermitted'] = 'Операцијата не е дозволена';
$messages['nofromaddress'] = 'Не е внесена е-пошта во одберениот идентитет';
$messages['editorwarning'] = 'Префрлањето на уредникот на обичен текст ќе резултира со губење на целото форматирање на текстот. Дали сакате да продолжите?';
$messages['httpreceivedencrypterror'] = 'Се појави фатална конфигурациска грешка. Веднаш контактирајте го администраторот. <b>Вашето писмо не мож.</b>';
$messages['smtpconnerror'] = 'SMTP грешка ($code): Не успеав да се поврзам со серверот';
$messages['smtpautherror'] = 'SMTP грешка: Не успееа потврдувањето';
$messages['smtpfromerror'] = 'SMTP грешка ($code): Не успеав да го поставам испраќачот „$from“ ($msg)';
$messages['smtptoerror'] = 'SMTP грешка ($code): Не успеав да го поставам примачот „$yo“ ($msg)';
$messages['smtprecipientserror'] = 'SMTP грешка: Не успеав да ја испарсирам листата на примачи';
$messages['smtperror'] = 'SMTP Error: $msg';
$messages['emailformaterror'] = 'Неправилна поштенска адреса: $email';
$messages['toomanyrecipients'] = 'Too many recipients. Reduce the number of recipients to $max.';
$messages['maxgroupmembersreached'] = 'The number of group members exceeds the maximum of $max.';
$messages['internalerror'] = 'An internal error occured. Please try again.';
$messages['contactdelerror'] = 'Could not delete contact(s).';
$messages['contactdeleted'] = 'Contact(s) deleted successfully.';
$messages['contactrestoreerror'] = 'Could not restore deleted contact(s).';
$messages['contactrestored'] = 'Contact(s) restored successfully.';
$messages['groupdeleted'] = 'Group deleted successfully.';
$messages['grouprenamed'] = 'Group renamed successfully.';
$messages['groupcreated'] = 'Group created successfully.';
$messages['savedsearchdeleted'] = 'Saved search deleted successfully.';
$messages['savedsearchdeleteerror'] = 'Could not delete saved search.';
$messages['savedsearchcreated'] = 'Saved search created successfully.';
$messages['savedsearchcreateerror'] = 'Could not create saved search.';
$messages['messagedeleted'] = 'Message(s) deleted successfully.';
$messages['messagemoved'] = 'Message(s) moved successfully.';
$messages['messagecopied'] = 'Message(s) copied successfully.';
$messages['messagemarked'] = 'Message(s) marked successfully.';
$messages['autocompletechars'] = 'Enter at least $min characters for autocompletion.';
$messages['autocompletemore'] = 'More matching entries found. Please type more characters.';
$messages['namecannotbeempty'] = 'Name cannot be empty.';
$messages['nametoolong'] = 'Name is too long.';
$messages['folderupdated'] = 'Folder updated successfully.';
$messages['foldercreated'] = 'Folder created successfully.';
$messages['invalidimageformat'] = 'Not a valid image format.';
$messages['mispellingsfound'] = 'Spelling errors detected in the message.';
$messages['parentnotwritable'] = 'Unable to create/move folder into selected parent folder. No access rights.';
$messages['messagetoobig'] = 'The message part is too big to process it.';
$messages['attachmentvalidationerror'] = 'WARNING! This attachment is suspicious because its type doesn\'t match the type declared in the message. If you do not trust the sender, you shouldn\'t open it in the browser because it may contain malicious contents.<br/><br/><em>Expected: $expected; found: $detected</em>';
$messages['noscriptwarning'] = 'Warning: This webmail service requires Javascript! In order to use it please enable Javascript in your browser\'s settings.';

?>
