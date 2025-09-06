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

$labels['changepasswd']  = 'Ubah sandi';
$labels['curpasswd']  = 'Sandi saat ini:';
$labels['newpasswd']  = 'Sandi Baru:';
$labels['confpasswd']  = 'Konfirmasi Sandi Baru:';
$messages['nopassword'] = 'Masukkan sandi baru.';
$messages['nocurpassword'] = 'Masukkan sandi saat ini.';
$messages['passwordincorrect'] = 'Sandi saat ini salah.';
$messages['passwordinconsistency'] = 'Sandi tidak cocok, harap coba lagi.';
$messages['crypterror'] = 'Tidak dapat menyimpan sandi baru. Fungsi enkripsi tidak ditemukan.';
$messages['connecterror'] = 'Tidak dapat menyimpan sandi baru. Koneksi error.';
$messages['internalerror'] = 'Tidak dapat menyimpan sandi baru.';
$messages['passwordshort'] = 'Panjang password minimal $length karakter';
$messages['passwordweak'] = 'Sandi harus menyertakan setidaknya satu angka dan satu tanda baca.';
$messages['passwordforbidden'] = 'Sandi mengandung karakter terlarang.';
$messages['firstloginchange'] = 'Ini login pertama Anda. Harap ubah sandi Anda.';
$messages['disablednotice'] = 'Sistem saat ini sedang dalam pemeliharaan dan perubahan sandi tidak mungkin saat ini. Semua mestinya segera kembali normal. Kami mohon maaf untuk ketidaknyamanan ini.';
$messages['passwinhistory'] = 'Kata sandi sudah pernah digunakan';
$messages['samepasswd'] = 'Kata sandi harus berbeda dengan yang sebelumnya';
$messages['passwdexpirewarning'] = 'Peringatan! Masa kata sandi segera berakhir, ganti sebelum $expirationdatetime.';
$messages['passwdexpired'] = 'Kata sandi kadaluarsa, harus diganti sekarang.';
$messages['passwdconstraintviolation'] = 'Kata sandi melanggar aturan. Mungkin kata sandi terlalu lemah.';

$labels['hestia_err_policy_short'] = 'Kata sandi terlalu pendek (panjang minimum: % menit).';
$labels['hestia_err_endpoint_invalid'] = 'Titik akhir Hestia tidak dikonfigurasi atau tidak valid.';
$labels['hestia_err_identity'] = 'Tidak dapat mengurai nama pengguna menjadi petunjuk local@domain%.';
$labels['hestia_err_owner_unknown'] = 'Tidak dapat menentukan pemilik domain untuk %domain.';
$labels['hestia_err_unknown'] = 'Kesalahan yang tidak diketahui dari API Hestia.';
$labels['hestia_err_default'] = 'Hestia mengembalikan kode kesalahan %kode.';
$labels['hestia_err_1'] = 'Tidak ada cukup argumen yang diberikan.';
$labels['hestia_err_2'] = 'Objek atau argumen tidak valid.';
$labels['hestia_err_3'] = 'Objek tidak ada (kotak surat atau domain tidak ditemukan).';
$labels['hestia_err_4'] = 'Objek sudah ada.';
$labels['hestia_err_5'] = 'Objek sudah ditangguhkan.';
$labels['hestia_err_6'] = 'Objek sudah tidak ditangguhkan.';
$labels['hestia_err_7'] = 'Objek yang sedang digunakan; tidak dapat dimodifikasi.';
$labels['hestia_err_8'] = 'Operasi diblokir oleh batas paket.';
$labels['hestia_err_9'] = 'Kata sandi salah atau tidak valid.';
$labels['hestia_err_10'] = 'Izin ditolak oleh Hestia (identitas API tidak memiliki hak atau IP tidak diizinkan).';
$labels['hestia_err_11'] = 'Fitur dinonaktifkan oleh kebijakan sistem.';
$labels['hestia_err_12'] = 'Kesalahan penguraian.';
$labels['hestia_err_13'] = 'Kesalahan disk.';
$labels['hestia_err_14'] = 'Beban server terlalu tinggi.';
$labels['hestia_err_15'] = 'Kesalahan koneksi.';
$labels['hestia_err_16'] = 'Kesalahan FTP.';
$labels['hestia_err_17'] = 'Kesalahan basis data.';
$labels['hestia_err_18'] = 'Kesalahan RRD.';
$labels['hestia_err_19'] = 'Kesalahan pembaruan.';
$labels['hestia_err_20'] = 'Kesalahan restart.';
?>
