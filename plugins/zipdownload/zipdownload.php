<?php

use GuzzleHttp\Psr7\StreamWrapper;
use Psr\Http\Message\StreamInterface;
use ZipStream\ZipStream;

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
 * @author Christopher Gurnee
 */
class zipdownload extends rcube_plugin
{
    public $task = 'mail';

    private $charset = 'ASCII';
    private $names = [];
    private $default_limit = '50MB';
    private $timezone;

    // RFC4155: mbox date format
    public const MBOX_DATE_FORMAT = 'D M d H:i:s Y';

    /**
     * Plugin initialization
     */
    #[\Override]
    public function init()
    {
        // check requirements first
        if (!class_exists('ZipArchive', false)) {
            rcmail::raise_error([
                'code' => 520,
                'message' => 'php-zip extension is required for the zipdownload plugin',
            ], true, false);
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
                    '_mbox' => $rcmail->output->get_env('mailbox'),
                    '_uid' => $rcmail->output->get_env('uid'),
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
        $this->add_label('export');

        $rcmail = rcmail::get_instance();
        $menu = [];
        $ul_attr = [
            'role' => 'menu',
            'aria-labelledby' => 'aria-label-zipdownloadmenu',
            'class' => 'toolbarmenu menu',
        ];

        foreach (['eml', 'mbox', 'maildir'] as $type) {
            $menu[] = html::tag('li', null, $rcmail->output->button([
                    'command' => "download-{$type}",
                    'label' => "zipdownload.download{$type}",
                    'class' => "download {$type} disabled",
                    'classact' => "download {$type} active",
                    'type' => 'link',
                ])
            );
        }

        $rcmail->output->add_footer(
            html::div(['id' => 'zipdownload-menu', 'class' => 'popupmenu', 'aria-hidden' => 'true'],
                html::tag('h2', ['class' => 'voice', 'id' => 'aria-label-zipdownloadmenu'], rcube::Q($this->gettext('exportmenu')))
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

        $message = new rcube_message(rcube_utils::get_input_string('_uid', rcube_utils::INPUT_GET));
        $filename = ($this->_filename_from_subject($message->subject) ?: 'attachments') . '.zip';

        if (class_exists('ZipStream\ZipStream')) {
            $this->_download_attachments_zipstream($message, $filename);
        } else {
            $this->_download_attachments_tempfile($message, $filename);
        }
    }

    /**
     * Perform attachment download using temporary files
     *
     * @param rcube_message $message  Where to retrieve attachments
     * @param string        $filename Name to give to the download file
     */
    public function _download_attachments_tempfile($message, $filename)
    {
        $tmpfname = rcube_utils::temp_filename('zipdownload');
        $tempfiles = [$tmpfname];

        // open zip file
        $zip = new \ZipArchive();
        $zip->open($tmpfname, \ZipArchive::OVERWRITE);

        foreach ($message->attachments as $part) {
            $disp_name = $this->_create_displayname($part);

            $tmpfn = rcube_utils::temp_filename('zipattach');
            $tmpfp = fopen($tmpfn, 'w');
            $tempfiles[] = $tmpfn;

            $message->get_part_body($part->mime_id, false, 0, $tmpfp);
            $zip->addFile($tmpfn, $disp_name);
            fclose($tmpfp);
        }

        $zip->close();

        $this->_deliver_zipfile($tmpfname, $filename);

        // delete temporary files from disk
        foreach ($tempfiles as $tmpfn) {
            unlink($tmpfn);
        }

        exit;
    }

    /**
     * Perform attachment download using ZipStream
     *
     * @param rcube_message $message  Where to retrieve attachments
     * @param string        $filename Name to give to the download file
     */
    public function _download_attachments_zipstream($message, $filename)
    {
        $rcmail = rcmail::get_instance();
        $rcmail->output->download_headers($filename);

        $zip = new ZipStream(
            sendHttpHeaders: false,
            defaultDeflateLevel: 1,
            flushOutput: true
        );

        foreach ($message->attachments as $part) {
            $disp_name = $this->_create_displayname($part);
            $fs = new \FiberStream(static fn ($fp) => $zip->addFileFromStream($disp_name, $fp));
            $message->get_part_body($part->mime_id, false, 0, $fs->get_file());
            $fs->close();
        }

        $zip->finish();

        exit;
    }

    /**
     * Handler for message download action
     */
    public function download_messages()
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->config->get('zipdownload_selection', $this->default_limit)) {
            $messageset = rcmail_action::get_uids(null, null, $multi, rcube_utils::INPUT_POST);
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
        $rcmail = rcmail::get_instance();
        $filename = $part->filename;

        if ($filename === null || $filename === '') {
            $ext = array_first((array) rcube_mime::get_mime_extensions($part->mimetype));
            $filename = $rcmail->gettext('messagepart') . ' ' . $part->mime_id;
            if ($ext) {
                $filename .= '.' . $ext;
            }
        }

        $displayname = $this->_convert_filename($filename);

        /*
         * Adding a number before dot of extension on a name of file with same name on zip
         * Ext: attach(1).txt on attach filename that has a attach.txt filename on same zip
         */
        if (isset($this->names[$displayname])) {
            [$filename, $ext] = preg_split('/\.(?=[^\.]*$)/', $displayname);
            $displayname = $filename . '(' . ($this->names[$displayname]++) . ').' . $ext;
            $this->names[$displayname] = 1;
        } else {
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

        $rcmail = rcmail::get_instance();
        $imap = $rcmail->get_storage();
        $mode = rcube_utils::get_input_string('_mode', rcube_utils::INPUT_POST);
        $limit = $rcmail->config->get('zipdownload_selection', $this->default_limit);
        $limit = $limit !== true ? parse_bytes($limit) : -1;
        $delimiter = $imap->get_hierarchy_delimiter();
        $folders = count($messageset) > 1;
        $timezone = new \DateTimeZone('UTC');
        $messages = [];
        $size = 0;

        // collect messages metadata (and check size limit)
        foreach ($messageset as $mbox => $uids) {
            $imap->set_folder($mbox);

            if ($uids === '*') {
                $index = $imap->index($mbox, null, null, true);
                $uids = $index->get();
            }

            foreach ($uids as $uid) {
                $headers = $imap->get_message_headers($uid);

                // Received (internal) date
                $date = rcube_utils::anytodatetime($headers->internaldate);

                if ($mode == 'mbox') {
                    // Sender address
                    $from = rcube_mime::decode_address_list($headers->from, null, true, $headers->charset, true);
                    $from = array_shift($from);
                    $from = preg_replace('/\s/', '-', $from);

                    if ($date) {
                        $date = $date->setTimezone($timezone)->format(self::MBOX_DATE_FORMAT);
                    } else {
                        $date = new \DateTime('now', $timezone);
                    }

                    // Mbox format header (RFC4155)
                    $header = sprintf("From %s %s\r\n", $from ?: 'MAILER-DAEMON', $date);

                    $messages[$uid . ':' . $mbox] = $header;
                } else { // maildir
                    $subject = rcube_mime::decode_header($headers->subject, $headers->charset);
                    $subject = $this->_filename_from_subject(mb_substr($subject, 0, 16));
                    $subject = $this->_convert_filename($subject);

                    $path = $folders ? str_replace($delimiter, '/', $mbox) . '/' : '';
                    $disp_name = $path . $uid . ($subject ? " {$subject}" : '') . '.eml';

                    $messages[$uid . ':' . $mbox] = ($date ? $date->getTimestamp() : '') . ':' . $disp_name;
                }

                $size += $headers->size;

                if ($limit > 0 && $size > $limit) {
                    $msg = $this->gettext([
                        'name' => 'sizelimiterror',
                        'vars' => ['$size' => rcmail_action::show_bytes($limit)],
                    ]);

                    $rcmail->output->show_message($msg, 'error');
                    $rcmail->output->send('iframe');
                    exit;
                }
            }
        }

        $basename = $folders ? 'messages' : $imap->get_folder();
        if (class_exists('ZipStream\ZipStream')) {
            $this->_download_messages_zipstream($messages, $mode, $basename);
        } else {
            $this->_download_messages_tempfile($messages, $mode, $basename);
        }
    }

    /**
     * Helper method to add a single email to an mbox-style file stream
     *
     * @param resource     $stream      File stream to write to
     * @param string       $header      Mbox header to write before the email
     * @param string       $mbox        The mailbox folder containing the email
     * @param string       $uid         The UID of the email to write
     * @param bool         $is_last     Is this the last email in the mbox
     * @param \FiberStream $fiberstream FiberStream, if any, associated with the $stream
     */
    private function _write_mbox_stream($stream, $header, $mbox, $uid, $is_last, $fiberstream = null)
    {
        $rcmail = rcmail::get_instance();
        $imap = $rcmail->get_storage();

        fwrite($stream, $header);
        $imap->set_folder($mbox);

        // Use stream filter to quote "From " in the message body
        $filter = stream_filter_append($stream, 'mbox_filter');
        $imap->get_raw_body($uid, $stream);
        stream_filter_remove($filter);

        // Make sure the delimiter is a double \r\n
        if ($fiberstream) {
            $last_two = $fiberstream->get_last_two();  // if $stream doesn't support the functions below
        } else {
            $last_two = stream_get_contents($stream, 2, fstat($stream)['size'] - 2);
        }
        if ($last_two != "\r\n") {
            fwrite($stream, "\r\n");
        }
        if (!$is_last) {
            fwrite($stream, "\r\n");
        }
    }

    /**
     * Perform message download using temporary files
     *
     * @param array  $messages Map of uid:mbox => mbox_header or timestamp:display_name
     * @param string $mode     The _mode POST parameter
     * @param string $basename Name, without extension, to give to the download file
     */
    private function _download_messages_tempfile($messages, $mode, $basename)
    {
        $rcmail = rcmail::get_instance();
        $imap = $rcmail->get_storage();
        $tmpfname = rcube_utils::temp_filename('zipdownload');
        $tempfiles = [$tmpfname];

        if ($mode == 'mbox') {
            $tmpfp = fopen($tmpfname . '.mbox', 'w');
            if (!$tmpfp) {
                exit;
            }
            stream_filter_register('mbox_filter', 'zipdownload_mbox_filter');
        }

        // open zip file
        putenv('TZ=UTC');  // see _datetime_to_ziplocal() comments
        $zip = new \ZipArchive();
        $zip->open($tmpfname, \ZipArchive::OVERWRITE);

        $last_key = array_key_last($messages);
        foreach ($messages as $key => $value) {
            [$uid, $mbox] = explode(':', $key, 2);

            if (!empty($tmpfp)) {
                $this->_write_mbox_stream($tmpfp, $value, $mbox, $uid, $key == $last_key);
            } else { // maildir
                [$date, $filename] = explode(':', $value, 2);
                $tmpfn = rcube_utils::temp_filename('zipmessage');
                $fp = fopen($tmpfn, 'w');
                $imap->set_folder($mbox);
                $imap->get_raw_body($uid, $fp);
                $tempfiles[] = $tmpfn;
                fclose($fp);
                $zip->addFile($tmpfn, $filename);
                if ($date) {
                    $date = $this->_datetime_to_ziplocal(new \DateTime('@' . $date));
                    $zip->setMtimeName($filename, $date->getTimestamp());
                }
            }
        }

        if (!empty($tmpfp)) {
            $tempfiles[] = $tmpfname . '.mbox';
            fclose($tmpfp);
            $zip->addFile($tmpfname . '.mbox', $basename . '.mbox');
        }

        $zip->close();

        $this->_deliver_zipfile($tmpfname, $basename . '.zip');

        // delete temporary files from disk
        foreach ($tempfiles as $tmpfn) {
            unlink($tmpfn);
        }

        exit;
    }

    /**
     * Perform message download using ZipStream
     *
     * @param array  $messages Map of uid:mbox => mbox_header or timestamp:display_name
     * @param string $mode     The _mode POST parameter
     * @param string $basename Name, without extension, to give to the download file
     */
    private function _download_messages_zipstream($messages, $mode, $basename)
    {
        $rcmail = rcmail::get_instance();
        $imap = $rcmail->get_storage();

        $rcmail->output->download_headers($basename . '.zip');

        $zip = new ZipStream(
            sendHttpHeaders: false,
            defaultDeflateLevel: 1,
            flushOutput: true
        );

        if ($mode == 'mbox') {
            $fs = new \FiberStream(static fn ($fp) => $zip->addFileFromStream($basename . '.mbox', $fp));
            stream_filter_register('mbox_filter', 'zipdownload_mbox_filter');
        }

        $last_key = array_key_last($messages);
        foreach ($messages as $key => $value) {
            [$uid, $mbox] = explode(':', $key, 2);

            if ($mode == 'mbox') {
                $this->_write_mbox_stream($fs->get_file(), $value, $mbox, $uid, $key == $last_key, $fs);
            } else {  // maildir
                [$date, $filename] = explode(':', $value, 2);
                $date = $date ? $this->_datetime_to_ziplocal(new \DateTime('@' . $date)) : null;
                $imap->set_folder($mbox);
                $fs = new \FiberStream(static fn ($fp) => $zip->addFileFromStream($filename, $fp, lastModificationDateTime: $date));
                $imap->get_raw_body($uid, $fs->get_file());
                $fs->close();
            }
        }

        if ($mode == 'mbox') {
            $fs->close();
        }
        $zip->finish();

        exit;
    }

    /**
     * Zip files do not store timezones; most extraction tools extract times
     * as though they were local times. This converts the UTC times inside
     * DateTime objects into a user's preferred local time (despite claiming
     * to still be UTC) so that when added and later extracted to/from a zip
     * file, they will be in that user's local time.
     *
     * Also, ZipArchive creation is affected the system's default timezone
     * (NOT date_default_timezone_set); to mitigate this, putenv('TZ=UTC').
     *
     * @param \DateTimeInterface $real The accurate DateTime of a file (is not changed)
     *
     * @return \DateTimeInterface A "fake" DateTimeImmutable for inclusion into a zip
     */
    private function _datetime_to_ziplocal($real)
    {
        if (!$this->timezone) {
            if ($this->timezone === false) {
                return $real;
            }
            $rcmail = rcmail::get_instance();
            try {
                $this->timezone = new \DateTimeZone($rcmail->config->get('timezone'));
            } catch (\DateInvalidTimeZoneException) {
                $this->timezone = false;
                return $real;
            }
        }

        $real = \DateTime::createFromInterface($real);
        $real->setTimezone($this->timezone);
        $local = max($real->format('Y/m/d H:i:s'), '1980/01/01 00:00:00');  // Earliest supported by zip
        return \DateTimeImmutable::createFromFormat('Y/m/d H:i:s O', $local . ' +0000');
    }

    /**
     * Helper method to send the zip archive to the browser
     */
    private function _deliver_zipfile($tmpfname, $filename)
    {
        $rcmail = rcmail::get_instance();

        $rcmail->output->download_headers($filename, ['length' => filesize($tmpfname)]);

        $tmpfp = fopen($tmpfname, 'r');
        if (!$tmpfp) {
            return;
        }
        while (true) {
            $data = fread($tmpfp, 512 * 1024);
            if (strlen($data) == 0) {
                break;
            }
            echo $data;
            ob_flush();
            flush();
        }
        fclose($tmpfp);
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

        return trim($str, ' ./_');
    }
}

class zipdownload_mbox_filter extends \php_user_filter
{
    #[\Override]
    #[\ReturnTypeWillChange]
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            // messages are read line by line
            if (preg_match('/^>*From /', $bucket->data)) {
                $bucket->data = '>' . $bucket->data;
                $bucket->datalen++;
            }

            $consumed += (int) $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return \PSFS_PASS_ON;
    }
}

/**
 * Consider a source of data, which writes to a standard file stream, and
 * a destination of data, which reads from that stream, via this pattern:
 *     $stream = fopen(..., 'w+');
 *     write_all_to_stream($stream);
 *     rewind($stream);
 *     read_entire_stream($stream);
 * The stream must be backed by something, typically a temp file or a
 * php://memory file. As an alternative, a FiberStream can be used:
 *     $fs = new FiberStream('read_entire_stream');  // arg is a callable
 *     write_all_to_stream($fs->get_file());
 *     $fs->close();
 * The FiberStream allows the source to fwrite() up to a certain limit (the
 * chunk_size, defaulting to 512 KiB), and then transfers control to the
 * destination, which will fread() until the buffer is emptied, after which
 * it will transfer control back to the source to begin writing again. By
 * using a Fiber to switch back and forth, only a limited amount of buffer
 * space is required, typically around 1 to 2 times the chunk_size.
 */
final class FiberStream implements StreamInterface
{
    public const DEFAULT_CHUNK_SIZE = 512 * 1024;
    private $chunk_size;
    private $dest_fiber;
    private $write_file;
    private $read_file;
    private $buffer = '';
    private $read_pos = 0;
    private $last_two = '';
    private $closing = false;
    private $closed = false;

    /**
     * @param callable(resource): mixed $dest       Called by FiberStream to read an entire file stream
     * @param int                       $chunk_size The stream's chunk size and desired buffer limit
     */
    public function __construct(callable $dest, $chunk_size = self::DEFAULT_CHUNK_SIZE)
    {
        $this->chunk_size = $chunk_size;
        $this->dest_fiber = new \Fiber(function () use ($dest) {
            $this->read_file = StreamWrapper::getResource($this);
            stream_set_chunk_size($this->read_file, $this->chunk_size);
            $dest($this->read_file);
        });
        $this->write_file = StreamWrapper::getResource($this);
        stream_set_chunk_size($this->write_file, $chunk_size);
    }

    /**
     * @return resource A file stream an entire file should be written to
     */
    public function get_file()
    {
        return $this->write_file;
    }

    /**
     * Start or resume the destination fiber
     */
    private function run_dest_fiber()
    {
        if (!$this->dest_fiber->isStarted()) {
            $this->dest_fiber->start();
        } else {
            $this->dest_fiber->resume();
        }
    }

    private function check_not_closed()
    {
        if ($this->closed || $this->dest_fiber->isTerminated()) {
            throw new \RuntimeException('FiberStream is closed');
        }
    }

    #[\Override]
    public function write($string): int
    {
        $this->check_not_closed();
        $this->buffer .= $string;
        $last = substr($this->buffer, -2);
        if (strlen($last) == 2) {
            $this->last_two = $last;
        } elseif (strlen($last) == 1) {
            $this->last_two = substr($this->last_two, -1) . $last;
        }
        if (strlen($this->buffer) >= $this->chunk_size) {
            $this->run_dest_fiber();  // suspend writer/source and allow dest to read() the buffer
        }
        return strlen($string);
    }

    public function get_last_two()
    {
        return $this->last_two;  // for _write_mbox_stream()
    }

    #[\Override]
    public function read($length): string
    {
        $this->check_not_closed();
        if (\Fiber::getCurrent() !== $this->dest_fiber) {
            throw new \RuntimeException('Only the dest callable may call FiberStream::read');
        }
        if (strlen($this->buffer) == 0 && !$this->closing) {
            $this->dest_fiber->suspend();  // suspend reader/dest and allow source to write() more
        }
        $result = substr($this->buffer, $this->read_pos, $length);
        $this->read_pos += $length;
        if ($this->read_pos >= strlen($this->buffer)) {
            $this->buffer = '';
            $this->read_pos = 0;
        }
        return $result;
    }

    #[\Override]
    public function eof(): bool
    {
        return $this->closing && $this->read_pos >= strlen($this->buffer);
    }

    /**
     * Do not call fclose() on any files received from a FiberStream (e.g. from get_file()),
     * instead call this which will close the files in the correct order.
     */
    #[\Override]
    public function close(): void
    {
        if (!$this->closed) {
            fclose($this->write_file);  // Can cause more calls to our write()/read()/eof(),
            $this->closing = true;      // so wait until those calls have completed and only then set this flag.
            $this->check_not_closed();  // Ensure dest_fiber hasn't returned early;
            $this->run_dest_fiber();    // runs until dest returns, it's expected to read() until eof.
            fclose($this->read_file);
            $this->buffer = '';
            $this->closed = true;
        }
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getContents();
    }

    #[\Override]
    public function getContents(): string
    {
        $buffer = $this->buffer;
        $this->buffer = '';
        $this->read_pos = 0;
        return $buffer;
    }

    #[\Override]
    public function detach()
    {
        $this->close();
        return null;
    }

    #[\Override]
    public function getSize(): ?int
    {
        return null;
    }

    #[\Override]
    public function isReadable(): bool
    {
        return \Fiber::getCurrent() === $this->dest_fiber;
    }

    #[\Override]
    public function isWritable(): bool
    {
        return \Fiber::getCurrent() !== $this->dest_fiber;
    }

    #[\Override]
    public function isSeekable(): bool
    {
        return false;
    }

    #[\Override]
    public function rewind(): void
    {
        $this->seek(0);
    }

    #[\Override]
    public function seek($offset, $whence = \SEEK_SET): void
    {
        throw new \RuntimeException('Cannot seek a FiberStream');
    }

    #[\Override]
    public function tell(): int
    {
        throw new \RuntimeException('Cannot determine the position of a FiberStream');
    }

    #[\Override]
    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }
}
