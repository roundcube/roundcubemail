<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/bc.php                                                |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2012, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide deprecated functions aliases for backward compatibility     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Roundcube Webmail deprecated functions
 *
 * @package Webmail
 * @subpackage Legacy
 * @author Thomas Bruederli <roundcube@gmail.com>
 */

// constants for input reading
define('RCUBE_INPUT_GET',  rcube_utils::INPUT_GET);
define('RCUBE_INPUT_POST', rcube_utils::INPUT_POST);
define('RCUBE_INPUT_GPC',  rcube_utils::INPUT_GPC);

define('JS_OBJECT_NAME',   rcmail_output::JS_OBJECT_NAME);
define('RCMAIL_CHARSET',   RCUBE_CHARSET);

function get_table_name($table)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->db->table_name($table);
}

function rcube_label($p, $domain=null)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->gettext($p, $domain);
}

function rcube_label_exists($name, $domain=null, &$ref_domain = null)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->text_exists($name, $domain, $ref_domain);
}

function rcmail_overwrite_action($action)
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->overwrite_action($action);
}

function rcmail_url($action, $p=array(), $task=null)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->url((array)$p + array('_action' => $action, 'task' => $task));
}

function rcmail_temp_gc()
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->gc_temp();
}

function rcube_charset_convert($str, $from, $to=NULL)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_charset::convert($str, $from, $to);
}

function rc_detect_encoding($string, $failover='')
{
    _deprecation_warning(__FUNCTION__);
    return rcube_charset::detect($string, $failover);
}

function rc_utf8_clean($input)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_charset::clean($input);
}

function json_serialize($input)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_output::json_serialize($input);
}

function rep_specialchars_output($str, $enctype='', $mode='', $newlines=true)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::rep_specialchars_output($str, $enctype, $mode, $newlines);
}

function Q($str, $mode='strict', $newlines=true)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::rep_specialchars_output($str, 'html', $mode, $newlines);
}

function JQ($str)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::rep_specialchars_output($str, 'js');
}

function get_input_value($fname, $source, $allow_html=FALSE, $charset=NULL)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::get_input_value($fname, $source, $allow_html, $charset);
}

function parse_input_value($value, $allow_html=FALSE, $charset=NULL)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::parse_input_value($value, $allow_html, $charset);
}

function request2param($mode = RCUBE_INPUT_GPC, $ignore = 'task|action')
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::request2param($mode, $ignore);
}

function html_identifier($str, $encode=false)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::html_identifier($str, $encode);
}

function rcube_table_output($attrib, $table_data, $a_show_cols, $id_col)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->table_output($attrib, $table_data, $a_show_cols, $id_col);
}

function rcmail_get_edit_field($col, $value, $attrib, $type='text')
{
    _deprecation_warning(__FUNCTION__);
    return rcube_output::get_edit_field($col, $value, $attrib, $type);
}

function rcmail_mod_css_styles($source, $container_id, $allow_remote=false)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::mod_css_styles($source, $container_id, $allow_remote);
}

function rcmail_xss_entity_decode($content)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::xss_entity_decode($content);
}

function create_attrib_string($attrib, $allowed_attribs=array('id', 'class', 'style'))
{
    _deprecation_warning(__FUNCTION__);
    return html::attrib_string($attrib, $allowed_attribs);
}

function parse_attrib_string($str)
{
    _deprecation_warning(__FUNCTION__);
    return html::parse_attrib_string($str);
}

function format_date($date, $format=NULL, $convert=true)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->format_date($date, $format, $convert);
}

function rcmail_mailbox_list($attrib)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->folder_list($attrib);
}

function rcmail_mailbox_select($attrib = array())
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->folder_selector($attrib);
}

function rcmail_render_folder_tree_html(&$arrFolders, &$mbox_name, &$jslist, $attrib, $nestLevel = 0)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->render_folder_tree_html($arrFolders, $mbox_name, $jslist, $attrib, $nestLevel);
}

function rcmail_render_folder_tree_select(&$arrFolders, &$mbox_name, $maxlength, &$select, $realnames = false, $nestLevel = 0, $opts = array())
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->render_folder_tree_select($arrFolders, $mbox_name, $maxlength, $select, $realnames, $nestLevel, $opts);    
}

function rcmail_build_folder_tree(&$arrFolders, $folder, $delm = '/', $path = '')
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->build_folder_tree($arrFolders, $folder, $delm, $path);
}

function rcmail_folder_classname($folder_id)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->folder_classname($folder_id);
}

function rcmail_localize_foldername($name)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->localize_foldername($name);
}

function rcmail_localize_folderpath($path)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->localize_folderpath($path);
}

function rcmail_quota_display($attrib)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->quota_display($attrib);
}

function rcmail_quota_content($attrib = null)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->quota_content($attrib);
}

function rcmail_display_server_error($fallback=null, $fallback_args=null, $suffix='')
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->display_server_error($fallback, $fallback_args, $suffix);
}

function rcmail_filetype2classname($mimetype, $filename)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::file2class($mimetype, $filename);
}

function rcube_html_editor($mode='')
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->html_editor($mode);
}

function rcmail_replace_emoticons($html)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->replace_emoticons($html);
}

function rcmail_deliver_message(&$message, $from, $mailto, &$smtp_error, &$body_file=null, $smtp_opts=null)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->deliver_message($message, $from, $mailto, $smtp_error, $body_file, $smtp_opts);
}

function rcmail_gen_message_id()
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->gen_message_id();
}

function rcmail_user_date()
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->user_date();
}

function rcmail_mem_check($need)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::mem_check($need);
}

function rcube_https_check($port=null, $use_https=true)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::https_check($port, $use_https);
}

function rcube_sess_unset($var_name=null)
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->session->remove($var_name);
}

function rcube_parse_host($name, $host='')
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::parse_host($name, $host);
}

function check_email($email, $dns_check=true)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::check_email($email, $dns_check);
}

function console()
{
    _deprecation_warning(__FUNCTION__);
    call_user_func_array(array('rcmail', 'console'), func_get_args());
}

function write_log($name, $line)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::write_log($name, $line);
}

function rcmail_log_login()
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->log_login();
}

function rcmail_remote_ip()
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::remote_ip();
}

function rcube_check_referer()
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::check_referer();
}

function rcube_timer()
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::timer();
}

function rcube_print_time($timer, $label='Timer', $dest='console')
{
    _deprecation_warning(__FUNCTION__);
    rcmail::print_timer($timer, $label, $dest);
}

function raise_error($arg=array(), $log=false, $terminate=false)
{
    _deprecation_warning(__FUNCTION__);
    rcmail::raise_error($arg, $log, $terminate);
}

function rcube_log_bug($arg_arr)
{
    _deprecation_warning(__FUNCTION__);
    rcmail::log_bug($arg_arr);
}

function rcube_upload_progress()
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->upload_progress();
}

function rcube_upload_init()
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->upload_init();
}

function rcube_autocomplete_init()
{
    _deprecation_warning(__FUNCTION__);
    rcmail::get_instance()->autocomplete_init();
}

function rcube_fontdefs($font = null)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::font_defs($font);
}

function send_nocacheing_headers()
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->output->nocacheing_headers();
}

function show_bytes($bytes)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->show_bytes($bytes);
}

function rc_wordwrap($string, $width=75, $break="\n", $cut=false, $charset=null)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_mime::wordwrap($string, $width, $break, $cut, $charset);
}

function rc_request_header($name)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::request_header($name);
}

function rcube_explode_quoted_string($delimiter, $string)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::explode_quoted_string($delimiter, $string);
}

function rc_mime_content_type($path, $name, $failover = 'application/octet-stream', $is_stream=false)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_mime::file_content_type($path, $name, $failover, $is_stream);
}

function rc_image_content_type($data)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_mime::image_content_type($data);
}

function rcube_strtotime($date)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::strtotime($date);
}

function rcube_idn_to_ascii($str)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::idn_to_ascii($str);
}

function rcube_idn_to_utf8($str)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::idn_to_utf8($str);
}

function send_future_expire_header($offset = 2600000)
{
    _deprecation_warning(__FUNCTION__);
    return rcmail::get_instance()->output->future_expire_header($offset);
}

function get_opt($aliases = array())
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::get_opt($aliases);
}

function prompt_silent($prompt = 'Password:')
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::prompt_silent($prompt);
}

function get_boolean($str)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_utils::get_boolean($str);
}

function enriched_to_html($data)
{
    _deprecation_warning(__FUNCTION__);
    return rcube_enriched::to_html($data);
}

function strip_quotes($str)
{
    _deprecation_warning(__FUNCTION__);
    return str_replace(array("'", '"'), '', $str);
}

function strip_newlines($str)
{
    _deprecation_warning(__FUNCTION__);
    return preg_replace('/[\r\n]/', '', $str);
}

function _deprecation_warning($func)
{
    static $warnings = array();

    if (!$warnings[$func]++)  // only log once
        rcmail::write_log('errors', "Warning: Call to deprecated function $func(); See bc.inc for replacement");
}

class rcube_html_page extends rcmail_html_page
{
}

class washtml extends rcube_washtml
{
}

class html2text extends rcube_html2text
{
}
