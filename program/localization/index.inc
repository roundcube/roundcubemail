<?php

/*
 +-----------------------------------------------------------------------+
 | program/localization/index.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005-2008, RoundCube Dev. - Switzerland                 |
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

$rcube_languages = array(
  'sq_AL' => 'Albanian',
  'ar_SA' => 'Arabic',
  'hy_AM' => 'Armenian',
  'az_AZ' => 'Azerbaijani',
  'bs_BA' => 'Bosnian (Serbian Latin)',
  'bg_BG' => 'Bulgarian',
  'ca_ES' => 'Català',
  'cy_GB' => 'Cymraeg',
  'zh_CN' => 'Chinese (Simplified)',
  'zh_TW' => 'Chinese (Traditional)',
  'hr_HR' => 'Croatian (Hrvatski)',
  'cs_CZ' => 'Czech',
  'da_DK' => 'Dansk',
  'de_DE' => 'Deutsch (Deutsch)',
  'de_CH' => 'Deutsch (Schweiz)',
  'en_GB' => 'English (GB)',
  'en_US' => 'English (US)',
  'es_ES' => 'Español',
  'eo'    => 'Esperanto',
  'et_EE' => 'Estonian',
  'eu_ES' => 'Euskara (Basque)',
  'fi_FI' => 'Finnish (Suomi)',
  'nl_BE' => 'Flemish',
  'fr_FR' => 'Français',
  'gl_ES' => 'Galego (Galician)',
  'ka_GE' => 'Georgian (Kartuli)',
  'el_GR' => 'Greek',
  'he_IL' => 'Hebrew',
  'hi_IN' => 'Hindi',
  'hu_HU' => 'Hungarian',
  'is_IS' => 'Icelandic',
  'id_ID' => 'Indonesian',
  'ga_IE' => 'Irish',
  'it_IT' => 'Italiano',
  'ja_JP' => 'Japanese (日本語)',
  'ko_KR' => 'Korean',
  'ku'    => 'Kurdish (Kurmancî)',
  'lv_LV' => 'Latvian',
  'lt_LT' => 'Lithuanian',
  'mk_MK' => 'Macedonian',
  'ms_MY' => 'Malay',
  'nl_NL' => 'Nederlands',
  'ne_NP' => 'Nepali',
  'nb_NO' => 'Norsk (Bokmål)',
  'nn_NO' => 'Norsk (Nynorsk)',
  'fa'    => 'Persian (Farsi)',
  'pl_PL' => 'Polski',
  'pt_BR' => 'Portuguese (Brazilian)',
  'pt_PT' => 'Portuguese (Standard)',
  'ro_RO' => 'Romanian',
  'ru_RU' => 'Русский',
  'sr_CS' => 'Serbian (Cyrillic)',
  'si_LK' => 'Sinhala',
  'sk_SK' => 'Slovak',
  'sl_SI' => 'Slovenian',
  'sv_SE' => 'Swedish (Svenska)',
  'th_TH' => 'Thai',
  'tr_TR' => 'Türkçe',
  'uk_UA' => 'Ukrainian',
  'vi_VN' => 'Vietnamese',
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
