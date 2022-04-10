<?php

/**
 * Managesieve (Sieve Filters) Engine
 *
 * Engine part of Managesieve plugin implementing UI and backend access.
 *
 * Copyright (C) The Roundcube Dev Team
 * Copyright (C) Kolab Systems AG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_sieve_engine
{
    protected $rc;
    protected $sieve;
    protected $plugin;
    protected $errors;
    protected $form;
    protected $list;
    protected $tips    = [];
    protected $script  = [];
    protected $exts    = [];
    protected $active  = [];
    protected $headers = [];
    protected $disabled_actions = [];
    protected $addr_headers = [
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
    ];
    protected $notify_methods = [
        'mailto',
        // 'sms',
        // 'tel',
    ];
    protected $notify_importance_options = [
        3 => 'notifyimportancelow',
        2 => 'notifyimportancenormal',
        1 => 'notifyimportancehigh'
    ];

    const VERSION  = '9.4';
    const PROGNAME = 'Roundcube (Managesieve)';
    const PORT     = 4190;


    /**
     * Class constructor
     */
    function __construct($plugin)
    {
        $this->rc      = rcube::get_instance();
        $this->plugin  = $plugin;
        $this->headers = $this->get_default_headers();
    }

    /**
     * Loads configuration, initializes plugin (including sieve connection)
     */
    function start($mode = null)
    {
        // register UI objects
        $this->rc->output->add_handlers([
                'filterslist'      => [$this, 'filters_list'],
                'filtersetslist'   => [$this, 'filtersets_list'],
                'filterform'       => [$this, 'filter_form'],
                'filtersetform'    => [$this, 'filterset_form'],
                'filterseteditraw' => [$this, 'filterset_editraw'],
        ]);

        $this->disabled_actions = (array) $this->rc->config->get('managesieve_disabled_actions');

        // connect to managesieve server
        $error = $this->connect($_SESSION['username'], $this->rc->decrypt($_SESSION['password']));

        $script_name = null;

        // load current/active script
        if (!$error) {
            // Get list of scripts
            $list = $this->list_scripts();

            // reset current script when entering filters UI (#1489412)
            if ($this->rc->action == 'plugin.managesieve') {
                $this->rc->session->remove('managesieve_current');
            }

            if ($mode != 'vacation' && $mode != 'forward') {
                if (!empty($_GET['_set']) || !empty($_POST['_set'])) {
                    $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_GPC, true);
                }
                else if (!empty($_SESSION['managesieve_current'])) {
                    $script_name = $_SESSION['managesieve_current'];
                }
            }

            $error = $this->load_script($script_name);
        }

        // finally set script objects
        if ($error) {
            switch ($error) {
                case rcube_sieve::ERROR_CONNECTION:
                case rcube_sieve::ERROR_LOGIN:
                    $this->rc->output->show_message('managesieve.filterconnerror', 'error');
                    break;

                default:
                    $this->rc->output->show_message('managesieve.filterunknownerror', 'error');
                    break;
            }

            // reload interface in case of possible error when specified script wasn't found (#1489412)
            if ($script_name !== null && !empty($list) && !in_array($script_name, $list)) {
                $this->rc->output->command('reload', 500);
            }

            // to disable 'Add filter' button set env variable
            $this->rc->output->set_env('filterconnerror', true);
            $this->script = [];
        }
        else {
            $this->exts = $this->sieve->get_extensions();
            $this->init_script();
            $this->rc->output->set_env('currentset', $this->sieve->current);
            $_SESSION['managesieve_current'] = $this->sieve->current;
        }

        $this->rc->output->set_env('raw_sieve_editor', $this->rc->config->get('managesieve_raw_editor', true));
        $this->rc->output->set_env('managesieve_disabled_actions', $this->disabled_actions);
        $this->rc->output->set_env('managesieve_no_set_list', in_array('list_sets', $this->disabled_actions));

        return $error;
    }

    /**
     * Connect to configured managesieve server
     *
     * @param string $username User login
     * @param string $password User password
     *
     * @return int Connection status: 0 on success, >0 on failure
     */
    public function connect($username, $password)
    {
        $host = $this->rc->config->get('managesieve_host', 'localhost');
        $host = rcube_utils::parse_host($host);

        $plugin = $this->rc->plugins->exec_hook('managesieve_connect', [
                'user'           => $username,
                'password'       => $password,
                'host'           => $host,
                'auth_type'      => $this->rc->config->get('managesieve_auth_type'),
                'disabled'       => $this->rc->config->get('managesieve_disabled_extensions'),
                'debug'          => $this->rc->config->get('managesieve_debug', false),
                'auth_cid'       => $this->rc->config->get('managesieve_auth_cid'),
                'auth_pw'        => $this->rc->config->get('managesieve_auth_pw'),
                'socket_options' => $this->rc->config->get('managesieve_conn_options'),
                'gssapi_context' => null,
                'gssapi_cn'      => null,
        ]);

        list($host, $scheme, $port) = rcube_utils::parse_host_uri($plugin['host']);

        $tls = $scheme === 'tls';

        if (empty($port)) {
            $port = getservbyname('sieve', 'tcp') ?: self::PORT;
        }

        $host = rcube_utils::idn_to_ascii($host);

        // Handle per-host socket options
        rcube_utils::parse_socket_options($plugin['socket_options'], $host);

        // try to connect to managesieve server and to fetch the script
        $this->sieve = new rcube_sieve(
            $plugin['user'],
            $plugin['password'],
            $host,
            $port,
            $plugin['auth_type'],
            $tls,
            $plugin['disabled'],
            $plugin['debug'],
            $plugin['auth_cid'],
            $plugin['auth_pw'],
            $plugin['socket_options'],
            $plugin['gssapi_context'],
            $plugin['gssapi_cn']
        );

        $error = $this->sieve->error();

        if ($error) {
            rcube::raise_error([
                    'code'    => 403,
                    'file'    => __FILE__,
                    'line'    => __LINE__,
                    'message' => "Unable to connect to managesieve on $host:$port"
                ], true, false
            );
        }

        return $error;
    }

    /**
     * Load specified (or active) script
     *
     * @param string $script_name Optional script name
     *
     * @return int Connection status: 0 on success, >0 on failure
     */
    protected function load_script($script_name = null)
    {
        // Get list of scripts
        $list = $this->list_scripts();

        if ($script_name === null || $script_name === '') {
            // get (first) active script
            if (!empty($this->active)) {
               $script_name = $this->active[0];
            }
            else if ($list) {
                $script_name = $list[0];
            }
            else {
                // if script does not exist create one with default content
                $this->create_default_script();
            }
        }

        if ($script_name) {
            $this->sieve->load($script_name);
        }

        return $this->sieve->error();
    }

    /**
     * User interface actions handler
     */
    function actions()
    {
        $error = $this->start();

        // Handle user requests
        if ($action = rcube_utils::get_input_string('_act', rcube_utils::INPUT_GPC)) {
            $fid = (int) rcube_utils::get_input_value('_fid', rcube_utils::INPUT_POST);

            if ($action == 'delete' && !$error) {
                if (!in_array('delete_filter', $this->disabled_actions)) {
                    if (isset($this->script[$fid])) {
                        $result = false;
                        if ($this->sieve->script->delete_rule($fid)) {
                            $result = $this->save_script();
                        }

                        if ($result === true) {
                            $this->rc->output->show_message('managesieve.filterdeleted', 'confirmation');
                            $this->rc->output->command('managesieve_updatelist', 'del', ['id' => $fid]);
                        }
                        else {
                            $this->rc->output->show_message('managesieve.filterdeleteerror', 'error');
                        }
                    }
                }
                else {
                    $this->rc->output->show_message('managesieve.disabledaction', 'error');
                }
            }
            else if ($action == 'move' && !$error) {
                if (isset($this->script[$fid])) {
                    $to   = (int) rcube_utils::get_input_value('_to', rcube_utils::INPUT_POST);
                    $rule = $this->script[$fid];

                    // remove rule
                    unset($this->script[$fid]);
                    $this->script = array_values($this->script);

                    // add at target position
                    if ($to >= count($this->script)) {
                        $this->script[] = $rule;
                    }
                    else {
                        $script = [];
                        foreach ($this->script as $idx => $r) {
                            if ($idx == $to) {
                                $script[] = $rule;
                            }
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
                            ['list' => $result, 'clear' => true, 'set' => $to]);
                    }
                    else {
                        $this->rc->output->show_message('managesieve.moveerror', 'error');
                    }
                }
            }
            else if ($action == 'act' && !$error) {
                if (isset($this->script[$fid])) {
                    $rule     = $this->script[$fid];
                    $disabled = !empty($rule['disabled']);
                    $rule['disabled'] = !$disabled;
                    $result = $this->sieve->script->update_rule($fid, $rule);

                    if ($result !== false) {
                        $result = $this->save_script();
                    }

                    if ($result === true) {
                        if ($rule['disabled']) {
                            $this->rc->output->show_message('managesieve.deactivated', 'confirmation');
                        }
                        else {
                            $this->rc->output->show_message('managesieve.activated', 'confirmation');
                        }
                        $this->rc->output->command('managesieve_updatelist', 'update',
                            ['id' => $fid, 'disabled' => $rule['disabled']]);
                    }
                    else {
                        if ($rule['disabled']) {
                            $this->rc->output->show_message('managesieve.deactivateerror', 'error');
                        }
                        else {
                            $this->rc->output->show_message('managesieve.activateerror', 'error');
                        }
                    }
                }
            }
            else if ($action == 'setact' && !$error) {
                if (!in_array('enable_disable_set', $this->disabled_actions)) {
                    $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_POST, true);
                    $result      = $this->activate_script($script_name);
                    $kep14       = $this->rc->config->get('managesieve_kolab_master');

                    if ($result === true) {
                        $this->rc->output->set_env('active_sets', $this->active);
                        $this->rc->output->show_message('managesieve.setactivated', 'confirmation');
                        $this->rc->output->command('managesieve_updatelist', 'setact',
                            ['name' => $script_name, 'active' => true, 'all' => !$kep14]);
                    }
                    else {
                        $this->rc->output->show_message('managesieve.setactivateerror', 'error');
                    }
                }
                else {
                    $this->rc->output->show_message('managesieve.disabledaction', 'error');
                }
            }
            else if ($action == 'deact' && !$error) {
                if (!in_array('enable_disable_set', $this->disabled_actions)) {
                    $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_POST, true);
                    $result      = $this->deactivate_script($script_name);

                    if ($result === true) {
                        $this->rc->output->set_env('active_sets', $this->active);
                        $this->rc->output->show_message('managesieve.setdeactivated', 'confirmation');
                        $this->rc->output->command('managesieve_updatelist', 'setact',
                            ['name' => $script_name, 'active' => false]);
                    }
                    else {
                        $this->rc->output->show_message('managesieve.setdeactivateerror', 'error');
                    }
                }
                else {
                    $this->rc->output->show_message('managesieve.disabledaction', 'error');
                }
            }
            else if ($action == 'setdel' && !$error) {
                if (!in_array('delete_set', $this->disabled_actions)) {
                    $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_POST, true);
                    $result      = $this->remove_script($script_name);

                    if ($result === true) {
                        $this->rc->output->show_message('managesieve.setdeleted', 'confirmation');
                        $this->rc->output->command('managesieve_updatelist', 'setdel', ['name' => $script_name]);
                        $this->rc->session->remove('managesieve_current');
                    }
                    else {
                        $this->rc->output->show_message('managesieve.setdeleteerror', 'error');
                    }
                }
                else {
                    $this->rc->output->show_message('managesieve.disabledaction', 'error');
                }
            }
            else if ($action == 'setget') {
                if (!in_array('download_set', $this->disabled_actions)) {
                    $this->rc->request_security_check(rcube_utils::INPUT_GET);

                    $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_GPC, true);
                    $script      = $this->sieve->get_script($script_name);

                    if ($script !== false) {
                        $this->rc->output->download_headers($script_name . '.txt', ['length' => strlen($script)]);
                        echo $script;
                    }

                    exit;
                }
            }
            else if ($action == 'list') {
                $result = $this->list_rules();

                $this->rc->output->command('managesieve_updatelist', 'list', ['list' => $result]);
            }
            else if ($action == 'ruleadd') {
                $rid = rcube_utils::get_input_string('_rid', rcube_utils::INPUT_POST);
                $id  = $this->genid();
                $content = $this->rule_div($fid, $id, false, !empty($_SESSION['managesieve-compact-form']));

                $this->rc->output->command('managesieve_rulefill', $content, $id, $rid);
            }
            else if ($action == 'actionadd') {
                $aid = rcube_utils::get_input_string('_aid', rcube_utils::INPUT_POST);
                $id  = $this->genid();
                $content = $this->action_div($fid, $id, false);

                $this->rc->output->command('managesieve_actionfill', $content, $id, $aid);
            }
            else if ($action == 'addresses') {
                $aid = rcube_utils::get_input_string('_aid', rcube_utils::INPUT_POST);

                $this->rc->output->command('managesieve_vacation_addresses_update', $aid, $this->user_emails());
            }

            $this->rc->output->send();
        }
        else if ($this->rc->task == 'mail') {
            // Initialize the form
            $rules = rcube_utils::get_input_value('r', rcube_utils::INPUT_GET);
            if (!empty($rules)) {
                $tests = [];
                foreach ($rules as $rule) {
                    list($header, $value) = explode(':', $rule, 2);
                    $tests[] = [
                        'type' => 'contains',
                        'test' => 'header',
                        'arg1' => $header,
                        'arg2' => $value,
                    ];
                }

                $this->form = [
                    'join'  => count($tests) > 1 ? 'allof' : 'anyof',
                    'name'  => '',
                    'tests' => $tests,
                    'actions' => [
                        ['type' => 'fileinto'],
                        ['type' => 'stop'],
                    ],
                ];
            }
        }

        $this->send();
    }

    function saveraw()
    {
        // Init plugin and handle managesieve connection
        $error = $this->start();

        $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_POST);

        $result = $this->sieve->save_script($script_name, $_POST['rawsetcontent']);

        if ($result === false) {
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
            $errorLines = $this->sieve->get_error_lines();
            if (count($errorLines) > 0) {
                $this->rc->output->set_env("sieve_errors", $errorLines);
            }
        }
        else {
            $this->rc->output->show_message('managesieve.setupdated', 'confirmation');
            $this->rc->output->command('parent.managesieve_updatelist', 'refresh');
        }

        $this->send();
    }

    function save()
    {
        // Init plugin and handle managesieve connection
        $error = $this->start();

        // get request size limits (#1488648)
        $max_post = max([
                ini_get('max_input_vars'),
                ini_get('suhosin.request.max_vars'),
                ini_get('suhosin.post.max_vars'),
        ]);
        $max_depth = max([
                ini_get('suhosin.request.max_array_depth'),
                ini_get('suhosin.post.max_array_depth'),
        ]);

        // check request size limit
        if ($max_post && count($_POST, COUNT_RECURSIVE) >= $max_post) {
            rcube::raise_error([
                    'code' => 500, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Request size limit exceeded (one of max_input_vars/suhosin.request.max_vars/suhosin.post.max_vars)"
                ], true, false
            );
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
        }
        // check request depth limits
        else if ($max_depth && count($_POST['_header']) > $max_depth) {
            rcube::raise_error([
                    'code' => 500, 'file' => __FILE__, 'line' => __LINE__,
                    'message' => "Request size limit exceeded (one of suhosin.request.max_array_depth/suhosin.post.max_array_depth)"
                ], true, false
            );
            $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
        }
        // filters set add action
        else if (!empty($_POST['_newset'])) {
            $name       = rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST, true);
            $copy       = rcube_utils::get_input_string('_copy', rcube_utils::INPUT_POST, true);
            $from       = rcube_utils::get_input_string('_from', rcube_utils::INPUT_POST);
            $exceptions = $this->rc->config->get('managesieve_filename_exceptions');
            $kolab      = $this->rc->config->get('managesieve_kolab_master');
            $name_uc    = mb_strtolower($name);
            $list       = $this->list_scripts();

            if (in_array('new_set', $this->disabled_actions)) {
                $error = 'managesieve.disabledaction';
            }
            else if (!$name) {
                $this->errors['name'] = $this->plugin->gettext('cannotbeempty');
            }
            else if (mb_strlen($name) > 128) {
                $this->errors['name'] = $this->plugin->gettext('nametoolong');
            }
            else if (!empty($exceptions) && in_array($name, (array)$exceptions)) {
                $this->errors['name'] = $this->plugin->gettext('namereserved');
            }
            else if (!empty($kolab) && in_array($name_uc, ['MASTER', 'USER', 'MANAGEMENT'])) {
                $this->errors['name'] = $this->plugin->gettext('namereserved');
            }
            else if (in_array($name, $list)) {
                $this->errors['name'] = $this->plugin->gettext('setexist');
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
                        $this->errors['file'] = $this->plugin->gettext('setcreateerror');
                    }
                }
                else {
                    // upload failed
                    rcmail_action::upload_error($_FILES['_file']['error']);
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
                    ['name' => $name, 'index' => $index]);
            }
            else if (!empty($msg)) {
                $this->rc->output->command('display_message', $msg, 'error');
            }
            else if ($error) {
                $this->rc->output->show_message($error, 'error');
            }
        }
        // filter add/edit action
        else if (isset($_POST['_name'])) {
            $name = trim(rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST, true));
            $fid  = trim(rcube_utils::get_input_string('_fid', rcube_utils::INPUT_POST));
            $join = trim(rcube_utils::get_input_string('_join', rcube_utils::INPUT_POST));

            // and arrays
            $headers        = rcube_utils::get_input_value('_header', rcube_utils::INPUT_POST);
            $cust_headers   = rcube_utils::get_input_value('_custom_header', rcube_utils::INPUT_POST);
            $cust_vars      = rcube_utils::get_input_value('_custom_var', rcube_utils::INPUT_POST);
            $ops            = rcube_utils::get_input_value('_rule_op', rcube_utils::INPUT_POST);
            $sizeops        = rcube_utils::get_input_value('_rule_size_op', rcube_utils::INPUT_POST);
            $sizeitems      = rcube_utils::get_input_value('_rule_size_item', rcube_utils::INPUT_POST);
            $sizetargets    = rcube_utils::get_input_value('_rule_size_target', rcube_utils::INPUT_POST);
            $spamtestops    = rcube_utils::get_input_value('_rule_spamtest_op', rcube_utils::INPUT_POST);
            $spamtesttargets = rcube_utils::get_input_value('_rule_spamtest_target', rcube_utils::INPUT_POST);
            $targets        = rcube_utils::get_input_value('_rule_target', rcube_utils::INPUT_POST, true);
            $mods           = rcube_utils::get_input_value('_rule_mod', rcube_utils::INPUT_POST);
            $mod_types      = rcube_utils::get_input_value('_rule_mod_type', rcube_utils::INPUT_POST);
            $body_trans     = rcube_utils::get_input_value('_rule_trans', rcube_utils::INPUT_POST);
            $body_types     = rcube_utils::get_input_value('_rule_trans_type', rcube_utils::INPUT_POST, true);
            $comparators    = rcube_utils::get_input_value('_rule_comp', rcube_utils::INPUT_POST);
            $indexes        = rcube_utils::get_input_value('_rule_index', rcube_utils::INPUT_POST);
            $lastindexes    = rcube_utils::get_input_value('_rule_index_last', rcube_utils::INPUT_POST);
            $dateheaders    = rcube_utils::get_input_value('_rule_date_header', rcube_utils::INPUT_POST);
            $dateparts      = rcube_utils::get_input_value('_rule_date_part', rcube_utils::INPUT_POST);
            $mime_parts     = rcube_utils::get_input_value('_rule_mime_part', rcube_utils::INPUT_POST);
            $mime_types     = rcube_utils::get_input_value('_rule_mime_type', rcube_utils::INPUT_POST);
            $mime_params    = rcube_utils::get_input_value('_rule_mime_param', rcube_utils::INPUT_POST, true);
            $message        = rcube_utils::get_input_value('_rule_message', rcube_utils::INPUT_POST);
            $dup_handles    = rcube_utils::get_input_value('_rule_duplicate_handle', rcube_utils::INPUT_POST, true);
            $dup_headers    = rcube_utils::get_input_value('_rule_duplicate_header', rcube_utils::INPUT_POST, true);
            $dup_uniqueids  = rcube_utils::get_input_value('_rule_duplicate_uniqueid', rcube_utils::INPUT_POST, true);
            $dup_seconds    = rcube_utils::get_input_value('_rule_duplicate_seconds', rcube_utils::INPUT_POST);
            $dup_lasts      = rcube_utils::get_input_value('_rule_duplicate_last', rcube_utils::INPUT_POST);
            $act_types      = rcube_utils::get_input_value('_action_type', rcube_utils::INPUT_POST, true);
            $mailboxes      = rcube_utils::get_input_value('_action_mailbox', rcube_utils::INPUT_POST, true);
            $act_targets    = rcube_utils::get_input_value('_action_target', rcube_utils::INPUT_POST, true);
            $domain_targets = rcube_utils::get_input_value('_action_target_domain', rcube_utils::INPUT_POST);
            $area_targets   = rcube_utils::get_input_value('_action_target_area', rcube_utils::INPUT_POST, true);
            $reasons        = rcube_utils::get_input_value('_action_reason', rcube_utils::INPUT_POST, true);
            $addresses      = rcube_utils::get_input_value('_action_addresses', rcube_utils::INPUT_POST, true);
            $intervals      = rcube_utils::get_input_value('_action_interval', rcube_utils::INPUT_POST);
            $interval_types = rcube_utils::get_input_value('_action_interval_type', rcube_utils::INPUT_POST);
            $from           = rcube_utils::get_input_value('_action_from', rcube_utils::INPUT_POST, true);
            $subject        = rcube_utils::get_input_value('_action_subject', rcube_utils::INPUT_POST, true);
            $flags          = rcube_utils::get_input_value('_action_flags', rcube_utils::INPUT_POST);
            $varnames       = rcube_utils::get_input_value('_action_varname', rcube_utils::INPUT_POST);
            $varvalues      = rcube_utils::get_input_value('_action_varvalue', rcube_utils::INPUT_POST);
            $varmods        = rcube_utils::get_input_value('_action_varmods', rcube_utils::INPUT_POST);
            $notifymethods  = rcube_utils::get_input_value('_action_notifymethod', rcube_utils::INPUT_POST);
            $notifytargets  = rcube_utils::get_input_value('_action_notifytarget', rcube_utils::INPUT_POST, true);
            $notifyoptions  = rcube_utils::get_input_value('_action_notifyoption', rcube_utils::INPUT_POST, true);
            $notifymessages = rcube_utils::get_input_value('_action_notifymessage', rcube_utils::INPUT_POST, true);
            $notifyfrom     = rcube_utils::get_input_value('_action_notifyfrom', rcube_utils::INPUT_POST);
            $notifyimp      = rcube_utils::get_input_value('_action_notifyimportance', rcube_utils::INPUT_POST);
            $addheader_name  = rcube_utils::get_input_value('_action_addheader_name', rcube_utils::INPUT_POST);
            $addheader_value = rcube_utils::get_input_value('_action_addheader_value', rcube_utils::INPUT_POST, true);
            $addheader_pos   = rcube_utils::get_input_value('_action_addheader_pos', rcube_utils::INPUT_POST);
            $delheader_name  = rcube_utils::get_input_value('_action_delheader_name', rcube_utils::INPUT_POST);
            $delheader_value = rcube_utils::get_input_value('_action_delheader_value', rcube_utils::INPUT_POST, true);
            $delheader_pos   = rcube_utils::get_input_value('_action_delheader_pos', rcube_utils::INPUT_POST);
            $delheader_index = rcube_utils::get_input_value('_action_delheader_index', rcube_utils::INPUT_POST);
            $delheader_op    = rcube_utils::get_input_value('_action_delheader_op', rcube_utils::INPUT_POST);
            $delheader_comp  = rcube_utils::get_input_value('_action_delheader_comp', rcube_utils::INPUT_POST);

            $this->form['disabled'] = empty($_POST['_enabled']);
            $this->form['join']     = $join == 'allof';
            $this->form['name']     = $name;
            $this->form['tests']    = [];
            $this->form['actions']  = [];

            if ($name == '') {
                $this->errors['name'] = $this->plugin->gettext('cannotbeempty');
            }
            else {
                foreach ($this->script as $idx => $rule)
                    if ($rule['name'] == $name && $idx != $fid) {
                        $this->errors['name'] = $this->plugin->gettext('ruleexist');
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
                    // targets are indexed differently (assume form order)
                    $target     = $this->strip_value($targets[$idx], true);
                    $header     = $this->strip_value($header);
                    $operator   = $this->strip_value($ops[$idx]);
                    $comparator = $this->strip_value($comparators[$idx]);

                    if ($header == 'size') {
                        $sizeop     = $this->strip_value($sizeops[$idx]);
                        $sizeitem   = $this->strip_value($sizeitems[$idx]);
                        $sizetarget = $this->strip_value($sizetargets[$idx]);

                        $this->form['tests'][$i]['test'] = 'size';
                        $this->form['tests'][$i]['type'] = $sizeop;
                        $this->form['tests'][$i]['arg']  = $sizetarget;

                        if ($sizetarget == '') {
                            $this->errors['tests'][$i]['sizetarget'] = $this->plugin->gettext('cannotbeempty');
                        }
                        else if (!preg_match('/^[0-9]+(K|M|G)?$/i', $sizetarget.$sizeitem, $m)) {
                            $this->errors['tests'][$i]['sizetarget'] = $this->plugin->gettext('forbiddenchars');
                            $this->form['tests'][$i]['item'] = $sizeitem;
                        }
                        else {
                            $this->form['tests'][$i]['arg'] .= $m[1];
                        }
                    }
                    else if ($header == 'spamtest') {
                        $spamtestop     = $this->strip_value($spamtestops[$idx]);
                        $spamtesttarget = $this->strip_value($spamtesttargets[$idx]);
                        $comparator     = 'i;ascii-numeric';

                        if (!$spamtestop) {
                            $spamtestop     = 'value-eq';
                            $spamtesttarget = '0';
                        }

                        $this->form['tests'][$i]['test'] = 'spamtest';
                        $this->form['tests'][$i]['type'] = $spamtestop;
                        $this->form['tests'][$i]['arg']  = $spamtesttarget;

                        if ($spamtesttarget === '') {
                            $this->errors['tests'][$i]['spamtesttarget'] = $this->plugin->gettext('cannotbeempty');
                        }
                        else if (!preg_match('/^([0-9]|10)$/i', $spamtesttarget)) {
                            $this->errors['tests'][$i]['spamtesttarget'] = $this->plugin->gettext('forbiddenchars');
                        }
                    }
                    else if ($header == 'currentdate') {
                        $datepart = $this->strip_value($dateparts[$idx]);
                        $type     = preg_replace('/^not/', '', $operator);

                        $this->form['tests'][$i]['test'] = 'currentdate';
                        $this->form['tests'][$i]['type'] = $type;
                        $this->form['tests'][$i]['part'] = $datepart;
                        $this->form['tests'][$i]['arg']  = $target;
                        $this->form['tests'][$i]['not']  = preg_match('/^not/', $operator) === 1;

                        if ($type == 'exists') {
                            $this->errors['tests'][$i]['op'] = true;
                        }

                        if ($type != 'exists') {
                            if (empty($target)) {
                                $this->errors['tests'][$i]['target'] = $this->plugin->gettext('cannotbeempty');
                            }
                            else if (strpos($type, 'count-') === 0) {
                                foreach ($target as $arg) {
                                    if (preg_match('/[^0-9]/', $arg)) {
                                        $this->errors['tests'][$i]['target'] = $this->plugin->gettext('forbiddenchars');
                                    }
                                }
                            }
                            else if (strpos($type, 'value-') === 0) {
                                // Some date/time formats do not support i;ascii-numeric comparator
                                if ($comparator == 'i;ascii-numeric' && in_array($datepart, ['date', 'time', 'iso8601', 'std11'])) {
                                    $comparator = '';
                                }
                            }

                            if (!preg_match('/^(regex|matches|count-)/', $type) && !empty($target)) {
                                foreach ($target as $arg) {
                                    if (!$this->validate_date_part($datepart, $arg)) {
                                        $this->errors['tests'][$i]['target'] = $this->plugin->gettext('invaliddateformat');
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    else if ($header == 'date') {
                        $datepart    = $this->strip_value($dateparts[$idx]);
                        $dateheader  = $this->strip_value($dateheaders[$idx]);
                        $index       = $this->strip_value($indexes[$idx]);
                        $indexlast   = $this->strip_value($lastindexes[$idx]);
                        $mod         = $this->strip_value($mods[$idx]);

                        $type = preg_replace('/^not/', '', $operator);

                        if ($type == 'exists') {
                            $this->errors['tests'][$i]['op'] = true;
                        }

                        if (!empty($index) && $mod != 'envelope') {
                            $this->form['tests'][$i]['index'] = intval($index);
                            $this->form['tests'][$i]['last']  = !empty($indexlast);
                        }

                        if (empty($dateheader)) {
                            $dateheader = 'Date';
                        }
                        else if (!preg_match('/^[\x21-\x39\x41-\x7E]+$/i', $dateheader)) {
                            $this->errors['tests'][$i]['dateheader'] = $this->plugin->gettext('forbiddenchars');
                        }

                        $this->form['tests'][$i]['test']   = 'date';
                        $this->form['tests'][$i]['type']   = $type;
                        $this->form['tests'][$i]['part']   = $datepart;
                        $this->form['tests'][$i]['arg']    = $target;
                        $this->form['tests'][$i]['header'] = $dateheader;
                        $this->form['tests'][$i]['not']    = preg_match('/^not/', $operator) === 1;

                        if ($type != 'exists') {
                            if (empty($target)) {
                                $this->errors['tests'][$i]['target'] = $this->plugin->gettext('cannotbeempty');
                            }
                            else if (strpos($type, 'count-') === 0) {
                                foreach ($target as $arg) {
                                    if (preg_match('/[^0-9]/', $arg)) {
                                        $this->errors['tests'][$i]['target'] = $this->plugin->gettext('forbiddenchars');
                                    }
                                }
                            }
                            else if (strpos($type, 'value-') === 0) {
                                // Some date/time formats do not support i;ascii-numeric comparator
                                if ($comparator == 'i;ascii-numeric' && in_array($datepart, ['date', 'time', 'iso8601', 'std11'])) {
                                    $comparator = '';
                                }
                            }

                            if (!empty($target) && !preg_match('/^(regex|matches|count-)/', $type)) {
                                foreach ($target as $arg) {
                                    if (!$this->validate_date_part($datepart, $arg)) {
                                        $this->errors['tests'][$i]['target'] = $this->plugin->gettext('invaliddateformat');
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    else if ($header == 'body') {
                        $trans      = $this->strip_value($body_trans[$idx]);
                        $trans_type = $this->strip_value($body_types[$idx], true);
                        $type       = preg_replace('/^not/', '', $operator);

                        $this->form['tests'][$i]['test'] = 'body';
                        $this->form['tests'][$i]['type'] = $type;
                        $this->form['tests'][$i]['arg']  = $target;
                        $this->form['tests'][$i]['not']  = preg_match('/^not/', $operator) === 1;
                        $this->form['tests'][$i]['part'] = $trans;

                        if ($trans == 'content') {
                            $this->form['tests'][$i]['content'] = $trans_type;
                        }

                        if ($type == 'exists') {
                            $this->errors['tests'][$i]['op'] = true;
                        }

                        if (empty($target) && $type != 'exists') {
                            $this->errors['tests'][$i]['target'] = $this->plugin->gettext('cannotbeempty');
                        }
                        else if (preg_match('/^(value|count)-/', $type)) {
                            foreach ($target as $target_value) {
                                if (preg_match('/[^0-9]/', $target_value)) {
                                    $this->errors['tests'][$i]['target'] = $this->plugin->gettext('forbiddenchars');
                                }
                            }
                        }
                    }
                    else if ($header == 'message') {
                        $test = $this->strip_value($message[$idx]);

                        if (preg_match('/^not/', $test)) {
                            $this->form['tests'][$i]['not'] = true;
                            $test = substr($test, 3);
                        }

                        $this->form['tests'][$i]['test'] = $test;

                        if ($test == 'duplicate') {
                            $this->form['tests'][$i]['last']     = !empty($dup_lasts[$idx]);
                            $this->form['tests'][$i]['handle']   = trim($dup_handles[$idx]);
                            $this->form['tests'][$i]['header']   = trim($dup_headers[$idx]);
                            $this->form['tests'][$i]['uniqueid'] = trim($dup_uniqueids[$idx]);
                            $this->form['tests'][$i]['seconds']  = trim($dup_seconds[$idx]);

                            if ($this->form['tests'][$i]['seconds']
                                && preg_match('/[^0-9]/', $this->form['tests'][$i]['seconds'])
                            ) {
                                $this->errors['tests'][$i]['duplicate_seconds'] = $this->plugin->gettext('forbiddenchars');
                            }

                            if ($this->form['tests'][$i]['header'] && $this->form['tests'][$i]['uniqueid']) {
                                $this->errors['tests'][$i]['duplicate_uniqueid'] = $this->plugin->gettext('duplicate.conflict.err');
                            }
                        }
                    }
                    else {
                        $cust_header = $headers = $this->strip_value($cust_headers[$idx]);
                        $mod         = $this->strip_value($mods[$idx]);
                        $mod_type    = $this->strip_value($mod_types[$idx]);
                        $index       = isset($indexes[$idx]) ? $this->strip_value($indexes[$idx]) : null;
                        $indexlast   = isset($lastindexes[$idx]) ? $this->strip_value($lastindexes[$idx]) : null;
                        $mime_param  = isset($mime_params[$idx]) ? $this->strip_value($mime_params[$idx]) : null;
                        $mime_type   = $mime_types[$idx] ?? null;
                        $mime_part   = $mime_parts[$idx] ?? null;
                        $cust_var    = null;

                        if ($header == 'string') {
                            $cust_var = $headers = $this->strip_value($cust_vars[$idx]);
                        }

                        $this->form['tests'][$i]['not'] = preg_match('/^not/', $operator) === 1;

                        $type = preg_replace('/^not/', '', $operator);

                        if (!empty($index) && $mod != 'envelope') {
                            $this->form['tests'][$i]['index'] = intval($index);
                            $this->form['tests'][$i]['last']  = !empty($indexlast);
                        }

                        if ($header == '...' || $header == 'string') {
                            if (!count($headers)) {
                                $this->errors['tests'][$i]['header'] = $this->plugin->gettext('cannotbeempty');
                            }
                            else if ($header == '...') {
                                foreach ($headers as $hr) {
                                    // RFC2822: printable ASCII except colon
                                    if (!preg_match('/^[\x21-\x39\x41-\x7E]+$/i', $hr)) {
                                        $this->errors['tests'][$i]['header'] = $this->plugin->gettext('forbiddenchars');
                                    }
                                }
                            }

                            if (empty($this->errors['tests'][$i]['header'])) {
                                $cust_header = $cust_var = (is_array($headers) && count($headers) == 1) ? $headers[0] : $headers;
                            }
                        }

                        $test   = $header == 'string' ? 'string' : 'header';
                        $header = $header == 'string' ? $cust_var : $header;
                        $header = $header == '...' ? $cust_header : $header;

                        if (is_array($header)) {
                            foreach ($header as $h_index => $val) {
                                if (isset($this->headers[$val])) {
                                    $header[$h_index] = $this->headers[$val];
                                }
                            }
                        }

                        if ($type == 'exists') {
                            $this->form['tests'][$i]['test'] = 'exists';
                            $this->form['tests'][$i]['arg'] = $header;
                        }
                        else {
                            if ($mod == 'address' || $mod == 'envelope') {
                                $found = false;
                                if (empty($this->errors['tests'][$i]['header'])) {
                                    foreach ((array)$header as $hdr) {
                                        if (!in_array(strtolower(trim($hdr)), $this->addr_headers)) {
                                            $found = true;
                                        }
                                    }
                                }
                                if (!$found) {
                                    $test = $mod;
                                }
                            }

                            $this->form['tests'][$i]['type'] = $type;
                            $this->form['tests'][$i]['test'] = $test;
                            $this->form['tests'][$i]['arg1'] = $header;
                            $this->form['tests'][$i]['arg2'] = $target;

                            if (empty($target)) {
                                $this->errors['tests'][$i]['target'] = $this->plugin->gettext('cannotbeempty');
                            }
                            else if (preg_match('/^(value|count)-/', $type)) {
                                foreach ($target as $target_value) {
                                    if (preg_match('/[^0-9]/', $target_value)) {
                                        $this->errors['tests'][$i]['target'] = $this->plugin->gettext('forbiddenchars');
                                    }
                                }
                            }

                            if ($mod) {
                                $this->form['tests'][$i]['part'] = $mod_type;
                            }
                        }

                        if ($test == 'header') {
                            if (in_array($mime_type, ['type', 'subtype', 'contenttype', 'param'])) {
                                $this->form['tests'][$i]['mime-' . $mime_type] = true;
                                if ($mime_type == 'param') {
                                    if (empty($mime_param)) {
                                        $this->errors['tests'][$i]['mime-param'] = $this->plugin->gettext('cannotbeempty');
                                    }

                                    $this->form['tests'][$i]['mime-param'] = $mime_param;
                                }
                            }

                            if ($mime_part == 'anychild') {
                                $this->form['tests'][$i]['mime-anychild'] = true;
                            }
                        }
                    }

                    if ($header != 'size' && $comparator) {
                        $this->form['tests'][$i]['comparator'] = $comparator;
                    }

                    $i++;
                }
            }

            $i = 0;
            // actions
            foreach ($act_types as $idx => $type) {
                $type = $this->strip_value($type);

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
//                      $this->errors['actions'][$i]['targetarea'] = $this->plugin->gettext('cannotbeempty');
                    break;

                case 'redirect':
                case 'redirect_copy':
                    $target = $this->strip_value($act_targets[$idx]);
                    $domain = $this->strip_value($domain_targets[$idx]);

                    // force one of the configured domains
                    $domains = (array) $this->rc->config->get('managesieve_domains');
                    if (!empty($domains) && !empty($target)) {
                        if (!$domain || !in_array($domain, $domains)) {
                            $domain = $domains[0];
                        }

                        $target .= '@' . $domain;
                    }

                    $this->form['actions'][$i]['target'] = $target;

                    if ($target == '') {
                        $this->errors['actions'][$i]['target'] = $this->plugin->gettext('cannotbeempty');
                    }
                    else if (!rcube_utils::check_email($target)) {
                        $this->errors['actions'][$i]['target'] = $this->plugin->gettext(!empty($domains) ? 'forbiddenchars' : 'noemailwarning');
                    }

                    if ($type == 'redirect_copy') {
                        $type = 'redirect';
                        $this->form['actions'][$i]['copy'] = true;
                    }

                    break;

                case 'addflag':
                case 'setflag':
                case 'removeflag':
                    $this->form['actions'][$i]['target'] = $this->strip_value($flags[$idx]);

                    if (empty($this->form['actions'][$i]['target'])) {
                        $this->errors['actions'][$i]['flag'] = $this->plugin->gettext('noflagset');
                    }

                    break;

                case 'addheader':
                case 'deleteheader':
                    $this->form['actions'][$i]['name']  = trim($type == 'addheader' ? $addheader_name[$idx] : $delheader_name[$idx]);
                    $this->form['actions'][$i]['value'] = $type == 'addheader' ? $addheader_value[$idx] : $delheader_value[$idx];
                    $this->form['actions'][$i]['last']  = ($type == 'addheader' ? $addheader_pos[$idx] : $delheader_pos[$idx]) == 'last';

                    if (empty($this->form['actions'][$i]['name'])) {
                        $this->errors['actions'][$i]['name'] = $this->plugin->gettext('cannotbeempty');
                    }
                    else if (!preg_match('/^[0-9a-z_-]+$/i', $this->form['actions'][$i]['name'])) {
                        $this->errors['actions'][$i]['name'] = $this->plugin->gettext('forbiddenchars');
                    }

                    if ($type == 'deleteheader') {
                        foreach ((array) $this->form['actions'][$i]['value'] as $pidx => $pattern) {
                            if (empty($pattern)) {
                                unset($this->form['actions'][$i]['value'][$pidx]);
                            }
                        }

                        $this->form['actions'][$i]['match-type'] = $delheader_op[$idx];
                        $this->form['actions'][$i]['comparator'] = $delheader_comp[$idx];
                        $this->form['actions'][$i]['index']      = $delheader_index[$idx];

                        if (empty($this->form['actions'][$i]['index'])) {
                            if (!empty($this->form['actions'][$i]['last'])) {
                                $this->errors['actions'][$i]['index'] = $this->plugin->gettext('lastindexempty');
                            }
                        }
                        else if (!preg_match('/^[0-9]+$/i', $this->form['actions'][$i]['index'])) {
                            $this->errors['actions'][$i]['index'] = $this->plugin->gettext('forbiddenchars');
                        }
                    }
                    else {
                        if (empty($this->form['actions'][$i]['value'])) {
                            $this->errors['actions'][$i]['value'] = $this->plugin->gettext('cannotbeempty');
                        }
                    }

                    break;

                case 'vacation':
                    $reason        = $this->strip_value($reasons[$idx], true);
                    $interval_type = $interval_types && $interval_types[$idx] == 'seconds' ? 'seconds' : 'days';

                    $this->form['actions'][$i]['reason']    = str_replace("\r\n", "\n", $reason);
                    $this->form['actions'][$i]['from']      = $from[$idx];
                    $this->form['actions'][$i]['subject']   = $subject[$idx];
                    $this->form['actions'][$i]['addresses'] = $addresses[$idx];
                    $this->form['actions'][$i][$interval_type] = $intervals[$idx];

                    // @TODO: vacation :mime, :handle

                    foreach ((array)$this->form['actions'][$i]['addresses'] as $aidx => $address) {
                        $this->form['actions'][$i]['addresses'][$aidx] = $address = trim($address);

                        if (empty($address)) {
                            unset($this->form['actions'][$i]['addresses'][$aidx]);
                        }
                        else if (!rcube_utils::check_email($address)) {
                            $this->errors['actions'][$i]['addresses'] = $this->plugin->gettext('noemailwarning');
                            break;
                        }
                    }

                    if (!empty($this->form['actions'][$i]['from'])) {
                        // According to RFC5230 the :from string must specify a valid [RFC2822] mailbox-list
                        // we'll try to extract addresses and validate them separately
                        $from = rcube_mime::decode_address_list($this->form['actions'][$i]['from'], null, true, RCUBE_CHARSET);
                        foreach ((array) $from as $idx => $addr) {
                            if (empty($addr['mailto']) || !rcube_utils::check_email($addr['mailto'])) {
                                $this->errors['actions'][$i]['from'] = $this->plugin->gettext('noemailwarning');
                                break;
                            }
                            else {
                                $from[$idx] = format_email_recipient($addr['mailto'], $addr['name']);
                            }
                        }

                        // Only one address is allowed (at least on cyrus imap)
                        if (is_array($from) && count($from) > 1) {
                            $this->errors['actions'][$i]['from'] = $this->plugin->gettext('noemailwarning');
                        }

                        // Then we convert it back to RFC2822 format
                        if (empty($this->errors['actions'][$i]['from']) && !empty($from)) {
                            $this->form['actions'][$i]['from'] = Mail_mimePart::encodeHeader(
                                'From', implode(', ', $from), RCUBE_CHARSET, 'base64', '');
                        }
                    }

                    if ($this->form['actions'][$i]['reason'] == '') {
                        $this->errors['actions'][$i]['reason'] = $this->plugin->gettext('cannotbeempty');
                    }
                    if ($this->form['actions'][$i][$interval_type] && !preg_match('/^[0-9]+$/', $this->form['actions'][$i][$interval_type])) {
                        $this->errors['actions'][$i]['interval'] = $this->plugin->gettext('forbiddenchars');
                    }
                    break;

                case 'set':
                    $this->form['actions'][$i]['name'] = $varnames[$idx];
                    $this->form['actions'][$i]['value'] = $varvalues[$idx];
                    foreach ((array)$varmods[$idx] as $v_m) {
                        $this->form['actions'][$i][$v_m] = true;
                    }

                    if (empty($varnames[$idx])) {
                        $this->errors['actions'][$i]['name'] = $this->plugin->gettext('cannotbeempty');
                    }
                    else if (!preg_match('/^[0-9a-z_]+$/i', $varnames[$idx])) {
                        $this->errors['actions'][$i]['name'] = $this->plugin->gettext('forbiddenchars');
                    }

                    if (!isset($varvalues[$idx]) || $varvalues[$idx] === '') {
                        $this->errors['actions'][$i]['value'] = $this->plugin->gettext('cannotbeempty');
                    }
                    break;

                case 'notify':
                    if (empty($notifymethods[$idx])) {
                        $this->errors['actions'][$i]['method'] = $this->plugin->gettext('cannotbeempty');
                    }
                    if (empty($notifytargets[$idx])) {
                        $this->errors['actions'][$i]['target'] = $this->plugin->gettext('cannotbeempty');
                    }
                    if (!empty($notifyfrom[$idx]) && !rcube_utils::check_email($notifyfrom[$idx])) {
                        $this->errors['actions'][$i]['from'] = $this->plugin->gettext('noemailwarning');
                    }

                    // skip empty options
                    foreach ((array)$notifyoptions[$idx] as $opt_idx => $opt) {
                        if (!strlen(trim($opt))) {
                            unset($notifyoptions[$idx][$opt_idx]);
                        }
                    }

                    $this->form['actions'][$i]['method']     = $notifymethods[$idx] . ':' . $notifytargets[$idx];
                    $this->form['actions'][$i]['options']    = $notifyoptions[$idx];
                    $this->form['actions'][$i]['message']    = $notifymessages[$idx];
                    $this->form['actions'][$i]['from']       = $notifyfrom[$idx];
                    $this->form['actions'][$i]['importance'] = $notifyimp[$idx];
                    break;
                }

                $this->form['actions'][$i]['type'] = $type;
                $i++;
            }

            if (!$this->errors && !$error) {
                // save the script
                if (!isset($this->script[$fid])) {
                    $fid = $this->sieve->script->add_rule($this->form);
                    $new = true;
                }
                else {
                    $fid = $this->sieve->script->update_rule($fid, $this->form);
                }

                if ($fid !== false) {
                    $save = $this->save_script();
                }

                if (!empty($save) && $fid !== false) {
                    $this->rc->output->show_message('managesieve.filtersaved', 'confirmation');
                    if ($this->rc->task != 'mail') {
                        $args = [
                            'name'     => $this->form['name'],
                            'id'       => $fid,
                            'disabled' => $this->form['disabled']
                        ];
                        $this->rc->output->command('parent.managesieve_updatelist', isset($new) ? 'add' : 'update', $args);
                    }
                    else {
                        $this->rc->output->command('managesieve_dialog_close');
                        $this->rc->output->send('iframe');
                    }
                }
                else {
                    $this->rc->output->show_message('managesieve.filtersaveerror', 'error');
                }
            }
            else {
                $this->rc->output->show_message('managesieve.filterformerror', 'warning');
            }
        }

        $this->send();
    }

    protected function send()
    {
        // Handle form action
        if (isset($_GET['_framed']) || isset($_POST['_framed'])) {
            if (isset($_GET['_newset']) || isset($_POST['_newset'])) {
                $this->rc->output->send('managesieve.setedit');
            }
            else if (isset($_GET['_seteditraw']) || isset($_POST['_seteditraw'])) {
                $this->rc->output->send('managesieve.seteditraw');
            }
            else {
                $this->rc->output->send('managesieve.filteredit');
            }
        }
        else {
            $this->rc->output->set_pagetitle($this->plugin->gettext('filters'));
            $this->rc->output->send('managesieve.managesieve');
        }
    }

    /**
     * Return the filters list as HTML table
     */
    function filters_list($attrib)
    {
        // add id to message list table if not specified
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmfilterslist';
        }

        // define list of cols to be displayed
        $a_show_cols = ['name'];

        $result = $this->list_rules();

        // create the table
        $out = rcmail_action::table_output($attrib, $result, $a_show_cols, 'id');

        // set client env
        $this->rc->output->add_gui_object('filterslist', $attrib['id']);
        $this->rc->output->include_script('list.js');

        // add some labels to client
        $this->rc->output->add_label('managesieve.filterdeleteconfirm');

        return $out;
    }

    /**
     * Return the filters list as <SELECT>
     */
    function filtersets_list($attrib, $no_env = false)
    {
        // add id to message list table if not specified
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmfiltersetslist';
        }

        $list = $this->list_scripts();

        if ($list) {
            asort($list, SORT_LOCALE_STRING);
        }

        if (!empty($attrib['type']) && $attrib['type'] == 'list') {
            // define list of cols to be displayed
            $a_show_cols = ['name'];
            $result      = [];
            $scripts     = [];

            if ($list) {
                foreach ($list as $idx => $set) {
                    $scripts['S' . $idx] = $set;
                    $result[] = [
                        'name'  => $set,
                        'id'    => 'S' . $idx,
                        'class' => !in_array($set, $this->active) ? 'disabled' : '',
                    ];
                }
            }

            // create XHTML table
            $out = $this->rc->table_output($attrib, $result, $a_show_cols, 'id');

            $this->rc->output->set_env('filtersets', $scripts);
            $this->rc->output->include_script('list.js');
        }
        else {
            $select = new html_select([
                    'name'     => '_set',
                    'id'       => $attrib['id'],
                    'class'    => 'custom-select',
                    'onchange' => $this->rc->task != 'mail' ? 'rcmail.managesieve_set()' : ''
            ]);

            if ($list) {
                foreach ($list as $set) {
                    $select->add($set, $set);
                }
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

    function filterset_editraw($attrib)
    {
        $script_name = rcube_utils::get_input_string('_set', rcube_utils::INPUT_GP);
        $script      = $this->sieve->get_script($script_name);
        $script_post = !empty($_POST['rawsetcontent']) ? $_POST['rawsetcontent'] : null;
        $framed      = !empty($_POST['_framed']) || !empty($_GET['_framed']);

        $hiddenfields = new html_hiddenfield();
        $hiddenfields->add(['name' => '_task',   'value' => $this->rc->task]);
        $hiddenfields->add(['name' => '_action', 'value' => 'plugin.managesieve-saveraw']);
        $hiddenfields->add(['name' => '_set',    'value' => $script_name]);
        $hiddenfields->add(['name' => '_seteditraw', 'value' => 1]);
        $hiddenfields->add(['name' => '_framed', 'value' => $framed ? 1 : 0]);

        $out = $hiddenfields->show();

        $txtarea = new html_textarea([
                'id'    => 'rawfiltersettxt',
                'name'  => 'rawsetcontent',
                'class' => 'form-control',
                'rows'  => '15'
        ]);

        $out .= $txtarea->show($script_post !== null ? $script_post : ($script !== false ? rtrim($script) : ''));

        $this->rc->output->add_gui_object('sievesetrawform', 'filtersetrawform');
        $this->plugin->include_stylesheet('codemirror/lib/codemirror.css');
        $this->plugin->include_script('codemirror/lib/codemirror.js');
        $this->plugin->include_script('codemirror/addon/selection/active-line.js');
        $this->plugin->include_script('codemirror/mode/sieve/sieve.js');

        if ($script === false) {
            $this->rc->output->show_message('managesieve.filterunknownerror', 'error');
        }

        $out = html::tag('form', $attrib + [
                'id'      => 'filtersetrawform',
                'name'    => 'filtersetrawform',
                'action'  => './',
                'method'  => 'post',
                'enctype' => 'multipart/form-data',
            ], $out
        );

        return str_replace('</form>', '', $out);
    }

    function filterset_form($attrib)
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmfiltersetform';
        }

        $framed       = !empty($_POST['_framed']) || !empty($_GET['_framed']);
        $table        = new html_table(['cols' => 2, 'class' => 'propform']);
        $hiddenfields = new html_hiddenfield(['name' => '_task', 'value' => $this->rc->task]);
        $hiddenfields->add(['name' => '_action', 'value' => 'plugin.managesieve-save']);
        $hiddenfields->add(['name' => '_framed', 'value' => $framed ? 1 : 0]);
        $hiddenfields->add(['name' => '_newset', 'value' => 1]);

        $name     = rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST);
        $copy     = rcube_utils::get_input_string('_copy', rcube_utils::INPUT_POST);
        $selected = rcube_utils::get_input_string('_from', rcube_utils::INPUT_POST);

        // filter set name input
        $input_name = new html_inputfield([
                'name'  => '_name',
                'id'    => '_name',
                'size'  => 30,
                'class' => !empty($this->errors['name']) ? 'error form-control' : 'form-control'
        ]);

        $table->add('title', html::label('_name', rcube::Q($this->plugin->gettext('filtersetname'))));
        $table->add(null, $input_name->show($name));

        $filters = '<ul class="proplist">';
        $filters .= '<li>' . html::label('from_none', html::tag('input', [
                'type'    => 'radio',
                'id'      => 'from_none',
                'name'    => '_from',
                'value'   => 'none',
                'checked' => !$selected || $selected == 'none'
            ]) . rcube::Q($this->plugin->gettext('none'))) . '</li>';

        // filters set list
        $list   = $this->list_scripts();
        $select = new html_select(['name' => '_copy', 'id' => '_copy', 'class' => 'custom-select']);

        if (is_array($list)) {
            asort($list, SORT_LOCALE_STRING);

            if (!$copy && isset($_SESSION['managesieve_current'])) {
                $copy = $_SESSION['managesieve_current'];
            }

            foreach ($list as $set) {
                $select->add($set, $set);
            }

            $filters .= '<li>' . html::label('from_set', html::tag('input', [
                    'type'    => 'radio',
                    'id'      => 'from_set',
                    'name'    => '_from',
                    'value'   => 'set',
                    'checked' => $selected == 'set',
                ]) .  rcube::Q($this->plugin->gettext('fromset')) . ' ' . $select->show($copy)) . '</li>';
        }

        // script upload box
        $upload = new html_inputfield([
                'name'  => '_file',
                'id'    => '_file',
                'size'  => 30,
                'type'  => 'file',
                'class' => !empty($this->errors['file']) ? 'error form-control' : 'form-control'
        ]);

        $filters .= '<li>' . html::label('from_file', html::tag('input', [
                'type'    => 'radio',
                'id'      => 'from_file',
                'name'    => '_from',
                'value'   => 'file',
                'checked' => $selected == 'file',
            ]) . rcube::Q($this->plugin->gettext('fromfile')) . ' ' . $upload->show()) . '</li>';

        $filters .= '</ul>';

        $table->add('title', html::label('from_none', rcube::Q($this->plugin->gettext('filters'))));
        $table->add('', $filters);

        $out = '<form name="filtersetform" action="./" method="post" enctype="multipart/form-data">'
            . "\n" . $hiddenfields->show() . "\n" . $table->show();

        $this->rc->output->add_gui_object('sieveform', 'filtersetform');

        if (!empty($this->errors['name'])) {
            $this->add_tip('_name', $this->errors['name'], true);
        }
        if (!empty($this->errors['file'])) {
            $this->add_tip('_file', $this->errors['file'], true);
        }

        $this->print_tips();

        return $out;
    }

    /**
     * Filter form object for templates engine
     */
    function filter_form($attrib)
    {
        if (empty($attrib['id'])) {
            $attrib['id'] = 'rcmfilterform';
        }

        $fid     = rcube_utils::get_input_string('_fid', rcube_utils::INPUT_GPC);
        $scr     = $this->form ?? (array_key_exists($fid, $this->script) ? $this->script[$fid] : null);
        $compact = !empty($attrib['compact-form']);
        $framed  = !empty($_POST['_framed']) || !empty($_GET['_framed']);

        $_SESSION['managesieve-compact-form'] = $compact;

        // do not allow creation of new filters
        if ($fid === null && in_array('new_filter', $this->disabled_actions)) {
            $this->rc->output->show_message('managesieve.disabledaction', 'error');
            return;
        }

        $hiddenfields = new html_hiddenfield(['name' => '_task', 'value' => $this->rc->task]);
        $hiddenfields->add(['name' => '_action', 'value' => 'plugin.managesieve-save']);
        $hiddenfields->add(['name' => '_framed', 'value' => $framed ? 1 : 0]);
        $hiddenfields->add(['name' => '_fid', 'value' => $fid]);

        $out = $hiddenfields->show();

        // 'any' flag
        $any = (
            (!isset($this->form) && !empty($scr) && empty($scr['tests']))
            || (!empty($scr['tests']) && count($scr['tests']) == 1
                && $scr['tests'][0]['test'] == 'true' && empty($scr['tests'][0]['not'])
            )
        );

        // filter name input
        $input_name = new html_inputfield([
                'name'  => '_name',
                'id'    => '_name',
                'size'  => 30,
                'class' => !empty($this->errors['name']) ? 'form-control error' : 'form-control'
        ]);

        if (!empty($this->errors['name'])) {
            $this->add_tip('_name', $this->errors['name'], true);
        }

        $input_name = $input_name->show(isset($scr) ? $scr['name'] : '');

        $out .= sprintf("\n" . '<div class="form-group row">'
            . '<label for="_name" class="col-sm-4 col-form-label">%s</label>'
            . '<div class="col-sm-8">%s</div></div>',
            rcube::Q($this->plugin->gettext('filtername')), $input_name
        );

        // filter set selector
        if ($this->rc->task == 'mail') {
            $out .= sprintf("\n" . '<div class="form-group row">'
                . '<label for="%s" class="col-sm-4 col-form-label">%s</label>'
                . '<div class="col-sm-8">%s</div></div>',
                'sievescriptname',
                rcube::Q($this->plugin->gettext('filterset')),
                $this->filtersets_list(['id' => 'sievescriptname'], true)
            );
        }

        $out .= sprintf("\n" . '<div class="form-group row form-check">'
            . '<label for="fenabled" class="col-sm-4 col-form-label">%s</label>'
            . '<div class="col-sm-8 form-check">'
                . '<input type="checkbox" id="fenabled" name="_enabled" value="1"' . (empty($scr['disabled']) ? ' checked' : '') . ' />'
            . '</div></div>',
            rcube::Q($this->plugin->gettext('filterenabled'))
        );

        if ($compact) {
            $select = new html_select(['name' => '_join', 'id' => '_join', 'class' => 'custom-select',
                'onchange' => 'rule_join_radio(this.value)']);

            foreach (['allof', 'anyof', 'any'] as $val) {
                $select->add($this->plugin->gettext('filter' . $val), $val);
            }

            $join = $any ? 'any' : 'allof';
            if (isset($scr) && !$any) {
                 $join = !empty($scr['join']) ? 'allof' : 'anyof';
            }

            $out .= sprintf("\n" . '<div class="form-group row">'
                . '<label for="_join" class="col-sm-4 col-form-label">%s</label>'
                . '<div class="col-sm-8">%s</div></div>',
                rcube::Q($this->plugin->gettext('scope')), $select->show($join)
            );

            $out .= '<div id="rules"'.($any ? ' style="display: none"' : '').'>';
            $out .= "\n<fieldset><legend>" . rcube::Q($this->plugin->gettext('rules')) . "</legend>\n";
        }
        else {
            $out .= '<br><fieldset><legend>' . rcube::Q($this->plugin->gettext('messagesrules')) . "</legend>\n";

            // any, allof, anyof radio buttons
            $field_id = '_allof';
            $input_join = new html_radiobutton(['name' => '_join', 'id' => $field_id, 'value' => 'allof',
                'onclick' => 'rule_join_radio(\'allof\')', 'class' => 'radio']);

            if (isset($scr) && !$any) {
                $input_join = $input_join->show($scr['join'] ? 'allof' : '');
            }
            else {
                $input_join = $input_join->show();
            }

            $out .= $input_join . html::label($field_id, rcube::Q($this->plugin->gettext('filterallof')));

            $field_id = '_anyof';
            $input_join = new html_radiobutton(['name' => '_join', 'id' => $field_id, 'value' => 'anyof',
                'onclick' => 'rule_join_radio(\'anyof\')', 'class' => 'radio']);

            if (isset($scr) && !$any) {
                $input_join = $input_join->show($scr['join'] ? '' : 'anyof');
            }
            else {
                $input_join = $input_join->show('anyof'); // default
            }

            $out .= $input_join . html::label($field_id, rcube::Q($this->plugin->gettext('filteranyof')));

            $field_id = '_any';
            $input_join = new html_radiobutton(['name' => '_join', 'id' => $field_id, 'value' => 'any',
                'onclick' => 'rule_join_radio(\'any\')', 'class' => 'radio']);

            $input_join = $input_join->show($any ? 'any' : '');

            $out .= $input_join . html::label($field_id, rcube::Q($this->plugin->gettext('filterany')));
            $out .= '<div id="rules"'.($any ? ' style="display: none"' : '').'>';
        }

        $rows_num = !empty($scr['tests']) ? count($scr['tests']) : 1;
        for ($x=0; $x<$rows_num; $x++) {
            $out .= $this->rule_div($fid, $x, true, $compact);
        }

        $out .= $compact ? "</fieldset>\n</div>\n" : "</div>\n</fieldset>\n";

        // actions
        $label = $this->plugin->gettext($compact ? 'actions' : 'messagesactions');
        $out .= '<fieldset><legend>' . rcube::Q($label) . "</legend>\n";

        $rows_num = isset($scr)  ? count($scr['actions']) : 1;

        $out .= '<div id="actions">';
        for ($x=0; $x<$rows_num; $x++) {
            $out .= $this->action_div($fid, $x);
        }
        $out .= "</div>\n";

        $out .= "</fieldset>\n";

        $this->print_tips();

        $this->rc->output->add_label(
            'managesieve.ruledeleteconfirm',
            'managesieve.actiondeleteconfirm'
        );
        $this->rc->output->set_env('rule_disabled', !empty($scr['disabled']));
        $this->rc->output->add_gui_object('sieveform', 'filterform');

        $attrib['name']   = 'filterform';
        $attrib['action'] = './';
        $attrib['method'] = 'post';

        $out = html::tag('form', $attrib, $out, ['name', 'action', 'method', 'class']);

        if (!$compact) {
            $out = str_replace('</form>', '', $out);
        }

        return $out;
    }

    function rule_div($fid, $id, $div = true, $compact = false)
    {
        if (isset($id) && isset($this->form)) {
            $rule = $this->form['tests'][$id];
        }
        else if (isset($id) && isset($this->script[$fid]['tests'][$id])) {
            $rule = $this->script[$fid]['tests'][$id];
        }
        else {
            $rule = ['test' => null];
        }

        if (isset($this->form['tests'])) {
            $rows_num = count($this->form['tests']);
        }
        else if (isset($this->script[$fid]['tests'])) {
            $rows_num = count($this->script[$fid]['tests']);
        }
        else {
            $rows_num = 0;
        }

        // headers select
        $select_header = new html_select(['name' => "_header[$id]", 'id' => 'header'.$id,
            'onchange' => 'rule_header_select(' .$id .')', 'class' => 'custom-select']);

        foreach ($this->headers as $index => $header) {
            $header = $this->rc->text_exists($index) ? $this->plugin->gettext($index) : $header;
            $select_header->add($header, $index);
        }
        $select_header->add($this->plugin->gettext('...'), '...');
        if (in_array('body', $this->exts)) {
            $select_header->add($this->plugin->gettext('body'), 'body');
        }
        $select_header->add($this->plugin->gettext('size'), 'size');
        if (in_array('spamtest', $this->exts)) {
            $select_header->add($this->plugin->gettext('spamtest'), 'spamtest');
        }
        if (in_array('date', $this->exts)) {
            $select_header->add($this->plugin->gettext('datetest'), 'date');
            $select_header->add($this->plugin->gettext('currdate'), 'currentdate');
        }
        if (in_array('variables', $this->exts)) {
            $select_header->add($this->plugin->gettext('string'), 'string');
        }
        if (in_array('duplicate', $this->exts)) {
            $select_header->add($this->plugin->gettext('message'), 'message');
        }

        $test = null;

        if (isset($rule['test'])) {
            if (in_array($rule['test'], ['header', 'address', 'envelope'])) {
                if (is_array($rule['arg1']) && count($rule['arg1']) == 1) {
                    $rule['arg1'] = $rule['arg1'][0];
                }

                $header  = !is_array($rule['arg1']) ? strtolower($rule['arg1']) : null;
                $matches = !is_array($rule['arg1']) && $header && isset($this->headers[$header]);
                $test    = $matches ? $header : '...';
            }
            else if ($rule['test'] == 'exists') {
                if (is_array($rule['arg']) && count($rule['arg']) == 1) {
                    $rule['arg'] = $rule['arg'][0];
                }

                $header  = !is_array($rule['arg']) ? strtolower($rule['arg']) : null;
                $matches = !is_array($rule['arg']) && $header && isset($this->headers[$header]);
                $test    = $matches ? $header : '...';
            }
            else if (in_array($rule['test'], ['size', 'spamtest', 'body', 'date', 'currentdate', 'string'])) {
                $test = $rule['test'];
            }
            else if (in_array($rule['test'], ['duplicate'])) {
                $test = 'message';
            }
            else if ($rule['test'] != 'true') {
                $test = '...';
            }
        }

        $tout = '<div class="flexbox">';
        $aout = $select_header->show($test);

        $custom  = null;
        $customv = null;

        // custom headers input
        if (isset($rule['test']) && in_array($rule['test'], ['header', 'address', 'envelope'])) {
            $custom = (array) $rule['arg1'];
            if (count($custom) == 1 && isset($this->headers[strtolower($custom[0])])) {
                $custom = null;
            }
        }
        else if (isset($rule['test']) && $rule['test'] == 'string') {
            $customv = (array) $rule['arg1'];
            if (count($customv) == 1 && isset($this->headers[strtolower($customv[0])])) {
                $customv = null;
            }
        }
        else if (isset($rule['test']) && $rule['test'] == 'exists') {
            $custom = (array) $rule['arg'];
            if (count($custom) == 1 && isset($this->headers[strtolower($custom[0])])) {
                $custom = null;
            }
        }

        // custom header and variable inputs
        $aout .= $this->list_input($id, 'custom_header', $custom, 15, false, [
                'disabled'    => !isset($custom),
                'class'       => $this->error_class($id, 'test', 'header', 'custom_header'),
                'placeholder' => $this->plugin->gettext('headername'),
                'title'       => $this->plugin->gettext('headername'),
            ]) . "\n";

        $aout .= $this->list_input($id, 'custom_var', $customv, 15, false, [
                'disabled' => !isset($customv),
                'class'    => $this->error_class($id, 'test', 'header', 'custom_var')
            ]) . "\n";

        $test       = self::rule_test($rule);
        $target     = '';
        $sizetarget = null;
        $sizeitem   = null;

        // target(s) input
        if (in_array($rule['test'], ['header', 'address', 'envelope', 'string'])) {
            $target = $rule['arg2'];
        }
        else if (in_array($rule['test'], ['body', 'date', 'currentdate', 'spamtest'])) {
            $target = $rule['arg'];
        }
        else if ($rule['test'] == 'size') {
            if (preg_match('/^([0-9]+)(K|M|G)?$/', $rule['arg'], $matches)) {
                $sizetarget = $matches[1];
                $sizeitem   = $matches[2];
            }
            else {
                $sizetarget = $rule['arg'];
                $sizeitem   = $rule['item'];
            }
        }

        // (current)date part select
        if (in_array('date', $this->exts) || in_array('currentdate', $this->exts)) {
            $date_parts = ['date', 'iso8601', 'std11', 'julian', 'time',
                'year', 'month', 'day', 'hour', 'minute', 'second', 'weekday', 'zone'];
            $select_dp = new html_select([
                    'name'  => "_rule_date_part[$id]",
                    'id'    => 'rule_date_part'.$id,
                    'style' => in_array($rule['test'], ['currentdate', 'date']) && !preg_match('/^(notcount|count)-/', $test) ? '' : 'display:none',
                    'class' => 'datepart_selector custom-select',
            ]);

            foreach ($date_parts as $part) {
                $select_dp->add(rcube::Q($this->plugin->gettext($part)), $part);
            }

            $aout .= $select_dp->show($rule['test'] == 'currentdate' || $rule['test'] == 'date' ? $rule['part'] : '');
        }

        // message test select (e.g. duplicate)
        if (in_array('duplicate', $this->exts)) {
            $select_msg = new html_select([
                    'name'  => "_rule_message[$id]",
                    'id'    => 'rule_message'.$id,
                    'style' => in_array($rule['test'], ['duplicate']) ? '' : 'display:none',
                    'class' => 'message_selector custom-select',
            ]);

            $select_msg->add(rcube::Q($this->plugin->gettext('duplicate')), 'duplicate');
            $select_msg->add(rcube::Q($this->plugin->gettext('notduplicate')), 'notduplicate');

            $tout .= $select_msg->show($test);
        }

        $tout .= $this->match_type_selector('rule_op', $id, $test, $rule['test']);
        $tout .= $this->list_input($id, 'rule_target', $target, null, false, [
                'disabled' => in_array($rule['test'], ['size', 'exists', 'duplicate', 'spamtest']),
                'class'    => $this->error_class($id, 'test', 'target', 'rule_target')
            ]) . "\n";

        $select_size_op = new html_select([
                'name'  => "_rule_size_op[$id]",
                'id'    => 'rule_size_op'.$id,
                'class' => 'input-group-prepend custom-select'
        ]);
        $select_size_op->add(rcube::Q($this->plugin->gettext('filterover')), 'over');
        $select_size_op->add(rcube::Q($this->plugin->gettext('filterunder')), 'under');

        $select_size_item = new html_select([
                'name'  => "_rule_size_item[$id]",
                'id'    => 'rule_size_item'.$id,
                'class' => 'input-group-append custom-select'
        ]);
        foreach (['', 'K', 'M', 'G'] as $unit) {
            $select_size_item->add($this->plugin->gettext($unit . 'B'), $unit);
        }

        $tout .= '<div id="rule_size' .$id. '" class="input-group" style="display:' . ($rule['test']=='size' ? 'inline' : 'none') .'">';
        $tout .= $select_size_op->show($rule['test']=='size' ? $rule['type'] : '');
        $tout .= html::tag('input', [
                'type'  => 'text',
                'name'  => "_rule_size_target[$id]",
                'id'    => 'rule_size_i'.$id,
                'value' => $sizetarget,
                'size'  => 10,
                'class' => $this->error_class($id, 'test', 'sizetarget', 'rule_size_i'),
        ]);
        $tout .= "\n" . $select_size_item->show($sizeitem);
        $tout .= '</div>';
        $tout .= '</div>';

        if (in_array('relational', $this->exts)) {
            $select_spamtest_op = new html_select([
                    'name'     => "_rule_spamtest_op[$id]",
                    'id'       => 'rule_spamtest_op' . $id,
                    'class'    => 'input-group-prepend custom-select',
                    'onchange' => 'rule_spamtest_select(' . $id .')'
            ]);
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestisunknown')), '');
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestisgreaterthan')), 'value-gt');
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestisgreaterthanequal')), 'value-ge');
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestislessthan')), 'value-lt');
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestislessthanequal')), 'value-le');
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestequals')), 'value-eq');
            $select_spamtest_op->add(rcube::Q($this->plugin->gettext('spamtestnotequals')), 'value-ne');

            $select_spamtest_target = new html_select([
                    'name'  => "_rule_spamtest_target[$id]",
                    'id'    => 'rule_spamtest_target' . $id,
                    'class' => 'input-group-append custom-select'
            ]);
            $select_spamtest_target->add(rcube::Q("0%"), '1');
            $select_spamtest_target->add(rcube::Q("20%"), '2');
            $select_spamtest_target->add(rcube::Q("30%"), '3');
            $select_spamtest_target->add(rcube::Q("40%"), '4');
            $select_spamtest_target->add(rcube::Q("50%"), '5');
            $select_spamtest_target->add(rcube::Q("60%"), '6');
            $select_spamtest_target->add(rcube::Q("70%"), '7');
            $select_spamtest_target->add(rcube::Q("80%"), '8');
            $select_spamtest_target->add(rcube::Q("90%"), '9');
            $select_spamtest_target->add(rcube::Q("100%"), '10');

            $tout .= '<div id="rule_spamtest' . $id . '" class="input-group" style="display:' . ($rule['test'] == 'spamtest' ? 'inline' : 'none') .'">';
            $tout .= $select_spamtest_op->show($rule['test'] == 'spamtest' && $target > 0 ? $rule['type'] : '');
            $tout .= $select_spamtest_target->show($rule['test'] == 'spamtest' ? $target : '');

            $tout .= '</div>';
        }
        // Advanced modifiers (address, envelope)
        $select_mod = new html_select([
                'name'     => "_rule_mod[$id]",
                'id'       => 'rule_mod_op' . $id,
                'class'    => 'custom-select',
                'onchange' => 'rule_mod_select(' .$id .')'
        ]);
        $select_mod->add(rcube::Q($this->plugin->gettext('none')), '');
        $select_mod->add(rcube::Q($this->plugin->gettext('address')), 'address');
        if (in_array('envelope', $this->exts)) {
            $select_mod->add(rcube::Q($this->plugin->gettext('envelope')), 'envelope');
        }

        $select_type = new html_select([
                'name'  => "_rule_mod_type[$id]",
                'id'    => 'rule_mod_type' . $id,
                'class' => 'custom-select',
        ]);
        $select_type->add(rcube::Q($this->plugin->gettext('allparts')), 'all');
        $select_type->add(rcube::Q($this->plugin->gettext('domain')), 'domain');
        $select_type->add(rcube::Q($this->plugin->gettext('localpart')), 'localpart');
        if (in_array('subaddress', $this->exts)) {
            $select_type->add(rcube::Q($this->plugin->gettext('user')), 'user');
            $select_type->add(rcube::Q($this->plugin->gettext('detail')), 'detail');
        }

        $need_mod = !in_array($rule['test'], ['size', 'spamtest', 'body', 'date', 'currentdate', 'duplicate', 'string']);
        $mout = '<div id="rule_mod' .$id. '" class="adv input-group"' . (!$need_mod ? ' style="display:none"' : '') . '>';
        $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('modifier'))));
        $mout .= $select_mod->show($rule['test']);
        $mout .= '</div>';
        $mout .= '<div id="rule_mod_type' . $id . '" class="adv input-group"';
        $mout .= (!in_array($rule['test'], ['address', 'envelope']) ? ' style="display:none"' : '') . '>';
        $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('modtype'))));
        $mout .= $select_type->show($rule['part'] ?? null);
        $mout .= '</div>';

        // Advanced modifiers (comparators)
        $need_comp = $rule['test'] != 'size' && $rule['test'] != 'spamtest' && $rule['test'] != 'duplicate';
        $mout .= '<div id="rule_comp' .$id. '" class="adv input-group"' . (!$need_comp ? ' style="display:none"' : '') . '>';
        $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('comparator'))));
        $mout .= $this->comparator_selector($rule['comparator'] ?? null, 'rule_comp', $id);
        $mout .= '</div>';

        // Advanced modifiers (mime)
        if (in_array('mime', $this->exts)) {
            $need_mime   = !$rule || in_array($rule['test'], ['header', 'address', 'exists']);
            $mime_type   = '';
            $select_mime = new html_select([
                    'name'  => "_rule_mime_type[$id]",
                    'id'    => 'rule_mime_type' . $id,
                    'style' => 'min-width:8em', 'onchange' => 'rule_mime_select(' . $id . ')',
                    'class' => 'custom-select',
            ]);
            $select_mime->add('-', '');

            foreach (['contenttype', 'type', 'subtype', 'param'] as $val) {
                if (isset($rule['mime-' . $val])) {
                    $mime_type = $val;
                }

                $select_mime->add(rcube::Q($this->plugin->gettext('mime-' . $val)), $val);
            }

            $select_mime_part = new html_select([
                    'name'  => "_rule_mime_part[$id]",
                    'id'    => 'rule_mime_part' . $id,
                    'class' => 'custom-select',
            ]);
            $select_mime_part->add(rcube::Q($this->plugin->gettext('mime-message')), '');
            $select_mime_part->add(rcube::Q($this->plugin->gettext('mime-anychild')), 'anychild');

            $mout .= '<div id="rule_mime_part' .$id. '" class="adv input-group"' . (!$need_mime ? ' style="display:none"' : '') . '>';
            $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('mimepart'))));
            $mout .= $select_mime_part->show(!empty($rule['mime-anychild']) ? 'anychild' : '');
            $mout .= '</div>';
            $mout .= '<div id="rule_mime' .$id. '" class="adv input-group"' . (!$need_mime ? ' style="display:none"' : '') . '>';
            $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('mime'))));
            $mout .= $select_mime->show($mime_type);
            $mout .= $this->list_input($id, 'rule_mime_param', $rule['mime-param'] ?? null,
                30, $mime_type != 'param', ['class' => $this->error_class($id, 'test', 'mime_param', 'rule_mime_param')]
            );
            $mout .= '</div>';
        }

        // Advanced modifiers (body transformations)
        $select_mod = new html_select([
                'name'     => "_rule_trans[$id]",
                'id'       => 'rule_trans_op' . $id,
                'class'    => 'custom-select',
                'onchange' => 'rule_trans_select(' .$id .')'
        ]);
        $select_mod->add(rcube::Q($this->plugin->gettext('text')), 'text');
        $select_mod->add(rcube::Q($this->plugin->gettext('undecoded')), 'raw');
        $select_mod->add(rcube::Q($this->plugin->gettext('contenttype')), 'content');

        $rule_content = '';
        if (isset($rule['content'])) {
            $rule_content = is_array($rule['content']) ? implode(',', $rule['content']) : $rule['content'];
        }

        $mout .= '<div id="rule_trans' .$id. '" class="adv input-group"' . ($rule['test'] != 'body' ? ' style="display:none"' : '') . '>';
        $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('modifier'))));
        $mout .= $select_mod->show($rule['part'] ?? null);
        $mout .= html::tag('input', [
                'type'  => 'text',
                'name'  => "_rule_trans_type[$id]",
                'id'    => 'rule_trans_type'.$id,
                'value' => $rule_content,
                'size'  => 20,
                'style' => !isset($rule['part']) || $rule['part'] != 'content' ? 'display:none' : '',
                'class' => $this->error_class($id, 'test', 'part', 'rule_trans_type'),
        ]);
        $mout .= '</div>';

        // Date header
        if (in_array('date', $this->exts)) {
            $mout .= '<div id="rule_date_header_div' .$id. '" class="adv input-group"'. ($rule['test'] != 'date' ? ' style="display:none"' : '') .'>';
            $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('dateheader'))));
            $mout .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_rule_date_header[$id]",
                    'id'    => 'rule_date_header' . $id,
                    'value' => $rule['test'] == 'date' ? $rule['header'] : '',
                    'size'  => 15,
                    'class' => $this->error_class($id, 'test', 'dateheader', 'rule_date_header'),
                ]);
            $mout .= '</div>';
        }

        // Index
        if (in_array('index', $this->exts)) {
            $need_index = in_array($rule['test'], ['header', ', address', 'date']);
            $mout .= '<div id="rule_index_div' .$id. '" class="adv input-group"'. (!$need_index ? ' style="display:none"' : '') .'>';
            $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('index'))));
            $mout .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_rule_index[$id]",
                    'id'    => 'rule_index' . $id,
                    'value' => !empty($rule['index']) ? intval($rule['index']) : '',
                    'size'  => 3,
                    'class' => $this->error_class($id, 'test', 'index', 'rule_index'),
            ]);
            $mout .= html::label('input-group-append',
                    html::tag('input', [
                        'type'    => 'checkbox',
                        'name'    => "_rule_index_last[$id]",
                        'id'      => 'rule_index_last' . $id,
                        'value'   => 1,
                        'checked' => !empty($rule['last']),
                    ]) . rcube::Q($this->plugin->gettext('indexlast')));
            $mout .= '</div>';
        }

        // Duplicate
        if (in_array('duplicate', $this->exts)) {
            $need_duplicate = $rule['test'] == 'duplicate';
            $mout .= '<div id="rule_duplicate_div' .$id. '" class="adv"'. (!$need_duplicate ? ' style="display:none"' : '') .'>';

            foreach (['handle', 'header', 'uniqueid'] as $unit) {
                $mout .= '<div class="input-group">';
                $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('duplicate.' . $unit))));
                $mout .= html::tag('input', [
                        'type'  => 'text',
                        'name'  => '_rule_duplicate_' . $unit . "[$id]",
                        'id'    => 'rule_duplicate_' . $unit . $id,
                        'value' => $rule[$unit] ?? '',
                        'size'  => 30,
                        'class' => $this->error_class($id, 'test', 'duplicate_' . $unit, 'rule_duplicate_' . $unit),
                ]);
                $mout .= '</div>';
            }

            $mout .= '<div class="input-group">';
            $mout .= html::span('label input-group-prepend', html::span('input-group-text', rcube::Q($this->plugin->gettext('duplicate.seconds'))));
            $mout .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_rule_duplicate_seconds[$id]",
                    'id'    => 'rule_duplicate_seconds' . $id,
                    'value' => $rule['seconds'] ?? '',
                    'size'  => 6,
                    'class' => $this->error_class($id, 'test', 'duplicate_seconds', 'rule_duplicate_seconds'),
            ]);
            $mout .= html::label('input-group-append',
                html::tag('input', [
                    'type'    => 'checkbox',
                    'name'    => "_rule_duplicate_last[$id]",
                    'id'      => 'rule_duplicate_last' . $id,
                    'value'   => 1,
                    'checked' => !empty($rule['last']),
                ]) . rcube::Q($this->plugin->gettext('duplicate.last')));
            $mout .= '</div>';
            $mout .= '</div>';
        }

        $add_title = rcube::Q($this->plugin->gettext('add'));
        $del_title = rcube::Q($this->plugin->gettext('del'));
        $adv_title = rcube::Q($this->plugin->gettext('advancedopts'));

        // Build output table
        $out = $div ? '<div class="rulerow" id="rulerow' .$id .'">'."\n" : '';
        $out .= '<table class="compact-table"><tr>';

        if (!$compact) {
            $out .= '<td class="advbutton">';
            $out .= sprintf('<a href="#" id="ruleadv%s" title="%s" onclick="rule_adv_switch(%s, this); return false" class="show">'
                . '<span class="inner">%s</span></a>', $id, $adv_title, $id, $adv_title);
            $out .= '</td>';
        }

        $out .= '<td class="rowactions"><div class="flexbox">' . $aout . '</div></td>';
        $out .= '<td class="rowtargets">' . $tout . "\n";
        $out .= '<div id="rule_advanced' .$id. '" style="display:none" class="advanced">' . $mout . '</div>';
        $out .= '</td>';
        $out .= '<td class="rowbuttons">';
        if ($compact) {
            $out .= sprintf('<a href="#" id="ruleadv%s" title="%s" onclick="rule_adv_switch(%s, this); return false" class="advanced show">'
                . '<span class="inner">%s</span></a>', $id, $adv_title, $id, $adv_title);
        }
        $out .= sprintf('<a href="#" id="ruleadd%s" title="%s" onclick="rcmail.managesieve_ruleadd(\'%s\'); return false" class="button create add">'
            . '<span class="inner">%s</span></a>', $id, $add_title, $id, $add_title);
        $out .= sprintf('<a href="#" id="ruledel%s" title="%s" onclick="rcmail.managesieve_ruledel(\'%s\'); return false" class="button delete del%s">'
            . '<span class="inner">%s</span></a>', $id, $del_title, $id, ($rows_num < 2 ? ' disabled' : ''), $del_title);
        $out .= '</td>';

        $out .= '</tr></table>';
        $out .= $div ? "</div>\n" : '';

        return $out;
    }

    private static function rule_test(&$rule)
    {
        // first modify value/count tests with 'not' keyword
        // we'll revert the meaning of operators
        if (!empty($rule['not']) && !empty($rule['type'])
            && preg_match('/^(count|value)-([gteqnl]{2})/', $rule['type'], $m)
        ) {
            $rule['not'] = false;

            switch ($m[2]) {
            case 'gt': $rule['type'] = $m[1] . '-le'; break;
            case 'ge': $rule['type'] = $m[1] . '-lt'; break;
            case 'lt': $rule['type'] = $m[1] . '-ge'; break;
            case 'le': $rule['type'] = $m[1] . '-gt'; break;
            case 'eq': $rule['type'] = $m[1] . '-ne'; break;
            case 'ne': $rule['type'] = $m[1] . '-eq'; break;
            }
        }
        else if (!empty($rule['not']) && !empty($rule['test']) && $rule['test'] == 'size') {
            $rule['not']  = false;
            $rule['type'] = $rule['type'] == 'over' ? 'under' : 'over';
        }

        $set  = ['header', 'address', 'envelope', 'body', 'date', 'currentdate', 'string'];
        $test = null;

        // build test string supported by select element
        if (!empty($rule['size'])) {
            $test = $rule['type'];
        }
        else if (!empty($rule['test']) && in_array($rule['test'], $set)) {
            $test = (!empty($rule['not']) ? 'not' : '') . ($rule['type'] ?: 'is');
        }
        else if (!empty($rule['test'])) {
            $test = (!empty($rule['not']) ? 'not' : '') . $rule['test'];
        }

        return $test;
    }

    function action_div($fid, $id, $div = true)
    {
        if (isset($id) && isset($this->form)) {
            $action = $this->form['actions'][$id];
        }
        else if (isset($id) && isset($this->script[$fid]['actions'][$id])) {
            $action = $this->script[$fid]['actions'][$id];
        }
        else {
            $action = ['type' => null];
        }

        if (isset($this->form['actions'])) {
            $rows_num = count($this->form['actions']);
        }
        else if (isset($this->script[$fid]['actions'])) {
            $rows_num = count($this->script[$fid]['actions']);
        }
        else {
            $rows_num = 0;
        }

        $out = $div ? '<div class="actionrow" id="actionrow' .$id .'">'."\n" : '';

        $out .= '<table class="compact-table"><tr><td class="rowactions">';

        // action select
        $select_action = new html_select([
                'name'     => "_action_type[$id]",
                'id'       => 'action_type' . $id,
                'class'    => 'custom-select',
                'onchange' => "action_type_select($id)"
        ]);
        if (in_array('fileinto', $this->exts)) {
            $select_action->add($this->plugin->gettext('messagemoveto'), 'fileinto');
        }
        if (in_array('fileinto', $this->exts) && in_array('copy', $this->exts)) {
            $select_action->add($this->plugin->gettext('messagecopyto'), 'fileinto_copy');
        }
        if ($action['type'] == 'redirect' || !in_array('redirect', $this->disabled_actions)) {
            $select_action->add($this->plugin->gettext('messageredirect'), 'redirect');
            if (in_array('copy', $this->exts)) {
                $select_action->add($this->plugin->gettext('messagesendcopy'), 'redirect_copy');
            }
        }
        if (in_array('reject', $this->exts)) {
            $select_action->add($this->plugin->gettext('messagediscard'), 'reject');
        }
        else if (in_array('ereject', $this->exts)) {
            $select_action->add($this->plugin->gettext('messagediscard'), 'ereject');
        }
        if (in_array('vacation', $this->exts)) {
            $select_action->add($this->plugin->gettext('messagereply'), 'vacation');
        }
        $select_action->add($this->plugin->gettext('messagedelete'), 'discard');
        if (in_array('imapflags', $this->exts) || in_array('imap4flags', $this->exts)) {
            $select_action->add($this->plugin->gettext('setflags'), 'setflag');
            $select_action->add($this->plugin->gettext('addflags'), 'addflag');
            $select_action->add($this->plugin->gettext('removeflags'), 'removeflag');
        }
        if (in_array('editheader', $this->exts)) {
            $select_action->add($this->plugin->gettext('addheader'), 'addheader');
            $select_action->add($this->plugin->gettext('deleteheader'), 'deleteheader');
        }
        if (in_array('variables', $this->exts)) {
            $select_action->add($this->plugin->gettext('setvariable'), 'set');
        }
        if (in_array('enotify', $this->exts) || in_array('notify', $this->exts)) {
            $select_action->add($this->plugin->gettext('notify'), 'notify');
        }
        $select_action->add($this->plugin->gettext('messagekeep'), 'keep');
        $select_action->add($this->plugin->gettext('rulestop'), 'stop');

        $select_type = $action['type'];
        if (in_array($action['type'], ['fileinto', 'redirect']) && !empty($action['copy'])) {
            $select_type .= '_copy';
        }

        $out .= $select_action->show($select_type);
        $out .= '</td>';

        // actions target inputs
        $out .= '<td class="rowtargets">';

        // force domain selection in redirect email input
        $domains = (array) $this->rc->config->get('managesieve_domains');

        if (!empty($domains)) {
            sort($domains);

            $domain_select = new html_select([
                    'name'  => "_action_target_domain[$id]",
                    'id'    => 'action_target_domain' . $id,
                    'class' => 'custom-select',
            ]);

            $domain_select->add(array_combine($domains, $domains));

            if ($action['type'] == 'redirect') {
                $parts = explode('@', $action['target']);
                if (!empty($parts)) {
                    $action['domain'] = array_pop($parts);
                    $action['target'] = implode('@', $parts);
                }
            }
        }

        // redirect target
        $out .= '<span id="redirect_target' . $id . '" class="input-group" style="white-space:nowrap;'
            . ' display:' . ($action['type'] == 'redirect' ? '' : 'none') . '">'
            . html::tag('input', [
                'type'  => 'text',
                'name'  => "_action_target[$id]",
                'id'    => 'action_target' . $id,
                'value' => $action['type'] == 'redirect' ? $action['target'] : '',
                'size'  => !empty($domains) ? 20 : 35,
                'class' => $this->error_class($id, 'action', 'target', 'action_target'),
            ]);
        $out .= isset($domain_select) ? '<span class="input-group-append input-group-prepend">'
            . ' <span class="input-group-text">@</span> </span>'
            . $domain_select->show(!empty($action['domain']) ? $action['domain'] : '') : '';
        $out .= '</span>';

        // (e)reject target
        $out .= html::tag('textarea', [
                'name'  => '_action_target_area[' . $id . ']',
                'id'    => 'action_target_area' . $id,
                'rows'  => 3,
                'cols'  => 35,
                'class' => $this->error_class($id, 'action', 'targetarea', 'action_target_area'),
                'style' => 'display:' . (in_array($action['type'], ['reject', 'ereject']) ? 'inline' : 'none'),
            ],
            (in_array($action['type'], ['reject', 'ereject']) ? rcube::Q($action['target'], 'strict', false) : '')
        );

        // vacation
        $vsec      = in_array('vacation-seconds', $this->exts);
        $auto_addr = $this->rc->config->get('managesieve_vacation_addresses_init');
        $from_addr = $this->rc->config->get('managesieve_vacation_from_init');

        if (empty($action)) {
            if ($auto_addr) {
                $action['addresses'] = $this->user_emails();
            }
            if ($from_addr) {
                $default_identity = $this->rc->user->list_emails(true);
                $action['from']   = format_email_recipient($default_identity['email'], $default_identity['name']);
            }
        }
        else if (!empty($action['from'])) {
            $from = rcube_mime::decode_address_list($action['from'], null, true, RCUBE_CHARSET);
            foreach ((array) $from as $idx => $addr) {
                $from[$idx] = format_email_recipient($addr['mailto'], $addr['name']);
            }
            if (!empty($from)) {
                $action['from'] = implode(', ', $from);
            }
        }

        $action_subject = '';
        if (isset($action['subject'])) {
            $action_subject = is_array($action['subject']) ? implode(', ', $action['subject']) : $action['subject'];
        }

        $out .= '<div id="action_vacation' .$id.'" style="display:' .($action['type'] == 'vacation' ? 'inline' : 'none') .'" class="composite">';
        $out .= '<span class="label">'. rcube::Q($this->plugin->gettext('vacationreason')) .'</span><br>';
        $out .= html::tag('textarea', [
                'name'  => "_action_reason[$id]",
                'id'   => 'action_reason' . $id,
                'rows'  => 3,
                'cols'  => 35,
                'class' => $this->error_class($id, 'action', 'reason', 'action_reason'),
            ], rcube::Q($action['reason'] ?? '', 'strict', false));
        $out .= '<br><span class="label">' .rcube::Q($this->plugin->gettext('vacationsubject')) . '</span><br>';
        $out .= html::tag('input', [
                'type'  => 'text',
                'name'  => "_action_subject[$id]",
                'id'    => 'action_subject' . $id,
                'value' => $action_subject,
                'size'  => 35,
                'class' => $this->error_class($id, 'action', 'subject', 'action_subject'),
        ]);
        $out .= '<br><span class="label">' .rcube::Q($this->plugin->gettext('vacationfrom')) . '</span><br>';
        $out .= html::tag('input', [
                'type'  => 'text',
                'name'  => "_action_from[$id]",
                'id'    => 'action_from' . $id,
                'value' => $action['from'] ?? '',
                'size'  => 35,
                'class' => $this->error_class($id, 'action', 'from', 'action_from'),
        ]);
        $out .= '<br><span class="label">' .rcube::Q($this->plugin->gettext('vacationaddr')) . '</span><br>';
        $out .= $this->list_input($id, 'action_addresses', $action['addresses'] ?? null,
                30, false, ['class' => $this->error_class($id, 'action', 'addresses', 'action_addresses')]
            )
            . html::a(['href' => '#', 'onclick' => rcmail_output::JS_OBJECT_NAME . ".managesieve_vacation_addresses($id)"],
                rcube::Q($this->plugin->gettext('filladdresses')));
        $out .= '<br><span class="label">' . rcube::Q($this->plugin->gettext('vacationinterval')) . '</span><br>';
        $out .= '<div class="input-group">' . html::tag('input', [
                'type'  => 'text',
                'name'  => "_action_interval[$id]",
                'id'    => 'action_interval' . $id,
                'value' => rcube_sieve_vacation::vacation_interval($action, $this->exts),
                'size'  => 2,
                'class' => $this->error_class($id, 'action', 'interval', 'action_interval'),
        ]);
        if ($vsec) {
            $interval_select = new html_select([
                    'name'  => "_action_interval_type[$id]",
                    'class' => 'input-group-append custom-select'
            ]);
            $interval_select->add($this->plugin->gettext('days'), 'days');
            $interval_select->add($this->plugin->gettext('seconds'), 'seconds');
            $out .= $interval_select->show(isset($action['seconds']) ? 'seconds' : 'days');
        }
        else {
            $out .= "\n" . html::span('input-group-append', html::span('input-group-text', $this->plugin->gettext('days')));
        }
        $out .= '</div></div>';

        // flags
        $flags = [
            'read'      => '\\Seen',
            'answered'  => '\\Answered',
            'flagged'   => '\\Flagged',
            'deleted'   => '\\Deleted',
            'draft'     => '\\Draft',
        ];

        $flags_target   = isset($action['target']) ? (array) $action['target'] : [];
        $custom_flags   = [];
        $is_flag_action = preg_match('/^(set|add|remove)flag$/', (string) $action['type']);

        if ($is_flag_action) {
            $custom_flags = array_filter($flags_target, function($v) use($flags) {
                return !in_array_nocase($v, $flags);
            });
        }

        $flout = '';

        foreach ($flags as $fidx => $flag) {
            $flout .= html::label(null, html::tag('input', [
                    'type'    => 'checkbox',
                    'name'    => "_action_flags[$id][]",
                    'value'   => $flag,
                    'checked' => $is_flag_action && in_array_nocase($flag, $flags_target),
                ])
                . rcube::Q($this->plugin->gettext('flag'.$fidx))) . '<br>';
        }

        $flout .= $this->list_input($id, 'action_flags', $custom_flags, null, false, [
                'class' => $this->error_class($id, 'action', 'flag', 'action_flags_flag'),
                'id'    => "action_flags_flag{$id}"
        ]);

        $out .= html::div([
                'id'    => 'action_flags' . $id,
                'style' => 'display:' . ($is_flag_action ? 'inline' : 'none'),
                'class' => trim('checklist ' . $this->error_class($id, 'action', 'flags', 'action_flags')),
            ], $flout);

        // set variable
        $set_modifiers = [
            'lower',
            'upper',
            'lowerfirst',
            'upperfirst',
            'quotewildcard',
            'length'
        ];

        $out .= '<div id="action_set' .$id.'" class="composite" style="display:' .($action['type'] == 'set' ? 'inline' : 'none') .'">';
        foreach (['name', 'value'] as $unit) {
            $out .= '<span class="label">' .rcube::Q($this->plugin->gettext('setvar' . $unit)) . '</span><br>';
            $out .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => '_action_var' . $unit . '[' . $id . ']',
                    'id'    => 'action_var' . $unit . $id,
                    'value' => $action[$unit] ?? '',
                    'size'  => 35,
                    'class' => $this->error_class($id, 'action', $unit, 'action_var' . $unit),
            ]);
            $out .= '<br>';
        }

        $smout = '';
        foreach ($set_modifiers as $s_m) {
            $smout .= html::label(null,
                html::tag('input', [
                    'type'    => 'checkbox',
                    'name'    => "_action_varmods[$id][]",
                    'value'   => $s_m,
                    'checked' => array_key_exists($s_m, (array) $action) && !empty($action[$s_m]),
                ])
                . rcube::Q($this->plugin->gettext('var' . $s_m))
            );
        }

        $out .= '<span class="label">' .rcube::Q($this->plugin->gettext('setvarmodifiers')) . '</span>';
        $out .= html::div('checklist', $smout);
        $out .= '</div>';

        // notify
        $notify_methods     = (array) $this->rc->config->get('managesieve_notify_methods');
        $importance_options = $this->notify_importance_options;

        if (empty($notify_methods)) {
            $notify_methods = $this->notify_methods;
        }

        $method = $target = '';

        if (!empty($action['method'])) {
            list($method, $target) = explode(':', $action['method'], 2);
            $method = strtolower($method);
        }

        if ($method && !in_array($method, $notify_methods)) {
            $notify_methods[] = $method;
        }

        $select_method = new html_select([
            'name'  => "_action_notifymethod[$id]",
            'id'    => "_action_notifymethod$id",
            'class' => 'input-group-prepend custom-select ' . $this->error_class($id, 'action', 'method', 'action_notifymethod'),
        ]);

        foreach ($notify_methods as $m_n) {
            $select_method->add(rcube::Q($this->rc->text_exists('managesieve.notifymethod'.$m_n) ? $this->plugin->gettext('managesieve.notifymethod'.$m_n) : $m_n), $m_n);
        }

        $select_importance = new html_select([
            'name'  => "_action_notifyimportance[$id]",
            'id'    => "_action_notifyimportance$id",
            'class' => 'custom-select ' . $this->error_class($id, 'action', 'importance', 'action_notifyimportance')
        ]);

        foreach ($importance_options as $io_v => $io_n) {
            $select_importance->add(rcube::Q($this->plugin->gettext($io_n)), $io_v);
        }

        // @TODO: nice UI for mailto: (other methods too) URI parameters
        $out .= '<div id="action_notify' .$id.'" style="display:' .($action['type'] == 'notify' ? 'inline' : 'none') .'" class="composite">';
        $out .= '<span class="label">' .rcube::Q($this->plugin->gettext('notifytarget')) . '</span><br>';
        $out .= '<div class="input-group">';
        $out .= $select_method->show($method);
        $out .= html::tag('input', [
                'type'  => 'text',
                'name'  => "_action_notifytarget[$id]",
                'id'    => 'action_notifytarget' . $id,
                'value' => $target,
                'size'  => 25,
                'class' => $this->error_class($id, 'action', 'target', 'action_notifytarget'),
        ]);
        $out .= '</div>';
        $out .= '<br><span class="label">'. rcube::Q($this->plugin->gettext('notifymessage')) .'</span><br>';
        $out .= html::tag('textarea', [
                'name'  => "_action_notifymessage[$id]",
                'id'    => 'action_notifymessage' . $id,
                'rows'  => 3,
                'cols'  => 35,
                'class' => $this->error_class($id, 'action', 'message', 'action_notifymessage'),
            ], isset($action['message']) ? rcube::Q($action['message'], 'strict', false) : ''
        );
        if (in_array('enotify', $this->exts)) {
            $out .= '<br><span class="label">' .rcube::Q($this->plugin->gettext('notifyfrom')) . '</span><br>';
            $out .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_action_notifyfrom[$id]",
                    'id'    => 'action_notifyfrom' . $id,
                    'value' => $action['from'] ?? '',
                    'size'  => 35,
                    'class' => $this->error_class($id, 'action', 'from', 'action_notifyfrom'),
            ]);
        }
        $out .= '<br><span class="label">' . rcube::Q($this->plugin->gettext('notifyimportance')) . '</span><br>';
        $out .= $select_importance->show(!empty($action['importance']) ? (int) $action['importance'] : 2);
        $out .= '<div id="action_notifyoption_div' . $id  . '">'
            . '<span class="label">' . rcube::Q($this->plugin->gettext('notifyoptions')) . '</span><br>'
            . $this->list_input($id, 'action_notifyoption', !empty($action['options']) ? (array) $action['options'] : [],
                30, false, ['class' => $this->error_class($id, 'action', 'options', 'action_notifyoption')]
            ) . '</div>';
        $out .= '</div>';

        if (in_array('editheader', $this->exts)) {
            $action['pos'] = !empty($action['last']) ? 'last' : '';
            $pos1_selector = new html_select([
                    'name'  => "_action_addheader_pos[$id]",
                    'id'    => "action_addheader_pos$id",
                    'class' => 'custom-select ' . $this->error_class($id, 'action', 'pos', 'action_addheader_pos')
            ]);
            $pos1_selector->add($this->plugin->gettext('headeratstart'), '');
            $pos1_selector->add($this->plugin->gettext('headeratend'), 'last');
            $pos2_selector = new html_select([
                    'name'  => "_action_delheader_pos[$id]",
                    'id'    => "action_delheader_pos$id",
                    'class' => 'custom-select ' . $this->error_class($id, 'action', 'pos', 'action_delheader_pos')
            ]);
            $pos2_selector->add($this->plugin->gettext('headerfromstart'), '');
            $pos2_selector->add($this->plugin->gettext('headerfromend'), 'last');

            // addheader
            $out .= '<div id="action_addheader' .$id.'" style="display:' .($action['type'] == 'addheader' ? 'inline' : 'none') .'" class="composite">';
            $out .= '<label class="label" for="action_addheader_name' . $id .'">' .rcube::Q($this->plugin->gettext('headername')) . '</label><br>';
            $out .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_action_addheader_name[$id]",
                    'id'    => "action_addheader_name{$id}",
                    'value' => $action['name'] ?? '',
                    'size'  => 35,
                    'class' => $this->error_class($id, 'action', 'name', 'action_addheader_name'),
            ]);
            $out .= '<br><label class="label" for="action_addheader_value' . $id .'">'. rcube::Q($this->plugin->gettext('headervalue')) .'</label><br>';
            $out .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_action_addheader_value[$id]",
                    'id'    => "action_addheader_value{$id}",
                    'value' => $action['value'] ?? '',
                    'size'  => 35,
                    'class' => $this->error_class($id, 'action', 'value', 'action_addheader_value'),
            ]);
            $out .= '<br><label class="label" for="action_addheader_pos' . $id .'">'. rcube::Q($this->plugin->gettext('headerpos')) .'</label><br>';
            $out .= $pos1_selector->show($action['pos']);
            $out .= '</div>';

            // deleteheader
            $out .= '<div id="action_deleteheader' .$id.'" style="display:' .($action['type'] == 'deleteheader' ? 'inline' : 'none') .'" class="composite">';
            $out .= '<label class="label" for="action_delheader_name' . $id .'">' .rcube::Q($this->plugin->gettext('headername')) . '</label><br>';
            $out .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_action_delheader_name[$id]",
                    'id'    => "action_delheader_name{$id}",
                    'value' => $action['name'] ?? '',
                    'size'  => 35,
                    'class' => $this->error_class($id, 'action', 'name', 'action_delheader_name'),
            ]);
            $out .= '<br><label class="label" for="action_delheader_value' . $id .'">'. rcube::Q($this->plugin->gettext('headerpatterns')) .'</label><br>';
            $out .= $this->list_input($id, 'action_delheader_value', $action['value'] ?? null,
                null, false, ['class' => $this->error_class($id, 'action', 'value', 'action_delheader_value')]) . "\n";
            $out .= '<br><div class="adv input-group">';
            $out .= html::span('label input-group-prepend', html::label([
                    'class' => 'input-group-text', 'for' => 'action_delheader_op'.$id
                ], rcube::Q($this->plugin->gettext('headermatchtype'))));
            $out .= $this->match_type_selector('action_delheader_op', $id, $action['match-type'] ?? null, null, 'basic');
            $out .= '</div>';
            $out .= '<div class="adv input-group">';
            $out .= html::span('label input-group-prepend', html::label([
                    'class' => 'input-group-text', 'for' => 'action_delheader_comp_op'.$id
                ], rcube::Q($this->plugin->gettext('comparator'))));
            $out .= $this->comparator_selector($action['comparator'] ?? null, 'action_delheader_comp', $id);
            $out .= '</div>';
            $out .= '<br><label class="label" for="action_delheader_index' . $id .'">'. rcube::Q($this->plugin->gettext('headeroccurrence')) .'</label><br>';
            $out .= '<div class="input-group">';
            $out .= html::tag('input', [
                    'type'  => 'text',
                    'name'  => "_action_delheader_index[$id]",
                    'id'    => "action_delheader_index{$id}",
                    'value' => !empty($action['index']) ? intval($action['index']) : '',
                    'size'  => 5,
                    'class' => $this->error_class($id, 'action', 'index', 'action_delheader_index'),
            ]);
            $out .= ' ' . $pos2_selector->show($action['pos']);
            $out .= '</div></div>';
        }

        // mailbox select
        $additional = [];
        if ($action['type'] == 'fileinto' && isset($action['target'])) {
            // make sure non-existing (or unsubscribed) mailbox is listed (#1489956)
            if ($mailbox = $this->mod_mailbox($action['target'], 'out')) {
                $additional = [$mailbox];
            }
        }
        else {
            $mailbox = '';
        }

        $select = rcmail_action::folder_selector([
                'maxlength'  => 100,
                'name'       => "_action_mailbox[$id]",
                'id'         => "action_mailbox{$id}",
                'style'      => 'display:'.(empty($action['type']) || $action['type'] == 'fileinto' ? 'inline' : 'none'),
                'additional' => $additional,
        ]);
        $out .= $select->show($mailbox);
        $out .= '</td>';

        // add/del buttons
        $add_label = rcube::Q($this->plugin->gettext('add'));
        $del_label = rcube::Q($this->plugin->gettext('del'));
        $out .= '<td class="rowbuttons">';
        $out .= sprintf('<a href="#" id="actionadd%s" title="%s" onclick="rcmail.managesieve_actionadd(%s)" class="button create add">'
            . '<span class="inner">%s</span></a>', $id, $add_label, $id, $add_label);
        $out .= sprintf('<a href="#" id="actiondel%s" title="%s" onclick="rcmail.managesieve_actiondel(%s)" class="button delete del%s">'
            . '<span class="inner">%s</span></a>', $id, $del_label, $id, ($rows_num < 2 ? ' disabled' : ''), $del_label);
        $out .= '</td>';

        $out .= '</tr></table>';

        $out .= $div ? "</div>\n" : '';

        return $out;
    }

    /**
     * Generates a numeric identifier for a filter
     */
    protected function genid()
    {
        return preg_replace('/[^0-9]/', '', microtime(true));
    }

    /**
     * Trims and makes safe an input value
     *
     * @param string|array $str        Input value
     * @param bool         $allow_html Allow HTML tags in the value
     * @param bool         $trim       Trim the value
     *
     * @return string|array
     */
    protected function strip_value($str, $allow_html = false, $trim = true)
    {
        if (is_array($str)) {
            foreach ($str as $idx => $val) {
                $str[$idx] = $this->strip_value($val, $allow_html, $trim);

                if ($str[$idx] === '') {
                    unset($str[$idx]);
                }
            }

            return $str;
        }

        if (!$allow_html) {
            $str = strip_tags($str);
        }

        return $trim ? trim($str) : $str;
    }

    /**
     * Returns error class, if there's a form error "registered"
     */
    protected function error_class($id, $type, $target, $elem_prefix = '')
    {
        // TODO: tooltips
        if (
            ($type == 'test' && !empty($this->errors['tests'][$id][$target]))
            || ($type == 'action' && !empty($this->errors['actions'][$id][$target]))
        ) {
            $str = $this->errors[$type == 'test' ? 'tests' : 'actions'][$id][$target];
            $this->add_tip($elem_prefix . $id, $str, true);

            return 'error';
        }

        return '';
    }

    protected function add_tip($id, $str, $error = false)
    {
        $class = $error ? 'sieve error' : '';

        $this->tips[] = [$id, $class, $str];
    }

    protected function print_tips()
    {
        if (empty($this->tips)) {
            return;
        }

        $script = rcmail_output::JS_OBJECT_NAME.'.managesieve_tip_register('.json_encode($this->tips).');';
        $this->rc->output->add_script($script, 'docready');
    }

    protected function list_input($id, $name, $value, $size = null, $hidden = false, $attrib = [])
    {
        $value = (array) $value;
        $value = array_map(['rcube', 'Q'], $value);
        $value = implode("\n", $value);

        $attrib = array_merge($attrib, [
                'data-type' => 'list',
                'data-size' => $size,
                'data-hidden' => $hidden ?: null,
                'name'      => '_' . $name . '[' . $id . ']',
                'style'     => 'display:none',
        ]);

        if (empty($attrib['id'])) {
            $attrib['id'] = $name . $id;
        }

        return html::tag('textarea', $attrib, $value);
    }

    /**
     * Validate input for date part elements
     */
    protected function validate_date_part($type, $value)
    {
        // we do simple validation of date/part format
        switch ($type) {
            case 'date': // yyyy-mm-dd
                return preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $value);
            case 'iso8601':
                return preg_match('/^[0-9: .,ZWT+-]+$/', $value);
            case 'std11':
                return preg_match('/^((Sun|Mon|Tue|Wed|Thu|Fri|Sat),\s+)?[0-9]{1,2}\s+'
                    . '(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+[0-9]{2,4}\s+'
                    . '[0-9]{2}:[0-9]{2}(:[0-9]{2})?\s+([+-]*[0-9]{4}|[A-Z]{1,3})$/', $value);
            case 'julian':
                return preg_match('/^[0-9]+$/', $value);
            case 'time': // hh:mm:ss
                return preg_match('/^[0-9]{2}:[0-9]{2}:[0-9]{2}$/', $value);
            case 'year':
                return preg_match('/^[0-9]{4}$/', $value);
            case 'month':
                return preg_match('/^[0-9]{2}$/', $value) && $value > 0 && $value < 13;
            case 'day':
                return preg_match('/^[0-9]{2}$/', $value) && $value > 0 && $value < 32;
            case 'hour':
                return preg_match('/^[0-9]{2}$/', $value) && $value < 24;
            case 'minute':
                return preg_match('/^[0-9]{2}$/', $value) && $value < 60;
            case 'second':
                // According to RFC5260, seconds can be from 00 to 60
                return preg_match('/^[0-9]{2}$/', $value) && $value < 61;
            case 'weekday':
                return preg_match('/^[0-9]$/', $value) && $value < 7;
            case 'zone':
                return preg_match('/^[+-][0-9]{4}$/', $value);
        }
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
    protected function mod_mailbox($mailbox, $mode = 'out')
    {
        $delimiter         = $_SESSION['imap_delimiter'];
        $replace_delimiter = $this->rc->config->get('managesieve_replace_delimiter');
        $mbox_encoding     = $this->rc->config->get('managesieve_mbox_encoding', 'UTF7-IMAP');

        if ($mode == 'out') {
            $mailbox = rcube_charset::convert($mailbox, $mbox_encoding, 'UTF7-IMAP');
            if ($replace_delimiter && $replace_delimiter != $delimiter) {
                $mailbox = str_replace($replace_delimiter, $delimiter, $mailbox);
            }
        }
        else {
            $mailbox = rcube_charset::convert($mailbox, 'UTF7-IMAP', $mbox_encoding);
            if ($replace_delimiter && $replace_delimiter != $delimiter) {
                $mailbox = str_replace($delimiter, $replace_delimiter, $mailbox);
            }
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
            foreach ((array) $this->list as $idx => $name) {
                $_name = strtoupper($name);
                if ($_name == 'MASTER') {
                    $master_script = $name;
                }
                else if ($_name == 'MANAGEMENT') {
                    $management_script = $name;
                }
                else if ($_name == 'USER') {
                    $user_script = $name;
                }
                else {
                    continue;
                }

                unset($this->list[$idx]);
            }

            // get active script(s), read USER script
            if (!empty($user_script)) {
                $extension = $this->rc->config->get('managesieve_filename_extension', '.sieve');
                $filename_regex = '/'.preg_quote($extension, '/').'$/';
                $_SESSION['managesieve_user_script'] = $user_script;

                $this->sieve->load($user_script);

                foreach ($this->sieve->script->as_array() as $rules) {
                    foreach ($rules['actions'] as $action) {
                        if ($action['type'] == 'include' && empty($action['global'])) {
                            $name = preg_replace($filename_regex, '', $action['target']);
                            // make sure the script exist
                            if (in_array($name, $this->list)) {
                                $this->active[] = $name;
                            }
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
                    if (empty($this->master_file)) {
                        $this->sieve->activate('USER');
                    }
                }
            }
        }
        else if (!empty($this->list)) {
            // Get active script name
            if ($active = $this->sieve->get_active()) {
                $this->active = [$active];
            }

            // Hide scripts from config
            $exceptions = $this->rc->config->get('managesieve_filename_exceptions');
            if (!empty($exceptions)) {
                $this->list = array_diff($this->list, (array)$exceptions);
            }
        }

        // When no script listing allowed limit the list to the defined script
        if (in_array('list_sets', $this->disabled_actions)) {
            $script_name = $this->rc->config->get('managesieve_script_name', 'roundcube');
            $this->list = array_intersect($this->list, [$script_name]);
            $this->active = null;
            if (in_array($script_name, $this->list)) {
                // Because its the only allowed script make sure its active
                $this->activate_script($script_name);
            }
        }

        // reindex
        if (!empty($this->list)) {
            $this->list = array_values($this->list);
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
            $result      = false;

            // if the script is not active...
            if ($user_script && array_search($name, (array) $this->active) === false) {
                // ...rewrite USER file adding appropriate include command
                if ($this->sieve->load($user_script)) {
                    $script = $this->sieve->script->as_array();
                    $list   = [];
                    $regexp = '/' . preg_quote($extension, '/') . '$/';

                    // Create new include entry
                    $rule = [
                        'actions' => [
                            [
                                'target'   => $name . $extension,
                                'type'     => 'include',
                                'personal' => true,
                            ]
                        ]
                    ];

                    // get all active scripts for sorting
                    foreach ($script as $rid => $rules) {
                        foreach ($rules['actions'] as $action) {
                            if ($action['type'] == 'include' && empty($action['global'])) {
                                $target = $extension ? preg_replace($regexp, '', $action['target']) : $action['target'];
                                $list[] = $target;
                            }
                        }
                    }
                    $list[] = $name;

                    // Sort and find current script position
                    asort($list, SORT_LOCALE_STRING);
                    $list  = array_values($list);
                    $index = array_search($name, $list);

                    // add rule at the end of the script
                    if ($index === false || $index == count($list)-1) {
                        $this->sieve->script->add_rule($rule);
                    }
                    // add rule at index position
                    else {
                        $script2 = [];
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
            if ($result) {
                $this->active = [$name];
            }
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
            $result      = false;

            // if the script is active...
            if ($user_script && ($key = array_search($name, $this->active)) !== false) {
                // ...rewrite USER file removing appropriate include command
                if ($this->sieve->load($user_script)) {
                    $script = $this->sieve->script->as_array();
                    $name   = $name.$extension;
                    $rid    = 0;

                    foreach ($script as $rid => $rules) {
                        foreach ($rules['actions'] as $action) {
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
                $this->active = [];
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
        $result = [];
        $i      = 1;

        foreach ($this->script as $idx => $filter) {
            if (empty($filter['actions'])) {
                continue;
            }
            $fname = !empty($filter['name']) ? $filter['name'] : "#$i";
            $result[] = [
                'id'    => $idx,
                'name'  => $fname,
                'class' => !empty($filter['disabled']) ? 'disabled' : '',
            ];
            $i++;
        }

        return $result;
    }

    /**
     * Initializes internal script data
     */
    protected function init_script()
    {
        if (!$this->sieve->script) {
            return;
        }

        $this->script = $this->sieve->script->as_array();

        $headers    = [];
        $exceptions = ['date', 'currentdate', 'size', 'spamtest', 'body'];

        // find common headers used in script, will be added to the list
        // of available (predefined) headers (#1489271)
        foreach ($this->script as $rule) {
            foreach ((array) $rule['tests'] as $test) {
                if ($test['test'] == 'header') {
                    foreach ((array) $test['arg1'] as $header) {
                        $lc_header = strtolower($header);

                        // skip special names to not confuse UI
                        if (in_array($lc_header, $exceptions)) {
                            continue;
                        }

                        if (!isset($this->headers[$lc_header]) && !isset($headers[$lc_header])) {
                            $headers[$lc_header] = $header;
                        }
                    }
                }
            }
        }

        ksort($headers);

        $this->headers += $headers;
    }

    /**
     * Get all e-mail addresses of the user
     */
    protected function user_emails()
    {
        $addresses = $this->rc->user->list_emails();

        foreach ($addresses as $idx => $email) {
            $addresses[$idx] = $email['email'];
        }

        $addresses = array_unique($addresses);
        sort($addresses);

        return $addresses;
    }

    /**
     * Convert configured default headers into internal format
     */
    protected function get_default_headers()
    {
        $default = ['Subject', 'From', 'To'];
        $headers = (array) $this->rc->config->get('managesieve_default_headers', $default);
        $keys    = array_map('strtolower', $headers);
        $headers = array_combine($keys, $headers);

        // make sure there's no Date header
        unset($headers['date']);

        return $headers;
    }

    /**
     * Match type selector
     */
    protected function match_type_selector($name, $id, $test, $rule = null, $mode = 'all')
    {
        // matching type select (operator)
        $select_op = new html_select([
                'name'     => "_{$name}[$id]",
                'id'       => "{$name}{$id}",
                'style'    => 'display:' . (!in_array($rule, ['size', 'duplicate', 'spamtest']) ? 'inline' : 'none'),
                'class'    => 'operator_selector col-6 custom-select',
                'onchange' => "{$name}_select(this, '{$id}')",
        ]);

        $select_op->add(rcube::Q($this->plugin->gettext('filtercontains')), 'contains');
        $select_op->add(rcube::Q($this->plugin->gettext('filternotcontains')), 'notcontains');
        $select_op->add(rcube::Q($this->plugin->gettext('filteris')), 'is');
        $select_op->add(rcube::Q($this->plugin->gettext('filterisnot')), 'notis');
        if ($mode == 'all') {
            $select_op->add(rcube::Q($this->plugin->gettext('filterexists')), 'exists');
            $select_op->add(rcube::Q($this->plugin->gettext('filternotexists')), 'notexists');
        }
        $select_op->add(rcube::Q($this->plugin->gettext('filtermatches')), 'matches');
        $select_op->add(rcube::Q($this->plugin->gettext('filternotmatches')), 'notmatches');
        if (in_array('regex', $this->exts)) {
            $select_op->add(rcube::Q($this->plugin->gettext('filterregex')), 'regex');
            $select_op->add(rcube::Q($this->plugin->gettext('filternotregex')), 'notregex');
        }
        if ($mode == 'all' && in_array('relational', $this->exts)) {
            $select_op->add(rcube::Q($this->plugin->gettext('countisgreaterthan')), 'count-gt');
            $select_op->add(rcube::Q($this->plugin->gettext('countisgreaterthanequal')), 'count-ge');
            $select_op->add(rcube::Q($this->plugin->gettext('countislessthan')), 'count-lt');
            $select_op->add(rcube::Q($this->plugin->gettext('countislessthanequal')), 'count-le');
            $select_op->add(rcube::Q($this->plugin->gettext('countequals')), 'count-eq');
            $select_op->add(rcube::Q($this->plugin->gettext('countnotequals')), 'count-ne');
            $select_op->add(rcube::Q($this->plugin->gettext('valueisgreaterthan')), 'value-gt');
            $select_op->add(rcube::Q($this->plugin->gettext('valueisgreaterthanequal')), 'value-ge');
            $select_op->add(rcube::Q($this->plugin->gettext('valueislessthan')), 'value-lt');
            $select_op->add(rcube::Q($this->plugin->gettext('valueislessthanequal')), 'value-le');
            $select_op->add(rcube::Q($this->plugin->gettext('valueequals')), 'value-eq');
            $select_op->add(rcube::Q($this->plugin->gettext('valuenotequals')), 'value-ne');
        }

        return $select_op->show($test);
    }

    protected function comparator_selector($comparator, $name, $id)
    {
        $select_comp = new html_select([
                'name'  => "_{$name}[$id]",
                'id'    => "{$name}_op{$id}",
                'class' => 'custom-select'
        ]);
        $select_comp->add(rcube::Q($this->plugin->gettext('default')), '');
        $select_comp->add(rcube::Q($this->plugin->gettext('octet')), 'i;octet');
        $select_comp->add(rcube::Q($this->plugin->gettext('asciicasemap')), 'i;ascii-casemap');
        if (in_array('comparator-i;ascii-numeric', $this->exts)) {
            $select_comp->add(rcube::Q($this->plugin->gettext('asciinumeric')), 'i;ascii-numeric');
        }

        return $select_comp->show($comparator);
    }

    /**
     * Merge a rule into the script
     */
    protected function merge_rule($rule, $existing, &$script_name = null)
    {
        // if script does not exist create a new one
        if ($script_name === null || $script_name === false) {
            $script_name = $this->create_default_script();
            $this->sieve->load($script_name);
            $this->init_script();
        }

        if (!$this->sieve->script) {
            return false;
        }

        $script_active   = in_array($script_name, $this->active);
        $rule_active     = empty($rule['disabled']);
        $rule_index      = 0;
        $activate_script = false;

        // If the script is not active, but the rule is,
        // put the rule in an active script if there is one
        if (!$script_active && $rule_active && !empty($this->active)) {
            // Remove the rule from current (inactive) script
            if (isset($existing['idx'])) {
                unset($this->script[$existing['idx']]);
                $this->sieve->script->content = $this->script;
                $this->save_script($script_name);
            }

            // Load and init the active script, add the rule there
            $this->sieve->load($script_name = $this->active[0]);
            $this->init_script();
            array_unshift($this->script, $rule);
        }
        // update original forward rule/script
        else {
            // re-order rules if needed
            if (isset($rule['after']) && $rule['after'] !== '') {
                // unset the original rule
                if (isset($existing['idx'])) {
                    $this->script[$existing['idx']] = null;
                }

                // add at target position
                if ($rule['after'] >= count($this->script) - 1) {
                    $this->script[] = $rule;
                    $this->script = array_values(array_filter($this->script));
                    $rule_index = count($this->script);
                }
                else {
                    $script = [];

                    foreach ($this->script as $idx => $r) {
                        if ($r) {
                            $script[] = $r;
                        }

                        if ($idx == $rule['after']) {
                            $script[] = $rule;
                            $rule_index = count($script);
                        }
                    }

                    $this->script = $script;
                }
            }
            // rule exists, update it "in place"
            else if (isset($existing['idx'])) {
                $this->script[$existing['idx']] = $rule;
                $rule_index = $existing['idx'];
            }
            // otherwise put the rule on top
            else {
                array_unshift($this->script, $rule);
                $rule_index = 0;
            }

            // if the script is not active, but the rule is, we need to de-activate
            // all rules except the forward rule
            if (!$script_active && $rule_active) {
                $activate_script = true;
                foreach ($this->script as $idx => $r) {
                    if ($idx !== $rule_index) {
                        $this->script[$idx]['disabled'] = true;
                    }
                }
            }
        }

        $this->sieve->script->content = $this->script;

        // save the script
        $saved = $this->save_script($script_name);

        // activate the script
        if ($saved && $activate_script) {
            $this->activate_script($script_name);
        }

        return $saved;
    }

    /**
     * Create default script
     */
    protected function create_default_script()
    {
        // if script not exists build default script contents
        $script_file  = $this->rc->config->get('managesieve_default');
        $script_name  = $this->rc->config->get('managesieve_script_name');
        $kolab_master = $this->rc->config->get('managesieve_kolab_master');
        $content      = '';

        if (empty($script_name)) {
            $script_name = 'roundcube';
        }

        if ($script_file && !$kolab_master && is_readable($script_file) && !is_dir($script_file)) {
            $content = file_get_contents($script_file);
        }

        // add script and set it active
        if ($this->sieve->save_script($script_name, $content)) {
            $this->activate_script($script_name);
            $this->list[] = $script_name;
        }

        return $script_name;
    }
}
