<?php

/**
 * Managesieve Forward Engine
 *
 * Engine part of Managesieve plugin implementing UI and backend access.
 *
 * Copyright (C) 2011-2017, Kolab Systems AG
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

class rcube_sieve_forward extends rcube_sieve_engine
{
    protected $error;
    protected $script_name;
    protected $forward = array();

    function actions()
    {
        $error = $this->start('forward');

        // find current forward rule
        if (!$error) {
            $this->forward_rule();
            $this->forward_post();
        }

        $this->plugin->add_label('forward.saving');
        $this->rc->output->add_handlers(array(
            'forwardform' => array($this, 'forward_form'),
        ));

        $this->rc->output->set_pagetitle($this->plugin->gettext('forward'));
        $this->rc->output->send('managesieve.forward');
    }

    /**
     * Find and load sieve script with/for forward rule
     *
     * @param string $script_name Optional script name
     *
     * @return int Connection status: 0 on success, >0 on failure
     */
    protected function load_script($script_name = null)
    {
        if ($this->script_name !== null) {
            return 0;
        }

        $list     = $this->list_scripts();
        $master   = $this->rc->config->get('managesieve_kolab_master');
        $included = array();

        $this->script_name = false;

        // first try the active script(s)...
        if (!empty($this->active)) {
            // Note: there can be more than one active script on KEP:14-enabled server
            foreach ($this->active as $script) {
                if ($this->sieve->load($script)) {
                    foreach ($this->sieve->script->as_array() as $rule) {
                        if (!empty($rule['actions'])) {
                            if ($rule['actions'][0]['type'] == 'redirect') {
                                $this->script_name = $script;
                                return 0;
                            }
                            else if (empty($master) && $rule['actions'][0]['type'] == 'include') {
                                $included[] = $rule['actions'][0]['target'];
                            }
                        }
                    }
                }
            }

            // ...else try scripts included in active script (not for KEP:14)
            foreach ($included as $script) {
                if ($this->sieve->load($script)) {
                    foreach ($this->sieve->script->as_array() as $rule) {
                        if (!empty($rule['actions']) && $rule['actions'][0]['type'] == 'redirect') {
                            $this->script_name = $script;
                            return 0;
                        }
                    }
                }
            }
        }

        // try all other scripts
        if (!empty($list)) {
            // else try included scripts
            foreach (array_diff($list, $included, $this->active) as $script) {
                if ($this->sieve->load($script)) {
                    foreach ($this->sieve->script->as_array() as $rule) {
                        if (!empty($rule['actions']) && $rule['actions'][0]['type'] == 'redirect') {
                            $this->script_name = $script;
                            return 0;
                        }
                    }
                }
            }

            // none of the scripts contains existing forward rule
            // use any (first) active or just existing script (in that order)
            if (!empty($this->active)) {
                $this->sieve->load($this->script_name = $this->active[0]);
            }
            else {
                $this->sieve->load($this->script_name = $list[0]);
            }
        }

        return $this->sieve->error();
    }

    private function forward_rule()
    {
        if ($this->script_name === false || $this->script_name === null || !$this->sieve->load($this->script_name)) {
            return;
        }

        $list   = array();
        $active = in_array($this->script_name, $this->active);

        // find (first) simple forward rule that can be expressed with the minimal settings
        foreach ($this->script as $idx => $rule) {
            if (empty($this->forward) && !empty($rule['actions']) && $rule['actions'][0]['type'] == 'redirect') {
                $ignore_rule = false;
                $target = null;
                $stop_found = false;
                foreach ($rule['actions'] as $act) {
                    if ($stop_found) {
                        // we might loose information if there rules after the stop
                        $ignore_rule = true;
                    }
                    if ($act['type'] == 'keep') {
                        $action = 'copy';
                    }
                    else if ($act['type'] == 'stop') {
                        // we might loose information if there rules after the stop
                        $stop_found = true;
                    }
                    else if ($act['type'] == 'discard') {
                        $action = 'redirect';
                    }
                    else if ($act['type'] == 'redirect') {
                        if (!empty($target)) {
                            // we cannot use this rule, because there are multiple targets
                            $ignore_rule = true;
                        }
                        else {
                            $action = $act['copy'] ? 'copy' : 'redirect';
                            $target = $act['target'];
                        }
                    }
                    else {
                        // we cannot use this rule, because there are unknown commands, and we don't want to overwrite them.
                        $ignore_rule = true;
                    }
                }

                if (count($rule['tests']) != 1 || $rule['tests'][0]['test'] != "true" || $rule['tests'][0]['not'] != null) {
                    // ignore rules that have special conditions
                    $ignore_rule = true;
                }

                if (!$ignore_rule) {
                    $this->forward = array_merge($rule['actions'][0], array(
                        'idx'      => $idx,
                        'disabled' => $rule['disabled'] || !$active,
                        'name'     => $rule['name'],
                        'tests'    => $rule['tests'],
                        'action'   => $action ?: 'keep',
                        'target'   => $target,
                    ));
                }
            }
            else if ($active) {
                $list[$idx] = $rule['name'];
            }
        }

        $this->forward['list'] = $list;
    }

    private function forward_post()
    {
        if (empty($_POST)) {
            return;
        }

        $date_extension  = in_array('date', $this->exts);
        $regex_extension = in_array('regex', $this->exts);

        $status        = rcube_utils::get_input_value('forward_status', rcube_utils::INPUT_POST);
        $action        = rcube_utils::get_input_value('forward_action', rcube_utils::INPUT_POST);
        $target        = rcube_utils::get_input_value('action_target', rcube_utils::INPUT_POST, true);

        $forward_action['type']         = 'forward';
        $forward_action['reason']       = $this->strip_value(str_replace("\r\n", "\n", $reason));
        $forward_action['subject']      = trim($subject);
        $forward_action['from']         = trim($from);
        $forward_tests                  = (array) $this->forward['tests'];

        if ($action == 'redirect' || $action == 'copy') {
            if (empty($target) || !rcube_utils::check_email($target)) {
                $error = 'noemailwarning';
            }
        }

        if (empty($forward_tests)) {
            $forward_tests = (array) $this->rc->config->get('managesieve_forward_test', array(array('test' => 'true')));
        }

        if (!$error) {
            $rule               = $this->forward;
            $rule['type']       = 'if';
            $rule['name']       = $rule['name'] ?: $this->plugin->gettext('forward');
            $rule['disabled']   = $status == 'off';
            $rule['tests']      = $forward_tests;
            $rule['join']       = $date_extension ? count($forward_tests) > 1 : false;
            $rule['actions']    = array($forward_action);
            $rule['after']      = $after;

            if ($action && $action != 'keep') {
                $rule['actions'][] = array(
                    'type'   => $action == 'discard' ? 'discard' : 'redirect',
                    'copy'   => $action == 'copy',
                    'target' => $action != 'discard' ? $target : '',
                );
            }

            if ($this->save_forward_script($rule)) {
                $this->rc->output->show_message('managesieve.forwardsaved', 'confirmation');
                $this->rc->output->send();
            }
        }

        $this->rc->output->show_message($error ?: 'managesieve.saveerror', 'error');
        $this->rc->output->send();
    }

    /**
     * Independent forward form
     */
    public function forward_form($attrib)
    {
        // build FORM tag
        $form_id = $attrib['id'] ?: 'form';
        $out     = $this->rc->output->request_form(array(
            'id'      => $form_id,
            'name'    => $form_id,
            'method'  => 'post',
            'task'    => 'settings',
            'action'  => 'plugin.managesieve-forward',
            'noclose' => true
            ) + $attrib);


        // form elements
        $status    = new html_select(array('name' => 'forward_status', 'id' => 'forward_status'));
        $action    = new html_select(array('name' => 'forward_action', 'id' => 'forward_action'));

        $status->add($this->plugin->gettext('forward.on'), 'on');
        $status->add($this->plugin->gettext('forward.off'), 'off');

        if (in_array('copy', $this->exts)) {
            $action->add($this->plugin->gettext('forward.copy'), 'copy');
        }
        $action->add($this->plugin->gettext('forward.redirect'), 'redirect');

        // force domain selection in redirect email input
        $domains  = (array) $this->rc->config->get('managesieve_domains');
        $redirect = $this->forward['action'] == 'redirect' || $this->forward['action'] == 'copy';

        if (!empty($domains)) {
            sort($domains);

            $domain_select = new html_select(array('name' => 'action_domain', 'id' => 'action_domain'));
            $domain_select->add(array_combine($domains, $domains));

            if ($redirect && $this->forward['target']) {
                $parts = explode('@', $this->forward['target']);
                if (!empty($parts)) {
                    $this->forward['domain'] = array_pop($parts);
                    $this->forward['target'] = implode('@', $parts);
                }
            }
        }

        // redirect target
        $action_target = '<span id="action_target_span" class="input-group">'
            . '<input type="text" name="action_target" id="action_target"'
            . ' value="' .($redirect ? rcube::Q($this->forward['target'], 'strict', false) : '') . '"'
            . (!empty($domains) ? ' size="20"' : ' size="35"') . '/>'
            . (!empty($domains) ? ' <span class="input-group-prepend input-group-append"><span class="input-group-text">@</span></span> '
                . $domain_select->show($this->forward['domain']) : '')
            . '</span>';

        // Message tab
        $table = new html_table(array('cols' => 2));

        $table->add('title', html::label('forward_action', $this->plugin->gettext('forward.action')));
        $table->add('forward input-group input-group-combo', $action->show($this->forward['action']) . ' ' . $action_target);

        $table->add('title', html::label('forward_status', $this->plugin->gettext('forward.status')));
        $table->add(null, $status->show(!isset($this->forward['disabled']) || $this->forward['disabled'] ? 'off' : 'on'));

        $out .= $table->show($attrib);

        $out .= '</form>';

        $this->rc->output->add_gui_object('sieveform', $form_id);

        return $out;
    }

    /**
     * Saves forward script (adding some variables)
     */
    protected function save_forward_script($rule)
    {
        // if script does not exist create a new one
        if ($this->script_name === null || $this->script_name === false) {
            $this->script_name = $this->rc->config->get('managesieve_script_name');
            if (empty($this->script_name)) {
                $this->script_name = 'roundcube';
            }

            // use default script contents
            if (!$this->rc->config->get('managesieve_kolab_master')) {
                $script_file = $this->rc->config->get('managesieve_default');
                if ($script_file && is_readable($script_file)) {
                    $content = file_get_contents($script_file);
                }
            }

            // create and load script
            if ($this->sieve->save_script($this->script_name, $content)) {
                $this->sieve->load($this->script_name);
            }
        }

        $script_active = in_array($this->script_name, $this->active);

        // re-order rules if needed
        if (isset($rule['after']) && $rule['after'] !== '') {
            // reset original forward rule
            if (isset($this->forward['idx'])) {
                $this->script[$this->forward['idx']] = null;
            }

            // add at target position
            if ($rule['after'] >= count($this->script) - 1) {
                $this->script[] = $rule;
            }
            else {
                $script = array();

                foreach ($this->script as $idx => $r) {
                    if ($r) {
                        $script[] = $r;
                    }

                    if ($idx == $rule['after']) {
                        $script[] = $rule;
                    }
                }

                $this->script = $script;
            }

            $this->script = array_values(array_filter($this->script));
        }
        // update original forward rule if it exists
        else if (isset($this->forward['idx'])) {
            $this->script[$this->forward['idx']] = $rule;
        }
        // otherwise put forward rule on top
        else {
            array_unshift($this->script, $rule);
        }

        // if the script was not active, we need to de-activate
        // all rules except the forward rule, but only if it is not disabled
        if (!$script_active && !$rule['disabled']) {
            foreach ($this->script as $idx => $r) {
                if (empty($r['actions']) || $r['actions'][0]['type'] != 'forward') {
                    $this->script[$idx]['disabled'] = true;
                }
            }
        }

        if (!$this->sieve->script) {
            return false;
        }

        $this->sieve->script->content = $this->script;

        // save the script
        $saved = $this->save_script($this->script_name);

        // activate the script
        if ($saved && !$script_active && !$rule['disabled']) {
            $this->activate_script($this->script_name);
        }

        return $saved;
    }

    /**
     * API: get forward rule
     *
     * @return array forward rule information
     */
    public function get_forward()
    {
        $this->exts = $this->sieve->get_extensions();
        $this->init_script();
        $this->forward_rule();

        $forward = array(
            'supported' => $this->exts,
            'enabled'   => empty($this->forward['disabled']),
            'action'    => $this->forward['action'],
            'target'    => $this->forward['target'],
        );

        return $forward;
    }

    /**
     * API: set forward rule
     *
     * @param array $forward forward rule information (see self::get_forward())
     *
     * @return bool True on success, False on failure
     */
    public function set_forward($data)
    {
        $this->exts  = $this->sieve->get_extensions();
        $this->error = false;

        $this->init_script();
        $this->forward_rule();

        $forward['type']      = 'forward';

        if ($data['action'] == 'redirect' || $data['action'] == 'copy') {
            if (empty($data['target']) || !rcube_utils::check_email($data['target'])) {
                $this->error = "Invalid address in action taget: " . $data['target'];
                return false;
            }
        }
        else if ($data['action']) {
            $this->error = "Unsupported forward action: " . $data['action'];
            return false;
        }

        if (empty($forward_tests)) {
            $forward_tests = (array) $this->rc->config->get('managesieve_forward_test', array(array('test' => 'true')));
        }

        $rule             = $this->forward;
        $rule['type']     = 'if';
        $rule['name']     = $rule['name'] ?: 'Out-of-Office';
        $rule['disabled'] = isset($data['enabled']) && !$data['enabled'];
        $rule['tests']    = $forward_tests;
        $rule['join']     = $date_extension ? count($forward_tests) > 1 : false;
        $rule['actions']  = array($forward);

        if ($data['action'] && $data['action'] != 'keep') {
            $rule['actions'][] = array(
                'type'   => $data['action'] == 'discard' ? 'discard' : 'redirect',
                'copy'   => $data['action'] == 'copy',
                'target' => $data['action'] != 'discard' ? $data['target'] : '',
            );
        }

        return $this->save_forward_script($rule);
    }

    /**
     * API: connect to managesieve server
     */
    public function connect($username, $password)
    {
        $error = parent::connect($username, $password);

        if ($error) {
            return $error;
        }

        return $this->load_script();
    }

    /**
     * API: Returns last error
     *
     * @return string Error message
     */
    public function get_error()
    {
        return $this->error;
    }
}
