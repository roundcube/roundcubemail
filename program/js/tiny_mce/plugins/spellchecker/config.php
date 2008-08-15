<?php

	/** start RoundCube specific code */
	
	define('INSTALL_PATH', preg_replace('/program\/js\/.+$/', '', getcwd()));
	require_once INSTALL_PATH . 'program/include/iniset.php';
	
	$rcmail_config = new rcube_config();
	$config['general.engine'] = $rcmail_config->get('spellcheck_engine') == 'pspell' ? 'PSpell' : 'GoogleSpell';
	$config['GoogleSpell.rpc_uri'] = $rcmail_config->get('spellcheck_uri');
	
	/** end RoundCube specific code */

	// General settings
	//$config['general.engine'] = 'GoogleSpell';
	//$config['general.engine'] = 'PSpell';
	//$config['general.engine'] = 'PSpellShell';
	//$config['general.remote_rpc_url'] = 'http://some.other.site/some/url/rpc.php';

	// PSpell settings
	$config['PSpell.mode'] = PSPELL_FAST;
	$config['PSpell.spelling'] = "";
	$config['PSpell.jargon'] = "";
	$config['PSpell.encoding'] = "";

	// PSpellShell settings
	$config['PSpellShell.mode'] = PSPELL_FAST;
	$config['PSpellShell.aspell'] = '/usr/bin/aspell';
	$config['PSpellShell.tmp'] = '/tmp';
	
	// Windows PSpellShell settings
	//$config['PSpellShell.aspell'] = '"c:\Program Files\Aspell\bin\aspell.exe"';
	//$config['PSpellShell.tmp'] = 'c:/temp';
?>
