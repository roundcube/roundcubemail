<?php

/**
 * ZipDownload
 *
 * Plugin to allow the download of all message attachments in one zip file
 *
 * @version @package_version@
 * @requires php_zip extension (including ZipArchive class)
 * @author Philip Weir
 * @author Thomas Bruderli
 */
class zipdownload extends rcube_plugin
{
    public $task = 'mail';
    private $charset = 'ASCII';

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

        $this->register_action('plugin.zipdownload.zip_attachments', array($this, 'download_attachments'));
        $this->register_action('plugin.zipdownload.zip_messages', array($this, 'download_selection'));
        $this->register_action('plugin.zipdownload.zip_folder', array($this, 'download_folder'));

        if (($selection = $rcmail->config->get('zipdownload_selection')) || $rcmail->config->get('zipdownload_folder')) {
            $this->include_script('zipdownload.js');
            $this->api->output->set_env('zipdownload_selection', $selection);

            if ($rcmail->config->get('zipdownload_folder', false) && ($rcmail->action == '' || $rcmail->action == 'show')) {
                $zipdownload = $this->api->output->button(array('command' => 'plugin.zipdownload.zip_folder', 'type' => 'link', 'classact' => 'active', 'content' => $this->gettext('downloadfolder')));
                $this->api->add_content(html::tag('li', array('class' => 'separator_above'), $zipdownload), 'mailboxoptions');
            }
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
                '_action' => 'plugin.zipdownload.zip_attachments',
                '_mbox'   => $rcmail->output->env['mailbox'],
                '_uid'    => $rcmail->output->env['uid'],
            ));

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
     * Handler for attachment download action
     */
    public function download_attachments()
    {
        $rcmail    = rcmail::get_instance();
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
            $filename = $part->filename;

            if ($filename === null || $filename === '') {
                $ext      = (array) rcube_mime::get_mime_extensions($part->mimetype);
                $ext      = array_shift($ext);
                $filename = $rcmail->gettext('messagepart') . ' ' . $pid;
                if ($ext) {
                    $filename .= '.' . $ext;
                }
            }

            $disp_name = $this->_convert_filename($filename);

            if ($part->body) {
                $orig_message_raw = $part->body;
                $zip->addFromString($disp_name, $orig_message_raw);
            }
            else {
                $tmpfn = tempnam($temp_dir, 'zipattach');
                $tmpfp = fopen($tmpfn, 'w');
                $imap->get_message_part($message->uid, $part->mime_id, $part, null, $tmpfp, true);
                $tempfiles[] = $tmpfn;
                fclose($tmpfp);
                $zip->addFile($tmpfn, $disp_name);
            }
        }

        $zip->close();

        $filename = ($message->subject ? $message->subject : 'roundcube') . '.zip';
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
    public function download_selection()
    {
        if (isset($_REQUEST['_uid'])) {
            $uids = explode(",", rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GPC));

            if (sizeof($uids) > 0) {
                $this->_download_messages($uids);
            }
        }
    }

    /**
     * Handler for folder download action
     */
    public function download_folder()
    {
        $imap      = rcmail::get_instance()->get_storage();
        $mbox_name = $imap->get_folder();

        // initialize searching result if search_filter is used
        if ($_SESSION['search_filter'] && $_SESSION['search_filter'] != 'ALL') {
            $imap->search($mbox_name, $_SESSION['search_filter'], RCUBE_CHARSET);
        }

        // fetch message headers for all pages
        $uids = array();
        if ($count = $imap->count($mbox_name, $imap->get_threading() ? 'THREADS' : 'ALL', FALSE)) {
            for ($i = 0; ($i * $imap->get_pagesize()) <= $count; $i++) {
                $a_headers = $imap->list_messages($mbox_name, ($i + 1));

                foreach ($a_headers as $header) {
                    if (empty($header))
                        continue;

                    array_push($uids, $header->uid);
                }
            }
        }

        if (sizeof($uids) > 0)
            $this->_download_messages($uids);
    }

    /**
     * Helper method to packs all the given messages into a zip archive
     *
     * @param array List of message UIDs to download
     */
    private function _download_messages($uids)
    {
        $rcmail    = rcmail::get_instance();
        $imap      = $rcmail->get_storage();
        $temp_dir  = $rcmail->config->get('temp_dir');
        $tmpfname  = tempnam($temp_dir, 'zipdownload');
        $tempfiles = array($tmpfname);

        // open zip file
        $zip = new ZipArchive();
        $zip->open($tmpfname, ZIPARCHIVE::OVERWRITE);

        foreach ($uids as $uid){
            $headers = $imap->get_message_headers($uid);
            $subject = rcube_mime::decode_mime_string((string)$headers->subject);
            $subject = $this->_convert_filename($subject);
            $subject = substr($subject, 0, 16);

            $disp_name = ($subject ? $subject : 'message_rfc822') . ".eml";
            $disp_name = $uid . "_" . $disp_name;

            $tmpfn = tempnam($temp_dir, 'zipmessage');
            $tmpfp = fopen($tmpfn, 'w');
            $imap->get_raw_body($uid, $tmpfp);
            $tempfiles[] = $tmpfn;
            fclose($tmpfp);
            $zip->addFile($tmpfn, $disp_name);
        }

        $zip->close();

        $this->_deliver_zipfile($tmpfname, $imap->get_folder() . '.zip');

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

        if ($browser->ie && $browser->ver < 7)
            $filename = rawurlencode(abbreviate_string($filename, 55));
        else if ($browser->ie)
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

        return strtr($str, array(':'=>'', '/'=>'-'));
    }
}
