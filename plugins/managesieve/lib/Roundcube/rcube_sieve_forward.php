<?php

/**
 * Managesieve Forward Engine
 *
 * Engine part of Managesieve plugin implementing UI and backend access.
 *
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
 * along with this program. If not, see https://www.gnu.org/licenses/.
 */

class rcube_sieve_forward extends rcube_sieve_engine
{
    protected $error;
    protected $script_name;
    protected $forward = [];

    function actions()
    {
        $error = $this->start('forward');

        // find current forward rule
        if (!$error) {
            $this->forward_rule();
            $this->forward_post();
        }

        $this->plugin->add_label('forward.saving');
        $this->rc->output->add_handlers([
                'forwardform' => [$this, 'forward_form'],
        ]);

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
        $included = [];

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

        $list   = [];
        $active = in_array($this->script_name, $this->active);

        // find (first) simple forward rule that can be expressed with the minimal settings
        foreach ($this->script as $idx => $rule) {
            if (empty($this->forward) && !empty($rule['actions']) && $rule['actions'][0]['type'] == 'redirect') {
                $ignore_rule = false;
                $target      = null;
                $stop_found  = false;
                $action      = 'keep';

                foreach ($rule['actions'] as $act) {
                    if ($stop_found) {
                        // we might loose information if there rules after the stop
                        $ignore_rule = true;
                    }
                    if ($act['type'] == 'keep') {
                        $action = 'copy';
                    }
                    else if ($act['type'] == 'stop') {
                        // we might loose information if there are rules after the stop
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
                            $action = !empty($act['copy']) ? 'copy' : 'redirect';
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
                    $this->forward = array_merge($rule['actions'][0], [
                        'idx'      => $idx,
                        'disabled' => $rule['disabled'] || !$active,
                        'name'     => $rule['name'],
                        'tests'    => $rule['tests'],
                        'action'   => $action,
                        'target'   => $target,
                    ]);
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

        $status = rcube_utils::get_input_string('forward_status', rcube_utils::INPUT_POST);
        $action = rcube_utils::get_input_string('forward_action', rcube_utils::INPUT_POST);
        $target = rcube_utils::get_input_string('action_target', rcube_utils::INPUT_POST, true);
        $target_domain = rcube_utils::get_input_string('action_domain', rcube_utils::INPUT_POST);

        $date_extension = in_array('date', $this->exts);

        if ($target_domain) {
            $target .= '@' . $target_domain;
        }

        if (empty($target) || !rcube_utils::check_email($target)) {
            $error = 'noemailwarning';
        }

        if (empty($this->forward['tests'])) {
            $forward_tests = (array) $this->rc->config->get('managesieve_forward_test', [['test' => 'true']]);
        }
        else {
            $forward_tests = (array) $this->forward['tests'];
        }

        if (empty($error)) {
            $rule               = $this->forward;
            $rule['type']       = 'if';
            $rule['name']       = !empty($rule['name']) ? $rule['name'] : $this->plugin->gettext('forward');
            $rule['disabled']   = $status == 'off';
            $rule['tests']      = $forward_tests;
            $rule['join']       = $date_extension ? count($forward_tests) > 1 : false;
            $rule['actions']    = [[
                    'type'   => 'redirect',
                    'copy'   => $action == 'copy',
                    'target' => $target,
            ]];

            if ($this->merge_rule($rule, $this->forward, $this->script_name)) {
                $this->rc->output->show_message('managesieve.forwardsaved', 'confirmation');
                $this->rc->output->send();
            }
        }

        if (empty($error)) {
            $error = 'managesieve.saveerror';
        }

        $this->rc->output->show_message($error, 'error');
        $this->rc->output->send();
    }

    /**
     * Independent forward form
     */
    public function forward_form($attrib)
    {
        // build FORM tag
        $form_id = !empty($attrib['id']) ? $attrib['id'] : 'form';
        $out     = $this->rc->output->request_form([
                'id'      => $form_id,
                'name'    => $form_id,
                'method'  => 'post',
                'task'    => 'settings',
                'action'  => 'plugin.managesieve-forward',
                'noclose' => true
            ] + $attrib
        );


        // form elements
        $status = new html_select(['name' => 'forward_status', 'id' => 'forward_status', 'class' => 'custom-select']);
        $action = new html_select(['name' => 'forward_action', 'id' => 'forward_action', 'class' => 'custom-select']);

        $status->add($this->plugin->gettext('forward.on'), 'on');
        $status->add($this->plugin->gettext('forward.off'), 'off');

        if (in_array('copy', $this->exts)) {
            $action->add($this->plugin->gettext('forward.copy'), 'copy');
        }
        $action->add($this->plugin->gettext('forward.redirect'), 'redirect');

        // force domain selection in redirect email input
        $domains  = (array) $this->rc->config->get('managesieve_domains');
        $redirect = !empty($this->forward['action'])
            && ($this->forward['action'] == 'redirect' || $this->forward['action'] == 'copy');

        if (!empty($domains)) {
            sort($domains);

            $domain_select = new html_select(['name' => 'action_domain', 'id' => 'action_domain', 'class' => 'custom-select']);
            $domain_select->add(array_combine($domains, $domains));

            if ($redirect && !empty($this->forward['target'])) {
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
            . (!empty($domain_select) ? ' size="20"' : ' size="35"') . '/>'
            . (!empty($domain_select) ? ' <span class="input-group-prepend input-group-append"><span class="input-group-text">@</span></span> '
                . $domain_select->show(!empty($this->forward['domain']) ? $this->forward['domain'] : null) : '')
            . '</span>';

        // Message tab
        $table = new html_table(['cols' => 2]);

        $table->add('title', html::label('forward_action', $this->plugin->gettext('forward.action')));
        $table->add('forward input-group input-group-combo',
            $action->show(!empty($this->forward['action']) ? $this->forward['action'] : null) . ' ' . $action_target
        );

        $table->add('title', html::label('forward_status', $this->plugin->gettext('forward.status')));
        $table->add(null, $status->show(!isset($this->forward['disabled']) || $this->forward['disabled'] ? 'off' : 'on'));

        $out .= $table->show($attrib);

        $out .= '</form>';

        $this->rc->output->add_gui_object('sieveform', $form_id);

        return $out;
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

        $forward = [
            'supported' => $this->exts,
            'enabled'   => empty($this->forward['disabled']),
            'action'    => $this->forward['action'],
            'target'    => $this->forward['target'],
        ];

        return $forward;
    }

    /**
     * API: set forward rule
     *
     * @param array $data forward rule information (see self::get_forward())
     *
     * @return bool True on success, False on failure
     */
    public function set_forward($data)
    {
        $this->exts  = $this->sieve->get_extensions();
        $this->error = false;

        $this->init_script();
        $this->forward_rule();

        $date_extension = in_array('date', $this->exts);

        if ($data['action'] == 'redirect' || $data['action'] == 'copy') {
            if (empty($data['target']) || !rcube_utils::check_email($data['target'])) {
                $this->error = "Invalid address in action target: " . $data['target'];
                return false;
            }
        }
        else if ($data['action']) {
            $this->error = "Unsupported forward action: " . $data['action'];
            return false;
        }

        if (empty($forward_tests)) {
            $forward_tests = (array) $this->rc->config->get('managesieve_forward_test', [['test' => 'true']]);
        }

        $rule             = $this->forward;
        $rule['type']     = 'if';
        $rule['name']     = !empty($rule['name']) ? $rule['name'] : 'Out-of-Office';
        $rule['disabled'] = isset($data['enabled']) && !$data['enabled'];
        $rule['tests']    = $forward_tests;
        $rule['join']     = $date_extension ? count($forward_tests) > 1 : false;
        $rule['actions']  = [[
                'type'   => 'redirect',
                'copy'   => $data['action'] == 'copy',
                'target' => $data['target'],
        ]];

        return $this->merge_rule($rule, $this->forward, $this->script_name);
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
