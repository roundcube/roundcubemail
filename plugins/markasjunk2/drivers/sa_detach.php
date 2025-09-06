<?php

/**
 * SpamAssassin detach ham driver
 * @version 2.0
 * @author Philip Weir
 *
 * Copyright (C) 2011-2014 Philip Weir
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

class markasjunk2_sa_detach
{
	public function spam($uids, $mbox)
	{
		// do nothing
	}

	public function ham(&$uids, $mbox)
	{
		$rcmail = rcube::get_instance();
		$storage = $rcmail->storage;

		$new_uids = array();
		foreach ($uids as $uid) {
			$saved = false;
			$message = new rcube_message($uid);

			if (sizeof($message->attachments) > 0) {
				foreach ($message->attachments as $part) {
					if ($part->ctype_primary == 'message' && $part->ctype_secondary == 'rfc822' && $part->ctype_parameters['x-spam-type'] == 'original') {
						$orig_message_raw = $message->get_part_body($part->mime_id);
						$saved = $storage->save_message($mbox, $orig_message_raw);

						if ($saved !== false) {
							$rcmail->output->command('rcmail_markasjunk2_move', null, $uid);
							array_push($new_uids, $saved);
						}
					}
				}
			}
		}

		if (sizeof($new_uids) > 0)
			$uids = $new_uids;
	}
}

?>