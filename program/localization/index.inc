<?php

/*
 +-----------------------------------------------------------------------+
 | program/localization/index.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2009, RoundCube Dev. - Switzerland                 |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide a centralized location table                                |
 |   for keeping track of available languages                            |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$

*/

// langage codes according to ISO 639-1
// country codes according to ISO 3166-1 (Alpha-2)
// original names from http://www.mediaglyphs.org/mg/p/langnames.html

$rcube_languages = array(
  'sq_AL' => 'Albanian (Shqip)',
  'ar_SA' => 'Arabic (العربية)',
  'hy_AM' => 'Armenian (Հայերեն)',
  'ast'	  => 'Asturiana (Asturianu)',
  'az_AZ' => 'Azerbaijani (Azəri)',
  'eu_ES' => 'Basque (Euskara)',
  'bn_BD' => 'Bengali (বাংলা)',
  'bs_BA' => 'Bosnian (Bošnjački)',
  'br'	  => 'Breton (Brezhoneg)',
  'bg_BG' => 'Bulgarian (Български)',
  'ca_ES' => 'Catalan (Català)',
  'zh_CN' => 'Chinese (普通话)',
  'zh_TW' => 'Chinese (國語)',
  'hr_HR' => 'Croatian (Hrvatski)',
  'cy_GB' => 'Cymric (Cymraeg)',
  'cs_CZ' => 'Czech (Česky)',
  'da_DK' => 'Danish (Dansk)',
  'fa_AF' => 'Dari (ﻯﺭﺩ)',
  'de_DE' => 'Deutsch (Deutsch)',
  'de_CH' => 'Deutsch (Schweiz)',
  'nl_NL' => 'Dutch (Nederlands)',
  'en_GB' => 'English (GB)',
  'en_US' => 'English (US)',
  'eo'    => 'Esperanto',
  'et_EE' => 'Estonian (Eesti)',
  'fi_FI' => 'Finnish (Suomi)',
  'nl_BE' => 'Flemish (Vlaams)',
  'fr_FR' => 'French (Français)',
  'gl_ES' => 'Galician (Galego)',
  'ka_GE' => 'Georgian (ქართული)',
  'el_GR' => 'Greek (Ελληνικά)',
  'he_IL' => 'Hebrew (עברית)',
  'hi_IN' => 'Hindi (हिनदी)',
  'hu_HU' => 'Hungarian (Magyar)',
  'is_IS' => 'Icelandic (Íslenska)',
  'id_ID' => 'Indonesian (Bahasa Indonesia)',
  'ga_IE' => 'Irish (Gaedhilge)',
  'it_IT' => 'Italian (Italiano)',
  'ja_JP' => 'Japanese (日本語)',
  'ko_KR' => 'Korean (한국어)',
  'ku'    => 'Kurdish (Kurmancî)',
  'lv_LV' => 'Latvian (Latviešu)',
  'lt_LT' => 'Lithuanian (Lietuviškai)',
  'mk_MK' => 'Macedonian (Македонски)',
  'ms_MY' => 'Malay (Bahasa Melayu)',
  'mr_IN' => 'Marathi (मराठी)',
  'ne_NP' => 'Nepali (नेपाली)',
  'nb_NO' => 'Norwegian (Bokmål)',
  'nn_NO' => 'Norwegian (Nynorsk)',
  'ps' 	  => 'Pashto',
  'fa'    => 'Persian (دری)',
  'pl_PL' => 'Polish (Polski)',
  'pt_BR' => 'Portuguese (Brasil)',
  'pt_PT' => 'Portuguese (Português)',
  'ro_RO' => 'Romanian (Româneşte)',
  'ru_RU' => 'Russian (Русский)',
  'sr_CS' => 'Serbian (Српски)',
  'si_LK' => 'Sinhalese (සිංහල)',
  'sk_SK' => 'Slovak (Slovenčina)',
  'sl_SI' => 'Slovenian (Slovenščina)',
  'es_AR' => 'Spanish (Argentina)',
  'es_ES' => 'Spanish (Español)',
  'sv_SE' => 'Swedish (Svenska)',
  'th_TH' => 'Thai (ไทย)',
  'tr_TR' => 'Turkish (Türkçe)',
  'uk_UA' => 'Ukrainian (Українська)',
  'vi_VN' => 'Vietnamese (Tiếng Việt)',
);

$rcube_language_aliases = array(
  'am' => 'hy_AM',
  'ar' => 'ar_SA',
  'az' => 'az_AZ',
  'bg' => 'bg_BG',
  'bs' => 'bs_BA',
  'ca' => 'ca_ES',
  'cn' => 'zh_CN',
  'cs' => 'cs_CZ',
  'cz' => 'cs_CZ',
  'da' => 'da_DK',
  'de' => 'de_DE',
  'ee' => 'et_EE',
  'el' => 'el_GR',
  'en' => 'en_US',
  'eu' => 'eu_ES',
  'ga' => 'ga_IE',
  'ge' => 'ka_GE',
  'gl' => 'gl_ES',
  'he' => 'he_IL',
  'hi' => 'hi_IN',
  'hr' => 'hr_HR',
  'ja' => 'ja_JP',
  'ko' => 'ko_KR',
  'kr' => 'ko_KR',
  'ne' => 'ne_NP',
  'no' => 'nn_NO',
  'ms' => 'ms_MY',
  'mr' => 'mr_IN',
  'pl' => 'pl_PL',
  'tw' => 'zh_TW',
  'si' => 'si_LK',
  'sl' => 'sl_SI',
  'sr' => 'sr_CS',
  'sr_cyrillic' => 'sr_CS',
  'sr_latin' => 'bs_BA',
  'se' => 'sv_SE',
  'sv' => 'sv_SE',
  'uk' => 'uk_UA',
  'vn' => 'vi_VN',
  'vi' => 'vi_VN',
  'zh' => 'zh_CN',
);

?>
