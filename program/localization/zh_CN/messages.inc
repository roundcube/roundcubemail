<?php

/*

+-----------------------------------------------------------------------+
| localization/zh_CN/messages.inc                                       |
|                                                                       |
| Language file of the RoundCube Webmail client                         |
| Copyright (C) 2005-2010, RoundCube Dev. - Switzerland                 |
| Licensed under the GNU GPL                                            |
|                                                                       |
+-----------------------------------------------------------------------+
| Author: Xue zhong sheng <xue.zhongsheng@gmail.com>                    |
|         Zhang Huang bin <michaelbibby@gmail.com>                      |
+-----------------------------------------------------------------------+

@version $Id$

*/

$messages = array();
$messages['loginfailed'] = '登录失败';
$messages['cookiesdisabled'] = '您的浏览器不接受 cookies';
$messages['sessionerror'] = '会话已过期';
$messages['imaperror'] = '连接到邮件服务器失败';
$messages['servererror'] = '服务器错误！';
$messages['invalidrequest'] = '无效的请求！数据保存失败。';
$messages['nomessagesfound'] = '此邮件夹内无邮件';
$messages['loggedout'] = '您已成功注销';
$messages['mailboxempty'] = '邮件夹为空';
$messages['loading'] = '正在加载...';
$messages['uploading'] = '正在上传文件...';
$messages['loadingdata'] = '正在加载数据...';
$messages['checkingmail'] = '检查新邮件...';
$messages['sendingmessage'] = '正在发送邮件...';
$messages['messagesent'] = '邮件发送成功';
$messages['savingmessage'] = '正在储存邮件...';
$messages['messagesaved'] = '邮件已暂存到草稿箱';
$messages['successfullysaved'] = '保存成功';
$messages['addedsuccessfully'] = '成功添加联系人';
$messages['contactexists'] = '当前联系人的电子邮件地址已存在';
$messages['blockedimages'] = '为保护隐私，此邮件中的远程图片未显示';
$messages['encryptedmessage'] = '因此邮件已加密，无法正常显示';
$messages['nocontactsfound'] = '未找到联系人';
$messages['contactnotfound'] = '未找到指定的联系人';
$messages['sendingfailed'] = '发送失败';
$messages['senttooquickly'] = '您需要等待 $sec 秒才能发送邮件';
$messages['errorsavingsent'] = '保存已发送邮件时出现错误';
$messages['errorsaving'] = '保存过程中发生错误';
$messages['errormoving'] = '无法移动邮件';
$messages['errorcopying'] = '无法复制邮件';
$messages['errordeleting'] = '无法删除邮件';
$messages['errormarking'] = '无法标记邮件';
$messages['deletecontactconfirm'] = '确定要删除已选中的联系人？';
$messages['deletemessagesconfirm'] = '确定要删除已选中的邮件？';
$messages['deletefolderconfirm'] = '确定要删除已选中的邮件夹？';
$messages['purgefolderconfirm'] = '是否确认要删除当前邮件夹中的所有邮件？';
$messages['foldercreating'] = '正在创建邮件夹';
$messages['folderdeleting'] = '正在删除邮件夹';
$messages['folderrenaming'] = '正在重命名邮件夹';
$messages['foldermoving'] = '正在移动目录...';
$messages['formincomplete'] = '当前表单未填写完整，请完整填写';
$messages['noemailwarning'] = '请输入一个有效的邮件地址';
$messages['nonamewarning'] = '请输入名字';
$messages['nopagesizewarning'] = '请输入每页显示的邮件数量';
$messages['nosenderwarning'] = '请输入发件人的邮件地址';
$messages['norecipientwarning'] = '至少需要一个收件人';
$messages['nosubjectwarning'] = '主题为空。您要输入一个主题吗？';
$messages['nobodywarning'] = '要发送无正文的邮件吗？';
$messages['notsentwarning'] = '邮件未发送。您确定要离开并废弃当前邮件吗？';
$messages['noldapserver'] = '请选择一个LDAP服务器进行查找';
$messages['nocontactsreturned'] = '未找到联系人';
$messages['nosearchname'] = '请输入一个联系人姓名或电子邮件地址';
$messages['notuploadedwarning'] = '附件还没有全部上传，请等待或者取消上传。';
$messages['searchsuccessful'] = '共找到 $nr 封邮件';
$messages['searchnomatch'] = '未找到任何符合条件的邮件';
$messages['searching'] = '正在查找...';
$messages['checking'] = '正在检查...';
$messages['nospellerrors'] = '未发现拼写错误';
$messages['folderdeleted'] = '邮件夹已被成功删除';
$messages['deletedsuccessfully'] = '删除成功';
$messages['converting'] = '格式转换中...';
$messages['messageopenerror'] = '无法从服务器上加载邮件内容';
$messages['fileuploaderror'] = '文件上传失败';
$messages['filesizeerror'] = '上传的文件超过了 $size 的大小限制';
$messages['copysuccess'] = '成功复制 $nr 个地址';
$messages['copyerror'] = '无法复制地址';
$messages['sourceisreadonly'] = '源地址为只读';
$messages['errorsavingcontact'] = '无法保存联系人的地址';
$messages['movingmessage'] = '移动邮件到...';
$messages['copyingmessage'] = '复制邮件到...';
$messages['receiptsent'] = '成功发送了一个已读回执';
$messages['errorsendingreceipt'] = '无法发送回执';
$messages['nodeletelastidentity'] = '无法删除这个身份，这是最后一个。';
$messages['addsubfolderhint'] = '新建的文件夹将成为所选文件夹的子文件夹';
$messages['forbiddencharacter'] = '目录名包含隐藏字符';
$messages['selectimportfile'] = '请选择要上传的文件';
$messages['addresswriterror'] = '已选择的地址簿不可写';
$messages['contactaddedtogroup'] = '成功添加联系人到这个分组';
$messages['contactremovedfromgroup'] = '成功从这个分组移除联系人';
$messages['importwait'] = '正在导入，请稍后...';
$messages['importerror'] = '导入失败！所上传的文件不是有效的 vCard 文件。';
$messages['importconfirm'] = '<b>成功导入 $inserted 联系人，$skipped 乎略已存在的联系人</b>:<p><em>$names</em></p>';
$messages['opnotpermitted'] = '不允许的操作';
$messages['nofromaddress'] = '选种的身份中没有邮件地址';
$messages['editorwarning'] = '切换到纯文本编辑器将导致邮件正文中的所有文本格式失效，您确定要这样做吗？';
$messages['httpreceivedencrypterror'] = '发送一个致命的配置错误，请立即联系管理员。<b>您的邮件无法发送.</b>';
$messages['smtpconnerror'] = 'SMTP 错误 ($code):连接服务器失败';
$messages['smtpautherror'] = 'SMTP 错误 ($code): 认证失败';
$messages['smtpfromerror'] = 'SMTP 错误 ($code):添加发件人失败 "$from" ($msg)';
$messages['smtptoerror'] = 'SMTP 错误 ($code): 添加收件人失败 "$to" ($msg)';
$messages['smtprecipientserror'] = 'SMTP 错误: 无法解析收件人列表';
$messages['smtperror'] = 'SMTP 错误: $msg';
$messages['emailformaterror'] = '无效的邮件地址：$email';
$messages['toomanyrecipients'] = '邮件人太多，请减少收件人数量至 $max.';
$messages['maxgroupmembersreached'] = '组员数量超过最大值 $max';
$messages['internalerror'] = '遇到一个内部错误，请重试';

?>
