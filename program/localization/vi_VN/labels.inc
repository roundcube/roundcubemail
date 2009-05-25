<?php

/*

 +-----------------------------------------------------------------------+
 | language/vn/labels.inc                                                |
 |                                                                       |
 | Language file of the RoundCube Webmail client                         |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Vinh Bui <bqvinh@buivinh.info>                                |
 +-----------------------------------------------------------------------+

 @version $Id$

*/

$labels = array();

// login page
$labels['welcome']   = 'Xin mời sử dụng $product';
$labels['username']  = 'Tên người dùng';
$labels['password']  = 'Mật khẩu';
$labels['server']    = 'Máy chủ?';
$labels['login']     = 'Đăng nhập';

// taskbar
$labels['logout']   = 'Đăng xuất';
$labels['mail']     = 'E-Mail';
$labels['settings'] = 'Thiết lập';
$labels['addressbook'] = 'Sổ địa chỉ';

// mailbox names
$labels['inbox']  = 'Inbox';
$labels['drafts'] = 'Drafts';
$labels['sent']   = 'Sent';
$labels['trash']  = 'Trash';
$labels['junk']   = 'Junk';

// message listing
$labels['subject'] = 'Tiêu đề';
$labels['from']    = 'Người gửi';
$labels['to']      = 'Người nhận';
$labels['cc']      = 'Đồng nhận';
$labels['bcc']     = 'Đồng nhận (2)';
$labels['replyto'] = 'Trả lời vào';
$labels['date']    = 'Ngày';
$labels['size']    = 'D.Lượng';
$labels['priority'] = 'Ưu tiên';
$labels['organization'] = 'Cơ quan';

// aliases
$labels['reply-to'] = $labels['replyto'];

$labels['mailboxlist'] = 'Thư mục';
$labels['messagesfromto'] = 'Thư $from - $to / $count';
$labels['messagenrof'] = 'Thư $nr / $count';

$labels['moveto']   = 'chuyển đến...';
$labels['download'] = 'tải xuống';

$labels['filename'] = 'Tên file';
$labels['filesize'] = 'Dung lượng';

$labels['preferhtml'] = 'Dùng HTML';
$labels['htmlmessage'] = 'Thư kiểu HTML';
$labels['prettydate'] = 'Hiển thị ngày đầy đủ';

$labels['addtoaddressbook'] = 'Thêm và sổ địa chỉ';

// weekdays short
$labels['sun'] = 'CN';
$labels['mon'] = 'T2';
$labels['tue'] = 'T3';
$labels['wed'] = 'T4';
$labels['thu'] = 'T5';
$labels['fri'] = 'T6';
$labels['sat'] = 'T7';

// weekdays long
$labels['sunday']    = 'Chủ nhật';
$labels['monday']    = 'Thứ hai';
$labels['tuesday']   = 'Thứ ba';
$labels['wednesday'] = 'Thứ tư';
$labels['thursday']  = 'Thứ năm';
$labels['friday']    = 'Thứ sáu';
$labels['saturday']  = 'Thứ bẩy';

$labels['today'] = 'Hôm nay';

// toolbar buttons
$labels['checkmail']        = 'Kiểm tra thư mới';
$labels['writenewmessage']  = 'Soạn thư mới';
$labels['replytomessage']   = 'Trả lời thư';
$labels['replytoallmessage'] = 'Trả lời tất cả';
$labels['forwardmessage']   = 'Chuyển thư';
$labels['deletemessage']    = 'Xóa thư';
$labels['printmessage']     = 'In thư';
$labels['previousmessages'] = 'Hiển thị các thư trước';
$labels['nextmessages']     = 'Hiển thị các thư sau';
$labels['backtolist']       = 'Trở về danh sách thư';
$labels['viewsource']       = 'Hiển thị mã nguồn';

$labels['select'] = 'Chọn';
$labels['all'] = 'Toàn bộ';
$labels['none'] = 'Bỏ chọn';
$labels['unread'] = 'Chưa đọc';

$labels['compact'] = 'Rút gọn';
$labels['empty'] = 'Xóa rỗng';
$labels['purge'] = 'Tối giản';

$labels['quota'] = 'Hạn mức';
$labels['unknown']  = 'không rõ';
$labels['unlimited']  = 'không hạn chế';

$labels['quicksearch']  = 'Tìm nhanh';
$labels['resetsearch']  = 'Xóa bỏ tìm kiếm';

// message compose
$labels['compose']  = 'Soạn thư';
$labels['savemessage']  = 'Lưu thư đang soạn';
$labels['sendmessage']  = 'Gửi thư';
$labels['addattachment']  = 'Gửi kèm';
$labels['charset']  = 'Bộ ký tự';
$labels['returnreceipt'] = 'Báo nhận';

$labels['checkspelling'] = 'Kiểm tra chính tả';
$labels['resumeediting'] = 'Soạn thảo';
$labels['revertto'] = 'Chuyển về';

$labels['attachments'] = 'Gửi kèm';
$labels['upload'] = 'Tải lên';
$labels['close']  = 'Đóng';

$labels['low']     = 'Thấp';
$labels['lowest']  = 'Thấp nhất';
$labels['normal']  = 'Bình thường';
$labels['high']    = 'Khẩn';
$labels['highest'] = 'Thượng khẩn';

$labels['nosubject']  = '(no subject)';
$labels['showimages'] = 'Hiển thị ảnh';

// address boook
$labels['name']      = 'Tên hiển thị';
$labels['firstname'] = 'Tên';
$labels['surname']   = 'Họ';
$labels['email']     = 'E-Mail';

$labels['addcontact'] = 'Thêm mới địa chỉ';
$labels['editcontact'] = 'Sửa địa chỉ cũ';

$labels['edit']   = 'Sửa đổi';
$labels['cancel'] = 'Ngưng';
$labels['save']   = 'Lưu';
$labels['delete'] = 'Xóa';

$labels['newcontact']     = 'Thêm mới địa chỉ liên hệ';
$labels['deletecontact']  = 'Xóa địa chỉ được chọn';
$labels['composeto']      = 'Soạn thư cho';
$labels['contactsfromto'] = 'Địa chỉ từ $from - $to / $count';
$labels['print']          = 'In thư';
$labels['export']         = 'Export';

$labels['previouspage']   = 'Trang trước';
$labels['nextpage']       = 'Trang tiếp';

// LDAP search
$labels['ldapsearch'] = 'LDAP directory search';

$labels['ldappublicsearchname']    = 'Tên địa chỉ';
$labels['ldappublicsearchtype'] = 'Khớp toàn bộ?';
$labels['ldappublicserverselect'] = 'Chọn máy chủ';
$labels['ldappublicsearchfield'] = 'Tìm trên';
$labels['ldappublicsearchform'] = 'Tìm địa chỉ liên hệ';
$labels['ldappublicsearch'] = 'Tìm kiếm';

// settings
$labels['settingsfor']  = 'Thiết lập cho';

$labels['preferences']  = 'Tham số';
$labels['userpreferences']  = 'Tham số người dùng';
$labels['editpreferences']  = 'Sửa đổi';

$labels['identities']  = 'Người dùng';
$labels['manageidentities']  = 'Quản trị người dùng';
$labels['newidentity']  = 'Người dùng mới';

$labels['newitem']  = 'Thêm mới';
$labels['edititem']  = 'Sửa đổi';

$labels['setdefault']  = 'Thiết lập mặc định';
$labels['language']  = 'Ngôn ngữ';
$labels['timezone']  = 'Múi giờ';
$labels['pagesize']  = 'Số dòng trên trang';
$labels['signature'] = 'Chữ ký';
$labels['dstactive']  = 'Giờ mùa hè';

$labels['folder']  = 'Thư mục';
$labels['folders']  = 'Thư mục';
$labels['foldername']  = 'Tên thư mục';
$labels['subscribed']  = 'Cho sử dụng';
$labels['create']  = 'Tạo';
$labels['createfolder']  = 'Tạo thư mục mới';
$labels['rename'] = 'Đổi tên';
$labels['renamefolder'] = 'Đổi tên thư mục';
$labels['deletefolder']  = 'Xóa thư mục';
$labels['managefolders']  = 'Quản trị thư mục';

$labels['sortby'] = 'Sắp xếp';
$labels['sortasc']  = 'Xếp tăng dần';
$labels['sortdesc'] = 'Xếp giảm dần';

?>