<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 | Copyright (C) Kolab Systems AG                                        |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   An abstract for HTTP request handlers with some helpers.            |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+
*/

/**
 * An abstract for HTTP request handlers with some helpers.
 *
 * @package Webmail
 */
abstract class rcmail_action
{
    const MODE_AJAX = 1;
    const MODE_HTTP = 2;

    /**
     * Mode of operation supported by the action. Use MODE_* constants.
     *
     * @var int
     */
    protected static $mode = self::MODE_HTTP;

    /**
     * Deprecated action aliases.
     *
     * @todo Get rid of these (but it will be a big BC break)
     * @var array
     */
    public static $aliases = [];

    /**
     * Request handler. The only abstract method.
     */
    abstract public function run();

    /**
     * Request sanity checks, e.g. supported request mode
     *
     * @return bool
     */
    public function checks()
    {
        $rcmail = rcmail::get_instance();

        if (!(static::$mode & self::MODE_HTTP) && empty($rcmail->output->ajax_call)) {
            return false;
        }

        if (!(static::$mode & self::MODE_AJAX) && !empty($rcmail->output->ajax_call)) {
            return false;
        }

        return true;
    }

    /**
     * Set environment variables for specified config options
     *
     * @param array $options List of configuration option names
     */
    public static function set_env_config($options)
    {
        $rcmail = rcmail::get_instance();

        foreach ((array) $options as $option) {
            if ($rcmail->config->get($option)) {
                $rcmail->output->set_env($option, true);
            }
        }
    }

    /**
     * Create a HTML table based on the given data
     *
     * @param array  $attrib     Named table attributes
     * @param mixed  $table_data Table row data. Either a two-dimensional array
     *                           or a valid SQL result set
     * @param array  $show_cols  List of cols to show
     * @param string $id_col     Name of the identifier col
     *
     * @return string HTML table code
     */
    public static function table_output($attrib, $table_data, $show_cols, $id_col)
    {
        $rcmail = rcmail::get_instance();
        $table  = new html_table($attrib);

        // add table header
        if (!$attrib['noheader']) {
            foreach ($show_cols as $col) {
                $table->add_header($col, rcube::Q($rcmail->gettext($col)));
            }
        }

        if (!is_array($table_data)) {
            $db = $rcmail->get_dbh();
            while ($table_data && ($sql_arr = $db->fetch_assoc($table_data))) {
                $table->add_row(array('id' => 'rcmrow' . rcube_utils::html_identifier($sql_arr[$id_col])));

                // format each col
                foreach ($show_cols as $col) {
                    $table->add($col, rcube::Q($sql_arr[$col]));
                }
            }
        }
        else {
            foreach ($table_data as $row_data) {
                $class = !empty($row_data['class']) ? $row_data['class'] : null;
                if (!empty($attrib['rowclass'])) {
                    $class = trim($class . ' ' . $attrib['rowclass']);
                }

                $rowid = 'rcmrow' . rcube_utils::html_identifier($row_data[$id_col]);

                $table->add_row(['id' => $rowid, 'class' => $class]);

                // format each col
                foreach ($show_cols as $col) {
                    $val = is_array($row_data[$col]) ? $row_data[$col][0] : $row_data[$col];
                    $table->add($col, empty($attrib['ishtml']) ? rcube::Q($val) : $val);
                }
            }
        }

        return $table->show($attrib);
    }

    /**
     * Return HTML for quota indicator object
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML code for the quota indicator object
     */
    public static function quota_display($attrib)
    {
        $rcmail = rcmail::get_instance();

        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmquotadisplay';
        }

        $_SESSION['quota_display'] = !empty($attrib['display']) ? $attrib['display'] : 'text';

        $rcmail->output->add_gui_object('quotadisplay', $attrib['id']);

        $quota = $rcmail->quota_content($attrib);

        $rcmail->output->add_script('rcmail.set_quota('.rcube_output::json_serialize($quota).');', 'docready');

        return html::span($attrib, '&nbsp;');
    }

    /**
     * Return (parsed) quota information
     *
     * @param array $attrib Named parameters
     * @param array $folder Current folder
     *
     * @return array Quota information
     */
    public static function quota_content($attrib = null, $folder = null)
    {
        $rcmail = rcmail::get_instance();
        $quota  = $rcmail->storage->get_quota($folder);
        $quota  = $rcmail->plugins->exec_hook('quota', $quota);

        $quota_result           = (array) $quota;
        $quota_result['type']   = isset($_SESSION['quota_display']) ? $_SESSION['quota_display'] : '';
        $quota_result['folder'] = $folder !== null && $folder !== '' ? $folder : 'INBOX';

        if ($quota['total'] > 0) {
            if (!isset($quota['percent'])) {
                $quota_result['percent'] = min(100, round(($quota['used']/max(1,$quota['total']))*100));
            }

            $title = $rcmail->gettext('quota') . ': ' . sprintf('%s / %s (%.0f%%)',
                $rcmail->show_bytes($quota['used'] * 1024),
                $rcmail->show_bytes($quota['total'] * 1024),
                $quota_result['percent']
            );

            $quota_result['title'] = $title;

            if (!empty($attrib['width'])) {
                $quota_result['width'] = $attrib['width'];
            }
            if (!empty($attrib['height'])) {
                $quota_result['height'] = $attrib['height'];
            }

            // build a table of quota types/roots info
            if (($root_cnt = count($quota_result['all'])) > 1 || count($quota_result['all'][key($quota_result['all'])]) > 1) {
                $table = new html_table(array('cols' => 3, 'class' => 'quota-info'));

                $table->add_header(null, rcube::Q($rcmail->gettext('quotatype')));
                $table->add_header(null, rcube::Q($rcmail->gettext('quotatotal')));
                $table->add_header(null, rcube::Q($rcmail->gettext('quotaused')));

                foreach ($quota_result['all'] as $root => $data) {
                    if ($root_cnt > 1 && $root) {
                        $table->add(['colspan' => 3, 'class' => 'root'], self::Q($root));
                    }

                    if ($storage = $data['storage']) {
                        $percent = min(100, round(($storage['used']/max(1,$storage['total']))*100));

                        $table->add('name', rcube::Q($rcmail->gettext('quotastorage')));
                        $table->add(null, $rcmail->show_bytes($storage['total'] * 1024));
                        $table->add(null, sprintf('%s (%.0f%%)', $rcmail->show_bytes($storage['used'] * 1024), $percent));
                    }
                    if ($message = $data['message']) {
                        $percent = min(100, round(($message['used']/max(1,$message['total']))*100));

                        $table->add('name', self::Q($rcmail->gettext('quotamessage')));
                        $table->add(null, intval($message['total']));
                        $table->add(null, sprintf('%d (%.0f%%)', $message['used'], $percent));
                    }
                }

                $quota_result['table'] = $table->show();
            }
        }
        else {
            $unlimited               = $rcmail->config->get('quota_zero_as_unlimited');
            $quota_result['title']   = $rcmail->gettext($unlimited ? 'unlimited' : 'unknown');
            $quota_result['percent'] = 0;
        }

        // cleanup
        unset($quota_result['abort']);
        if (empty($quota_result['table'])) {
            unset($quota_result['all']);
        }

        return $quota_result;
    }

    /**
     * Outputs error message according to server error/response codes
     *
     * @param string $fallback      Fallback message label
     * @param array  $fallback_args Fallback message label arguments
     * @param string $suffix        Message label suffix
     * @param array  $params        Additional parameters (type, prefix)
     */
    public static function display_server_error($fallback = null, $fallback_args = null, $suffix = '', $params = [])
    {
        $rcmail   = rcmail::get_instance();
        $storage  = $rcmail->get_storage();
        $err_code = $storage->get_error_code();
        $res_code = $storage->get_response_code();
        $args     = [];

        if ($res_code == rcube_storage::NOPERM) {
            $error = 'errornoperm';
        }
        else if ($res_code == rcube_storage::READONLY) {
            $error = 'errorreadonly';
        }
        else if ($res_code == rcube_storage::OVERQUOTA) {
            $error = 'erroroverquota';
        }
        else if ($err_code && ($err_str = $storage->get_error_str())) {
            // try to detect access rights problem and display appropriate message
            if (stripos($err_str, 'Permission denied') !== false) {
                $error = 'errornoperm';
            }
            // try to detect full mailbox problem and display appropriate message
            // there can be e.g. "Quota exceeded" / "quotum would exceed" / "Over quota"
            else if (stripos($err_str, 'quot') !== false && preg_match('/exceed|over/i', $err_str)) {
                $error = 'erroroverquota';
            }
            else {
                $error = 'servererrormsg';
                $args  = array('msg' => rcube::Q($err_str));
            }
        }
        else if ($err_code < 0) {
            $error = 'storageerror';
        }
        else if ($fallback) {
            $error = $fallback;
            $args  = $fallback_args;
            $params['prefix'] = false;
        }

        if (!empty($error)) {
            if ($suffix && $rcmail->text_exists($error . $suffix)) {
                $error .= $suffix;
            }

            $msg = $rcmail->gettext(array('name' => $error, 'vars' => $args));

            if ($params['prefix'] && $fallback) {
                $msg = $rcmail->gettext(array('name' => $fallback, 'vars' => $fallback_args)) . ' ' . $msg;
            }

            $rcmail->output->show_message($msg, $params['type'] ?: 'error');
        }
    }

    /**
     * Displays an error message on storage fatal errors
     */
    public static function storage_fatal_error()
    {
        $rcmail   = rcmail::get_instance();
        $err_code = $rcmail->storage->get_error_code();
        switch ($err_code) {
        // Not all are really fatal, but these should catch
        // connection/authentication errors the best we can
        case rcube_imap_generic::ERROR_NO:
        case rcube_imap_generic::ERROR_BAD:
        case rcube_imap_generic::ERROR_BYE:
            self::display_server_error();
        }
    }

    /**
     * Output HTML editor scripts
     *
     * @param string $mode Editor mode
     */
    public static function html_editor($mode = '')
    {
        $rcmail           = rcmail::get_instance();
        $spellcheck       = intval($rcmail->config->get('enable_spellcheck'));
        $spelldict        = intval($rcmail->config->get('spellcheck_dictionary'));
        $disabled_plugins = [];
        $disabled_buttons = [];
        $extra_plugins    = [];
        $extra_buttons    = [];

        if (!$spellcheck) {
            $disabled_plugins[] = 'spellchecker';
        }

        $hook = $rcmail->plugins->exec_hook('html_editor', [
                'mode'             => $mode,
                'disabled_plugins' => $disabled_plugins,
                'disabled_buttons' => $disabled_buttons,
                'extra_plugins' => $extra_plugins,
                'extra_buttons' => $extra_buttons,
        ]);

        if (!empty($hook['abort'])) {
            return;
        }

        $lang_codes = [$_SESSION['language']];
        $assets_dir = $rcmail->config->get('assets_dir') ?: INSTALL_PATH;
        $skin_path  = $rcmail->output->get_skin_path();

        if ($pos = strpos($_SESSION['language'], '_')) {
            $lang_codes[] = substr($_SESSION['language'], 0, $pos);
        }

        foreach ($lang_codes as $code) {
            if (file_exists("$assets_dir/program/js/tinymce/langs/$code.js")) {
                $lang = $code;
                break;
            }
        }

        if (empty($lang)) {
            $lang = 'en';
        }

        $config = [
            'mode'       => $mode,
            'lang'       => $lang,
            'skin_path'  => $skin_path,
            'spellcheck' => $spellcheck, // deprecated
            'spelldict'  => $spelldict,
            'content_css'      => 'program/resources/tinymce/content.css',
            'disabled_plugins' => $hook['disabled_plugins'],
            'disabled_buttons' => $hook['disabled_buttons'],
            'extra_plugins'    => $hook['extra_plugins'],
            'extra_buttons'    => $hook['extra_buttons'],
        ];

        if ($path = $rcmail->config->get('editor_css_location')) {
            if ($path = $rcmail->find_asset($skin_path . $path)) {
                $config['content_css'] = $path;
            }
        }

        $font_family = $rcmail->output->get_env('default_font');
        $font_size   = $rcmail->output->get_env('default_font_size');
        $style       = [];

        if ($font_family) {
            $style[] = "font-family: $font_family;";
        }
        if ($font_size) {
            $style[] = "font-size: $font_size;";
        }
        if (!empty($style)) {
            $config['content_style'] = "body {" . implode(' ', $style) . "}";
        }

        $rcmail->output->set_env('editor_config', $config);
        $rcmail->output->add_label('selectimage', 'addimage', 'selectmedia', 'addmedia', 'close');

        if ($path = $rcmail->config->get('media_browser_css_location', 'program/resources/tinymce/browser.css')) {
            if ($path != 'none' && ($path = $rcmail->find_asset($path))) {
                $rcmail->output->include_css($path);
            }
        }

        $rcmail->output->include_script('tinymce/tinymce.min.js');
        $rcmail->output->include_script('editor.js');
    }

    /**
     * File upload progress handler.
     *
     * @deprecated We're using HTML5 upload progress
     */
    public static function upload_progress()
    {
        // NOOP
        rcmail::get_instance()->output->send();
    }

    /**
     * Initializes file uploading interface.
     *
     * @param int $max_size Optional maximum file size in bytes
     *
     * @return string Human-readable file size limit
     */
    public static function upload_init($max_size = null)
    {
        $rcmail = rcmail::get_instance();

        // find max filesize value
        $max_filesize = rcube_utils::max_upload_size();
        if ($max_size && $max_size < $max_filesize) {
            $max_filesize = $max_size;
        }

        $max_filesize_txt = $rcmail->show_bytes($max_filesize);
        $rcmail->output->set_env('max_filesize', $max_filesize);
        $rcmail->output->set_env('filesizeerror', $rcmail->gettext(array(
            'name' => 'filesizeerror', 'vars' => array('size' => $max_filesize_txt))));

        if ($max_filecount = ini_get('max_file_uploads')) {
            $rcmail->output->set_env('max_filecount', $max_filecount);
            $rcmail->output->set_env('filecounterror', $rcmail->gettext(array(
                'name' => 'filecounterror', 'vars' => array('count' => $max_filecount))));
        }

        $rcmail->output->add_label('uploadprogress', 'GB', 'MB', 'KB', 'B');

        return $max_filesize_txt;
    }

    /**
     * Upload form object
     *
     * @param array  $attrib     Object attributes
     * @param string $name       Form object name
     * @param string $action     Form action name
     * @param array  $input_attr File input attributes
     * @param int    $max_size   Maximum upload size
     *
     * @return string HTML output
     */
    public static function upload_form($attrib, $name, $action, $input_attr = [], $max_size = null)
    {
        $rcmail = rcmail::get_instance();

        // Get filesize, enable upload progress bar
        $max_filesize = self::upload_init($max_size);

        $hint = html::div('hint', $rcmail->gettext(['name' => 'maxuploadsize', 'vars' => ['size' => $max_filesize]]));

        if (!empty($attrib['mode']) && $attrib['mode'] == 'hint') {
            return $hint;
        }

        // set defaults
        $attrib += ['id' => 'rcmUploadbox', 'buttons' => 'yes'];

        $event   = rcmail_output::JS_OBJECT_NAME . ".command('$action', this.form)";
        $form_id = $attrib['id'] . 'Frm';

        // Default attributes of file input and form
        $input_attr += [
            'id'   => $attrib['id'] . 'Input',
            'type' => 'file',
            'name' => '_attachments[]',
            'class' => 'form-control',
        ];

        $form_attr = [
            'id'      => $form_id,
            'name'    => $name,
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ];

        if (!empty($attrib['mode']) && $attrib['mode'] == 'smart') {
            unset($attrib['buttons']);
            $form_attr['class'] = 'smart-upload';
            $input_attr = array_merge($input_attr, [
                // #5854: Chrome does not execute onchange when selecting the same file.
                //        To fix this we reset the input using null value.
                'onchange' => "$event; this.value=null",
                'class'    => 'smart-upload',
                'tabindex' => '-1',
            ]);
        }

        $input   = new html_inputfield($input_attr);
        $content = $attrib['prefix'] . $input->show();

        if (empty($attrib['mode']) || $attrib['mode'] != 'smart') {
            $content = html::div(null, $content . $hint);
        }

        if (rcube_utils::get_boolean($attrib['buttons'])) {
            $button   = new html_inputfield(['type' => 'button']);
            $content .= html::div('buttons',
                $button->show($rcmail->gettext('close'), ['class' => 'button', 'onclick' => "$('#{$attrib['id']}').hide()"])
                . ' ' .
                $button->show($rcmail->gettext('upload'), ['class' => 'button mainaction', 'onclick' => $event])
            );
        }

        $rcmail->output->add_gui_object($name, $form_id);

        return html::div($attrib, $rcmail->output->form_tag($form_attr, $content));
    }

    /**
     * Outputs uploaded file content (with image thumbnails support
     *
     * @param array $file Upload file data
     */
    public static function display_uploaded_file($file)
    {
        if (empty($file)) {
            return;
        }

        $rcmail = rcmail::get_instance();

        $file = $rcmail->plugins->exec_hook('attachment_display', $file);

        if ($file['status']) {
            if (empty($file['size'])) {
                $file['size'] = $file['data'] ? strlen($file['data']) : @filesize($file['path']);
            }

            // generate image thumbnail for file browser in HTML editor
            if (!empty($_GET['_thumbnail'])) {
                $thumbnail_size = 80;
                $mimetype       = $file['mimetype'];
                $file_ident     = $file['id'] . ':' . $file['mimetype'] . ':' . $file['size'];
                $thumb_name     = 'thumb' . md5($file_ident . ':' . $rcmail->user->ID . ':' . $thumbnail_size);
                $cache_file     = rcube_utils::temp_filename($thumb_name, false, false);

                // render thumbnail image if not done yet
                if (!is_file($cache_file)) {
                    if (!$file['path']) {
                        $orig_name = $filename = $cache_file . '.tmp';
                        file_put_contents($orig_name, $file['data']);
                    }
                    else {
                        $filename = $file['path'];
                    }

                    $image = new rcube_image($filename);
                    if ($imgtype = $image->resize($thumbnail_size, $cache_file, true)) {
                        $mimetype = 'image/' . $imgtype;

                        if (!empty($orig_name)) {
                            unlink($orig_name);
                        }
                    }
                }

                if (is_file($cache_file)) {
                    // cache for 1h
                    $rcmail->output->future_expire_header(3600);
                    header('Content-Type: ' . $mimetype);
                    header('Content-Length: ' . filesize($cache_file));

                    readfile($cache_file);
                    exit;
                }
            }

            header('Content-Type: ' . $file['mimetype']);
            header('Content-Length: ' . $file['size']);

            if ($file['data']) {
                echo $file['data'];
            }
            else if ($file['path']) {
                readfile($file['path']);
            }
        }
    }

    /**
     * Initializes client-side autocompletion.
     */
    public static function autocomplete_init()
    {
        static $init;

        if ($init) {
            return;
        }

        $init   = 1;
        $rcmail = rcmail::get_instance();

        if (($threads = (int) $rcmail->config->get('autocomplete_threads')) > 0) {
            $book_types = (array) $rcmail->config->get('autocomplete_addressbooks', 'sql');
            if (count($book_types) > 1) {
                $rcmail->output->set_env('autocomplete_threads', $threads);
                $rcmail->output->set_env('autocomplete_sources', $book_types);
            }
        }

        $rcmail->output->set_env('autocomplete_max', (int) $rcmail->config->get('autocomplete_max', 15));
        $rcmail->output->set_env('autocomplete_min_length', $rcmail->config->get('autocomplete_min_length'));
        $rcmail->output->add_label('autocompletechars', 'autocompletemore');
    }

    /**
     * Returns supported font-family specifications
     *
     * @param string $font Font name
     *
     * @return string|array Font-family specification array or string (if $font is used)
     */
    public static function font_defs($font = null)
    {
        $fonts = [
            'Andale Mono'   => '"Andale Mono",Times,monospace',
            'Arial'         => 'Arial,Helvetica,sans-serif',
            'Arial Black'   => '"Arial Black","Avant Garde",sans-serif',
            'Book Antiqua'  => '"Book Antiqua",Palatino,serif',
            'Courier New'   => '"Courier New",Courier,monospace',
            'Georgia'       => 'Georgia,Palatino,serif',
            'Helvetica'     => 'Helvetica,Arial,sans-serif',
            'Impact'        => 'Impact,Chicago,sans-serif',
            'Tahoma'        => 'Tahoma,Arial,Helvetica,sans-serif',
            'Terminal'      => 'Terminal,Monaco,monospace',
            'Times New Roman' => '"Times New Roman",Times,serif',
            'Trebuchet MS'  => '"Trebuchet MS",Geneva,sans-serif',
            'Verdana'       => 'Verdana,Geneva,sans-serif',
        ];

        if ($font) {
            return !empty($fonts[$font]) ? $fonts[$font] : null;
        }

        return $fonts;
    }

    /**
     * Create a human readable string for a number of bytes
     *
     * @param int    $bytes Number of bytes
     * @param string &$unit Size unit
     *
     * @return string Byte string
     */
    public static function show_bytes($bytes, &$unit = null)
    {
        $rcmail = rcmail::get_instance();

        // Plugins may want to display different units
        $plugin = $rcmail->plugins->exec_hook('show_bytes', ['bytes' => $bytes, 'unit' => null]);

        $unit = $plugin['unit'];

        if (isset($plugin['result'])) {
            return $plugin['result'];
        }

        if ($bytes >= 1073741824) {
            $unit = 'GB';
            $gb   = $bytes/1073741824;
            $str  = sprintf($gb >= 10 ? "%d " : "%.1f ", $gb) . $rcmail->gettext($unit);
        }
        else if ($bytes >= 1048576) {
            $unit = 'MB';
            $mb   = $bytes/1048576;
            $str  = sprintf($mb >= 10 ? "%d " : "%.1f ", $mb) . $rcmail->gettext($unit);
        }
        else if ($bytes >= 1024) {
            $unit = 'KB';
            $str  = sprintf("%d ",  round($bytes/1024)) . $rcmail->gettext($unit);
        }
        else {
            $unit = 'B';
            $str  = sprintf('%d ', $bytes) . $rcmail->gettext($unit);
        }

        return $str;
    }

    /**
     * Returns real size (calculated) of the message part
     *
     * @param rcube_message_part $part Message part
     *
     * @return string Part size (and unit)
     */
    public static function message_part_size($part)
    {
        if (isset($part->d_parameters['size'])) {
            $size = self::show_bytes((int) $part->d_parameters['size']);
        }
        else {
            $size = $part->size;

            if ($size === 0) {
                $part->exact_size = true;
            }

            if ($part->encoding == 'base64') {
                $size = $size / 1.33;
            }

            $size = self::show_bytes($size);
        }

        if (!$part->exact_size) {
            $size = '~' . $size;
        }

        return $size;
    }

    /**
     * Returns message UID(s) and IMAP folder(s) from GET/POST data
     *
     * @param string $uids           UID value to decode
     * @param string $mbox           Default mailbox value (if not encoded in UIDs)
     * @param bool   $is_multifolder Will be set to True if multi-folder request
     * @param int    $mode           Request mode. Default: rcube_utils::INPUT_GPC.
     *
     * @return array  List of message UIDs per folder
     */
    public static function get_uids($uids = null, $mbox = null, &$is_multifolder = false, $mode = null)
    {
        // message UID (or comma-separated list of IDs) is provided in
        // the form of <ID>-<MBOX>[,<ID>-<MBOX>]*

        $_uid  = $uids ?: rcube_utils::get_input_value('_uid', $mode ?: rcube_utils::INPUT_GPC);
        $_mbox = $mbox ?: (string) rcube_utils::get_input_value('_mbox', $mode ?: rcube_utils::INPUT_GPC);

        // already a hash array
        if (is_array($_uid) && !isset($_uid[0])) {
            return $_uid;
        }

        $result = [];

        // special case: *
        if ($_uid == '*' && is_object($_SESSION['search'][1]) && $_SESSION['search'][1]->multi) {
            $is_multifolder = true;
            // extract the full list of UIDs per folder from the search set
            foreach ($_SESSION['search'][1]->sets as $subset) {
                $mbox = $subset->get_parameters('MAILBOX');
                $result[$mbox] = $subset->get();
            }
        }
        else {
            if (is_string($_uid)) {
                $_uid = explode(',', $_uid);
            }

            // create a per-folder UIDs array
            foreach ((array)$_uid as $uid) {
                list($uid, $mbox) = explode('-', $uid, 2);
                if (!strlen($mbox)) {
                    $mbox = $_mbox;
                }
                else {
                    $is_multifolder = true;
                }

                if ($uid == '*') {
                    $result[$mbox] = $uid;
                }
                else if (preg_match('/^[0-9:.]+$/', $uid)) {
                    $result[$mbox][] = $uid;
                }
            }
        }

        return $result;
    }

    /**
     * Get resource file content (with assets_dir support)
     *
     * @param string $name File name
     *
     * @return string File content
     */
    public static function get_resource_content($name)
    {
        if (!strpos($name, '/')) {
            $name = "program/resources/$name";
        }

        $assets_dir = rcmail::get_instance()->config->get('assets_dir');

        if ($assets_dir) {
            $path = slashify($assets_dir) . $name;
            if (@file_exists($path)) {
                $name = $path;
            }
        }

        return file_get_contents($name, false);
    }

    public static function get_form_tags($attrib, $action, $id = null, $hidden = null)
    {
        static $edit_form;

        $rcmail = rcmail::get_instance();

        $form_start = $form_end = '';

        if (empty($edit_form)) {
            $request_key = $action . (isset($id) ? '.'.$id : '');
            $form_start = $rcmail->output->request_form([
                    'name'    => 'form',
                    'method'  => 'post',
                    'task'    => $rcmail->task,
                    'action'  => $action,
                    'request' => $request_key,
                    'noclose' => true
                ] + $attrib
            );

            if (is_array($hidden)) {
                $hiddenfields = new html_hiddenfield($hidden);
                $form_start .= $hiddenfields->show();
            }

            $form_end  = !strlen($attrib['form']) ? '</form>' : '';
            $edit_form = !empty($attrib['form']) ? $attrib['form'] : 'form';

            $rcmail->output->add_gui_object('editform', $edit_form);
        }

        return array($form_start, $form_end);
    }
}
