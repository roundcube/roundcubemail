<?php

/*
 +-----------------------------------------------------------------------+
 | program/include/main.inc                                              |
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

 $Id$

*/

/**
 * Roundcube Webmail deprecated functions
 *
 * @package Core
 * @author Thomas Bruederli <roundcube@gmail.com>
 */

// constants for input reading
define('RCUBE_INPUT_GET',  rcube_ui::INPUT_GET);
define('RCUBE_INPUT_POST', rcube_ui::INPUT_POST);
define('RCUBE_INPUT_GPC',  rcube_ui::INPUT_GPC);


function get_table_name($table)
{
    return rcmail::get_instance()->db->table_name($table);
}

function get_sequence_name($sequence)
{
    return rcmail::get_instance()->db->sequence_name($sequence);
}

function rcube_label($p, $domain=null)
{
    return rcmail::get_instance()->gettext($p, $domain);
}

function rcube_label_exists($name, $domain=null, &$ref_domain = null)
{
    return rcmail::get_instance()->text_exists($name, $domain, $ref_domain);
}

function rcmail_overwrite_action($action)
{
    rcmail::get_instance()->overwrite_action($action);
}

function rcmail_url($action, $p=array(), $task=null)
{
    return rcube_ui::url($action, $p, $task);
}

function rcmail_temp_gc()
{
  $rcmail = rcmail::get_instance()->temp_gc();
}

function rcube_charset_convert($str, $from, $to=NULL)
{
    return rcube_charset::convert($str, $from, $to);
}

function rc_detect_encoding($string, $failover='')
{
    return rcube_charset::detect($string, $failover);
}

function rc_utf8_clean($input)
{
    return rcube_charset::clean($input);
}

function json_serialize($input)
{
    return rcube_output::json_serialize($input);
}

function rep_specialchars_output($str, $enctype='', $mode='', $newlines=TRUE)
{
    return rcube_ui::rep_specialchars_output($str, $enctype, $mode, $newlines);
}

function Q($str, $mode='strict', $newlines=TRUE)
{
    return rcube_ui::Q($str, $mode, $newlines);
}

function JQ($str)
{
    return rcube_ui::JQ($str);
}

function get_input_value($fname, $source, $allow_html=FALSE, $charset=NULL)
{
    return rcube_ui::get_input_value($fname, $source, $allow_html, $charset);
}

function parse_input_value($value, $allow_html=FALSE, $charset=NULL)
{
    return rcube_ui::parse_input_value($value, $allow_html, $charset);
}

function request2param($mode = RCUBE_INPUT_GPC, $ignore = 'task|action')
{
    return rcube_ui::request2param($mode, $ignore);
}

function html_identifier($str, $encode=false)
{
    return rcube_ui::html_identifier($str, $encode);
}

function rcube_table_output($attrib, $table_data, $a_show_cols, $id_col)
{
    return rcube_ui::table_output($attrib, $table_data, $a_show_cols, $id_col);
}

function rcmail_get_edit_field($col, $value, $attrib, $type='text')
{
  return rcube_ui::get_edit_field($col, $value, $attrib, $type);
}

function rcmail_mod_css_styles($source, $container_id, $allow_remote=false)
{
    return rcube_ui::mod_css_styles($source, $container_id, $allow_remote);
}

function rcmail_xss_entity_decode($content)
{
    return rcube_ui::xss_entity_decode($content);
}

function create_attrib_string($attrib, $allowed_attribs=array('id', 'class', 'style'))
{
    return html::attrib_string($attrib, $allowed_attribs);
}

function parse_attrib_string($str)
{
    return html::parse_attrib_string($str);
}

function format_date($date, $format=NULL, $convert=true)
{
    return rcube_ui::format_date($date, $format, $convert);
}

function rcmail_mailbox_list($attrib)
{
    return rcube_ui::folder_list($attrib);
}

function rcmail_mailbox_select($attrib = array())
{
    return rcube_ui::folder_selector($attrib);
}

function rcmail_localize_foldername($name)
{
    return rcube_ui::localize_foldername($name);
}

function rcmail_localize_folderpath($path)
{
    return rcube_ui::localize_folderpath($path);
}

function rcmail_quota_display($attrib)
{
    return rcube_ui::quota_display($attrib);
}

function rcmail_quota_content($attrib = null)
{
    return rcube_ui::quota_content($attrib);
}

function rcmail_display_server_error($fallback=null, $fallback_args=null)
{
    rcube_ui::display_server_error($fallback, $fallback_args);
}

function rcmail_filetype2classname($mimetype, $filename)
{
    return rcube_ui::file2class($mimetype, $filename);
}

function rcube_html_editor($mode='')
{
    rcube_ui::html_editor($mode);
}

function rcmail_replace_emoticons($html)
{
    return rcube_ui::replace_emoticons($html);
}

function rcmail_deliver_message(&$message, $from, $mailto, &$smtp_error, &$body_file=null, $smtp_opts=null)
{
    return rcmail::get_instance()->deliver_message($message, $from, $mailto, $smtp_error, $body_file, $smtp_opts);
}

function rcmail_gen_message_id()
{
    return rcmail::get_instance()->gen_message_id();
}

function rcmail_user_date()
{
    return rcmail::get_instance()->user_date();
}

function rcmail_mem_check($need)
{
    return rcube_ui::mem_check($need);
}

function rcube_https_check($port=null, $use_https=true)
{
    return rcube_ui::https_check($port, $use_https);
}

function rcube_sess_unset($var_name=null)
{
    rcmail::get_instance()->session->remove($var_name);
}

function rcube_parse_host($name, $host='')
{
    return rcmail::parse_host($name, $host);
}

function check_email($email, $dns_check=true)
{
    return rcmail::get_instance()->check_email($email, $dns_check);
}

function console()
{
    call_user_func_array(array('rcmail', 'console'), func_get_args());
}

function write_log($name, $line)
{
    return rcmail::write_log($name, $line);
}

function rcmail_log_login()
{
    return rcmail::get_instance()->log_login();
}

function rcmail_remote_ip()
{
    return rcmail::remote_ip();
}

function rcube_check_referer()
{
    return rcmail::check_referer();
}

function rcube_timer()
{
    return rcmail::timer();
}

function rcube_print_time($timer, $label='Timer', $dest='console')
{
    rcmail::print_timer($timer, $label, $dest);
}

function raise_error($arg=array(), $log=false, $terminate=false)
{
    rcmail::raise_error($arg, $log, $terminate);
}

function rcube_log_bug($arg_arr)
{
    rcmail::log_bug($arg_arr);
}

function rcube_upload_progress()
{
    rcube_ui::upload_progress();
}

function rcube_upload_init()
{
    return rcube_ui::upload_init();
}

function rcube_autocomplete_init()
{
    rcube_ui::autocomplete_init();
}

function rcube_fontdefs($font = null)
{
    return rcube_ui::font_defs($font);
}

function send_nocacheing_headers()
{
    return rcmail::get_instance()->output->nocacheing_headers();
}

function show_bytes($bytes)
{
    return rcube_ui::show_bytes($bytes);
}

function rc_wordwrap($string, $width=75, $break="\n", $cut=false)
{
    return rcube_mime::wordwrap($string, $width, $break, $cut);
}

function rc_request_header($name)
{
    return rcube_request_header($name);
}

function rc_mime_content_type($path, $name, $failover = 'application/octet-stream', $is_stream=false)
{
    return rcube_mime::file_content_type($path, $name, $failover, $is_stream);
}

function rc_image_content_type($data)
{
    return rcube_mime::image_content_type($data);
}
