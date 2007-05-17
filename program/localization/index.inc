<?php

/*
 +-----------------------------------------------------------------------+
 | program/localization/index.inc                                        |
 |                                                                       |
 | This file is part of the RoundCube Webmail client                     |
 | Copyright (C) 2005, RoundCube Dev, - Switzerland                      |
 | Licensed under the GNU GPL                                            |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide centralized location for keeping track of                   |
 |   available languages                                                 |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+

 $Id$
 
 */
 
 $rcube_languages = array(
	'en_US' => 'English (US)',
	'en_GB' => 'English (GB)',
	'ar'    => 'Arabic',
	'am'    => 'Armenian',
	'bg'    => 'Bulgarian',
	'bs_BA' => 'Bosnian (Bosanski)',
	'zh_TW' => 'Chinese (BIG5)',
	'zh_CN' => 'Chinese (GB2312)',
 	'ca'    => 'Català',
	'cz'    => 'Czech',
	'da'    => 'Dansk',
 	'de_DE' => 'Deutsch (Deutsch)',
 	'de_CH' => 'Deutsch (Schweiz)',
 	'es'    => 'Español',
 	'et_EE' => 'Estonian',
	'eu'    => 'Euskara',
	'fr'    => 'Français', 
	'ga'    => 'Galician',
	'el'    => 'Greek',
	'hi'    => 'Hindi',
	'hr'    => 'Hrvatski',
	'hu'    => 'Hungarian',
	'id_ID' => 'Indonesian',
	'it'    => 'Italiano',
	'ja'    => 'Japanese (日本語)',
	'kr'    => 'Korean',
	'kur_KU' => 'Kurdish (Kurmancî)',
	'lv'    => 'Latvian',
	'lt'    => 'Lithuanian',
	'nl_NL' => 'Nederlands',
	'nl_BE' => 'Flemish',
	'nb_NO' => 'Norsk (bokmål)',
	'nn_NO' => 'Norsk (nynorsk)',
	'fa'    => 'Persian',
	'pl'    => 'Polski',
	'pt_PT' => 'Portuguese (Standard)',
	'pt_BR' => 'Portuguese (Brazilian)',
	'ro'    => 'Romanian',
	'ru'    => 'Russian',
	'fi'    => 'Suomi',
	'se'    => 'Svenska',
	'si'    => 'Sinhala',
	'sl'    => 'Slovenian',
	'sk'    => 'Slovak',
	'sr_latin' => 'Serbian Latin',
	'sr_cyrillic' => 'Serbian Cyrillic',
	'tr'    => 'Turkish',
	'th'    => 'Thai',
	'vn'    => 'Vietnamese'
);

$rcube_language_aliases = array(
	'ee' => 'et_EE',
	'bs' => 'bs_BA',
  'cn' => 'zh_CN',
	'de' => 'de_DE',
	'en' => 'en_US',
	'nl' => 'nl_NL',
	'no' => 'nn_NO',
	'pt' => 'pt_PT',
  'tw' => 'zh_TW'
);

$rcube_charsets = array();


?>
