<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/rcube_ui.php                                          |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 | Copyright (C) 2011-2012, Kolab Systems AG                             |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide basic functions for the webmail user interface              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 | Author: Aleksander Machniak <alec@alec.pl>                            |
 +-----------------------------------------------------------------------+

 $Id$

*/

/**
 * Roundcube Webmail functions for user interface
 *
 * @package Core
 * @author Thomas Bruederli <roundcube@gmail.com>
 * @author Aleksander Machniak <alec@alec.pl>
 */
class rcube_ui
{
    // define constants for input reading
    const INPUT_GET  = 0x0101;
    const INPUT_POST = 0x0102;
    const INPUT_GPC  = 0x0103;


    /**
     * Get localized text in the desired language
     * It's a global wrapper for rcube::gettext()
     *
     * @param mixed  $p      Named parameters array or label name
     * @param string $domain Domain to search in (e.g. plugin name)
     *
     * @return string Localized text
     * @see rcube::gettext()
     */
    public static function label($p, $domain = null)
    {
        return rcube::get_instance()->gettext($p, $domain);
    }


    /**
     * Global wrapper of rcube::text_exists()
     * to check whether a text label is defined
     *
     * @see rcube::text_exists()
     */
    public static function label_exists($name, $domain = null, &$ref_domain = null)
    {
        return rcube::get_instance()->text_exists($name, $domain, $ref_domain);
    }


    /**
     * Compose an URL for a specific action
     *
     * @param string  Request action
     * @param array   More URL parameters
     * @param string  Request task (omit if the same)
     *
     * @return The application URL
     */
    public static function url($action, $p = array(), $task = null)
    {
        return rcube::get_instance()->url((array)$p + array('_action' => $action, 'task' => $task));
    }


    /**
     * Replacing specials characters to a specific encoding type
     *
     * @param  string  Input string
     * @param  string  Encoding type: text|html|xml|js|url
     * @param  string  Replace mode for tags: show|replace|remove
     * @param  boolean Convert newlines
     *
     * @return string  The quoted string
     */
    public static function rep_specialchars_output($str, $enctype = '', $mode = '', $newlines = true)
    {
        static $html_encode_arr = false;
        static $js_rep_table = false;
        static $xml_rep_table = false;

        // encode for HTML output
        if ($enctype == 'html') {
            if (!$html_encode_arr) {
                $html_encode_arr = get_html_translation_table(HTML_SPECIALCHARS);
                unset($html_encode_arr['?']);
            }

            $encode_arr = $html_encode_arr;

            // don't replace quotes and html tags
            if ($mode == 'show' || $mode == '') {
                $ltpos = strpos($str, '<');
                if ($ltpos !== false && strpos($str, '>', $ltpos) !== false) {
                    unset($encode_arr['"']);
                    unset($encode_arr['<']);
                    unset($encode_arr['>']);
                    unset($encode_arr['&']);
                }
            }
            else if ($mode == 'remove') {
                $str = strip_tags($str);
            }

            $out = strtr($str, $encode_arr);

            // avoid douple quotation of &
            $out = preg_replace('/&amp;([A-Za-z]{2,6}|#[0-9]{2,4});/', '&\\1;', $out);

            return $newlines ? nl2br($out) : $out;
        }

        // if the replace tables for XML and JS are not yet defined
        if ($js_rep_table === false) {
            $js_rep_table = $xml_rep_table = array();
            $xml_rep_table['&'] = '&amp;';

            // can be increased to support more charsets
            for ($c=160; $c<256; $c++) {
                $xml_rep_table[chr($c)] = "&#$c;";
            }

            $xml_rep_table['"'] = '&quot;';
            $js_rep_table['"']  = '\\"';
            $js_rep_table["'"]  = "\\'";
            $js_rep_table["\\"] = "\\\\";
            // Unicode line and paragraph separators (#1486310)
            $js_rep_table[chr(hexdec(E2)).chr(hexdec(80)).chr(hexdec(A8))] = '&#8232;';
            $js_rep_table[chr(hexdec(E2)).chr(hexdec(80)).chr(hexdec(A9))] = '&#8233;';
        }

        // encode for javascript use
        if ($enctype == 'js') {
            return preg_replace(array("/\r?\n/", "/\r/", '/<\\//'), array('\n', '\n', '<\\/'), strtr($str, $js_rep_table));
        }

        // encode for plaintext
        if ($enctype == 'text') {
            return str_replace("\r\n", "\n", $mode=='remove' ? strip_tags($str) : $str);
        }

        if ($enctype == 'url') {
            return rawurlencode($str);
        }

        // encode for XML
        if ($enctype == 'xml') {
            return strtr($str, $xml_rep_table);
        }

        // no encoding given -> return original string
        return $str;
    }


    /**
     * Quote a given string.
     * Shortcut function for self::rep_specialchars_output()
     *
     * @return string HTML-quoted string
     * @see self::rep_specialchars_output()
     */
    public static function Q($str, $mode = 'strict', $newlines = true)
    {
        return self::rep_specialchars_output($str, 'html', $mode, $newlines);
    }


    /**
     * Quote a given string for javascript output.
     * Shortcut function for self::rep_specialchars_output()
     *
     * @return string JS-quoted string
     * @see self::rep_specialchars_output()
     */
    public static function JQ($str)
    {
        return self::rep_specialchars_output($str, 'js');
    }


    /**
     * Read input value and convert it for internal use
     * Performs stripslashes() and charset conversion if necessary
     *
     * @param  string   Field name to read
     * @param  int      Source to get value from (GPC)
     * @param  boolean  Allow HTML tags in field value
     * @param  string   Charset to convert into
     *
     * @return string   Field value or NULL if not available
     */
    public static function get_input_value($fname, $source, $allow_html=FALSE, $charset=NULL)
    {
        $value = NULL;

        if ($source == self::INPUT_GET) {
            if (isset($_GET[$fname])) {
                $value = $_GET[$fname];
            }
        }
        else if ($source == self::INPUT_POST) {
            if (isset($_POST[$fname])) {
                $value = $_POST[$fname];
            }
        }
        else if ($source == self::INPUT_GPC) {
            if (isset($_POST[$fname])) {
                $value = $_POST[$fname];
            }
            else if (isset($_GET[$fname])) {
                $value = $_GET[$fname];
            }
            else if (isset($_COOKIE[$fname])) {
                $value = $_COOKIE[$fname];
            }
        }

        return self::parse_input_value($value, $allow_html, $charset);
    }

    /**
     * Parse/validate input value. See self::get_input_value()
     * Performs stripslashes() and charset conversion if necessary
     *
     * @param  string   Input value
     * @param  boolean  Allow HTML tags in field value
     * @param  string   Charset to convert into
     *
     * @return string   Parsed value
     */
    public static function parse_input_value($value, $allow_html=FALSE, $charset=NULL)
    {
        global $OUTPUT;

        if (empty($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $idx => $val) {
                $value[$idx] = self::parse_input_value($val, $allow_html, $charset);
            }
            return $value;
        }

        // strip single quotes if magic_quotes_sybase is enabled
        if (ini_get('magic_quotes_sybase')) {
            $value = str_replace("''", "'", $value);
        }
        // strip slashes if magic_quotes enabled
        else if (get_magic_quotes_gpc() || get_magic_quotes_runtime()) {
            $value = stripslashes($value);
        }

        // remove HTML tags if not allowed
        if (!$allow_html) {
            $value = strip_tags($value);
        }

        $output_charset = is_object($OUTPUT) ? $OUTPUT->get_charset() : null;

        // remove invalid characters (#1488124)
        if ($output_charset == 'UTF-8') {
            $value = rcube_charset::clean($value);
        }

        // convert to internal charset
        if ($charset && $output_charset) {
            $value = rcube_charset::convert($value, $output_charset, $charset);
        }

        return $value;
    }


    /**
     * Convert array of request parameters (prefixed with _)
     * to a regular array with non-prefixed keys.
     *
     * @param int    $mode   Source to get value from (GPC)
     * @param string $ignore PCRE expression to skip parameters by name
     *
     * @return array Hash array with all request parameters
     */
    public static function request2param($mode = null, $ignore = 'task|action')
    {
        $out = array();
        $src = $mode == self::INPUT_GET ? $_GET : ($mode == self::INPUT_POST ? $_POST : $_REQUEST);

        foreach ($src as $key => $value) {
            $fname = $key[0] == '_' ? substr($key, 1) : $key;
            if ($ignore && !preg_match('/^(' . $ignore . ')$/', $fname)) {
                $out[$fname] = self::get_input_value($key, $mode);
            }
        }

        return $out;
    }


    /**
     * Convert the given string into a valid HTML identifier
     * Same functionality as done in app.js with rcube_webmail.html_identifier()
     */
    public static function html_identifier($str, $encode=false)
    {
        if ($encode) {
            return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
        }
        else {
            return asciiwords($str, true, '_');
        }
    }


    /**
     * Create a HTML table based on the given data
     *
     * @param  array  Named table attributes
     * @param  mixed  Table row data. Either a two-dimensional array or a valid SQL result set
     * @param  array  List of cols to show
     * @param  string Name of the identifier col
     *
     * @return string HTML table code
     */
    public static function table_output($attrib, $table_data, $a_show_cols, $id_col)
    {
        global $RCMAIL;

        $table = new html_table(/*array('cols' => count($a_show_cols))*/);

        // add table header
        if (!$attrib['noheader']) {
            foreach ($a_show_cols as $col) {
                $table->add_header($col, self::Q(self::label($col)));
            }
        }

        if (!is_array($table_data)) {
            $db = $RCMAIL->get_dbh();
            while ($table_data && ($sql_arr = $db->fetch_assoc($table_data))) {
                $table->add_row(array('id' => 'rcmrow' . self::html_identifier($sql_arr[$id_col])));

                // format each col
                foreach ($a_show_cols as $col) {
                    $table->add($col, self::Q($sql_arr[$col]));
                }
            }
        }
        else {
            foreach ($table_data as $row_data) {
                $class = !empty($row_data['class']) ? $row_data['class'] : '';
                $rowid = 'rcmrow' . self::html_identifier($row_data[$id_col]);

                $table->add_row(array('id' => $rowid, 'class' => $class));

                // format each col
                foreach ($a_show_cols as $col) {
                    $table->add($col, self::Q(is_array($row_data[$col]) ? $row_data[$col][0] : $row_data[$col]));
                }
            }
        }

        return $table->show($attrib);
    }


    /**
     * Create an edit field for inclusion on a form
     *
     * @param string col field name
     * @param string value field value
     * @param array attrib HTML element attributes for field
     * @param string type HTML element type (default 'text')
     *
     * @return string HTML field definition
     */
    public static function get_edit_field($col, $value, $attrib, $type = 'text')
    {
        static $colcounts = array();

        $fname = '_'.$col;
        $attrib['name']  = $fname . ($attrib['array'] ? '[]' : '');
        $attrib['class'] = trim($attrib['class'] . ' ff_' . $col);

        if ($type == 'checkbox') {
            $attrib['value'] = '1';
            $input = new html_checkbox($attrib);
        }
        else if ($type == 'textarea') {
            $attrib['cols'] = $attrib['size'];
            $input = new html_textarea($attrib);
        }
        else if ($type == 'select') {
            $input = new html_select($attrib);
            $input->add('---', '');
            $input->add(array_values($attrib['options']), array_keys($attrib['options']));
        }
        else if ($attrib['type'] == 'password') {
            $input = new html_passwordfield($attrib);
        }
        else {
            if ($attrib['type'] != 'text' && $attrib['type'] != 'hidden') {
                $attrib['type'] = 'text';
            }
            $input = new html_inputfield($attrib);
        }

        // use value from post
        if (isset($_POST[$fname])) {
            $postvalue = self::get_input_value($fname, self::INPUT_POST, true);
            $value = $attrib['array'] ? $postvalue[intval($colcounts[$col]++)] : $postvalue;
        }

        $out = $input->show($value);

        return $out;
    }


    /**
     * Replace all css definitions with #container [def]
     * and remove css-inlined scripting
     *
     * @param string CSS source code
     * @param string Container ID to use as prefix
     *
     * @return string Modified CSS source
     * @todo I'm not sure this should belong to rcube_ui class
     */
    public static function mod_css_styles($source, $container_id, $allow_remote=false)
    {
        $last_pos = 0;
        $replacements = new rcube_string_replacer;

        // ignore the whole block if evil styles are detected
        $source   = self::xss_entity_decode($source);
        $stripped = preg_replace('/[^a-z\(:;]/i', '', $source);
        $evilexpr = 'expression|behavior|javascript:|import[^a]' . (!$allow_remote ? '|url\(' : '');
        if (preg_match("/$evilexpr/i", $stripped)) {
            return '/* evil! */';
        }

        // cut out all contents between { and }
        while (($pos = strpos($source, '{', $last_pos)) && ($pos2 = strpos($source, '}', $pos))) {
            $styles = substr($source, $pos+1, $pos2-($pos+1));

            // check every line of a style block...
            if ($allow_remote) {
                $a_styles = preg_split('/;[\r\n]*/', $styles, -1, PREG_SPLIT_NO_EMPTY);
                foreach ($a_styles as $line) {
                    $stripped = preg_replace('/[^a-z\(:;]/i', '', $line);
                    // ... and only allow strict url() values
                    $regexp = '!url\s*\([ "\'](https?:)//[a-z0-9/._+-]+["\' ]\)!Uims';
                    if (stripos($stripped, 'url(') && !preg_match($regexp, $line)) {
                        $a_styles = array('/* evil! */');
                        break;
                    }
                }
                $styles = join(";\n", $a_styles);
            }

            $key = $replacements->add($styles);
            $source = substr($source, 0, $pos+1)
                . $replacements->get_replacement($key)
                . substr($source, $pos2, strlen($source)-$pos2);
            $last_pos = $pos+2;
        }

        // remove html comments and add #container to each tag selector.
        // also replace body definition because we also stripped off the <body> tag
        $styles = preg_replace(
            array(
                '/(^\s*<!--)|(-->\s*$)/',
                '/(^\s*|,\s*|\}\s*)([a-z0-9\._#\*][a-z0-9\.\-_]*)/im',
                '/'.preg_quote($container_id, '/').'\s+body/i',
            ),
            array(
                '',
                "\\1#$container_id \\2",
                $container_id,
            ),
            $source);

        // put block contents back in
        $styles = $replacements->resolve($styles);

        return $styles;
    }


    /**
     * Convert the given date to a human readable form
     * This uses the date formatting properties from config
     *
     * @param mixed  Date representation (string, timestamp or DateTime object)
     * @param string Date format to use
     * @param bool   Enables date convertion according to user timezone
     *
     * @return string Formatted date string
     */
    public static function format_date($date, $format = null, $convert = true)
    {
        global $RCMAIL, $CONFIG;

        if (is_object($date) && is_a($date, 'DateTime')) {
            $timestamp = $date->format('U');
        }
        else {
            if (!empty($date)) {
                $timestamp = rcube_strtotime($date);
            }

            if (empty($timestamp)) {
                return '';
            }

            try {
                $date = new DateTime("@".$timestamp);
            }
            catch (Exception $e) {
                return '';
            }
        }

        if ($convert) {
            try {
                // convert to the right timezone
                $stz = date_default_timezone_get();
                $tz = new DateTimeZone($RCMAIL->config->get('timezone'));
                $date->setTimezone($tz);
                date_default_timezone_set($tz->getName());

                $timestamp = $date->format('U');
            }
            catch (Exception $e) {
            }
        }

        // define date format depending on current time
        if (!$format) {
            $now         = time();
            $now_date    = getdate($now);
            $today_limit = mktime(0, 0, 0, $now_date['mon'], $now_date['mday'], $now_date['year']);
            $week_limit  = mktime(0, 0, 0, $now_date['mon'], $now_date['mday']-6, $now_date['year']);

            if ($CONFIG['prettydate'] && $timestamp > $today_limit && $timestamp < $now) {
                $format = $RCMAIL->config->get('date_today', $RCMAIL->config->get('time_format', 'H:i'));
                $today  = true;
            }
            else if ($CONFIG['prettydate'] && $timestamp > $week_limit && $timestamp < $now) {
                $format = $RCMAIL->config->get('date_short', 'D H:i');
            }
            else {
                $format = $RCMAIL->config->get('date_long', 'Y-m-d H:i');
            }
        }

        // strftime() format
        if (preg_match('/%[a-z]+/i', $format)) {
            $format = strftime($format, $timestamp);
            if ($stz) {
                date_default_timezone_set($stz);
            }
            return $today ? (self::label('today') . ' ' . $format) : $format;
        }

        // parse format string manually in order to provide localized weekday and month names
        // an alternative would be to convert the date() format string to fit with strftime()
        $out = '';
        for ($i=0; $i<strlen($format); $i++) {
            if ($format[$i] == "\\") {  // skip escape chars
                continue;
            }

            // write char "as-is"
            if ($format[$i] == ' ' || $format[$i-1] == "\\") {
                $out .= $format[$i];
            }
            // weekday (short)
            else if ($format[$i] == 'D') {
                $out .= self::label(strtolower(date('D', $timestamp)));
            }
            // weekday long
            else if ($format[$i] == 'l') {
                $out .= self::label(strtolower(date('l', $timestamp)));
            }
            // month name (short)
            else if ($format[$i] == 'M') {
                $out .= self::label(strtolower(date('M', $timestamp)));
            }
            // month name (long)
            else if ($format[$i] == 'F') {
                $out .= self::label('long'.strtolower(date('M', $timestamp)));
            }
            else if ($format[$i] == 'x') {
                $out .= strftime('%x %X', $timestamp);
            }
            else {
                $out .= date($format[$i], $timestamp);
            }
        }

        if ($today) {
            $label = self::label('today');
            // replcae $ character with "Today" label (#1486120)
            if (strpos($out, '$') !== false) {
                $out = preg_replace('/\$/', $label, $out, 1);
            }
            else {
                $out = $label . ' ' . $out;
            }
        }

        if ($stz) {
            date_default_timezone_set($stz);
        }

        return $out;
    }


    /**
     * Return folders list in HTML
     *
     * @param array $attrib Named parameters
     *
     * @return string HTML code for the gui object
     */
    public static function folder_list($attrib)
    {
        global $RCMAIL;
        static $a_mailboxes;

        $attrib += array('maxlength' => 100, 'realnames' => false, 'unreadwrap' => ' (%s)');

        // add some labels to client
        $RCMAIL->output->add_label('purgefolderconfirm', 'deletemessagesconfirm');

        $type = $attrib['type'] ? $attrib['type'] : 'ul';
        unset($attrib['type']);

        if ($type == 'ul' && !$attrib['id']) {
            $attrib['id'] = 'rcmboxlist';
        }

        if (empty($attrib['folder_name'])) {
            $attrib['folder_name'] = '*';
        }

        // get current folder
        $mbox_name = $RCMAIL->storage->get_folder();

        // build the folders tree
        if (empty($a_mailboxes)) {
            // get mailbox list
            $a_folders = $RCMAIL->storage->list_folders_subscribed(
                '', $attrib['folder_name'], $attrib['folder_filter']);
            $delimiter = $RCMAIL->storage->get_hierarchy_delimiter();
            $a_mailboxes = array();

            foreach ($a_folders as $folder) {
                self::build_folder_tree($a_mailboxes, $folder, $delimiter);
            }
        }

        // allow plugins to alter the folder tree or to localize folder names
        $hook = $RCMAIL->plugins->exec_hook('render_mailboxlist', array(
            'list'      => $a_mailboxes,
            'delimiter' => $delimiter,
            'type'      => $type,
            'attribs'   => $attrib,
        ));

        $a_mailboxes = $hook['list'];
        $attrib      = $hook['attribs'];

        if ($type == 'select') {
            $select = new html_select($attrib);

            // add no-selection option
            if ($attrib['noselection']) {
                $select->add(self::label($attrib['noselection']), '');
            }

            self::render_folder_tree_select($a_mailboxes, $mbox_name, $attrib['maxlength'], $select, $attrib['realnames']);
            $out = $select->show($attrib['default']);
        }
        else {
            $js_mailboxlist = array();
            $out = html::tag('ul', $attrib, self::render_folder_tree_html($a_mailboxes, $mbox_name, $js_mailboxlist, $attrib), html::$common_attrib);

            $RCMAIL->output->add_gui_object('mailboxlist', $attrib['id']);
            $RCMAIL->output->set_env('mailboxes', $js_mailboxlist);
            $RCMAIL->output->set_env('unreadwrap', $attrib['unreadwrap']);
            $RCMAIL->output->set_env('collapsed_folders', (string)$RCMAIL->config->get('collapsed_folders'));
        }

        return $out;
    }


    /**
     * Return folders list as html_select object
     *
     * @param array $p  Named parameters
     *
     * @return html_select HTML drop-down object
     */
    public static function folder_selector($p = array())
    {
        global $RCMAIL;

        $p += array('maxlength' => 100, 'realnames' => false);
        $a_mailboxes = array();
        $storage = $RCMAIL->get_storage();

        if (empty($p['folder_name'])) {
            $p['folder_name'] = '*';
        }

        if ($p['unsubscribed']) {
            $list = $storage->list_folders('', $p['folder_name'], $p['folder_filter'], $p['folder_rights']);
        }
        else {
            $list = $storage->list_folders_subscribed('', $p['folder_name'], $p['folder_filter'], $p['folder_rights']);
        }

        $delimiter = $storage->get_hierarchy_delimiter();

        foreach ($list as $folder) {
            if (empty($p['exceptions']) || !in_array($folder, $p['exceptions'])) {
                self::build_folder_tree($a_mailboxes, $folder, $delimiter);
            }
        }

        $select = new html_select($p);

        if ($p['noselection']) {
            $select->add($p['noselection'], '');
        }

        self::render_folder_tree_select($a_mailboxes, $mbox, $p['maxlength'], $select, $p['realnames'], 0, $p);

        return $select;
    }


    /**
     * Create a hierarchical array of the mailbox list
     */
    private static function build_folder_tree(&$arrFolders, $folder, $delm = '/', $path = '')
    {
        global $RCMAIL;

        // Handle namespace prefix
        $prefix = '';
        if (!$path) {
            $n_folder = $folder;
            $folder = $RCMAIL->storage->mod_folder($folder);

            if ($n_folder != $folder) {
                $prefix = substr($n_folder, 0, -strlen($folder));
            }
        }

        $pos = strpos($folder, $delm);

        if ($pos !== false) {
            $subFolders    = substr($folder, $pos+1);
            $currentFolder = substr($folder, 0, $pos);

            // sometimes folder has a delimiter as the last character
            if (!strlen($subFolders)) {
                $virtual = false;
            }
            else if (!isset($arrFolders[$currentFolder])) {
                $virtual = true;
            }
            else {
                $virtual = $arrFolders[$currentFolder]['virtual'];
            }
        }
        else {
            $subFolders    = false;
            $currentFolder = $folder;
            $virtual       = false;
        }

        $path .= $prefix . $currentFolder;

        if (!isset($arrFolders[$currentFolder])) {
            $arrFolders[$currentFolder] = array(
                'id' => $path,
                'name' => rcube_charset::convert($currentFolder, 'UTF7-IMAP'),
                'virtual' => $virtual,
                'folders' => array());
        }
        else {
            $arrFolders[$currentFolder]['virtual'] = $virtual;
        }

        if (strlen($subFolders)) {
            self::build_folder_tree($arrFolders[$currentFolder]['folders'], $subFolders, $delm, $path.$delm);
        }
    }


    /**
     * Return html for a structured list &lt;ul&gt; for the mailbox tree
     */
    private static function render_folder_tree_html(&$arrFolders, &$mbox_name, &$jslist, $attrib, $nestLevel = 0)
    {
        global $RCMAIL;

        $maxlength = intval($attrib['maxlength']);
        $realnames = (bool)$attrib['realnames'];
        $msgcounts = $RCMAIL->storage->get_cache('messagecount');
        $collapsed = $RCMAIL->config->get('collapsed_folders');

        $out = '';
        foreach ($arrFolders as $key => $folder) {
            $title        = null;
            $folder_class = self::folder_classname($folder['id']);
            $collapsed    = strpos($collapsed, '&'.rawurlencode($folder['id']).'&') !== false;
            $unread       = $msgcounts ? intval($msgcounts[$folder['id']]['UNSEEN']) : 0;

            if ($folder_class && !$realnames) {
                $foldername = $RCMAIL->gettext($folder_class);
            }
            else {
                $foldername = $folder['name'];

                // shorten the folder name to a given length
                if ($maxlength && $maxlength > 1) {
                    $fname = abbreviate_string($foldername, $maxlength);
                    if ($fname != $foldername) {
                        $title = $foldername;
                    }
                    $foldername = $fname;
                }
            }

            // make folder name safe for ids and class names
            $folder_id = self::html_identifier($folder['id'], true);
            $classes   = array('mailbox');

            // set special class for Sent, Drafts, Trash and Junk
            if ($folder_class) {
                $classes[] = $folder_class;
            }

            if ($folder['id'] == $mbox_name) {
                $classes[] = 'selected';
            }

            if ($folder['virtual']) {
                $classes[] = 'virtual';
            }
            else if ($unread) {
                $classes[] = 'unread';
            }

            $js_name = self::JQ($folder['id']);
            $html_name = self::Q($foldername) . ($unread ? html::span('unreadcount', sprintf($attrib['unreadwrap'], $unread)) : '');
            $link_attrib = $folder['virtual'] ? array() : array(
                'href' => self::url('', array('_mbox' => $folder['id'])),
                'onclick' => sprintf("return %s.command('list','%s',this)", JS_OBJECT_NAME, $js_name),
                'rel' => $folder['id'],
                'title' => $title,
            );

            $out .= html::tag('li', array(
                'id' => "rcmli".$folder_id,
                'class' => join(' ', $classes),
                'noclose' => true),
                html::a($link_attrib, $html_name) .
                (!empty($folder['folders']) ? html::div(array(
                    'class' => ($collapsed ? 'collapsed' : 'expanded'),
                    'style' => "position:absolute",
                    'onclick' => sprintf("%s.command('collapse-folder', '%s')", JS_OBJECT_NAME, $js_name)
                ), '&nbsp;') : ''));

            $jslist[$folder_id] = array(
                'id'      => $folder['id'],
                'name'    => $foldername,
                'virtual' => $folder['virtual']
            );

            if (!empty($folder['folders'])) {
                $out .= html::tag('ul', array('style' => ($collapsed ? "display:none;" : null)),
                    self::render_folder_tree_html($folder['folders'], $mbox_name, $jslist, $attrib, $nestLevel+1));
            }

            $out .= "</li>\n";
        }

        return $out;
    }


    /**
     * Return html for a flat list <select> for the mailbox tree
     */
    private static function render_folder_tree_select(&$arrFolders, &$mbox_name, $maxlength, &$select, $realnames = false, $nestLevel = 0, $opts = array())
    {
        global $RCMAIL;

        $out = '';

        foreach ($arrFolders as $key => $folder) {
            // skip exceptions (and its subfolders)
            if (!empty($opts['exceptions']) && in_array($folder['id'], $opts['exceptions'])) {
                continue;
            }

            // skip folders in which it isn't possible to create subfolders
            if (!empty($opts['skip_noinferiors'])) {
                $attrs = $RCMAIL->storage->folder_attributes($folder['id']);
                if ($attrs && in_array('\\Noinferiors', $attrs)) {
                    continue;
                }
            }

            if (!$realnames && ($folder_class = self::folder_classname($folder['id']))) {
                $foldername = self::label($folder_class);
            }
            else {
                $foldername = $folder['name'];

                // shorten the folder name to a given length
                if ($maxlength && $maxlength > 1) {
                    $foldername = abbreviate_string($foldername, $maxlength);
                }

                 $select->add(str_repeat('&nbsp;', $nestLevel*4) . $foldername, $folder['id']);

                if (!empty($folder['folders'])) {
                    $out .= self::render_folder_tree_select($folder['folders'], $mbox_name, $maxlength,
                        $select, $realnames, $nestLevel+1, $opts);
                }
            }
        }

        return $out;
    }


    /**
     * Return internal name for the given folder if it matches the configured special folders
     */
    private static function folder_classname($folder_id)
    {
        global $CONFIG;

        if ($folder_id == 'INBOX') {
            return 'inbox';
        }

        // for these mailboxes we have localized labels and css classes
        foreach (array('sent', 'drafts', 'trash', 'junk') as $smbx)
        {
            if ($folder_id == $CONFIG[$smbx.'_mbox']) {
                return $smbx;
            }
        }
    }


    /**
     * Try to localize the given IMAP folder name.
     * UTF-7 decode it in case no localized text was found
     *
     * @param string $name  Folder name
     *
     * @return string Localized folder name in UTF-8 encoding
     */
    public static function localize_foldername($name)
    {
        if ($folder_class = self::folder_classname($name)) {
            return self::label($folder_class);
        }
        else {
            return rcube_charset::convert($name, 'UTF7-IMAP');
        }
    }


    public static function localize_folderpath($path)
    {
        global $RCMAIL;

        $protect_folders = $RCMAIL->config->get('protect_default_folders');
        $default_folders = (array) $RCMAIL->config->get('default_folders');
        $delimiter       = $RCMAIL->storage->get_hierarchy_delimiter();
        $path            = explode($delimiter, $path);
        $result          = array();

        foreach ($path as $idx => $dir) {
            $directory = implode($delimiter, array_slice($path, 0, $idx+1));
            if ($protect_folders && in_array($directory, $default_folders)) {
                unset($result);
                $result[] = self::localize_foldername($directory);
            }
            else {
                $result[] = rcube_charset::convert($dir, 'UTF7-IMAP');
            }
        }

        return implode($delimiter, $result);
    }


    public static function quota_display($attrib)
    {
        global $OUTPUT;

        if (!$attrib['id']) {
            $attrib['id'] = 'rcmquotadisplay';
        }

        $_SESSION['quota_display'] = !empty($attrib['display']) ? $attrib['display'] : 'text';

        $OUTPUT->add_gui_object('quotadisplay', $attrib['id']);

        $quota = self::quota_content($attrib);

        $OUTPUT->add_script('rcmail.set_quota('.rcube_output::json_serialize($quota).');', 'docready');

        return html::span($attrib, '');
    }


    public static function quota_content($attrib = null)
    {
        global $RCMAIL;

        $quota = $RCMAIL->storage->get_quota();
        $quota = $RCMAIL->plugins->exec_hook('quota', $quota);

        $quota_result = (array) $quota;
        $quota_result['type'] = isset($_SESSION['quota_display']) ? $_SESSION['quota_display'] : '';

        if (!$quota['total'] && $RCMAIL->config->get('quota_zero_as_unlimited')) {
            $quota_result['title']   = self::label('unlimited');
            $quota_result['percent'] = 0;
        }
        else if ($quota['total']) {
            if (!isset($quota['percent'])) {
                $quota_result['percent'] = min(100, round(($quota['used']/max(1,$quota['total']))*100));
            }

            $title = sprintf('%s / %s (%.0f%%)',
                self::show_bytes($quota['used'] * 1024), self::show_bytes($quota['total'] * 1024),
                $quota_result['percent']);

            $quota_result['title'] = $title;

            if ($attrib['width']) {
                $quota_result['width'] = $attrib['width'];
            }
            if ($attrib['height']) {
                $quota_result['height']	= $attrib['height'];
            }
        }
        else {
            $quota_result['title']   = self::label('unknown');
            $quota_result['percent'] = 0;
        }

        return $quota_result;
    }


    /**
     * Outputs error message according to server error/response codes
     *
     * @param string $fallback       Fallback message label
     * @param array  $fallback_args  Fallback message label arguments
     */
    public static function display_server_error($fallback = null, $fallback_args = null)
    {
        global $RCMAIL;

        $err_code = $RCMAIL->storage->get_error_code();
        $res_code = $RCMAIL->storage->get_response_code();

        if ($err_code < 0) {
            $RCMAIL->output->show_message('storageerror', 'error');
        }
        else if ($res_code == rcube_storage::NOPERM) {
            $RCMAIL->output->show_message('errornoperm', 'error');
        }
        else if ($res_code == rcube_storage::READONLY) {
            $RCMAIL->output->show_message('errorreadonly', 'error');
        }
        else if ($err_code && ($err_str = $RCMAIL->storage->get_error_str())) {
            // try to detect access rights problem and display appropriate message
            if (stripos($err_str, 'Permission denied') !== false) {
                $RCMAIL->output->show_message('errornoperm', 'error');
            }
            else {
                $RCMAIL->output->show_message('servererrormsg', 'error', array('msg' => $err_str));
            }
        }
        else if ($fallback) {
            $RCMAIL->output->show_message($fallback, 'error', $fallback_args);
        }
    }


    /**
     * Generate CSS classes from mimetype and filename extension
     *
     * @param string $mimetype  Mimetype
     * @param string $filename  Filename
     *
     * @return string CSS classes separated by space
     */
    public static function file2class($mimetype, $filename)
    {
        list($primary, $secondary) = explode('/', $mimetype);

        $classes = array($primary ? $primary : 'unknown');
        if ($secondary) {
            $classes[] = $secondary;
        }
        if (preg_match('/\.([a-z0-9]+)$/i', $filename, $m)) {
            $classes[] = $m[1];
        }

        return strtolower(join(" ", $classes));
    }


    /**
     * Output HTML editor scripts
     *
     * @param string $mode  Editor mode
     */
    public static function html_editor($mode = '')
    {
        global $RCMAIL;

        $hook = $RCMAIL->plugins->exec_hook('html_editor', array('mode' => $mode));

        if ($hook['abort']) {
            return;
        }

        $lang = strtolower($_SESSION['language']);

        // TinyMCE uses two-letter lang codes, with exception of Chinese
        if (strpos($lang, 'zh_') === 0) {
            $lang = str_replace('_', '-', $lang);
        }
        else {
            $lang = substr($lang, 0, 2);
        }

        if (!file_exists(INSTALL_PATH . 'program/js/tiny_mce/langs/'.$lang.'.js')) {
            $lang = 'en';
        }

        $script = json_encode(array(
            'mode'       => $mode,
            'lang'       => $lang,
            'skin_path'  => $RCMAIL->output->get_skin_path(),
            'spellcheck' => intval($RCMAIL->config->get('enable_spellcheck')),
            'spelldict'  => intval($RCMAIL->config->get('spellcheck_dictionary'))
        ));

        $RCMAIL->output->include_script('tiny_mce/tiny_mce.js');
        $RCMAIL->output->include_script('editor.js');
        $RCMAIL->output->add_script("rcmail_editor_init($script)", 'docready');
    }


    /**
     * Replaces TinyMCE's emoticon images with plain-text representation
     *
     * @param string $html  HTML content
     *
     * @return string HTML content
     */
    public static function replace_emoticons($html)
    {
        $emoticons = array(
            '8-)' => 'smiley-cool',
            ':-#' => 'smiley-foot-in-mouth',
            ':-*' => 'smiley-kiss',
            ':-X' => 'smiley-sealed',
            ':-P' => 'smiley-tongue-out',
            ':-@' => 'smiley-yell',
            ":'(" => 'smiley-cry',
            ':-(' => 'smiley-frown',
            ':-D' => 'smiley-laughing',
            ':-)' => 'smiley-smile',
            ':-S' => 'smiley-undecided',
            ':-$' => 'smiley-embarassed',
            'O:-)' => 'smiley-innocent',
            ':-|' => 'smiley-money-mouth',
            ':-O' => 'smiley-surprised',
            ';-)' => 'smiley-wink',
        );

        foreach ($emoticons as $idx => $file) {
            // <img title="Cry" src="http://.../program/js/tiny_mce/plugins/emotions/img/smiley-cry.gif" border="0" alt="Cry" />
            $search[]  = '/<img title="[a-z ]+" src="https?:\/\/[a-z0-9_.\/-]+\/tiny_mce\/plugins\/emotions\/img\/'.$file.'.gif"[^>]+\/>/i';
            $replace[] = $idx;
        }

        return preg_replace($search, $replace, $html);
    }


    /**
     * File upload progress handler.
     */
    public static function upload_progress()
    {
        global $RCMAIL;

        $prefix = ini_get('apc.rfc1867_prefix');
        $params = array(
            'action' => $RCMAIL->action,
            'name' => self::get_input_value('_progress', self::INPUT_GET),
        );

        if (function_exists('apc_fetch')) {
            $status = apc_fetch($prefix . $params['name']);

            if (!empty($status)) {
                $status['percent'] = round($status['current']/$status['total']*100);
                $params = array_merge($status, $params);
            }
        }

        if (isset($params['percent']))
            $params['text'] = self::label(array('name' => 'uploadprogress', 'vars' => array(
                'percent' => $params['percent'] . '%',
                'current' => self::show_bytes($params['current']),
                'total'   => self::show_bytes($params['total'])
        )));

        $RCMAIL->output->command('upload_progress_update', $params);
        $RCMAIL->output->send();
    }


    /**
     * Initializes file uploading interface.
     */
    public static function upload_init()
    {
        global $RCMAIL;

        // Enable upload progress bar
        if (($seconds = $RCMAIL->config->get('upload_progress')) && ini_get('apc.rfc1867')) {
            if ($field_name = ini_get('apc.rfc1867_name')) {
                $RCMAIL->output->set_env('upload_progress_name', $field_name);
                $RCMAIL->output->set_env('upload_progress_time', (int) $seconds);
            }
        }

        // find max filesize value
        $max_filesize = parse_bytes(ini_get('upload_max_filesize'));
        $max_postsize = parse_bytes(ini_get('post_max_size'));
        if ($max_postsize && $max_postsize < $max_filesize) {
            $max_filesize = $max_postsize;
        }

        $RCMAIL->output->set_env('max_filesize', $max_filesize);
        $max_filesize = self::show_bytes($max_filesize);
        $RCMAIL->output->set_env('filesizeerror', self::label(array(
            'name' => 'filesizeerror', 'vars' => array('size' => $max_filesize))));

        return $max_filesize;
    }


    /**
     * Initializes client-side autocompletion.
     */
    public static function autocomplete_init()
    {
        global $RCMAIL;
        static $init;

        if ($init) {
            return;
        }

        $init = 1;

        if (($threads = (int)$RCMAIL->config->get('autocomplete_threads')) > 0) {
            $book_types = (array) $RCMAIL->config->get('autocomplete_addressbooks', 'sql');
            if (count($book_types) > 1) {
                $RCMAIL->output->set_env('autocomplete_threads', $threads);
                $RCMAIL->output->set_env('autocomplete_sources', $book_types);
            }
        }

        $RCMAIL->output->set_env('autocomplete_max', (int)$RCMAIL->config->get('autocomplete_max', 15));
        $RCMAIL->output->set_env('autocomplete_min_length', $RCMAIL->config->get('autocomplete_min_length'));
        $RCMAIL->output->add_label('autocompletechars', 'autocompletemore');
    }


    /**
     * Returns supported font-family specifications
     *
     * @param string $font  Font name
     *
     * @param string|array Font-family specification array or string (if $font is used)
     */
    public static function font_defs($font = null)
    {
        $fonts = array(
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
        );

        if ($font) {
            return $fonts[$font];
        }

        return $fonts;
    }


    /**
     * Create a human readable string for a number of bytes
     *
     * @param int Number of bytes
     *
     * @return string Byte string
     */
    public static function show_bytes($bytes)
    {
        if ($bytes >= 1073741824) {
            $gb  = $bytes/1073741824;
            $str = sprintf($gb>=10 ? "%d " : "%.1f ", $gb) . self::label('GB');
        }
        else if ($bytes >= 1048576) {
            $mb  = $bytes/1048576;
            $str = sprintf($mb>=10 ? "%d " : "%.1f ", $mb) . self::label('MB');
        }
        else if ($bytes >= 1024) {
            $str = sprintf("%d ",  round($bytes/1024)) . self::label('KB');
        }
        else {
            $str = sprintf('%d ', $bytes) . self::label('B');
        }

        return $str;
    }


    /**
     * Decode escaped entities used by known XSS exploits.
     * See http://downloads.securityfocus.com/vulnerabilities/exploits/26800.eml for examples
     *
     * @param string CSS content to decode
     *
     * @return string Decoded string
     * @todo I'm not sure this should belong to rcube_ui class
     */
    public static function xss_entity_decode($content)
    {
        $out = html_entity_decode(html_entity_decode($content));
        $out = preg_replace_callback('/\\\([0-9a-f]{4})/i',
            array(self, 'xss_entity_decode_callback'), $out);
        $out = preg_replace('#/\*.*\*/#Ums', '', $out);

        return $out;
    }


    /**
     * preg_replace_callback callback for xss_entity_decode
     *
     * @param array $matches Result from preg_replace_callback
     *
     * @return string Decoded entity
     */
    public static function xss_entity_decode_callback($matches)
    {
        return chr(hexdec($matches[1]));
    }


    /**
     * Check if we can process not exceeding memory_limit
     *
     * @param integer Required amount of memory
     *
     * @return boolean True if memory won't be exceeded, False otherwise
     */
    public static function mem_check($need)
    {
        $mem_limit = parse_bytes(ini_get('memory_limit'));
        $memory    = function_exists('memory_get_usage') ? memory_get_usage() : 16*1024*1024; // safe value: 16MB

        return $mem_limit > 0 && $memory + $need > $mem_limit ? false : true;
    }


    /**
     * Check if working in SSL mode
     *
     * @param integer $port      HTTPS port number
     * @param boolean $use_https Enables 'use_https' option checking
     *
     * @return boolean
     */
    public static function https_check($port=null, $use_https=true)
    {
        global $RCMAIL;

        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) != 'off') {
            return true;
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https') {
            return true;
        }
        if ($port && $_SERVER['SERVER_PORT'] == $port) {
            return true;
        }
        if ($use_https && isset($RCMAIL) && $RCMAIL->config->get('use_https')) {
            return true;
        }

        return false;
    }

}
