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
	'bg'    => 'Bulgarian',
	'bs_BA' => 'Bosnian (Bosanski)',
	'tw'    => 'Chinese (BIG5)',
	'cn'    => 'Chinese (GB2312)',
	'cz'    => 'Czech',
 	'ca'    => 'Català',
	'da'    => 'Dansk',
 	'de_DE' => 'Deutsch (Deutsch)',
 	'de_CH' => 'Deutsch (Schweiz)',
 	'es'    => 'Español',
 	'ee'    => 'Estonian',
	'eu'    => 'Euskara',
	'fr'    => 'Français', 
	'ga'    => 'Galician',
	'el'    => 'Greek',
	'hr'	=> 'Hrvatski',
	'hu'    => 'Hungarian',
	'it'    => 'Italiano',
	'ja'    => 'Japanese (日本語)',
	'kr'    => 'Korean',
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
	'si'    => 'Slovenian',
	'sk'    => 'Slovak',
	'tr'    => 'Turkish',
	'vn'    => 'Vietnamese'
);

$rcube_language_aliases = array(
	'et_EE' => 'ee',
	'bs' => 'bs_BA',
	'de' => 'de_DE',
	'en' => 'en_US',
	'nl' => 'nl_NL',
	'no' => 'nn_NO',
	'pt' => 'pt_PT'
);

$rcube_charsets = array();


?>