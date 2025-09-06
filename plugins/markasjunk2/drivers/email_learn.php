<?php

/**
 * Email learn driver
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

class markasjunk2_email_learn
{
	public function spam($uids, $mbox)
	{
		$this->_do_emaillearn($uids, true);
	}

	public function ham($uids, $mbox)
	{
		$this->_do_emaillearn($uids, false);
	}

	private function _do_emaillearn($uids, $spam)
	{
		$rcmail = rcube::get_instance();
		$identity_arr = $rcmail->user->get_identity();
		$from = $identity_arr['email'];

		if ($spam)
			$mailto = $rcmail->config->get('markasjunk2_email_spam');
		else
			$mailto = $rcmail->config->get('markasjunk2_email_ham');

		$mailto = str_replace('%u', $_SESSION['username'], $mailto);
		$mailto = str_replace('%l', $rcmail->user->get_username('local'), $mailto);
		$mailto = str_replace('%d', $rcmail->user->get_username('domain'), $mailto);
		$mailto = str_replace('%i', $from, $mailto);

		if (!$mailto)
			return;

		$message_charset = $rcmail->output->get_charset();
		// chose transfer encoding
		$charset_7bit = array('ASCII', 'ISO-2022-JP', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-15');
		$transfer_encoding = in_array(strtoupper($message_charset), $charset_7bit) ? '7bit' : '8bit';

		$temp_dir = realpath($rcmail->config->get('temp_dir'));

		$subject = $rcmail->config->get('markasjunk2_email_subject');
		$subject = str_replace('%u', $_SESSION['username'], $subject);
		$subject = str_replace('%t', ($spam) ? 'spam' : 'ham', $subject);
		$subject = str_replace('%l', $rcmail->user->get_username('local'), $subject);
		$subject = str_replace('%d', $rcmail->user->get_username('domain'), $subject);

		// compose headers array
		$headers = array();
		$headers['Date'] = date('r');
		$headers['From'] = format_email_recipient($identity_arr['email'], $identity_arr['name']);
		$headers['To'] = $mailto;
		$headers['Subject'] = $subject;

		foreach ($uids as $uid) {
			$MESSAGE = new rcube_message($uid);

			// set message charset as default
			if (!empty($MESSAGE->headers->charset))
				$rcmail->storage->set_charset($MESSAGE->headers->charset);

			$MAIL_MIME = new Mail_mime($rcmail->config->header_delimiter());

			if ($rcmail->config->get('markasjunk2_email_attach', false)) {
				$tmpPath = tempnam($temp_dir, 'rcmMarkASJunk2');

				// send mail as attachment
				$MAIL_MIME->setTXTBody(($spam ? 'Spam' : 'Ham'). ' report from ' . $rcmail->config->get('product_name'), false, true);

				$raw_message = $rcmail->storage->get_raw_body($uid);
				$subject = $MESSAGE->get_header('subject');

				if (isset($subject) && $subject !="")
					$disp_name = $subject . ".eml";
				else
					$disp_name = "message_rfc822.eml";

				if (file_put_contents($tmpPath, $raw_message)) {
					$MAIL_MIME->addAttachment($tmpPath, "message/rfc822", $disp_name, true,
						$transfer_encoding, 'attachment', '', '', '',
						$rcmail->config->get('mime_param_folding') ? 'quoted-printable' : NULL,
						$rcmail->config->get('mime_param_folding') == 2 ? 'quoted-printable' : NULL,
						'', RCUBE_CHARSET
					);
				}

				// encoding settings for mail composing
				$MAIL_MIME->setParam('text_encoding', $transfer_encoding);
				$MAIL_MIME->setParam('html_encoding', 'quoted-printable');
				$MAIL_MIME->setParam('head_encoding', 'quoted-printable');
				$MAIL_MIME->setParam('head_charset', $message_charset);
				$MAIL_MIME->setParam('html_charset', $message_charset);
				$MAIL_MIME->setParam('text_charset', $message_charset);

				// pass headers to message object
				$MAIL_MIME->headers($headers);
			}
			else {
				$headers['Resent-From'] = $headers['From'];
				$headers['Resent-Date'] = $headers['Date'];
				$headers['Date'] = $MESSAGE->headers->date;
				$headers['From'] = $MESSAGE->headers->from;
				$headers['Subject'] = $MESSAGE->headers->subject;
				$MAIL_MIME->headers($headers);

				if ($MESSAGE->has_html_part()) {
					$body = $MESSAGE->first_html_part();
					$MAIL_MIME->setHTMLBody($body);
				}

				$body = $MESSAGE->first_text_part();
				$MAIL_MIME->setTXTBody($body, false, true);

				foreach ($MESSAGE->attachments as $attachment) {
					$MAIL_MIME->addAttachment(
						$MESSAGE->get_part_body($attachment->mime_id, true),
						$attachment->mimetype,
						$attachment->filename,
						false,
						$attachment->encoding,
						$attachment->disposition,
						'', $attachment->charset
					);
				}

				foreach ($MESSAGE->mime_parts as $attachment) {
					if (!empty($attachment->content_id)) {
						// covert CID to Mail_MIME format
						$attachment->content_id = str_replace('<', '', $attachment->content_id);
						$attachment->content_id = str_replace('>', '', $attachment->content_id);

						if (empty($attachment->filename))
							$attachment->filename = $attachment->content_id;

						$message_body = $MAIL_MIME->getHTMLBody();
						$dispurl = 'cid:' . $attachment->content_id;
						$message_body = str_replace($dispurl, $attachment->filename, $message_body);
						$MAIL_MIME->setHTMLBody($message_body);

						$MAIL_MIME->addHTMLImage(
							$MESSAGE->get_part_body($attachment->mime_id, true),
							$attachment->mimetype,
							$attachment->filename,
							false
						);
					}
				}

				// encoding settings for mail composing
				$MAIL_MIME->setParam('head_encoding', $MESSAGE->headers->encoding);
				$MAIL_MIME->setParam('head_charset', $MESSAGE->headers->charset);

				foreach ($MESSAGE->mime_parts as $mime_id => $part) {
					$mimetype = strtolower($part->ctype_primary . '/' . $part->ctype_secondary);

					if ($mimetype == 'text/html') {
						$MAIL_MIME->setParam('text_encoding', $part->encoding);
						$MAIL_MIME->setParam('html_charset', $part->charset);
					}
					else if ($mimetype == 'text/plain') {
						$MAIL_MIME->setParam('html_encoding', $part->encoding);
						$MAIL_MIME->setParam('text_charset', $part->charset);
					}
				}
			}

			$rcmail->deliver_message($MAIL_MIME, $from, $mailto, $smtp_error, $body_file);

			// clean up
			if (file_exists($tmpPath))
				unlink($tmpPath);

			if ($rcmail->config->get('markasjunk2_debug')) {
				if ($spam)
					rcube::write_log('markasjunk2', $uid . ' SPAM ' . $mailto . ' (' . $subject . ')');
				else
					rcube::write_log('markasjunk2', $uid . ' HAM ' . $mailto . ' (' . $subject . ')');

				if ($smtp_error['vars'])
					rcube::write_log('markasjunk2', $smtp_error['vars']);
			}
		}
	}
}

?>