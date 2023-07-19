<?php

/**
 * Managesieve Spam Engine
 *
 * Engine part of Managesieve plugin implementing UI and backend access
 * for spam filter settings. Based on the 'rcube_sieve_forward' engine
 *
 * Copyright (C) Greenhost / Mart van Santen
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

class rcube_sieve_spam extends rcube_sieve_engine
{
    protected $error;
    protected $script_name;
    protected $spam = [];

    public const DEFAULT_THRESHOLD = 5;
    public const DEFAULT_HEADER = 'x-spam-score';
    public const DEFAULT_FOLDER = 'INBOX.Junk';
    /**
     * Generates a numeric identifier for a filter
     */
    protected function genid()
    {
        return preg_replace('/[^0-9]/', '', microtime(true));
    }

    function actions()
    {
        $error = $this->start('spam');

        // Handle ajax requests
        if ($action = rcube_utils::get_input_string('_act', rcube_utils::INPUT_GPC)) {
            if ($action == 'spamacladd') {
                $aid = rcube_utils::get_input_string('_aid', rcube_utils::INPUT_POST);
                $id  = $this->genid();
                $content = $this->acl_div($id);
                $this->rc->output->command('managesieve_actionfill', $content, $id, $aid);
                $this->rc->output->send();return;
            }

        }

        // find current spam rule
        if (!$error) {
            $this->spam_rule();
            $this->spam_post();
        }
        else {
        }

        $this->plugin->add_label('spam.saving');
        $this->rc->output->add_handlers([
            'spamform' => [$this, 'spam_form'],
        ]);

        $this->rc->output->set_pagetitle($this->plugin->gettext('spam'));
        $this->rc->output->send('managesieve.spam');
    }

    /**
     * Find and load sieve script with/for spam rule
     *
     * @params string Optional script name, however ignored
     * 
     * @return int Connection status: 0 on success, >0 on failure
     */
    protected function load_script($script_name = NULL)
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
                        if ($this->isSpamRule($rule)) {
                            $this->script_name = $script;
                            return 0;
                        }
                    }
                }
            }

            // ...else try scripts included in active script (not for KEP:14)
            foreach ($included as $script) {
                if ($this->sieve->load($script)) {
                    foreach ($this->sieve->script->as_array() as $rule) {
                        if ($this->isSpamRule($rule)) {
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
                        if ($this->isSpamRule($rule)) {
                            $this->script_name = $script;
                            return 0;
                        }
                    }
                }
            }

            // none of the scripts contains existing spam rule
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

    private function isSpamRule($rule) {

        $spam_header  = (string) ( $this->rc->config->get('managesieve_spam_header') ?? rcube_sieve_spam::DEFAULT_HEADER );

        if (
            !empty($rule['actions']) &&
            !empty($rule['actions'][0]['type']) &&
            !empty($rule['tests'][0]['test']) &&
            !empty($rule['tests'][0]['arg1']) &&
            $rule['actions'][0]['type'] == 'fileinto' &&
            $rule['tests'][0]['test'] == 'header' &&
            strtolower($rule['tests'][0]['arg1']) == strtolower($spam_header)) {

            return true;
        }
        return false;

    }

    private function spam_rule()
    {
        $spam_threshold = (string) ( $this->rc->config->get('managesieve_spam_threshold') ?? rcube_sieve_spam::DEFAULT_THRESHOLD);
	

        if ($this->script_name === false || $this->script_name === null || !$this->sieve->load($this->script_name)) {
            return;
        }

        $list   = [];
        $active = in_array($this->script_name, $this->active);
        $acl_allow = [];

        // find (first) simple spam rule. We use the header identiefer and action fileinto
        // as 'markers' to identify the script
        foreach ($this->script as $idx => $rule) {
            if (empty($this->spam) && $this->isSpamRule($rule)) {
                $this->spam = array_merge($rule['actions'][0], [
                    'idx'      => $idx,
                    'disabled' => $rule['disabled'] || !$active,
                    'name'     => $rule['name'],
                    'tests'    => $rule['tests'],
                    'threshold'   => $rule['tests'][0]['arg2'] ?? $spam_threshold,
                ]);

                foreach($rule['tests'] as $current) {
                    if ($current['test'] == 'header' &&
                        $current['arg1'] == 'from' &&
                        $current['type'] == 'contains') {
                        $acl_allow[] = $current['arg2'];
                    }
                }

            }
            else if ($active) {
                $list[$idx] = $rule['name'];
            }
        }

        $this->spam['acl_allow'] = $acl_allow;
        $this->spam['list'] = $list;
    }

    private function spam_post()
    {
        if (empty($_POST)) {
            return;
        }

        $spam_header  = (string) ($this->rc->config->get('managesieve_spam_heaser') ?? rcube_sieve_spam::DEFAULT_HEADER );
        $spam_folder = (string) ($this->rc->config->get('managesieve_spam_folder') ?? rcube_sieve_spam::DEFAULT_FOLDER );
        $spam_threshold = (string) ($this->rc->config->get('managesieve_spam_threshold') ?? rcube_sieve_spam::DEFAULT_THRESHOLD );

        $threshold = rcube_utils::get_input_string('spam_threshold', rcube_utils::INPUT_POST) ?? $spam_threshold;

        $status = rcube_utils::get_input_string('spam_status', rcube_utils::INPUT_POST);
        $acl_allow = rcube_utils::get_input_value('_acl_allow', rcube_utils::INPUT_POST);

        if (empty($error)) {
            $rule               = $this->spam;

            $spam_tests[] = [
                'test' => 'header',
                'type' => 'value-gt',
                'arg1' => $spam_header,
                'arg2' => [$threshold],
                'comparator' => 'i;ascii-numeric',

            ];

            foreach($acl_allow as $item) {
                $item = trim($item);
                if (strlen($item) >0) {
                    $spam_tests[] = [
                        'test' => 'header',
                        'type' => 'contains',
                        'not' => true,
                        'arg1' => 'from',
                        'arg2' => $item,
                    ];
                }

            }

            $rule['type']       = 'if';
            $rule['name']       = "Spam settings";

            $rule['disabled']   = $status == 'off';
            $rule['tests']      = $spam_tests;
            $rule['join']       = (count($spam_tests) > 1);

            $rule['actions']    = [[
                'type'   => 'fileinto',
                'target' => $spam_folder,
            ]];

            if ($this->merge_rule($rule, $this->spam, $this->script_name)) {
                $this->rc->output->show_message('managesieve.spamsaved', 'confirmation');
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
     * Independent spam form
     */
    public function spam_form($attrib)
    {

        $spam_threshold = (string) $this->rc->config->get('managesieve_spam_folder') ?? rcube_sieve_spam::DEFAULT_THRESHOLD;


        // build FORM tag
        $form_id = !empty($attrib['id']) ? $attrib['id'] : 'form';
        $out     = $this->rc->output->request_form([
            'id'      => $form_id,
            'name'    => $form_id,
            'method'  => 'post',
            'task'    => 'settings',
            'action'  => 'plugin.managesieve-spam',
            'noclose' => true,
            'class' => 'propform',
        ] + $attrib
        );

        $this->rc->output->add_label(
            'managesieve.acldeleteconfirm'
        );


        // form elements
        $status = new html_select(['name' => 'spam_status', 'id' => 'spam_status', 'class' => 'custom-select']);

        $status->add($this->plugin->gettext('spam.on'), 'on');
        $status->add($this->plugin->gettext('spam.off'), 'off');


        $threshold = new html_inputfield(['name' => 'spam_threshold', 'id' => 'spam_threshold', 'class' => 'form-control' ]);

        if (!isset($this->spam['threshold']) || !is_numeric($this->spam['threshold'])) {
            $this->spam['threshold'] = $spam_threshold;
	rcube::write_log('errors', "T: $spam_threshold");


        }

        // Message tab
        $table = new html_table(['cols' => 2]);

        $action_target = '<span id="action_target_span" class="input-group">'
            . '<input type="range" min="2" max="10" step="1" value="'.$this->spam['threshold'].'" name="spam_threshold" id="spam_threshold"/>'
            . '</span>';


        $table->add('title', html::label('spam_threshold', $this->plugin->gettext('spam.threshold')));
        #       $table->add(null, $threshold->show($this->spam['threshold']));
        $table->add(null, $action_target);
        #$threshold->show($this->spam['threshold']));

        $table->add('title', html::label('spam_status', $this->plugin->gettext('spam.status')));
        $table->add(null, $status->show(!isset($this->spam['disabled']) || $this->spam['disabled'] ? 'off' : 'on'));

        $out .= html::tag('fieldset', '', html::tag('legend', null, $this->plugin->gettext('spam')) . $table->show($attrib));
        //       $out .= $table->show($attrib);



        $rows_num = 1;
        $divs = '<div id="actions">';
        $i = 0;
        $rows_num = max(count($this->spam['acl_allow']), 1);
        foreach($this->spam['acl_allow'] as $item) {
            $divs .= $this->acl_div($i, $rows_num, $item);
            $i++;
        }
        if ($i == 0) {
            $divs .= $this->acl_div($i, $rows_num);
        }
        $divs .= "</div>\n";

        $out .= html::tag('fieldset', '', html::tag('legend', null, $this->plugin->gettext('spam.allowlist')) . $divs);
        //       $out .= $table->show($attrib);


        $this->rc->output->add_gui_object('sieveform', $form_id);
        $out .= '</form>';

        return $out;
    }


    public function acl_div($id, $rows_num = 1, $value = '') {

        $out =  '<div class="aclrow'.$id.'" id="aclrow' .$id .'">'."\n";
        $out .= '<table class="compact-table"><tr><td class="rowactions">';

        $out .= html::tag('input', [
            'type'  => 'text',
            'name'  => "_acl_allow[$id]",
            'id'    => 'acl_allow' . $id,
            'value' => $value,
            'size'  => 35,
            'class' => 'form-control',
        ]);

        $add_label = rcube::Q($this->plugin->gettext('add'));
        $del_label = rcube::Q($this->plugin->gettext('del'));
        $out .= '<td class="rowbuttons">';
        $out .= sprintf('<a href="#" id="acladd%s" title="%s" onclick="rcmail.managesieve_spam_acladd(%s)" class="button create add">'
            . '<span class="inner">%s</span></a>', $id, $add_label, $id, $add_label);
        $out .= sprintf('<a href="#" id="acladd%s" title="%s" onclick="rcmail.managesieve_spam_acldel(%s)" class="button delete del%s">'
            . '<span class="inner">%s</span></a>', $id, $del_label, $id, ($rows_num < 2 ? ' disabled' : ''), $del_label);
        $out .= '</td>';

        $out .= '</tr></table>';

        $out .= "</div>\n" ;
        return $out;

    }

    /**
     * API: get spam rule
     *
     * @return array spam rule information
     */
    public function get_spam()
    {
        $this->exts = $this->sieve->get_extensions();
        $this->init_script();
        $this->spam_rule();

        $spam = [
            'supported' => $this->exts,
            'enabled'   => empty($this->spam['disabled']),
            'threshold' => $this->spam['threshold'],
            'acl_allow' => $this->spam['acl_allow'],
        ];

        return $spam;
    }

    /**
     * API: set spam rule
     *
     * @param array $spam spam rule information (see self::get_spam())
     *
     * @return bool True on success, False on failure
     */
    public function set_spam($data)
    {

        $spam_folder = (string) $this->rc->config->get('managesieve_spam_folder') ?? rcube_sieve_spam::DEFAULT_FOLDER;
        $spam_threshold = (string) $this->rc->config->get('managesieve_spam_threshold') ?? rcube_sieve_spam::DEFAULT_THRESHOLD;

        $this->exts  = $this->sieve->get_extensions();
        $this->error = false;

        $this->init_script();
        $this->spam_rule();

        $threshold = $data['threshold'] ?? $spam_threshold;
        $acl_allow = $data['acl_allow'] ?? [];

        $date_extension = in_array('date', $this->exts);

        $spam_tests[] = [
            'test' => 'header',
            'type' => 'value-gt',
            'arg1' => $spam_header,
            'arg2' => [$threshold],
            'comparator' => 'i;ascii-numeric',

        ];

        foreach($acl_allow as $item) {
            $item = trim($item);
            if (strlen($item) >0) {
                $spam_tests[] = [
                    'test' => 'header',
                    'type' => 'contains',
                    'not' => true,
                    'arg1' => 'from',
                    'arg2' => $item,
                ];
            }

        }

        $rule             = $this->spam;

        $rule['type']       = 'if';
        $rule['name']       = "Spam settings";

        $rule['disabled']   = isset($data['enabled']) && !$data['enabled'];
        $rule['tests']      = $spam_tests;
        $rule['join']       = (count($spam_tests) > 1);

        $rule['actions']    = [[
            'type'   => 'fileinto',
            'target' => $spam_folder,
        ]];

        return $this->merge_rule($rule, $this->spam, $this->script_name);
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
