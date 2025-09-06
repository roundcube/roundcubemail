<?php

/**
 * Copy spam/ham messages to a direcotry for learning later
 * @version 2.0
 * @author Philip Weir
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

class markasjunk2_dir_learn
{
	public function spam($uids, $mbox)
	{
		$this->_do_messagemove($uids, true);
	}

	public function ham($uids, $mbox)
	{
		$this->_do_messagemove($uids, false);
	}

	private function _do_messagemove($uids, $spam)
	{
	    $rcmail = rcube::get_instance();

		if ($spam)
			$dest_dir = unslashify($rcmail->config->get('markasjunk2_spam_dir'));
		else
			$dest_dir = unslashify($rcmail->config->get('markasjunk2_ham_dir'));

		if (!$dest_dir)
			return;

		$filename = $rcmail->config->get('markasjunk2_filename');
		$filename = str_replace('%u', $_SESSION['username'], $filename);
		$filename = str_replace('%t', ($spam) ? 'spam' : 'ham', $filename);
		$filename = str_replace('%l', $rcmail->user->get_username('local'), $filename);
		$filename = str_replace('%d', $rcmail->user->get_username('domain'), $filename);

		foreach ($uids as $uid) {
			$tmpfname = tempnam($dest_dir, $filename);
			file_put_contents($tmpfname, $rcmail->storage->get_raw_body($uid));

			if ($rcmail->config->get('markasjunk2_debug'))
				rcube::write_log('markasjunk2', $tmpfname);
		}
	}
}

?>