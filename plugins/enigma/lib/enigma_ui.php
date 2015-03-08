<?php
/*
 +-------------------------------------------------------------------------+
 | User Interface for the Enigma Plugin                                    |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_ui
{
    private $rc;
    private $enigma;
    private $home;
    private $css_loaded;
    private $js_loaded;
    private $data;
    private $keys_parts  = array();
    private $keys_bodies = array();


    function __construct($enigma_plugin, $home='')
    {
        $this->enigma = $enigma_plugin;
        $this->rc     = $enigma_plugin->rc;
        $this->home   = $home; // we cannot use $enigma_plugin->home here
    }

    /**
     * UI initialization and requests handlers.
     *
     * @param string Preferences section
     */
    function init($section='')
    {
        $this->add_js();

        $action = rcube_utils::get_input_value('_a', rcube_utils::INPUT_GPC);

        if ($this->rc->action == 'plugin.enigmakeys') {
            switch ($action) {
                case 'delete':
                    $this->key_delete();
                    break;
/*
                case 'edit':
                    $this->key_edit();
                    break;
*/
                case 'import':
                    $this->key_import();
                    break;

                case 'search':
                case 'list':
                    $this->key_list();
                    break;

                case 'info':
                    $this->key_info();
                    break;
            }

            $this->rc->output->add_handlers(array(
                    'keyslist'     => array($this, 'tpl_keys_list'),
                    'keyframe'     => array($this, 'tpl_key_frame'),
                    'countdisplay' => array($this, 'tpl_keys_rowcount'),
                    'searchform'   => array($this->rc->output, 'search_form'),
            ));

            $this->rc->output->set_pagetitle($this->enigma->gettext('enigmakeys'));
            $this->rc->output->send('enigma.keys');
        }
/*
        // Preferences UI
        else if ($this->rc->action == 'plugin.enigmacerts') {
            $this->rc->output->add_handlers(array(
                    'keyslist'     => array($this, 'tpl_certs_list'),
                    'keyframe'     => array($this, 'tpl_cert_frame'),
                    'countdisplay' => array($this, 'tpl_certs_rowcount'),
                    'searchform'   => array($this->rc->output, 'search_form'),
            ));

            $this->rc->output->set_pagetitle($this->enigma->gettext('enigmacerts'));
            $this->rc->output->send('enigma.certs'); 
        }
*/
        // Message composing UI
        else if ($this->rc->action == 'compose') {
            $this->compose_ui();
        }
    }

    /**
     * Adds CSS style file to the page header.
     */
    function add_css()
    {
        if ($this->css_loaded)
            return;

        $skin_path = $this->enigma->local_skin_path();
        if (is_file($this->home . "/$skin_path/enigma.css")) {
            $this->enigma->include_stylesheet("$skin_path/enigma.css");
        }

        $this->css_loaded = true;
    }

    /**
     * Adds javascript file to the page header.
     */
    function add_js()
    {
        if ($this->js_loaded) {
            return;
        }

        $this->enigma->include_script('enigma.js');

        $this->js_loaded = true;
    }

    /**
     * Initializes key password prompt
     *
     * @param enigma_error Error object with key info
     */
    function password_prompt($status)
    {
        $data = $status->getData('missing');

        if (empty($data)) {
            $data = $status->getData('bad');
        }

        $data = array('keyid' => key($data), 'user' => $data[key($data)]);

        $this->rc->output->set_env('enigma_password_request', $data);

        // add some labels to client
        $this->rc->output->add_label('enigma.enterkeypasstitle', 'enigma.enterkeypass',
            'save', 'cancel');

        $this->add_css();
        $this->add_js();
    }

    /**
     * Template object for key info/edit frame.
     *
     * @param array Object attributes
     *
     * @return string HTML output
     */
    function tpl_key_frame($attrib)
    {
        if (!$attrib['id']) {
            $attrib['id'] = 'rcmkeysframe';
        }

        $attrib['name'] = $attrib['id'];

        $this->rc->output->set_env('contentframe', $attrib['name']);
        $this->rc->output->set_env('blankpage', $attrib['src'] ? 
            $this->rc->output->abs_url($attrib['src']) : 'program/resources/blank.gif');

        return $this->rc->output->frame($attrib);
    }

    /**
     * Template object for list of keys.
     *
     * @param array Object attributes
     *
     * @return string HTML content
     */
    function tpl_keys_list($attrib)
    {
        // add id to message list table if not specified
        if (!strlen($attrib['id'])) {
            $attrib['id'] = 'rcmenigmakeyslist';
        }

        // define list of cols to be displayed
        $a_show_cols = array('name');

        // create XHTML table
        $out = $this->rc->table_output($attrib, array(), $a_show_cols, 'id');

        // set client env
        $this->rc->output->add_gui_object('keyslist', $attrib['id']);
        $this->rc->output->include_script('list.js');

        // add some labels to client
        $this->rc->output->add_label('enigma.keyremoveconfirm', 'enigma.keyremoving');

        return $out;
    }

    /**
     * Key listing (and searching) request handler
     */
    private function key_list()
    {
        $this->enigma->load_engine();

        $pagesize = $this->rc->config->get('pagesize', 100);
        $page     = max(intval(rcube_utils::get_input_value('_p', rcube_utils::INPUT_GPC)), 1);
        $search   = rcube_utils::get_input_value('_q', rcube_utils::INPUT_GPC);

        // define list of cols to be displayed
//        $a_show_cols = array('name');

        // Get the list
        $list = $this->enigma->engine->list_keys($search);

        if ($list && ($list instanceof enigma_error))
            $this->rc->output->show_message('enigma.keylisterror', 'error');
        else if (empty($list))
            $this->rc->output->show_message('enigma.nokeysfound', 'notice');
        else {
            if (is_array($list)) {
                // Save the size
                $listsize = count($list);

                // Sort the list by key (user) name
                usort($list, array('enigma_key', 'cmp'));

                // Slice current page
                $list = array_slice($list, ($page - 1) * $pagesize, $pagesize);

                $size = count($list);

                // Add rows
                foreach ($list as $key) {
                    $this->rc->output->command('enigma_add_list_row',
                        array('name' => rcube::Q($key->name), 'id' => $key->id));
                }
            }
        }

        $this->rc->output->set_env('search_request', $search);
        $this->rc->output->set_env('pagecount', ceil($listsize/$pagesize));
        $this->rc->output->set_env('current_page', $page);
        $this->rc->output->command('set_rowcount',
            $this->get_rowcount_text($listsize, $size, $page));

        $this->rc->output->send();
    }

    /**
     * Template object for list records counter.
     *
     * @param array Object attributes
     *
     * @return string HTML output
     */
    function tpl_keys_rowcount($attrib)
    {
        if (!$attrib['id'])
            $attrib['id'] = 'rcmcountdisplay';

        $this->rc->output->add_gui_object('countdisplay', $attrib['id']);

        return html::span($attrib, $this->get_rowcount_text());
    }

    /**
     * Returns text representation of list records counter
     */
    private function get_rowcount_text($all=0, $curr_count=0, $page=1)
    {
        if (!$curr_count)
            $out = $this->enigma->gettext('nokeysfound');
        else {
            $pagesize = $this->rc->config->get('pagesize', 100);
            $first = ($page - 1) * $pagesize;

            $out = $this->enigma->gettext(array(
                'name' => 'keysfromto',
                'vars' => array(
                    'from'  => $first + 1,
                    'to'    => $first + $curr_count,
                    'count' => $all)
            ));
        }

        return $out;
    }

    /**
     * Key information page handler
     */
    private function key_info()
    {
        $this->enigma->load_engine();

        $id  = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GET);
        $res = $this->enigma->engine->get_key($id);

        if ($res instanceof enigma_key) {
            $this->data = $res;
        }
        else { // error
            $this->rc->output->show_message('enigma.keyopenerror', 'error');
            $this->rc->output->command('parent.enigma_loadframe');
            $this->rc->output->send('iframe');
        }

        $this->rc->output->add_handlers(array(
            'keyname' => array($this, 'tpl_key_name'),
            'keydata' => array($this, 'tpl_key_data'),
        ));

        $this->rc->output->set_pagetitle($this->enigma->gettext('keyinfo'));
        $this->rc->output->send('enigma.keyinfo');
    }

    /**
     * Template object for key name
     */
    function tpl_key_name($attrib)
    {
        return rcube::Q($this->data->name);
    }

    /**
     * Template object for key information page content
     */
    function tpl_key_data($attrib)
    {
        $out = '';
        $table = new html_table(array('cols' => 2)); 

        // Key user ID
        $table->add('title', $this->enigma->gettext('keyuserid'));
        $table->add(null, rcube::Q($this->data->name));
        // Key ID
        $table->add('title', $this->enigma->gettext('keyid'));
        $table->add(null, $this->data->subkeys[0]->get_short_id());
        // Key type
        $keytype = $this->data->get_type();
        if ($keytype == enigma_key::TYPE_KEYPAIR)
            $type = $this->enigma->gettext('typekeypair');
        else if ($keytype == enigma_key::TYPE_PUBLIC)
            $type = $this->enigma->gettext('typepublickey');
        $table->add('title', $this->enigma->gettext('keytype'));
        $table->add(null, $type);
        // Key fingerprint
        $table->add('title', $this->enigma->gettext('fingerprint'));
        $table->add(null, $this->data->subkeys[0]->get_fingerprint());

        $out .= html::tag('fieldset', null,
            html::tag('legend', null,
                $this->enigma->gettext('basicinfo')) . $table->show($attrib));
/*
        // Subkeys
        $table = new html_table(array('cols' => 6)); 
        // Columns: Type, ID, Algorithm, Size, Created, Expires

        $out .= html::tag('fieldset', null,
            html::tag('legend', null, 
                $this->enigma->gettext('subkeys')) . $table->show($attrib));

        // Additional user IDs
        $table = new html_table(array('cols' => 2));
        // Columns: User ID, Validity

        $out .= html::tag('fieldset', null,
            html::tag('legend', null, 
                $this->enigma->gettext('userids')) . $table->show($attrib));
*/
        return $out;
    }

    /**
     * Key import page handler
     */
    private function key_import()
    {
        // Import process
        if ($_FILES['_file']['tmp_name'] && is_uploaded_file($_FILES['_file']['tmp_name'])) {
            $this->enigma->load_engine();
            $result = $this->enigma->engine->import_key($_FILES['_file']['tmp_name'], true);

            if (is_array($result)) {
                // reload list if any keys has been added
                if ($result['imported']) {
                    $this->rc->output->command('parent.enigma_list', 1);
                }
                else
                    $this->rc->output->command('parent.enigma_loadframe');

                $this->rc->output->show_message('enigma.keysimportsuccess', 'confirmation',
                    array('new' => $result['imported'], 'old' => $result['unchanged']));

                $this->rc->output->send('iframe');
            }
            else {
                $this->rc->output->show_message('enigma.keysimportfailed', 'error');
            }
        }
        else if ($err = $_FILES['_file']['error']) {
            if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                $this->rc->output->show_message('filesizeerror', 'error',
                    array('size' => $this->rc->show_bytes(parse_bytes(ini_get('upload_max_filesize')))));
            } else {
                $this->rc->output->show_message('fileuploaderror', 'error');
            }
        }

        $this->rc->output->add_handlers(array(
            'importform' => array($this, 'tpl_key_import_form'),
        ));

        $this->rc->output->set_pagetitle($this->enigma->gettext('keyimport'));
        $this->rc->output->send('enigma.keyimport');
    }

    /**
     * Template object for key import (upload) form
     */
    function tpl_key_import_form($attrib)
    {
        $attrib += array('id' => 'rcmKeyImportForm');

        $upload = new html_inputfield(array('type' => 'file', 'name' => '_file',
            'id' => 'rcmimportfile', 'size' => 30));

        $form = html::p(null,
            rcube::Q($this->enigma->gettext('keyimporttext'), 'show')
            . html::br() . html::br() . $upload->show()
        );

        $this->rc->output->add_label('selectimportfile', 'importwait');
        $this->rc->output->add_gui_object('importform', $attrib['id']);

        $out = $this->rc->output->form_tag(array(
            'action' => $this->rc->url(array('action' => $this->rc->action, 'a' => 'import')),
            'method' => 'post',
            'enctype' => 'multipart/form-data') + $attrib,
            $form);

        return $out;
    }

    /**
     * Key deleting
     */
    private function key_delete()
    {
        $keys = rcube_utils::get_input_value('_keys', rcube_utils::INPUT_POST);

        $this->enigma->load_engine();

        foreach ((array)$keys as $key) {
            $res = $this->enigma->engine->delete_key($key);

            if ($res !== true) {
                $this->rc->output->show_message('enigma.keyremoveerror', 'error');
                $this->rc->output->command('enigma_list');
                $this->rc->output->send();
            }
        }

        $this->rc->output->command('enigma_list');
        $this->rc->output->show_message('enigma.keyremovesuccess', 'confirmation');
        $this->rc->output->send();
    }

    private function compose_ui()
    {
/*
        $this->add_css();

        // Options menu button
        // @TODO: make this work with non-default skins
        $this->enigma->add_button(array(
            'type'     => 'link',
            'command'  => 'plugin.enigma',
            'onclick'  => "rcmail.command('menu-open', 'enigmamenu', event.target, event)",
            'class'    => 'button enigma',
            'title'    => 'securityoptions',
            'label'    => 'securityoptions',
            'domain'   => $this->enigma->ID,
            'width'    => 32,
            'height'   => 32
            ), 'toolbar');

        // Options menu contents
        $this->enigma->add_hook('render_page', array($this, 'compose_menu'));
*/
    }

    function compose_menu($p)
    {
        $menu = new html_table(array('cols' => 2));
        $chbox = new html_checkbox(array('value' => 1));

        $menu->add(null, html::label(array('for' => 'enigmadefaultopt'),
            rcube::Q($this->enigma->gettext('identdefault'))));
        $menu->add(null, $chbox->show(1, array('name' => '_enigma_default', 'id' => 'enigmadefaultopt')));

        $menu->add(null, html::label(array('for' => 'enigmasignopt'),
            rcube::Q($this->enigma->gettext('signmsg'))));
        $menu->add(null, $chbox->show(1, array('name' => '_enigma_sign', 'id' => 'enigmasignopt')));

        $menu->add(null, html::label(array('for' => 'enigmacryptopt'),
            rcube::Q($this->enigma->gettext('encryptmsg'))));
        $menu->add(null, $chbox->show(1, array('name' => '_enigma_crypt', 'id' => 'enigmacryptopt')));

        $menu = html::div(array('id' => 'enigmamenu', 'class' => 'popupmenu'),
            $menu->show());

        $p['content'] = preg_replace('/(<form name="form"[^>]+>)/i', '\\1'."\n$menu", $p['content']);

        return $p;
    }

    /**
     * Handler for message_body_prefix hook.
     * Called for every displayed (content) part of the message.
     * Adds infobox about signature verification and/or decryption
     * status above the body.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function status_message($p)
    {
        // skip: not a message part
        if ($p['part'] instanceof rcube_message) {
            return $p;
        }

        // skip: message has no signed/encoded content
        if (!$this->enigma->engine) {
            return $p;
        }

        $engine  = $this->enigma->engine;
        $part_id = $p['part']->mime_id;

        // Decryption status
        if (isset($engine->decryptions[$part_id])) {
            $attach_scripts = true;

            // get decryption status
            $status = $engine->decryptions[$part_id];

            // display status info
            $attrib['id'] = 'enigma-message';

            if ($status instanceof enigma_error) {
                $attrib['class'] = 'enigmaerror';
                $code            = $status->getCode();

                if ($code == enigma_error::E_KEYNOTFOUND) {
                    $msg = rcube::Q(str_replace('$keyid', enigma_key::format_id($status->getData('id')),
                        $this->enigma->gettext('decryptnokey')));
                }
                else if ($code == enigma_error::E_BADPASS) {
                    $msg = rcube::Q($this->enigma->gettext('decryptbadpass'));
                    $this->password_prompt($status);
                }
                else {
                    $msg = rcube::Q($this->enigma->gettext('decrypterror'));
                }
            }
            else {
                $attrib['class'] = 'enigmanotice';
                $msg = rcube::Q($this->enigma->gettext('decryptok'));
            }

            $p['prefix'] .= html::div($attrib, $msg);
        }

        // Signature verification status
        if (isset($engine->signed_parts[$part_id])
            && ($sig = $engine->signatures[$engine->signed_parts[$part_id]])
        ) {
            $attach_scripts = true;

            // display status info
            $attrib['id'] = 'enigma-message';

            if ($sig instanceof enigma_signature) {
                $sender = ($sig->name ? $sig->name . ' ' : '') . '<' . $sig->email . '>';

                if ($sig->valid === enigma_error::E_UNVERIFIED) {
                    $attrib['class'] = 'enigmawarning';
                    $msg = str_replace('$sender', $sender, $this->enigma->gettext('sigunverified'));
                    $msg = str_replace('$keyid', $sig->id, $msg);
                    $msg = rcube::Q($msg);
                }
                else if ($sig->valid) {
                    $attrib['class'] = 'enigmanotice';
                    $msg = rcube::Q(str_replace('$sender', $sender, $this->enigma->gettext('sigvalid')));
                }
                else {
                    $attrib['class'] = 'enigmawarning';
                    $msg = rcube::Q(str_replace('$sender', $sender, $this->enigma->gettext('siginvalid')));
                }
            }
            else if ($sig && $sig->getCode() == enigma_error::E_KEYNOTFOUND) {
                $attrib['class'] = 'enigmawarning';
                $msg = rcube::Q(str_replace('$keyid', enigma_key::format_id($sig->getData('id')),
                    $this->enigma->gettext('signokey')));
            }
            else {
                $attrib['class'] = 'enigmaerror';
                $msg = rcube::Q($this->enigma->gettext('sigerror'));
            }
/*
            $msg .= '&nbsp;' . html::a(array('href' => "#sigdetails",
                'onclick' => rcmail_output::JS_OBJECT_NAME.".command('enigma-sig-details')"),
                rcube::Q($this->enigma->gettext('showdetails')));
*/
            // test
//            $msg .= '<br /><pre>'.$sig->body.'</pre>';

            $p['prefix'] .= html::div($attrib, $msg);

            // Display each signature message only once
            unset($engine->signatures[$engine->signed_parts[$part_id]]);
        }

        if ($attach_scripts) {
            // add css and js script
            $this->add_css();
            $this->add_js();
        }

        return $p;
    }

    /**
     * Handler for message_load hook.
     * Check message bodies and attachments for keys/certs.
     */
    function message_load($p)
    {
        $engine = $this->enigma->load_engine();

        // handle attachments vcard attachments
        foreach ((array) $p['object']->attachments as $attachment) {
            if ($engine->is_keys_part($attachment)) {
                $this->keys_parts[] = $attachment->mime_id;
            }
        }

        // the same with message bodies
        foreach ((array) $p['object']->parts as $part) {
            if ($engine->is_keys_part($part)) {
                $this->keys_parts[]  = $part->mime_id;
                $this->keys_bodies[] = $part->mime_id;
            }
        }

        // @TODO: inline PGP keys

        if ($this->keys_parts) {
            $this->enigma->add_texts('localization');
        }

        return $p;
    }

    /**
     * Handler for template_object_messagebody hook.
     * This callback function adds a box below the message content
     * if there is a key/cert attachment available
     */
    function message_output($p)
    {
        foreach ($this->keys_parts as $part) {
            // remove part's body
            if (in_array($part, $this->keys_bodies)) {
                $p['content'] = '';
            }

            // add box below message body
            $p['content'] .= html::p(array('class' => 'enigmaattachment'),
                html::a(array(
                    'href'    => "#",
                    'onclick' => "return ".rcmail_output::JS_OBJECT_NAME.".enigma_import_attachment('".rcube::JQ($part)."')",
                    'title'   => $this->enigma->gettext('keyattimport')),
                    html::span(null, $this->enigma->gettext('keyattfound'))));

            $attach_scripts = true;
        }

        if ($attach_scripts) {
            // add css and js script
            $this->add_css();
            $this->add_js();
        }

        return $p;
    }

}
