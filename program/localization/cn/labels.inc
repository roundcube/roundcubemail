<?php

/*
 +-----------------------------------------------------------------------+
 | language/cn/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005, RoundQube Dev. - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 |  Simplified-chinese utf-8 by winman 2006/02/19                        |
 |  Author: winman rong  <winman.rong@gmail.com>                         |
 +-----------------------------------------------------------------------+

 $Id$

*/

$labels = array();

// login page
$labels['username']  = '用户名';
$labels['password']  = '密码';
$labels['server']    = '服务器';
$labels['login']     = '登录';

// taskbar
$labels['logout']   = '退出';
$labels['mail']     = '电子邮件';
$labels['settings'] = '个人设置';
$labels['addressbook'] = '地址薄';

// mailbox names
$labels['inbox']  = '收件箱';
$labels['sent']   = '发件箱';
$labels['trash']  = '垃圾桶';
$labels['drafts'] = '草稿';
$labels['junk']   = '垃圾邮件';

// message listing
$labels['subject'] = '邮件标题';
$labels['from']    = '发件人';
$labels['to']      = '接收人';
$labels['cc']      = '副本';
$labels['bcc']     = '密送';
$labels['replyto'] = '回复地址';
$labels['date']    = '日期';
$labels['size']    = '文件大小';
$labels['priority'] = '优先级';
$labels['organization'] = '团体';

// aliases
$labels['reply-to'] = $labels['回复到'];

$labels['mailboxlist'] = '文件夹';
$labels['messagesfromto'] = 'Messages $from to $to of $count';
$labels['messagenrof'] = 'Message $nr of $count';

$labels['moveto']   = '移动到...';
$labels['download'] = '下载';

$labels['filename'] = '文件名称';
$labels['filesize'] = '文件大小';

$labels['preferhtml'] = '以HTML格式显示';
$labels['htmlmessage'] = 'HTML格式邮件';
$labels['prettydate'] = 'Pretty dates';

$labels['addtoaddressbook'] = '加到地址薄';

// weekdays short
$labels['sun'] = '星期日';
$labels['mon'] = '星期一';
$labels['tue'] = '星期二';
$labels['wed'] = '星期三';
$labels['thu'] = '星期四';
$labels['fri'] = '星期五';
$labels['sat'] = '星期六';

// weekdays long
$labels['sunday']    = '星期日';
$labels['monday']    = '星期一';
$labels['tuesday']   = '星期二';
$labels['wednesday'] = '星期三';
$labels['thursday']  = '星期四';
$labels['friday']    = '星期五';
$labels['saturday']  = '星期六';

$labels['today'] = '今天';

// toolbar buttons
$labels['writenewmessage']  = '写新邮件';
$labels['replytomessage']   = '回复邮件';
$labels['forwardmessage']   = '转发邮件';
$labels['deletemessage']    = '把邮件移动到垃圾桶';
$labels['printmessage']     = '打印邮件';
$labels['previousmessages'] = '上一封邮件';
$labels['nextmessages']     = '下一封邮件';
$labels['backtolist']       = '返回收件箱';
$labels['viewsource']       = '显示源代码';

$labels['select'] = '选择';
$labels['all'] = '全部';
$labels['none'] = '不选';
$labels['unread'] = '没读过的';

$labels['compact'] = 'Compact';
$labels['empty'] = '空的';
$labels['purge'] = '清除';

$labels['quota'] = '空间使用';

// message compose
$labels['compose']  = '写新邮件';
$labels['sendmessage']  = '立刻发送邮件';
$labels['addattachment']  = '添加附件';

$labels['attachments'] = '附件';
$labels['upload'] = '上传';
$labels['close']  = '关闭';

$labels['low']     = '低';
$labels['lowest']  = '最低';
$labels['normal']  = '正常';
$labels['high']    = '高';
$labels['highest'] = '最高';

$labels['nosubject']  = '(没有主题)';
$labels['showimages'] = '显示图片';


// address boook
$labels['name']      = '显示名字';
$labels['firstname'] = '名';
$labels['surname']   = '姓';
$labels['email']     = '电子邮件';

$labels['addcontact'] = '增加新联系人';
$labels['editcontact'] = '编辑联系信息';

$labels['edit']   = '编辑';
$labels['cancel'] = '取消';
$labels['save']   = '保存';
$labels['delete'] = '删除';

$labels['newcontact']     = '建立新的联系信息';
$labels['addcontact']     = '将联系人添加到地址薄';
$labels['deletecontact']  = '删除选择的联系信息';
$labels['composeto']      = '给所选择的联系人发送邮件';
$labels['contactsfromto'] = 'Contacts $from to $to of $count';
$labels['print']          = '打印';
$labels['export']         = '导出';


// LDAP search
$labels['ldapsearch'] = 'LDAP 目录搜索';

$labels['ldappublicsearchname']    = '联系人名字';
$labels['ldappublicsearchtype'] = '精确匹配?';
$labels['ldappublicserverselect'] = '选择服务器';
$labels['ldappublicsearchfield'] = 'Search on';
$labels['ldappublicsearchform'] = 'Look for a contact';
$labels['ldappublicsearch'] = '搜索';


// settings
$labels['settingsfor']  = '设置';

$labels['preferences']  = '参数选择';
$labels['userpreferences']  = '用户参数';
$labels['editpreferences']  = '编辑用户参数';

$labels['identities']  = '发件人身份';
$labels['manageidentities']  = '管理发件人资料';
$labels['newidentity']  = '建立新身份';

$labels['newitem']  = 'New item';
$labels['edititem']  = 'Edit item';

$labels['setdefault']  = '设置为默认';
$labels['language']  = '语言';
$labels['timezone']  = '时区';
$labels['pagesize']  = '每页行数';
$labels['signature'] = '签名';


$labels['folder']  = '文件夹';
$labels['folders']  = '文件夹';
$labels['foldername']  = '文件夹名称';
$labels['subscribed']  = '显示与否';
$labels['create']  = '创建';
$labels['createfolder']  = '建立新文件夹';
$labels['deletefolder']  = '删除文件夹';
$labels['managefolders']  = '管理文件夹';

$labels['sortby'] = 'Sort by';
$labels['sortasc']  = '由小到大排列';
$labels['sortdesc'] = '由大到小排列';

?>