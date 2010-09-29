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
    private $css_added;
    private $data;


    function __construct($enigma_plugin, $home='')
    {
        $this->enigma = $enigma_plugin;
        $this->rc = $enigma_plugin->rc;
        // we cannot use $enigma_plugin->home here
        $this->home = $home;
    }

    /**
     * UI initialization and requests handlers.
     *
     * @param string Preferences section
     */
    function init($section='')
    {
        $this->enigma->include_script('enigma.js');

        // Enigma actions
        if ($this->rc->action == 'plugin.enigma') {
            $action = get_input_value('_a', RCUBE_INPUT_GPC);

            switch ($action) {
                case 'keyedit':
                    $this->key_edit();
                    break;
                case 'keyimport':
                    $this->key_import();
                    break;
                case 'keysearch':
                case 'keylist':
                    $this->key_list();
                    break;
                case 'keyinfo':
                default:
                    $this->key_info();
            }
        }
        // Preferences UI
        else { // if ($this->rc->action == 'edit-prefs') {
            if ($section == 'enigmacerts') {
                $this->rc->output->add_handlers(array(
                    'keyslist' => array($this, 'tpl_certs_list'),
                    'keyframe' => array($this, 'tpl_cert_frame'),
                    'countdisplay' => array($this, 'tpl_certs_rowcount'),
                    'searchform' => array($this->rc->output, 'search_form'),
                ));
                $this->rc->output->set_pagetitle($this->enigma->gettext('enigmacerts'));
                $this->rc->output->send('enigma.certs'); 
            }
            else {
                $this->rc->output->add_handlers(array(
                    'keyslist' => array($this, 'tpl_keys_list'),
                    'keyframe' => array($this, 'tpl_key_frame'),
                    'countdisplay' => array($this, 'tpl_keys_rowcount'),
                    'searchform' => array($this->rc->output, 'search_form'),
                ));
                $this->rc->output->set_pagetitle($this->enigma->gettext('enigmakeys'));
                $this->rc->output->send('enigma.keys'); 
            }
        }
    }

   /**
     * Adds CSS style file to the page header.
     */
    function add_css()
    {
        if ($this->css_loaded)
            return;

        $skin = $this->rc->config->get('skin');
        if (!file_exists($this->home . "/skins/$skin/enigma.css"))
            $skin = 'default';

        $this->enigma->include_stylesheet("skins/$skin/enigma.css");
        $this->css_added = true;
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
            $this->rc->output->abs_url($attrib['src']) : 'program/blank.gif');

        return html::tag('iframe', $attrib);
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
        $out = rcube_table_output($attrib, array(), $a_show_cols, 'id');

        // set client env
        $this->rc->output->add_gui_object('keyslist', $attrib['id']);
        $this->rc->output->include_script('list.js');

        // add some labels to client
        $this->rc->output->add_label('enigma.keyconfirmdelete');

        return $out;
    }

    /**
     * Key listing (and searching) request handler
     */
    private function key_list()
    {
        $this->enigma->load_engine();

        $pagesize = $this->rc->config->get('pagesize', 100);
        $page     = max(intval(get_input_value('_p', RCUBE_INPUT_GPC)), 1);
        $search   = get_input_value('_q', RCUBE_INPUT_GPC);

        // define list of cols to be displayed
        $a_show_cols = array('name');
        $result = array();

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
                foreach($list as $idx => $key) {
                    $this->rc->output->command('enigma_add_list_row',
                        array('name' => Q($key->name), 'id' => $key->id));
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
        $id = get_input_value('_id', RCUBE_INPUT_GET);

        $this->enigma->load_engine();
        $res = $this->enigma->engine->get_key($id);

        if ($res instanceof enigma_key)
            $this->data = $res;
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
        return Q($this->data->name);
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
        $table->add(null, Q($this->data->name));
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
            else
                $this->rc->output->show_message('enigma.keysimportfailed', 'error');
        }
        else if ($err = $_FILES['_file']['error']) {
            if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                $this->rc->output->show_message('filesizeerror', 'error',
                    array('size' => show_bytes(parse_bytes(ini_get('upload_max_filesize')))));
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
            Q($this->enigma->gettext('keyimporttext'), 'show')
            . html::br() . html::br() . $upload->show()
        );

        $this->rc->output->add_label('selectimportfile', 'importwait');
        $this->rc->output->add_gui_object('importform', $attrib['id']);

        $out = $this->rc->output->form_tag(array(
            'action' => $this->rc->url(array('action' => 'plugin.enigma', 'a' => 'keyimport')),
            'method' => 'post',
            'enctype' => 'multipart/form-data') + $attrib,
            $form);

        return $out;
    }


}
