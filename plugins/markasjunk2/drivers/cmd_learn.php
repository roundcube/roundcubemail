<?php

/**
 * Command line learn driver
 * @version 2.0
 * @author Philip Weir
 * Patched by Julien Vehent to support DSPAM
 * Enhanced support for DSPAM by Stevan Bajic <stevan@bajic.ch>
 *
 * Copyright (C) 2009-2014 Philip Weir
 *
 * This driver is part of the MarkASJunk2 plugin for Roundcube.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Roundcube. If not, see http://www.gnu.org/licenses/.
 */

class markasjunk2_cmd_learn
{
	public function spam($uids, $mbox)
	{
		$this->_do_salearn($uids, true);
	}

	public function ham($uids, $mbox)
	{
		$this->_do_salearn($uids, false);
	}

	private function _do_salearn($uids, $spam)
	{
		$rcmail = rcube::get_instance();
		$temp_dir = realpath($rcmail->config->get('temp_dir'));

		if ($spam)
			$command = $rcmail->config->get('markasjunk2_spam_cmd');
		else
			$command = $rcmail->config->get('markasjunk2_ham_cmd');

		if (!$command)
			return;

		$command = str_replace('%u', $_SESSION['username'], $command);
		$command = str_replace('%l', $rcmail->user->get_username('local'), $command);
		$command = str_replace('%d', $rcmail->user->get_username('domain'), $command);
		if (preg_match('/%i/', $command)) {
			$identity_arr = $rcmail->user->get_identity();
			$command = str_replace('%i', $identity_arr['email'], $command);
		}

		foreach ($uids as $uid) {
			// reset command for next message
			$tmp_command = $command;

			// get DSPAM signature from header (if %xds macro is used)
			if (preg_match('/%xds/', $command)) {
				if (preg_match('/^X\-DSPAM\-Signature:\s+((\d+,)?([a-f\d]+))\s*$/im', $rcmail->storage->get_raw_headers($uid), $dspam_signature))
					$tmp_command = str_replace('%xds', $dspam_signature[1], $tmp_command);
				else
					continue; // no DSPAM signature found in headers -> continue with next uid/message
			}

			if (preg_match('/%f/', $command)) {
				$tmpfname = tempnam($temp_dir, 'rcmSALearn');
				file_put_contents($tmpfname, $rcmail->storage->get_raw_body($uid));
				$tmp_command = str_replace('%f', $tmpfname, $tmp_command);
			}

			exec($tmp_command, $output);

			if ($rcmail->config->get('markasjunk2_debug')) {
				rcube::write_log('markasjunk2', $tmp_command);
				rcube::write_log('markasjunk2', $output);
			}

			if (preg_match('/%f/', $command))
				unlink($tmpfname);

			$output = '';
		}
	}
}

?>