<?php

/**
 * ZipDownload
 *
 * Plugin to allow the download of all message attachments in one zip file
 * and downloading of many messages in one go.
 *
 * @version 3.1
 * @requires php_zip extension (including ZipArchive class)
 * @author Philip Weir
 * @author Thomas Bruderli
 * @author Aleksander Machniak
 */
class zipdownload extends rcube_plugin
{
    public $task = 'mail';
    private $charset = 'ASCII';

    private $names = [];

    /**
     * Plugin initialization
     */
    public function init()
    {
        // check requirements first
        if (!class_exists('ZipArchive', false)) {
            rcmail::raise_error(array(
                'code'    => 520,
                'file'    => __FILE__,
                'line'    => __LINE__,
                'message' => "php_zip extension is required for the zipdownload plugin"), true, false);
            return;
        }

        $rcmail = rcmail::get_instance();

        $this->load_config();
        $this->charset = $rcmail->config->get('zipdownload_charset', RCUBE_CHARSET);
        $this->add_texts('localization');

        if ($rcmail->config->get('zipdownload_attachments', 1) > -1 && ($rcmail->action == 'show' || $rcmail->action == 'preview')) {
            $this->add_hook('template_object_messageattachments', array($this, 'attachment_ziplink'));
        }

        $this->register_action('plugin.zipdownload.attachments', array($this, 'download_attachments'));
        $this->register_action('plugin.zipdownload.messages', array($this, 'download_messages'));

        if (!$rcmail->action && $rcmail->config->get('zipdownload_selection')) {
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
            $href = $rcmail->url(array(
                '_action' => 'plugin.zipdownload.attachments',
                '_mbox'   => $rcmail->output->env['mailbox'],
                '_uid'    => $rcmail->output->env['uid'],
            ), false, false, true);

            $link = html::a(array('href' => $href, 'class' => 'button zipdownload'),
                rcube::Q($this->gettext('downloadall'))
            );

            // append link to attachments list, slightly different in some skins
            switch (rcmail::get_instance()->config->get('skin')) {
                case 'classic':
                    $p['content'] = str_replace('</ul>', html::tag('li', array('class' => 'zipdownload'), $link) . '</ul>', $p['content']);
                    break;

                default:
                    $p['content'] .= $link;
                    break;
            }

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
        $menu    = array();
        $ul_attr = array('role' => 'menu', 'aria-labelledby' => 'aria-label-zipdownloadmenu');
        if ($rcmail->config->get('skin') != 'classic') {
            $ul_attr['class'] = 'toolbarmenu';
        }

        foreach (array('eml', 'mbox', 'maildir') as $type) {
            $menu[] = html::tag('li', null, $rcmail->output->button(array(
                    'command'  => "download-$type",
                    'label'    => "zipdownload.download$type",
                    'classact' => 'active',
            )));
        }

        $rcmail->output->add_footer(html::div(array('id' => 'zipdownload-menu', 'class' => 'popupmenu', 'aria-hidden' => 'true'),
            html::tag('h2', array('class' => 'voice', 'id' => 'aria-label-zipdownloadmenu'), "Message Download Options Menu") .
            html::tag('ul', $ul_attr, implode('', $menu))));
    }

    /**
     * Handler for attachment download action
     */
    public function download_attachments()
    {
        $rcmail = rcmail::get_instance();

        // require CSRF protected request
        $rcmail->request_security_check(rcube_utils::INPUT_GET);

        $imap      = $rcmail->get_storage();
        $temp_dir  = $rcmail->config->get('temp_dir');
        $tmpfname  = tempnam($temp_dir, 'zipdownload');
        $tempfiles = array($tmpfname);
        $message   = new rcube_message(rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GET));

        // open zip file
        $zip = new ZipArchive();
        $zip->open($tmpfname, ZIPARCHIVE::OVERWRITE);

        foreach ($message->attachments as $part) {
            $pid      = $part->mime_id;
            $part     = $message->mime_parts[$pid];
            $disp_name = $this->_create_displayname($part->filename);

            $tmpfn       = tempnam($temp_dir, 'zipattach');
            $tmpfp       = fopen($tmpfn, 'w');
            $tempfiles[] = $tmpfn;

            $message->get_part_body($part->mime_id, false, 0, $tmpfp);
            $zip->addFile($tmpfn, $disp_name);
            fclose($tmpfp);
        }

        $zip->close();

        $filename = ($message->subject ?: 'roundcube') . '.zip';
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

        if ($rcmail->config->get('zipdownload_selection') && !empty($_POST['_uid'])) {
            $messageset = rcmail::get_uids();
            if (sizeof($messageset)) {
                $this->_download_messages($messageset);
            }
        }
    }
    
    
    /**
     * Create and get display name of attachment part to add on zip file
     *
     * @param $part stdClass Part of attachment on message
     * @param $pid string Part Id of attachment
     *
     * @return string Display name of attachment part
     */
    private function _create_displayname($part, $pid)
    {
        $rcmail    = rcmail::get_instance();
        $filename = $part->filename;

        if ($filename === null || $filename === '') {
            $ext      = (array) rcube_mime::get_mime_extensions($part->mimetype);
            $ext      = array_shift($ext);
            $filename = $rcmail->gettext('messagepart') . ' ' . $pid;
            if ($ext) {
                $filename .= '.' . $ext;
            }
        }

        $displayname   = $this->_convert_filename($filename);
        /**
         * Adding a number before dot of extension on a name of file with same name on zip
         * Ext: attach(1).txt on attach filename that has a attach.txt filename on same zip
         */
        if (in_array($displayname, $this->names)) {
            $attachNumber = 1;
            list($filename, $ext) = preg_split("/\.(?=[^\.]*$)/", $displayname);
            foreach ($this->names as $name) {
                if (preg_match("/{$filename}\[(\d+)\]\.{$ext}/", $name, $match)) {
                    $attachNumber =  ((int) $match[1]) + 1;
                }
            }
            $displayname = $filename . '(' .$attachNumber . ').' . $ext;
        }
        $this->names[] = $displayname;

        return $displayname;
    }

    /**
     * Helper method to packs all the given messages into a zip archive
     *
     * @param array List of message UIDs to download
     */
    private function _download_messages($messageset)
    {
        $rcmail    = rcmail::get_instance();
        $imap      = $rcmail->get_storage();
        $mode      = rcube_utils::get_input_value('_mode', rcube_utils::INPUT_POST);
        $temp_dir  = $rcmail->config->get('temp_dir');
        $tmpfname  = tempnam($temp_dir, 'zipdownload');
        $tempfiles = array($tmpfname);
        $folders   = count($messageset) > 1;

        // @TODO: file size limit

        // open zip file
        $zip = new ZipArchive();
        $zip->open($tmpfname, ZIPARCHIVE::OVERWRITE);

        if ($mode == 'mbox') {
            $tmpfp = fopen($tmpfname . '.mbox', 'w');
        }

        foreach ($messageset as $mbox => $uids) {
            $imap->set_folder($mbox);
            $path = $folders ? str_replace($imap->get_hierarchy_delimiter(), '/', $mbox) . '/' : '';

            if ($uids === '*') {
                $index = $imap->index($mbox, null, null, true);
                $uids  = $index->get();
            }

            foreach ($uids as $uid) {
                $headers = $imap->get_message_headers($uid);

                if ($mode == 'mbox') {
                    $from = rcube_mime::decode_address_list($headers->from, null, true, $headers->charset, true);
                    $from = array_shift($from);

                    // Mbox format header
                    // @FIXME: \r\n or \n
                    // @FIXME: date format
                    $header = sprintf("From %s %s\r\n",
                        // replace spaces with hyphens
                        $from ? preg_replace('/\s/', '-', $from) : 'MAILER-DAEMON',
                        // internaldate
                        $headers->internaldate
                    );

                    fwrite($tmpfp, $header);

                    // Use stream filter to quote "From " in the message body
                    stream_filter_register('mbox_filter', 'zipdownload_mbox_filter');
                    $filter = stream_filter_append($tmpfp, 'mbox_filter');
                    $imap->get_raw_body($uid, $tmpfp);
                    stream_filter_remove($filter);
                    fwrite($tmpfp, "\r\n");
                }
                else { // maildir
                    $subject = rcube_mime::decode_mime_string((string)$headers->subject);
                    $subject = $this->_convert_filename($subject);
                    $subject = substr($subject, 0, 16);

                    $disp_name = ($subject ?: 'message_rfc822') . ".eml";
                    $disp_name = $path . $uid . "_" . $disp_name;

                    $tmpfn = tempnam($temp_dir, 'zipmessage');
                    $tmpfp = fopen($tmpfn, 'w');
                    $imap->get_raw_body($uid, $tmpfp);
                    $tempfiles[] = $tmpfn;
                    fclose($tmpfp);
                    $zip->addFile($tmpfn, $disp_name);
                }
            }
        }

        $filename = $folders ? 'messages' : $imap->get_folder();

        if ($mode == 'mbox') {
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
        $browser = new rcube_browser;
        $rcmail  = rcmail::get_instance();

        $rcmail->output->nocacheing_headers();

        if ($browser->ie)
            $filename = rawurlencode($filename);
        else
            $filename = addcslashes($filename, '"');

        // send download headers
        header("Content-Type: application/octet-stream");
        if ($browser->ie) {
            header("Content-Type: application/force-download");
        }

        // don't kill the connection if download takes more than 30 sec.
        @set_time_limit(0);
        header("Content-Disposition: attachment; filename=\"". $filename ."\"");
        header("Content-length: " . filesize($tmpfname));
        readfile($tmpfname);
    }

    /**
     * Helper function to convert filenames to the configured charset
     */
    private function _convert_filename($str)
    {
        $str = rcube_charset::convert($str, RCUBE_CHARSET, $this->charset);

        return strtr($str, array(':' => '', '/' => '-'));
    }
}

class zipdownload_mbox_filter extends php_user_filter
{
    function filter($in, $out, &$consumed, $closing)
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
