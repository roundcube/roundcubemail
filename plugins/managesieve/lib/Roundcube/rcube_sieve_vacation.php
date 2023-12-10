<?php

/**
 * Managesieve Vacation Engine
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
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

class rcube_sieve_vacation extends rcube_sieve_engine
{
    protected $error;
    protected $script_name;
    protected $vacation = [];

    function actions()
    {
        $error = $this->start('vacation');

        // find current vacation rule
        if (!$error) {
            $this->vacation_rule();
            $this->vacation_post();
        }

        $this->plugin->add_label('vacation.saving');
        $this->rc->output->add_handlers([
                'vacationform' => [$this, 'vacation_form'],
        ]);

        $this->rc->output->set_pagetitle($this->plugin->gettext('vacation'));
        $this->rc->output->send('managesieve.vacation');
    }

    /**
     * Find and load sieve script with/for vacation rule
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
                            $action = $rule['actions'][0];
                            if ($action['type'] == 'vacation') {
                                $this->script_name = $script;
                                return 0;
                            }
                            else if (empty($master) && empty($action['global']) && $action['type'] == 'include') {
                                $included[] = $action['target'];
                            }
                        }
                    }
                }
            }

            // ...else try scripts included in active script (not for KEP:14)
            foreach ($included as $script) {
                if ($this->sieve->load($script)) {
                    foreach ($this->sieve->script->as_array() as $rule) {
                        if (!empty($rule['actions']) && $rule['actions'][0]['type'] == 'vacation') {
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
                        if (!empty($rule['actions']) && $rule['actions'][0]['type'] == 'vacation') {
                            $this->script_name = $script;
                            return 0;
                        }
                    }
                }
            }

            // none of the scripts contains existing vacation rule
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

    protected function vacation_rule()
    {
        if ($this->script_name === false || $this->script_name === null || !$this->sieve->load($this->script_name)) {
            return;
        }

        $list   = [];
        $active = in_array($this->script_name, $this->active);

        // find (first) vacation rule
        foreach ($this->script as $idx => $rule) {
            if (empty($this->vacation) && !empty($rule['actions']) && $rule['actions'][0]['type'] == 'vacation') {
                $action = 'keep';
                $target = null;

                foreach ($rule['actions'] as $act) {
                    if ($act['type'] == 'discard' || $act['type'] == 'keep') {
                        $action = $act['type'];
                    }
                    else if ($act['type'] == 'redirect') {
                        $action = $act['copy'] ? 'copy' : 'redirect';
                        $target = $act['target'];
                    }
                }

                $this->vacation = array_merge($rule['actions'][0], [
                        'idx'      => $idx,
                        'disabled' => $rule['disabled'] || !$active,
                        'name'     => $rule['name'],
                        'tests'    => $rule['tests'],
                        'action'   => $action,
                        'target'   => $target,
                ]);
            }
            else if ($active) {
                $list[$idx] = $rule['name'] ?: ('#' . ($idx + 1));
            }
        }

        $this->vacation['list'] = $list;
    }

    protected function vacation_post()
    {
        if (empty($_POST)) {
            return;
        }

        $date_extension  = in_array('date', $this->exts);
        $regex_extension = in_array('regex', $this->exts);

        // set user's timezone
        try {
            $timezone = new DateTimeZone($this->rc->config->get('timezone', 'GMT'));
        }
        catch (Exception $e) {
            $timezone = new DateTimeZone('GMT');
        }

        $status        = rcube_utils::get_input_string('vacation_status', rcube_utils::INPUT_POST);
        $from          = rcube_utils::get_input_string('vacation_from', rcube_utils::INPUT_POST, true);
        $subject       = rcube_utils::get_input_string('vacation_subject', rcube_utils::INPUT_POST, true);
        $reason        = rcube_utils::get_input_string('vacation_reason', rcube_utils::INPUT_POST, true);
        $addresses     = rcube_utils::get_input_value('vacation_addresses', rcube_utils::INPUT_POST, true);
        $interval      = rcube_utils::get_input_string('vacation_interval', rcube_utils::INPUT_POST);
        $interval_type = rcube_utils::get_input_string('vacation_interval_type', rcube_utils::INPUT_POST);
        $date_from     = rcube_utils::get_input_string('vacation_datefrom', rcube_utils::INPUT_POST);
        $date_to       = rcube_utils::get_input_string('vacation_dateto', rcube_utils::INPUT_POST);
        $time_from     = rcube_utils::get_input_string('vacation_timefrom', rcube_utils::INPUT_POST);
        $time_to       = rcube_utils::get_input_string('vacation_timeto', rcube_utils::INPUT_POST);
        $after         = rcube_utils::get_input_string('vacation_after', rcube_utils::INPUT_POST);
        $action        = rcube_utils::get_input_string('vacation_action', rcube_utils::INPUT_POST);
        $target        = rcube_utils::get_input_string('action_target', rcube_utils::INPUT_POST, true);
        $target_domain = rcube_utils::get_input_string('action_domain', rcube_utils::INPUT_POST);

        $interval_type                   = $interval_type == 'seconds' ? 'seconds' : 'days';
        $vacation_action['type']         = 'vacation';
        $vacation_action['reason']       = $this->strip_value(str_replace("\r\n", "\n", $reason), true);
        $vacation_action['subject']      = trim($subject);
        $vacation_action['from']         = trim($from);
        $vacation_action['addresses']    = $addresses;
        $vacation_action[$interval_type] = $interval;
        $vacation_tests                  = !empty($this->vacation['tests']) ? $this->vacation['tests'] : [];

        foreach ((array) $vacation_action['addresses'] as $aidx => $address) {
            $vacation_action['addresses'][$aidx] = $address = trim($address);

            if (empty($address)) {
                unset($vacation_action['addresses'][$aidx]);
            }
            else if (!rcube_utils::check_email($address)) {
                $error = 'noemailwarning';
                break;
            }
        }

        if (!empty($vacation_action['from'])) {
            // According to RFC5230 the :from string must specify a valid [RFC2822] mailbox-list
            // we'll try to extract addresses and validate them separately
            $from = rcube_mime::decode_address_list($vacation_action['from'], null, true, RCUBE_CHARSET);
            foreach ((array) $from as $idx => $addr) {
                if (empty($addr['mailto']) || !rcube_utils::check_email($addr['mailto'])) {
                    $error = $from_error = 'noemailwarning';
                    break;
                }
                else {
                    $from[$idx] = format_email_recipient($addr['mailto'], $addr['name']);
                }
            }

            // Only one address is allowed (at least on cyrus imap)
            if (is_array($from) && count($from) > 1) {
                $error = $from_error = 'noemailwarning';
            }

            // Then we convert it back to RFC2822 format
            if (empty($from_error) && !empty($from)) {
                $vacation_action['from'] = Mail_mimePart::encodeHeader(
                    'From', implode(', ', $from), RCUBE_CHARSET, 'base64', '');
            }
        }

        if ($vacation_action['reason'] == '') {
            $error = 'managesieve.emptyvacationbody';
        }

        if ($vacation_action[$interval_type] && !preg_match('/^[0-9]+$/', $vacation_action[$interval_type])) {
            $error = 'managesieve.forbiddenchars';
        }

        // find and remove existing date/regex/true rules
        foreach ((array) $vacation_tests as $idx => $t) {
            if ($t['test'] == 'currentdate' || $t['test'] == 'true'
                || ($t['test'] == 'header' && $t['type'] == 'regex' && $t['arg1'] == 'received')
            ) {
                unset($vacation_tests[$idx]);
            }
        }

        if ($date_extension) {
            $date_format = $this->rc->config->get('date_format', 'Y-m-d');
            foreach (['date_from', 'date_to'] as $var) {
                $time = ${str_replace('date', 'time', $var)};
                $date = rcube_utils::format_datestr($$var, $date_format);
                $date = trim($date . ' ' . $time);

                if ($date && ($dt = rcube_utils::anytodatetime($date, $timezone))) {
                    if ($time) {
                        $vacation_tests[] = [
                            'test' => 'currentdate',
                            'part' => 'iso8601',
                            'type' => 'value-' . ($var == 'date_from' ? 'ge' : 'le'),
                            'zone' => $dt->format('O'),
                            'arg'  => str_replace('+00:00', 'Z', strtoupper($dt->format('c'))),
                        ];
                    }
                    else {
                        $vacation_tests[] = [
                            'test' => 'currentdate',
                            'part' => 'date',
                            'type' => 'value-' . ($var == 'date_from' ? 'ge' : 'le'),
                            'zone' => $dt->format('O'),
                            'arg'  => $dt->format('Y-m-d'),
                        ];
                    }
                }
            }
        }
        else if ($regex_extension) {
            // Add date range rules if range specified
            if ($date_from && $date_to) {
                if ($tests = self::build_regexp_tests($date_from, $date_to, $error)) {
                    $vacation_tests = array_merge($vacation_tests, $tests);
                }
            }
        }

        if ($action == 'redirect' || $action == 'copy') {
            if ($target_domain) {
                $target .= '@' . $target_domain;
            }

            if (empty($target) || !rcube_utils::check_email($target)) {
                $error = 'noemailwarning';
            }
        }

        if (empty($vacation_tests)) {
            $vacation_tests = (array) $this->rc->config->get('managesieve_vacation_test', [['test' => 'true']]);
        }

        if (empty($error)) {
            $rule               = $this->vacation;
            $rule['type']       = 'if';
            $rule['name']       = !empty($rule['name']) ? $rule['name'] : $this->plugin->gettext('vacation');
            $rule['disabled']   = $status == 'off';
            $rule['tests']      = $vacation_tests;
            $rule['join']       = $date_extension ? count($vacation_tests) > 1 : false;
            $rule['actions']    = [$vacation_action];
            $rule['after']      = $after;

            if ($action && $action != 'keep') {
                $rule['actions'][] = [
                    'type'   => $action == 'discard' ? 'discard' : 'redirect',
                    'copy'   => $action == 'copy',
                    'target' => $action != 'discard' ? $target : '',
                ];
            }

            if ($this->merge_rule($rule, $this->vacation, $this->script_name)) {
                $this->rc->output->show_message('managesieve.vacationsaved', 'confirmation');
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
     * Independent vacation form
     */
    public function vacation_form($attrib)
    {
        // check supported extensions
        $date_extension    = in_array('date', $this->exts);
        $regex_extension   = in_array('regex', $this->exts);
        $seconds_extension = in_array('vacation-seconds', $this->exts);

        // build FORM tag
        $form_id = !empty($attrib['id']) ? $attrib['id'] : 'form';
        $out     = $this->rc->output->request_form([
                'id'      => $form_id,
                'name'    => $form_id,
                'method'  => 'post',
                'task'    => 'settings',
                'action'  => 'plugin.managesieve-vacation',
                'noclose' => true
            ] + $attrib
        );

        $from_addr = $this->rc->config->get('managesieve_vacation_from_init');
        $auto_addr = $this->rc->config->get('managesieve_vacation_addresses_init');

        if (count($this->vacation) < 2) {
            if ($auto_addr) {
                $this->vacation['addresses'] = $this->user_emails();
            }
            if ($from_addr) {
                $default_identity = $this->rc->user->list_emails(true);
                $this->vacation['from'] = format_email_recipient($default_identity['email'], $default_identity['name']);
            }
        }
        else if (!empty($this->vacation['from'])) {
            $from = rcube_mime::decode_address_list($this->vacation['from'], null, true, RCUBE_CHARSET);
            foreach ((array) $from as $idx => $addr) {
                $from[$idx] = format_email_recipient($addr['mailto'], $addr['name']);
            }
            $this->vacation['from'] = implode(', ', $from);
        }

        // form elements
        $from      = new html_inputfield(['name' => 'vacation_from', 'id' => 'vacation_from', 'size' => 50, 'class' => 'form-control']);
        $subject   = new html_inputfield(['name' => 'vacation_subject', 'id' => 'vacation_subject', 'size' => 50, 'class' => 'form-control']);
        $reason    = new html_textarea(['name' => 'vacation_reason', 'id' => 'vacation_reason', 'cols' => 60, 'rows' => 8]);
        $interval  = new html_inputfield(['name' => 'vacation_interval', 'id' => 'vacation_interval', 'size' => 5, 'class' => 'form-control']);
        $addresses = '<textarea name="vacation_addresses" id="vacation_addresses" data-type="list" data-size="30" style="display: none">'
            . (!empty($this->vacation['addresses']) ? rcube::Q(implode("\n", (array) $this->vacation['addresses']), 'strict', false) : '')
            . '</textarea>';
        $status    = new html_select(['name' => 'vacation_status', 'id' => 'vacation_status', 'class' => 'custom-select']);
        $action    = new html_select(['name' => 'vacation_action', 'id' => 'vacation_action', 'class' => 'custom-select', 'onchange' => 'vacation_action_select()']);
        $addresses_link = new html_inputfield([
                'type'    => 'button',
                'href'    => '#',
                'class' => 'button',
                'onclick' => rcmail_output::JS_OBJECT_NAME . '.managesieve_vacation_addresses()'
            ]);

        $redirect = !empty($this->vacation['action'])
            && ($this->vacation['action'] == 'redirect' || $this->vacation['action'] == 'copy');

        $status->add($this->plugin->gettext('vacation.on'), 'on');
        $status->add($this->plugin->gettext('vacation.off'), 'off');

        $action->add($this->plugin->gettext('vacation.keep'), 'keep');
        $action->add($this->plugin->gettext('vacation.discard'), 'discard');
        if ($redirect || !in_array('redirect', $this->disabled_actions)) {
            $action->add($this->plugin->gettext('vacation.redirect'), 'redirect');
            if (in_array('copy', $this->exts)) {
                $action->add($this->plugin->gettext('vacation.copy'), 'copy');
            }
        }

        if (
            $this->rc->config->get('managesieve_vacation') != 2
            && !empty($this->vacation['list'])
            && in_array($this->script_name, $this->active)
        ) {
            $after = new html_select(['name' => 'vacation_after', 'id' => 'vacation_after', 'class' => 'custom-select']);

            $after->add('---', '');
            foreach ($this->vacation['list'] as $idx => $rule) {
                $after->add($rule, $idx);
            }
        }

        $interval_txt = $interval->show(self::vacation_interval($this->vacation, $this->exts));
        if ($seconds_extension) {
            $interval_select = new html_select(['name' => 'vacation_interval_type', 'class' => 'custom-select']);
            $interval_select->add($this->plugin->gettext('days'), 'days');
            $interval_select->add($this->plugin->gettext('seconds'), 'seconds');
            $interval_txt .= $interval_select->show(isset($this->vacation['seconds']) ? 'seconds' : 'days');
        }
        else {
            $interval_txt .= "\n" . html::span('input-group-append',
                html::span('input-group-text', $this->plugin->gettext('days')));
        }

        $date_format = $this->rc->config->get('date_format', 'Y-m-d');
        $time_format = $this->rc->config->get('time_format', 'H:i');

        if ($date_extension || $regex_extension) {
            $date_from = new html_inputfield(['name' => 'vacation_datefrom', 'id' => 'vacation_datefrom', 'class' => 'datepicker form-control', 'size' => 12]);
            $date_to   = new html_inputfield(['name' => 'vacation_dateto', 'id' => 'vacation_dateto', 'class' => 'datepicker form-control', 'size' => 12]);
        }

        if ($date_extension) {
            $time_from  = new html_inputfield(['name' => 'vacation_timefrom', 'id' => 'vacation_timefrom', 'size' => 7, 'class' => 'form-control']);
            $time_to    = new html_inputfield(['name' => 'vacation_timeto', 'id' => 'vacation_timeto', 'size' => 7, 'class' => 'form-control']);
            $date_value = [];

            if (!empty($this->vacation['tests'])) {
                foreach ((array) $this->vacation['tests'] as $test) {
                    if ($test['test'] == 'currentdate') {
                        $idx = $test['type'] == 'value-ge' ? 'from' : 'to';

                        if ($test['part'] == 'date') {
                            $date_value[$idx]['date'] = $test['arg'];
                        }
                        else if ($test['part'] == 'iso8601') {
                            $date_value[$idx]['datetime'] = $test['arg'];
                        }
                    }
                }
            }

            foreach ($date_value as $idx => $value) {
                $date = !empty($value['datetime']) ? $value['datetime'] : $value['date'];
                $date_value[$idx] = $this->rc->format_date($date, $date_format, false);

                if (!empty($value['datetime'])) {
                    $date_value['time_' . $idx] = $this->rc->format_date($date, $time_format, true);
                }
            }
        }
        else if ($regex_extension) {
            // Sieve 'date' extension not available, read start/end from RegEx based rules instead
            if ($date_tests = self::parse_regexp_tests($this->vacation['tests'])) {
                $date_value['from'] = $this->rc->format_date($date_tests['from'], $date_format, false);
                $date_value['to']   = $this->rc->format_date($date_tests['to'], $date_format, false);
            }
        }

        // force domain selection in redirect email input
        $domains = (array) $this->rc->config->get('managesieve_domains');

        if (!empty($domains)) {
            sort($domains);

            $domain_select = new html_select(['name' => 'action_domain', 'id' => 'action_domain', 'class' => 'custom-select']);
            $domain_select->add(array_combine($domains, $domains));

            if ($redirect && !empty($this->vacation['target'])) {
                $parts = explode('@', $this->vacation['target']);
                if (!empty($parts)) {
                    $this->vacation['domain'] = array_pop($parts);
                    $this->vacation['target'] = implode('@', $parts);
                }
            }
        }

        // redirect target
        $action_target = ' <span id="action_target_span" class="input-group"' . (!$redirect ? ' style="display:none"' : '') . '>'
            . '<input type="text" name="action_target" id="action_target"'
            . ' value="' .($redirect ? rcube::Q($this->vacation['target'], 'strict', false) : '') . '"'
            . (!empty($domain_select) ? ' size="20"' : ' size="35"') . '/>'
            . (!empty($domain_select) ? ' <span class="input-group-append input-group-prepend"><span class="input-group-text">@</span></span>'
                . $domain_select->show(!empty($this->vacation['domain']) ? $this->vacation['domain'] : null) : '')
            . '</span>';

        // Message tab
        $table = new html_table(['cols' => 2]);

        $table->add('title', html::label('vacation_subject', $this->plugin->gettext('vacation.subject')));
        $table->add(null, $subject->show(!empty($this->vacation['subject']) ? $this->vacation['subject'] : null));
        $table->add('title', html::label('vacation_reason', $this->plugin->gettext('vacation.body')));
        $table->add(null, $reason->show(!empty($this->vacation['reason']) ? $this->vacation['reason'] : null));

        if (!empty($date_from)) {
            $table->add('title', html::label('vacation_datefrom', $this->plugin->gettext('vacation.start')));
            $table->add(null, $date_from->show(!empty($date_value['from']) ? $date_value['from'] : null)
                . (!empty($time_from) ? ' ' . $time_from->show(!empty($date_value['time_from']) ? $date_value['time_from'] : null) : '')
            );
            $table->add('title', html::label('vacation_dateto', $this->plugin->gettext('vacation.end')));
            $table->add(null, $date_to->show(!empty($date_value['to']) ? $date_value['to'] : null)
                . (!empty($time_to) ? ' ' . $time_to->show(!empty($date_value['time_to']) ? $date_value['time_to'] : null) : ''));
        }

        $table->add('title', html::label('vacation_status', $this->plugin->gettext('vacation.status')));
        $table->add(null, $status->show(!isset($this->vacation['disabled']) || $this->vacation['disabled'] ? 'off' : 'on'));

        $out .= html::tag('fieldset', '', html::tag('legend', null, $this->plugin->gettext('vacation.reply')) . $table->show($attrib));

        // Advanced tab
        $table = new html_table(['cols' => 2]);

        $table->add('title', html::label('vacation_from', $this->plugin->gettext('vacation.from')));
        $table->add(null, $from->show(!empty($this->vacation['from']) ? $this->vacation['from'] : null));
        $table->add('title', html::label('vacation_addresses', $this->plugin->gettext('vacation.addresses')));
        $table->add(null, $addresses . $addresses_link->show($this->plugin->gettext('filladdresses')));
        $table->add('title', html::label('vacation_interval', $this->plugin->gettext('vacation.interval')));
        $table->add(null, html::span('input-group', $interval_txt));

        if (!empty($after)) {
            $table->add('title', html::label('vacation_after', $this->plugin->gettext('vacation.after')));
            $table->add(null, $after->show(isset($this->vacation['idx']) ? $this->vacation['idx'] - 1 : ''));
        }

        $table->add('title', html::label('vacation_action', $this->plugin->gettext('vacation.action')));
        $table->add('vacation input-group input-group-combo',
            $action->show(!empty($this->vacation['action']) ? $this->vacation['action'] : null) . $action_target
        );

        $out .= html::tag('fieldset', '', html::tag('legend', null, $this->plugin->gettext('vacation.advanced'))
            . $table->show($attrib)
        );

        $out .= '</form>';

        $this->rc->output->add_gui_object('sieveform', $form_id);

        if (!empty($time_format)) {
            $this->rc->output->set_env('time_format', $time_format);
        }

        return $out;
    }

    protected static function build_regexp_tests($date_from, $date_to, &$error)
    {
        $tests    = [];
        $dt_from  = rcube_utils::anytodatetime($date_from);
        $dt_to    = rcube_utils::anytodatetime($date_to);
        $interval = $dt_from->diff($dt_to);

        if ($interval->invert || $interval->days > 365) {
            $error = 'managesieve.invaliddateformat';
            return;
        }

        $dt_i     = $dt_from;
        $interval = new DateInterval('P1D');
        $matchexp = '';

        while (!$dt_i->diff($dt_to)->invert) {
            $days     = (int) $dt_i->format('d');
            $matchexp .= $days < 10 ? "[ 0]$days" : $days;

            if ($days == $dt_i->format('t') || $dt_i->diff($dt_to)->days == 0) {
                $test = [
                    'test' => 'header',
                    'type' => 'regex',
                    'arg1' => 'received',
                    'arg2' => "($matchexp) " . $dt_i->format('M Y')
                ];

                $tests[]  = $test;
                $matchexp = '';
            }
            else {
                $matchexp .= '|';
            }

            $dt_i->add($interval);
        }

        return $tests;
    }

    protected static function parse_regexp_tests($tests)
    {
        $rx_from = '/^\(([0-9]{2}).*\)\s([A-Za-z]+)\s([0-9]{4})/';
        $rx_to   = '/^\(.*([0-9]{2})\)\s([A-Za-z]+)\s([0-9]{4})/';
        $result  = [];

        foreach ((array) $tests as $test) {
            if ($test['test'] == 'header' && $test['type'] == 'regex' && $test['arg1'] == 'received') {
                $textexp = preg_replace('/\[ ([^\]]*)\]/', '0', $test['arg2']);

                if (empty($result['from']) && preg_match($rx_from, $textexp, $matches)) {
                    $result['from'] = $matches[1]." ".$matches[2]." ".$matches[3];
                }

                if (preg_match($rx_to, $textexp, $matches)) {
                    $result['to'] = $matches[1]." ".$matches[2]." ".$matches[3];
                }
            }
        }

        return $result;
    }

    /**
     * Get current vacation interval
     */
    public static function vacation_interval(&$vacation, $extensions = [])
    {
        $rcube = rcube::get_instance();
        $seconds_extension = in_array('vacation-seconds', $extensions);

        if (isset($vacation['seconds'])) {
            $interval = $vacation['seconds'];
        }
        else if (isset($vacation['days'])) {
            $interval = $vacation['days'];
        }
        else if ($interval_cfg = $rcube->config->get('managesieve_vacation_interval')) {
            if (preg_match('/^([0-9]+)s$/', $interval_cfg, $m)) {
                if ($seconds_extension) {
                    $vacation['seconds'] = ($interval = intval($m[1])) ? $interval : null;
                }
                else {
                    $vacation['days'] = $interval = ceil(intval($m[1])/86400);
                }
            }
            else {
                $vacation['days'] = $interval = intval($interval_cfg);
            }
        }

        return !empty($interval) ? $interval : '';
    }

    /**
     * API: get vacation rule
     *
     * @return array Vacation rule information
     */
    public function get_vacation()
    {
        $this->exts = $this->sieve->get_extensions();
        $this->init_script();
        $this->vacation_rule();

        // check supported extensions
        $date_extension  = in_array('date', $this->exts);
        $regex_extension = in_array('regex', $this->exts);

        // set user's timezone
        try {
            $timezone = new DateTimeZone($this->rc->config->get('timezone', 'GMT'));
        }
        catch (Exception $e) {
            $timezone = new DateTimeZone('GMT');
        }

        $interval = null;
        $start    = null;
        $end      = null;

        if ($date_extension) {
            $date_value = [];
            if (!empty($this->vacation['tests'])) {
                foreach ((array) $this->vacation['tests'] as $test) {
                    if ($test['test'] == 'currentdate') {
                        $idx = $test['type'] == 'value-ge' ? 'start' : 'end';

                        if ($test['part'] == 'date') {
                            $date_value[$idx]['date'] = $test['arg'];
                        }
                        else if ($test['part'] == 'iso8601') {
                            $date_value[$idx]['datetime'] = $test['arg'];
                        }
                    }
                }
            }

            foreach ($date_value as $idx => $value) {
                ${$idx} = new DateTime(!empty($value['datetime']) ? $value['datetime'] : $value['date'], $timezone);
            }
        }
        else if ($regex_extension) {
            // Sieve 'date' extension not available, read start/end from RegEx based rules instead
            if ($date_tests = self::parse_regexp_tests($this->vacation['tests'])) {
                $start = new DateTime($date_tests['from'] . ' ' . '00:00:00', $timezone);
                $end   = new DateTime($date_tests['to'] . ' ' . '23:59:59', $timezone);
            }
        }

        if (isset($this->vacation['seconds'])) {
            $interval = $this->vacation['seconds'] . 's';
        }
        else if (isset($this->vacation['days'])) {
            $interval = $this->vacation['days'] . 'd';
        }

        $vacation = [
            'supported' => $this->exts,
            'interval'  => $interval,
            'start'     => $start,
            'end'       => $end,
            'enabled'   => !empty($this->vacation['reason']) && empty($this->vacation['disabled']),
            'message'   => isset($this->vacation['reason']) ? $this->vacation['reason'] : null,
            'subject'   => isset($this->vacation['subject']) ? $this->vacation['subject'] : null,
            'action'    => isset($this->vacation['action']) ? $this->vacation['action'] : null,
            'target'    => isset($this->vacation['target']) ? $this->vacation['target'] : null,
            'addresses' => isset($this->vacation['addresses']) ? $this->vacation['addresses'] : null,
            'from'      => isset($this->vacation['from']) ? $this->vacation['from'] : null,
        ];

        return $vacation;
    }

    /**
     * API: set vacation rule
     *
     * @param array $data Vacation rule information (see self::get_vacation())
     *
     * @return bool True on success, False on failure
     */
    public function set_vacation($data)
    {
        $this->exts  = $this->sieve->get_extensions();
        $this->error = false;

        $this->init_script();
        $this->vacation_rule();

        // check supported extensions
        $date_extension  = in_array('date', $this->exts);
        $regex_extension = in_array('regex', $this->exts);

        $vacation['type']      = 'vacation';
        $vacation['reason']    = $this->strip_value(str_replace("\r\n", "\n", $data['message']), true);
        $vacation['addresses'] = $data['addresses'];
        $vacation['subject']   = trim($data['subject']);
        $vacation['from']      = trim($data['from']);
        $vacation_tests        = (array) $this->vacation['tests'];

        foreach ((array) $vacation['addresses'] as $aidx => $address) {
            $vacation['addresses'][$aidx] = $address = trim($address);

            if (empty($address)) {
                unset($vacation['addresses'][$aidx]);
            }
            else if (!rcube_utils::check_email($address)) {
                $this->error = "Invalid address in vacation addresses: $address";
                return false;
            }
        }

        if (!empty($vacation['from']) && !rcube_utils::check_email($vacation['from'])) {
            $this->error = "Invalid address in 'from': " . $vacation['from'];
            return false;
        }

        if ($vacation['reason'] == '') {
            $this->error = "No vacation message specified";
            return false;
        }

        if (!empty($data['interval'])) {
            if (!preg_match('/^([0-9]+)\s*([sd])$/', $data['interval'], $m)) {
                $this->error = "Invalid vacation interval value: " . $data['interval'];
                return false;
            }
            else if ($m[1]) {
                $vacation[strtolower($m[2]) == 's' ? 'seconds' : 'days'] = $m[1];
            }
        }

        // find and remove existing date/regex/true rules
        foreach ((array) $vacation_tests as $idx => $t) {
            if ($t['test'] == 'currentdate' || $t['test'] == 'true'
                || ($t['test'] == 'header' && $t['type'] == 'regex' && $t['arg1'] == 'received')
            ) {
                unset($vacation_tests[$idx]);
            }
        }

        if ($date_extension) {
            foreach (['start', 'end'] as $var) {
                if (!empty($data[$var])) {
                    $dt = $data[$var];
                    $vacation_tests[] = [
                        'test' => 'currentdate',
                        'part' => 'iso8601',
                        'type' => 'value-' . ($var == 'start' ? 'ge' : 'le'),
                        'zone' => $dt->format('O'),
                        'arg'  => str_replace('+00:00', 'Z', strtoupper($dt->format('c'))),
                    ];
                }
            }
        }
        else if ($regex_extension) {
            // Add date range rules if range specified
            if (!empty($data['start']) && !empty($data['end'])) {
                if ($tests = self::build_regexp_tests($data['start'], $data['end'], $error)) {
                    $vacation_tests = array_merge($vacation_tests, $tests);
                }

                if ($error) {
                    $this->error = "Invalid dates specified or unsupported period length";
                    return false;
                }
            }
        }

        if ($data['action'] == 'redirect' || $data['action'] == 'copy') {
            if (empty($data['target']) || !rcube_utils::check_email($data['target'])) {
                $this->error = "Invalid address in action target: " . $data['target'];
                return false;
            }
        }
        else if ($data['action'] && $data['action'] != 'keep' && $data['action'] != 'discard') {
            $this->error = "Unsupported vacation action: " . $data['action'];
            return false;
        }

        if (empty($vacation_tests)) {
            $vacation_tests = (array) $this->rc->config->get('managesieve_vacation_test', [['test' => 'true']]);
        }

        $rule             = $this->vacation;
        $rule['type']     = 'if';
        $rule['name']     = !empty($rule['name']) ? $rule['name'] : 'Out-of-Office';
        $rule['disabled'] = isset($data['enabled']) && !$data['enabled'];
        $rule['tests']    = $vacation_tests;
        $rule['join']     = $date_extension ? count($vacation_tests) > 1 : false;
        $rule['actions']  = [$vacation];

        if (!empty($data['action']) && $data['action'] != 'keep') {
            $rule['actions'][] = [
                'type'   => $data['action'] == 'discard' ? 'discard' : 'redirect',
                'copy'   => $data['action'] == 'copy',
                'target' => $data['action'] != 'discard' ? $data['target'] : '',
            ];
        }

        return $this->merge_rule($rule, $this->vacation, $this->script_name);
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
