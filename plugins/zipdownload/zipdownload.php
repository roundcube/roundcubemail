<?php

/**
 * ZipDownload
 *
 * Plugin to allow the download of all message attachments in one zip file
 * and also download of many messages in one go.
 *
 * @requires php_zip extension (including ZipArchive class)
 *
 * @author Philip Weir
 * @author Thomas Bruderli
 * @author Aleksander Machniak
 */
class zipdownload extends rcube_plugin
{
    public $task = 'mail';

    private $charset       = 'ASCII';
    private $names         = [];
    private $default_limit = '50MB';

    // RFC4155: mbox date format
    const MBOX_DATE_FORMAT = 'D M d H:i:s Y';

    /**
     * Plugin initialization
     */
    public function init()
    {
        // check requirements first
        if (!class_exists('ZipArchive', false)) {
            rcmail::raise_error([
                    'code'    => 520,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "php-zip extension is required for the zipdownload plugin"
                ], true, false
            );
            return;
        }

        $rcmail = rcmail::get_instance();

        $this->load_config();
        $this->charset = $rcmail->config->get('zipdownload_charset', RCUBE_CHARSET);

        if ($rcmail->config->get('zipdownload_attachments', 1) > -1 && ($rcmail->action == 'show' || $rcmail->action == 'preview')) {
            $this->add_texts('localization');
            $this->add_hook('template_object_messageattachments', [$this, 'attachment_ziplink']);
        }

        $this->register_action('plugin.zipdownload.attachments', [$this, 'download_attachments']);
        $this->register_action('plugin.zipdownload.messages', [$this, 'download_messages']);

        if (!$rcmail->action && $rcmail->config->get('zipdownload_selection', $this->default_limit)) {
            $this->add_texts('localization');
            $this->download_menu();
        }
    }

    /**
     * Place a link/button after attachments listing to trigger download
     */
    public function attachment_ziplink($p)
    {
        $rcmail = rcmail::get_instance();

        // only show the link if there is more than the configured number of attachments
        if (substr_count($p['content'], '<li') > $rcmail->config->get('zipdownload_attachments', 1)) {
            $href = $rcmail->url([
                    '_action' => 'plugin.zipdownload.attachments',
                    '_mbox'   => $rcmail->output->get_env('mailbox'),
                    '_uid'    => $rcmail->output->get_env('uid'),
                ],
                false, false, true
            );

            // append the link to the attachments list
            $p['content'] .= html::a(
                ['href' => $href, 'class' => 'button zipdownload'],
                rcube::Q($this->gettext('downloadall'))
            );

            $this->include_stylesheet($this->local_skin_path() . '/zipdownload.css');
        }

        return $p;
    }

    /**
     * Adds download options menu to the page
     */
    public function download_menu()
    {
        $this->include_script('zipdownload.js');
        $this->add_label('download');

        $rcmail  = rcmail::get_instance();
        $menu    = [];
        $ul_attr = [
            'role' => 'menu',
            'aria-labelledby' => 'aria-label-zipdownloadmenu',
            'class' => 'toolbarmenu menu',
        ];

        foreach (['eml', 'mbox', 'maildir'] as $type) {
            $menu[] = html::tag('li', null, $rcmail->output->button([
                    'command'  => "download-$type",
                    'label'    => "zipdownload.download$type",
                    'class'    => "download $type disabled",
                    'classact' => "download $type active",
                    'type'     => 'link',
                ])
            );
        }

        $rcmail->output->add_footer(
            html::div(['id' => 'zipdownload-menu', 'class' => 'popupmenu', 'aria-hidden' => 'true'],
                html::tag('h2', ['class' => 'voice', 'id' => 'aria-label-zipdownloadmenu'], "Message Download Options Menu")
                . html::tag('ul', $ul_attr, implode('', $menu))
            )
        );
    }

    /**
     * Handler for attachment download action
     */
    public function download_attachments()
    {
        $rcmail = rcmail::get_instance();

        // require CSRF protected request
        $rcmail->request_security_check(rcube_utils::INPUT_GET);

        $tmpfname  = rcube_utils::temp_filename('zipdownload');
        $tempfiles = [$tmpfname];
        $message   = new rcube_message(rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET));

        // open zip file
        $zip = new ZipArchive();
        $zip->open($tmpfname, ZIPARCHIVE::OVERWRITE);

        foreach ($message->attachments as $part) {
            $pid       = $part->mime_id;
            $part      = $message->mime_parts[$pid];
            $disp_name = $this->_create_displayname($part);

            $tmpfn       = rcube_utils::temp_filename('zipattach');
            $tmpfp       = fopen($tmpfn, 'w');
            $tempfiles[] = $tmpfn;

            $message->get_part_body($part->mime_id, false, 0, $tmpfp);
            $zip->addFile($tmpfn, $disp_name);
            fclose($tmpfp);
        }

        $zip->close();

        $filename = ($this->_filename_from_subject($message->subject) ?: 'attachments') . '.zip';

        $this->_deliver_zipfile($tmpfname, $filename);

        // delete temporary files from disk
        foreach ($tempfiles as $tmpfn) {
            unlink($tmpfn);
        }

        exit;
    }

    /**
     * Handler for message download action
     */
    public function download_messages()
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->config->get('zipdownload_selection', $this->default_limit)) {
            $messageset = rcmail::get_uids(null, null, $multi, rcube_utils::INPUT_POST);
            if (count($messageset)) {
                $this->_download_messages($messageset);
            }
        }
    }

    /**
     * Create and get display name of attachment part to add on zip file
     *
     * @param rcube_message_part $part Part of attachment on message
     *
     * @return string Display name of attachment part
     */
    private function _create_displayname($part)
    {
        $rcmail   = rcmail::get_instance();
        $filename = $part->filename;

        if ($filename === null || $filename === '') {
            $ext      = array_first((array) rcube_mime::get_mime_extensions($part->mimetype));
            $filename = $rcmail->gettext('messagepart') . ' ' . $part->mime_id;
            if ($ext) {
                $filename .= '.' . $ext;
            }
        }

        $displayname = $this->_convert_filename($filename);

        /**
         * Adding a number before dot of extension on a name of file with same name on zip
         * Ext: attach(1).txt on attach filename that has a attach.txt filename on same zip
         */
        if (isset($this->names[$displayname])) {
            list($filename, $ext) = preg_split("/\.(?=[^\.]*$)/", $displayname);
            $displayname = $filename . '(' . ($this->names[$displayname]++) . ').' . $ext;
            $this->names[$displayname] = 1;
        }
        else {
            $this->names[$displayname] = 1;
        }

        return $displayname;
    }

    /**
     * Helper method to packs all the given messages into a zip archive
     *
     * @param array $messageset List of message UIDs to download
     */
    private function _download_messages($messageset)
    {
        $this->add_texts('localization');

        $rcmail    = rcmail::get_instance();
        $imap      = $rcmail->get_storage();
        $mode      = rcube_utils::get_input_string('_mode', rcube_utils::INPUT_POST);
        $limit     = $rcmail->config->get('zipdownload_selection', $this->default_limit);
        $limit     = $limit !== true ? parse_bytes($limit) : -1;
        $delimiter = $imap->get_hierarchy_delimiter();
        $tmpfname  = rcube_utils::temp_filename('zipdownload');
        $tempfiles = [$tmpfname];
        $folders   = count($messageset) > 1;
        $timezone  = new DateTimeZone('UTC');
        $messages  = [];
        $size      = 0;

        // collect messages metadata (and check size limit)
        foreach ($messageset as $mbox => $uids) {
            $imap->set_folder($mbox);

            if ($uids === '*') {
                $index = $imap->index($mbox, null, null, true);
                $uids  = $index->get();
            }

            foreach ($uids as $uid) {
                $headers = $imap->get_message_headers($uid);

                if ($mode == 'mbox') {
                    // Sender address
                    $from = rcube_mime::decode_address_list($headers->from, null, true, $headers->charset, true);
                    $from = array_shift($from);
                    $from = preg_replace('/\s/', '-', $from);

                    // Received (internal) date
                    $date = rcube_utils::anytodatetime($headers->internaldate, $timezone);
                    if ($date) {
                        $date = $date->format(self::MBOX_DATE_FORMAT);
                    }

                    // Mbox format header (RFC4155)
                    $header = sprintf("From %s %s\r\n",
                        $from ?: 'MAILER-DAEMON',
                        $date ?: ''
                    );

                    $messages[$uid . ':' . $mbox] = $header;
                }
                else { // maildir
                    $subject = rcube_mime::decode_header($headers->subject, $headers->charset);
                    $subject = $this->_filename_from_subject(mb_substr($subject, 0, 16));
                    $subject = $this->_convert_filename($subject);

                    $path      = $folders ? str_replace($delimiter, '/', $mbox) . '/' : '';
                    $disp_name = $path . $uid . ($subject ? " $subject" : '') . '.eml';

                    $messages[$uid . ':' . $mbox] = $disp_name;
                }

                $size += $headers->size;

                if ($limit > 0 && $size > $limit) {
                    unlink($tmpfname);

                    $msg = $this->gettext([
                            'name' => 'sizelimiterror',
                            'vars' => ['$size' => rcmail_action::show_bytes($limit)]
                    ]);

                    $rcmail->output->show_message($msg, 'error');
                    $rcmail->output->send('iframe');
                    exit;
                }
            }
        }

        if ($mode == 'mbox') {
            $tmpfp = fopen($tmpfname . '.mbox', 'w');
            if (!$tmpfp) {
                exit;
            }
        }

        // open zip file
        $zip = new ZipArchive();
        $zip->open($tmpfname, ZIPARCHIVE::OVERWRITE);

        $last_key = array_key_last($messages);
        foreach ($messages as $key => $value) {
            list($uid, $mbox) = explode(':', $key, 2);
            $imap->set_folder($mbox);

            if (!empty($tmpfp)) {
                fwrite($tmpfp, $value);

                // Use stream filter to quote "From " in the message body
                stream_filter_register('mbox_filter', 'zipdownload_mbox_filter');
                $filter = stream_filter_append($tmpfp, 'mbox_filter');
                $imap->get_raw_body($uid, $tmpfp);
                stream_filter_remove($filter);

                // Make sure the delimiter is a double \r\n
                $fstat = fstat($tmpfp);
                if (stream_get_contents($tmpfp, 2, $fstat['size'] - 2) != "\r\n") {
                    fwrite($tmpfp, "\r\n");
                }
                if ($key != $last_key) {
                    fwrite($tmpfp, "\r\n");
                }
            }
            else { // maildir
                $tmpfn = rcube_utils::temp_filename('zipmessage');
                $fp = fopen($tmpfn, 'w');
                $imap->get_raw_body($uid, $fp);
                $tempfiles[] = $tmpfn;
                fclose($fp);
                $zip->addFile($tmpfn, $value);
            }
        }

        $filename = $folders ? 'messages' : $imap->get_folder();

        if (!empty($tmpfp)) {
            $tempfiles[] = $tmpfname . '.mbox';
            fclose($tmpfp);
            $zip->addFile($tmpfname . '.mbox', $filename . '.mbox');
        }

        $zip->close();

        $this->_deliver_zipfile($tmpfname, $filename . '.zip');

        // delete temporary files from disk
        foreach ($tempfiles as $tmpfn) {
            unlink($tmpfn);
        }

        exit;
    }

    /**
     * Helper method to send the zip archive to the browser
     */
    private function _deliver_zipfile($tmpfname, $filename)
    {
        $rcmail = rcmail::get_instance();

        $rcmail->output->download_headers($filename, ['length' => filesize($tmpfname)]);

        readfile($tmpfname);
    }

    /**
     * Helper function to convert filenames to the configured charset
     */
    private function _convert_filename($str)
    {
        $str = strtr($str, [':' => '', '/' => '-']);

        return rcube_charset::convert($str, RCUBE_CHARSET, $this->charset);
    }

    /**
     * Helper function to convert message subject into filename
     */
    private function _filename_from_subject($str)
    {
        $str = preg_replace('/[\t\n\r\0\x0B]+\s*/', ' ', $str);

        return trim($str, " ./_");
    }
}

class zipdownload_mbox_filter extends php_user_filter
{
    #[ReturnTypeWillChange]
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            // messages are read line by line
            if (preg_match('/^>*From /', $bucket->data)) {
                $bucket->data     = '>' . $bucket->data;
                $bucket->datalen += 1;
            }

            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
