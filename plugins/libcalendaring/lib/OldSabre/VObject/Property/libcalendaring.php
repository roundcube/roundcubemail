<?php

/**
 * Library providing common functions for calendaring plugins
 *
 * Provides utility functions for calendar-related modules such as
 * - alarms display and dismissal
 * - attachment handling
 * - recurrence computation and UI elements
 * - ical parsing and exporting
 * - itip scheduling protocol
 *
 * @version @package_version@
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2012-2015, Kolab Systems AG <contact@kolabsys.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class libcalendaring extends rcube_plugin
{
    public $rc;
    public $timezone;
    public $gmt_offset;
    public $dst_active;
    public $timezone_offset;
    public $ical_parts = array();
    public $ical_message;

    public $defaults = array(
      'calendar_date_format'  => "yyyy-MM-dd",
      'calendar_date_short'   => "M-d",
      'calendar_date_long'    => "MMM d yyyy",
      'calendar_date_agenda'  => "ddd MM-dd",
      'calendar_time_format'  => "HH:mm",
      'calendar_first_day'    => 1,
      'calendar_first_hour'   => 6,
      'calendar_date_format_sets' => array(
        'yyyy-MM-dd' => array('MMM d yyyy',   'M-d',  'ddd MM-dd'),
        'dd-MM-yyyy' => array('d MMM yyyy',   'd-M',  'ddd dd-MM'),
        'yyyy/MM/dd' => array('MMM d yyyy',   'M/d',  'ddd MM/dd'),
        'MM/dd/yyyy' => array('MMM d yyyy',   'M/d',  'ddd MM/dd'),
        'dd/MM/yyyy' => array('d MMM yyyy',   'd/M',  'ddd dd/MM'),
        'dd.MM.yyyy' => array('dd. MMM yyyy', 'd.M',  'ddd dd.MM.'),
        'd.M.yyyy'   => array('d. MMM yyyy',  'd.M',  'ddd d.MM.'),
      ),
    );

    private static $instance;
    private static $email_regex = '/([a-z0-9][a-z0-9\-\.\+\_]*@[^&@"\'.][^@&"\']*\\.([^\\x00-\\x40\\x5b-\\x60\\x7b-\\x7f]{2,}|xn--[a-z0-9]{2,}))/';

    private $mail_ical_parser;

    /**
     * Singleton getter to allow direct access from other plugins
     */
    public static function get_instance()
    {
        return self::$instance;
    }

    /**
     * Required plugin startup method
     */
    public function init()
    {
        self::$instance = $this;

        $this->rc = rcube::get_instance();

        // set user's timezone
        try {
            $this->timezone = new DateTimeZone($this->rc->config->get('timezone', 'GMT'));
        }
        catch (Exception $e) {
            $this->timezone = new DateTimeZone('GMT');
        }

        $now = new DateTime('now', $this->timezone);

        $this->gmt_offset      = $now->getOffset();
        $this->dst_active      = $now->format('I');
        $this->timezone_offset = $this->gmt_offset / 3600 - $this->dst_active;

        $this->add_texts('localization/', false);

        // include client scripts and styles
        if ($this->rc->output) {
            // add hook to display alarms
            $this->add_hook('refresh', array($this, 'refresh'));
            $this->register_action('plugin.alarms', array($this, 'alarms_action'));
            $this->register_action('plugin.expand_attendee_group', array($this, 'expand_attendee_group'));
        }

        // proceed initialization in startup hook
        $this->add_hook('startup', array($this, 'startup'));
    }

    /**
     * Startup hook
     */
    public function startup($args)
    {
        if ($this->rc->output && $this->rc->output->type == 'html') {
            $this->rc->output->set_env('libcal_settings', $this->load_settings());
            $this->include_script('libcalendaring.js');
            $this->include_stylesheet($this->local_skin_path() . '/libcal.css');
        }

        if ($args['task'] == 'mail') {
            if ($args['action'] == 'show' || $args['action'] == 'preview') {
                $this->add_hook('message_load', array($this, 'mail_message_load'));
            }
        }
    }

    /**
     * Load iCalendar functions
     */
    public static function get_ical()
    {
        $self = self::get_instance();
        require_once($self->home . '/libvcalendar.php');
        return new libvcalendar();
    }

    /**
     * Load iTip functions
     */
    public static function get_itip($domain = 'libcalendaring')
    {
        $self = self::get_instance();
        require_once($self->home . '/lib/libcalendaring_itip.php');
        return new libcalendaring_itip($self, $domain);
    }

    /**
     * Load recurrence computation engine
     */
    public static function get_recurrence()
    {
        $self = self::get_instance();
        require_once($self->home . '/lib/libcalendaring_recurrence.php');
        return new libcalendaring_recurrence($self);
    }

    /**
     * Shift dates into user's current timezone
     *
     * @param mixed Any kind of a date representation (DateTime object, string or unix timestamp)
     * @return object DateTime object in user's timezone
     */
    public function adjust_timezone($dt, $dateonly = false)
    {
        if (is_numeric($dt))
            $dt = new DateTime('@'.$dt);
        else if (is_string($dt))
            $dt = rcube_utils::anytodatetime($dt);

        if ($dt instanceof DateTime && !($dt->_dateonly || $dateonly)) {
            $dt->setTimezone($this->timezone);
        }

        return $dt;
    }


    /**
     *
     */
    public function load_settings()
    {
        $this->date_format_defaults();
        $settings = array();

        // configuration
        $settings['date_format'] = (string)$this->rc->config->get('calendar_date_format', $this->defaults['calendar_date_format']);
        $settings['time_format'] = (string)$this->rc->config->get('calendar_time_format', $this->defaults['calendar_time_format']);
        $settings['date_short']  = (string)$this->rc->config->get('calendar_date_short', $this->defaults['calendar_date_short']);
        $settings['date_long']   = (string)$this->rc->config->get('calendar_date_long', $this->defaults['calendar_date_long']);
        $settings['dates_long']  = str_replace(' yyyy', '[ yyyy]', $settings['date_long']) . "{ '&mdash;' " . $settings['date_long'] . '}';
        $settings['first_day']   = (int)$this->rc->config->get('calendar_first_day', $this->defaults['calendar_first_day']);

        $settings['timezone'] = $this->timezone_offset;
        $settings['dst'] = $this->dst_active;

        // localization
        $settings['days'] = array(
            $this->rc->gettext('sunday'),   $this->rc->gettext('monday'),
            $this->rc->gettext('tuesday'),  $this->rc->gettext('wednesday'),
            $this->rc->gettext('thursday'), $this->rc->gettext('friday'),
            $this->rc->gettext('saturday')
        );
        $settings['days_short'] = array(
            $this->rc->gettext('sun'), $this->rc->gettext('mon'),
            $this->rc->gettext('tue'), $this->rc->gettext('wed'),
            $this->rc->gettext('thu'), $this->rc->gettext('fri'),
            $this->rc->gettext('sat')
        );
        $settings['months'] = array(
            $this->rc->gettext('longjan'), $this->rc->gettext('longfeb'),
            $this->rc->gettext('longmar'), $this->rc->gettext('longapr'),
            $this->rc->gettext('longmay'), $this->rc->gettext('longjun'),
            $this->rc->gettext('longjul'), $this->rc->gettext('longaug'),
            $this->rc->gettext('longsep'), $this->rc->gettext('longoct'),
            $this->rc->gettext('longnov'), $this->rc->gettext('longdec')
        );
        $settings['months_short'] = array(
            $this->rc->gettext('jan'), $this->rc->gettext('feb'),
            $this->rc->gettext('mar'), $this->rc->gettext('apr'),
            $this->rc->gettext('may'), $this->rc->gettext('jun'),
            $this->rc->gettext('jul'), $this->rc->gettext('aug'),
            $this->rc->gettext('sep'), $this->rc->gettext('oct'),
            $this->rc->gettext('nov'), $this->rc->gettext('dec')
        );
        $settings['today'] = $this->rc->gettext('today');

        // define list of file types which can be displayed inline
        // same as in program/steps/mail/show.inc
        $settings['mimetypes'] = (array)$this->rc->config->get('client_mimetypes');

        return $settings;
    }


    /**
     * Helper function to set date/time format according to config and user preferences
     */
    private function date_format_defaults()
    {
        static $defaults = array();

        // nothing to be done
        if (isset($defaults['date_format']))
          return;

        $defaults['date_format'] = $this->rc->config->get('calendar_date_format', self::from_php_date_format($this->rc->config->get('date_format')));
        $defaults['time_format'] = $this->rc->config->get('calendar_time_format', self::from_php_date_format($this->rc->config->get('time_format')));

        // override defaults
        if ($defaults['date_format'])
            $this->defaults['calendar_date_format'] = $defaults['date_format'];
        if ($defaults['time_format'])
            $this->defaults['calendar_time_format'] = $defaults['time_format'];

        // derive format variants from basic date format
        $format_sets = $this->rc->config->get('calendar_date_format_sets', $this->defaults['calendar_date_format_sets']);
        if ($format_set = $format_sets[$this->defaults['calendar_date_format']]) {
            $this->defaults['calendar_date_long'] = $format_set[0];
            $this->defaults['calendar_date_short'] = $format_set[1];
            $this->defaults['calendar_date_agenda'] = $format_set[2];
        }
    }

    /**
     * Compose a date string for the given event
     */
    public function event_date_text($event, $tzinfo = false)
    {
        $fromto = '--';

        // handle task objects
        if ($event['_type'] == 'task' && is_object($event['due'])) {
            $date_format = $event['due']->_dateonly ? self::to_php_date_format($this->rc->config->get('calendar_date_format', $this->defaults['calendar_date_format'])) : null;
            $fromto = rcmail::get_instance()->format_date($event['due'], $date_format, false);

            // add timezone information
            if ($fromto && $tzinfo && ($tzname = $this->timezone->getName())) {
                $fromto .= ' (' . strtr($tzname, '_', ' ') . ')';
            }

            return $fromto;
        }

        // abort if no valid event dates are given
        if (!is_object($event['start']) || !is_a($event['start'], 'DateTime') || !is_object($event['end']) || !is_a($event['end'], 'DateTime')) {
            return $fromto;
        }

        $duration = $event['start']->diff($event['end'])->format('s');

        $this->date_format_defaults();
        $date_format = self::to_php_date_format($this->rc->config->get('calendar_date_format', $this->defaults['calendar_date_format']));
        $time_format = self::to_php_date_format($this->rc->config->get('calendar_time_format', $this->defaults['calendar_time_format']));

        if ($event['allday']) {
            $fromto = rcmail::get_instance()->format_date($event['start'], $date_format);
            if (($todate = rcmail::get_instance()->format_date($event['end'], $date_format)) != $fromto)
                $fromto .= ' - ' . $todate;
        }
        else if ($duration < 86400 && $event['start']->format('d') == $event['end']->format('d')) {
            $fromto = rcmail::get_instance()->format_date($event['start'], $date_format) . ' ' . rcmail::get_instance()->format_date($event['start'], $time_format) .
                ' - ' . rcmail::get_instance()->format_date($event['end'], $time_format);
        }
        else {
            $fromto = rcmail::get_instance()->format_date($event['start'], $date_format) . ' ' . rcmail::get_instance()->format_date($event['start'], $time_format) .
                ' - ' . rcmail::get_instance()->format_date($event['end'], $date_format) . ' ' . rcmail::get_instance()->format_date($event['end'], $time_format);
        }

        // add timezone information
        if ($tzinfo && ($tzname = $this->timezone->getName())) {
            $fromto .= ' (' . strtr($tzname, '_', ' ') . ')';
        }

        return $fromto;
    }


    /**
     * Render HTML form for alarm configuration
     */
    public function alarm_select($attrib, $alarm_types, $absolute_time = true)
    {
        unset($attrib['name']);
        $select_type = new html_select(array('name' => 'alarmtype[]', 'class' => 'edit-alarm-type', 'id' => $attrib['id']));
        $select_type->add($this->gettext('none'), '');
        foreach ($alarm_types as $type)
            $select_type->add($this->gettext(strtolower("alarm{$type}option")), $type);

        $input_value = new html_inputfield(array('name' => 'alarmvalue[]', 'class' => 'edit-alarm-value', 'size' => 3));
        $input_date = new html_inputfield(array('name' => 'alarmdate[]', 'class' => 'edit-alarm-date', 'size' => 10));
        $input_time = new html_inputfield(array('name' => 'alarmtime[]', 'class' => 'edit-alarm-time', 'size' => 6));

        $select_offset = new html_select(array('name' => 'alarmoffset[]', 'class' => 'edit-alarm-offset'));
        foreach (array('-M','-H','-D','+M','+H','+D') as $trigger)
            $select_offset->add($this->gettext('trigger' . $trigger), $trigger);

        if ($absolute_time)
            $select_offset->add($this->gettext('trigger@'), '@');

        // pre-set with default values from user settings
        $preset = self::parse_alarm_value($this->rc->config->get('calendar_default_alarm_offset', '-15M'));
        $hidden = array('style' => 'display:none');
        $html = html::span('edit-alarm-set',
            $select_type->show($this->rc->config->get('calendar_default_alarm_type', '')) . ' ' .
            html::span(array('class' => 'edit-alarm-values', 'style' => 'display:none'),
                $input_value->show($preset[0]) . ' ' .
                $select_offset->show($preset[1]) . ' ' .
                $input_date->show('', $hidden) . ' ' .
                $input_time->show('', $hidden)
            )
        );

        // TODO: support adding more alarms
        #$html .= html::a(array('href' => '#', 'id' => 'edit-alam-add', 'title' => $this->gettext('addalarm')),
        #  $attrib['addicon'] ? html::img(array('src' => $attrib['addicon'], 'alt' => 'add')) : '(+)');

        return $html;
    }

    /**
     * Get a list of email addresses of the given user (from login and identities)
     *
     * @param string User Email (default to current user)
     * @return array Email addresses related to the user
     */
    public function get_user_emails($user = null)
    {
        static $_emails = array();

        if (empty($user)) {
            $user = $this->rc->user->get_username();
        }

        // return cached result
        if (is_array($_emails[$user])) {
            return $_emails[$user];
        }

        $emails = array($user);
        $plugin = $this->rc->plugins->exec_hook('calendar_user_emails', array('emails' => $emails));
        $emails = array_map('strtolower', $plugin['emails']);

        // add all emails from the current user's identities
        if (!$plugin['abort'] && ($user == $this->rc->user->get_username())) {
            foreach ($this->rc->user->list_emails() as $identity) {
                $emails[] = strtolower($identity['email']);
            }
        }

        $_emails[$user] = array_unique($emails);
        return $_emails[$user];
    }

    /**
     * Set the given participant status to the attendee matching the current user's identities
     *
     * @param array   Hash array with event struct
     * @param string  The PARTSTAT value to set
     * @return mixed  Email address of the updated attendee or False if none matching found
     */
    public function set_partstat(&$event, $status, $recursive = true)
    {
        $success = false;
        $emails = $this->get_user_emails();
        foreach ((array)$event['attendees'] as $i => $attendee) {
            if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
                $event['attendees'][$i]['status'] = strtoupper($status);
                $success = $attendee['email'];
            }
        }

        // apply partstat update to each existing exception
        if ($event['recurrence'] && is_array($event['recurrence']['EXCEPTIONS'])) {
            foreach ($event['recurrence']['EXCEPTIONS'] as $i => $exception) {
                $this->set_partstat($event['recurrence']['EXCEPTIONS'][$i], $status, false);
            }

            // set link to top-level exceptions
            $event['exceptions'] = &$event['recurrence']['EXCEPTIONS'];
        }

        return $success;
    }


    /*********  Alarms handling  *********/

    /**
     * Helper function to convert alarm trigger strings
     * into two-field values (e.g. "-45M" => 45, "-M")
     */
    public static function parse_alarm_value($val)
    {
        if ($val[0] == '@') {
            return array(new DateTime($val));
        }
        else if (preg_match('/([+-]?)P?(T?\d+[HMSDW])+/', $val, $m) && preg_match_all('/T?(\d+)([HMSDW])/', $val, $m2, PREG_SET_ORDER)) {
            if ($m[1] == '')
                $m[1] = '+';
            foreach ($m2 as $seg) {
                $prefix = $seg[2] == 'D' || $seg[2] == 'W' ? 'P' : 'PT';
                if ($seg[1] > 0) {  // ignore zero values
                    // convert seconds to minutes
                    if ($seg[2] == 'S') {
                        $seg[2] = 'M';
                        $seg[1] = max(1, round($seg[1]/60));
                    }

                    return array($seg[1], $m[1].$seg[2], $m[1].$seg[1].$seg[2], $m[1].$prefix.$seg[1].$seg[2]);
                }
            }

            // return zero value nevertheless
            return array($seg[1], $m[1].$seg[2], $m[1].$seg[1].$seg[2], $m[1].$prefix.$seg[1].$seg[2]);
        }

        return false;
    }

    /**
     * Convert the alarms list items to be processed on the client
     */
    public static function to_client_alarms($valarms)
    {
        return array_map(function($alarm){
            if ($alarm['trigger'] instanceof DateTime) {
                $alarm['trigger'] = '@' . $alarm['trigger']->format('U');
            }
            else if ($trigger = libcalendaring::parse_alarm_value($alarm['trigger'])) {
                $alarm['trigger'] = $trigger[2];
            }
            return $alarm;
        }, (array)$valarms);
    }

    /**
     * Process the alarms values submitted by the client
     */
    public static function from_client_alarms($valarms)
    {
        return array_map(function($alarm){
            if ($alarm['trigger'][0] == '@') {
                try {
                    $alarm['trigger'] = new DateTime($alarm['trigger']);
                    $alarm['trigger']->setTimezone(new DateTimeZone('UTC'));
                }
                catch (Exception $e) { /* handle this ? */ }
            }
            else if ($trigger = libcalendaring::parse_alarm_value($alarm['trigger'])) {
                $alarm['trigger'] = $trigger[3];
            }
            return $alarm;
        }, (array)$valarms);
    }

    /**
     * Render localized text for alarm settings
     */
    public static function alarms_text($alarms)
    {
        if (is_array($alarms) && is_array($alarms[0])) {
            $texts = array();
            foreach ($alarms as $alarm) {
                if ($text = self::alarm_text($alarm))
                    $texts[] = $text;
            }

            return join(', ', $texts);
        }
        else {
            return self::alarm_text($alarms);
        }
    }

    /**
     * Render localized text for a single alarm property
     */
    public static function alarm_text($alarm)
    {
        if (is_string($alarm)) {
            list($trigger, $action) = explode(':', $alarm);
        }
        else {
            $trigger = $alarm['trigger'];
            $action = $alarm['action'];
        }

        $text = '';
        $rcube = rcube::get_instance();

        switch ($action) {
        case 'EMAIL':
            $text = $rcube->gettext('libcalendaring.alarmemail');
            break;
        case 'DISPLAY':
            $text = $rcube->gettext('libcalendaring.alarmdisplay');
            break;
        case 'AUDIO':
            $text = $rcube->gettext('libcalendaring.alarmaudio');
            break;
        }

        if ($trigger instanceof DateTime) {
            $text .= ' ' . $rcube->gettext(array(
                'name' => 'libcalendaring.alarmat',
                'vars' => array('datetime' => rcmail::get_instance()->format_date($trigger))
            ));
        }
        else if (preg_match('/@(\d+)/', $trigger, $m)) {
            $text .= ' ' . $rcube->gettext(array(
                'name' => 'libcalendaring.alarmat',
                'vars' => array('datetime' => rcmail::get_instance()->format_date($m[1]))
            ));
        }
        else if ($val = self::parse_alarm_value($trigger)) {
            // TODO: for all-day events say 'on date of event at XX' ?
            if ($val[0] == 0)
                $text .= ' ' . $rcube->gettext('libcalendaring.triggerattime');
            else
                $text .= ' ' . intval($val[0]) . ' ' . $rcube->gettext('libcalendaring.trigger' . $val[1]);
        }
        else {
            return false;
        }

        return $text;
    }

    /**
     * Get the next alarm (time & action) for the given event
     *
     * @param array Record data
     * @return array Hash array with alarm time/type or null if no alarms are configured
     */
    public static function get_next_alarm($rec, $type = 'event')
    {
        if (!($rec['valarms'] || $rec['alarms']) || $rec['cancelled'] || $rec['status'] == 'CANCELLED')
            return null;

        if ($type == 'task') {
            $timezone = self::get_instance()->timezone;
            if ($rec['startdate'])
                $rec['start'] = new DateTime($rec['startdate'] . ' ' . ($rec['starttime'] ?: '12:00'), $timezone);
            if ($rec['date'])
                $rec[($rec['start'] ? 'end' : 'start')] = new DateTime($rec['date'] . ' ' . ($rec['time'] ?: '12:00'), $timezone);
        }

        if (!$rec['end'])
            $rec['end'] = $rec['start'];

        // support legacy format
        if (!$rec['valarms']) {
            list($trigger, $action) = explode(':', $rec['alarms'], 2);
            if ($alarm = self::parse_alarm_value($trigger)) {
                $rec['valarms'] = array(array('action' => $action, 'trigger' => $alarm[3] ?: $alarm[0]));
            }
        }

        $expires = new DateTime('now - 12 hours');
        $alarm_id = $rec['id'];  // alarm ID eq. record ID by default to keep backwards compatibility

        // handle multiple alarms
        $notify_at = null;
        foreach ($rec['valarms'] as $alarm) {
            $notify_time = null;

            if ($alarm['trigger'] instanceof DateTime) {
                $notify_time = $alarm['trigger'];
            }
            else if (is_string($alarm['trigger'])) {
                $refdate = $alarm['trigger'][0] == '+' ? $rec['end'] : $rec['start'];

                // abort if no reference date is available to compute notification time
                if (!is_a($refdate, 'DateTime'))
                    continue;

                // TODO: for all-day events, take start @ 00:00 as reference date ?

                try {
                    $interval = new DateInterval(trim($alarm['trigger'], '+-'));
                    $interval->invert = $alarm['trigger'][0] != '+';
                    $notify_time = clone $refdate;
                    $notify_time->add($interval);
                }
                catch (Exception $e) {
                    rcube::raise_error($e, true);
                    continue;
                }
            }

            if ($notify_time && (!$notify_at || ($notify_time > $notify_at && $notify_time > $expires))) {
                $notify_at = $notify_time;
                $action = $alarm['action'];
                $alarm_prop = $alarm;

                // generate a unique alarm ID if multiple alarms are set
                if (count($rec['valarms']) > 1) {
                    $alarm_id = substr(md5($rec['id']), 0, 16) . '-' . $notify_at->format('Ymd\THis');
                }
            }
        }

        return !$notify_at ? null : array(
            'time'   => $notify_at->format('U'),
            'action' => $action ? strtoupper($action) : 'DISPLAY',
            'id'     => $alarm_id,
            'prop'   => $alarm_prop,
        );
    }

    /**
     * Handler for keep-alive requests
     * This will check for pending notifications and pass them to the client
     */
    public function refresh($attr)
    {
        // collect pending alarms from all providers (e.g. calendar, tasks)
        $plugin = $this->rc->plugins->exec_hook('pending_alarms', array(
            'time' => time(),
            'alarms' => array(),
        ));

        if (!$plugin['abort'] && !empty($plugin['alarms'])) {
            // make sure texts and env vars are available on client
            $this->add_texts('localization/', true);
            $this->rc->output->add_label('close');
            $this->rc->output->set_env('snooze_select', $this->snooze_select());
            $this->rc->output->command('plugin.display_alarms', $this->_alarms_output($plugin['alarms']));
        }
    }

    /**
     * Handler for alarm dismiss/snooze requests
     */
    public function alarms_action()
    {
        $action = rcube_utils::get_input_value('action', rcube_utils::INPUT_GPC);
        $data  = rcube_utils::get_input_value('data', rcube_utils::INPUT_POST, true);

        $data['ids'] = explode(',', $data['id']);
        $plugin = $this->rc->plugins->exec_hook('dismiss_alarms', $data);

        if ($plugin['success'])
            $this->rc->output->show_message('successfullysaved', 'confirmation');
        else
            $this->rc->output->show_message('calendar.errorsaving', 'error');
    }

    /**
     * Generate reduced and streamlined output for pending alarms
     */
    private function _alarms_output($alarms)
    {
        $out = array();
        foreach ($alarms as $alarm) {
            $out[] = array(
                'id'       => $alarm['id'],
                'start'    => $alarm['start'] ? $this->adjust_timezone($alarm['start'])->format('c') : '',
                'end'      => $alarm['end']   ? $this->adjust_timezone($alarm['end'])->format('c') : '',
                'allDay'   => ($alarm['allday'] == 1)?true:false,
                'title'    => $alarm['title'],
                'location' => $alarm['location'],
                'calendar' => $alarm['calendar'],
            );
        }

        return $out;
    }

    /**
     * Render a dropdown menu to choose snooze time
     */
    private function snooze_select($attrib = array())
    {
        $steps = array(
             5 => 'repeatinmin',
            10 => 'repeatinmin',
            15 => 'repeatinmin',
            20 => 'repeatinmin',
            30 => 'repeatinmin',
            60 => 'repeatinhr',
            120 => 'repeatinhrs',
            1440 => 'repeattomorrow',
            10080 => 'repeatinweek',
        );

        $items = array();
        foreach ($steps as $n => $label) {
            $items[] = html::tag('li', null, html::a(array('href' => "#" . ($n * 60), 'class' => 'active'),
                $this->gettext(array('name' => $label, 'vars' => array('min' => $n % 60, 'hrs' => intval($n / 60))))));
        }

        return html::tag('ul', $attrib + array('class' => 'toolbarmenu'), join("\n", $items), html::$common_attrib);
    }


    /*********  Recurrence rules handling ********/

    /**
     * Render localized text describing the recurrence rule of an event
     */
    public function recurrence_text($rrule)
    {
        // derive missing FREQ and INTERVAL from RDATE list
        if (empty($rrule['FREQ']) && !empty($rrule['RDATE'])) {
            $first = $rrule['RDATE'][0];
            $second = $rrule['RDATE'][1];
            $third  = $rrule['RDATE'][2];
            if (is_a($first, 'DateTime') && is_a($second, 'DateTime')) {
                $diff = $first->diff($second);
                foreach (array('y' => 'YEARLY', 'm' => 'MONTHLY', 'd' => 'DAILY') as $k => $freq) {
                    if ($diff->$k != 0) {
                        $rrule['FREQ'] = $freq;
                        $rrule['INTERVAL'] = $diff->$k;

                        // verify interval with next item
                        if (is_a($third, 'DateTime')) {
                            $diff2 = $second->diff($third);
                            if ($diff2->$k != $diff->$k) {
                                unset($rrule['INTERVAL']);
                            }
                        }
                        break;
                    }
                }
            }
            if (!$rrule['INTERVAL']) {
                $rrule['FREQ'] = 'RDATE';
            }
            $rrule['UNTIL'] = end($rrule['RDATE']);
        }

        $freq = sprintf('%s %d ', $this->gettext('every'), $rrule['INTERVAL']);
        $details = '';
        switch ($rrule['FREQ']) {
            case 'DAILY':
                $freq .= $this->gettext('days');
                break;
            case 'WEEKLY':
                $freq .= $this->gettext('weeks');
                break;
            case 'MONTHLY':
                $freq .= $this->gettext('months');
                break;
            case 'YEARLY':
                $freq .= $this->gettext('years');
                break;
        }

        if ($rrule['INTERVAL'] <= 1) {
            $freq = $this->gettext(strtolower($rrule['FREQ']));
        }

        if ($rrule['COUNT']) {
            $until =  $this->gettext(array('name' => 'forntimes', 'vars' => array('nr' => $rrule['COUNT'])));
        }
        else if ($rrule['UNTIL']) {
            $until = $this->gettext('recurrencend') . ' ' . rcmail::get_instance()->format_date($rrule['UNTIL'], self::to_php_date_format($this->rc->config->get('calendar_date_format', $this->defaults['calendar_date_format'])));
        }
        else {
            $until = $this->gettext('forever');
        }

        $except = '';
        if (is_array($rrule['EXDATE']) && !empty($rrule['EXDATE'])) {
          $format = self::to_php_date_format($this->rc->config->get('calendar_date_format', $this->defaults['calendar_date_format']));
          $exdates = array_map(
            function($dt) use ($format) { return rcmail::get_instance()->format_date($dt, $format); },
            array_slice($rrule['EXDATE'], 0, 10)
          );
          $except = '; ' . $this->gettext('except') . ' ' . join(', ', $exdates);
        }

        return rtrim($freq . $details . ', ' . $until . $except);
    }

    /**
     * Generate the form for recurrence settings
     */
    public function recurrence_form($attrib = array())
    {
        switch ($attrib['part']) {
            // frequency selector
            case 'frequency':
                $select = new html_select(array('name' => 'frequency', 'id' => 'edit-recurrence-frequency'));
                $select->add($this->gettext('never'),   '');
                $select->add($this->gettext('daily'),   'DAILY');
                $select->add($this->gettext('weekly'),  'WEEKLY');
                $select->add($this->gettext('monthly'), 'MONTHLY');
                $select->add($this->gettext('yearly'),  'YEARLY');
                $select->add($this->gettext('rdate'),   'RDATE');
                $html = html::label('edit-recurrence-frequency', $this->gettext('frequency')) . $select->show('');
                break;

            // daily recurrence
            case 'daily':
                $select = $this->interval_selector(array('name' => 'interval', 'class' => 'edit-recurrence-interval', 'id' => 'edit-recurrence-interval-daily'));
                $html = html::div($attrib, html::label('edit-recurrence-interval-daily', $this->gettext('every')) . $select->show(1) . html::span('label-after', $this->gettext('days')));
                break;

            // weekly recurrence form
            case 'weekly':
                $select = $this->interval_selector(array('name' => 'interval', 'class' => 'edit-recurrence-interval', 'id' => 'edit-recurrence-interval-weekly'));
                $html = html::div($attrib, html::label('edit-recurrence-interval-weekly', $this->gettext('every')) . $select->show(1) . html::span('label-after', $this->gettext('weeks')));
                // weekday selection
                $daymap = array('sun','mon','tue','wed','thu','fri','sat');
                $checkbox = new html_checkbox(array('name' => 'byday', 'class' => 'edit-recurrence-weekly-byday'));
                $first = $this->rc->config->get('calendar_first_day', 1);
                for ($weekdays = '', $j = $first; $j <= $first+6; $j++) {
                    $d = $j % 7;
                    $weekdays .= html::label(array('class' => 'weekday'),
                        $checkbox->show('', array('value' => strtoupper(substr($daymap[$d], 0, 2)))) .
                        $this->gettext($daymap[$d])
                    ) . ' ';
                }
                $html .= html::div($attrib, html::label(null, $this->gettext('bydays')) . $weekdays);
                break;

            // monthly recurrence form
            case 'monthly':
                $select = $this->interval_selector(array('name' => 'interval', 'class' => 'edit-recurrence-interval', 'id' => 'edit-recurrence-interval-monthly'));
                $html = html::div($attrib, html::label('edit-recurrence-interval-monthly', $this->gettext('every')) . $select->show(1) . html::span('label-after', $this->gettext('months')));

                $checkbox = new html_checkbox(array('name' => 'bymonthday', 'class' => 'edit-recurrence-monthly-bymonthday'));
                for ($monthdays = '', $d = 1; $d <= 31; $d++) {
                    $monthdays .= html::label(array('class' => 'monthday'), $checkbox->show('', array('value' => $d)) . $d);
                    $monthdays .= $d % 7 ? ' ' : html::br();
                }

                // rule selectors
                $radio = new html_radiobutton(array('name' => 'repeatmode', 'class' => 'edit-recurrence-monthly-mode'));
                $table = new html_table(array('cols' => 2, 'border' => 0, 'cellpadding' => 0, 'class' => 'formtable'));
                $table->add('label', html::label(null, $radio->show('BYMONTHDAY', array('value' => 'BYMONTHDAY')) . ' ' . $this->gettext('each')));
                $table->add(null, $monthdays);
                $table->add('label', html::label(null, $radio->show('', array('value' => 'BYDAY')) . ' ' . $this->gettext('onevery')));
                $table->add(null, $this->rrule_selectors($attrib['part']));

                $html .= html::div($attrib, $table->show());
                break;

            // annually recurrence form
            case 'yearly':
                $select = $this->interval_selector(array('name' => 'interval', 'class' => 'edit-recurrence-interval', 'id' => 'edit-recurrence-interval-yearly'));
                $html = html::div($attrib, html::label('edit-recurrence-interval-yearly', $this->gettext('every')) . $select->show(1) . html::span('label-after', $this->gettext('years')));
                // month selector
                $monthmap = array('','jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec');
                $checkbox = new html_checkbox(array('name' => 'bymonth', 'class' => 'edit-recurrence-yearly-bymonth'));
                for ($months = '', $m = 1; $m <= 12; $m++) {
                    $months .= html::label(array('class' => 'month'), $checkbox->show(null, array('value' => $m)) . $this->gettext($monthmap[$m]));
                    $months .= $m % 4 ? ' ' : html::br();
                }
                $html .= html::div($attrib + array('id' => 'edit-recurrence-yearly-bymonthblock'), $months);

                // day rule selection
                $html .= html::div($attrib, html::label(null, $this->gettext('onevery')) . $this->rrule_selectors($attrib['part'], '---'));
                break;

            // end of recurrence form
            case 'until':
                $radio = new html_radiobutton(array('name' => 'repeat', 'class' => 'edit-recurrence-until'));
                $select = $this->interval_selector(array('name' => 'times', 'id' => 'edit-recurrence-repeat-times'));
                $input = new html_inputfield(array('name' => 'untildate', 'id' => 'edit-recurrence-enddate', 'size' => "10"));

                $html = html::div('line first',
                    html::label(null, $radio->show('', array('value' => '', 'id' => 'edit-recurrence-repeat-forever')) . ' ' .
                        $this->gettext('forever'))
                );

                $forntimes = $this->gettext(array(
                    'name' => 'forntimes',
                    'vars' => array('nr' => '%s'))
                );
                $html .= html::div('line',
                    $radio->show('', array('value' => 'count', 'id' => 'edit-recurrence-repeat-count', 'aria-label' => sprintf($forntimes, 'N'))) . ' ' .
                        sprintf($forntimes, $select->show(1))
                );

                $html .= html::div('line',
                    $radio->show('', array('value' => 'until', 'id' => 'edit-recurrence-repeat-until', 'aria-label' => $this->gettext('untilenddate'))) . ' ' .
                        $this->gettext('untildate') . ' ' . $input->show('', array('aria-label' => $this->gettext('untilenddate')))
                );

                $html = html::div($attrib, html::label(null, ucfirst($this->gettext('recurrencend'))) . $html);
                break;

            case 'rdate':
                $ul = html::tag('ul', array('id' => 'edit-recurrence-rdates'), '');
                $input = new html_inputfield(array('name' => 'rdate', 'id' => 'edit-recurrence-rdate-input', 'size' => "10"));
                $button = new html_inputfield(array('type' => 'button', 'class' => 'button add', 'value' => $this->gettext('addrdate')));
                $html .= html::div($attrib, $ul . html::div('inputform', $input->show() . $button->show()));
                break;
        }

        return $html;
    }

    /**
     * Input field for interval selection
     */
    private function interval_selector($attrib)
    {
        $select = new html_select($attrib);
        $select->add(range(1,30), range(1,30));
        return $select;
    }

    /**
     * Drop-down menus for recurrence rules like "each last sunday of"
     */
    private function rrule_selectors($part, $noselect = null)
    {
        // rule selectors
        $select_prefix = new html_select(array('name' => 'bydayprefix', 'id' => "edit-recurrence-$part-prefix"));
        if ($noselect) $select_prefix->add($noselect, '');
        $select_prefix->add(array(
                $this->gettext('first'),
                $this->gettext('second'),
                $this->gettext('third'),
                $this->gettext('fourth'),
                $this->gettext('last')
            ),
            array(1, 2, 3, 4, -1));

        $select_wday = new html_select(array('name' => 'byday', 'id' => "edit-recurrence-$part-byday"));
        if ($noselect) $select_wday->add($noselect, '');

        $daymap = array('sunday','monday','tuesday','wednesday','thursday','friday','saturday');
        $first = $this->rc->config->get('calendar_first_day', 1);
        for ($j = $first; $j <= $first+6; $j++) {
            $d = $j % 7;
            $select_wday->add($this->gettext($daymap[$d]), strtoupper(substr($daymap[$d], 0, 2)));
        }

        return $select_prefix->show() . '&nbsp;' . $select_wday->show();
    }

    /**
     * Convert the recurrence settings to be processed on the client
     */
    public function to_client_recurrence($recurrence, $allday = false)
    {
        if ($recurrence['UNTIL'])
            $recurrence['UNTIL'] = $this->adjust_timezone($recurrence['UNTIL'], $allday)->format('c');

        // format RDATE values
        if (is_array($recurrence['RDATE'])) {
            $libcal = $this;
            $recurrence['RDATE'] = array_map(function($rdate) use ($libcal) {
                return $libcal->adjust_timezone($rdate, true)->format('c');
            }, $recurrence['RDATE']);
        }

        unset($recurrence['EXCEPTIONS']);

        return $recurrence;
    }

    /**
     * Process the alarms values submitted by the client
     */
    public function from_client_recurrence($recurrence, $start = null)
    {
        if (is_array($recurrence) && !empty($recurrence['UNTIL'])) {
            $recurrence['UNTIL'] = new DateTime($recurrence['UNTIL'], $this->timezone);
        }

        if (is_array($recurrence) && is_array($recurrence['RDATE'])) {
            $tz = $this->timezone;
            $recurrence['RDATE'] = array_map(function($rdate) use ($tz, $start) {
                try {
                    $dt = new DateTime($rdate, $tz);
                    if (is_a($start, 'DateTime'))
                        $dt->setTime($start->format('G'), $start->format('i'));
                    return $dt;
                }
                catch (Exception $e) {
                    return null;
                }
            }, $recurrence['RDATE']);
        }

        return $recurrence;
    }


    /*********  Attachments handling  *********/

    /**
     * Handler for attachment uploads
     */
    public function attachment_upload($session_key, $id_prefix = '')
    {
        // Upload progress update
        if (!empty($_GET['_progress'])) {
            $this->rc->upload_progress();
        }

        $recid = $id_prefix . rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
        $uploadid = rcube_utils::get_input_value('_uploadid', rcube_utils::INPUT_GPC);

        if (!is_array($_SESSION[$session_key]) || $_SESSION[$session_key]['id'] != $recid) {
            $_SESSION[$session_key] = array();
            $_SESSION[$session_key]['id'] = $recid;
            $_SESSION[$session_key]['attachments'] = array();
        }

        // clear all stored output properties (like scripts and env vars)
        $this->rc->output->reset();

        if (is_array($_FILES['_attachments']['tmp_name'])) {
            foreach ($_FILES['_attachments']['tmp_name'] as $i => $filepath) {
              // Process uploaded attachment if there is no error
              $err = $_FILES['_attachments']['error'][$i];

              if (!$err) {
                $attachment = array(
                    'path' => $filepath,
                    'size' => $_FILES['_attachments']['size'][$i],
                    'name' => $_FILES['_attachments']['name'][$i],
                    'mimetype' => rcube_mime::file_content_type($filepath, $_FILES['_attachments']['name'][$i], $_FILES['_attachments']['type'][$i]),
                    'group' => $recid,
                );

                $attachment = $this->rc->plugins->exec_hook('attachment_upload', $attachment);
              }

              if (!$err && $attachment['status'] && !$attachment['abort']) {
                  $id = $attachment['id'];

                  // store new attachment in session
                  unset($attachment['status'], $attachment['abort']);
                  $_SESSION[$session_key]['attachments'][$id] = $attachment;

                  if (($icon = $_SESSION[$session_key . '_deleteicon']) && is_file($icon)) {
                      $button = html::img(array(
                          'src' => $icon,
                          'alt' => $this->rc->gettext('delete')
                      ));
                  }
                  else {
                      $button = rcube::Q($this->rc->gettext('delete'));
                  }

                  $content = html::a(array(
                      'href' => "#delete",
                      'class' => 'delete',
                      'onclick' => sprintf("return %s.remove_from_attachment_list('rcmfile%s')", JS_OBJECT_NAME, $id),
                      'title' => $this->rc->gettext('delete'),
                      'aria-label' => $this->rc->gettext('delete') . ' ' . $attachment['name'],
                  ), $button);

                  $content .= rcube::Q($attachment['name']);

                  $this->rc->output->command('add2attachment_list', "rcmfile$id", array(
                      'html' => $content,
                      'name' => $attachment['name'],
                      'mimetype' => $attachment['mimetype'],
                      'classname' => rcube_utils::file2class($attachment['mimetype'], $attachment['name']),
                      'complete' => true), $uploadid);
              }
              else {  // upload failed
                  if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
                    $msg = $this->rc->gettext(array('name' => 'filesizeerror', 'vars' => array(
                        'size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
                  }
                  else if ($attachment['error']) {
                      $msg = $attachment['error'];
                  }
                  else {
                      $msg = $this->rc->gettext('fileuploaderror');
                  }

                  $this->rc->output->command('display_message', $msg, 'error');
                  $this->rc->output->command('remove_from_attachment_list', $uploadid);
                }
            }
        }
        else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // if filesize exceeds post_max_size then $_FILES array is empty,
            // show filesizeerror instead of fileuploaderror
            if ($maxsize = ini_get('post_max_size'))
                $msg = $this->rc->gettext(array('name' => 'filesizeerror', 'vars' => array(
                    'size' => show_bytes(parse_bytes($maxsize)))));
            else
                $msg = $this->rc->gettext('fileuploaderror');

            $this->rc->output->command('display_message', $msg, 'error');
            $this->rc->output->command('remove_from_attachment_list', $uploadid);
        }

        $this->rc->output->send('iframe');
    }


    /**
     * Deliver an event/task attachment to the client
     * (similar as in Roundcube core program/steps/mail/get.inc)
     */
    public function attachment_get($attachment)
    {
        ob_end_clean();

        if ($attachment && $attachment['body']) {
            // allow post-processing of the attachment body
            $part = new rcube_message_part;
            $part->filename  = $attachment['name'];
            $part->size      = $attachment['size'];
            $part->mimetype  = $attachment['mimetype'];

            $plugin = $this->rc->plugins->exec_hook('message_part_get', array(
                'body'     => $attachment['body'],
                'mimetype' => strtolower($attachment['mimetype']),
                'download' => !empty($_GET['_download']),
                'part'     => $part,
            ));

            if ($plugin['abort'])
                exit;

            $mimetype = $plugin['mimetype'];
            list($ctype_primary, $ctype_secondary) = explode('/', $mimetype);

            $browser = $this->rc->output->browser;

            // send download headers
            if ($plugin['download']) {
                header("Content-Type: application/octet-stream");
                if ($browser->ie)
                    header("Content-Type: application/force-download");
            }
            else if ($ctype_primary == 'text') {
                header("Content-Type: text/$ctype_secondary");
            }
            else {
                header("Content-Type: $mimetype");
                header("Content-Transfer-Encoding: binary");
            }

            // display page, @TODO: support text/plain (and maybe some other text formats)
            if ($mimetype == 'text/html' && empty($_GET['_download'])) {
                $OUTPUT = new rcube_html_page();
                // @TODO: use washtml on $body
                $OUTPUT->write($plugin['body']);
            }
            else {
                // don't kill the connection if download takes more than 30 sec.
                @set_time_limit(0);

                $filename = $attachment['name'];
                $filename = preg_replace('[\r\n]', '', $filename);

                if ($browser->ie && $browser->ver < 7)
                    $filename = rawurlencode(abbreviate_string($filename, 55));
                else if ($browser->ie)
                    $filename = rawurlencode($filename);
                else
                    $filename = addcslashes($filename, '"');

                $disposition = !empty($_GET['_download']) ? 'attachment' : 'inline';
                header("Content-Disposition: $disposition; filename=\"$filename\"");

                echo $plugin['body'];
            }

            exit;
        }

        // if we arrive here, the requested part was not found
        header('HTTP/1.1 404 Not Found');
        exit;
    }

    /**
     * Show "loading..." page in attachment iframe
     */
    public function attachment_loading_page()
    {
        $url = str_replace('&_preload=1', '', $_SERVER['REQUEST_URI']);
        $message = $this->rc->gettext('loadingdata');

        if (defined(RCUBE_CHARSET)) {
            $charset = RCUBE_CHARSET;
        } elseif (defined(RCMAIL_CHARSET)) {
            $charset = RCMAIL_CHARSET;
        } else {
            $charset = $this->rc->config->get('default_charset');
        }
        header('Content-Type: text/html; charset=' . $charset);
        print "<html>\n<head>\n"
            . '<meta http-equiv="refresh" content="0; url='.Q($url).'">' . "\n"
            . '<meta http-equiv="content-type" content="text/html; charset=' . $charset . '">' . "\n"
            . "</head>\n<body>\n$message\n</body>\n</html>";
        exit;
    }

    /**
     * Template object for attachment display frame
     */
    public function attachment_frame($attrib = array())
    {
        $mimetype = strtolower($this->attachment['mimetype']);
        list($ctype_primary, $ctype_secondary) = explode('/', $mimetype);

        $attrib['src'] = './?' . str_replace('_frame=', ($ctype_primary == 'text' ? '_show=' : '_preload='), $_SERVER['QUERY_STRING']);

        $this->rc->output->add_gui_object('attachmentframe', $attrib['id']);

        return html::iframe($attrib);
    }

    /**
     *
     */
    public function attachment_header($attrib = array())
    {
        $rcmail = rcmail::get_instance();
        $dl_link = strtolower($attrib['downloadlink']) == 'true';
        $dl_url = $this->rc->url(array('_frame' => null, '_download' => 1) + $_GET);

        $table = new html_table(array('cols' => $dl_link ? 3 : 2));

        if (!empty($this->attachment['name'])) {
            $table->add('title', rcube::Q($this->rc->gettext('filename')));
            $table->add('header', rcube::Q($this->attachment['name']));
            if ($dl_link) {
                $table->add('download-link', html::a($dl_url, rcube::Q($this->rc->gettext('download'))));
            }
        }

        if (!empty($this->attachment['mimetype'])) {
            $table->add('title', rcube::Q($this->rc->gettext('type')));
            $table->add('header', rcube::Q($this->attachment['mimetype']));
        }

        if (!empty($this->attachment['size'])) {
            $table->add('title', rcube::Q($this->rc->gettext('filesize')));
            $table->add('header', rcube::Q(show_bytes($this->attachment['size'])));
        }

        $this->rc->output->set_env('attachment_download_url', $dl_url);

        return $table->show($attrib);
    }


    /*********  iTip message detection  *********/

    /**
     * Check mail message structure of there are .ics files attached
     */
    public function mail_message_load($p)
    {
        $this->ical_message = $p['object'];
        $itip_part     = null;

        // check all message parts for .ics files
        foreach ((array)$this->ical_message->mime_parts as $part) {
            if (self::part_is_vcalendar($part)) {
                if ($part->ctype_parameters['method'])
                    $itip_part = $part->mime_id;
                else
                    $this->ical_parts[] = $part->mime_id;
            }
        }

        // priorize part with method parameter
        if ($itip_part) {
            $this->ical_parts = array($itip_part);
        }
    }

    /**
     * Getter for the parsed iCal objects attached to the current email message
     *
     * @return object libvcalendar parser instance with the parsed objects
     */
    public function get_mail_ical_objects()
    {
        // create parser and load ical objects
        if (!$this->mail_ical_parser) {
            $this->mail_ical_parser = $this->get_ical();

            foreach ($this->ical_parts as $mime_id) {
                $part    = $this->ical_message->mime_parts[$mime_id];
                if (defined(RCUBE_CHARSET)) {
                    $def_charset = RCUBE_CHARSET;
                } elseif (defined(RCMAIL_CHARSET)) {
                    $def_charset = RCMAIL_CHARSET;
                } else {
                    $def_charset = $this->rc->config->get('default_charset');
                }
                $charset = $part->ctype_parameters['charset'] ?: $def_charset;
                $this->mail_ical_parser->import($this->ical_message->get_part_body($mime_id, true), $charset);

                // check if the parsed object is an instance of a recurring event/task
                array_walk($this->mail_ical_parser->objects, 'libcalendaring::identify_recurrence_instance');

                // stop on the part that has an iTip method specified
                if (count($this->mail_ical_parser->objects) && $this->mail_ical_parser->method) {
                    $this->mail_ical_parser->message_date = $this->ical_message->headers->date;
                    $this->mail_ical_parser->mime_id = $mime_id;

                    // store the message's sender address for comparisons
                    $this->mail_ical_parser->sender = preg_match(self::$email_regex, $this->ical_message->headers->from, $m) ? $m[1] : '';
                    if (!empty($this->mail_ical_parser->sender)) {
                        foreach ($this->mail_ical_parser->objects as $i => $object) {
                            $this->mail_ical_parser->objects[$i]['_sender'] = $this->mail_ical_parser->sender;
                            $this->mail_ical_parser->objects[$i]['_sender_utf'] = rcube_utils::idn_to_utf8($this->mail_ical_parser->sender);
                        }
                    }
                    break;
                }
            }
        }

        return $this->mail_ical_parser;
    }

    /**
     * Read the given mime message from IMAP and parse ical data
     *
     * @param string Mailbox name
     * @param string Message UID
     * @param string Message part ID and object index (e.g. '1.2:0')
     * @param string Object type filter (optional)
     *
     * @return array Hash array with the parsed iCal 
     */
    public function mail_get_itip_object($mbox, $uid, $mime_id, $type = null)
    {
        if (defined(RCUBE_CHARSET)) {
            $charset = RCUBE_CHARSET;
        } elseif (defined(RCMAIL_CHARSET)) {
            $charset = RCMAIL_CHARSET;
        } else {
            $charset = $this->rc->config->get('default_charset');
        }

        // establish imap connection
        $imap = $this->rc->get_storage();
        $imap->set_folder($mbox);

        if ($uid && $mime_id) {
            list($mime_id, $index) = explode(':', $mime_id);

            $part    = $imap->get_message_part($uid, $mime_id);
            $headers = $imap->get_message_headers($uid);
            $parser  = $this->get_ical();

            if ($part->ctype_parameters['charset']) {
                $charset = $part->ctype_parameters['charset'];
            }

            if ($part) {
                $objects = $parser->import($part, $charset);
            }
        }

        // successfully parsed events/tasks?
        if (!empty($objects) && ($object = $objects[$index]) && (!$type || $object['_type'] == $type)) {
            if ($parser->method)
                $object['_method'] = $parser->method;

            // store the message's sender address for comparisons
            $object['_sender'] = preg_match(self::$email_regex, $headers->from, $m) ? $m[1] : '';
            $object['_sender_utf'] = rcube_utils::idn_to_utf8($object['_sender']);

            // check if this is an instance of a recurring event/task
            self::identify_recurrence_instance($object);

            return $object;
        }

        return null;
    }

    /**
     * Checks if specified message part is a vcalendar data
     *
     * @param rcube_message_part Part object
     * @return boolean True if part is of type vcard
     */
    public static function part_is_vcalendar($part)
    {
        return (
            in_array($part->mimetype, array('text/calendar', 'text/x-vcalendar', 'application/ics')) ||
            // Apple sends files as application/x-any (!?)
            ($part->mimetype == 'application/x-any' && $part->filename && preg_match('/\.ics$/i', $part->filename))
        );
    }

    /**
     * Single occourrences of recurring events are identified by their RECURRENCE-ID property
     * in iCal which is represented as 'recurrence_date' in our internal data structure.
     *
     * Check if such a property exists and derive the '_instance' identifier and '_savemode'
     * attributes which are used in the storage backend to identify the nested exception item.
     */
    public static function identify_recurrence_instance(&$object)
    {
        // for savemode=all, remove recurrence instance identifiers
        if (!empty($object['_savemode']) && $object['_savemode'] == 'all' && $object['recurrence']) {
            unset($object['_instance'], $object['recurrence_date']);
        }
        // set instance and 'savemode' according to recurrence-id
        else if (!empty($object['recurrence_date']) && is_a($object['recurrence_date'], 'DateTime')) {
            $object['_instance'] = self::recurrence_instance_identifier($object);
            $object['_savemode'] = $object['thisandfuture'] ? 'future' : 'current';
        }
        else if (!empty($object['recurrence_id']) && !empty($object['_instance'])) {
            if (strlen($object['_instance']) > 4) {
                $object['recurrence_date'] = rcube_utils::anytodatetime($object['_instance'], $object['start']->getTimezone());
            }
            else {
                $object['recurrence_date'] = clone $object['start'];
            }
        }
    }

    /**
     * Return a date() format string to render identifiers for recurrence instances
     *
     * @param array Hash array with event properties
     * @return string Format string
     */
    public static function recurrence_id_format($event)
    {
        return $event['allday'] ? 'Ymd' : 'Ymd\THis';
    }

    /**
     * Return the identifer for the given instance of a recurring event
     *
     * @param array Hash array with event properties
     * @return mixed Format string or null if identifier cannot be generated
     */
    public static function recurrence_instance_identifier($event)
    {
        $instance_date = $event['recurrence_date'] ?: $event['start'];

        if ($instance_date && is_a($instance_date, 'DateTime')) {
          $recurrence_id_format = $event['allday'] ? 'Ymd' : 'Ymd\THis';
          return $instance_date->format($recurrence_id_format);
        }

        return null;
    }


    /*********  Attendee handling functions  *********/

    /**
     * Handler for attendee group expansion requests
     */
    public function expand_attendee_group()
    {
        $id     = rcube_utils::get_input_value('id', rcube_utils::INPUT_POST);
        $data   = rcube_utils::get_input_value('data', rcube_utils::INPUT_POST, true);
        $result = array('id' => $id, 'members' => array());
        $maxnum = 500;

        // iterate over all autocomplete address books (we don't know the source of the group)
        foreach ((array)$this->rc->config->get('autocomplete_addressbooks', 'sql') as $abook_id) {
            if (($abook = $this->rc->get_address_book($abook_id)) && $abook->groups) {
                foreach ($abook->list_groups($data['name'], 1) as $group) {
                    // this is the matching group to expand
                    if (in_array($data['email'], (array)$group['email'])) {
                        $abook->set_pagesize($maxnum);
                        $abook->set_group($group['ID']);

                        // get all members
                        $res = $abook->list_records($this->rc->config->get('contactlist_fields'));

                        // handle errors (e.g. sizelimit, timelimit)
                        if ($abook->get_error()) {
                            $result['error'] = $this->rc->gettext('expandattendeegrouperror', 'libcalendaring');
                            $res = false;
                        }
                        // check for maximum number of members (we don't wanna bloat the UI too much)
                        else if ($res->count > $maxnum) {
                            $result['error'] = $this->rc->gettext('expandattendeegroupsizelimit', 'libcalendaring');
                            $res = false;
                        }

                        while ($res && ($member = $res->iterate())) {
                            $emails = (array)$abook->get_col_values('email', $member, true);
                            if (!empty($emails) && ($email = array_shift($emails))) {
                                $result['members'][] = array(
                                    'email' => $email,
                                    'name' => rcube_addressbook::compose_list_name($member),
                                );
                            }
                        }

                        break 2;
                    }
                }
            }
        }

        $this->rc->output->command('plugin.expand_attendee_callback', $result);
    }


    /*********  Static utility functions  *********/

    /**
     * Convert the internal structured data into a vcalendar rrule 2.0 string
     */
    public static function to_rrule($recurrence, $allday = false)
    {
        if (is_string($recurrence))
            return $recurrence;

        $rrule = '';
        foreach ((array)$recurrence as $k => $val) {
            $k = strtoupper($k);
            switch ($k) {
            case 'UNTIL':
                // convert to UTC according to RFC 5545
                if (is_a($val, 'DateTime')) {
                    if (!$allday && !$val->_dateonly) {
                        $until = clone $val;
                        $until->setTimezone(new DateTimeZone('UTC'));
                        $val = $until->format('Ymd\THis\Z');
                    }
                    else {
                        $val = $val->format('Ymd');
                    }
                }
                break;
            case 'RDATE':
            case 'EXDATE':
                foreach ((array)$val as $i => $ex) {
                    if (is_a($ex, 'DateTime'))
                        $val[$i] = $ex->format('Ymd\THis');
                }
                $val = join(',', (array)$val);
                break;
            case 'EXCEPTIONS':
                continue 2;
            }

            if (strlen($val))
                $rrule .= $k . '=' . $val . ';';
        }

        return rtrim($rrule, ';');
    }

    /**
     * Convert from fullcalendar date format to PHP date() format string
     */
    public static function to_php_date_format($from)
    {
        // "dd.MM.yyyy HH:mm:ss" => "d.m.Y H:i:s"
        return strtr(strtr($from, array(
            'yyyy' => 'Y',
            'yy'   => 'y',
            'MMMM' => 'F',
            'MMM'  => 'M',
            'MM'   => 'm',
            'M'    => 'n',
            'dddd' => 'l',
            'ddd'  => 'D',
            'dd'   => 'd',
            'd'    => 'j',
            'HH'   => '**',
            'hh'   => '%%',
            'H'    => 'G',
            'h'    => 'g',
            'mm'   => 'i',
            'ss'   => 's',
            'TT'   => 'A',
            'tt'   => 'a',
            'T'    => 'A',
            't'    => 'a',
            'u'    => 'c',
        )), array(
            '**'   => 'H',
            '%%'   => 'h',
        ));
    }

    /**
     * Convert from PHP date() format to fullcalendar format string
     */
    public static function from_php_date_format($from)
    {
        // "d.m.Y H:i:s" => "dd.MM.yyyy HH:mm:ss"
        return strtr($from, array(
            'y' => 'yy',
            'Y' => 'yyyy',
            'M' => 'MMM',
            'F' => 'MMMM',
            'm' => 'MM',
            'n' => 'M',
            'j' => 'd',
            'd' => 'dd',
            'D' => 'ddd',
            'l' => 'dddd',
            'H' => 'HH',
            'h' => 'hh',
            'G' => 'H',
            'g' => 'h',
            'i' => 'mm',
            's' => 'ss',
            'A' => 'TT',
            'a' => 'tt',
            'c' => 'u',
        ));
    }

}
