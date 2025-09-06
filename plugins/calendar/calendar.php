<?php

/**
 * Calendar plugin for Roundcube webmail
 *
 * @author Lazlo Westerhof <hello@lazlo.me>
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2010, Lazlo Westerhof <hello@lazlo.me>
 * Copyright (C) 2014-2015, Kolab Systems AG <contact@kolabsys.com>
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

class calendar extends rcube_plugin
{
  const FREEBUSY_UNKNOWN = 0;
  const FREEBUSY_FREE = 1;
  const FREEBUSY_BUSY = 2;
  const FREEBUSY_TENTATIVE = 3;
  const FREEBUSY_OOF = 4;

  const SESSION_KEY = 'calendar_temp';

  public $task = '?(?!logout).*';
  public $rc;
  public $lib;
  private $_drivers = null;
  private $_cals = null;
  private $_cal_driver_map = null;
  public $resources_dir;
  public $home;  // declare public to be used in other classes
  public $urlbase;
  public $timezone;
  public $timezone_offset;
  public $gmt_offset;
  public $ui;

  public $defaults = array(
    'calendar_default_view' => "agendaWeek",
    'calendar_timeslots'    => 2,
    'calendar_work_start'   => 6,
    'calendar_work_end'     => 18,
    'calendar_agenda_range' => 60,
    'calendar_agenda_sections' => 'smart',
    'calendar_event_coloring'  => 0,
    'calendar_time_indicator'  => true,
    'calendar_allow_invite_shared' => false,
    'calendar_itip_send_option'    => 3,
    'calendar_itip_after_action'   => 0,
  );

  private $ical;
  private $itip;

  /**
   * Plugin initialization.
   */
  function init()
  {
    $this->require_plugin('libcalendaring');

    $this->rc = rcube::get_instance();
    $this->lib = libcalendaring::get_instance();

    $this->register_task('calendar', 'calendar');

    // load calendar configuration
    $this->load_config();

    // load localizations
    $this->add_texts('localization/', $this->rc->task == 'calendar' && (!$this->rc->action || $this->rc->action == 'print'));

    $this->timezone = $this->lib->timezone;
    $this->gmt_offset = $this->lib->gmt_offset;
    $this->dst_active = $this->lib->dst_active;
    $this->timezone_offset = $this->gmt_offset / 3600 - $this->dst_active;

    require($this->home . '/lib/calendar_ui.php');
    $this->ui = new calendar_ui($this);

    // catch iTIP confirmation requests that don're require a valid session
    if ($this->rc->action == 'attend' && !empty($_REQUEST['_t'])) {
      $this->add_hook('startup', array($this, 'itip_attend_response'));
    }
    else if ($this->rc->action == 'feed' && !empty($_REQUEST['_cal'])) {
      $this->add_hook('startup', array($this, 'ical_feed_export'));
    }
    else {
      // default startup routine
      $this->add_hook('startup', array($this, 'startup'));
    }

    $this->add_hook('user_delete', array($this, 'user_delete'));
  }

  /**
   * Setup basic plugin environment and UI
   */
  protected function setup()
  {
    $this->require_plugin('libcalendaring');
    $this->require_plugin('libkolab');

    $this->lib             = libcalendaring::get_instance();
    $this->timezone        = $this->lib->timezone;
    $this->gmt_offset      = $this->lib->gmt_offset;
    $this->dst_active      = $this->lib->dst_active;
    $this->timezone_offset = $this->gmt_offset / 3600 - $this->dst_active;

    // load localizations
    $this->add_texts('localization/', $this->rc->task == 'calendar' && (!$this->rc->action || $this->rc->action == 'print'));

    require($this->home . '/lib/calendar_ui.php');
    $this->ui = new calendar_ui($this);
  }

  /**
   * Startup hook
   */
  public function startup($args)
  {
    // the calendar module can be enabled/disabled by the kolab_auth plugin
    if ($this->rc->config->get('calendar_disabled', false) || !$this->rc->config->get('calendar_enabled', true))
        return;

    // load Calendar user interface
    if (!$this->rc->output->ajax_call && (!$this->rc->output->env['framed'] || $args['action'] == 'preview')) {
      $this->ui->init();

      // settings are required in (almost) every GUI step
      if ($args['action'] != 'attend')
        $this->rc->output->set_env('calendar_settings', $this->load_settings());
    }

    if ($args['task'] == 'calendar' && $args['action'] != 'save-pref') {
      // Load drivers to register possible hooks.
      $this->load_drivers();

      // register calendar actions
      $this->register_action('index', array($this, 'calendar_view'));
      $this->register_action('event', array($this, 'event_action'));
      $this->register_action('calendar', array($this, 'calendar_action'));
      $this->register_action('count', array($this, 'count_events'));
      $this->register_action('load_events', array($this, 'load_events'));
      $this->register_action('export_events', array($this, 'export_events'));
      $this->register_action('import_events', array($this, 'import_events'));
      $this->register_action('upload', array($this, 'attachment_upload'));
      $this->register_action('get-attachment', array($this, 'attachment_get'));
      $this->register_action('freebusy-status', array($this, 'freebusy_status'));
      $this->register_action('freebusy-times', array($this, 'freebusy_times'));
      $this->register_action('randomdata', array($this, 'generate_randomdata'));
      $this->register_action('print', array($this,'print_view'));
      $this->register_action('mailimportitip', array($this, 'mail_import_itip'));
      $this->register_action('mailimportattach', array($this, 'mail_import_attachment'));
      $this->register_action('mailtoevent', array($this, 'mail_message2event'));
      $this->register_action('inlineui', array($this, 'get_inline_ui'));
      $this->register_action('check-recent', array($this, 'check_recent'));
      $this->register_action('itip-status', array($this, 'event_itip_status'));
      $this->register_action('itip-remove', array($this, 'event_itip_remove'));
      $this->register_action('itip-decline-reply', array($this, 'mail_itip_decline_reply'));
      $this->register_action('itip-delegate', array($this, 'mail_itip_delegate'));
      $this->register_action('resources-list', array($this, 'resources_list'));
      $this->register_action('resources-owner', array($this, 'resources_owner'));
      $this->register_action('resources-calendar', array($this, 'resources_calendar'));
      $this->register_action('resources-autocomplete', array($this, 'resources_autocomplete'));
      $this->add_hook('refresh', array($this, 'refresh'));

      // remove undo information...
      if ($undo = $_SESSION['calendar_event_undo']) {
        // ...after timeout
        $undo_time = $this->rc->config->get('undo_timeout', 0);
        if ($undo['ts'] < time() - $undo_time) {
          $this->rc->session->remove('calendar_event_undo');
          // @TODO: do EXPUNGE on kolab objects?
        }
      }

      // loading preinstalled calendars
      $preinstalled_calendars = $this->rc->config->get('calendar_preinstalled_calendars', FALSE);
      if ($preinstalled_calendars && is_array($preinstalled_calendars)) {
      
          // expanding both caldav url and user with RC (imap) username
          foreach ($preinstalled_calendars as $index => $cal){
              $preinstalled_calendars[$index]['caldav_url'] = str_replace('%u', $this->rc->get_user_name(), $cal['caldav_url']); 
              $preinstalled_calendars[$index]['caldav_user'] = str_replace('%u', $this->rc->get_user_name(), $cal['caldav_user']);
          }
        
          foreach ($this->get_drivers() as $driver_name => $driver) {
              foreach ($preinstalled_calendars as $cal) {
                  if ($driver_name == $cal['driver']) {    
                      if (!$driver->create_calendar($cal)) {
                          $error_msg = 'Unable to add default calendars' . ($driver && $driver->last_error ? ': ' . $driver->last_error :'');
                          $this->rc->output->show_message($error_msg, 'error');
                      }
                  }
              }
          }
      }
    }
    else if ($args['task'] == 'settings') {
      // add hooks for Calendar settings
      $this->add_hook('preferences_sections_list', array($this, 'preferences_sections_list'));
      $this->add_hook('preferences_list', array($this, 'preferences_list'));
      $this->add_hook('preferences_save', array($this, 'preferences_save')); 
    }
    else if ($args['task'] == 'mail') {
      // hooks to catch event invitations on incoming mails
      if ($args['action'] == 'show' || $args['action'] == 'preview') {
        $this->add_hook('template_object_messagebody', array($this, 'mail_messagebody_html'));
      }

      // add 'Create event' item to message menu
      if ($this->api->output->type == 'html') {
        $this->api->add_content(html::tag('li', null, 
          $this->api->output->button(array(
            'command'  => 'calendar-create-from-mail',
            'label'    => 'calendar.createfrommail',
            'type'     => 'link',
            'classact' => 'icon calendarlink active',
            'class'    => 'icon calendarlink',
            'innerclass' => 'icon calendar',
          ))),
          'messagemenu');

        $this->api->output->add_label('calendar.createfrommail');
      }

      $this->add_hook('messages_list', array($this, 'mail_messages_list'));
      $this->add_hook('message_compose', array($this, 'mail_message_compose'));
    }
    else if ($args['task'] == 'addressbook') {
      if ($this->rc->config->get('calendar_contact_birthdays')) {
        $this->add_hook('contact_update', array($this, 'contact_update'));
        $this->add_hook('contact_create', array($this, 'contact_update'));
      }
    }

    // add hooks to display alarms
    $this->add_hook('pending_alarms', array($this, 'pending_alarms'));
    $this->add_hook('dismiss_alarms', array($this, 'dismiss_alarms'));
  }

  /**
   * Helper method to load all configured drivers.
   */
  public function load_drivers()
  {
    if($this->_drivers == null)
    {
      $this->_drivers = array();

      foreach($this->get_driver_names() as $driver_name)
      {
        $driver_name = trim($driver_name);
        $driver_class = $driver_name . '_driver';

        require_once($this->home . '/drivers/calendar_driver.php');
        require_once($this->home . '/drivers/' . $driver_name . '/' . $driver_class . '.php');

        if($driver_name == "kolab")
          $this->require_plugin('libkolab');

        $driver = new $driver_class($this);

        if ($driver->undelete)
          $driver->undelete = $this->rc->config->get('undo_timeout', 0) > 0;

        $this->_drivers[$driver_name] = $driver;
      }
    }
  }

  /*
   * Helper method to get configured driver names.
   * @return List of driver names.
   */
  public function get_driver_names()
  {
    $driver_names = $this->rc->config->get('calendar_driver', array('kolab'));
    if(!is_array($driver_names)) $driver_names = array($driver_names);
    return $driver_names;
  }

  /**
   * Helpers function to return loaded drivers.
   * @return List of driver objects.
   */
  public function get_drivers()
  {
    $this->load_drivers();
    return $this->_drivers;
  }

  /**
   * Helper method to get driver by name.
   *
   * @param string $name Driver name to get driver object for.
   * @return mixed Driver object or null if no such driver exists.
   */
  public function get_driver_by_name($name)
  {
    $this->load_drivers();
    if(isset($this->_drivers[$name]))
    {
      return $this->_drivers[$name];
    }
    else
    {
      rcube::raise_error("Unknown driver requested \"$name\".", true, true);
      return null;
    }
  }

  /**
   * Helper method to get the driver by GPC input, e.g. "_driver" or "driver"
   * property specified in POST/GET or COOKIE variables.
   *
   * @param boolean $quiet = false Indicates where to raise an error if no driver was found in GPC
   * @return mixed Driver object or null if no such driver exists.
   */
  public function get_driver_by_gpc($quiet = false)
  {
    $this->load_drivers();
    $driver_name = null;
    foreach(array("_driver", "driver") as $input_name)
    {
      $driver_name = rcube_utils::get_input_value($input_name, rcube_utils::INPUT_GPC);
      if($driver_name != null) break;
    }

    // Remove possible postfix "_driver" from requested driver name.
    $driver_name = str_replace("_driver", "", $driver_name);

    if($driver_name != null)
    {
      if(isset($this->_drivers[$driver_name]))
      {
        return $this->_drivers[$driver_name];
      }
      else
      {
        rcube::raise_error("Unknown driver requested \"$driver_name\".", true, true);
      }
    }
    else
    {
      if(!$quiet) {
        rcube::raise_error("No driver name found in GPC.", true, true);
      }
    }

    return null;
  }

  /**
   * Helper function to retrieve the default driver
   *
   * @return mixed Driver object or null if no default driver could be determined.
   */
  public function get_default_driver()
  {
    $default = $this->rc->config->get("calendar_driver_default", "kolab"); // Fallback to kolab if nothing was configured.
    return $this->get_driver_by_name($default);
  }

  /**
   * Return the driver for the given event.
   *
   * @param $id ID or UID of the event.
   * @return mixed Returns the driver object or null if no driver could be found for this event.
   */
  public function get_driver_by_event($id)
  {
    foreach($this->get_drivers() as $driver) {
      if($driver->get_event($id))
        return $driver;
    }

    return null;
  }

  /**
   * Get driver for given calendar id.
   * @param int Calendar id to get driver for.
   * @return mixed Driver object for given calendar.
   */
  public function get_driver_by_cal($cal_id)
  {
    if ($this->_cal_driver_map == null)
      $this->get_calendars();

    if (!isset($this->_cal_driver_map[$cal_id])){
      rcube::raise_error("No driver found for calendar \"$cal_id\".", true, true);
    }

    return $this->_cal_driver_map[$cal_id];
  }

  /**
   * Helper function to build calendar to driver map and calendar array.
   * @return array List of calendar properties.
   */
  public function get_calendars()
  {
    if ($this->_cals == null || $this->_cal_driver_map == null) {
      $this->_cals = array();
      $this->_cal_driver_map = array();

      $this->load_drivers();
      foreach ($this->get_drivers() as $driver) {
        foreach ((array)$driver->list_calendars() as $id => $prop) {
          $prop["driver"] = get_class($driver);
          $this->_cals[$id] = $prop;
          $this->_cal_driver_map[$id] = $driver;
        }
      }
    }

    return $this->_cals;
  }

  /**
   * Load iTIP functions
   */
  private function load_itip()
  {
    if (!$this->itip) {
      require_once($this->home . '/lib/calendar_itip.php');
      $this->itip = new calendar_itip($this);
      
      if ($this->rc->config->get('kolab_invitation_calendars'))
        $this->itip->set_rsvp_actions(array('accepted','tentative','declined','delegated','needs-action'));
    }

    return $this->itip;
  }

  /**
   * Load iCalendar functions
   */
  public function get_ical()
  {
    if (!$this->ical) {
      $this->ical = libcalendaring::get_ical();
    }
    
    return $this->ical;
  }

  /**
   * Get properties of the calendar this user has specified as default
   */
  public function get_default_calendar($sensitivity = null)
  {
    $default_id = $this->rc->config->get('calendar_default_calendar');
    // TODO: $calendars = $this->driver->list_calendars(calendar_driver::FILTER_PERSONAL | calendar_driver::FILTER_WRITEABLE);

    foreach($this->get_drivers() as $driver){
      $calendars = $driver->list_calendars(false, true);
      if($default_id) {
        $calendar = $calendars[$default_id] ?: null;

        if($calendar && (!$writeable || !$calendar["readonly"])
          && (!$confidential || $calendar["subtype"] != "confidential"))
        {
          //rcmail::console("422: get_default_calendar(): " . print_r($calendar, true));
          return $calendar;
        }
      }
      else
      {
        // No default if, so get first calendar of first driver.
        foreach ($calendars as $calendar) {
          if ($calendar['default']) {
            //rcmail::console("431: get_default_calendar(): " . print_r($calendar, true));
            return $calendar;
          }
          if ((!$writeable || !$calendar['readonly']) && (!$confidential || $calendar["subtype"] != "confidential")) {
            //rcmail::console("435: get_default_calendar(): " . print_r($calendar, true));
            return $calendar;
          }
        }
      }
    }

    return null;
  }
  
  /**
   * Render the main calendar view from skin template
   */
  function calendar_view()
  {
    $this->rc->output->set_pagetitle($this->gettext('calendar'));

    // Add CSS stylesheets to the page header
    $this->ui->addCSS();

    // Add JS files to the page header
    $this->ui->addJS();

    $this->ui->init_templates();
    $this->rc->output->add_label('lowest','low','normal','high','highest','delete','cancel','uploading','noemailwarning','close');
    $this->rc->output->add_label('libcalendaring.itipaccepted','libcalendaring.itiptentative','libcalendaring.itipdeclined','libcalendaring.itipdelegated','libcalendaring.expandattendeegroup','libcalendaring.expandattendeegroupnodata');

    // initialize attendees autocompletion
    $this->rc->autocomplete_init();

    $this->rc->output->set_env('timezone', $this->timezone->getName());
    $this->rc->output->set_env('calendar_driver', $this->rc->config->get('calendar_driver'), false);
    $this->rc->output->set_env('calendar_resources', (bool)$this->rc->config->get('calendar_resources_driver'));
//  $this->rc->output->set_env('mscolors', jqueryui::get_color_values());
    $this->rc->output->set_env('identities-selector', $this->ui->identity_select(array('id' => 'edit-identities-list', 'aria-label' => $this->gettext('roleorganizer'))));

    $view = rcube_utils::get_input_value('view', rcube_utils::INPUT_GPC);
    if (in_array($view, array('agendaWeek', 'agendaDay', 'month', 'table')))
      $this->rc->output->set_env('view', $view);

    if ($date = rcube_utils::get_input_value('date', rcube_utils::INPUT_GPC))
      $this->rc->output->set_env('date', $date);

    if ($msgref = rcube_utils::get_input_value('itip', rcube_utils::INPUT_GPC))
      $this->rc->output->set_env('itip_events', $this->itip_events($msgref));

    $this->rc->output->send("calendar.calendar");
  }

  /**
   * Handler for preferences_sections_list hook.
   * Adds Calendar settings sections into preferences sections list.
   *
   * @param array Original parameters
   * @return array Modified parameters
   */
  function preferences_sections_list($p)
  {
    $p['list']['calendar'] = array(
      'id' => 'calendar', 'section' => $this->gettext('calendar'),
    );

    return $p;
  }

  /**
   * Handler for preferences_list hook.
   * Adds options blocks into Calendar settings sections in Preferences.
   *
   * @param array Original parameters
   * @return array Modified parameters
   */
  function preferences_list($p)
  {
    if ($p['section'] != 'calendar') {
      return $p;
    }

    $no_override = array_flip((array)$this->rc->config->get('dont_override'));

    $p['blocks']['view']['name'] = $this->gettext('mainoptions');

    if (!isset($no_override['calendar_default_view'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_default_view';
	  $view = $this->rc->config->get('calendar_default_view', $this->defaults['calendar_default_view']);
      $select = new html_select(array('name' => '_default_view', 'id' => $field_id));
      $select->add($this->gettext('day'), "agendaDay");
      $select->add($this->gettext('week'), "agendaWeek");
      $select->add($this->gettext('month'), "month");
      $select->add($this->gettext('agenda'), "table");
      $p['blocks']['view']['options']['default_view'] = array(
        'title' => html::label($field_id, rcube::Q($this->gettext('default_view'))),
        'content' => $select->show($this->rc->config->get('calendar_default_view', $this->defaults['calendar_default_view'])),
      );
    }

    if (!isset($no_override['calendar_timeslots'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_timeslot';
      $choices = array('1', '2', '3', '4', '6');
      $select = new html_select(array('name' => '_timeslots', 'id' => $field_id));
      $select->add($choices);
      $p['blocks']['view']['options']['timeslots'] = array(
        'title' => html::label($field_id, rcube::Q($this->gettext('timeslots'))),
        'content' => $select->show(strval($this->rc->config->get('calendar_timeslots', $this->defaults['calendar_timeslots']))),
      );
    }

    if (!isset($no_override['calendar_first_day'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_firstday';
      $select = new html_select(array('name' => '_first_day', 'id' => $field_id));
      $select->add($this->rc->gettext('sunday'), '0');
      $select->add($this->rc->gettext('monday'), '1');
      $select->add($this->rc->gettext('tuesday'), '2');
      $select->add($this->rc->gettext('wednesday'), '3');
      $select->add($this->rc->gettext('thursday'), '4');
      $select->add($this->rc->gettext('friday'), '5');
      $select->add($this->rc->gettext('saturday'), '6');
      $p['blocks']['view']['options']['first_day'] = array(
        'title' => html::label($field_id, rcube::Q($this->gettext('first_day'))),
        'content' => $select->show(strval($this->rc->config->get('calendar_first_day', $this->defaults['calendar_first_day']))),
      );
    }

    if (!isset($no_override['calendar_first_hour'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $time_format = $this->rc->config->get('time_format', libcalendaring::to_php_date_format($this->rc->config->get('calendar_time_format', $this->defaults['calendar_time_format'])));
      $select_hours = new html_select();
      for ($h = 0; $h < 24; $h++)
        $select_hours->add(date($time_format, mktime($h, 0, 0)), $h);

      $field_id = 'rcmfd_firsthour';
      $p['blocks']['view']['options']['first_hour'] = array(
        'title' => html::label($field_id, rcube::Q($this->gettext('first_hour'))),
        'content' => $select_hours->show($this->rc->config->get('calendar_first_hour', $this->defaults['calendar_first_hour']), array('name' => '_first_hour', 'id' => $field_id)),
      );
    }

    if (!isset($no_override['calendar_work_start'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_workstart';
      $p['blocks']['view']['options']['workinghours'] = array(
        'title' => html::label($field_id, rcube::Q($this->gettext('workinghours'))),
        'content' => $select_hours->show($this->rc->config->get('calendar_work_start', $this->defaults['calendar_work_start']), array('name' => '_work_start', 'id' => $field_id)) .
        ' &mdash; ' . $select_hours->show($this->rc->config->get('calendar_work_end', $this->defaults['calendar_work_end']), array('name' => '_work_end', 'id' => $field_id)),
      );
    }

    if (!isset($no_override['calendar_event_coloring'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_coloring';
      $select_colors = new html_select(array('name' => '_event_coloring', 'id' => $field_id));
      $select_colors->add($this->gettext('coloringmode0'), 0);
      $select_colors->add($this->gettext('coloringmode1'), 1);
      $select_colors->add($this->gettext('coloringmode2'), 2);
      $select_colors->add($this->gettext('coloringmode3'), 3);

      $p['blocks']['view']['options']['eventcolors'] = array(
        'title' => html::label($field_id . 'value', rcube::Q($this->gettext('eventcoloring'))),
        'content' => $select_colors->show($this->rc->config->get('calendar_event_coloring', $this->defaults['calendar_event_coloring'])),
      );
    }

    if (!isset($no_override['calendar_default_alarm_type'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_alarm';
      $select_type = new html_select(array('name' => '_alarm_type', 'id' => $field_id));
      $select_type->add($this->gettext('none'), '');
      $types = array();
      foreach ($this->get_drivers() as $driver) {
        foreach ($driver->alarm_types as $type) {
          $types[$type] = $type;
        }
      }
      foreach ($types as $type) {
        $select_type->add($this->gettext(strtolower("alarm{$type}option"), 'libcalendaring'), $type);
      }
      $p['blocks']['view']['options']['alarmtype'] = array(
        'title' => html::label($field_id, rcube::Q($this->gettext('defaultalarmtype'))),
        'content' => $select_type->show($this->rc->config->get('calendar_default_alarm_type', '')),
      );
    }

    if (!isset($no_override['calendar_default_alarm_offset'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_alarm';
      $input_value = new html_inputfield(array('name' => '_alarm_value', 'id' => $field_id . 'value', 'size' => 3));
      $select_offset = new html_select(array('name' => '_alarm_offset', 'id' => $field_id . 'offset'));
      foreach (array('-M','-H','-D','+M','+H','+D') as $trigger)
        $select_offset->add($this->rc->gettext('trigger' . $trigger, 'libcalendaring'), $trigger);

      $preset = libcalendaring::parse_alarm_value($this->rc->config->get('calendar_default_alarm_offset', '-15M'));
      $p['blocks']['view']['options']['alarmoffset'] = array(
        'title' => html::label($field_id . 'value', rcube::Q($this->gettext('defaultalarmoffset'))),
        'content' => $input_value->show($preset[0]) . ' ' . $select_offset->show($preset[1]),
      );
    }

    if (!isset($no_override['calendar_default_calendar'])) {
      if (!$p['current']) {
        $p['blocks']['view']['content'] = true;
        return $p;
      }
      // default calendar selection
      $field_id = 'rcmfd_default_calendar';
      $select_cal = new html_select(array('name' => '_default_calendar', 'id' => $field_id, 'is_escaped' => true));
      foreach($this->get_drivers() as $driver){
        foreach ((array)$driver->list_calendars(false, true) as $id => $prop) {
          $select_cal->add($prop['name'], strval($id));
          if ($prop['default'])
            $default_calendar = $id;
        }
      }
      $p['blocks']['view']['options']['defaultcalendar'] = array(
        'title' => html::label($field_id . 'value', rcube::Q($this->gettext('defaultcalendar'))),
        'content' => $select_cal->show($this->rc->config->get('calendar_default_calendar', $default_calendar)),
      );
    }

    $p['blocks']['itip']['name'] = $this->gettext('itipoptions');

    // Invitations handling
    if (!isset($no_override['calendar_itip_after_action'])) {
      if (!$p['current']) {
        $p['blocks']['itip']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_after_action';
      $select   = new html_select(array('name' => '_after_action', 'id' => $field_id,
        'onchange' => "\$('#{$field_id}_select')[this.value == 4 ? 'show' : 'hide']()"));

      $select->add($this->gettext('afternothing'), '');
      $select->add($this->gettext('aftertrash'), 1);
      $select->add($this->gettext('afterdelete'), 2);
      $select->add($this->gettext('afterflagdeleted'), 3);
      $select->add($this->gettext('aftermoveto'), 4);

      $val = $this->rc->config->get('calendar_itip_after_action', $this->defaults['calendar_itip_after_action']);
      if ($val !== null && $val !== '' && !is_int($val)) {
        $folder = $val;
        $val    = 4;
      }

      $folders = $this->rc->folder_selector(array(
          'id'            => $field_id . '_select',
          'name'          => '_after_action_folder',
          'maxlength'     => 30,
          'folder_filter' => 'mail',
          'folder_rights' => 'w',
          'style'         => $val !== 4 ? 'display:none' : '',
      ));

      $p['blocks']['itip']['options']['after_action'] = array(
        'title'   => html::label($field_id, rcube::Q($this->gettext('afteraction'))),
        'content' => $select->show($val) . $folders->show($folder),
      );
    }

    // category definitions
    foreach ($this->get_drivers() as $driver) {
      if (!$driver->nocategories && !isset($no_override['calendar_categories'])) {
        $p['blocks']['categories']['name'] = $this->gettext('categories');

        if (!$p['current']) {
          $p['blocks']['categories']['content'] = true;
          return $p;
        }

        $categories = (array)$driver->list_categories();
        $categories_list = '';
        foreach ($categories as $name => $color) {
          $key = md5($name);
          $field_class = 'rcmfd_category_' . str_replace(' ', '_', $name);
          $category_remove = new html_inputfield(array('type' => 'button', 'value' => 'X', 'class' => 'button', 'onclick' => '$(this).parent().remove()', 'title' => $this->gettext('remove_category')));
          $category_name = new html_inputfield(array('name' => "_categories[$key]", 'class' => $field_class, 'size' => 30, 'disabled' => $driver->categoriesimmutable));
          $category_color = new html_inputfield(array('name' => "_colors[$key]", 'class' => "$field_class colors", 'size' => 6));
          $hidden = $driver->categoriesimmutable ? html::tag('input', array('type' => 'hidden', 'name' => "_categories[$key]", 'value' => $name)) : '';
          $categories_list .= html::div(null, $hidden . $category_name->show($name) . '&nbsp;' . $category_color->show($color) . '&nbsp;' . $category_remove->show());
        }

        $p['blocks']['categories']['options']['category_' . $name] = array(
          'content' => html::div(array('id' => 'calendarcategories'), $categories_list),
        );

        $field_id = 'rcmfd_new_category';
        $new_category = new html_inputfield(array('name' => '_new_category', 'id' => $field_id, 'size' => 30));
        $add_category = new html_inputfield(array('type' => 'button', 'class' => 'button', 'value' => $this->gettext('add_category'), 'onclick' => "rcube_calendar_add_category()"));
        $p['blocks']['categories']['options']['categories'] = array(
          'content' => $new_category->show('') . '&nbsp;' . $add_category->show(),
        );

        $this->rc->output->add_script('function rcube_calendar_add_category(){
          var name = $("#rcmfd_new_category").val();
          if (name.length) {
            var input = $("<input>").attr("type", "text").attr("name", "_categories[]").attr("size", 30).val(name);
            var color = $("<input>").attr("type", "text").attr("name", "_colors[]").attr("size", 6).addClass("colors").val("000000");
            var button = $("<input>").attr("type", "button").attr("value", "X").addClass("button").click(function(){ $(this).parent().remove() });
            $("<div>").append(input).append("&nbsp;").append(color).append("&nbsp;").append(button).appendTo("#calendarcategories");
            color.miniColors({ colorValues:(rcmail.env.mscolors || []) });
            $("#rcmfd_new_category").val("");
          }
        }');
            
        $this->rc->output->add_script('$("#rcmfd_new_category").keypress(function(event){
          if (event.which == 13) {
            rcube_calendar_add_category();
            event.preventDefault();
          }
        });
        ', 'docready');

        // load miniColors js/css files
        jqueryui::miniColors();
      }
    }
    
    /*
	$table = new html_table(array('cols' => 2, 'cellpadding' => 0, 'cellspacing' => 0, 'class' => 'account-details'));
    $table = new html_table(array('class' => 'account-details', 'cols' => 2, 'cellpadding' => 0, 'cellspacing' => 0));
if(count($cals) > 0){
      $i ++;
      $table->add('title', html::tag('h4', null, '&nbsp;' . $this->gettext('calendars') . ':&nbsp;&sup' . $i . ';'));
      $table->add('', '');
      ksort($cals);
      $repl = $rcmail->config->get('caldav_url_replace', false);
      foreach($cals as $key => $cal){
        $temp = explode('?', $cal['caldav_url'], 2);
        $url = slashify($temp[0]) . ($temp[1] ? ('?' . $temp[1]) : '');
         if(is_array($repl)){
          foreach($repl as $key1 => $val){
            $url = str_replace($key1, $val, $url);
          }
        }
        $table->add('title','&nbsp;&#9679; ' . $key);
        $table->add('', html::tag('input', array('value' => $url, 'onclick' => 'select_all(this)', 'name' => $key,  'type' => 'text', 'size' => $url_box_length)));
      }
  }
   out = $table->show();
  */
	/*
    // virtual birthdays calendar TODO
    if (!isset($no_override['calendar_contact_birthdays'])) {
      $p['blocks']['birthdays']['name'] = $this->gettext('birthdayscalendar');

      if (!$p['current']) {
        $p['blocks']['birthdays']['content'] = true;
        return $p;
      }

      $field_id = 'rcmfd_contact_birthdays';
      $input    = new html_checkbox(array('name' => '_contact_birthdays', 'id' => $field_id, 'value' => 1, 'onclick' => '$(".calendar_birthday_props").prop("disabled",!this.checked)'));

      $p['blocks']['birthdays']['options']['contact_birthdays'] = array(
        'title'   => html::label($field_id, $this->gettext('displaybirthdayscalendar')),
        'content' => $input->show($this->rc->config->get('calendar_contact_birthdays')?1:0),
      );

      $input_attrib = array(
        'class' => 'calendar_birthday_props',
        'disabled' => !$this->rc->config->get('calendar_contact_birthdays'),
      );

      $sources = array();
      $checkbox = new html_checkbox(array('name' => '_birthday_adressbooks[]') + $input_attrib);
      foreach ($this->rc->get_address_sources(false, true) as $source) {
        $active = in_array($source['id'], (array)$this->rc->config->get('calendar_birthday_adressbooks', array())) ? $source['id'] : '';
        $sources[] = html::label(null, $checkbox->show($active, array('value' => $source['id'])) . '&nbsp;' . rcube::Q($source['realname'] ?: $source['name']));
      }

      $p['blocks']['birthdays']['options']['birthday_adressbooks'] = array(
        'title'   => rcube::Q($this->gettext('birthdayscalendarsources')),
        'content' => join(html::br(), $sources),
      );

      $field_id = 'rcmfd_birthdays_alarm';
      $select_type = new html_select(array('name' => '_birthdays_alarm_type', 'id' => $field_id) + $input_attrib);
      $select_type->add($this->gettext('none'), '');
      foreach ($this->get_default_driver()->alarm_types as $type) { // TODO: Replace with dedicated birthday calendar as soon as it is available
        $select_type->add($this->rc->gettext(strtolower("alarm{$type}option"), 'libcalendaring'), $type);
      }

      $input_value = new html_inputfield(array('name' => '_birthdays_alarm_value', 'id' => $field_id . 'value', 'size' => 3) + $input_attrib);
      $select_offset = new html_select(array('name' => '_birthdays_alarm_offset', 'id' => $field_id . 'offset') + $input_attrib);
      foreach (array('-M','-H','-D') as $trigger)
        $select_offset->add($this->rc->gettext('trigger' . $trigger, 'libcalendaring'), $trigger);

      $preset = libcalendaring::parse_alarm_value($this->rc->config->get('calendar_birthdays_alarm_offset', '-1D'));
      $p['blocks']['birthdays']['options']['birthdays_alarmoffset'] = array(
        'title' => html::label($field_id . 'value', rcube::Q($this->gettext('showalarms'))),
        'content' => $select_type->show($this->rc->config->get('calendar_birthdays_alarm_type', '')) . ' ' . $input_value->show($preset[0]) . '&nbsp;' . $select_offset->show($preset[1]),
      );
    }
	
	*/

    return $p;
  }

  /**
   * Handler for preferences_save hook.
   * Executed on Calendar settings form submit.
   *
   * @param array Original parameters
   * @return array Modified parameters
   */
  function preferences_save($p)
  {
    if ($p['section'] == 'calendar') {

      // compose default alarm preset value
      $alarm_offset  = rcube_utils::get_input_value('_alarm_offset', rcube_utils::INPUT_POST);
      $alarm_value   = rcube_utils::get_input_value('_alarm_value', rcube_utils::INPUT_POST);
      $default_alarm = $alarm_offset[0] . intval($alarm_value) . $alarm_offset[1];

      $birthdays_alarm_offset = rcube_utils::get_input_value('_birthdays_alarm_offset', rcube_utils::INPUT_POST);
      $birthdays_alarm_value  = rcube_utils::get_input_value('_birthdays_alarm_value', rcube_utils::INPUT_POST);
      $birthdays_alarm_value  = $birthdays_alarm_offset[0] . intval($birthdays_alarm_value) . $birthdays_alarm_offset[1];

      $p['prefs'] = array(
        'calendar_default_view' => rcube_utils::get_input_value('_default_view', rcube_utils::INPUT_POST),
        'calendar_timeslots'    => intval(rcube_utils::get_input_value('_timeslots', rcube_utils::INPUT_POST)),
        'calendar_first_day'    => intval(rcube_utils::get_input_value('_first_day', rcube_utils::INPUT_POST)),
        'calendar_first_hour'   => intval(rcube_utils::get_input_value('_first_hour', rcube_utils::INPUT_POST)),
        'calendar_work_start'   => intval(rcube_utils::get_input_value('_work_start', rcube_utils::INPUT_POST)),
        'calendar_work_end'     => intval(rcube_utils::get_input_value('_work_end', rcube_utils::INPUT_POST)),
        'calendar_event_coloring'       => intval(rcube_utils::get_input_value('_event_coloring', rcube_utils::INPUT_POST)),
        'calendar_default_alarm_type'   => rcube_utils::get_input_value('_alarm_type', rcube_utils::INPUT_POST),
        'calendar_default_alarm_offset' => $default_alarm,
        'calendar_default_calendar'     => rcube_utils::get_input_value('_default_calendar', rcube_utils::INPUT_POST),
        'calendar_date_format' => null,  // clear previously saved values
        'calendar_time_format' => null,
        'calendar_contact_birthdays'    => rcube_utils::get_input_value('_contact_birthdays', rcube_utils::INPUT_POST) ? true : false,
        'calendar_birthday_adressbooks' => (array) rcube_utils::get_input_value('_birthday_adressbooks', rcube_utils::INPUT_POST),
        'calendar_birthdays_alarm_type'   => rcube_utils::get_input_value('_birthdays_alarm_type', rcube_utils::INPUT_POST),
        'calendar_birthdays_alarm_offset' => $birthdays_alarm_value ?: null,
        'calendar_itip_after_action'      => intval(rcube_utils::get_input_value('_after_action', rcube_utils::INPUT_POST)),
      );

      if ($p['prefs']['calendar_itip_after_action'] == 4) {
        $p['prefs']['calendar_itip_after_action'] = rcube_utils::get_input_value('_after_action_folder', rcube_utils::INPUT_POST, true);
      }

      // categories
      foreach($this->get_drivers() as $driver) {
        if (!$driver->nocategories) {
          $old_categories = $new_categories = array();
          foreach ($driver->list_categories() as $name => $color) {
            $old_categories[md5($name)] = $name;
          }

          $categories = (array)rcube_utils::get_input_value('_categories', rcube_utils::INPUT_POST);
          $colors = (array)rcube_utils::get_input_value('_colors', rcube_utils::INPUT_POST);

          foreach ($categories as $key => $name) {
            $color = preg_replace('/^#/', '', strval($colors[$key]));

            // rename categories in existing events -> driver's job
            if ($oldname = $old_categories[$key]) {
              $driver->replace_category($oldname, $name, $color);
              unset($old_categories[$key]);
            } else
              $driver->add_category($name, $color);

            $new_categories[$name] = $color;
          }

          // these old categories have been removed, alter events accordingly -> driver's job
          foreach ((array)$old_categories[$key] as $key => $name) {
            $driver->remove_category($name);
          }

          $p['prefs']['calendar_categories'] = $new_categories;
        }
      }
    }

    return $p;
  }

  /**
   * Dispatcher for calendar actions initiated by the client
   */
  function calendar_action()
  {
    $action = rcube_utils::get_input_value('action', rcube_utils::INPUT_GPC);
    $cal    = rcube_utils::get_input_value('c', rcube_utils::INPUT_GPC);
    $success = $reload = false;
    $driver = null;

    if (isset($cal['showalarms']))
      $cal['showalarms'] = intval($cal['showalarms']);

    switch ($action) {
      case "form-new":
      case "form-edit":
        echo $this->ui->calendar_editform($action, $cal);
        exit;
      case "new":
        $driver = $this->get_driver_by_gpc();
        $success = $driver->create_calendar($cal);
        $reload = true;
        break;
      case "edit":
        $driver = $this->get_driver_by_cal($cal['id']);
        $success = $driver->edit_calendar($cal);
        $reload = true;
        break;
      case "delete":
        $driver = $this->get_driver_by_cal($cal['id']);
        if ($success = $driver->delete_calendar($cal))
          $this->rc->output->command('plugin.destroy_source', array('id' => $cal['id']));
        break;
      case "subscribe":
        $driver = $this->get_driver_by_cal($cal['id']);
        if (!$driver->subscribe_calendar($cal))
          $this->rc->output->show_message($this->gettext('errorsaving'), 'error');
        return;
      case "search":
        $results    = array();
        $color_mode = $this->rc->config->get('calendar_event_coloring', $this->defaults['calendar_event_coloring']);
        $query      = rcube_utils::get_input_value('q', rcube_utils::INPUT_GPC);
        $source     = rcube_utils::get_input_value('source', rcube_utils::INPUT_GPC);

        $search_more_results = false;
        foreach($this->get_drivers() as $driver) {
          foreach ((array)$driver->search_calendars($query, $source) as $id => $prop) {
            $editname = $prop['editname'];
            unset($prop['editname']);  // force full name to be displayed
            $prop['active'] = false;

            // let the UI generate HTML and CSS representation for this calendar
            $html = $this->ui->calendar_list_item($id, $prop, $jsenv);
            $cal = $jsenv[$id];
            $cal['editname'] = $editname;
            $cal['html'] = $html;
            if (!empty($prop['color']))
              $cal['css'] = $this->ui->calendar_css_classes($id, $prop, $color_mode);

            $results[] = $cal;
          }

          $search_more_results |= $driver->search_more_results;
        }

        // report more results available
        if ($search_more_results)
          $this->rc->output->show_message('autocompletemore', 'info');

        $this->rc->output->command('multi_thread_http_response', $results, rcube_utils::get_input_value('_reqid', rcube_utils::INPUT_GPC));
        return;
    }
    
    if ($success)
      $this->rc->output->show_message('successfullysaved', 'confirmation');
    else {
      $error_msg = $this->gettext('errorsaving') . ($driver && $driver->last_error ? ': ' . $driver->last_error :'');
      $this->rc->output->show_message($error_msg, 'error');
    }

    $this->rc->output->command('plugin.unlock_saving');

    if ($success && $reload)
      $this->rc->output->command('plugin.reload_view');
  }
  
  
  /**
   * Dispatcher for event actions initiated by the client
   */
  function event_action()
  {
    $action = rcube_utils::get_input_value('action', rcube_utils::INPUT_GPC);
    $event  = rcube_utils::get_input_value('e', rcube_utils::INPUT_POST, true);
    $success = $reload = $got_msg = false;

    $driver = null;
    if($event['calendar'])
      $driver = $this->get_driver_by_cal($event['calendar']);

    // This can happen if creating a new event outside the calendar e.g. from an ical file attached to an email.
    if(!$driver)
      $driver = $this->get_default_driver();

    // force notify if hidden + active
    if ((int)$this->rc->config->get('calendar_itip_send_option', $this->defaults['calendar_itip_send_option']) === 1)
      $event['_notify'] = 1;

    // read old event data in order to find changes
    if (($event['_notify'] || $event['_decline']) && $action != 'new') {
      $old = $this->driver->get_event($event);

    // Support event moving across different drivers
    if(isset($event["_fromcalendar"]) && $event["_fromcalendar"] != $event["calendar"]) {
      $fromdriver = $this->get_driver_by_cal($event["_fromcalendar"]);
      if(get_class($fromdriver) != get_class($driver)) {
        $fromevent = $event;
        $fromevent["calendar"] = $event["_fromcalendar"];
        if($fromdriver->remove_event($fromevent))
          $action = "new";
      }
    }

      // load main event if savemode is 'all' or if deleting 'future' events
      if (($event['_savemode'] == 'all' || ($event['_savemode'] == 'future' && $action == 'remove' && !$event['_decline'])) && $old['recurrence_id']) {
        $old['id'] = $old['recurrence_id'];
        $old = $this->driver->get_event($old);
      }
    }

    switch ($action) {
      case "new":
        // create UID for new event
        $event['uid'] = $this->generate_uid();
        $this->write_preprocess($event, $action);
        if ($success = $driver->new_event($event)) {
          $event['id'] = $event['uid'];
          $event['_savemode'] = 'all';
          $this->cleanup_event($event);
          $this->event_save_success($event, null, $action, true);
        }
        $reload = $success && $event['recurrence'] ? 2 : 1;
        break;
        
      case "edit":
        $this->write_preprocess($event, $action);
        if ($success = $driver->edit_event($event)) {
          $this->cleanup_event($event);
          $this->event_save_success($event, $old, $action, $success);
        }
        $reload = $success && ($event['recurrence'] || $event['_savemode'] || $event['_fromcalendar']) ? 2 : 1;
        break;
      
      case "resize":
        $this->write_preprocess($event, $action);
        if ($success = $driver->resize_event($event)) {
          $this->event_save_success($event, $old, $action, $success);
        }
        $reload = $event['_savemode'] ? 2 : 1;
        break;
      
      case "move":
        $this->write_preprocess($event, $action);
        if ($success = $driver->move_event($event)) {
          $this->event_save_success($event, $old, $action, $success);
        }
        $reload  = $success && $event['_savemode'] ? 2 : 1;
        break;
      
      case "remove":
        // remove previous deletes
        $undo_time = $driver->undelete ? $this->rc->config->get('undo_timeout', 0) : 0;
        $this->rc->session->remove('calendar_event_undo');
        
        // search for event if only UID is given
        if (!isset($event['calendar']) && $event['uid']) {
          if (!($event = $driver->get_event($event, calendar_driver::FILTER_WRITEABLE))) {
            break;
          }
          $undo_time = 0;
        }

        $success = $driver->remove_event($event, $undo_time < 1);
        $reload = (!$success || $event['_savemode']) ? 2 : 1;

        if ($undo_time > 0 && $success) {
          $_SESSION['calendar_event_undo'] = array('ts' => time(), 'data' => $event);
          // display message with Undo link.
          $msg = html::span(null, $this->gettext('successremoval'))
            . ' ' . html::a(array('onclick' => sprintf("%s.http_request('event', 'action=undo', %s.display_message('', 'loading'))",
              rcmail_output::JS_OBJECT_NAME, rcmail_output::JS_OBJECT_NAME)), $this->gettext('undo'));
          $this->rc->output->show_message($msg, 'confirmation', null, true, $undo_time);
          $got_msg = true;
        }
        else if ($success) {
          $this->rc->output->show_message('calendar.successremoval', 'confirmation');
          $got_msg = true;
        }

        // send cancellation for the main event
        if ($event['_savemode'] == 'all') {
          unset($old['_instance'], $old['recurrence_date'], $old['recurrence_id']);
        }
        // send an update for the main event's recurrence rule instead of a cancellation message
        else if ($event['_savemode'] == 'future' && $success !== false && $success !== true) {
          $event['_savemode'] = 'all';  // force event_save_success() to load master event
          $action = 'edit';
          $success = true;
        }

        // send iTIP reply that participant has declined the event
        if ($success && $event['_decline']) {
          $emails = $this->get_user_emails();
          foreach ($old['attendees'] as $i => $attendee) {
            if ($attendee['role'] == 'ORGANIZER')
              $organizer = $attendee;
            else if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
              $old['attendees'][$i]['status'] = 'DECLINED';
              $reply_sender = $attendee['email'];
            }
          }

          if ($event['_savemode'] == 'future' && $event['id'] != $old['id']) {
            $old['thisandfuture'] = true;
          }

          $itip = $this->load_itip();
          $itip->set_sender_email($reply_sender);
          if ($organizer && $itip->send_itip_message($old, 'REPLY', $organizer, 'itipsubjectdeclined', 'itipmailbodydeclined'))
            $this->rc->output->command('display_message', $this->gettext(array('name' => 'sentresponseto', 'vars' => array('mailto' => $organizer['name'] ? $organizer['name'] : $organizer['email']))), 'confirmation');
          else
            $this->rc->output->command('display_message', $this->gettext('itipresponseerror'), 'error');
        }
        else if ($success) {
          $this->event_save_success($event, $old, $action, $success);
        }
        break;

      case "undo":
        // Restore deleted event
        $event  = $_SESSION['calendar_event_undo']['data'];

        if ($event)
          $success = $driver->restore_event($event);

        if ($success) {
          $this->rc->session->remove('calendar_event_undo');
          $this->rc->output->show_message('calendar.successrestore', 'confirmation');
          $got_msg = true;
          $reload = 2;
        }

        break;

      case "rsvp":
        $itip_sending  = $this->rc->config->get('calendar_itip_send_option', $this->defaults['calendar_itip_send_option']);
        $status        = rcube_utils::get_input_value('status', rcube_utils::INPUT_POST);
        $attendees     = rcube_utils::get_input_value('attendees', rcube_utils::INPUT_POST);
        $reply_comment = $event['comment'];

        $this->write_preprocess($event, 'edit');
        $ev = $driver->get_event($event);
        $ev['attendees'] = $event['attendees'];
        $ev['free_busy'] = $event['free_busy'];
        $ev['_savemode'] = $event['_savemode'];

        // send invitation to delegatee + add it as attendee
        if ($status == 'delegated' && $event['to']) {
          $itip = $this->load_itip();
          if ($itip->delegate_to($ev, $event['to'], (bool)$event['rsvp'], $attendees)) {
            $this->rc->output->show_message('calendar.itipsendsuccess', 'confirmation');
            $noreply = false;
          }
        }

        $event = $ev;

        // compose a list of attendees affected by this change
        $updated_attendees = array_filter(array_map(function($j) use ($event) {
          return $event['attendees'][$j];
        }, $attendees));

        if ($success = $driver->edit_rsvp($event, $status, $updated_attendees)) {
          $noreply = rcube_utils::get_input_value('noreply', rcube_utils::INPUT_GPC);
          $noreply = intval($noreply) || $status == 'needs-action' || $itip_sending === 0;
          $reload  = $event['calendar'] != $ev['calendar'] || $event['recurrence'] ? 2 : 1;
          $organizer = null;
          $emails = $this->get_user_emails();

          foreach ($event['attendees'] as $i => $attendee) {
            if ($attendee['role'] == 'ORGANIZER') {
              $organizer = $attendee;
            }
            else if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
              $reply_sender = $attendee['email'];
            }
          }

          if (!$noreply) {
            $itip = $this->load_itip();
            $itip->set_sender_email($reply_sender);
            $event['comment'] = $reply_comment;
            $event['thisandfuture'] = $event['_savemode'] == 'future';
            if ($organizer && $itip->send_itip_message($event, 'REPLY', $organizer, 'itipsubject' . $status, 'itipmailbody' . $status))
              $this->rc->output->command('display_message', $this->gettext(array('name' => 'sentresponseto', 'vars' => array('mailto' => $organizer['name'] ? $organizer['name'] : $organizer['email']))), 'confirmation');
            else
              $this->rc->output->command('display_message', $this->gettext('itipresponseerror'), 'error');
          }

          // refresh all calendars
          if ($event['calendar'] != $ev['calendar']) {
            $this->rc->output->command('plugin.refresh_calendar', array('source' => null, 'refetch' => true));
            $reload = 0;
          }
        }
        break;

      case "dismiss":
        $event['ids'] = explode(',', $event['id']);
        $plugin = $this->rc->plugins->exec_hook('dismiss_alarms', $event);
        $success = $plugin['success'];
        foreach ($event['ids'] as $id) {
          if (strpos($id, 'cal:') === 0)
            $success |= $driver->dismiss_alarm(substr($id, 4), $event['snooze']);
        }
        break;

      case "changelog":
        $data = $driver->get_event_changelog($event);
        if (is_array($data) && !empty($data)) {
          $lib = $this->lib;
          $dtformat = $this->rc->config->get('date_format') . ' ' . $this->rc->config->get('time_format');
          array_walk($data, function(&$change) use ($lib, $dtformat) {
            if ($change['date']) {
              $dt = $lib->adjust_timezone($change['date']);
              if ($dt instanceof DateTime)
                $change['date'] = rcmail::get_instance()->format_date($dt, $dtformat, false);
            }
          });
          $this->rc->output->command('plugin.render_event_changelog', $data);
        }
        else {
          $this->rc->output->command('plugin.render_event_changelog', false);
        }
        $got_msg = true;
        $reload = false;
        break;

      case "diff":
        $data = $driver->get_event_diff($event, $event['rev1'], $event['rev2']);
        if (is_array($data)) {
          // convert some properties, similar to self::_client_event()
          $lib = $this->lib;
          array_walk($data['changes'], function(&$change, $i) use ($event, $lib) {
            // convert date cols
            foreach (array('start','end','created','changed') as $col) {
              if ($change['property'] == $col) {
                $change['old'] = $lib->adjust_timezone($change['old'], strlen($change['old']) == 10)->format('c');
                $change['new'] = $lib->adjust_timezone($change['new'], strlen($change['new']) == 10)->format('c');
              }
            }
            // create textual representation for alarms and recurrence
            if ($change['property'] == 'alarms') {
              if (is_array($change['old']))
                $change['old_'] = libcalendaring::alarm_text($change['old']);
              if (is_array($change['new']))
                $change['new_'] = libcalendaring::alarm_text(array_merge((array)$change['old'], $change['new']));
            }
            if ($change['property'] == 'recurrence') {
              if (is_array($change['old']))
                $change['old_'] = $lib->recurrence_text($change['old']);
              if (is_array($change['new']))
                $change['new_'] = $lib->recurrence_text(array_merge((array)$change['old'], $change['new']));
            }
            if ($change['property'] == 'attachments') {
              if (is_array($change['old']))
                $change['old']['classname'] = rcube_utils::file2class($change['old']['mimetype'], $change['old']['name']);
              if (is_array($change['new']))
                $change['new']['classname'] = rcube_utils::file2class($change['new']['mimetype'], $change['new']['name']);
            }
            // compute a nice diff of description texts
            if ($change['property'] == 'description') {
              $change['diff_'] = libkolab::html_diff($change['old'], $change['new']);
            }
          });
          $this->rc->output->command('plugin.event_show_diff', $data);
        }
        else {
          $this->rc->output->command('display_message', $this->gettext('objectdiffnotavailable'), 'error');
        }
        $got_msg = true;
        $reload = false;
        break;

      case "show":
        if ($event = $driver->get_event_revison($event, $event['rev'])) {
          $this->rc->output->command('plugin.event_show_revision', $this->_client_event($event));
        }
        else {
          $this->rc->output->command('display_message', $this->gettext('objectnotfound'), 'error');
        }
        $got_msg = true;
        $reload = false;
        break;

      case "restore":
        if ($success = $driver->restore_event_revision($event, $event['rev'])) {
          $_event = $driver->get_event($event);
          $reload = $_event['recurrence'] ? 2 : 1;
          $this->rc->output->command('display_message', $this->gettext(array('name' => 'objectrestoresuccess', 'vars' => array('rev' => $event['rev']))), 'confirmation');
          $this->rc->output->command('plugin.close_history_dialog');
        }
        else {
          $this->rc->output->command('display_message', $this->gettext('objectrestoreerror'), 'error');
          $reload = 0;
        }
        $got_msg = true;
        break;
    }

    // show confirmation/error message
    if (!$got_msg) {
      if ($success)
        $this->rc->output->show_message('successfullysaved', 'confirmation');
      else
        $this->rc->output->show_message('calendar.errorsaving', 'error');
    }

    // unlock client
    $this->rc->output->command('plugin.unlock_saving');

    // update event object on the client or trigger a complete refretch if too complicated
    if ($reload) {
      $args = array('source' => $event['calendar']);
      if ($reload > 1)
        $args['refetch'] = true;
      else if ($success && $action != 'remove')
        $args['update'] = $this->_client_event($driver->get_event($event), true);
      $this->rc->output->command('plugin.refresh_calendar', $args);
    }
  }

  /**
   * Helper method sending iTip notifications after successful event updates
   */
  private function event_save_success(&$event, $old, $action, $success)
  {
    // $success is a new event ID
    if ($success !== true) {
      // send update notification on the main event
      if ($event['_savemode'] == 'future' && $event['_notify'] && $old['attendees'] && $old['recurrence_id']) {
        $master = $this->driver->get_event(array('id' => $old['recurrence_id'], 'calendar' => $old['calendar']), 0, true);
        unset($master['_instance'], $master['recurrence_date']);

        $sent = $this->notify_attendees($master, null, $action, $event['_comment']);
        if ($sent < 0)
          $this->rc->output->show_message('calendar.errornotifying', 'error');

        $event['attendees'] = $master['attendees'];  // this tricks us into the next if clause
      }

      // delete old reference if saved as new
      if ($event['_savemode'] == 'future' || $event['_savemode'] == 'new') {
        $old = null;
      }

      $event['id'] = $success;
      $event['_savemode'] = 'all';
    }

    // send out notifications
    if ($event['_notify'] && ($event['attendees'] || $old['attendees'])) {
      $_savemode = $event['_savemode'];

      // send notification for the main event when savemode is 'all'
      if ($action != 'remove' && $_savemode == 'all' && ($event['recurrence_id'] || $old['recurrence_id'] || ($old && $old['id'] != $event['id']))) {
        $event['id'] = $event['recurrence_id'] ?: ($old['recurrence_id'] ?: $old['id']);
        $event = $this->driver->get_event($event, 0, true);
        unset($event['_instance'], $event['recurrence_date']);
      }
      else {
        // make sure we have the complete record
        $event = $action == 'remove' ? $old : $this->driver->get_event($event, 0, true);
      }

      $event['_savemode'] = $_savemode;

      if ($old) {
        $old['thisandfuture'] = $_savemode == 'future';
      }

      // only notify if data really changed (TODO: do diff check on client already)
      if (!$old || $action == 'remove' || self::event_diff($event, $old)) {
        $sent = $this->notify_attendees($event, $old, $action, $event['_comment']);
        if ($sent > 0)
          $this->rc->output->show_message('calendar.itipsendsuccess', 'confirmation');
        else if ($sent < 0)
          $this->rc->output->show_message('calendar.errornotifying', 'error');
      }
    }
  }

  /**
   * Handler for load-requests from fullcalendar
   * This will return pure JSON formatted output
   */
  function load_events()
  {
    $start  = rcube_utils::get_input_value('start', rcube_utils::INPUT_GET);
    $end    = rcube_utils::get_input_value('end', rcube_utils::INPUT_GET);
    $query  = rcube_utils::get_input_value('q', rcube_utils::INPUT_GET);
    $source = rcube_utils::get_input_value('source', rcube_utils::INPUT_GET);

    if (!is_numeric($start) || strpos($start, 'T')) {
      $start = new DateTime($start, $this->timezone);
      $start = $start->getTimestamp();
    }
    if (!is_numeric($end) || strpos($end, 'T')) {
      $end = new DateTime($end, $this->timezone);
      $end = $end->getTimestamp();
    }

    $events = $this->driver->load_events($start, $end, $query, $source);
    echo $this->encode($events, !empty($query));
    exit;
  }

  /**
   * Handler for requests fetching event counts for calendars
   */
  public function count_events()
  {
    // don't update session on these requests (avoiding race conditions)
    $this->rc->session->nowrite = true;

    $start = rcube_utils::get_input_value('start', rcube_utils::INPUT_GET);
    if (!$start) {
      $start = new DateTime('today 00:00:00', $this->timezone);
      $start = $start->format('U');
    }

    $counts = 0;
    foreach($this->get_drivers() as $driver) {
      $counts += $driver->count_events(
        rcube_utils::get_input_value('source', rcube_utils::INPUT_GET),
        $start,
        rcube_utils::get_input_value('end', rcube_utils::INPUT_GET)
      );
    }

    $this->rc->output->command('plugin.update_counts', array('counts' => $counts));
  }

  /**
   * Load event data from an iTip message attachment
   */
  public function itip_events($msgref)
  {
    $path = explode('/', $msgref);
    $msg = array_pop($path);
    $mbox = join('/', $path);
    list($uid, $mime_id) = explode('#', $msg);
    $events = array();

    if ($event = $this->lib->mail_get_itip_object($uid, $mime_id, 'event')) {
      $partstat = 'NEEDS-ACTION';
/*
      $user_emails = $this->lib->get_user_emails();
      foreach ($event['attendees'] as $attendee) {
        if (in_array($attendee['email'], $user_emails)) {
          $partstat = $attendee['status'];
          break;
        }
      }
*/
      $event['id'] = $event['uid'];
      $event['temporary'] = true;
      $event['readonly'] = true;
      $event['calendar'] = '--invitation--itip';
      $event['className'] = 'fc-invitation-' . strtolower($partstat);
      $event['_mbox'] = $mbox;
      $event['_uid']  = $uid;
      $event['_part'] = $mime_id;

      $events[] = $this->_client_event($event, true);

      // add recurring instances
      if (!empty($event['recurrence'])) {
        foreach ($this->driver->get_recurring_events($event, $event['start']) as $recurring) {
          $recurring['temporary'] = true;
          $recurring['readonly'] = true;
          $recurring['calendar'] = '--invitation--itip';
          $events[] = $this->_client_event($recurring, true);
        }
      }
    }

    return $events;
  }

  /**
   * Handler for keep-alive requests
   * This will check for updated data in active calendars and sync them to the client
   */
  public function refresh($attr)
  {
     // refresh the entire calendar every 10th time to also sync deleted events
    if (rand(0,10) == 10) {
        $this->rc->output->command('plugin.refresh_calendar', array('refetch' => true));
        return;
    }

    $counts = array();

    foreach($this->get_drivers() as $driver) {
      foreach ($driver->list_calendars(calendar_driver::FILTER_ACTIVE) as $cal) {
        $events = $driver->load_events(
          rcube_utils::get_input_value('start', rcube_utils::INPUT_GPC),
          rcube_utils::get_input_value('end', rcube_utils::INPUT_GPC),
          rcube_utils::get_input_value('q', rcube_utils::INPUT_GPC),
          $cal['id'],
          1,
          $attr['last']
        );

        foreach ($events as $event) {
          $this->rc->output->command('plugin.refresh_calendar',
            array('source' => $cal['id'], 'update' => $this->_client_event($event)));
        }

        // refresh count for this calendar
        if ($cal['counts']) {
          $today = new DateTime('today 00:00:00', $this->timezone);
          $counts += $driver->count_events($cal['id'], $today->format('U'));
        }
      }
    }

    if (!empty($counts)) {
      $this->rc->output->command('plugin.update_counts', array('counts' => $counts));
    }
  }

  /**
   * Handler for pending_alarms plugin hook triggered by the calendar module on keep-alive requests.
   * This will check for pending notifications and pass them to the client
   */
  public function pending_alarms($p)
  {
    $time = $p['time'] ?: time();
    foreach($this->get_drivers() as $driver) {
      if ($alarms = $driver->pending_alarms($time)) {
        foreach ($alarms as $alarm) {
          $alarm['id'] = 'cal:' . $alarm['id'];  // prefix ID with cal:
          $p['alarms'][] = $alarm;
        }
      }
    }

    // get alarms for birthdays calendar
    if ($this->rc->config->get('calendar_contact_birthdays') && $this->rc->config->get('calendar_birthdays_alarm_type') == 'DISPLAY') {
      $cache = $this->rc->get_cache('calendar.birthdayalarms', 'db');

      // TODO: Use dedicated birthday calendar as soon as it exists
      foreach ($this->get_default_driver()->load_birthday_events($time, $time + 86400 * 60) as $e) {
        $alarm = libcalendaring::get_next_alarm($e);

        // overwrite alarm time with snooze value (or null if dismissed)
        if ($dismissed = $cache->get($e['id']))
          $alarm['time'] = $dismissed['notifyat'];

        // add to list if alarm is set
        if ($alarm && $alarm['time'] && $alarm['time'] <= $time) {
          $e['id'] = 'cal:bday:' . $e['id'];
          $e['notifyat'] = $alarm['time'];
          $p['alarms'][] = $e;
        }
      }
    }

    return $p;
  }

  /**
   * Handler for alarm dismiss hook triggered by libcalendaring
   */
  public function dismiss_alarms($p)
  {
    foreach($this->get_drivers() as $driver) { // TODO: Maybe use get_driver_by_cal() ?
      foreach ((array)$p['ids'] as $id) { 
        if (strpos($id, 'cal:bday:') === 0) {
          $p['success'] |= $driver->dismiss_birthday_alarm(substr($id, 9), $p['snooze']);
        } else if (strpos($id, 'cal:') === 0) {
          $p['success'] |= $driver->dismiss_alarm(substr($id, 4), $p['snooze']);
        }
      }
    }

    return $p;
  }

  /**
   * Handler for check-recent requests which are accidentally sent to calendar taks
   */
  function check_recent()
  {
    // NOP
    $this->rc->output->send();
  }

  /**
   * Hook triggered when a contact is saved
   */
  function contact_update($p)
  {
    // clear birthdays calendar cache
    if (!empty($p['record']['birthday'])) {
      $cache = $this->rc->get_cache('calendar.birthdays', 'db');
      $cache->remove();
    }
  }

    /**
     *
     */
    function import_events()
    {
      // Upload progress update
      if (!empty($_GET['_progress'])) {
        $this->rc->upload_progress();
      }

      @set_time_limit(0);

      // process uploaded file if there is no error
      $err = $_FILES['_data']['error'];

      if (!$err && $_FILES['_data']['tmp_name']) {
        $calendar   = rcube_utils::get_input_value('calendar', rcube_utils::INPUT_GPC);
        $rangestart = $_REQUEST['_range'] ? date_create("now -" . intval($_REQUEST['_range']) . " months") : 0;

        // extract zip file
        if ($_FILES['_data']['type'] == 'application/zip') {
          $count = 0;
          if (class_exists('ZipArchive', false)) {
            $zip = new ZipArchive();
            if ($zip->open($_FILES['_data']['tmp_name'])) {
              $randname = uniqid('zip-' . session_id(), true);
              $tmpdir = slashify($this->rc->config->get('temp_dir', sys_get_temp_dir())) . $randname;
              mkdir($tmpdir, 0700);

              // extract each ical file from the archive and import it
              for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('/\.ics$/i', $filename)) {
                  $tmpfile = $tmpdir . '/' . basename($filename);
                  if (copy('zip://' . $_FILES['_data']['tmp_name'] . '#'.$filename, $tmpfile)) {
                    $count += $this->import_from_file($tmpfile, $calendar, $rangestart, $errors);
                    unlink($tmpfile);
                  }
                }
              }

              rmdir($tmpdir);
              $zip->close();
            }
            else {
              $errors = 1;
              $msg = 'Failed to open zip file.';
            }
          }
          else {
            $errors = 1;
            $msg = 'Zip files are not supported for import.';
          }
        }
        else {
          // attempt to import teh uploaded file directly
          $count = $this->import_from_file($_FILES['_data']['tmp_name'], $calendar, $rangestart, $errors);
        }

        if ($count) {
          $this->rc->output->command('display_message', $this->gettext(array('name' => 'importsuccess', 'vars' => array('nr' => $count))), 'confirmation');
          $this->rc->output->command('plugin.import_success', array('source' => $calendar, 'refetch' => true));
        }
        else if (!$errors) {
          $this->rc->output->command('display_message', $this->gettext('importnone'), 'notice');
          $this->rc->output->command('plugin.import_success', array('source' => $calendar));
        }
        else {
          $this->rc->output->command('plugin.import_error', array('message' => $this->gettext('importerror') . ($msg ? ': ' . $msg : '')));
        }
      }
      else {
        if ($err == UPLOAD_ERR_INI_SIZE || $err == UPLOAD_ERR_FORM_SIZE) {
          $msg = rcmail::get_instance()->gettext(array('name' => 'filesizeerror', 'vars' => array(
            'size' => show_bytes(parse_bytes(ini_get('upload_max_filesize'))))));
        }
        else {
          $msg = rcmail::get_instance()->gettext('fileuploaderror');
        }

        $this->rc->output->command('plugin.import_error', array('message' => $msg));
      }

      $this->rc->output->send('iframe');
    }

  /**
   * Helper function to parse and import a single .ics file
   */
  private function import_from_file($filepath, $calendar, $rangestart, &$errors)
  {
    $user_email = $this->rc->user->get_username();

    $ical = $this->get_ical();
    $errors = !$ical->fopen($filepath);
    $count = $i = 0;
    $driver = $this->get_driver_by_cal($calendar);
    foreach ($ical as $event) {
      // keep the browser connection alive on long import jobs
      if (++$i > 100 && $i % 100 == 0) {
          echo "<!-- -->";
          ob_flush();
      }

      // TODO: correctly handle recurring events which start before $rangestart
      if ($event['end'] < $rangestart && (!$event['recurrence'] || ($event['recurrence']['until'] && $event['recurrence']['until'] < $rangestart)))
        continue;

      $event['_owner'] = $user_email;
      $event['calendar'] = $calendar;
      if ($driver->new_event($event)) {
        $count++;
      }
      else {
        $errors++;
      }
    }

    return $count;
  }

  /**
   * Construct the ics file for exporting events to iCalendar format;
   */
  function export_events($terminate = true)
  {
    $start = rcube_utils::get_input_value('start', rcube_utils::INPUT_GET);
    $end   = rcube_utils::get_input_value('end', rcube_utils::INPUT_GET);

    if (!isset($start))
      $start = 'today -1 year';
    if (!is_numeric($start))
      $start = strtotime($start . ' 00:00:00');
    if (!$end)
      $end = 'today +10 years';
    if (!is_numeric($end))
      $end = strtotime($end . ' 23:59:59');

    $event_id    = rcube_utils::get_input_value('id', rcube_utils::INPUT_GET);
    $attachments = rcube_utils::get_input_value('attachments', rcube_utils::INPUT_GET);
    $calid = $filename = rcube_utils::get_input_value('source', rcube_utils::INPUT_GET);
    $driver = $this->get_driver_by_cal($calid);
    $calendars = $this->driver->list_calendars();
    $events = array();

    if ($calendars[$calid]) {
      $filename = $calendars[$calid]['name'] ? $calendars[$calid]['name'] : $calid;
      $filename = asciiwords(html_entity_decode($filename));  // to 7bit ascii
      if (!empty($event_id)) {
        if ($event = $driver->get_event(array('calendar' => $calid, 'id' => $event_id), 0, true)) {
          if ($event['recurrence_id']) {
            $event = $driver->get_event(array('calendar' => $calid, 'id' => $event['recurrence_id']), 0, true);
          }
          $events = array($event);
          $filename = asciiwords($event['title']);
          if (empty($filename))
            $filename = 'event';
        }
      }
      else {
        $events = $driver->load_events($start, $end, null, $calid, 0);
        if (empty($filename))
          $filename = $calid;
      }
    }

    header("Content-Type: text/calendar");
    header("Content-Disposition: inline; filename=".$filename.'.ics');

    $this->get_ical()->export($events, '', true, $attachments ? array($driver, 'get_attachment_body') : null);

    if ($terminate)
      exit;
  }

  /**
   * Handler for iCal feed requests
   */
  function ical_feed_export()
  {
    $session_exists = !empty($_SESSION['user_id']);

    // process HTTP auth info
    if (!empty($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
      $_POST['_user'] = $_SERVER['PHP_AUTH_USER']; // used for rcmail::autoselect_host()
      $auth = $this->rc->plugins->exec_hook('authenticate', array(
        'host' => $this->rc->autoselect_host(),
        'user' => trim($_SERVER['PHP_AUTH_USER']),
        'pass' => $_SERVER['PHP_AUTH_PW'],
        'cookiecheck' => true,
        'valid' => true,
      ));
      if ($auth['valid'] && !$auth['abort'])
        $this->rc->login($auth['user'], $auth['pass'], $auth['host']);
    }

    // require HTTP auth
    if (empty($_SESSION['user_id'])) {
      header('WWW-Authenticate: Basic realm="Roundcube Calendar"');
      header('HTTP/1.0 401 Unauthorized');
      exit;
    }

    // decode calendar feed hash
    $format = 'ics';
    $calhash = rcube_utils::get_input_value('_cal', rcube_utils::INPUT_GET);
    if (preg_match(($suff_regex = '/\.([a-z0-9]{3,5})$/i'), $calhash, $m)) {
      $format = strtolower($m[1]);
      $calhash = preg_replace($suff_regex, '', $calhash);
    }

    if (!strpos($calhash, ':'))
      $calhash = base64_decode($calhash);

    list($user, $_GET['source']) = explode(':', $calhash, 2);

    // sanity check user
    if ($this->rc->user->get_username() == $user) {
      $this->export_events(false);
    }
    else {
      header('HTTP/1.0 404 Not Found');
    }

    // don't save session data
    if (!$session_exists)
      session_destroy();
    exit;
  }


  /**
   *
   */
  function load_settings()
  {
    $this->lib->load_settings();
    $this->defaults += $this->lib->defaults;

    $settings = array();

    // configuration
    $settings['default_calendar'] = $this->rc->config->get('calendar_default_calendar');
    $settings['default_view'] = (string)$this->rc->config->get('calendar_default_view', $this->defaults['calendar_default_view']);
    $settings['date_agenda'] = (string)$this->rc->config->get('calendar_date_agenda', $this->defaults['calendar_date_agenda']);

    $settings['timeslots'] = (int)$this->rc->config->get('calendar_timeslots', $this->defaults['calendar_timeslots']);
    $settings['first_day'] = (int)$this->rc->config->get('calendar_first_day', $this->defaults['calendar_first_day']);
    $settings['first_hour'] = (int)$this->rc->config->get('calendar_first_hour', $this->defaults['calendar_first_hour']);
    $settings['work_start'] = (int)$this->rc->config->get('calendar_work_start', $this->defaults['calendar_work_start']);
    $settings['work_end'] = (int)$this->rc->config->get('calendar_work_end', $this->defaults['calendar_work_end']);
    $settings['agenda_range'] = (int)$this->rc->config->get('calendar_agenda_range', $this->defaults['calendar_agenda_range']);
    $settings['agenda_sections'] = $this->rc->config->get('calendar_agenda_sections', $this->defaults['calendar_agenda_sections']);
    $settings['event_coloring'] = (int)$this->rc->config->get('calendar_event_coloring', $this->defaults['calendar_event_coloring']);
    $settings['time_indicator'] = (int)$this->rc->config->get('calendar_time_indicator', $this->defaults['calendar_time_indicator']);
    $settings['invite_shared'] = (int)$this->rc->config->get('calendar_allow_invite_shared', $this->defaults['calendar_allow_invite_shared']);
    $settings['invitation_calendars'] = (bool)$this->rc->config->get('kolab_invitation_calendars', false);
    $settings['itip_notify'] = (int)$this->rc->config->get('calendar_itip_send_option', $this->defaults['calendar_itip_send_option']);

    // get user identity to create default attendee
    if ($this->ui->screen == 'calendar') {
      $identity = null;
      foreach ($this->rc->user->list_emails() as $rec) {
        if (!$identity)
          $identity = $rec;
        $identity['emails'][] = $rec['email'];
        $settings['identities'][$rec['identity_id']] = $rec['email'];
      }
      $identity['emails'][] = $this->rc->user->get_username();
      $settings['identity'] = array('name' => $identity['name'], 'email' => strtolower($identity['email']), 'emails' => ';' . strtolower(join(';', $identity['emails'])));
    }

    return $settings;
  }

  /**
   * Encode events as JSON
   *
   * @param  array  Events as array
   * @param  boolean Add CSS class names according to calendar and categories
   * @return string JSON encoded events
   */
  function encode($events, $addcss = false)
  {
    $json = array();
    foreach ($events as $event) {
      $json[] = $this->_client_event($event, $addcss);
    }
    return json_encode($json);
  }

  /**
   * Convert an event object to be used on the client
   */
  private function _client_event($event, $addcss = false)
  {
    // compose a human readable strings for alarms_text and recurrence_text
    if ($event['valarms']) {
      $event['alarms_text'] = libcalendaring::alarms_text($event['valarms']);
      $event['valarms'] = libcalendaring::to_client_alarms($event['valarms']);
    }
    if ($event['recurrence']) {
      $event['recurrence_text'] = $this->lib->recurrence_text($event['recurrence']);
      $event['recurrence'] = $this->lib->to_client_recurrence($event['recurrence'], $event['allday']);
      unset($event['recurrence_date']);
    }

    foreach ((array)$event['attachments'] as $k => $attachment) {
      $event['attachments'][$k]['classname'] = rcube_utils::file2class($attachment['mimetype'], $attachment['name']);
    }

    // Get driver for event calendar
    $driver = $this->get_driver_by_cal($event['calendar']);

    // convert link URIs references into structs
    if (array_key_exists('links', $event)) {
      foreach ((array)$event['links'] as $i => $link) {
        if (strpos($link, 'imap://') === 0 && ($msgref = $driver->get_message_reference($link))) {
          $event['links'][$i] = $msgref;
        }
      }
    }

    // check for organizer in attendees list
    $organizer = null;
    foreach ((array)$event['attendees'] as $i => $attendee) {
      if ($attendee['role'] == 'ORGANIZER') {
        $organizer = $attendee;
      }
      if ($attendee['status'] == 'DELEGATED' && $attendee['rsvp'] == false) {
        $event['attendees'][$i]['noreply'] = true;
      }
      else {
        unset($event['attendees'][$i]['noreply']);
      }
    }

    if ($organizer === null && !empty($event['organizer'])) {
      $organizer = $event['organizer'];
      $organizer['role'] = 'ORGANIZER';
      if (!is_array($event['attendees']))
        $event['attendees'] = array();
      array_unshift($event['attendees'], $organizer);
    }
	
	// Convert HTML description into plain text
     if ($this->is_html($event)) {
       $h2t = new rcube_html2text($event['description'], false, true, 0);
       $event['description'] = trim($h2t->get_text());
     }

    // mapping url => vurl because of the fullcalendar client script
    $event['vurl'] = $event['url'];
    unset($event['url']);

    return array(
      '_id'   => $event['calendar'] . ':' . $event['id'],  // unique identifier for fullcalendar
      'start' => $this->lib->adjust_timezone($event['start'], $event['allday'])->format('c'),
      'end'   => $this->lib->adjust_timezone($event['end'], $event['allday'])->format('c'),
      // 'changed' might be empty for event recurrences (Bug #2185)
      'changed' => $event['changed'] ? $this->lib->adjust_timezone($event['changed'])->format('c') : null,
      'created' => $event['created'] ? $this->lib->adjust_timezone($event['created'])->format('c') : null,
      'title'       => strval($event['title']),
      'description' => strval($event['description']),
      'location'    => strval($event['location']),
      'className'   => ($addcss ? 'fc-event-cal-'.asciiwords($event['calendar'], true).' ' : '') .
          'fc-event-cat-' . asciiwords(strtolower(join('-', (array)$event['categories'])), true) .
          rtrim(' ' . $event['className']),
      'allDay'      => ($event['allday'] == 1),
    ) + $event;
  }


  /**
   * Generate a unique identifier for an event
   */
  public function generate_uid()
  {
    return strtoupper(md5(time() . uniqid(rand())) . '-' . substr(md5($this->rc->user->get_username()), 0, 16));
  }


  /**
   * TEMPORARY: generate random event data for testing
   * Create events by opening http://<roundcubeurl>/?_task=calendar&_action=randomdata&_driver=kolab&_num=500&_date=2014-08-01&_dev=120
   */
  public function generate_randomdata()
  {
    @set_time_limit(0);

    $driver = $this->get_driver_by_gpc();
    $num   = $_REQUEST['_num'] ? intval($_REQUEST['_num']) : 100;
    $date  = $_REQUEST['_date'] ?: 'now';
    $dev   = $_REQUEST['_dev'] ?: 30;
    $cats  = array_keys($driver->list_categories());
    $cals  = $driver->list_calendars(calendar_driver::FILTER_ACTIVE);
    $count = 0;

    while ($count++ < $num) {
      $spread = intval($dev) * 86400; // days
      $refdate = strtotime($date);
      $start = round(($refdate + rand(-$spread, $spread)) / 600) * 600;
      $duration = round(rand(30, 360) / 30) * 30 * 60;
      $allday = rand(0,20) > 18;
      $alarm = rand(-30,12) * 5;
      $fb = rand(0,2);
      
      if (date('G', $start) > 23)
        $start -= 3600;
      
      if ($allday) {
        $start = strtotime(date('Y-m-d 00:00:00', $start));
        $duration = 86399;
      }
      
      $title = '';
      $len = rand(2, 12);
      $words = explode(" ", "The Hough transform is named after Paul Hough who patented the method in 1962. It is a technique which can be used to isolate features of a particular shape within an image. Because it requires that the desired features be specified in some parametric form, the classical Hough transform is most commonly used for the de- tection of regular curves such as lines, circles, ellipses, etc. A generalized Hough transform can be employed in applications where a simple analytic description of a feature(s) is not possible. Due to the computational complexity of the generalized Hough algorithm, we restrict the main focus of this discussion to the classical Hough transform. Despite its domain restrictions, the classical Hough transform (hereafter referred to without the classical prefix ) retains many applications, as most manufac- tured parts (and many anatomical parts investigated in medical imagery) contain feature boundaries which can be described by regular curves. The main advantage of the Hough transform technique is that it is tolerant of gaps in feature boundary descriptions and is relatively unaffected by image noise.");
//      $chars = "!# abcdefghijklmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ 1234567890";
      for ($i = 0; $i < $len; $i++)
        $title .= $words[rand(0,count($words)-1)] . " ";
      
      $driver->new_event(array(
        'uid' => $this->generate_uid(),
        'start' => new DateTime('@'.$start),
        'end' => new DateTime('@'.($start + $duration)),
        'allday' => $allday,
        'title' => rtrim($title),
        'free_busy' => $fb == 2 ? 'outofoffice' : ($fb ? 'busy' : 'free'),
        'categories' => $cats[array_rand($cats)],
        'calendar' => array_rand($cals),
        'alarms' => $alarm > 0 ? "-{$alarm}M:DISPLAY" : '',
        'priority' => rand(0,9),
      ));
    }
    
    $this->rc->output->redirect('');
  }

  /**
   * Handler for attachments upload
   */
  public function attachment_upload()
  {
    $this->lib->attachment_upload(self::SESSION_KEY, 'cal-');
  }

  /**
   * Handler for attachments download/displaying
   */
  public function attachment_get()
  {
    // show loading page
    if (!empty($_GET['_preload'])) {
        return $this->lib->attachment_loading_page();
    }

    $event_id = rcube_utils::get_input_value('_event', rcube_utils::INPUT_GPC);
    $calendar = rcube_utils::get_input_value('_cal', rcube_utils::INPUT_GPC);
    $id       = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
    $rev      = rcube_utils::get_input_value('_rev', rcube_utils::INPUT_GPC);
    $driver = $this->get_driver_by_cal($calendar);

    $event = array('id' => $event_id, 'calendar' => $calendar, 'rev' => $rev);
    $attachment = $driver->get_attachment($id, $event);

    // show part page
    if (!empty($_GET['_frame'])) {
        $this->lib->attachment = $attachment;
        $this->register_handler('plugin.attachmentframe', array($this->lib, 'attachment_frame'));
        $this->register_handler('plugin.attachmentcontrols', array($this->lib, 'attachment_header'));
        $this->rc->output->send('calendar.attachment');
    }
    // deliver attachment content
    else if ($attachment) {
        $attachment['body'] = $driver->get_attachment_body($id, $event);
        $this->lib->attachment_get($attachment);
    }

    // if we arrive here, the requested part was not found
    header('HTTP/1.1 404 Not Found');
    exit;
  }

  /**
    * Determine whether the given event description is HTML formatted
    */
   private function is_html($event)
   {
       // check for opening and closing <html> or <body> tags
       return (preg_match('/<(html|body)(\s+[a-z]|>)/', $event['description'], $m) && strpos($event['description'], '</'.$m[1].'>') > 0);
   }

  /**
   * Prepares new/edited event properties before save
   */
  private function write_preprocess(&$event, $action)
  {
    // convert dates into DateTime objects in user's current timezone
    $event['start'] = new DateTime($event['start'], $this->timezone);
    $event['end'] = new DateTime($event['end'], $this->timezone);
    $event['allday'] = (bool)$event['allday'];

    // start/end is all we need for 'move' action (#1480)
    if ($action == 'move') {
      return;
    }

    // convert the submitted recurrence settings
    if (is_array($event['recurrence'])) {
      $event['recurrence'] = $this->lib->from_client_recurrence($event['recurrence'], $event['start']);
    }

    // convert the submitted alarm values
    if ($event['valarms']) {
      $event['valarms'] = libcalendaring::from_client_alarms($event['valarms']);
    }

    $attachments = array();
    $eventid     = 'cal-'.$event['id'];

    if (is_array($_SESSION[self::SESSION_KEY]) && $_SESSION[self::SESSION_KEY]['id'] == $eventid) {
      if (!empty($_SESSION[self::SESSION_KEY]['attachments'])) {
        foreach ($_SESSION[self::SESSION_KEY]['attachments'] as $id => $attachment) {
          if (is_array($event['attachments']) && in_array($id, $event['attachments'])) {
            $attachments[$id] = $this->rc->plugins->exec_hook('attachment_get', $attachment);
          }
        }
      }
    }

    $event['attachments'] = $attachments;

    // convert link references into simple URIs
    if (array_key_exists('links', $event)) {
      $event['links'] = array_map(function($link) {
          return is_array($link) ? $link['uri'] : strval($link);
        }, (array)$event['links']);
    }

    // check for organizer in attendees
    if ($action == 'new' || $action == 'edit') {
      if (!$event['attendees'])
        $event['attendees'] = array();

      $emails = $this->get_user_emails();
      $organizer = $owner = false;
      foreach ((array)$event['attendees'] as $i => $attendee) {
        if ($attendee['role'] == 'ORGANIZER')
          $organizer = $i;
        if ($attendee['email'] == in_array(strtolower($attendee['email']), $emails))
          $owner = $i;
        if (!isset($attendee['rsvp']))
          $event['attendees'][$i]['rsvp'] = true;
        else if (is_string($attendee['rsvp']))
          $event['attendees'][$i]['rsvp'] = $attendee['rsvp'] == 'true' || $attendee['rsvp'] == '1';
      }

      // set new organizer identity
      if ($organizer !== false && !empty($event['_identity']) && ($identity = $this->rc->user->get_identity($event['_identity']))) {
        $event['attendees'][$organizer]['name'] = $identity['name'];
        $event['attendees'][$organizer]['email'] = $identity['email'];
      }

      // set owner as organizer if yet missing
      if ($organizer === false && $owner !== false) {
        $event['attendees'][$owner]['role'] = 'ORGANIZER';
        unset($event['attendees'][$owner]['rsvp']);
      }
    }

    // mapping url => vurl because of the fullcalendar client script
    if (array_key_exists('vurl', $event)) {
      $event['url'] = $event['vurl'];
      unset($event['vurl']);
    }
  }

  /**
   * Releases some resources after successful event save
   */
  private function cleanup_event(&$event)
  {
    // remove temp. attachment files
    if (!empty($_SESSION[self::SESSION_KEY]) && ($eventid = $_SESSION[self::SESSION_KEY]['id'])) {
      $this->rc->plugins->exec_hook('attachments_cleanup', array('group' => $eventid));
      $this->rc->session->remove(self::SESSION_KEY);
    }
  }

  /**
   * Send out an invitation/notification to all event attendees
   */
  private function notify_attendees($event, $old, $action = 'edit', $comment = null, $rsvp = null)
  {
    if ($action == 'remove' || ($event['status'] == 'CANCELLED' && $old['status'] != $event['status'])) {
      $event['cancelled'] = true;
      $is_cancelled = true;
    }
	
    if ($rsvp === null)
       $rsvp = !$old || $event['sequence'] > $old['sequence'];
 
    $itip = $this->load_itip();
    $emails = $this->get_user_emails();
    $itip_notify = (int)$this->rc->config->get('calendar_itip_send_option', $this->defaults['calendar_itip_send_option']);

    // add comment to the iTip attachment
    $event['comment'] = $comment;

    // set a valid recurrence-id if this is a recurrence instance
    libcalendaring::identify_recurrence_instance($event);

    // compose multipart message using PEAR:Mail_Mime
    $method = $action == 'remove' ? 'CANCEL' : 'REQUEST';
    $message = $itip->compose_itip_message($event, $method, $rsvp);

    // list existing attendees from $old event
    $old_attendees = array();
    foreach ((array)$old['attendees'] as $attendee) {
      $old_attendees[] = $attendee['email'];
    }

    // send to every attendee
    $sent = 0; $current = array();
    foreach ((array)$event['attendees'] as $attendee) {
      $current[] = strtolower($attendee['email']);
      
      // skip myself for obvious reasons
      if (!$attendee['email'] || in_array(strtolower($attendee['email']), $emails))
        continue;

      // skip if notification is disabled for this attendee
      if ($attendee['noreply'] && $itip_notify & 2)
        continue;

      // skip if this attendee has delegated and set RSVP=FALSE
      if ($attendee['status'] == 'DELEGATED' && $attendee['rsvp'] === false)
        continue;

      // which template to use for mail text
      $is_new = !in_array($attendee['email'], $old_attendees);
      $is_rsvp = $is_new || $event['sequence'] > $old['sequence'];
      $bodytext = $is_cancelled ? 'eventcancelmailbody' : ($is_new ? 'invitationmailbody' : 'eventupdatemailbody');
      $subject  = $is_cancelled ? 'eventcancelsubject'  : ($is_new ? 'invitationsubject' : ($event['title'] ? 'eventupdatesubject':'eventupdatesubjectempty'));

      $event['comment'] = $comment;

      // finally send the message
      if ($itip->send_itip_message($event, $method, $attendee, $subject, $bodytext, $message, $is_rsvp))
        $sent++;
      else
        $sent = -100;
    }

    // TODO: on change of a recurring (main) event, also send updates to differing attendess of recurrence exceptions

    // send CANCEL message to removed attendees
    foreach ((array)$old['attendees'] as $attendee) {
      if ($attendee['role'] == 'ORGANIZER' || !$attendee['email'] || in_array(strtolower($attendee['email']), $current))
        continue;

      $vevent = $old;
      $vevent['cancelled'] = $is_cancelled;
      $vevent['attendees'] = array($attendee);
      $vevent['comment']   = $comment;
      if ($itip->send_itip_message($vevent, 'CANCEL', $attendee, 'eventcancelsubject', 'eventcancelmailbody'))
        $sent++;
      else
        $sent = -100;
    }

    return $sent;
  }

  private function _get_freebusy_list($email, $start, $end)
  {
    $fblist = array();
    foreach($this->get_drivers() as $driver){
      if($driver->freebusy) {
        $cur = $driver->get_freebusy_list($email, $start, $end);
        if($cur) {
          $fblist = array_merge($fblist, $cur);
        }
      }
    }

    if(sizeof($fblist) == 0) return false;
    else return $fblist;
  }

  /**
   * Echo simple free/busy status text for the given user and time range
   */
  public function freebusy_status()
  {
    $email = rcube_utils::get_input_value('email', rcube_utils::INPUT_GPC);
    $start = rcube_utils::get_input_value('start', rcube_utils::INPUT_GPC);
    $end   = rcube_utils::get_input_value('end', rcube_utils::INPUT_GPC);

    // convert dates into unix timestamps
    if (!empty($start) && !is_numeric($start)) {
      $dts = new DateTime($start, $this->timezone);
      $start = $dts->format('U');
    }
    if (!empty($end) && !is_numeric($end)) {
      $dte = new DateTime($end, $this->timezone);
      $end = $dte->format('U');
    }
    
    if (!$start) $start = time();
    if (!$end) $end = $start + 3600;
    
    $fbtypemap = array(calendar::FREEBUSY_UNKNOWN => 'UNKNOWN', calendar::FREEBUSY_FREE => 'FREE', calendar::FREEBUSY_BUSY => 'BUSY', calendar::FREEBUSY_TENTATIVE => 'TENTATIVE', calendar::FREEBUSY_OOF => 'OUT-OF-OFFICE');
    $status = 'UNKNOWN';
    
    // if the backend has free-busy information
    $fblist = $this->_get_freebusy_list($email, $start, $end);
    if (is_array($fblist)) {
      $status = 'FREE';
      
      foreach ($fblist as $slot) {
        list($from, $to, $type) = $slot;
        if ($from < $end && $to > $start) {
          $status = isset($type) && $fbtypemap[$type] ? $fbtypemap[$type] : 'BUSY';
          break;
        }
      }
    }
    
    // let this information be cached for 5min
    send_future_expire_header(300);
    
    echo $status;
    exit;
  }
  
  /**
   * Return a list of free/busy time slots within the given period
   * Echo data in JSON encoding
   */
  public function freebusy_times()
  {
    $email = rcube_utils::get_input_value('email', rcube_utils::INPUT_GPC);
    $start = rcube_utils::get_input_value('start', rcube_utils::INPUT_GPC);
    $end   = rcube_utils::get_input_value('end', rcube_utils::INPUT_GPC);
    $interval  = intval(rcube_utils::get_input_value('interval', rcube_utils::INPUT_GPC));
    $strformat = $interval > 60 ? 'Ymd' : 'YmdHis';

    // convert dates into unix timestamps
    if (!empty($start) && !is_numeric($start)) {
      $dts = rcube_utils::anytodatetime($start, $this->timezone);
      $start = $dts ? $dts->format('U') : null;
    }
    if (!empty($end) && !is_numeric($end)) {
      $dte = rcube_utils::anytodatetime($end, $this->timezone);
      $end = $dte ? $dte->format('U') : null;
    }

    if (!$start) $start = time();
    if (!$end)   $end = $start + 86400 * 30;
    if (!$interval) $interval = 60;  // 1 hour
    
    if (!$dte) {
      $dts = new DateTime('@'.$start);
      $dts->setTimezone($this->timezone);
    }
    
    $fblist = $this->_get_freebusy_list($email, $start, $end);
    $slots = array();
    
    // build a list from $start till $end with blocks representing the fb-status
    for ($s = 0, $t = $start; $t <= $end; $s++) {
      $status = self::FREEBUSY_UNKNOWN;
      $t_end = $t + $interval * 60;
      $dt = new DateTime('@'.$t);
      $dt->setTimezone($this->timezone);

      // determine attendee's status
      if (is_array($fblist)) {
        $status = self::FREEBUSY_FREE;
        foreach ($fblist as $slot) {
          list($from, $to, $type) = $slot;

          // check for possible all-day times
          if (gmdate('His', $from) == '000000' && gmdate('His', $to) == '235959') {
              // shift into the user's timezone for sane matching
              $from -= $this->gmt_offset;
              $to   -= $this->gmt_offset;
          }

          if ($from < $t_end && $to > $t) {
            $status = isset($type) ? $type : self::FREEBUSY_BUSY;
            if ($status == self::FREEBUSY_BUSY)  // can't get any worse :-)
              break;
          }
        }
      }
      
      $slots[$s] = $status;
      $times[$s] = intval($dt->format($strformat));
      $t = $t_end;
    }
    
    $dte = new DateTime('@'.$t_end);
    $dte->setTimezone($this->timezone);
    
    // let this information be cached for 5min
    send_future_expire_header(300);
    
    echo json_encode(array(
      'email' => $email,
      'start' => $dts->format('c'),
      'end'   => $dte->format('c'),
      'interval' => $interval,
      'slots' => $slots,
      'times' => $times,
    ));
    exit;
  }

  /**
   * Handler for printing calendars
   */
  public function print_view()
  {
    $title = $this->gettext('print');

    $view = rcube_utils::get_input_value('view', rcube_utils::INPUT_GPC);
    if (!in_array($view, array('agendaWeek', 'agendaDay', 'month', 'table')))
      $view = 'agendaDay';

    $this->rc->output->set_env('view',$view);

    if ($date = rcube_utils::get_input_value('date', rcube_utils::INPUT_GPC))
      $this->rc->output->set_env('date', $date);

    if ($range = rcube_utils::get_input_value('range', rcube_utils::INPUT_GPC))
      $this->rc->output->set_env('listRange', intval($range));

    if (isset($_REQUEST['sections']))
      $this->rc->output->set_env('listSections', rcube_utils::get_input_value('sections', rcube_utils::INPUT_GPC));

    if ($search = rcube_utils::get_input_value('search', rcube_utils::INPUT_GPC)) {
      $this->rc->output->set_env('search', $search);
      $title .= ' "' . $search . '"';
    }

    // Add CSS stylesheets to the page header
    $skin_path = $this->local_skin_path();
    $this->include_stylesheet($skin_path . '/fullcalendar.css');
    $this->include_stylesheet($skin_path . '/print.css');
    
    // Add JS files to the page header
    $this->include_script('print.js');
    $this->include_script('lib/js/fullcalendar.js');
    
    $this->register_handler('plugin.calendar_css', array($this->ui, 'calendar_css'));
    $this->register_handler('plugin.calendar_list', array($this->ui, 'calendar_list'));
    
    $this->rc->output->set_pagetitle($title);
    $this->rc->output->send("calendar.print");
  }

  /**
   *
   */
  public function get_inline_ui()
  {
    foreach (array('save','cancel','savingdata') as $label)
      $texts['calendar.'.$label] = $this->gettext($label);
    
    $texts['calendar.new_event'] = $this->gettext('createfrommail');
    
    $this->ui->init_templates();
    $this->ui->calendar_list();  # set env['calendars']
    echo $this->api->output->parse('calendar.eventedit', false, false);
    echo html::tag('script', array('type' => 'text/javascript'),
      "rcmail.set_env('calendars', " . json_encode($this->api->output->env['calendars']) . ");\n".
      "rcmail.set_env('deleteicon', '" . $this->api->output->env['deleteicon'] . "');\n".
      "rcmail.set_env('cancelicon', '" . $this->api->output->env['cancelicon'] . "');\n".
      "rcmail.set_env('loadingicon', '" . $this->api->output->env['loadingicon'] . "');\n".
      "rcmail.gui_object('attachmentlist', '"  . $this->ui->attachmentlist_id . "');\n".
      "rcmail.add_label(" . json_encode($texts) . ");\n"
    );
    exit;
  }

  /**
   * Compare two event objects and return differing properties
   *
   * @param array Event A
   * @param array Event B
   * @return array List of differing event properties
   */
  public static function event_diff($a, $b)
  {
    $diff = array();
    $ignore = array('changed' => 1, 'attachments' => 1);
    foreach (array_unique(array_merge(array_keys($a), array_keys($b))) as $key) {
      if (!$ignore[$key] && $key[0] != '_' && $a[$key] != $b[$key])
        $diff[] = $key;
    }
    
    // only compare number of attachments
    if (isset($a['attachments']) != isset($b['attachments']))
      $diff[] = 'attachments';
    
    return $diff;
  }

  /**
   * Update attendee properties on the given event object
   *
   * @param array The event object to be altered
   * @param array List of hash arrays each represeting an updated/added attendee
   */
  public static function merge_attendee_data(&$event, $attendees, $removed = null)
  {
    if (!empty($attendees) && !is_array($attendees[0])) {
      $attendees = array($attendees);
    }

    foreach ($attendees as $attendee) {
      $found = false;

      foreach ($event['attendees'] as $i => $candidate) {
        if ($candidate['email'] == $attendee['email']) {
          $event['attendees'][$i] = $attendee;
          $found = true;
          break;
        }
      }

      if (!$found) {
        $event['attendees'][] = $attendee;
      }
    }

    // filter out removed attendees
    if (!empty($removed)) {
      $event['attendees'] = array_filter($event['attendees'], function($attendee) use ($removed) {
        return !in_array($attendee['email'], $removed);
      });
    }
  }


  /****  Resource management functions  ****/

  /**
   * Getter for the configured implementation of the resource directory interface
   */
  private function resources_directory()
  {
    if (is_object($this->resources_dir)) {
      return $this->resources_dir;
    }

    if ($driver_name = $this->rc->config->get('calendar_resources_driver')) {
      $driver_class = 'resources_driver_' . $driver_name;

      require_once($this->home . '/drivers/resources_driver.php');
      require_once($this->home . '/drivers/' . $driver_name . '/' . $driver_class . '.php');

      $this->resources_dir = new $driver_class($this);
    }

    return $this->resources_dir;
  }

  /**
   * Handler for resoruce autocompletion requests
   */
  public function resources_autocomplete()
  {
    $search = rcube_utils::get_input_value('_search', rcube_utils::INPUT_GPC, true);
    $sid    = rcube_utils::get_input_value('_reqid', rcube_utils::INPUT_GPC);
    $maxnum = (int)$this->rc->config->get('autocomplete_max', 15);
    $results = array();

    if ($directory = $this->resources_directory()) {
      foreach ($directory->load_resources($search, $maxnum) as $rec) {
        $results[]  = array(
            'name'  => $rec['name'],
            'email' => $rec['email'],
            'type'  => $rec['_type'],
        );
      }
    }

    $this->rc->output->command('ksearch_query_results', $results, $search, $sid);
    $this->rc->output->send();
  }

  /**
   * Handler for load-requests for resource data
   */
  function resources_list()
  {
    $data = array();

    if ($directory = $this->resources_directory()) {
      foreach ($directory->load_resources() as $rec) {
        $data[] = $rec;
      }
    }

    $this->rc->output->command('plugin.resource_data', $data);
    $this->rc->output->send();
  }

  /**
   * Handler for requests loading resource owner information
   */
  function resources_owner()
  {
    if ($directory = $this->resources_directory()) {
      $id = rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC);
      $data = $directory->get_resource_owner($id);
    }

    $this->rc->output->command('plugin.resource_owner', $data);
    $this->rc->output->send();
  }

  /**
   * Deliver event data for a resource's calendar
   */
  function resources_calendar()
  {
    $events = array();

    if ($directory = $this->resources_directory()) {
      $events = $directory->get_resource_calendar(
        rcube_utils::get_input_value('_id', rcube_utils::INPUT_GPC),
        rcube_utils::get_input_value('start', rcube_utils::INPUT_GET),
        rcube_utils::get_input_value('end', rcube_utils::INPUT_GET));
    }

    echo $this->encode($events);
    exit;
  }


  /****  Event invitation plugin hooks ****/

  /**
   * Handler for calendar/itip-status requests
   */
  function event_itip_status()
  {
    $data = rcube_utils::get_input_value('data', rcube_utils::INPUT_POST, true);

    // find local copy of the referenced event
    $driver = null;

    if(isset($data["uid"]))
      $driver = $this->get_driver_by_event($data["uid"]);

    if($driver == null)
      $driver = $this->get_default_driver();

    $existing = $driver->get_event($data, calendar_driver::FILTER_WRITEABLE | calendar_driver::FILTER_PERSONAL);

    $itip = $this->load_itip();
    $response = $itip->get_itip_status($data, $existing);

    // get a list of writeable calendars to save new events to
    if (!$existing && !$data['nosave'] && $response['action'] == 'rsvp' || $response['action'] == 'import') {
      $calendars = $driver->list_calendars(calendar_driver::FILTER_PERSONAL);
      $calendar_select = new html_select(array('name' => 'calendar', 'id' => 'itip-saveto', 'is_escaped' => true));
      $calendar_select->add('--', '');
      $numcals = 0;
      foreach ($calendars as $calendar) {
        if ($calendar['editable']) {
          $calendar_select->add($calendar['name'], $calendar['id']);
          $numcals++;
        }
      }
      if ($numcals <= 1)
        $calendar_select = null;
    }

    if ($calendar_select) {
      $default_calendar = $this->get_default_calendar($data['sensitivity']);
      $response['select'] = html::span('folder-select', $this->gettext('saveincalendar') . '&nbsp;' .
        $calendar_select->show($default_calendar['id']));
    }
    else if ($data['nosave']) {
      $response['select'] = html::tag('input', array('type' => 'hidden', 'name' => 'calendar', 'id' => 'itip-saveto', 'value' => ''));
    }

    // render small agenda view for the respective day
    if ($data['method'] == 'REQUEST' && !empty($data['date']) && $response['action'] == 'rsvp') {
      $event_start = rcube_utils::anytodatetime($data['date']);
      $day_start = new Datetime(gmdate('Y-m-d 00:00', $data['date']), $this->lib->timezone);
      $day_end = new Datetime(gmdate('Y-m-d 23:59', $data['date']), $this->lib->timezone);

      // get events on that day from the user's personal calendars
      $calendars = $driver->list_calendars(calendar_driver::FILTER_PERSONAL);
      $events = $driver->load_events($day_start->format('U'), $day_end->format('U'), null, array_keys($calendars));
      usort($events, function($a, $b) { return $a['start'] > $b['start'] ? 1 : -1; });

      $before = $after = array();
      foreach ($events as $event) {
        // TODO: skip events with free_busy == 'free' ?
        if ($event['uid'] == $data['uid'] || $event['end'] < $day_start || $event['start'] > $day_end)
          continue;
        else if ($event['start'] < $event_start)
          $before[] = $this->mail_agenda_event_row($event);
        else
          $after[] = $this->mail_agenda_event_row($event);
      }

      $response['append'] = array(
        'selector' => '.calendar-agenda-preview',
        'replacements' => array(
          '%before%' => !empty($before) ? join("\n", array_slice($before,  -3)) : html::div('event-row no-event', $this->gettext('noearlierevents')),
          '%after%'  => !empty($after)  ? join("\n", array_slice($after, 0, 3)) : html::div('event-row no-event', $this->gettext('nolaterevents')),
        ),
      );
    }

    $this->rc->output->command('plugin.update_itip_object_status', $response);
  }

  /**
   * Handler for calendar/itip-remove requests
   */
  function event_itip_remove()
  {
    $success  = false;
    $uid      = rcube_utils::get_input_value('uid', rcube_utils::INPUT_POST);
    $instance = rcube_utils::get_input_value('_instance', rcube_utils::INPUT_POST);
    $savemode = rcube_utils::get_input_value('_savemode', rcube_utils::INPUT_POST);

    // search for event if only UID is given
    $driver = $this->get_driver_by_event($uid);
    if ($event = $driver->get_event(array('uid' => $uid, '_instance' => $instance), calendar_driver::FILTER_WRITEABLE)) {
      $event['_savemode'] = $savemode;
      $success = $driver->remove_event($event, true);
    }

    if ($success) {
      $this->rc->output->show_message('calendar.successremoval', 'confirmation');
    }
    else {
      $this->rc->output->show_message('calendar.errorsaving', 'error');
    }
  }

  /**
   * Handler for URLs that allow an invitee to respond on his invitation mail
   */
  public function itip_attend_response($p)
  {
    if ($p['action'] == 'attend') {
      $this->ui->init();

      $this->rc->output->set_env('task', 'calendar');  // override some env vars
      $this->rc->output->set_env('refresh_interval', 0);
      $this->rc->output->set_pagetitle($this->gettext('calendar'));

      $itip  = $this->load_itip();
      $token = rcube_utils::get_input_value('_t', rcube_utils::INPUT_GPC);

      // read event info stored under the given token
      if ($invitation = $itip->get_invitation($token)) {
        $this->token = $token;
        $this->event = $invitation['event'];

        // show message about cancellation
        if ($invitation['cancelled']) {
          $this->invitestatus = html::div('rsvp-status declined', $itip->gettext('eventcancelled'));
        }
        // save submitted RSVP status
        else if (!empty($_POST['rsvp'])) {
          $status = null;
          foreach (array('accepted','tentative','declined') as $method) {
            if ($_POST['rsvp'] == $itip->gettext('itip' . $method)) {
              $status = $method;
              break;
            }
          }

          // send itip reply to organizer
          $invitation['event']['comment'] = rcube_utils::get_input_value('_comment', rcube_utils::INPUT_POST);
          if ($status && $itip->update_invitation($invitation, $invitation['attendee'], strtoupper($status))) {
            $this->invitestatus = html::div('rsvp-status ' . strtolower($status), $itip->gettext('youhave'.strtolower($status)));
          }
          else
            $this->rc->output->command('display_message', $this->gettext('errorsaving'), 'error', -1);

          // if user is logged in...
          if ($this->rc->user->ID) {
            $invitation = $itip->get_invitation($token);
            $driver = $this->get_driver_by_cal($invitation['event']['calendar']);

            // save the event to his/her default calendar if not yet present
            if (!$driver->get_event($this->event) && ($calendar = $this->get_default_calendar($invitation['event']['sensitivity']))) {
              $invitation['event']['calendar'] = $calendar['id'];
              if ($driver->new_event($invitation['event']))
                $this->rc->output->command('display_message', $this->gettext(array('name' => 'importedsuccessfully', 'vars' => array('calendar' => $calendar['name']))), 'confirmation');
            }
          }
        }
        
        $this->register_handler('plugin.event_inviteform', array($this, 'itip_event_inviteform'));
        $this->register_handler('plugin.event_invitebox', array($this->ui, 'event_invitebox'));
        
        if (!$this->invitestatus) {
          $this->itip->set_rsvp_actions(array('accepted','tentative','declined'));
          $this->register_handler('plugin.event_rsvp_buttons', array($this->ui, 'event_rsvp_buttons'));
        }
        
        $this->rc->output->set_pagetitle($itip->gettext('itipinvitation') . ' ' . $this->event['title']);
      }
      else
        $this->rc->output->command('display_message', $this->gettext('itipinvalidrequest'), 'error', -1);
      
      $this->rc->output->send('calendar.itipattend');
    }
  }
  
  /**
   *
   */
  public function itip_event_inviteform($attrib)
  {
    $hidden = new html_hiddenfield(array('name' => "_t", 'value' => $this->token));
    return html::tag('form', array('action' => $this->rc->url(array('task' => 'calendar', 'action' => 'attend')), 'method' => 'post', 'noclose' => true) + $attrib) . $hidden->show();
  }

  /**
   * 
   */
  private function mail_agenda_event_row($event, $class = '')
  {
    $time = $event['allday'] ? $this->gettext('all-day') :
      rcmail::get_instance()->format_date($event['start'], $this->rc->config->get('time_format')) . ' - ' .
        rcmail::get_instance()->format_date($event['end'], $this->rc->config->get('time_format'));

    return html::div(rtrim('event-row ' . $class),
      html::span('event-date', $time) .
      html::span('event-title', rcube::Q($event['title']))
    );
  }
  
  /**
   * 
   */
  public function mail_messages_list($p)
  {
    if (in_array('attachment', (array)$p['cols']) && !empty($p['messages'])) {
      foreach ($p['messages'] as $header) {
        $part = new StdClass;
        $part->mimetype = $header->ctype;
        if (libcalendaring::part_is_vcalendar($part)) {
          $header->list_flags['attachmentClass'] = 'ical';
        }
        else if (in_array($header->ctype, array('multipart/alternative', 'multipart/mixed'))) {
          // TODO: fetch bodystructure and search for ical parts. Maybe too expensive?

          if (!empty($header->structure) && is_array($header->structure->parts)) {
            foreach ($header->structure->parts as $part) {
              if (libcalendaring::part_is_vcalendar($part) && !empty($part->ctype_parameters['method'])) {
                $header->list_flags['attachmentClass'] = 'ical';
                break;
              }
            }
          }
        }
      }
    }
  }

  /**
   * Add UI element to copy event invitations or updates to the calendar
   */
  public function mail_messagebody_html($p)
  {
    // load iCalendar functions (if necessary)
    if (!empty($this->lib->ical_parts)) {
      $this->get_ical();
      $this->load_itip();
    }

    $html = '';
    $has_events = false;
    $ical_objects = $this->lib->get_mail_ical_objects();

    // show a box for every event in the file
    foreach ($ical_objects as $idx => $event) {
      if ($event['_type'] != 'event')  // skip non-event objects (#2928)
        continue;

      $has_events = true;

      // get prepared inline UI for this event object
      if ($ical_objects->method) {
        $append = '';

        // prepare a small agenda preview to be filled with actual event data on async request
        if ($ical_objects->method == 'REQUEST') {
          $append = html::div('calendar-agenda-preview',
            html::tag('h3', 'preview-title', $this->gettext('agenda') . ' ' .
              html::span('date', rcmail::get_instance()->format_date($event['start'], $this->rc->config->get('date_format')))
            ) . '%before%' . $this->mail_agenda_event_row($event, 'current') . '%after%');
        }

        $html .= html::div('calendar-invitebox',
          $this->itip->mail_itip_inline_ui(
            $event,
            $ical_objects->method,
            $ical_objects->mime_id . ':' . $idx,
            'calendar',
            rcube_utils::anytodatetime($ical_objects->message_date),
            $this->rc->url(array('task' => 'calendar')) . '&view=agendaDay&date=' . $event['start']->format('U')
          ) . $append
        );
      }

      // limit listing
      if ($idx >= 3)
        break;
    }

    // prepend event boxes to message body
    if ($html) {
      $this->ui->init();
      $p['content'] = $html . $p['content'];
      $this->rc->output->add_label('calendar.savingdata','calendar.deleteventconfirm','calendar.declinedeleteconfirm');
    }

    // add "Save to calendar" button into attachment menu
    if ($has_events) {
      $this->add_button(array(
        'id'         => 'attachmentsavecal',
        'name'       => 'attachmentsavecal',
        'type'       => 'link',
        'wrapper'    => 'li',
        'command'    => 'attachment-save-calendar',
        'class'      => 'icon calendarlink',
        'classact'   => 'icon calendarlink active',
        'innerclass' => 'icon calendar',
        'label'      => 'calendar.savetocalendar',
        ), 'attachmentmenu');
    }

    return $p;
  }


  /**
   * Handler for POST request to import an event attached to a mail message
   */
  public function mail_import_itip()
  {
    $itip_sending = $this->rc->config->get('calendar_itip_send_option', $this->defaults['calendar_itip_send_option']);

    $uid     = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
    $mbox    = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);
    $mime_id = rcube_utils::get_input_value('_part', rcube_utils::INPUT_POST);
    $status  = rcube_utils::get_input_value('_status', rcube_utils::INPUT_POST);
    $delete  = intval(rcube_utils::get_input_value('_del', rcube_utils::INPUT_POST));
    $noreply = intval(rcube_utils::get_input_value('_noreply', rcube_utils::INPUT_POST));
    $noreply = $noreply || $status == 'needs-action' || $itip_sending === 0;
    $instance = rcube_utils::get_input_value('_instance', rcube_utils::INPUT_POST);
    $savemode = rcube_utils::get_input_value('_savemode', rcube_utils::INPUT_POST);

    $error_msg = $this->gettext('errorimportingevent');
    $success = false;
    $delegate = null;

    if ($status == 'delegated') {
      $delegates = rcube_mime::decode_address_list(rcube_utils::get_input_value('_to', rcube_utils::INPUT_POST, true), 1, false);
      $delegate  = reset($delegates);

      if (empty($delegate) || empty($delegate['mailto'])) {
        $this->rc->output->command('display_message', $this->gettext('libcalendaring.delegateinvalidaddress'), 'error');
        return;
      }
    }

    // successfully parsed events?
    if ($event = $this->lib->mail_get_itip_object($mbox, $uid, $mime_id, 'event')) {
      // forward iTip request to delegatee
      if ($delegate) {
        $rsvpme = intval(rcube_utils::get_input_value('_rsvp', rcube_utils::INPUT_POST));

        $itip = $this->load_itip();
        if ($itip->delegate_to($event, $delegate, $rsvpme ? true : false)) {
          $this->rc->output->show_message('calendar.itipsendsuccess', 'confirmation');
        }
        else {
          $this->rc->output->command('display_message', $this->gettext('itipresponseerror'), 'error');
        }

        // the delegator is set to non-participant, thus save as non-blocking
        $event['free_busy'] = 'free';
      }

      // find writeable calendar to store event
      $cal_id = !empty($_REQUEST['_folder']) ? rcube_utils::get_input_value('_folder', rcube_utils::INPUT_POST) : null;

      $calendar = null;
      $driver = null;

      if($cal_id) {
        $driver = $this->get_driver_by_cal($cal_id);
        $calendars = $driver->list_calendars(false, true);
        $calendar = $calendars[$cal_id];
      }

      $dontsave = ($_REQUEST['_folder'] === '' && $event['_method'] == 'REQUEST');

      // select default calendar except user explicitly selected 'none'
      if (!$calendar && !$dontsave)
        $calendar = $this->get_default_calendar(true, $event['sensitivity'] == 'confidential');

      if(!$driver) {
        $driver = $this->get_driver_by_cal($calendar["id"]);
      }

      // select default calendar except user explicitly selected 'none'
      if (!$calendar && !$dontsave)
         $calendar = $this->get_default_calendar($event['sensitivity']);

      $metadata = array(
        'uid' => $event['uid'],
        '_instance' => $event['_instance'],
        'changed' => is_object($event['changed']) ? $event['changed']->format('U') : 0,
        'sequence' => intval($event['sequence']),
        'fallback' => strtoupper($status),
        'method' => $event['_method'],
        'task' => 'calendar',
      );

      // update my attendee status according to submitted method
      if (!empty($status)) {
        $organizer = null;
        $emails = $this->get_user_emails();
        foreach ($event['attendees'] as $i => $attendee) {
          if ($attendee['role'] == 'ORGANIZER') {
            $organizer = $attendee;
          }
          else if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
            $event['attendees'][$i]['status'] = strtoupper($status);
            if (!in_array($event['attendees'][$i]['status'], array('NEEDS-ACTION','DELEGATED')))
              $event['attendees'][$i]['rsvp'] = false;  // unset RSVP attribute

            $metadata['attendee'] = $attendee['email'];
            $metadata['rsvp'] = $attendee['role'] != 'NON-PARTICIPANT';
            $reply_sender = $attendee['email'];
            $event_attendee = $attendee;
          }
        }

        // add attendee with this user's default identity if not listed
        if (!$reply_sender) {
          $sender_identity = $this->rc->user->list_emails(true);
          $event['attendees'][] = array(
            'name' => $sender_identity['name'],
            'email' => $sender_identity['email'],
            'role' => 'OPT-PARTICIPANT',
            'status' => strtoupper($status),
          );
          $metadata['attendee'] = $sender_identity['email'];
        }
      }
      
      // save to calendar
      if ($calendar && $calendar['editable']) {
        // check for existing event with the same UID
        $existing = $driver->get_event($event, calendar_driver::FILTER_WRITEABLE | calendar_driver::FILTER_PERSONAL);

        if ($existing) {
          // forward savemode for correct updates of recurring events
          $existing['_savemode'] = $savemode ?: $event['_savemode'];

          // only update attendee status
          if ($event['_method'] == 'REPLY') {
            // try to identify the attendee using the email sender address
            $existing_attendee = -1;
            $existing_attendee_emails = array();
            foreach ($existing['attendees'] as $i => $attendee) {
              $existing_attendee_emails[] = $attendee['email'];
              if ($event['_sender'] && ($attendee['email'] == $event['_sender'] || $attendee['email'] == $event['_sender_utf'])) {
                $existing_attendee = $i;
              }
            }
            $event_attendee = null;
            $update_attendees = array();
            foreach ($event['attendees'] as $attendee) {
              if ($event['_sender'] && ($attendee['email'] == $event['_sender'] || $attendee['email'] == $event['_sender_utf'])) {
                $event_attendee = $attendee;
                $update_attendees[] = $attendee;
                $metadata['fallback'] = $attendee['status'];
                $metadata['attendee'] = $attendee['email'];
                $metadata['rsvp'] = $attendee['rsvp'] || $attendee['role'] != 'NON-PARTICIPANT';
                if ($attendee['status'] != 'DELEGATED') {
                  break;
                }
              }
              // also copy delegate attendee
              else if (!empty($attendee['delegated-from']) &&
                       (stripos($attendee['delegated-from'], $event['_sender']) !== false ||
                        stripos($attendee['delegated-from'], $event['_sender_utf']) !== false)) {
                $update_attendees[] = $attendee;
                if (!in_array($attendee['email'], $existing_attendee_emails)) {
                  $existing['attendees'][] = $attendee;
                }
              }
            }

            // if delegatee has declined, set delegator's RSVP=True
            if ($event_attendee && $event_attendee['status'] == 'DECLINED' && $event_attendee['delegated-from']) {
              foreach ($existing['attendees'] as $i => $attendee) {
                if ($attendee['email'] == $event_attendee['delegated-from']) {
                  $existing['attendees'][$i]['rsvp'] = true;
                  break;
                }
              }
            }

            // found matching attendee entry in both existing and new events
            if ($existing_attendee >= 0 && $event_attendee) {
              $existing['attendees'][$existing_attendee] = $event_attendee;
              $success = $driver->update_attendees($existing, $update_attendees);
            }
            // update the entire attendees block
            else if (($event['sequence'] >= $existing['sequence'] || $event['changed'] >= $existing['changed']) && $event_attendee) {
              $existing['attendees'][] = $event_attendee;
              $success = $driver->update_attendees($existing, $update_attendees);
            }
            else {
              $error_msg = $this->gettext('newerversionexists');
            }
          }
          // delete the event when declined (#1670)
          else if ($status == 'declined' && $delete) {
             $deleted = $driver->remove_event($existing, true);
             $success = true;
          }
          // import the (newer) event
          else if ($event['sequence'] >= $existing['sequence'] || $event['changed'] >= $existing['changed']) {
            $event['id'] = $existing['id'];
            $event['calendar'] = $existing['calendar'];

            // preserve my participant status for regular updates
            if (empty($status)) {
              $emails = $this->get_user_emails();
              foreach ($event['attendees'] as $i => $attendee) {
                if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
                  foreach ($existing['attendees'] as $j => $_attendee) {
                    if ($attendee['email'] == $_attendee['email']) {
                      $event['attendees'][$i] = $existing['attendees'][$j];
                      break;
                    }
                  }
                }
              }
            }

            // set status=CANCELLED on CANCEL messages
            if ($event['_method'] == 'CANCEL')
              $event['status'] = 'CANCELLED';
            // show me as free when declined (#1670)
            if ($status == 'declined' || $event['status'] == 'CANCELLED' || $event_attendee['role'] == 'NON-PARTICIPANT')
              $event['free_busy'] = 'free';

            $success = $driver->edit_event($event);
          }
          else if (!empty($status)) {
            $existing['attendees'] = $event['attendees'];
            if ($status == 'declined' || $event_attendee['role'] == 'NON-PARTICIPANT')  // show me as free when declined (#1670)
              $existing['free_busy'] = 'free';
            $success = $driver->edit_event($existing);
          }
          else
            $error_msg = $this->gettext('newerversionexists');
        }
        else if (!$existing && ($status != 'declined' || $this->rc->config->get('kolab_invitation_calendars'))) {
          if ($status == 'declined' || $event['status'] == 'CANCELLED' || $event_attendee['role'] == 'NON-PARTICIPANT') {
            $event['free_busy'] = 'free';
          }

          // if the RSVP reply only refers to a single instance:
          // store unmodified master event with current instance as exception
          if (!empty($instance) && !empty($savemode) && $savemode != 'all') {
            $master = $this->lib->mail_get_itip_object('event');
            if ($master['recurrence'] && !$master['_instance']) {
              // compute recurring events until this instance's date
              if ($recurrence_date = rcube_utils::anytodatetime($instance, $master['start']->getTimezone())) {
                $recurrence_date->setTime(23,59,59);

                foreach ($driver->get_recurring_events($master, $master['start'], $recurrence_date) as $recurring) {
                  if ($recurring['_instance'] == $instance) {
                    // copy attendees block with my partstat to exception
                    $recurring['attendees'] = $event['attendees'];
                    $master['recurrence']['EXCEPTIONS'][] = $recurring;
                    $event = $recurring;  // set reference for iTip reply
                    break;
                  }
                }

                $master['calendar'] = $event['calendar'] = $calendar['id'];
                $success = $driver->new_event($master);
              }
              else {
                $master = null;
              }
            }
            else {
              $master = null;
            }
          }

          // save to the selected/default calendar
          if (!$master) {
            $event['calendar'] = $calendar['id'];
            $success = $driver->new_event($event);
          }
        }
        else if ($status == 'declined')
          $error_msg = null;
      }
      else if ($status == 'declined' || $dontsave)
        $error_msg = null;
      else
        $error_msg = $this->gettext('nowritecalendarfound');
    }

    if ($success) {
      $message = $event['_method'] == 'REPLY' ? 'attendeupdateesuccess' : ($deleted ? 'successremoval' : ($existing ? 'updatedsuccessfully' : 'importedsuccessfully'));
      $this->rc->output->command('display_message', $this->gettext(array('name' => $message, 'vars' => array('calendar' => $calendar['name']))), 'confirmation');
    }

    if ($success || $dontsave) {
      $metadata['calendar'] = $event['calendar'];
      $metadata['nosave'] = $dontsave;
      $metadata['rsvp'] = intval($metadata['rsvp']);
      $metadata['after_action'] = $this->rc->config->get('calendar_itip_after_action', $this->defaults['calendar_itip_after_action']);
      $this->rc->output->command('plugin.itip_message_processed', $metadata);
      $error_msg = null;
    }
    else if ($error_msg) {
      $this->rc->output->command('display_message', $error_msg, 'error');
    }

    // send iTip reply
    if ($event['_method'] == 'REQUEST' && $organizer && !$noreply && !in_array(strtolower($organizer['email']), $emails) && !$error_msg) {
      $event['comment'] = rcube_utils::get_input_value('_comment', rcube_utils::INPUT_POST);
      $itip = $this->load_itip();
      $itip->set_sender_email($reply_sender);
      if ($itip->send_itip_message($event, 'REPLY', $organizer, 'itipsubject' . $status, 'itipmailbody' . $status))
        $this->rc->output->command('display_message', $this->gettext(array('name' => 'sentresponseto', 'vars' => array('mailto' => $organizer['name'] ? $organizer['name'] : $organizer['email']))), 'confirmation');
      else
        $this->rc->output->command('display_message', $this->gettext('itipresponseerror'), 'error');
    }

    $this->rc->output->send();
  }


  /**
   * Handler for calendar/itip-remove requests
   */
  function mail_itip_decline_reply()
  {
    $uid     = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
    $mbox    = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);
    $mime_id = rcube_utils::get_input_value('_part', rcube_utils::INPUT_POST);

    if (($event = $this->lib->mail_get_itip_object($mbox, $uid, $mime_id, 'event')) && $event['_method'] == 'REPLY') {
      $event['comment'] = rcube_utils::get_input_value('_comment', rcube_utils::INPUT_POST);

      foreach ($event['attendees'] as $_attendee) {
        if ($_attendee['role'] != 'ORGANIZER') {
          $attendee = $_attendee;
          break;
        }
      }

      $itip = $this->load_itip();
      if ($itip->send_itip_message($event, 'CANCEL', $attendee, 'itipsubjectcancel', 'itipmailbodycancel'))
        $this->rc->output->command('display_message', $this->gettext(array('name' => 'sentresponseto', 'vars' => array('mailto' => $attendee['name'] ? $attendee['name'] : $attendee['email']))), 'confirmation');
      else
        $this->rc->output->command('display_message', $this->gettext('itipresponseerror'), 'error');
    }
    else {
      $this->rc->output->command('display_message', $this->gettext('itipresponseerror'), 'error');
    }
  }

  /**
   * Handler for calendar/itip-delegate requests
   */
  function mail_itip_delegate()
  {
    // forward request to mail_import_itip() with the right status
    $_POST['_status'] = $_REQUEST['_status'] = 'delegated';
    $this->mail_import_itip();
  }

  /**
   * Import the full payload from a mail message attachment
   */
  public function mail_import_attachment()
  {
    $uid     = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
    $mbox    = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);
    $mime_id = rcube_utils::get_input_value('_part', rcube_utils::INPUT_POST);
    if (defined(RCUBE_CHARSET)) {
        $charset = RCUBE_CHARSET;
    } elseif (defined(RCUBE_CHARSET)) {
        $charset = RCUBE_CHARSET;
    } else {
        $charset = $this->rc->config->get('default_charset');
    }

    // establish imap connection
    $imap = $this->rc->get_storage();
    $imap->set_folder($mbox);

    if ($uid && $mime_id) {
      $part = $imap->get_message_part($uid, $mime_id);
      if ($part->ctype_parameters['charset'])
        $charset = $part->ctype_parameters['charset'];
//      $headers = $imap->get_message_headers($uid);

      if ($part) {
        $events = $this->get_ical()->import($part, $charset);
      }
    }

    $success = $existing = 0;
    if (!empty($events)) {
      // find writeable calendar to store event
      $cal_id = !empty($_REQUEST['_calendar']) ? rcube_utils::get_input_value('_calendar', rcube_utils::INPUT_POST) : null;
      $driver = null;
      if($cal_id) $driver = $this->get_driver_by_cal($cal_id);
      else $driver = $this->get_driver_by_gpc();
      $calendars = $driver->list_calendars(calendar_driver::FILTER_PERSONAL);

      foreach ($events as $event) {
        // save to calendar
        $calendar = $calendars[$cal_id] ?: $this->get_default_calendar($event['sensitivity']);
        if ($calendar && $calendar['editable'] && $event['_type'] == 'event') {
          $event['calendar'] = $calendar['id'];

          if (!$driver->get_event($event['uid'], calendar_driver::FILTER_WRITEABLE)) {
            $success += (bool)$driver->new_event($event);
          }
          else {
            $existing++;
          }
        }
      }
    }

    if ($success) {
      $this->rc->output->command('display_message', $this->gettext(array(
        'name' => 'importsuccess',
        'vars' => array('nr' => $success),
      )), 'confirmation');
    }
    else if ($existing) {
      $this->rc->output->command('display_message', $this->gettext('importwarningexists'), 'warning');
    }
    else {
      $this->rc->output->command('display_message', $this->gettext('errorimportingevent'), 'error');
    }
  }

  /**
   * Read email message and return contents for a new event based on that message
   */
  public function mail_message2event()
  {
    $uid   = rcube_utils::get_input_value('_uid', rcube_utils::INPUT_POST);
    $mbox  = rcube_utils::get_input_value('_mbox', rcube_utils::INPUT_POST);
    $event = array();

    // establish imap connection
    $imap = $this->rc->get_storage();
    $imap->set_folder($mbox);
    $message = new rcube_message($uid);

    if ($message->headers) {
      $event['title'] = trim($message->subject);
      $event['description'] = trim($message->first_text_part());

      $driver = $this->get_default_driver();

      // add a reference to the email message
      if ($msgref = $driver->get_message_reference($message->headers, $mbox)) {
        $event['links'] = array($msgref);
      }
      // copy mail attachments to event
      else if ($message->attachments) {
        $eventid = 'cal-';
        if (!is_array($_SESSION[self::SESSION_KEY]) || $_SESSION[self::SESSION_KEY]['id'] != $eventid) {
          $_SESSION[self::SESSION_KEY] = array();
          $_SESSION[self::SESSION_KEY]['id'] = $eventid;
          $_SESSION[self::SESSION_KEY]['attachments'] = array();
        }

        foreach ((array)$message->attachments as $part) {
          $attachment = array(
            'data' => $imap->get_message_part($uid, $part->mime_id, $part),
            'size' => $part->size,
            'name' => $part->filename,
            'mimetype' => $part->mimetype,
            'group' => $eventid,
          );

          $attachment = $this->rc->plugins->exec_hook('attachment_save', $attachment);

          if ($attachment['status'] && !$attachment['abort']) {
            $id = $attachment['id'];
            $attachment['classname'] = rcube_utils::file2class($attachment['mimetype'], $attachment['name']);

            // store new attachment in session
            unset($attachment['status'], $attachment['abort'], $attachment['data']);
            $_SESSION[self::SESSION_KEY]['attachments'][$id] = $attachment;

            $attachment['id'] = 'rcmfile' . $attachment['id'];  // add prefix to consider it 'new'
            $event['attachments'][] = $attachment;
          }
        }
      }
      
      $this->rc->output->command('plugin.mail2event_dialog', $event);
    }
    else {
      $this->rc->output->command('display_message', $this->gettext('messageopenerror'), 'error');
    }
    
    $this->rc->output->send();
  }

  /**
   * Handler for the 'message_compose' plugin hook. This will check for
   * a compose parameter 'calendar_event' and create an attachment with the
   * referenced event in iCal format
   */
  public function mail_message_compose($args)
  {
    // set the submitted event ID as attachment
    if (!empty($args['param']['calendar_event'])) {
      list($cal, $id) = explode(':', $args['param']['calendar_event'], 2);
      $driver = $this->get_driver_by_cal($cal);
      if ($event = $driver->get_event(array('id' => $id, 'calendar' => $cal))) {
        $filename = asciiwords($event['title']);
        if (empty($filename))
          $filename = 'event';

        // save ics to a temp file and register as attachment
        $tmp_path = tempnam($this->rc->config->get('temp_dir'), 'rcmAttmntCal');
        file_put_contents($tmp_path, $this->get_ical()->export(array($event), '', false, array($driver, 'get_attachment_body')));

        $args['attachments'][] = array('path' => $tmp_path, 'name' => $filename . '.ics', 'mimetype' => 'text/calendar');
        $args['param']['subject'] = $event['title'];
		// PAMELA MANTIS 3909: le message reste vide quand on le crée depuis un événement
        $args['param']['body'] = $event['description'];
      }
    }

    return $args;
  }


  /**
   * Get a list of email addresses of the current user (from login and identities)
   */
  public function get_user_emails()
  {
    return $this->lib->get_user_emails();
  }


  /**
   * Build an absolute URL with the given parameters
   */
  public function get_url($param = array())
  {
    // PAMELA - Nouvelle URL
     $url = $_SERVER["REQUEST_URI"];
     $delm = '?';
 
     foreach ($param as $key => $val) {
       if ($val !== '' && $val !== null) {
         $par  = $key;
         $url .= $delm.urlencode($par).'='.urlencode($val);
         $delm = '&';
       }
     }
 
     return rcube_utils::resolve_url($url);
   }
 
   /**
    * PAMELA - Build an absolute URL with the given parameters
    */
   public function get_freebusy_url($param = array())
   {
     // PAMELA - Nouvelle URL
     $url = $_SERVER["REQUEST_URI"];
     $delm = '?';
 
     foreach ($param as $key => $val) {
       if ($val !== '' && $val !== null) {
         $par  = $key;
         $url .= $delm.urlencode($par).'='.urlencode($val);
         $delm = '&';
       }
     }
 
     return rcube_utils::resolve_url($url);
  }


  public function ical_feed_hash($source)
  {
    return base64_encode($this->rc->user->get_username() . ':' . $source);
  }

  /**
   * Handler for user_delete plugin hook
   */
  public function user_delete($args)
  {
    // delete itipinvitations entries related to this user
    $db = $this->rc->get_dbh();
    $table_itipinvitations = $db->table_name('itipinvitations', true);
    $db->query("DELETE FROM $table_itipinvitations WHERE `user_id` = ?", $args['user']->ID);

    foreach($this->get_drivers() as $driver)
      if(!$driver->user_delete($args))
        return false;

     return true;
  }

  /**
   * Magic getter for public access to protected members
   */
  public function __get($name)
  {
    switch ($name) {
      case 'ical':
        return $this->get_ical();

      case 'itip':
        return $this->load_itip();

      case 'driver':
        $driver = $this->get_driver_by_gpc(true);
        if(!$driver) $driver = $this->get_default_driver();
        return $driver;
    }

    return null;
  }

}
