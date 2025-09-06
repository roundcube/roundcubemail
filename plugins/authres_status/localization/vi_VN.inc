<?php

$labels = array();
$labels['nosignature'] = 'Không có thông tin chữ ký';
$labels['noauthresults'] = 'Tìm thấy chữ ký nhưng không có kết quả cho phép dùng. Lỗi cấu hình ở máy chủ?';
$labels['signaturepass'] = '(Các) Chữ ký hợp lệ từ tên miền của người gửi. Xác nhận bởi';
$labels['partialpass'] = 'Vài chữ ký không hợp lệ, nhưng có ít nhất một chữ ký hợp lệ cho tên miền của người gửi. Xác nhận bởi';
$labels['thirdparty'] = 'Xác nhận bởi bên thứ ba, chữ ký tồn tại, nhưng cho tên miền khác từ địa chỉ gửi. Xác nhận cho';
$labels['temporaryinvalid'] = 'Chữ ký (tạm thời) không hợp lệ. Xác nhận bởi';
$labels['invalidsignature'] = 'Chữ ký không hợp lệ! Xác nhận bởi';

$labels['for'] = 'cho';
$labels['by'] = 'bởi';

$labels['section_title']  = 'Tình trạng cho phép';
$labels['column_title']  = 'Kết quả cho phép';

$labels['title_enable_column']  = 'Kích hoạt cột';
$labels['label_enable_column']  = 'Kích hoạt cột kết quả cho phép trong danh sách thư';
$labels['label_fallback_verifier'] = 'Sử dụng bộ xác nhận DKIM nội tại (nếu chữ ký tìm thấy nhưng không có tiêu đề kết quả cho phép dùng)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Chỉ hiển thị các trạng thái sau';
$labels['label_include_status1']  = 'Không tìm thấy chữ ký.';
$labels['label_include_status2']  = 'Tìm thấy chữ ký nhưng không có kết quả cho phép dùng.';
$labels['label_include_status4']  = 'Tất cả các chữ ký đều hợp lệ';
$labels['label_include_status8']  = 'Có ít nhất một chữ ký hợp lệ';
$labels['label_include_status16']  = 'Chữ ký của bên thứ ba';
$labels['label_include_status32']  = 'Chữ ký tạm thời lỗi';
$labels['label_include_status64']  = 'Chữ ký lỗi';

?>
