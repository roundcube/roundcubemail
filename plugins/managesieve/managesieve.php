<?php

/**
 * Managesieve (Sieve Filters)
 *
 * Plugin that adds a possibility to manage Sieve filters in Thunderbird's style.
 * It's clickable interface which operates on text scripts and communicates
 * with server using managesieve protocol. Adds Filters tab in Settings.
 *
 * @version @package_version@
 * @author Aleksander Machniak <alec@alec.pl>
 *
 * Configuration (see config.inc.php.dist)
 *
 * Copyright (C) 2008-2012, The Roundcube Dev Team
 * Copyright (C) 2011-2012, Kolab Systems AG
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

class managesieve extends rcube_plugin
{
    public $task = 'mail|settings';

    private $rc;
    private $sieve;
    private $errors;
    private $form;
    private $tips = array();
    private $script = array();
    private $exts = array();
    private $list;
    private $active = array();
    private $headers = array(
        'subject' => 'Subject',
        'from'    => 'From',
        'to'      => 'To',
    );
    private $addr_headers = array(
        // Required
        "from", "to", "cc", "bcc", "sender", "resent-from", "resent-to",
        // Additional (RFC 822 / RFC 2822)
        "reply-to", "resent-reply-to", "resent-sender", "resent-cc", "resent-bcc",
        // Non-standard (RFC 2076, draft-palme-mailext-headers-08.txt)
        "for-approval", "for-handling", "for-comment", "apparently-to", "errors-to",
        "delivered-to", "return-receipt-to", "x-admin", "read-receipt-to",
        "x-confirm-reading-to", "return-receipt-requested",
        "registered-mail-reply-requested-by", "mail-followup-to", "mail-reply-to",
        "abuse-reports-to", "x-complaints-to", "x-report-abuse-to",
        // Undocumented
        "x-beenthere",
    );

    const VERSION  = '6.2';
    const PROGNAME = 'Roundcube (Managesieve)';
    const PORT     = 4190;


    function init()
    {
        $this->rc = rcmail::get_instance();

        // register actions
        $this->register_action('plugin.managesieve', array($this, 'managesieve_actions'));
        $this->register_action('plugin.managesieve-save', array($this, 'managesieve_save'));

        if ($this->rc->task == 'settings') {
            $this->init_ui();
        }
        else if ($this->rc->task == 'mail') {
            // register message hook
            $this->add_hook('message_headers_output', array($this, 'mail_headers'));

            // inject Create Filter popup stuff
            if (empty($this->rc->action) || $this->rc->action == 'show') {
                $this->mail_task_handler();
            }
        }
    }

    /**
     * Initializes plugin's UI (localization, js script)
     */
    private function init_ui()
    {
        if ($this->ui_initialized)
            return;

        // load localization
        $this->add_texts('localization/', array('filters','managefilters'));
        $this->include_script('managesieve.js');

        $this->ui_initialized = true;
    }

    /**
     * Add UI elements to the 'mailbox view' and 'show message' UI.
     */
    function mail_task_handler()
    {
        // use jQuery for popup window
        $this->require_plugin('jqueryui');

        // include js script and localization
        $this->init_ui();

        // include styles
        $skin_path = $this->local_skin_path();
        if (is_file($this->home . "/$skin_path/managesieve_mail.css")) {
            $this->include_stylesheet("$skin_path/managesieve_mail.css");
        }

        // add 'Create filter' item to message menu
        $this->api->add_content(html::tag('li', null, 
            $this->api->output->button(array(
                'command'  => 'managesieve-create',
                'label'    => 'managesieve.filtercreate',
                'type'     => 'link',
                'classact' => 'icon filterlink active',
                'class'    => 'icon filterlink',
                'innerclass' => 'icon filterlink',
            ))), 'messagemenu');

        // register some labels/messages
        $this->rc->output->add_label('managesieve.newfilter', 'managesieve.usedata',
            'managesieve.nodata', 'managesieve.nextstep', 'save');

        $this->rc->session->remove('managesieve_current');
    }

    /**
     * Get message headers for popup window
     */
    function mail_headers($args)
    {
        // this hook can be executed many times
        if ($this->mail_headers_done) {
            return $args;
        }

        $this->mail_headers_done = true;

        $headers = $args['headers'];
        $ret     = array();

        if ($headers->subject)
            $ret[] = array('Subject', rcube_mime::decode_header($headers->subject));

        // @TODO: List-Id, others?
        foreach (array('From', 'To') as $h) {
            $hl = strtolower($h);
            if ($headers->$hl) {
                $list = rcube_mime::decode_address_list($headers->$hl);
                foreach ($list as $item) {
                    if ($item['mailto']) {
                        $ret[] = array($h, $item['mailto']);
                    }
                }
            }
        }

        if ($this->rc->action == 'preview')
            $this->rc->output->command('parent.set_env', array('sieve_headers' => $ret));
        else
            $this->rc->output->set_env('sieve_headers', $ret);


        return $args;
    }

    /**
     * Loads configuration, initializes plugin (including sieve connection)
     */
    function managesieve_start()
    {
        $this->load_config();

        // register UI objects
        $this->rc->output->add_handlers(array(
            'filterslist'    => array($this, 'filters_list'),
            'filtersetslist' => array($this, 'filtersets_list'),
            'filterframe'    => array($this, 'filter_frame'),
            'filterform'     => array($this, 'filter_form'),
            'filtersetform'  => array($this, 'filterset_form'),
        ));

        // Add include path for internal classes
        $include_path = $this->home . '/lib' . PATH_SEPARATOR;
        $include_path .= ini_get('include_path');
        set_include_path($include_path);

        // Get connection parameters
        $host = $this->rc->config->get('managesieve_host', 'localhost');
        $port = $this->rc->config->get('managesieve_port');
        $tls  = $this->rc->config->get('managesieve_usetls', false);

        $host = rcube_parse_host($host);
        $host = rcube_idn_to_ascii($host);

        // remove tls:// prefix, set TLS flag
        if (($host = preg_replace('|^tls://|i', '', $host, 1, $cnt)) && $cnt) {
            $tls = true;
        }

        if (empty($port)) {
            $port = getservbyname('sieve', 'tcp');
            if (empty($port)) {
                $port = self::PORT;
            }
        }

        $plugin = $this->rc->plugins->exec_hook('managesieve_connect', array(
            'user'      => $_SESSION['username'],
            'password'  => $this->rc->decrypt($_SESSION['password']),
            'host'      => $host,
            'port'      => $port,
            'usetls'    => $tls,
            'auth_type' => $this->rc->config->get('managesieve_auth_type'),
            'disabled'  => $this->rc->config->get('managesieve_disabled_extensions'),
            'debug'     => $this->rc->config->get('managesieve_debug', false),
            'auth_cid'  => $this->rc->config->get('managesieve_auth_cid'),
            'auth_pw'   => $this->rc->config->get('managesieve_auth_pw'),
        ));

        // try to connect to managesieve server and to fetch the script
        $this->sieve = new rcube_sieve(
            $plugin['user'],
            $plugin['password'],
            $plugin['host'],
            $plugin['port'],
            $plugin['auth_type'],
            $plugin['usetls'],
            $plugin['disabled'],
            $plugin['debug'],
            $plugin['auth_cid'],
            $plugin['auth_pw']
        );

        if (!($error = $this->sieve->error())) {
            // Get list of scripts
            $list = $this->list_scripts();

            if (!empty($_GET['_set']) || !empty($_POST['_set'])) {
                $script_name = get_input_value('_set', RCUBE_INPUT_GPC, true);
            }
            else if (!empty($_SESSION['managesieve_current'])) {
                $script_name = $_SESSION['managesieve_current'];
            }
            else {
                // get (first) active script
                if (!empty($this->active[0])) {
                    $script_name = $this->active[0];
                }
                else if ($list) {
                    $script_name = $list[0];
                }
                // create a new (initial) script
                else {
                    // if script not exists build default script contents
                    $script_file = $this->rc->config->get('managesieve_default');
                    $script_name = $this->rc->config->get('managesieve_script_name');

                    if (empty($script_name))
                        $script_name = 'roundcube';

                    if ($script_file && is_readable($script_file))
                        $content = file_get_contents($script_file);

                    // add script and set it active
                    if ($this->sieve->save_script($script_name, $content)) {
                        $this->activate_script($script_name);
                        $this->list[] = $script_name;
                    }
                }
            }

            if ($script_name) {
                $this->sieve->load($script_name);
            }

            $error = $this->sieve->error();
        }

        // finally set script objects
        if ($error) {
            switch ($error) {
                case SIEVE_ERROR_CONNECTION:
                case SIEVE_ERROR_LOGIN:
                    $this->rc->output->show_message('managesieve.filterconnerror', 'error');
                    break;
                default:
                    $this->rc->output->show_message('managesieve.filterunknownerror', 'error');
                    break;
            }

            raise_error(array('code' => 403, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Unable to connect to managesieve on $host:$port"), true, false);

            // to disable 'Add filter' button set env variable
            $this->rc->output->set_env('filterconnerror', true);
            $this->script = array();
        }
        else {
            $this->exts = $this->sieve->get_extensions();
            $this->script = $this->sieve->script->as_array();
            $this->rc->output->set_env('currentset', $this->sieve->current);
            $_SESSION['managesieve_current'] = $this->sieve->current;
        }

        return $error;
    }

    function managesieve_actions()
    {
        $this->init_ui();

        $error = $this->managesieve_start();

        // Handle user requests
        if ($action = get_input_value('_act', RCUBE_INPUT_GPC)) {
            $fid = (int) get_input_value('_fid', RCUBE_INPUT_POST);

            if ($action == 'delete' && !$error) {
                if (isset($this->script[$fid])) {
                    if ($this->sieve->script->delete_rule($fid))
                        $result = $this->save_script();

                    if ($result === true) {
                        $this->rc->output->show_message('managesieve.filterdeleted', 'confirmation');
                        $this->rc->output->command('managesieve_updatelist', 'del', array('id' => $fid));
                    } else {
                        $this->rc->output->show_message('managesieve.filterdeleteerror', 'error');
                    }
                }
            }
            else if ($action == 'move' && !$error) {
                if (isset($this->script[$fid])) {
                    $to   = (int) get_input_value('_to', RCUBE_INPUT_POST);
                    $rule = $this->script[$fid];

                    // remove rule
                    unset($this->script[$fid]);
                    $this->script = array_values($this->script);

                    // add at target position
                    if ($to >= count($this->script)) {
                        $this->script[] = $rule;
                    }
                    else {
                        $script = array();
                        foreach ($this->script as $idx => $r) {
                            if ($idx == $to)
                                $script[] = $rule;
                            $script[] = $r;
                        }
                        $this->script = $script;
                    }

                    $this->sieve->script->content = $this->script;
                    $result = $this->save_script();

                    if ($result === true) {
                        $result = $this->list_rules();

                        $this->rc->output->show_message('managesieve.moved', 'confirmation');
                        $this->rc->output->command('managesieve_updatelist', 'list',
                            array('list' => $result, 'clear' => true, 'set' => $to));
                    } else {
                        $this->rc->output->show_message('managesieve.moveerror', 'error');
                    }
                }
            }
            else if ($action == 'act' && !$error) {
                if (isset($this->script[$fid])) {
                    $rule     = $this->script[$fid];
                    $disabled = $rule['disabled'] ? true : false;
                    $rule['disabled'] = !$disabled;
                    $result = $this->sieve->script->update_rule($fid, $rule);

                    if ($result !== false)
                        $result = $this->save_script();

                    if ($result === true) {
                        if ($rule['disabled'])
                            $this->rc->output->show_message('managesieve.deactivated', 'confirmation');
                        else
                            $this->rc->output->show_message('managesieve.activated', 'confirmation');
                        $this->rc->output->command('managesieve_updatelist', 'update',
                            array('id' => $fid, 'disabled' => $rule['disabled']));
                    } else {
                        if ($rule['disabled'])
                            $this->rc->output->show_message('managesieve.deactivateerror', 'error');
                        else
                            $this->rc->output->show_message('managesieve.activateerror', 'error');
                    }
                }
            }
            else if ($action == 'setact' && !$error) {
                $script_name = get_input_value('_set', RCUBE_INPUT_GPC, true);
                $result = $this->activate_script($script_name);
                $kep14  = $this->rc->config->get('managesieve_kolab_master');

                if ($result === true) {
                    $this->rc->output->set_env('active_sets', $this->active);
                    $this->rc->output->show_message('managesieve.setactivated', 'confirmation');
                    $this->rc->output->command('managesieve_updatelist', 'setact',
                        array('name' => $script_name, 'active' => true, 'all' => !$kep14));
                } else {
                    $this->rc->output->show_message('managesieve.setactivateerror', 'error');
                }
            }
            else if ($action == 'deact' && !$error) {
                $script_name = get_input_value('_set', RCUBE_INPUT_GPC, true);
                $result = $this->deactivate_script($script_name);

                if ($result === true) {
                    $this->rc->output->set_env('active_sets', $this->active);
                    $this->rc->output->show_message('managesieve.setdeactivated', 'confirmation');
                    $this->rc->output->command('managesieve_updatelist', 'setact',
                        array('name' => $script_name, 'active' => false));
                } else {
                    $this->rc->output->show_message('managesieve.setdeactivateerror', 'error');
                }
            }
            else if ($action == 'setdel' && !$error) {
                $script_name = get_input_value('_set', RCUBE_INPUT_GPC, true);
                $result = $this->remove_script($script_name);

                if ($result === true) {
                    $this->rc->output->show_message('managesieve.setdeleted', 'confirmation');
                    $this->rc->output->command('managesieve_updatelist', 'setdel',
                        array('name' => $script_name));
                    $this->rc->session->remove('managesieve_current');
                } else {
                    $this->rc->output->show_message('managesieve.setdeleteerror', 'error');
                }
            }
            else if ($action == 'setget') {
                $script_name = get_input_value('_set', RCUBE_INPUT_GPC, true);
                $script = $this->sieve->get_script($script_name);

                if (PEAR::isError($script))
                    exit;

                $browser = new rcube_browser;

                // send download headers
                header("Content-Type: application/octet-stream");
                header("Content-Length: ".strlen($script));

                if ($browser->ie)
                    header("Content-Type: application/force-download");
                if ($browser->ie && $browser->ver < 7)
                    $filename = rawurlencode(abbreviate_string($script_name, 55));
                else if ($browser->ie)
                    $filename = rawurlencode($script_name);
                else
                    $filename = addcslashes($script_name, '\\"');

                header("Content-Disposition: attachment; filename=\"$filename.txt\"");
                echo $script;
                exit;
            }
            else if ($action == 'list') {
                $result = $this->list_rules();

                $this->rc->output->command('managesieve_updatelist', 'list', array('list' => $result));
            }
            else if ($action == 'ruleadd') {
                $rid = get_input_value('_rid', RCUBE_INPUT_GPC);
                $id = $this->genid();
                $content = $this->rule_div($fid, $id, false);

                $this->rc->output->command('managesieve_rulefill', $content, $id, $rid);
            }
            else if ($action == 'actionadd') {
                $aid = get_input_value('_aid', RCUBE_INPUT_GPC);
                $id = $this->genid();
                $content = $this->action_div($fid, $id, false);

                $this->rc->output->command('managesieve_actionfill', $content, $id, $aid);
            }

            $this->rc->output->send();
        }
        else if ($this->rc->task == 'mail') {
            // Initialize the form
            $rules = get_input_value('r', RCUBE_INPUT_GET);
            if (!empty($rules)) {
                $i = 0;
                foreach ($rules as $rule) {
                    list($header, $value) = explode(':', $rule, 2);
                    $tests[$i] = array(
                        'type' => 'contains',
                        'test' => 'header',
                        'arg1' => $header,
                        'arg2' => $value,
                    );
                    $i++;
                }

                $this->form = array(
                    'join'  => count($tests) > 1 ? 'allof' : 'anyof',
                    'name'  => '',
                    'tests' => $tests,
                    'actions' => array(
                        0 => array('type' => 'fileinto'),
                        1 => array('type' => 'stop'),
                    ),
                );
            }
        }

        $this->managesieve_send();
    }

    function managesieve_save()
    {
        // load localization
        $this->add_texts('localization/', array('filters','managefilters'));

        // include main js script
        if ($this->api->output->type == 'html') {
            $this->include_script('managesieve.js');
        }

        // Init plugin and handle managesieve connection
        $error = $this->managesieve_start();

        // get request size limits (#1488648)
        $max_post = max(array(
            ini_get('max_input_vars'),
            ini_get('suhosin.request.max_vars'),
            ini_get('suhosin.post.max_vars'),
        ));
        $max_depth = max(array(
            ini_get('suhosin.request.max_array_depth'),
            ini_get('suhosin.post.max_array_depth'),
        ));

        // check request size limit
        if ($max_post && count($_POST, COUNT_RECURSIVE) >= $max_post) {
            rcube::raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Request size limit exceeded (one of max_input_vars/suhosin.request.max_vars/suhosin.post.max_vars)"
                ), true, false);
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
        }
        // check request depth limits
        else if ($max_depth && count($_POST['_header']) > $max_depth) {
            rcube::raise_error(array(
                'code' => 500, 'type' => 'php',
                'file' => __FILE__, 'line' => __LINE__,
                'message' => "Request size limit exceeded (one of suhosin.request.max_array_depth/suhosin.post.max_array_depth)"
                ), true, false);
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
        }
        // filters set add action
        else if (!empty($_POST['_newset'])) {
            $name       = get_input_value('_name', RCUBE_INPUT_POST, true);
            $copy       = get_input_value('_copy', RCUBE_INPUT_POST, true);
            $from       = get_input_value('_from', RCUBE_INPUT_POST);
            $exceptions = $this->rc->config->get('managesieve_filename_exceptions');
            $kolab      = $this->rc->config->get('managesieve_kolab_master');
            $name_uc    = mb_strtolower($name);
            $list       = $this->list_scripts();

            if (!$name) {
                $this->errors['name'] = $this->gettext('cannotbeempty');
            }
            else if (mb_strlen($name) > 128) {
                $this->errors['name'] = $this->gettext('nametoolong');
            }
            else if (!empty($exceptions) && in_array($name, (array)$exceptions)) {
                $this->errors['name'] = $this->gettext('namereserved');
            }
            else if (!empty($kolab) && in_array($name_uc, array('MASTER', 'USER', 'MANAGEMENT'))) {
                $this->errors['name'] = $this->gettext('namereserved');
            }
            else if (in_array($name, $list)) {
                $this->errors['name'] = $this->gettext('setexist');
            }
            else if ($from == 'file') {
                // from file
                if (is_uploaded_file($_FILES['_file']['tmp_name'])) {
                    $file = file_get_contents($_FILES['_file']['tmp_name']);
                    $file = preg_replace('/\r/', '', $file);
                    // for security don't save script directly
                    // check syntax before, like this...
                    $this->sieve->load_script($file);
                    if (!$this->save_script($name)) {
                        $this->errors['file'] = $this->gettext('setcreateerror');
                    }
                }
                else {  // upload failed
                    $err = $_FILES['_file']['error'];

                    if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                        $msg = rcube_label(array('name' => 'filesizeerror',
                            'vars' => array('size' =>
                                show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
                    }
                    else {
                        $this->errors['file'] = $this->gettext('fileuploaderror');
                    }
                }
            }
            else if (!$this->sieve->copy($name, $from == 'set' ? $copy : '')) {
                $error = 'managesieve.setcreateerror';
            }

            if (!$error && empty($this->errors)) {
                // Find position of the new script on the list
                $list[] = $name;
                asort($list, SORT_LOCALE_STRING);
                $list  = array_values($list);
                $index = array_search($name, $list);

                $this->rc->output->show_message('managesieve.setcreated', 'confirmation');
                $this->rc->output->command('parent.managesieve_updatelist', 'setadd',
                    array('name' => $name, 'index' => $index));
            } else if ($msg) {
                $this->rc->output->command('display_message', $msg, 'error');
            } else if ($error) {
                $this->rc->output->show_message($error, 'error');
            }
        }
        // filter add/edit action
        else if (isset($_POST['_name'])) {
            $name = trim(get_input_value('_name', RCUBE_INPUT_POST, true));
            $fid  = trim(get_input_value('_fid', RCUBE_INPUT_POST));
            $join = trim(get_input_value('_join', RCUBE_INPUT_POST));

            // and arrays
            $headers        = get_input_value('_header', RCUBE_INPUT_POST);
            $cust_headers   = get_input_value('_custom_header', RCUBE_INPUT_POST);
            $ops            = get_input_value('_rule_op', RCUBE_INPUT_POST);
            $sizeops        = get_input_value('_rule_size_op', RCUBE_INPUT_POST);
            $sizeitems      = get_input_value('_rule_size_item', RCUBE_INPUT_POST);
            $sizetargets    = get_input_value('_rule_size_target', RCUBE_INPUT_POST);
            $targets        = get_input_value('_rule_target', RCUBE_INPUT_POST, true);
            $mods           = get_input_value('_rule_mod', RCUBE_INPUT_POST);
            $mod_types      = get_input_value('_rule_mod_type', RCUBE_INPUT_POST);
            $body_trans     = get_input_value('_rule_trans', RCUBE_INPUT_POST);
            $body_types     = get_input_value('_rule_trans_type', RCUBE_INPUT_POST, true);
            $comparators    = get_input_value('_rule_comp', RCUBE_INPUT_POST);
            $act_types      = get_input_value('_action_type', RCUBE_INPUT_POST, true);
            $mailboxes      = get_input_value('_action_mailbox', RCUBE_INPUT_POST, true);
            $act_targets    = get_input_value('_action_target', RCUBE_INPUT_POST, true);
            $area_targets   = get_input_value('_action_target_area', RCUBE_INPUT_POST, true);
            $reasons        = get_input_value('_action_reason', RCUBE_INPUT_POST, true);
            $addresses      = get_input_value('_action_addresses', RCUBE_INPUT_POST, true);
            $days           = get_input_value('_action_days', RCUBE_INPUT_POST);
            $subject        = get_input_value('_action_subject', RCUBE_INPUT_POST, true);
            $flags          = get_input_value('_action_flags', RCUBE_INPUT_POST);
            $varnames       = get_input_value('_action_varname', RCUBE_INPUT_POST);
            $varvalues      = get_input_value('_action_varvalue', RCUBE_INPUT_POST);
            $varmods        = get_input_value('_action_varmods', RCUBE_INPUT_POST);
            $notifyaddrs    = get_input_value('_action_notifyaddress', RCUBE_INPUT_POST);
            $notifybodies   = get_input_value('_action_notifybody', RCUBE_INPUT_POST);
            $notifymessages = get_input_value('_action_notifymessage', RCUBE_INPUT_POST);
            $notifyfrom     = get_input_value('_action_notifyfrom', RCUBE_INPUT_POST);
            $notifyimp      = get_input_value('_action_notifyimportance', RCUBE_INPUT_POST);

            // we need a "hack" for radiobuttons
            foreach ($sizeitems as $item)
                $items[] = $item;

            $this->form['disabled'] = $_POST['_disabled'] ? true : false;
            $this->form['join']     = $join=='allof' ? true : false;
            $this->form['name']     = $name;
            $this->form['tests']    = array();
            $this->form['actions']  = array();

            if ($name == '')
                $this->errors['name'] = $this->gettext('cannotbeempty');
            else {
                foreach($this->script as $idx => $rule)
                    if($rule['name'] == $name && $idx != $fid) {
                        $this->errors['name'] = $this->gettext('ruleexist');
                        break;
                    }
            }

            $i = 0;
            // rules
            if ($join == 'any') {
                $this->form['tests'][0]['test'] = 'true';
            }
            else {
                foreach ($headers as $idx => $header) {
                    $header     = $this->strip_value($header);
                    $target     = $this->strip_value($targets[$idx], true);
                    $operator   = $this->strip_value($ops[$idx]);
                    $comparator = $this->strip_value($comparators[$idx]);

                    if ($header == 'size') {
                        $sizeop     = $this->strip_value($sizeops[$idx]);
                        $sizeitem   = $this->strip_value($items[$idx]);
                        $sizetarget = $this->strip_value($sizetargets[$idx]);

                        $this->form['tests'][$i]['test'] = 'size';
                        $this->form['tests'][$i]['type'] = $sizeop;
                        $this->form['tests'][$i]['arg']  = $sizetarget;

                        if ($sizetarget == '')
                            $this->errors['tests'][$i]['sizetarget'] = $this->gettext('cannotbeempty');
                        else if (!preg_match('/^[0-9]+(K|M|G)?$/i', $sizetarget.$sizeitem, $m)) {
                            $this->errors['tests'][$i]['sizetarget'] = $this->gettext('forbiddenchars');
                            $this->form['tests'][$i]['item'] = $sizeitem;
                        }
                        else
                            $this->form['tests'][$i]['arg'] .= $m[1];
                    }
                    else if ($header == 'body') {
                        $trans      = $this->strip_value($body_trans[$idx]);
                        $trans_type = $this->strip_value($body_types[$idx], true);

                        if (preg_match('/^not/', $operator))
                            $this->form['tests'][$i]['not'] = true;
                        $type = preg_replace('/^not/', '', $operator);

                        if ($type == 'exists') {
                            $this->errors['tests'][$i]['op'] = true;
                        }

                        $this->form['tests'][$i]['test'] = 'body';
                        $this->form['tests'][$i]['type'] = $type;
                        $this->form['tests'][$i]['arg']  = $target;

                        if ($target == '' && $type != 'exists')
                            $this->errors['tests'][$i]['target'] = $this->gettext('cannotbeempty');
                        else if (preg_match('/^(value|count)-/', $type) && !preg_match('/[0-9]+/', $target))
                            $this->errors['tests'][$i]['target'] = $this->gettext('forbiddenchars');

                        $this->form['tests'][$i]['part'] = $trans;
                        if ($trans == 'content') {
                            $this->form['tests'][$i]['content'] = $trans_type;
                        }
                    }
                    else {
                        $cust_header = $headers = $this->strip_value($cust_headers[$idx]);
                        $mod      = $this->strip_value($mods[$idx]);
                        $mod_type = $this->strip_value($mod_types[$idx]);

                        if (preg_match('/^not/', $operator))
                            $this->form['tests'][$i]['not'] = true;
                        $type = preg_replace('/^not/', '', $operator);

                        if ($header == '...') {
                            $headers = preg_split('/[\s,]+/', $cust_header, -1, PREG_SPLIT_NO_EMPTY);

                            if (!count($headers))
                                $this->errors['tests'][$i]['header'] = $this->gettext('cannotbeempty');
                            else {
                                foreach ($headers as $hr) {
                                    // RFC2822: printable ASCII except colon
                                    if (!preg_match('/^[\x21-\x39\x41-\x7E]+$/i', $hr)) {
                                        $this->errors['tests'][$i]['header'] = $this->gettext('forbiddenchars');
                                    }
                                }
                            }

                            if (empty($this->errors['tests'][$i]['header']))
                                $cust_header = (is_array($headers) && count($headers) == 1) ? $headers[0] : $headers;
                        }

                        if ($type == 'exists') {
                            $this->form['tests'][$i]['test'] = 'exists';
                            $this->form['tests'][$i]['arg'] = $header == '...' ? $cust_header : $header;
                        }
                        else {
                            $test   = 'header';
                            $header = $header == '...' ? $cust_header : $header;

                            if ($mod == 'address' || $mod == 'envelope') {
                                $found = false;
                                if (empty($this->errors['tests'][$i]['header'])) {
                                    foreach ((array)$header as $hdr) {
                                        if (!in_array(strtolower(trim($hdr)), $this->addr_headers))
                                            $found = true;
                                    }
                                }
                                if (!$found)
                                    $test = $mod;
                            }

                            $this->form['tests'][$i]['type'] = $type;
                            $this->form['tests'][$i]['test'] = $test;
                            $this->form['tests'][$i]['arg1'] = $header;
                            $this->form['tests'][$i]['arg2'] = $target;

                            if ($target == '')
                                $this->errors['tests'][$i]['target'] = $this->gettext('cannotbeempty');
                            else if (preg_match('/^(value|count)-/', $type) && !preg_match('/[0-9]+/', $target))
                                $this->errors['tests'][$i]['target'] = $this->gettext('forbiddenchars');

                            if ($mod) {
                                $this->form['tests'][$i]['part'] = $mod_type;
                            }
                        }
                    }

                    if ($header != 'size' && $comparator) {
                        if (preg_match('/^(value|count)/', $this->form['tests'][$i]['type']))
                            $comparator = 'i;ascii-numeric';

                        $this->form['tests'][$i]['comparator'] = $comparator;
                    }

                    $i++;
                }
            }

            $i = 0;
            // actions
            foreach($act_types as $idx => $type) {
                $type   = $this->strip_value($type);
                $target = $this->strip_value($act_targets[$idx]);

                switch ($type) {

                case 'fileinto':
                case 'fileinto_copy':
                    $mailbox = $this->strip_value($mailboxes[$idx], false, false);
                    $this->form['actions'][$i]['target'] = $this->mod_mailbox($mailbox, 'in');
                    if ($type == 'fileinto_copy') {
                        $type = 'fileinto';
                        $this->form['actions'][$i]['copy'] = true;
                    }
                    break;

                case 'reject':
                case 'ereject':
                    $target = $this->strip_value($area_targets[$idx]);
                    $this->form['actions'][$i]['target'] = str_replace("\r\n", "\n", $target);

 //                 if ($target == '')
//                      $this->errors['actions'][$i]['targetarea'] = $this->gettext('cannotbeempty');
                    break;

                case 'redirect':
                case 'redirect_copy':
                    $this->form['actions'][$i]['target'] = $target;

                    if ($this->form['actions'][$i]['target'] == '')
                        $this->errors['actions'][$i]['target'] = $this->gettext('cannotbeempty');
                    else if (!check_email($this->form['actions'][$i]['target']))
                        $this->errors['actions'][$i]['target'] = $this->gettext('noemailwarning');

                    if ($type == 'redirect_copy') {
                        $type = 'redirect';
                        $this->form['actions'][$i]['copy'] = true;
                    }
                    break;

                case 'addflag':
                case 'setflag':
                case 'removeflag':
                    $_target = array();
                    if (empty($flags[$idx])) {
                        $this->errors['actions'][$i]['target'] = $this->gettext('noflagset');
                    }
                    else {
                        foreach ($flags[$idx] as $flag) {
                            $_target[] = $this->strip_value($flag);
                        }
                    }
                    $this->form['actions'][$i]['target'] = $_target;
                    break;

                case 'vacation':
                    $reason = $this->strip_value($reasons[$idx]);
                    $this->form['actions'][$i]['reason']    = str_replace("\r\n", "\n", $reason);
                    $this->form['actions'][$i]['days']      = $days[$idx];
                    $this->form['actions'][$i]['subject']   = $subject[$idx];
                    $this->form['actions'][$i]['addresses'] = explode(',', $addresses[$idx]);
// @TODO: vacation :mime, :from, :handle

                    if ($this->form['actions'][$i]['addresses']) {
                        foreach($this->form['actions'][$i]['addresses'] as $aidx => $address) {
                            $address = trim($address);
                            if (!$address)
                                unset($this->form['actions'][$i]['addresses'][$aidx]);
                            else if(!check_email($address)) {
                                $this->errors['actions'][$i]['addresses'] = $this->gettext('noemailwarning');
                                break;
                            } else
                                $this->form['actions'][$i]['addresses'][$aidx] = $address;
                        }
                    }

                    if ($this->form['actions'][$i]['reason'] == '')
                        $this->errors['actions'][$i]['reason'] = $this->gettext('cannotbeempty');
                    if ($this->form['actions'][$i]['days'] && !preg_match('/^[0-9]+$/', $this->form['actions'][$i]['days']))
                        $this->errors['actions'][$i]['days'] = $this->gettext('forbiddenchars');
                    break;

                case 'set':
                    $this->form['actions'][$i]['name'] = $varnames[$idx];
                    $this->form['actions'][$i]['value'] = $varvalues[$idx];
                    foreach ((array)$varmods[$idx] as $v_m) {
                        $this->form['actions'][$i][$v_m] = true;
                    }

                    if (empty($varnames[$idx])) {
                        $this->errors['actions'][$i]['name'] = $this->gettext('cannotbeempty');
                    }
                    else if (!preg_match('/^[0-9a-z_]+$/i', $varnames[$idx])) {
                        $this->errors['actions'][$i]['name'] = $this->gettext('forbiddenchars');
                    }

                    if (!isset($varvalues[$idx]) || $varvalues[$idx] === '') {
                        $this->errors['actions'][$i]['value'] = $this->gettext('cannotbeempty');
                    }
                    break;

                case 'notify':
                    if (empty($notifyaddrs[$idx])) {
                        $this->errors['actions'][$i]['address'] = $this->gettext('cannotbeempty');
                    }
                    else if (!check_email($notifyaddrs[$idx])) {
                        $this->errors['actions'][$i]['address'] = $this->gettext('noemailwarning');
                    }
                    if (!empty($notifyfrom[$idx]) && !check_email($notifyfrom[$idx])) {
                        $this->errors['actions'][$i]['from'] = $this->gettext('noemailwarning');
                    }
                    $this->form['actions'][$i]['address'] = $notifyaddrs[$idx];
                    $this->form['actions'][$i]['body'] = $notifybodies[$idx];
                    $this->form['actions'][$i]['message'] = $notifymessages[$idx];
                    $this->form['actions'][$i]['from'] = $notifyfrom[$idx];
                    $this->form['actions'][$i]['importance'] = $notifyimp[$idx];
                    break;
                }

                $this->form['actions'][$i]['type'] = $type;
                $i++;
            }

            if (!$this->errors && !$error) {
                // zapis skryptu
                if (!isset($this->script[$fid])) {
                    $fid = $this->sieve->script->add_rule($this->form);
                    $new = true;
                } else
                    $fid = $this->sieve->script->update_rule($fid, $this->form);

                if ($fid !== false)
                    $save = $this->save_script();

                if ($save && $fid !== false) {
                    $this->rc->output->show_message('managesieve.filtersaved', 'confirmation');
                    if ($this->rc->task != 'mail') {
                        $this->rc->output->command('parent.managesieve_updatelist',
                            isset($new) ? 'add' : 'update',
                            array(
                                'name' => Q($this->form['name']),
                                'id' => $fid,
                                'disabled' => $this->form['disabled']
                        ));
                    }
                    else {
                        $this->rc->output->command('managesieve_dialog_close');
                        $this->rc->output->send('iframe');
                    }
                }
                else {
                    $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
//                  $this->rc->output->send();
                }
            }
        }

        $this->managesieve_send();
    }

    private function managesieve_send()
    {
        // Handle form action
        if (isset($_GET['_framed']) || isset($_POST['_framed'])) {
            if (isset($_GET['_newset']) || isset($_POST['_newset'])) {
                $this->rc->output->send('managesieve.setedit');
            }
            else {
                $this->rc->output->send('managesieve.filteredit');
            }
        } else {
            $this->rc->output->set_pagetitle($this->gettext('filters'));
            $this->rc->output->send('managesieve.managesieve');
        }
    }

    // return the filters list as HTML table
    function filters_list($attrib)
    {
        // add id to message list table if not specified
        if (!strlen($attrib['id']))
            $attrib['id'] = 'rcmfilterslist';

        // define list of cols to be displayed
        $a_show_cols = array('name');

        $result = $this->list_rules();

        // create XHTML table
        $out = rcube_table_output($attrib, $result, $a_show_cols, 'id');

        // set client env
        $this->rc->output->add_gui_object('filterslist', $attrib['id']);
        $this->rc->output->include_script('list.js');

        // add some labels to client
        $this->rc->output->add_label('managesieve.filterdeleteconfirm');

        return $out;
    }

    // return the filters list as <SELECT>
    function filtersets_list($attrib, $no_env = false)
    {
        // add id to message list table if not specified
        if (!strlen($attrib['id']))
            $attrib['id'] = 'rcmfiltersetslist';

        $list = $this->list_scripts();

        if ($list) {
            asort($list, SORT_LOCALE_STRING);
        }

        if (!empty($attrib['type']) && $attrib['type'] == 'list') {
            // define list of cols to be displayed
            $a_show_cols = array('name');

            if ($list) {
                foreach ($list as $idx => $set) {
                    $scripts['S'.$idx] = $set;
                    $result[] = array(
                        'name' => Q($set),
                        'id' => 'S'.$idx,
                        'class' => !in_array($set, $this->active) ? 'disabled' : '',
                    );
                }
            }

            // create XHTML table
            $out = rcube_table_output($attrib, $result, $a_show_cols, 'id');

            $this->rc->output->set_env('filtersets', $scripts);
            $this->rc->output->include_script('list.js');
        }
        else {
            $select = new html_select(array('name' => '_set', 'id' => $attrib['id'],
                'onchange' => $this->rc->task != 'mail' ? 'rcmail.managesieve_set()' : ''));

            if ($list) {
                foreach ($list as $set)
                    $select->add($set, $set);
            }

            $out = $select->show($this->sieve->current);
        }

        // set client env
        if (!$no_env) {
            $this->rc->output->add_gui_object('filtersetslist', $attrib['id']);
            $this->rc->output->add_label('managesieve.setdeleteconfirm');
        }

        return $out;
    }

    function filter_frame($attrib)
    {
        if (!$attrib['id'])
            $attrib['id'] = 'rcmfilterframe';

        $attrib['name'] = $attrib['id'];

        $this->rc->output->set_env('contentframe', $attrib['name']);
        $this->rc->output->set_env('blankpage', $attrib['src'] ?
        $this->rc->output->abs_url($attrib['src']) : 'program/resources/blank.gif');

        return $this->rc->output->frame($attrib);
    }

    function filterset_form($attrib)
    {
        if (!$attrib['id'])
            $attrib['id'] = 'rcmfiltersetform';

        $out = '<form name="filtersetform" action="./" method="post" enctype="multipart/form-data">'."\n";

        $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $this->rc->task));
        $hiddenfields->add(array('name' => '_action', 'value' => 'plugin.managesieve-save'));
        $hiddenfields->add(array('name' => '_framed', 'value' => ($_POST['_framed'] || $_GET['_framed'] ? 1 : 0)));
        $hiddenfields->add(array('name' => '_newset', 'value' => 1));

        $out .= $hiddenfields->show();

        $name     = get_input_value('_name', RCUBE_INPUT_POST);
        $copy     = get_input_value('_copy', RCUBE_INPUT_POST);
        $selected = get_input_value('_from', RCUBE_INPUT_POST);

        // filter set name input
        $input_name = new html_inputfield(array('name' => '_name', 'id' => '_name', 'size' => 30,
            'class' => ($this->errors['name'] ? 'error' : '')));

        $out .= sprintf('<label for="%s"><b>%s:</b></label> %s<br /><br />',
            '_name', Q($this->gettext('filtersetname')), $input_name->show($name));

        $out .="\n<fieldset class=\"itemlist\"><legend>" . $this->gettext('filters') . ":</legend>\n";
        $out .= '<input type="radio" id="from_none" name="_from" value="none"'
            .(!$selected || $selected=='none' ? ' checked="checked"' : '').'></input>';
        $out .= sprintf('<label for="%s">%s</label> ', 'from_none', Q($this->gettext('none')));

        // filters set list
        $list   = $this->list_scripts();
        $select = new html_select(array('name' => '_copy', 'id' => '_copy'));

        if (is_array($list)) {
            asort($list, SORT_LOCALE_STRING);

            if (!$copy)
                $copy = $_SESSION['managesieve_current'];

            foreach ($list as $set) {
                $select->add($set, $set);
            }

            $out .= '<br /><input type="radio" id="from_set" name="_from" value="set"'
                .($selected=='set' ? ' checked="checked"' : '').'></input>';
            $out .= sprintf('<label for="%s">%s:</label> ', 'from_set', Q($this->gettext('fromset')));
            $out .= $select->show($copy);
        }

        // script upload box
        $upload = new html_inputfield(array('name' => '_file', 'id' => '_file', 'size' => 30,
            'type' => 'file', 'class' => ($this->errors['file'] ? 'error' : '')));

        $out .= '<br /><input type="radio" id="from_file" name="_from" value="file"'
            .($selected=='file' ? ' checked="checked"' : '').'></input>';
        $out .= sprintf('<label for="%s">%s:</label> ', 'from_file', Q($this->gettext('fromfile')));
        $out .= $upload->show();
        $out .= '</fieldset>';

        $this->rc->output->add_gui_object('sieveform', 'filtersetform');

        if ($this->errors['name'])
            $this->add_tip('_name', $this->errors['name'], true);
        if ($this->errors['file'])
            $this->add_tip('_file', $this->errors['file'], true);

        $this->print_tips();

        return $out;
    }


    function filter_form($attrib)
    {
        if (!$attrib['id'])
            $attrib['id'] = 'rcmfilterform';

        $fid = get_input_value('_fid', RCUBE_INPUT_GPC);
        $scr = isset($this->form) ? $this->form : $this->script[$fid];

        $hiddenfields = new html_hiddenfield(array('name' => '_task', 'value' => $this->rc->task));
        $hiddenfields->add(array('name' => '_action', 'value' => 'plugin.managesieve-save'));
        $hiddenfields->add(array('name' => '_framed', 'value' => ($_POST['_framed'] || $_GET['_framed'] ? 1 : 0)));
        $hiddenfields->add(array('name' => '_fid', 'value' => $fid));

        $out = '<form name="filterform" action="./" method="post">'."\n";
        $out .= $hiddenfields->show();

        // 'any' flag
        if (sizeof($scr['tests']) == 1 && $scr['tests'][0]['test'] == 'true' && !$scr['tests'][0]['not'])
            $any = true;

        // filter name input
        $field_id = '_name';
        $input_name = new html_inputfield(array('name' => '_name', 'id' => $field_id, 'size' => 30,
            'class' => ($this->errors['name'] ? 'error' : '')));

        if ($this->errors['name'])
            $this->add_tip($field_id, $this->errors['name'], true);

        if (isset($scr))
            $input_name = $input_name->show($scr['name']);
        else
            $input_name = $input_name->show();

        $out .= sprintf("\n<label for=\"%s\"><b>%s:</b></label> %s\n",
            $field_id, Q($this->gettext('filtername')), $input_name);

        // filter set selector
        if ($this->rc->task == 'mail') {
            $out .= sprintf("\n&nbsp;<label for=\"%s\"><b>%s:</b></label> %s\n",
                $field_id, Q($this->gettext('filterset')),
                $this->filtersets_list(array('id' => 'sievescriptname'), true));
        }

        $out .= '<br /><br /><fieldset><legend>' . Q($this->gettext('messagesrules')) . "</legend>\n";

        // any, allof, anyof radio buttons
        $field_id = '_allof';
        $input_join = new html_radiobutton(array('name' => '_join', 'id' => $field_id, 'value' => 'allof',
            'onclick' => 'rule_join_radio(\'allof\')', 'class' => 'radio'));

        if (isset($scr) && !$any)
            $input_join = $input_join->show($scr['join'] ? 'allof' : '');
        else
            $input_join = $input_join->show();

        $out .= sprintf("%s<label for=\"%s\">%s</label>&nbsp;\n",
            $input_join, $field_id, Q($this->gettext('filterallof')));

        $field_id = '_anyof';
        $input_join = new html_radiobutton(array('name' => '_join', 'id' => $field_id, 'value' => 'anyof',
            'onclick' => 'rule_join_radio(\'anyof\')', 'class' => 'radio'));

        if (isset($scr) && !$any)
            $input_join = $input_join->show($scr['join'] ? '' : 'anyof');
        else
            $input_join = $input_join->show('anyof'); // default

        $out .= sprintf("%s<label for=\"%s\">%s</label>\n",
            $input_join, $field_id, Q($this->gettext('filteranyof')));

        $field_id = '_any';
        $input_join = new html_radiobutton(array('name' => '_join', 'id' => $field_id, 'value' => 'any',
            'onclick' => 'rule_join_radio(\'any\')', 'class' => 'radio'));

        $input_join = $input_join->show($any ? 'any' : '');

        $out .= sprintf("%s<label for=\"%s\">%s</label>\n",
            $input_join, $field_id, Q($this->gettext('filterany')));

        $rows_num = isset($scr) ? sizeof($scr['tests']) : 1;

        $out .= '<div id="rules"'.($any ? ' style="display: none"' : '').'>';
        for ($x=0; $x<$rows_num; $x++)
            $out .= $this->rule_div($fid, $x);
        $out .= "</div>\n";

        $out .= "</fieldset>\n";

        // actions
        $out .= '<fieldset><legend>' . Q($this->gettext('messagesactions')) . "</legend>\n";

        $rows_num = isset($scr) ? sizeof($scr['actions']) : 1;

        $out .= '<div id="actions">';
        for ($x=0; $x<$rows_num; $x++)
            $out .= $this->action_div($fid, $x);
        $out .= "</div>\n";

        $out .= "</fieldset>\n";

        $this->print_tips();

        if ($scr['disabled']) {
            $this->rc->output->set_env('rule_disabled', true);
        }
        $this->rc->output->add_label(
            'managesieve.ruledeleteconfirm',
            'managesieve.actiondeleteconfirm'
        );
        $this->rc->output->add_gui_object('sieveform', 'filterform');

        return $out;
    }

    function rule_div($fid, $id, $div=true)
    {
        $rule     = isset($this->form) ? $this->form['tests'][$id] : $this->script[$fid]['tests'][$id];
        $rows_num = isset($this->form) ? sizeof($this->form['tests']) : sizeof($this->script[$fid]['tests']);

        // headers select
        $select_header = new html_select(array('name' => "_header[]", 'id' => 'header'.$id,
            'onchange' => 'rule_header_select(' .$id .')'));
        foreach($this->headers as $name => $val)
            $select_header->add(Q($this->gettext($name)), Q($val));
        if (in_array('body', $this->exts))
            $select_header->add(Q($this->gettext('body')), 'body');
        $select_header->add(Q($this->gettext('size')), 'size');
        $select_header->add(Q($this->gettext('...')), '...');

        // TODO: list arguments
        $aout = '';

        if ((isset($rule['test']) && in_array($rule['test'], array('header', 'address', 'envelope')))
            && !is_array($rule['arg1']) && in_array($rule['arg1'], $this->headers)
        ) {
            $aout .= $select_header->show($rule['arg1']);
        }
        else if ((isset($rule['test']) && $rule['test'] == 'exists')
            && !is_array($rule['arg']) && in_array($rule['arg'], $this->headers)
        ) {
            $aout .= $select_header->show($rule['arg']);
        }
        else if (isset($rule['test']) && $rule['test'] == 'size')
            $aout .= $select_header->show('size');
        else if (isset($rule['test']) && $rule['test'] == 'body')
            $aout .= $select_header->show('body');
        else if (isset($rule['test']) && $rule['test'] != 'true')
            $aout .= $select_header->show('...');
        else
            $aout .= $select_header->show();

        if (isset($rule['test']) && in_array($rule['test'], array('header', 'address', 'envelope'))) {
            if (is_array($rule['arg1']))
                $custom = implode(', ', $rule['arg1']);
            else if (!in_array($rule['arg1'], $this->headers))
                $custom = $rule['arg1'];
        }
        else if (isset($rule['test']) && $rule['test'] == 'exists') {
            if (is_array($rule['arg']))
                $custom = implode(', ', $rule['arg']);
            else if (!in_array($rule['arg'], $this->headers))
                $custom = $rule['arg'];
        }

        $tout = '<div id="custom_header' .$id. '" style="display:' .(isset($custom) ? 'inline' : 'none'). '">
            <input type="text" name="_custom_header[]" id="custom_header_i'.$id.'" '
            . $this->error_class($id, 'test', 'header', 'custom_header_i')
            .' value="' .Q($custom). '" size="15" />&nbsp;</div>' . "\n";

        // matching type select (operator)
        $select_op = new html_select(array('name' => "_rule_op[]", 'id' => 'rule_op'.$id,
            'style' => 'display:' .($rule['test']!='size' ? 'inline' : 'none'),
            'class' => 'operator_selector',
            'onchange' => 'rule_op_select('.$id.')'));
        $select_op->add(Q($this->gettext('filtercontains')), 'contains');
        $select_op->add(Q($this->gettext('filternotcontains')), 'notcontains');
        $select_op->add(Q($this->gettext('filteris')), 'is');
        $select_op->add(Q($this->gettext('filterisnot')), 'notis');
        $select_op->add(Q($this->gettext('filterexists')), 'exists');
        $select_op->add(Q($this->gettext('filternotexists')), 'notexists');
        $select_op->add(Q($this->gettext('filtermatches')), 'matches');
        $select_op->add(Q($this->gettext('filternotmatches')), 'notmatches');
        if (in_array('regex', $this->exts)) {
            $select_op->add(Q($this->gettext('filterregex')), 'regex');
            $select_op->add(Q($this->gettext('filternotregex')), 'notregex');
        }
        if (in_array('relational', $this->exts)) {
            $select_op->add(Q($this->gettext('countisgreaterthan')), 'count-gt');
            $select_op->add(Q($this->gettext('countisgreaterthanequal')), 'count-ge');
            $select_op->add(Q($this->gettext('countislessthan')), 'count-lt');
            $select_op->add(Q($this->gettext('countislessthanequal')), 'count-le');
            $select_op->add(Q($this->gettext('countequals')), 'count-eq');
            $select_op->add(Q($this->gettext('countnotequals')), 'count-ne');
            $select_op->add(Q($this->gettext('valueisgreaterthan')), 'value-gt');
            $select_op->add(Q($this->gettext('valueisgreaterthanequal')), 'value-ge');
            $select_op->add(Q($this->gettext('valueislessthan')), 'value-lt');
            $select_op->add(Q($this->gettext('valueislessthanequal')), 'value-le');
            $select_op->add(Q($this->gettext('valueequals')), 'value-eq');
            $select_op->add(Q($this->gettext('valuenotequals')), 'value-ne');
        }

        // target input (TODO: lists)

        if (in_array($rule['test'], array('header', 'address', 'envelope'))) {
            $test   = ($rule['not'] ? 'not' : '').($rule['type'] ? $rule['type'] : 'is');
            $target = $rule['arg2'];
        }
        else if ($rule['test'] == 'body') {
            $test   = ($rule['not'] ? 'not' : '').($rule['type'] ? $rule['type'] : 'is');
            $target = $rule['arg'];
        }
        else if ($rule['test'] == 'size') {
            $test   = '';
            $target = '';
            if (preg_match('/^([0-9]+)(K|M|G)?$/', $rule['arg'], $matches)) {
                $sizetarget = $matches[1];
                $sizeitem = $matches[2];
            }
            else {
                $sizetarget = $rule['arg'];
                $sizeitem = $rule['item'];
            }
        }
        else {
            $test   = ($rule['not'] ? 'not' : '').$rule['test'];
            $target =  '';
        }

        $tout .= $select_op->show($test);
        $tout .= '<input type="text" name="_rule_target[]" id="rule_target' .$id. '"
            value="' .Q($target). '" size="20" ' . $this->error_class($id, 'test', 'target', 'rule_target')
            . ' style="display:' . ($rule['test']!='size' && $rule['test'] != 'exists' ? 'inline' : 'none') . '" />'."\n";

        $select_size_op = new html_select(array('name' => "_rule_size_op[]", 'id' => 'rule_size_op'.$id));
        $select_size_op->add(Q($this->gettext('filterover')), 'over');
        $select_size_op->add(Q($this->gettext('filterunder')), 'under');

        $tout .= '<div id="rule_size' .$id. '" style="display:' . ($rule['test']=='size' ? 'inline' : 'none') .'">';
        $tout .= $select_size_op->show($rule['test']=='size' ? $rule['type'] : '');
        $tout .= '<input type="text" name="_rule_size_target[]" id="rule_size_i'.$id.'" value="'.$sizetarget.'" size="10" ' 
            . $this->error_class($id, 'test', 'sizetarget', 'rule_size_i') .' />
            <input type="radio" name="_rule_size_item['.$id.']" value=""'
                . (!$sizeitem ? ' checked="checked"' : '') .' class="radio" />'.rcube_label('B').'
            <input type="radio" name="_rule_size_item['.$id.']" value="K"'
                . ($sizeitem=='K' ? ' checked="checked"' : '') .' class="radio" />'.rcube_label('KB').'
            <input type="radio" name="_rule_size_item['.$id.']" value="M"'
                . ($sizeitem=='M' ? ' checked="checked"' : '') .' class="radio" />'.rcube_label('MB').'
            <input type="radio" name="_rule_size_item['.$id.']" value="G"'
                . ($sizeitem=='G' ? ' checked="checked"' : '') .' class="radio" />'.rcube_label('GB');
        $tout .= '</div>';

        // Advanced modifiers (address, envelope)
        $select_mod = new html_select(array('name' => "_rule_mod[]", 'id' => 'rule_mod_op'.$id,
            'onchange' => 'rule_mod_select(' .$id .')'));
        $select_mod->add(Q($this->gettext('none')), '');
        $select_mod->add(Q($this->gettext('address')), 'address');
        if (in_array('envelope', $this->exts))
            $select_mod->add(Q($this->gettext('envelope')), 'envelope');

        $select_type = new html_select(array('name' => "_rule_mod_type[]", 'id' => 'rule_mod_type'.$id));
        $select_type->add(Q($this->gettext('allparts')), 'all');
        $select_type->add(Q($this->gettext('domain')), 'domain');
        $select_type->add(Q($this->gettext('localpart')), 'localpart');
        if (in_array('subaddress', $this->exts)) {
            $select_type->add(Q($this->gettext('user')), 'user');
            $select_type->add(Q($this->gettext('detail')), 'detail');
        }

        $need_mod = $rule['test'] != 'size' && $rule['test'] != 'body';
        $mout = '<div id="rule_mod' .$id. '" class="adv" style="display:' . ($need_mod ? 'block' : 'none') .'">';
        $mout .= ' <span>';
        $mout .= Q($this->gettext('modifier')) . ' ';
        $mout .= $select_mod->show($rule['test']);
        $mout .= '</span>';
        $mout .= ' <span id="rule_mod_type' . $id . '"';
        $mout .= ' style="display:' . (in_array($rule['test'], array('address', 'envelope')) ? 'inline' : 'none') .'">';
        $mout .= Q($this->gettext('modtype')) . ' ';
        $mout .= $select_type->show($rule['part']);
        $mout .= '</span>';
        $mout .= '</div>';

        // Advanced modifiers (body transformations)
        $select_mod = new html_select(array('name' => "_rule_trans[]", 'id' => 'rule_trans_op'.$id,
            'onchange' => 'rule_trans_select(' .$id .')'));
        $select_mod->add(Q($this->gettext('text')), 'text');
        $select_mod->add(Q($this->gettext('undecoded')), 'raw');
        $select_mod->add(Q($this->gettext('contenttype')), 'content');

        $mout .= '<div id="rule_trans' .$id. '" class="adv" style="display:' . ($rule['test'] == 'body' ? 'block' : 'none') .'">';
        $mout .= ' <span>';
        $mout .= Q($this->gettext('modifier')) . ' ';
        $mout .= $select_mod->show($rule['part']);
        $mout .= '<input type="text" name="_rule_trans_type[]" id="rule_trans_type'.$id
            . '" value="'.(is_array($rule['content']) ? implode(',', $rule['content']) : $rule['content'])
            .'" size="20" style="display:' . ($rule['part'] == 'content' ? 'inline' : 'none') .'"'
            . $this->error_class($id, 'test', 'part', 'rule_trans_type') .' />';
        $mout .= '</span>';
        $mout .= '</div>';

        // Advanced modifiers (body transformations)
        $select_comp = new html_select(array('name' => "_rule_comp[]", 'id' => 'rule_comp_op'.$id));
        $select_comp->add(Q($this->gettext('default')), '');
        $select_comp->add(Q($this->gettext('octet')), 'i;octet');
        $select_comp->add(Q($this->gettext('asciicasemap')), 'i;ascii-casemap');
        if (in_array('comparator-i;ascii-numeric', $this->exts)) {
            $select_comp->add(Q($this->gettext('asciinumeric')), 'i;ascii-numeric');
        }

        $mout .= '<div id="rule_comp' .$id. '" class="adv" style="display:' . ($rule['test'] != 'size' ? 'block' : 'none') .'">';
        $mout .= ' <span>';
        $mout .= Q($this->gettext('comparator')) . ' ';
        $mout .= $select_comp->show($rule['comparator']);
        $mout .= '</span>';
        $mout .= '</div>';

        // Build output table
        $out = $div ? '<div class="rulerow" id="rulerow' .$id .'">'."\n" : '';
        $out .= '<table><tr>';
        $out .= '<td class="advbutton">';
        $out .= '<a href="#" id="ruleadv' . $id .'" title="'. Q($this->gettext('advancedopts')). '"
            onclick="rule_adv_switch(' . $id .', this)" class="show">&nbsp;&nbsp;</a>';
        $out .= '</td>';
        $out .= '<td class="rowactions">' . $aout . '</td>';
        $out .= '<td class="rowtargets">' . $tout . "\n";
        $out .= '<div id="rule_advanced' .$id. '" style="display:none">' . $mout . '</div>';
        $out .= '</td>';

        // add/del buttons
        $out .= '<td class="rowbuttons">';
        $out .= '<a href="#" id="ruleadd' . $id .'" title="'. Q($this->gettext('add')). '"
            onclick="rcmail.managesieve_ruleadd(' . $id .')" class="button add"></a>';
        $out .= '<a href="#" id="ruledel' . $id .'" title="'. Q($this->gettext('del')). '"
            onclick="rcmail.managesieve_ruledel(' . $id .')" class="button del' . ($rows_num<2 ? ' disabled' : '') .'"></a>';
        $out .= '</td>';
        $out .= '</tr></table>';

        $out .= $div ? "</div>\n" : '';

        return $out;
    }

    function action_div($fid, $id, $div=true)
    {
        $action   = isset($this->form) ? $this->form['actions'][$id] : $this->script[$fid]['actions'][$id];
        $rows_num = isset($this->form) ? sizeof($this->form['actions']) : sizeof($this->script[$fid]['actions']);

        $out = $div ? '<div class="actionrow" id="actionrow' .$id .'">'."\n" : '';

        $out .= '<table><tr><td class="rowactions">';

        // action select
        $select_action = new html_select(array('name' => "_action_type[$id]", 'id' => 'action_type'.$id,
            'onchange' => 'action_type_select(' .$id .')'));
        if (in_array('fileinto', $this->exts))
            $select_action->add(Q($this->gettext('messagemoveto')), 'fileinto');
        if (in_array('fileinto', $this->exts) && in_array('copy', $this->exts))
            $select_action->add(Q($this->gettext('messagecopyto')), 'fileinto_copy');
        $select_action->add(Q($this->gettext('messageredirect')), 'redirect');
        if (in_array('copy', $this->exts))
            $select_action->add(Q($this->gettext('messagesendcopy')), 'redirect_copy');
        if (in_array('reject', $this->exts))
            $select_action->add(Q($this->gettext('messagediscard')), 'reject');
        else if (in_array('ereject', $this->exts))
            $select_action->add(Q($this->gettext('messagediscard')), 'ereject');
        if (in_array('vacation', $this->exts))
            $select_action->add(Q($this->gettext('messagereply')), 'vacation');
        $select_action->add(Q($this->gettext('messagedelete')), 'discard');
        if (in_array('imapflags', $this->exts) || in_array('imap4flags', $this->exts)) {
            $select_action->add(Q($this->gettext('setflags')), 'setflag');
            $select_action->add(Q($this->gettext('addflags')), 'addflag');
            $select_action->add(Q($this->gettext('removeflags')), 'removeflag');
        }
        if (in_array('variables', $this->exts)) {
            $select_action->add(Q($this->gettext('setvariable')), 'set');
        }
        if (in_array('enotify', $this->exts) || in_array('notify', $this->exts)) {
            $select_action->add(Q($this->gettext('notify')), 'notify');
        }
        $select_action->add(Q($this->gettext('rulestop')), 'stop');

        $select_type = $action['type'];
        if (in_array($action['type'], array('fileinto', 'redirect')) && $action['copy']) {
            $select_type .= '_copy';
        }

        $out .= $select_action->show($select_type);
        $out .= '</td>';

        // actions target inputs
        $out .= '<td class="rowtargets">';
        // shared targets
        $out .= '<input type="text" name="_action_target['.$id.']" id="action_target' .$id. '" '
            .'value="' .($action['type']=='redirect' ? Q($action['target'], 'strict', false) : ''). '" size="35" '
            .'style="display:' .($action['type']=='redirect' ? 'inline' : 'none') .'" '
            . $this->error_class($id, 'action', 'target', 'action_target') .' />';
        $out .= '<textarea name="_action_target_area['.$id.']" id="action_target_area' .$id. '" '
            .'rows="3" cols="35" '. $this->error_class($id, 'action', 'targetarea', 'action_target_area')
            .'style="display:' .(in_array($action['type'], array('reject', 'ereject')) ? 'inline' : 'none') .'">'
            . (in_array($action['type'], array('reject', 'ereject')) ? Q($action['target'], 'strict', false) : '')
            . "</textarea>\n";

        // vacation
        $out .= '<div id="action_vacation' .$id.'" style="display:' .($action['type']=='vacation' ? 'inline' : 'none') .'">';
        $out .= '<span class="label">'. Q($this->gettext('vacationreason')) .'</span><br />'
            .'<textarea name="_action_reason['.$id.']" id="action_reason' .$id. '" '
            .'rows="3" cols="35" '. $this->error_class($id, 'action', 'reason', 'action_reason') . '>'
            . Q($action['reason'], 'strict', false) . "</textarea>\n";
        $out .= '<br /><span class="label">' .Q($this->gettext('vacationsubject')) . '</span><br />'
            .'<input type="text" name="_action_subject['.$id.']" id="action_subject'.$id.'" '
            .'value="' . (is_array($action['subject']) ? Q(implode(', ', $action['subject']), 'strict', false) : $action['subject']) . '" size="35" '
            . $this->error_class($id, 'action', 'subject', 'action_subject') .' />';
        $out .= '<br /><span class="label">' .Q($this->gettext('vacationaddresses')) . '</span><br />'
            .'<input type="text" name="_action_addresses['.$id.']" id="action_addr'.$id.'" '
            .'value="' . (is_array($action['addresses']) ? Q(implode(', ', $action['addresses']), 'strict', false) : $action['addresses']) . '" size="35" '
            . $this->error_class($id, 'action', 'addresses', 'action_addr') .' />';
        $out .= '<br /><span class="label">' . Q($this->gettext('vacationdays')) . '</span><br />'
            .'<input type="text" name="_action_days['.$id.']" id="action_days'.$id.'" '
            .'value="' .Q($action['days'], 'strict', false) . '" size="2" '
            . $this->error_class($id, 'action', 'days', 'action_days') .' />';
        $out .= '</div>';

        // flags
        $flags = array(
            'read'      => '\\Seen',
            'answered'  => '\\Answered',
            'flagged'   => '\\Flagged',
            'deleted'   => '\\Deleted',
            'draft'     => '\\Draft',
        );
        $flags_target = (array)$action['target'];

        $out .= '<div id="action_flags' .$id.'" style="display:' 
            . (preg_match('/^(set|add|remove)flag$/', $action['type']) ? 'inline' : 'none') . '"'
            . $this->error_class($id, 'action', 'flags', 'action_flags') . '>';
        foreach ($flags as $fidx => $flag) {
            $out .= '<input type="checkbox" name="_action_flags[' .$id .'][]" value="' . $flag . '"'
                . (in_array_nocase($flag, $flags_target) ? 'checked="checked"' : '') . ' />'
                . Q($this->gettext('flag'.$fidx)) .'<br>';
        }
        $out .= '</div>';

        // set variable
        $set_modifiers = array(
            'lower',
            'upper',
            'lowerfirst',
            'upperfirst',
            'quotewildcard',
            'length'
        );

        $out .= '<div id="action_set' .$id.'" style="display:' .($action['type']=='set' ? 'inline' : 'none') .'">';
        $out .= '<span class="label">' .Q($this->gettext('setvarname')) . '</span><br />'
            .'<input type="text" name="_action_varname['.$id.']" id="action_varname'.$id.'" '
            .'value="' . Q($action['name']) . '" size="35" '
            . $this->error_class($id, 'action', 'name', 'action_varname') .' />';
        $out .= '<br /><span class="label">' .Q($this->gettext('setvarvalue')) . '</span><br />'
            .'<input type="text" name="_action_varvalue['.$id.']" id="action_varvalue'.$id.'" '
            .'value="' . Q($action['value']) . '" size="35" '
            . $this->error_class($id, 'action', 'value', 'action_varvalue') .' />';
        $out .= '<br /><span class="label">' .Q($this->gettext('setvarmodifiers')) . '</span><br />';
        foreach ($set_modifiers as $j => $s_m) {
            $s_m_id = 'action_varmods' . $id . $s_m;
            $out .= sprintf('<input type="checkbox" name="_action_varmods[%s][]" value="%s" id="%s"%s />%s<br>',
                $id, $s_m, $s_m_id,
                (array_key_exists($s_m, (array)$action) && $action[$s_m] ? ' checked="checked"' : ''),
                Q($this->gettext('var' . $s_m)));
        }
        $out .= '</div>';

        // notify
        // skip :options tag - not used by the mailto method
        $out .= '<div id="action_notify' .$id.'" style="display:' .($action['type']=='notify' ? 'inline' : 'none') .'">';
        $out .= '<span class="label">' .Q($this->gettext('notifyaddress')) . '</span><br />'
            .'<input type="text" name="_action_notifyaddress['.$id.']" id="action_notifyaddress'.$id.'" '
            .'value="' . Q($action['address']) . '" size="35" '
            . $this->error_class($id, 'action', 'address', 'action_notifyaddress') .' />';
        $out .= '<br /><span class="label">'. Q($this->gettext('notifybody')) .'</span><br />'
            .'<textarea name="_action_notifybody['.$id.']" id="action_notifybody' .$id. '" '
            .'rows="3" cols="35" '. $this->error_class($id, 'action', 'method', 'action_notifybody') . '>'
            . Q($action['body'], 'strict', false) . "</textarea>\n";
        $out .= '<br /><span class="label">' .Q($this->gettext('notifysubject')) . '</span><br />'
            .'<input type="text" name="_action_notifymessage['.$id.']" id="action_notifymessage'.$id.'" '
            .'value="' . Q($action['message']) . '" size="35" '
            . $this->error_class($id, 'action', 'message', 'action_notifymessage') .' />';
        $out .= '<br /><span class="label">' .Q($this->gettext('notifyfrom')) . '</span><br />'
            .'<input type="text" name="_action_notifyfrom['.$id.']" id="action_notifyfrom'.$id.'" '
            .'value="' . Q($action['from']) . '" size="35" '
            . $this->error_class($id, 'action', 'from', 'action_notifyfrom') .' />';
        $importance_options = array(
            3 => 'notifyimportancelow',
            2 => 'notifyimportancenormal',
            1 => 'notifyimportancehigh'
        );
        $select_importance = new html_select(array(
            'name' => '_action_notifyimportance[' . $id . ']',
            'id' => '_action_notifyimportance' . $id,
            'class' => $this->error_class($id, 'action', 'importance', 'action_notifyimportance')));
        foreach ($importance_options as $io_v => $io_n) {
            $select_importance->add(Q($this->gettext($io_n)), $io_v);
        }
        $out .= '<br /><span class="label">' . Q($this->gettext('notifyimportance')) . '</span><br />';
        $out .= $select_importance->show($action['importance'] ? $action['importance'] : 2);
        $out .= '</div>';

        // mailbox select
        if ($action['type'] == 'fileinto')
            $mailbox = $this->mod_mailbox($action['target'], 'out');
        else
            $mailbox = '';

        $select = rcmail_mailbox_select(array(
            'realnames' => false,
            'maxlength' => 100,
            'id' => 'action_mailbox' . $id,
            'name' => "_action_mailbox[$id]",
            'style' => 'display:'.(!isset($action) || $action['type']=='fileinto' ? 'inline' : 'none')
        ));
        $out .= $select->show($mailbox);
        $out .= '</td>';

        // add/del buttons
        $out .= '<td class="rowbuttons">';
        $out .= '<a href="#" id="actionadd' . $id .'" title="'. Q($this->gettext('add')). '"
            onclick="rcmail.managesieve_actionadd(' . $id .')" class="button add"></a>';
        $out .= '<a href="#" id="actiondel' . $id .'" title="'. Q($this->gettext('del')). '"
            onclick="rcmail.managesieve_actiondel(' . $id .')" class="button del' . ($rows_num<2 ? ' disabled' : '') .'"></a>';
        $out .= '</td>';

        $out .= '</tr></table>';

        $out .= $div ? "</div>\n" : '';

        return $out;
    }

    private function genid()
    {
        return preg_replace('/[^0-9]/', '', microtime(true));
    }

    private function strip_value($str, $allow_html = false, $trim = true)
    {
        if (!$allow_html) {
            $str = strip_tags($str);
        }

        return $trim ? trim($str) : $str;
    }

    private function error_class($id, $type, $target, $elem_prefix='')
    {
        // TODO: tooltips
        if (($type == 'test' && ($str = $this->errors['tests'][$id][$target])) ||
            ($type == 'action' && ($str = $this->errors['actions'][$id][$target]))
        ) {
            $this->add_tip($elem_prefix.$id, $str, true);
            return ' class="error"';
        }

        return '';
    }

    private function add_tip($id, $str, $error=false)
    {
        if ($error)
            $str = html::span('sieve error', $str);

        $this->tips[] = array($id, $str);
    }

    private function print_tips()
    {
        if (empty($this->tips))
            return;

        $script = JS_OBJECT_NAME.'.managesieve_tip_register('.json_encode($this->tips).');';
        $this->rc->output->add_script($script, 'foot');
    }

    /**
     * Converts mailbox name from/to UTF7-IMAP from/to internal Sieve encoding
     * with delimiter replacement.
     *
     * @param string $mailbox Mailbox name
     * @param string $mode    Conversion direction ('in'|'out')
     *
     * @return string Mailbox name
     */
    private function mod_mailbox($mailbox, $mode = 'out')
    {
        $delimiter         = $_SESSION['imap_delimiter'];
        $replace_delimiter = $this->rc->config->get('managesieve_replace_delimiter');
        $mbox_encoding     = $this->rc->config->get('managesieve_mbox_encoding', 'UTF7-IMAP');

        if ($mode == 'out') {
            $mailbox = rcube_charset_convert($mailbox, $mbox_encoding, 'UTF7-IMAP');
            if ($replace_delimiter && $replace_delimiter != $delimiter)
                $mailbox = str_replace($replace_delimiter, $delimiter, $mailbox);
        }
        else {
            $mailbox = rcube_charset_convert($mailbox, 'UTF7-IMAP', $mbox_encoding);
            if ($replace_delimiter && $replace_delimiter != $delimiter)
                $mailbox = str_replace($delimiter, $replace_delimiter, $mailbox);
        }

        return $mailbox;
    }

    /**
     * List sieve scripts
     *
     * @return array Scripts list
     */
    public function list_scripts()
    {
        if ($this->list !== null) {
            return $this->list;
        }

        $this->list = $this->sieve->get_scripts();

        // Handle active script(s) and list of scripts according to Kolab's KEP:14
        if ($this->rc->config->get('managesieve_kolab_master')) {

            // Skip protected names
            foreach ((array)$this->list as $idx => $name) {
                $_name = strtoupper($name);
                if ($_name == 'MASTER')
                    $master_script = $name;
                else if ($_name == 'MANAGEMENT')
                    $management_script = $name;
                else if($_name == 'USER')
                    $user_script = $name;
                else
                    continue;

                unset($this->list[$idx]);
            }

            // get active script(s), read USER script
            if ($user_script) {
                $extension = $this->rc->config->get('managesieve_filename_extension', '.sieve');
                $filename_regex = '/'.preg_quote($extension, '/').'$/';
                $_SESSION['managesieve_user_script'] = $user_script;

                $this->sieve->load($user_script);

                foreach ($this->sieve->script->as_array() as $rules) {
                    foreach ($rules['actions'] as $action) {
                        if ($action['type'] == 'include' && empty($action['global'])) {
                            $name = preg_replace($filename_regex, '', $action['target']);
                            $this->active[] = $name;
                        }
                    }
                }
            }
            // create USER script if it doesn't exist
            else {
                $content = "# USER Management Script\n"
                    ."#\n"
                    ."# This script includes the various active sieve scripts\n"
                    ."# it is AUTOMATICALLY GENERATED. DO NOT EDIT MANUALLY!\n"
                    ."#\n"
                    ."# For more information, see http://wiki.kolab.org/KEP:14#USER\n"
                    ."#\n";
                if ($this->sieve->save_script('USER', $content)) {
                    $_SESSION['managesieve_user_script'] = 'USER';
                    if (empty($this->master_file))
                        $this->sieve->activate('USER');
                }
            }
        }
        else if (!empty($this->list)) {
            // Get active script name
            if ($active = $this->sieve->get_active()) {
                $this->active = array($active);
            }

            // Hide scripts from config
            $exceptions = $this->rc->config->get('managesieve_filename_exceptions');
            if (!empty($exceptions)) {
                $this->list = array_diff($this->list, (array)$exceptions);
            }
        }

        return $this->list;
    }

    /**
     * Removes sieve script
     *
     * @param string $name Script name
     *
     * @return bool True on success, False on failure
     */
    public function remove_script($name)
    {
        $result = $this->sieve->remove($name);

        // Kolab's KEP:14
        if ($result && $this->rc->config->get('managesieve_kolab_master')) {
            $this->deactivate_script($name);
        }

        return $result;
    }

    /**
     * Activates sieve script
     *
     * @param string $name Script name
     *
     * @return bool True on success, False on failure
     */
    public function activate_script($name)
    {
        // Kolab's KEP:14
        if ($this->rc->config->get('managesieve_kolab_master')) {
            $extension   = $this->rc->config->get('managesieve_filename_extension', '.sieve');
            $user_script = $_SESSION['managesieve_user_script'];

            // if the script is not active...
            if ($user_script && ($key = array_search($name, $this->active)) === false) {
                // ...rewrite USER file adding appropriate include command
                if ($this->sieve->load($user_script)) {
                    $script = $this->sieve->script->as_array();
                    $list   = array();
                    $regexp = '/' . preg_quote($extension, '/') . '$/';

                    // Create new include entry
                    $rule = array(
                        'actions' => array(
                            0 => array(
                                'target'   => $name.$extension,
                                'type'     => 'include',
                                'personal' => true,
                    )));

                    // get all active scripts for sorting
                    foreach ($script as $rid => $rules) {
                        foreach ($rules['actions'] as $aid => $action) {
                            if ($action['type'] == 'include' && empty($action['global'])) {
                                $target = $extension ? preg_replace($regexp, '', $action['target']) : $action['target'];
                                $list[] = $target;
                            }
                        }
                    }
                    $list[] = $name;

                    // Sort and find current script position
                    asort($list, SORT_LOCALE_STRING);
                    $list = array_values($list);
                    $index = array_search($name, $list);

                    // add rule at the end of the script
                    if ($index === false || $index == count($list)-1) {
                        $this->sieve->script->add_rule($rule);
                    }
                    // add rule at index position
                    else {
                        $script2 = array();
                        foreach ($script as $rid => $rules) {
                            if ($rid == $index) {
                                $script2[] = $rule;
                            }
                            $script2[] = $rules;
                        }
                        $this->sieve->script->content = $script2;
                    }

                    $result = $this->sieve->save();
                    if ($result) {
                        $this->active[] = $name;
                    }
                }
            }
        }
        else {
            $result = $this->sieve->activate($name);
            if ($result)
                $this->active = array($name);
        }

        return $result;
    }

    /**
     * Deactivates sieve script
     *
     * @param string $name Script name
     *
     * @return bool True on success, False on failure
     */
    public function deactivate_script($name)
    {
        // Kolab's KEP:14
        if ($this->rc->config->get('managesieve_kolab_master')) {
            $extension   = $this->rc->config->get('managesieve_filename_extension', '.sieve');
            $user_script = $_SESSION['managesieve_user_script'];

            // if the script is active...
            if ($user_script && ($key = array_search($name, $this->active)) !== false) {
                // ...rewrite USER file removing appropriate include command
                if ($this->sieve->load($user_script)) {
                    $script = $this->sieve->script->as_array();
                    $name   = $name.$extension;

                    foreach ($script as $rid => $rules) {
                        foreach ($rules['actions'] as $aid => $action) {
                            if ($action['type'] == 'include' && empty($action['global'])
                                && $action['target'] == $name
                            ) {
                                break 2;
                            }
                        }
                    }

                    // Entry found
                    if ($rid < count($script)) {
                        $this->sieve->script->delete_rule($rid);
                        $result = $this->sieve->save();
                        if ($result) {
                            unset($this->active[$key]);
                        }
                    }
                }
            }
        }
        else {
            $result = $this->sieve->deactivate();
            if ($result)
                $this->active = array();
        }

        return $result;
    }

    /**
     * Saves current script (adding some variables)
     */
    public function save_script($name = null)
    {
        // Kolab's KEP:14
        if ($this->rc->config->get('managesieve_kolab_master')) {
            $this->sieve->script->set_var('EDITOR', self::PROGNAME);
            $this->sieve->script->set_var('EDITOR_VERSION', self::VERSION);
        }

        return $this->sieve->save($name);
    }

    /**
     * Returns list of rules from the current script
     *
     * @return array List of rules
     */
    public function list_rules()
    {
        $result = array();
        $i      = 1;

        foreach ($this->script as $idx => $filter) {
            if ($filter['type'] != 'if') {
                continue;
            }
            $fname = $filter['name'] ? $filter['name'] : "#$i";
            $result[] = array(
                'id'    => $idx,
                'name'  => Q($fname),
                'class' => $filter['disabled'] ? 'disabled' : '',
            );
            $i++;
        }

        return $result;
    }
}
