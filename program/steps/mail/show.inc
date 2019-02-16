<?php

/**
 +-----------------------------------------------------------------------+
 | program/steps/mail/show.inc                                           |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2013, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Display a mail message similar as a usual mail application does     |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

$PRINT_MODE = $RCMAIL->action == 'print';

// Read browser capabilities and store them in session
if ($caps = rcube_utils::get_input_value('_caps', rcube_utils::INPUT_GET)) {
    $browser_caps = array();
    foreach (explode(',', $caps) as $cap) {
        $cap = explode('=', $cap);
        $browser_caps[$cap[0]] = $cap[1];
    }
    $_SESSION['browser_caps'] = $browser_caps;
}

$msg_id    = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_GET);
$uid       = preg_replace('/\.[0-9.]+$/', '', $msg_id);
$mbox_name = $RCMAIL->storage->get_folder();

// similar code as in program/steps/mail/get.inc
if ($uid) {
    // set message format (need to be done before rcube_message construction)
    if (!empty($_GET['_format'])) {
        $prefer_html = $_GET['_format'] == 'html';
        $RCMAIL->config->set('prefer_html', $prefer_html);
        $_SESSION['msg_formats'][$mbox_name.':'.$uid] = $prefer_html;
    }
    else if (isset($_SESSION['msg_formats'][$mbox_name.':'.$uid])) {
        $RCMAIL->config->set('prefer_html', $_SESSION['msg_formats'][$mbox_name.':'.$uid]);
    }

    $MESSAGE = new rcube_message($msg_id, $mbox_name, intval($_GET['_safe']));

    // if message not found (wrong UID)...
    if (empty($MESSAGE->headers)) {
        rcmail_message_error($uid);
    }

    // show images?
    rcmail_check_safe($MESSAGE);

    // set message charset as default
    if (!empty($MESSAGE->headers->charset)) {
        $RCMAIL->storage->set_charset($MESSAGE->headers->charset);
    }

    if (!isset($_SESSION['writeable_abook'])) {
        $_SESSION['writeable_abook'] = $RCMAIL->get_address_sources(true) ? true : false;
    }

    $OUTPUT->set_pagetitle(abbreviate_string($MESSAGE->subject, 128, '...', true));

    // set environment
    $OUTPUT->set_env('uid', $msg_id);
    $OUTPUT->set_env('safemode', $MESSAGE->is_safe);
    $OUTPUT->set_env('message_context', $MESSAGE->context);
    $OUTPUT->set_env('message_flags', array_keys(array_change_key_case((array) $MESSAGE->headers->flags)));
    $OUTPUT->set_env('sender', $MESSAGE->sender['string']);
    $OUTPUT->set_env('mailbox', $mbox_name);
    $OUTPUT->set_env('username', $RCMAIL->get_user_name());
    $OUTPUT->set_env('permaurl', $RCMAIL->url(array('_action' => 'show', '_uid' => $msg_id, '_mbox' => $mbox_name)));
    $OUTPUT->set_env('has_writeable_addressbook', $_SESSION['writeable_abook']);
    $OUTPUT->set_env('delimiter', $RCMAIL->storage->get_hierarchy_delimiter());
    $OUTPUT->set_env('mimetypes', rcmail_supported_mimetypes());

    if ($MESSAGE->headers->get('list-post', false)) {
        $OUTPUT->set_env('list_post', true);
    }

    // set configuration
    $RCMAIL->set_env_config(array('delete_junk', 'flag_for_deletion', 'read_when_deleted',
        'skip_deleted', 'display_next', 'forward_attachment'));

    // set special folders
    foreach (array('drafts', 'trash', 'junk') as $mbox) {
        if ($folder = $RCMAIL->config->get($mbox . '_mbox')) {
            $OUTPUT->set_env($mbox . '_mailbox', $folder);
        }
    }

    if ($MESSAGE->has_html_part()) {
        $prefer_html = $RCMAIL->config->get('prefer_html');
        $OUTPUT->set_env('optional_format', $prefer_html ? 'text' : 'html');
    }

    if (!$OUTPUT->ajax_call) {
        $OUTPUT->add_label('checkingmail', 'deletemessage', 'movemessagetotrash',
            'movingmessage', 'deletingmessage', 'markingmessage', 'replyall', 'replylist',
            'bounce', 'bouncemsg', 'sendingmessage');
    }

    // check for unset disposition notification
    if ($MESSAGE->headers->mdn_to
        && $MESSAGE->context === null
        && empty($MESSAGE->headers->flags['MDNSENT'])
        && empty($MESSAGE->headers->flags['SEEN'])
        && ($RCMAIL->storage->check_permflag('MDNSENT') || $RCMAIL->storage->check_permflag('*'))
        && $mbox_name != $RCMAIL->config->get('drafts_mbox')
        && $mbox_name != $RCMAIL->config->get('sent_mbox')
    ) {
        $mdn_cfg = intval($RCMAIL->config->get('mdn_requests'));

        if ($mdn_cfg == 1 || (($mdn_cfg == 3 || $mdn_cfg ==  4) && rcmail_contact_exists($MESSAGE->sender['mailto']))) {
            // Send MDN
            if (rcmail_send_mdn($MESSAGE, $smtp_error))
                $OUTPUT->show_message('receiptsent', 'confirmation');
            else if ($smtp_error)
                $OUTPUT->show_message($smtp_error['label'], 'error', $smtp_error['vars']);
            else
                $OUTPUT->show_message('errorsendingreceipt', 'error');
        }
        else if ($mdn_cfg != 2 && $mdn_cfg != 4) {
            // Ask user
            $OUTPUT->add_label('mdnrequest');
            $OUTPUT->set_env('mdn_request', true);
        }
    }

    if (empty($MESSAGE->headers->flags['SEEN']) && $MESSAGE->context === null) {
        $v = intval($RCMAIL->config->get('mail_read_time'));
        if ($v > 0) {
            $OUTPUT->set_env('mail_read_time', $v);
        }
        else if ($v == 0) {
            $RCMAIL->output->command('set_unread_message', $MESSAGE->uid, $mbox_name);
            $RCMAIL->plugins->exec_hook('message_read', array(
                    'uid'     => $MESSAGE->uid,
                    'mailbox' => $mbox_name,
                    'message' => $MESSAGE,
            ));

            $set_seen_flag = true;
        }
    }
}


$OUTPUT->add_handlers(array(
    'mailboxname'        => 'rcmail_mailbox_name_display',
    'messageattachments' => 'rcmail_message_attachments',
    'messageobjects'     => 'rcmail_message_objects',
    'messagesummary'     => 'rcmail_message_summary',
    'messageheaders'     => 'rcmail_message_headers',
    'messagefullheaders' => 'rcmail_message_full_headers',
    'messagebody'        => 'rcmail_message_body',
    'contactphoto'       => 'rcmail_message_contactphoto',
));


if ($RCMAIL->action == 'print' && $OUTPUT->template_exists('messageprint'))
    $OUTPUT->send('messageprint', false);
else if ($RCMAIL->action == 'preview' && $OUTPUT->template_exists('messagepreview'))
    $OUTPUT->send('messagepreview', false);
else
    $OUTPUT->send('message', false);


// mark message as read
if (!empty($set_seen_flag)) {
    if ($RCMAIL->storage->set_flag($MESSAGE->uid, 'SEEN', $mbox_name)) {
        if ($count = rcmail_get_unseen_count($mbox_name)) {
            rcmail_set_unseen_count($mbox_name, $count - 1);
        }
    }
}

exit;


function rcmail_message_attachments($attrib)
{
    global $PRINT_MODE, $MESSAGE, $RCMAIL;

    $out = $ol = '';
    $attachments = array();

    if (count($MESSAGE->attachments)) {
        foreach ($MESSAGE->attachments as $attach_prop) {
            $filename = rcmail_attachment_name($attach_prop, true);
            $filesize = $RCMAIL->message_part_size($attach_prop);
            $mimetype = rcmail_fix_mimetype($attach_prop->mimetype);
            $class    = rcube_utils::file2class($mimetype, $filename);
            $id       = 'attach' . $attach_prop->mime_id;

            if ($attrib['maxlength'] && mb_strlen($filename) > $attrib['maxlength']) {
                $title    = $filename;
                $filename = abbreviate_string($filename, $attrib['maxlength']);
            }
            else {
                $title = '';
            }

            $item = html::span('attachment-name', rcube::Q($filename))
                . html::span('attachment-size', '(' . rcube::Q($filesize) . ')');

            if (!$PRINT_MODE) {
                $item = html::a(array(
                    'href'        => $MESSAGE->get_part_url($attach_prop->mime_id, false),
                    'onclick'     => sprintf('return %s.command(\'load-attachment\',\'%s\',this)',
                        rcmail_output::JS_OBJECT_NAME, $attach_prop->mime_id),
                    'onmouseover' => $title ? '' : 'rcube_webmail.long_subject_title_ex(this, 0)',
                    'title'       => $title,
                    'class'       => 'filename',
                    ), $item);

                $attachments[$attach_prop->mime_id] = $mimetype;
            }

            $ol .= html::tag('li', array('class' => $class, 'id' => $id), $item);
        }

        $out = html::tag('ul', $attrib, $ol, html::$common_attrib);

        $RCMAIL->output->set_env('attachments', $attachments);
        $RCMAIL->output->add_gui_object('attachments', $attrib['id']);
    }

    return $out;
}

function rcmail_remote_objects_msg()
{
    global $MESSAGE, $RCMAIL;

    $attrib['id']    = 'remote-objects-message';
    $attrib['class'] = 'notice';
    $attrib['style'] = 'display: none';

    $msg = html::span(null, rcube::Q($RCMAIL->gettext('blockedresources'))) . '&nbsp;'
        . html::a(array(
            'href'    => "#loadremote",
            'onclick' => rcmail_output::JS_OBJECT_NAME.".command('load-remote')"
        ),
        rcube::Q($RCMAIL->gettext('allow')));

    // add link to save sender in addressbook and reload message
    if ($MESSAGE->sender['mailto'] && $RCMAIL->config->get('show_images') == 1) {
        $msg .= ' ' . html::a(array(
                'href'    => "#loadremotealways",
                'onclick' => rcmail_output::JS_OBJECT_NAME.".command('load-remote', true)",
                'style'   => "white-space:nowrap"
            ),
            rcube::Q($RCMAIL->gettext(array('name' => 'alwaysallow', 'vars' => array('sender' => $MESSAGE->sender['mailto'])))));
    }

    $RCMAIL->output->add_gui_object('remoteobjectsmsg', $attrib['id']);

    return html::div($attrib, $msg);
}

function rcmail_message_buttons()
{
    global $RCMAIL, $MESSAGE;

    $delim = $RCMAIL->storage->get_hierarchy_delimiter();
    $dbox  = $RCMAIL->config->get('drafts_mbox');

    // the message is not a draft
    if ($MESSAGE->context
        || ($MESSAGE->folder != $dbox && strpos($MESSAGE->folder, $dbox.$delim) !== 0)
    ) {
        return '';
    }

    $attrib['id']    = 'message-buttons';
    $attrib['class'] = 'information notice';

    $msg = html::span(null, rcube::Q($RCMAIL->gettext('isdraft'))) . '&nbsp;'
        . html::a(array(
            'href'    => "#edit",
            'onclick' => rcmail_output::JS_OBJECT_NAME.".command('edit')"
        ),
        rcube::Q($RCMAIL->gettext('edit')));

    return html::div($attrib, $msg);
}

function rcmail_message_objects($attrib)
{
    global $RCMAIL, $MESSAGE;

    if (!$attrib['id'])
        $attrib['id'] = 'message-objects';

    $content = array(
        rcmail_message_buttons(),
        rcmail_remote_objects_msg(),
    );

    $plugin = $RCMAIL->plugins->exec_hook('message_objects',
        array('content' => $content, 'message' => $MESSAGE));

    $content = implode("\n", $plugin['content']);

    return html::div($attrib, $content);
}

function rcmail_contact_exists($email)
{
    global $RCMAIL;

    if ($email) {
        // @TODO: search in all address books?
        $CONTACTS = $RCMAIL->get_address_book(-1, true);

        if (is_object($CONTACTS)) {
            $existing = $CONTACTS->search('email', $email, 1, false);
            if ($existing->count) {
                return true;
            }
        }
    }

    return false;
}

function rcmail_message_contactphoto($attrib)
{
    global $RCMAIL, $MESSAGE;

    $placeholder = $attrib['placeholder'] ? $RCMAIL->output->abs_url($attrib['placeholder'], true) : null;
    $placeholder = $RCMAIL->output->asset_url($placeholder ?: 'program/resources/blank.gif');

    if ($MESSAGE->sender) {
        $photo_img = $RCMAIL->url(array(
            '_task'   => 'addressbook',
            '_action' => 'photo',
            '_email'  => $MESSAGE->sender['mailto'],
            '_error'  => strpos($placeholder, 'blank.gif') === false ? 1 : null,
        ));

        $attrib['onerror'] = "this.src = '$placeholder'; this.onerror = null";
    }
    else {
        $photo_img = $placeholder;
    }

    return html::img(array('src' => $photo_img, 'alt' => $RCMAIL->gettext('contactphoto')) + $attrib);
}

/**
 * Returns table with message headers
 */
function rcmail_message_headers($attrib, $headers=null)
{
    global $MESSAGE, $PRINT_MODE, $RCMAIL;
    static $sa_attrib;

    // keep header table attrib
    if (is_array($attrib) && !$sa_attrib && !$attrib['valueof']) {
        $sa_attrib = $attrib;
    }
    else if (!is_array($attrib) && is_array($sa_attrib)) {
        $attrib = $sa_attrib;
    }

    if (!isset($MESSAGE)) {
        return false;
    }

    // get associative array of headers object
    if (!$headers) {
        $headers_obj = $MESSAGE->headers;
        $headers     = get_object_vars($MESSAGE->headers);
    }
    else if (is_object($headers)) {
        $headers_obj = $headers;
        $headers     = get_object_vars($headers_obj);
    }
    else {
        $headers_obj = rcube_message_header::from_array($headers);
    }

    // show these headers
    $standard_headers = array('subject', 'from', 'sender', 'to', 'cc', 'bcc', 'replyto',
        'mail-reply-to', 'mail-followup-to', 'date', 'priority');
    $exclude_headers = $attrib['exclude'] ? explode(',', $attrib['exclude']) : array();
    $output_headers  = array();

    foreach ($standard_headers as $hkey) {
        if ($headers[$hkey]) {
            $value = $headers[$hkey];
        }
        else if ($headers['others'][$hkey]) {
            $value = $headers['others'][$hkey];
        }
        else if (!$attrib['valueof']) {
            continue;
        }

        if (in_array($hkey, $exclude_headers)) {
            continue;
        }

        $ishtml       = false;
        $header_title = $RCMAIL->gettext(preg_replace('/(^mail-|-)/', '', $hkey));

        if ($hkey == 'date') {
            $header_value = $RCMAIL->format_date($value,
                $PRINT_MODE ? $RCMAIL->config->get('date_long', 'x') : null);
        }
        else if ($hkey == 'priority') {
            if ($value) {
                $header_value = html::span('prio' . $value, rcube::Q(rcmail_localized_priority($value)));
                $ishtml       = true;
            }
            else {
                continue;
            }
        }
        else if ($hkey == 'replyto') {
            if ($headers['replyto'] != $headers['from']) {
                $header_value = rcmail_address_string($value, $attrib['max'], true,
                    $attrib['addicon'], $headers['charset'], $header_title);
                $ishtml = true;
            }
            else {
                continue;
            }
        }
        else if ($hkey == 'mail-reply-to') {
            if ($headers['mail-replyto'] != $headers['replyto']
                && $headers['replyto'] != $headers['from']
            ) {
                $header_value = rcmail_address_string($value, $attrib['max'], true,
                    $attrib['addicon'], $headers['charset'], $header_title);
                $ishtml = true;
            }
            else {
                continue;
            }
        }
        else if ($hkey == 'sender') {
            if ($headers['sender'] != $headers['from']) {
                $header_value = rcmail_address_string($value, $attrib['max'], true,
                    $attrib['addicon'], $headers['charset'], $header_title);
                $ishtml = true;
            }
            else {
                continue;
            }
        }
        else if ($hkey == 'mail-followup-to') {
            $header_value = rcmail_address_string($value, $attrib['max'], true,
                $attrib['addicon'], $headers['charset'], $header_title);
            $ishtml = true;
        }
        else if (in_array($hkey, array('from', 'to', 'cc', 'bcc'))) {
            $header_value = rcmail_address_string($value, $attrib['max'], true,
                $attrib['addicon'], $headers['charset'], $header_title);
            $ishtml = true;
        }
        else if ($hkey == 'subject' && empty($value)) {
            $header_value = $RCMAIL->gettext('nosubject');
        }
        else {
            $value        = is_array($value) ? implode(' ', $value) : $value;
            $header_value = trim(rcube_mime::decode_header($value, $headers['charset']));
        }

        $output_headers[$hkey] = array(
            'title' => $header_title,
            'value' => $header_value,
            'raw'   => $value,
            'html'  => $ishtml,
        );
    }

    $plugin = $RCMAIL->plugins->exec_hook('message_headers_output', array(
        'output'  => $output_headers,
        'headers' => $headers_obj,
        'exclude' => $exclude_headers, // readonly
        'folder'  => $MESSAGE->folder, // readonly
        'uid'     => $MESSAGE->uid,    // readonly
    ));

    // single header value is requested
    if (!empty($attrib['valueof'])) {
        $row = $plugin['output'][$attrib['valueof']];
        return $row['html'] ? $row['value'] : rcube::Q($row['value']);
    }

    // compose html table
    $table = new html_table(array('cols' => 2));

    foreach ($plugin['output'] as $hkey => $row) {
        $val = $row['html'] ? $row['value'] : rcube::Q($row['value']);

        $table->add(array('class' => 'header-title'), rcube::Q($row['title']));
        $table->add(array('class' => 'header '.$hkey), $val);
    }

    return $table->show($attrib);
}

/**
 * Returns element with "From|To <sender|recipient> on <date>"
 */
function rcmail_message_summary($attrib)
{
    global $MESSAGE, $RCMAIL;

    if (!isset($MESSAGE) || empty($MESSAGE->headers)) {
        return;
    }

    $header = $MESSAGE->context ? 'from' : rcmail_message_list_smart_column_name();
    $label  = 'shortheader' . $header;
    $date   = $RCMAIL->format_date($MESSAGE->headers->date, $RCMAIL->config->get('date_long', 'x'));
    $user   = $MESSAGE->headers->$header;

    if (!$user && $header == 'to') {
        $user = $MESSAGE->headers->cc;
    }
    if (!$user && $header == 'to') {
        $user = $MESSAGE->headers->bcc;
    }

    $vars[$header] = rcmail_address_string($user, 1, true, $attrib['addicon'], $MESSAGE->headers->charset);
    $vars['date']  = html::span('text-nowrap', $date);

    if (empty($user)) {
        $label = 'shortheaderdate';
    }

    $out = html::span(null, $RCMAIL->gettext(array('name' => $label, 'vars' => $vars)));

    return html::div($attrib, $out);
}

/**
 * Convert Priority header value into a localized string
 */
function rcmail_localized_priority($value)
{
    global $RCMAIL;

    $labels_map = array(
        '1' => 'highest',
        '2' => 'high',
        '3' => 'normal',
        '4' => 'low',
        '5' => 'lowest',
    );

    if ($value && $labels_map[$value]) {
        return $RCMAIL->gettext($labels_map[$value]);
    }

    return '';
}

/**
 * Returns block to show full message headers
 */
function rcmail_message_full_headers($attrib)
{
    global $OUTPUT, $RCMAIL;

    $html = html::div(array('id' => "all-headers", 'class' => "all", 'style' => 'display:none'),
        html::div(array('id' => 'headers-source'), ''));
    $html .= html::div(array(
            'class'   => "more-headers show-headers",
            'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".command('show-headers','',this)",
            'title'   => $RCMAIL->gettext('togglefullheaders')
        ), '');

    $OUTPUT->add_gui_object('all_headers_row', 'all-headers');
    $OUTPUT->add_gui_object('all_headers_box', 'headers-source');

    return html::div($attrib, $html);
}

/**
 * Handler for the 'messagebody' GUI object
 *
 * @param array Named parameters
 * @return string HTML content showing the message body
 */
function rcmail_message_body($attrib)
{
    global $OUTPUT, $MESSAGE, $RCMAIL, $REMOTE_OBJECTS;

    if (!is_array($MESSAGE->parts) && empty($MESSAGE->body)) {
        return '';
    }

    if (!$attrib['id'])
        $attrib['id'] = 'rcmailMsgBody';

    $safe_mode = $MESSAGE->is_safe || intval($_GET['_safe']);
    $out       = '';
    $part_no   = 0;

    $header_attrib = array();
    foreach ($attrib as $attr => $value) {
        if (preg_match('/^headertable([a-z]+)$/i', $attr, $regs)) {
            $header_attrib[$regs[1]] = $value;
        }
    }

    if (!empty($MESSAGE->parts)) {
        foreach ($MESSAGE->parts as $part) {
            if ($part->type == 'headers') {
                $out .= html::div('message-partheaders', rcmail_message_headers(count($header_attrib) ? $header_attrib : null, $part->headers));
            }
            else if ($part->type == 'content') {
                // unsupported (e.g. encrypted)
                if ($part->realtype) {
                    if ($part->realtype == 'multipart/encrypted' || $part->realtype == 'application/pkcs7-mime') {
                        if (!empty($_SESSION['browser_caps']['pgpmime']) && ($pgp_mime_part = $MESSAGE->get_multipart_encrypted_part())) {
                            $out .= html::span('part-notice', $RCMAIL->gettext('externalmessagedecryption'));
                            $OUTPUT->set_env('pgp_mime_part', $pgp_mime_part->mime_id);
                            $OUTPUT->set_env('pgp_mime_container', '#' . $attrib['id']);
                            $OUTPUT->add_label('loadingdata');
                        }

                        if (!$MESSAGE->encrypted_part) {
                            $out .= html::span('part-notice', $RCMAIL->gettext('encryptedmessage'));
                        }
                    }
                    continue;
                }
                else if (!$part->size) {
                    continue;
                }
                // Check if we have enough memory to handle the message in it
                // #1487424: we need up to 10x more memory than the body
                else if (!rcube_utils::mem_check($part->size * 10)) {
                    $out .= rcmail_part_too_big_message($MESSAGE, $part->mime_id);
                    continue;
                }

                // fetch part body
                $body = $MESSAGE->get_part_body($part->mime_id, true);

                // message is cached but not exists (#1485443), or other error
                if ($body === false) {
                    rcmail_message_error($MESSAGE->uid);
                }

                $plugin = $RCMAIL->plugins->exec_hook('message_body_prefix',
                    array('part' => $part, 'prefix' => ''));

                // Set attributes of the part container
                $container_class  = $part->ctype_secondary == 'html' ? 'message-htmlpart' : 'message-part';
                $container_id     = $container_class . (++$part_no);
                $container_attrib = array('class' => $container_class, 'id' => $container_id);

                $body_args = array(
                    'safe'         => $safe_mode,
                    'plain'        => !$RCMAIL->config->get('prefer_html'),
                    'css_prefix'   => 'v' . $part_no,
                    'body_class'   => 'rcmBody',
                    'container_id'     => $container_id,
                    'container_attrib' => $container_attrib,
                );

                // Parse the part content for display
                $body = rcmail_print_body($body, $part, $body_args);

                // check if the message body is PGP encrypted
                if (strpos($body, '-----BEGIN PGP MESSAGE-----') !== false) {
                    $OUTPUT->set_env('is_pgp_content', '#' . $container_id);
                }

                if ($part->ctype_secondary == 'html') {
                    $body = rcmail_html4inline($body, $body_args);
                }

                $out .= html::div($container_attrib, $plugin['prefix'] . $body);
            }
        }
    }
    else {
        // Check if we have enough memory to handle the message in it
        // #1487424: we need up to 10x more memory than the body
        if (!rcube_utils::mem_check(strlen($MESSAGE->body) * 10)) {
            $out .= rcmail_part_too_big_message($MESSAGE, 0);
        }
        else {
            $plugin = $RCMAIL->plugins->exec_hook('message_body_prefix',
                array('part' => $MESSAGE, 'prefix' => ''));

            $out .= html::div('message-part',
                $plugin['prefix'] . rcmail_plain_body($MESSAGE->body));
        }
    }

    // list images after mail body
    if ($RCMAIL->config->get('inline_images', true) && !empty($MESSAGE->attachments)) {
        $thumbnail_size   = $RCMAIL->config->get('image_thumbnail_size', 240);
        $client_mimetypes = (array)$RCMAIL->config->get('client_mimetypes');

        $show_label     = rcube::Q($RCMAIL->gettext('showattachment'));
        $download_label = rcube::Q($RCMAIL->gettext('download'));

        foreach ($MESSAGE->attachments as $attach_prop) {
            // skip inline images
            if ($attach_prop->content_id && $attach_prop->disposition == 'inline') {
                continue;
            }

            // Content-Type: image/*...
            if ($mimetype = rcmail_part_image_type($attach_prop)) {
                // display thumbnails
                if ($thumbnail_size) {
                    $supported = in_array($mimetype, $client_mimetypes);
                    $show_link_attr = array(
                        'href'    => $MESSAGE->get_part_url($attach_prop->mime_id, false),
                        'onclick' => sprintf(
                            'return %s.command(\'load-attachment\',\'%s\',this)',
                            rcmail_output::JS_OBJECT_NAME,
                            $attach_prop->mime_id
                        )
                    );
                    $download_link_attr = array(
                        'href'  => $show_link_attr['href'] . '&_download=1',
                    );
                    $show_link     = html::a($show_link_attr + array('class' => 'open'), $show_label);
                    $download_link = html::a($download_link_attr + array('class' => 'download'), $download_label);

                    $out .= html::p(array('class' => 'image-attachment', 'style' => $supported ? '' : 'display:none'),
                        html::a($show_link_attr + array('class' => 'image-link', 'style' => sprintf('width:%dpx', $thumbnail_size)),
                            html::img(array(
                                'class' => 'image-thumbnail',
                                'src'   => $MESSAGE->get_part_url($attach_prop->mime_id, 'image') . '&_thumb=1',
                                'title' => $attach_prop->filename,
                                'alt'   => $attach_prop->filename,
                                'style' => sprintf('max-width:%dpx; max-height:%dpx', $thumbnail_size, $thumbnail_size),
                                'onload' => $supported ? '' : '$(this).parents(\'p.image-attachment\').show()',
                            ))
                        ) .
                        html::span('image-filename', rcube::Q($attach_prop->filename)) .
                        html::span('image-filesize', rcube::Q($RCMAIL->message_part_size($attach_prop))) .
                        html::span('attachment-links', ($supported ? $show_link . '&nbsp;' : '') . $download_link) .
                        html::br(array('style' => 'clear:both'))
                    );
                }
                else {
                    $out .= html::tag('fieldset', 'image-attachment',
                        html::tag('legend', 'image-filename', rcube::Q($attach_prop->filename)) .
                        html::p(array('align' => 'center'),
                            html::img(array(
                                'src'   => $MESSAGE->get_part_url($attach_prop->mime_id, 'image'),
                                'title' => $attach_prop->filename,
                                'alt'   => $attach_prop->filename,
                        )))
                    );
                }
            }
        }
    }

    // tell client that there are blocked remote objects
    if ($REMOTE_OBJECTS && !$safe_mode) {
        $OUTPUT->set_env('blockedobjects', true);
    }

    return html::div($attrib, $out);
}

/**
 * Returns a HTML notice element for too big message parts
 */
function rcmail_part_too_big_message($MESSAGE, $part_id)
{
    global $RCMAIL;

    $token = $RCMAIL->get_request_token();
    $url   = $RCMAIL->url(array(
            'task'     => 'mail',
            'action'   => 'get',
            'download' => 1,
            'uid'      => $MESSAGE->uid,
            'part'     => $part_id,
            'mbox'     => $MESSAGE->folder,
            'token'    => $token,
    ));

    return html::span('part-notice', $RCMAIL->gettext('messagetoobig') . '&nbsp;' . html::a($url, $RCMAIL->gettext('download')));
}
