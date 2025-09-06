<?php

$labels = array();
$labels['nosignature'] = 'Tidak ada informasi tanda tangan';
$labels['noauthresults'] = 'Tanda tangan ditemukan tapi tak ada kepala hasil auth. Masalah konfigurasi server?';
$labels['signaturepass'] = 'Tanda tangan valid dari domain pengirim. diverifikasi oleh';
$labels['partialpass'] = 'Beberapa tanda tangan tidak valid tapi paling tidak satu valid bagi domain pengirim. diverifikasi oleh';
$labels['thirdparty'] = 'Ditandatangani oleh pihak ke tiga, tanda tangan ada tapi untuk domain yang berbeda dari alamat pengirim. diverifikasi untuk';
$labels['temporaryinvalid'] = 'Tanda tangan (untuk sementara) tidak valid! diverifikasi oleh';
$labels['invalidsignature'] = 'Tanda tangan tidak valid! diverifikasi oleh';

$labels['for'] = 'untuk';
$labels['by'] = 'oleh';

$labels['section_title']  = 'Status Otentikasi';
$labels['column_title']  = 'Hasil Auth.';

$labels['title_enable_column']  = 'Fungsikan Kolom';
$labels['label_enable_column']  = 'Fungsikan kolom hasil otentikasi dalam daftar pesan';
$labels['label_fallback_verifier'] = 'Gunakan verifikator DKIM internal (bila tanda tangan ditemukan tapi tak ada kepala hasil auth.)';

$labels['title_trusted_mtas'] = 'Trusted mta\'s';
$labels['label_trusted_mtas'] = 'Comma separated authserv-id list of mta\'s you trust. Only results from these mta\'s will be shown';

$labels['title_include_status'] = 'Hanya tampilkan status-status berikut';
$labels['label_include_status1']  = 'Tidak ditemukan tanda tangan';
$labels['label_include_status2']  = 'Tanda tangan ditemukan tapi tak ada kepala hasil auth.';
$labels['label_include_status4']  = 'Semua tanda tangan lolos validasi';
$labels['label_include_status8']  = 'Paling tidak satu tanda tangan lolos validasi';
$labels['label_include_status16']  = 'Tanda tangan pihak ke tiga';
$labels['label_include_status32']  = 'Tanda tangan untuk sementara gagal';
$labels['label_include_status64']  = 'Tanda tangan gagal secara permanen';

?>
