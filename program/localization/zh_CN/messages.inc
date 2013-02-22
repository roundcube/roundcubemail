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
$messages['errortitle']  = '发生错误！';
$messages['loginfailed']  = '登录失败。';
$messages['cookiesdisabled'] = '您的浏览器不接受 cookies。';
$messages['sessionerror'] = '会话已过期。';
$messages['storageerror'] = '连接邮件服务器失败。';
$messages['servererror'] = '服务器错误！';
$messages['servererrormsg'] = '服务器错误: $msg';
$messages['dberror'] = '数据库错误！';
$messages['requesttimedout'] = '请求超时';
$messages['errorreadonly'] = '不可对只读文件夹进行操作。';
$messages['errornoperm'] = '无权限操作';
$messages['invalidrequest'] = '无效的请求！数据保存失败。';
$messages['invalidhost'] = '非法主机名。';
$messages['nomessagesfound'] = '此邮件夹内无邮件。';
$messages['loggedout'] = '注销成功，再见！';
$messages['mailboxempty'] = '邮件夹为空';
$messages['refreshing'] = '正在刷新...';
$messages['loading'] = '正在载入...';
$messages['uploading'] = '正在上传文件...';
$messages['uploadingmany'] = '正在上传文件...';
$messages['loadingdata'] = '正在载入数据...';
$messages['checkingmail'] = '正在检查新邮件...';
$messages['sendingmessage'] = '正在发送邮件...';
$messages['messagesent'] = '邮件发送成功。';
$messages['savingmessage'] = '正在保存邮件...';
$messages['messagesaved'] = '邮件已暂存至草稿箱。';
$messages['successfullysaved'] = '保存成功。';
$messages['addedsuccessfully'] = '成功添加联系人。';
$messages['contactexists'] = '当前联系人的电子邮件地址已存在。';
$messages['contactnameexists'] = '已存在同名联系人。';
$messages['blockedimages'] = '为保护隐私，此邮件中的远程图片未予显示。';
$messages['encryptedmessage'] = '抱歉！该邮件已被加密，无法显示。';
$messages['nocontactsfound'] = '未找到联系人。';
$messages['contactnotfound'] = '未找到指定的联系人。';
$messages['contactsearchonly'] = '请输入联系人的搜索条件';
$messages['sendingfailed'] = '发送失败。';
$messages['senttooquickly'] = '您需要等待$sec秒才能发送邮件。';
$messages['errorsavingsent'] = '保存已发送邮件时发生错误。';
$messages['errorsaving'] = '保存时发生错误。';
$messages['errormoving'] = '无法移动邮件。';
$messages['errorcopying'] = '无法复制邮件。';
$messages['errordeleting'] = '无法删除邮件。';
$messages['errormarking'] = '无法标记邮件。';
$messages['deletecontactconfirm']  = '确定要删除已选中的联系人？';
$messages['deletegroupconfirm']  = '确定要删除以选中的组?';
$messages['deletemessagesconfirm'] = '确定要删除已选中的邮件？';
$messages['deletefolderconfirm']  = '确定要删除已选中的邮件夹？';
$messages['purgefolderconfirm']  = '是否确认要删除当前邮件夹中的所有邮件？';
$messages['contactdeleting'] = '正在删除联系人...';
$messages['groupdeleting'] = '正在删除组...';
$messages['folderdeleting'] = '正在删除文件夹...';
$messages['foldermoving'] = '正在移动文件夹...';
$messages['foldersubscribing'] = '订阅文件夹中...';
$messages['folderunsubscribing'] = '退订中...';
$messages['formincomplete'] = '当前表单未填写完整，请完整填写。';
$messages['noemailwarning'] = '请输入一个有效的邮件地址。';
$messages['nonamewarning']  = '请输入名字。';
$messages['nopagesizewarning'] = '请输入每页显示的邮件数量';
$messages['nosenderwarning'] = '请输入发件人地址。';
$messages['norecipientwarning'] = '至少需要一个收件人。';
$messages['nosubjectwarning']  = '主题为空。您要输入一个主题吗？';
$messages['nobodywarning'] = '要发送无正文的邮件吗？';
$messages['notsentwarning'] = '邮件未发送。您确定要离开并舍弃当前邮件吗？';
$messages['noldapserver'] = '请选择一个LDAP服务器进行查找。';
$messages['nosearchname'] = '请输入一个联系人姓名或电子邮件地址。';
$messages['notuploadedwarning'] = '附件还没有全部上传，请等待或者取消上传。';
$messages['searchsuccessful'] = '共找到 $nr 封邮件。';
$messages['contactsearchsuccessful'] = '共找到 $nr 位联系人。';
$messages['searchnomatch'] = '未找到任何符合条件的邮件';
$messages['searching'] = '正在搜索...';
$messages['checking'] = '正在检查...';
$messages['nospellerrors'] = '未发现拼写错误';
$messages['folderdeleted'] = '文件夹已被成功删除。';
$messages['foldersubscribed'] = '成功订阅文件夹。';
$messages['folderunsubscribed'] = '成功退订的文件夹。';
$messages['folderpurged'] = '成功清空文件夹。';
$messages['folderexpunged'] = '文件夹已清空。';
$messages['deletedsuccessfully'] = '删除成功。';
$messages['converting'] = '格式转换中...';
$messages['messageopenerror'] = '无法从服务器上加载邮件内容。';
$messages['fileuploaderror'] = '文件上传失败。';
$messages['filesizeerror'] = '上传的文件超过了 $size 的大小限制。';
$messages['copysuccess'] = '成功复制 $nr 个地址。';
$messages['copyerror'] = '无法复制地址。';
$messages['sourceisreadonly'] = '源地址为只读。';
$messages['errorsavingcontact'] = '无法保存联系人的地址。';
$messages['movingmessage'] = '移动邮件到...';
$messages['copyingmessage'] = '复制邮件到...';
$messages['copyingcontact'] = '复制联系人...';
$messages['deletingmessage'] = '正在删除邮件...';
$messages['markingmessage'] = '正在标记邮件...';
$messages['addingmember'] = '正在添加联系人到组...';
$messages['removingmember'] = '正在从组中删除联系人...';
$messages['receiptsent'] = '成功发送了一个已读回执。';
$messages['errorsendingreceipt'] = '无法发送回执。';
$messages['deleteidentityconfirm'] = '您真的想删除这个验证信息吗？';
$messages['nodeletelastidentity'] = '无法删除这个身份，这是最后一个。';
$messages['forbiddencharacter'] = '目录名包含隐藏字符。';
$messages['selectimportfile'] = '请选择要上传的文件。';
$messages['addresswriterror'] = '已选择的地址簿不可写。';
$messages['contactaddedtogroup'] = '成功添加联系人至该分组。';
$messages['contactremovedfromgroup'] = '成功从该分组移除联系人。';
$messages['nogroupassignmentschanged'] = 'No group assignments changed.';
$messages['importwait'] = '正在导入，请稍后...';
$messages['importformaterror'] = '导入失败！文件无效。';
$messages['importconfirm'] = '<b>成功导入 $inserted 联系人</b>';
$messages['importconfirmskipped'] = '<b>跳过已存在的 $skipped 项目</b>';
$messages['opnotpermitted'] = '不允许的操作!';
$messages['nofromaddress'] = '选中的身份中没有邮件地址。';
$messages['editorwarning'] = '切换到纯文本编辑器将导致邮件正文中的所有文本格式失效，您确定要这样做吗？';
$messages['httpreceivedencrypterror'] = '发生了一个致命的配置错误，请立即联系管理员。<b>您的邮件无法发送。</b>';
$messages['smtpconnerror'] = 'SMTP 错误 ($code):连接服务器失败。';
$messages['smtpautherror'] = 'SMTP 错误 ($code): 认证失败。';
$messages['smtpfromerror'] = 'SMTP 错误 ($code):添加发件人失败 "$from" ($msg)。';
$messages['smtptoerror'] = 'SMTP 错误 ($code): 添加收件人失败 "$to" ($msg)。';
$messages['smtprecipientserror'] = 'SMTP 错误: 无法解析收件人列表。';
$messages['smtperror'] = 'SMTP 错误: $msg';
$messages['emailformaterror'] = '无效的邮件地址：$email';
$messages['toomanyrecipients'] = '收件人太多，请减少人数至 $max。';
$messages['maxgroupmembersreached'] = '组员数量超过最大值 $max。';
$messages['internalerror'] = '遇到一个内部错误，请重试。';
$messages['contactdelerror'] = '无法删除联系人。';
$messages['contactdeleted'] = '删除联系人成功。';
$messages['contactrestoreerror'] = '无法恢复已删除的联系人。';
$messages['contactrestored'] = '联系人恢复成功。';
$messages['groupdeleted'] = '成功删除组。';
$messages['grouprenamed'] = '组改名成功。';
$messages['groupcreated'] = '成功创建组';
$messages['savedsearchdeleted'] = '成功删除保存的搜索';
$messages['savedsearchdeleteerror'] = '无法删除保存的搜索';
$messages['savedsearchcreated'] = '成功建立保存的搜索';
$messages['savedsearchcreateerror'] = '无法创建保存的搜索。';
$messages['messagedeleted'] = '删除邮件成功。';
$messages['messagemoved'] = '移动邮件成功。';
$messages['messagecopied'] = '复制邮件成功';
$messages['messagemarked'] = '标记邮件成功';
$messages['autocompletechars'] = '至少需要输入 $min 个字符';
$messages['autocompletemore'] = '找到太多的结果。请输入更多字符。';
$messages['namecannotbeempty'] = '必须输入名字';
$messages['nametoolong'] = '名字太长';
$messages['folderupdated'] = '成功更新文件夹';
$messages['foldercreated'] = '成功创建文件夹';
$messages['invalidimageformat'] = '非法的图像类型。';
$messages['mispellingsfound'] = '检查到拼写错误。';
$messages['parentnotwritable'] = '无法创建和转移到所选的目录,权限不足。';
$messages['messagetoobig'] = '由于邮件部分过大无法处理。';
$messages['attachmentvalidationerror'] = 'WARNING! This attachment is suspicious because its type doesn\'t match the type declared in the message. If you do not trust the sender, you shouldn\'t open it in the browser because it may contain malicious contents.<br/><br/><em>Expected: $expected; found: $detected</em>';
$messages['noscriptwarning'] = '警告：本程序需要 Javascript 支持！请在浏览器设置中启用 Javascript。';

?>
