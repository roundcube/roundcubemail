<?php
/**
 * User Interface class for the Calendar plugin
 *
 * @author Lazlo Westerhof <hello@lazlo.me>
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2010, Lazlo Westerhof <hello@lazlo.me>
 * Copyright (C) 2014, Kolab Systems AG <contact@kolabsys.com>
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


class calendar_ui
{
  private $rc;
  private $cal;
  private $ready = false;
  public $screen;

  function __construct($cal)
  {
    $this->cal = $cal;
    $this->rc = $cal->rc;
    $this->screen = $this->rc->task == 'calendar' ? ($this->rc->action ? $this->rc->action: 'calendar') : 'other';
  }

  /**
   * Calendar UI initialization and requests handlers
   */
  public function init()
  {
    if ($this->ready)  // already done
      return;

    // add taskbar button
    $this->cal->add_button(array(
      'command'    => 'calendar',
      'class'      => 'button-calendar',
      'classsel'   => 'button-calendar button-selected',
      'innerclass' => 'button-inner',
      'label'      => 'calendar.calendar',
      'type'       => 'link'
      ), 'taskbar');
    
    // load basic client script
    $this->cal->include_script('calendar_base.js');
    
    $skin_path = $this->cal->local_skin_path();
    $this->cal->include_stylesheet($skin_path . '/calendar.css');
    
    $this->ready = true;
  }

  /**
   * Register handler methods for the template engine
   */
  public function init_templates()
  {
    $this->cal->register_handler('plugin.calendar_css', array($this, 'calendar_css'));
    $this->cal->register_handler('plugin.calendar_list', array($this, 'calendar_list'));
    $this->cal->register_handler('plugin.calendar_select', array($this, 'calendar_select'));
    $this->cal->register_handler('plugin.identity_select', array($this, 'identity_select'));
    $this->cal->register_handler('plugin.category_select', array($this, 'category_select'));
    $this->cal->register_handler('plugin.status_select', array($this, 'status_select'));
    $this->cal->register_handler('plugin.freebusy_select', array($this, 'freebusy_select'));
    $this->cal->register_handler('plugin.priority_select', array($this, 'priority_select'));
    $this->cal->register_handler('plugin.sensitivity_select', array($this, 'sensitivity_select'));
    $this->cal->register_handler('plugin.alarm_select', array($this, 'alarm_select'));
    $this->cal->register_handler('plugin.recurrence_form', array($this->cal->lib, 'recurrence_form'));
    $this->cal->register_handler('plugin.attachments_form', array($this, 'attachments_form'));
    $this->cal->register_handler('plugin.attachments_list', array($this, 'attachments_list'));
    $this->cal->register_handler('plugin.filedroparea', array($this, 'file_drop_area'));
    $this->cal->register_handler('plugin.attendees_list', array($this, 'attendees_list'));
    $this->cal->register_handler('plugin.attendees_form', array($this, 'attendees_form'));
    $this->cal->register_handler('plugin.resources_form', array($this, 'resources_form'));
    $this->cal->register_handler('plugin.resources_list', array($this, 'resources_list'));
    $this->cal->register_handler('plugin.resources_searchform', array($this, 'resources_search_form'));
    $this->cal->register_handler('plugin.resource_info', array($this, 'resource_info'));
    $this->cal->register_handler('plugin.resource_calendar', array($this, 'resource_calendar'));
    $this->cal->register_handler('plugin.attendees_freebusy_table', array($this, 'attendees_freebusy_table'));
    $this->cal->register_handler('plugin.edit_attendees_notify', array($this, 'edit_attendees_notify'));
    $this->cal->register_handler('plugin.edit_recurring_warning', array($this, 'recurring_event_warning'));
    $this->cal->register_handler('plugin.event_rsvp_buttons', array($this, 'event_rsvp_buttons'));
    $this->cal->register_handler('plugin.angenda_options', array($this, 'angenda_options'));
    $this->cal->register_handler('plugin.events_import_form', array($this, 'events_import_form'));
    $this->cal->register_handler('plugin.events_export_form', array($this, 'events_export_form'));
    $this->cal->register_handler('plugin.object_changelog_table', array('libkolab', 'object_changelog_table'));
    $this->cal->register_handler('plugin.searchform', array($this->rc->output, 'search_form'));  // use generic method from rcube_template
    $this->cal->register_handler('plugin.calendar_create_menu', array($this, 'calendar_create_menu'));
  }

  /**
   * Adds CSS stylesheets to the page header
   */
  public function addCSS()
  {
    $skin_path = $this->cal->local_skin_path();
    $this->cal->include_stylesheet($skin_path . '/fullcalendar.css');
  }

  /**
   * Adds JS files to the page header
   */
  public function addJS()
  {
    $this->cal->include_script('calendar_ui.js');
    $this->cal->include_script('lib/js/fullcalendar.js');
    $this->rc->output->include_script('treelist.js');

    // include kolab folderlist widget if available
    if (in_array('libkolab', $this->cal->api->loaded_plugins())) {
        $this->cal->api->include_script('libkolab/js/folderlist.js');
        $this->cal->api->include_script('libkolab/js/audittrail.js');
    }

    jqueryui::miniColors();
  }

  /**
   *
   */
  function calendar_css($attrib = array())
  {
    $mode = $this->rc->config->get('calendar_event_coloring', $this->cal->defaults['calendar_event_coloring']);
    $css = "\n";

    foreach ($this->cal->get_drivers() as $name => $driver) {
      $categories = $driver->list_categories();

      foreach ((array)$categories as $class => $color) {
        if (empty($color))
          continue;

        $class = 'cat-' . asciiwords(strtolower($class), true);
        $css .= ".$class { color: #$color }\n";
        if ($mode > 0) {
          if ($mode == 2) {
            $css .= ".fc-event-$class .fc-event-bg {";
            $css .= " opacity: 0.9;";
            $css .= " filter: alpha(opacity=90);";
          } else {
            $css .= ".fc-event-$class.fc-event-skin, ";
            $css .= ".fc-event-$class .fc-event-skin, ";
            $css .= ".fc-event-$class .fc-event-inner {";
          }
          $css .= " background-color: #" . $color . ";";
          if ($mode % 2)
            $css .= " border-color: #$color;";
          $css .= "}\n";
        }
      }

      $calendars = $driver->list_calendars();
      foreach ((array)$calendars as $id => $prop) {
        if (!$prop['color'])
          continue;
        $css .= $this->calendar_css_classes($id, $prop, $mode);
      }
    }
  
    return html::tag('style', array('type' => 'text/css'), $css);
  }

  /**
   *
   */
  public function calendar_css_classes($id, $prop, $mode)
  {
    $color = $prop['color'];
    $class = 'cal-' . asciiwords($id, true);
    $css .= "li .$class, #eventshow .$class { color: #$color }\n";

    if ($mode != 1) {
      if ($mode == 3) {
        $css .= ".fc-event-$class .fc-event-bg {";
        $css .= " opacity: 0.9;";
        $css .= " filter: alpha(opacity=90);";
      }
      else {
        $css .= ".fc-event-$class, ";
        $css .= ".fc-event-$class .fc-event-inner {";
      }
      if (!$attrib['printmode']) // FIXME
        $css .= " background-color: #$color;";
      if ($mode % 2 == 0)
      $css .= " border-color: #$color;";
      $css .= "}\n";
    }

    return $css . ".$class .handle { background-color: #$color; }\n";
  }

  /**
   *
   */
  function calendar_list($attrib = array())
  {
    $html = '';
    $jsenv = array();
    $tree = true;
    // TODO: Check whether get_calendars() exists. Original $calendars = $this->cal->driver->list_calendars(0, $tree);
    $calendars = $this->cal->get_calendars(false, false, $tree);

    // walk folder tree
    if (is_object($tree)) {
      $html = $this->list_tree_html($tree, $calendars, $jsenv, $attrib);

      // append birthdays calendar which isn't part of $tree
      if ($bdaycal = $calendars[calendar_driver::BIRTHDAY_CALENDAR_ID]) {
        $calendars = array(calendar_driver::BIRTHDAY_CALENDAR_ID => $bdaycal);
      }
      else {
        $calendars = array();  // clear array for flat listing
      }
    }
    else {
      // fall-back to flat folder listing
      $attrib['class'] .= ' flat';
    }

    foreach ((array)$calendars as $id => $prop) {
      if ($attrib['activeonly'] && !$prop['active'])
        continue;

      $html .= html::tag('li', array('id' => 'rcmlical' . $id, 'class' => $prop['group']),
        $content = $this->calendar_list_item($id, $prop, $jsenv, $attrib['activeonly'])
      );
    }

    $this->rc->output->set_env('calendars', $jsenv);
    $this->rc->output->add_gui_object('calendarslist', $attrib['id']);

    return html::tag('ul', $attrib, $html, html::$common_attrib);
  }

  /**
   * Return html for a structured list <ul> for the folder tree
   */
  public function list_tree_html($node, $data, &$jsenv, $attrib)
  {
    $out = '';
    foreach ($node->children as $folder) {
      $id = $folder->id;
      $prop = $data[$id];
      $is_collapsed = false; // TODO: determine this somehow?

      $content = $this->calendar_list_item($id, $prop, $jsenv, $attrib['activeonly']);

      if (!empty($folder->children)) {
        $content .= html::tag('ul', array('style' => ($is_collapsed ? "display:none;" : null)),
          $this->list_tree_html($folder, $data, $jsenv, $attrib));
      }

      if (strlen($content)) {
        $out .= html::tag('li', array(
          'id' => 'rcmlical' . rcube_utils::html_identifier($id),
          'class' => $prop['group'] . ($prop['virtual'] ? ' virtual' : ''),
        ),
          $content);
      }
    }

    return $out;
  }

  /**
   * Helper method to build a calendar list item (HTML content and js data)
   */
  public function calendar_list_item($id, $prop, &$jsenv, $activeonly = false)
  {
    // enrich calendar properties with settings from the driver
    if (!$prop['virtual']) {
      unset($prop['user_id']);
      $prop['alarms']      = $this->cal->driver->alarms;
      $prop['attendees']   = $this->cal->driver->attendees;
      $prop['freebusy']    = $this->cal->driver->freebusy;
      $prop['attachments'] = $this->cal->driver->attachments;
      $prop['undelete']    = $this->cal->driver->undelete;
      $prop['feedurl']     = $this->cal->get_url(array('_cal' => $this->cal->ical_feed_hash($id) . '.ics', 'action' => 'feed'));

      $jsenv[$id] = $prop;
    }

    $classes = array('calendar', 'cal-'  . asciiwords($id, true));
    if (defined(RCUBE_CHARSET)) {
        $charset = RCUBE_CHARSET;
    } elseif (defined(RCUBE_CHARSET)) {
        $charset = RCUBE_CHARSET;
    } else {
        $charset = $this->rc->config->get('default_charset');
    }

    $title = $prop['title'] ?: ($prop['name'] != $prop['listname'] || strlen($prop['name']) > 25 ?
      html_entity_decode($prop['name'], ENT_COMPAT, $charset) : '');

    if ($prop['virtual'])
      $classes[] = 'virtual';
    else if (!$prop['editable'])
      $classes[] = 'readonly';
    if ($prop['subscribed'])
      $classes[] = 'subscribed';
    if ($prop['subscribed'] === 2)
      $classes[] = 'partial';
    if ($prop['class'])
      $classes[] = $prop['class'];

    $content = '';
    if (!$activeonly || $prop['active']) {
      $label_id = 'cl:' . $id;
      $content = html::div(join(' ', $classes),
        html::span(array('class' => 'calname', 'id' => $label_id, 'title' => $title), $prop['editname'] ? rcube::Q($prop['editname']) : $prop['listname']) .
        ($prop['virtual'] ? '' :
          html::tag('input', array('type' => 'checkbox', 'name' => '_cal[]', 'value' => $id, 'checked' => $prop['active'], 'aria-labelledby' => $label_id), '') .
          html::span('actions',
            ($prop['removable'] ? html::a(array('href' => '#', 'class' => 'remove', 'title' => $this->cal->gettext('removelist')), ' ') : '') .
            html::a(array('href' => '#', 'class' => 'quickview', 'title' => $this->cal->gettext('quickview'), 'role' => 'checkbox', 'aria-checked' => 'false'), '') .
            (isset($prop['subscribed']) ? html::a(array('href' => '#', 'class' => 'subscribed', 'title' => $this->cal->gettext('calendarsubscribe'), 'role' => 'checkbox', 'aria-checked' => $prop['subscribed'] ? 'true' : 'false'), ' ') : '')
          ) .
          html::span(array('class' => 'handle', 'style' => "background-color: #" . ($prop['color'] ?: 'f00')), '&nbsp;')
        )
      );
    }

    return $content;
  }  

  /**
   *
   */
  function angenda_options($attrib = array())
  {
    $attrib += array('id' => 'agendaoptions');
    $attrib['style'] .= 'display:none';
    
    $select_range = new html_select(array('name' => 'listrange', 'id' => 'agenda-listrange'));
    $select_range->add(1 . ' ' . preg_replace('/\(.+\)/', '', $this->cal->lib->gettext('days')), $days); // FIXME
    foreach (array(2,5,7,14,30,60,90,180,365) as $days)
      $select_range->add($days . ' ' . preg_replace('/\(|\)/', '', $this->cal->lib->gettext('days')), $days);
    
    $html = html::label('agenda-listrange', $this->cal->gettext('listrange'));
    $html .= $select_range->show($this->rc->config->get('calendar_agenda_range', $this->cal->defaults['calendar_agenda_range']));
    
    $select_sections = new html_select(array('name' => 'listsections', 'id' => 'agenda-listsections'));
    $select_sections->add('---', '');
    foreach (array('day' => 'libcalendaring.days', 'week' => 'libcalendaring.weeks', 'month' => 'libcalendaring.months', 'smart' => 'calendar.smartsections') as $val => $label)
      $select_sections->add(preg_replace('/\(|\)/', '', ucfirst($this->rc->gettext($label))), $val);
    
    $html .= html::span('spacer', '&nbsp;');
    $html .= html::label('agenda-listsections', $this->cal->gettext('listsections'));
    $html .= $select_sections->show($this->rc->config->get('calendar_agenda_sections', $this->cal->defaults['calendar_agenda_sections']));
    
    return html::div($attrib, $html);
  }

  /**
   * Render a HTML select box for calendar selection
   */
  function calendar_select($attrib = array())
  {
    $attrib['name']       = 'calendar';
    $attrib['is_escaped'] = true;
    $select = new html_select($attrib);

    foreach ((array)$this->cal->get_calendars() as $id => $prop) {
      if ($prop['editable'] || strpos($prop['rights'], 'i') !== false)
        $select->add($prop['name'], $id);
    }

    return $select->show(null);
  }

  /**
   * Render a HTML select box for user identity selection
   */
  function identity_select($attrib = array())
  {
    $attrib['name'] = 'identity';
    $select         = new html_select($attrib);
    $identities     = $this->rc->user->list_emails();

    foreach ($identities as $ident) {
        $select->add(format_email_recipient($ident['email'], $ident['name']), $ident['identity_id']);
    }

    return $select->show(null);
  }

  /**
   * Render a HTML select box to select an event category
   */
  function category_select($attrib = array())
  {
    $attrib['name'] = 'categories';
    $select = new html_select($attrib);
    $select->add('---', '');
    $keys = array();
    foreach ($this->cal->get_drivers() as $driver) {
      foreach((array)$driver->list_categories() as $key => $color) {
        if ($color && !in_array($key, $keys)) {
          $select->add($key, $key);
          array_push($keys, $key);
        }
      }
    }

    return $select->show(null);
  }

  /**
   * Render a HTML select box for status property
   */
  function status_select($attrib = array())
  {
    $attrib['name'] = 'status';
    $select = new html_select($attrib);
    $select->add('---', '');
    $select->add($this->cal->gettext('status-confirmed'), 'CONFIRMED');
    $select->add($this->cal->gettext('status-cancelled'), 'CANCELLED');
    //$select->add($this->cal->gettext('tentative'), 'TENTATIVE');
    return $select->show(null);
  }

  /**
   * Render a HTML select box for free/busy/out-of-office property
   */
  function freebusy_select($attrib = array())
  {
    $attrib['name'] = 'freebusy';
    $select = new html_select($attrib);
    $select->add($this->cal->gettext('free'), 'free');
    $select->add($this->cal->gettext('busy'), 'busy');
    // out-of-office is not supported by libkolabxml (#3220)
    // $select->add($this->cal->gettext('outofoffice'), 'outofoffice');
    $select->add($this->cal->gettext('tentative'), 'tentative');
    return $select->show(null);
  }

  /**
   * Render a HTML select for event priorities
   */
  function priority_select($attrib = array())
  {
    $attrib['name'] = 'priority';
    $select = new html_select($attrib);
    $select->add('---', '0');
    $select->add('1 '.$this->cal->gettext('highest'), '1');
    $select->add('2 '.$this->cal->gettext('high'),    '2');
    $select->add('3 ',                                '3');
    $select->add('4 ',                                '4');
    $select->add('5 '.$this->cal->gettext('normal'),  '5');
    $select->add('6 ',                                '6');
    $select->add('7 ',                                '7');
    $select->add('8 '.$this->cal->gettext('low'),     '8');
    $select->add('9 '.$this->cal->gettext('lowest'),  '9');
    return $select->show(null);
  }
  
  /**
   * Render HTML input for sensitivity selection
   */
  function sensitivity_select($attrib = array())
  {
    $attrib['name'] = 'sensitivity';
    $select = new html_select($attrib);
    $select->add($this->cal->gettext('public'), 'public');
    $select->add($this->cal->gettext('private'), 'private');
    $select->add($this->cal->gettext('confidential'), 'confidential');
    return $select->show(null);
  }
  
  /**
   * Render HTML form for alarm configuration
   */
  function alarm_select($attrib = array())
  {
    // Try GPC
    $driver = $this->cal->get_driver_by_gpc(true /* quiet */);

    // We assume that each calendar has equal alarm types, so fallback to default calendar is ok.
    if(!$driver) $driver = $this->cal->get_default_driver();

    return $this->cal->lib->alarm_select($attrib, $driver->alarm_types, $driver->alarm_absolute);
  }

  /**
   *
   */
  function edit_attendees_notify($attrib = array())
  {
    $checkbox = new html_checkbox(array('name' => '_notify', 'id' => 'edit-attendees-donotify', 'value' => 1));
    return html::div($attrib, html::label(null, $checkbox->show(1) . ' ' . $this->cal->gettext('sendnotifications')));
  }

  /**
   * Generate the form for recurrence settings
   */
  function recurring_event_warning($attrib = array())
  {
    $attrib['id'] = 'edit-recurring-warning';
    
    $radio = new html_radiobutton(array('name' => '_savemode', 'class' => 'edit-recurring-savemode'));
    $form = html::label(null, $radio->show('', array('value' => 'current')) . $this->cal->gettext('currentevent')) . ' ' .
       html::label(null, $radio->show('', array('value' => 'future')) . $this->cal->gettext('futurevents')) . ' ' .
       html::label(null, $radio->show('all', array('value' => 'all')) . $this->cal->gettext('allevents')) . ' ' .
       html::label(null, $radio->show('', array('value' => 'new')) . $this->cal->gettext('saveasnew'));
       
    return html::div($attrib, html::div('message', html::span('ui-icon ui-icon-alert', '') . $this->cal->gettext('changerecurringeventwarning')) . html::div('savemode', $form));
  }

  /**
   * Form for uploading and importing events
   */
  function events_import_form($attrib = array())
  {
    if (!$attrib['id'])
      $attrib['id'] = 'rcmImportForm';

    // Get max filesize, enable upload progress bar
    $max_filesize = $this->rc->upload_init();

    $accept = '.ics, text/calendar, text/x-vcalendar, application/ics';
    if (class_exists('ZipArchive', false)) {
      $accept .= ', .zip, application/zip';
    }

    $input = new html_inputfield(array(
      'type' => 'file', 'name' => '_data', 'size' => $attrib['uploadfieldsize'],
      'accept' => $accept));

    $select = new html_select(array('name' => '_range', 'id' => 'event-import-range'));
    $select->add(array(
        $this->cal->gettext('onemonthback'),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>2))),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>3))),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>6))),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>12))),
        $this->cal->gettext('all'),
      ),
      array('1','2','3','6','12',0));

    $html = html::div('form-section',
      html::div(null, $input->show()) .
      html::div('hint', $this->rc->gettext(array('name' => 'maxuploadsize', 'vars' => array('size' => $max_filesize))))
    );

    $html .= html::div('form-section',
      html::label('event-import-calendar', $this->cal->gettext('calendar')) .
      $this->calendar_select(array('name' => 'calendar', 'id' => 'event-import-calendar'))
    );

    $html .= html::div('form-section',
      html::label('event-import-range', $this->cal->gettext('importrange')) .
      $select->show(1)
    );

    $this->rc->output->add_gui_object('importform', $attrib['id']);
    $this->rc->output->add_label('import');

    return html::tag('form', array('action' => $this->rc->url(array('task' => 'calendar', 'action' => 'import_events')),
      'method' => "post", 'enctype' => 'multipart/form-data', 'id' => $attrib['id']),
      $html
    );
  }

  /**
   * Form to select options for exporting events
   */
  function events_export_form($attrib = array())
  {
    if (!$attrib['id'])
      $attrib['id'] = 'rcmExportForm';

    $html = html::div('form-section',
      html::label('event-export-calendar', $this->cal->gettext('calendar')) .
      $this->calendar_select(array('name' => 'calendar', 'id' => 'event-export-calendar'))
    );

    $select = new html_select(array('name' => 'range', 'id' => 'event-export-range'));
    $select->add(array(
        $this->cal->gettext('all'),
        $this->cal->gettext('onemonthback'),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>2))),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>3))),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>6))),
        $this->cal->gettext(array('name' => 'nmonthsback', 'vars' => array('nr'=>12))),
        $this->cal->gettext('customdate'),
      ),
      array(0,'1','2','3','6','12','custom'));

    $startdate = new html_inputfield(array('name' => 'start', 'size' => 11, 'id' => 'event-export-startdate'));

    $html .= html::div('form-section',
      html::label('event-export-range', $this->cal->gettext('exportrange')) .
      $select->show(0) .
      html::span(array('style'=>'display:none'), $startdate->show())
    );

    $checkbox = new html_checkbox(array('name' => 'attachments', 'id' => 'event-export-attachments', 'value' => 1));
    $html .= html::div('form-section',
      html::label('event-export-range', $this->cal->gettext('exportattachments')) .
      $checkbox->show(1)
    );

    $this->rc->output->add_gui_object('exportform', $attrib['id']);

    return html::tag('form', array('action' => $this->rc->url(array('task' => 'calendar', 'action' => 'export_events')),
      'method' => "post", 'id' => $attrib['id']),
      $html
    );
  }

  /**
   * Generate the form for event attachments upload
   */
  function attachments_form($attrib = array())
  {
    // add ID if not given
    if (!$attrib['id'])
      $attrib['id'] = 'rcmUploadForm';

    // Get max filesize, enable upload progress bar
    $max_filesize = $this->rc->upload_init();

    $button = new html_inputfield(array('type' => 'button'));
    $input = new html_inputfield(array(
      'type' => 'file', 'name' => '_attachments[]',
      'multiple' => 'multiple', 'size' => $attrib['attachmentfieldsize']));

    return html::div($attrib,
      html::div(null, $input->show()) .
      html::div('formbuttons', $button->show($this->rc->gettext('upload'), array('class' => 'button mainaction',
        'onclick' => rcmail_output::JS_OBJECT_NAME . ".upload_file(this.form)"))) .
      html::div('hint', $this->rc->gettext(array('name' => 'maxuploadsize', 'vars' => array('size' => $max_filesize))))
    );
  }

  /**
   * Register UI object for HTML5 drag & drop file upload
   */
  function file_drop_area($attrib = array())
  {
      if ($attrib['id']) {
          $this->rc->output->add_gui_object('filedrop', $attrib['id']);
          $this->rc->output->set_env('filedrop', array('action' => 'upload', 'fieldname' => '_attachments'));
      }
  }

  /**
   * Generate HTML element for attachments list
   */
  function attachments_list($attrib = array())
  {
    if (!$attrib['id'])
      $attrib['id'] = 'rcmAttachmentList';

    $skin_path = $this->cal->local_skin_path();
    if ($attrib['deleteicon']) {
      $_SESSION[calendar::SESSION_KEY . '_deleteicon'] = $skin_path . $attrib['deleteicon'];
      $this->rc->output->set_env('deleteicon', $skin_path . $attrib['deleteicon']);
    }
    if ($attrib['cancelicon'])
      $this->rc->output->set_env('cancelicon', $skin_path . $attrib['cancelicon']);
    if ($attrib['loadingicon'])
      $this->rc->output->set_env('loadingicon', $skin_path . $attrib['loadingicon']);

    $this->rc->output->add_gui_object('attachmentlist', $attrib['id']);
    $this->attachmentlist_id = $attrib['id'];

    return html::tag('ul', $attrib, '', html::$common_attrib);
  }

  /**
   * Handler for menu to choose the driver for calendar creation.
   */
  function calendar_create_menu($attrib = array())
  {
    $content = "";
    foreach($this->cal->get_drivers() as $name => $driver)
    {
      $content .= html::tag('li', null, $this->rc->output->button(
          array('label' => 'calendar.calendar_'.$name,
                'class' => 'active',
                'prop' => json_encode(array('driver' => $name)),
                'command' => 'calendar-create',
                'title' => 'calendar.createcalendar')));
    }

    return $content;
  }

  /**
   * Handler for calendar form template.
   * The form content could be overriden by the driver
   */
  function calendar_editform($action, $calendar = array())
  {
    // compose default calendar form fields
    $input_name = new html_inputfield(array('name' => 'name', 'id' => 'calendar-name', 'size' => 20));
    $input_color = new html_inputfield(array('name' => 'color', 'id' => 'calendar-color', 'size' => 6));
    $driver = $this->cal->get_driver_by_gpc();

    $formfields = array(
      'name' => array(
        'label' => $this->cal->gettext('name'),
        'value' => $input_name->show($calendar['name']),
        'id' => 'calendar-name',
      ),
      'color' => array(
        'label' => $this->cal->gettext('color'),
        'value' => $input_color->show($calendar['color']),
        'id' => 'calendar-color',
      ),
    );

    if ($driver->alarms) {
      $checkbox = new html_checkbox(array('name' => 'showalarms', 'id' => 'calendar-showalarms', 'value' => 1));
      $formfields['showalarms'] = array(
        'label' => $this->cal->gettext('showalarms'),
        'value' => $checkbox->show($calendar['showalarms']?1:0),
        'id' => 'calendar-showalarms',
      );
    }

    // allow driver to extend or replace the form content
    return html::tag('form', $attrib = array('action' => "#", 'method' => "get", 'id' => 'calendarpropform'),
      $this->cal->driver->calendar_form($this->action, $this->calendar, $formfields)
    );
  }

  /**
   *
   */
  function attendees_list($attrib = array())
  {
    // add "noreply" checkbox to attendees table only
    $invitations = strpos($attrib['id'], 'attend') !== false;

    $invite = new html_checkbox(array('value' => 1, 'id' => 'edit-attendees-invite'));
    $table  = new html_table(array('cols' => 5 + intval($invitations), 'border' => 0, 'cellpadding' => 0, 'class' => 'rectable'));

    $table->add_header('role', $this->cal->gettext('role'));
    $table->add_header('name', $this->cal->gettext($attrib['coltitle'] ?: 'attendee'));
    $table->add_header('availability', $this->cal->gettext('availability'));
    $table->add_header('confirmstate', $this->cal->gettext('confirmstate'));
    if ($invitations) {
      $table->add_header(array('class' => 'invite', 'title' => $this->cal->gettext('sendinvitations')),
        $invite->show(1) . html::label('edit-attendees-invite', html::span('inner', $this->cal->gettext('sendinvitations'))));
    }
    $table->add_header('options', '');

    // hide invite column if disabled by config
    $itip_notify = (int)$this->rc->config->get('calendar_itip_send_option', $this->cal->defaults['calendar_itip_send_option']);
    if ($invitations && !($itip_notify & 2)) {
        $css = sprintf('#%s td.invite, #%s th.invite { display:none !important }', $attrib['id'], $attrib['id']);
        $this->rc->output->add_footer(html::tag('style', array('type' => 'text/css'), $css));
    }

    return $table->show($attrib);
  }

  /**
   *
   */
  function attendees_form($attrib = array())
  {
    $input    = new html_inputfield(array('name' => 'participant', 'id' => 'edit-attendee-name', 'size' => 30));
    $textarea = new html_textarea(array('name' => 'comment', 'id' => 'edit-attendees-comment',
        'rows' => 4, 'cols' => 55, 'title' => $this->cal->gettext('itipcommenttitle')));

    return html::div($attrib,
      html::div(null, $input->show() . " " .
        html::tag('input', array('type' => 'button', 'class' => 'button', 'id' => 'edit-attendee-add', 'value' => $this->cal->gettext('addattendee'))) . " " .
        html::tag('input', array('type' => 'button', 'class' => 'button', 'id' => 'edit-attendee-schedule', 'value' => $this->cal->gettext('scheduletime').'...'))) .
      html::p('attendees-commentbox', html::label(null, $this->cal->gettext('itipcomment') . $textarea->show()))
    );
  }

  /**
   *
   */
  function resources_form($attrib = array())
  {
    $input = new html_inputfield(array('name' => 'resource', 'id' => 'edit-resource-name', 'size' => 30));

    return html::div($attrib,
      html::div(null, $input->show() . " " .
        html::tag('input', array('type' => 'button', 'class' => 'button', 'id' => 'edit-resource-add', 'value' => $this->cal->gettext('addresource'))) . " " .
        html::tag('input', array('type' => 'button', 'class' => 'button', 'id' => 'edit-resource-find', 'value' => $this->cal->gettext('findresources').'...')))
      );
  }

  /**
   *
   */
  function resources_list($attrib = array())
  {
    $attrib += array('id' => 'calendar-resources-list');

    $this->rc->output->add_gui_object('resourceslist', $attrib['id']);

    return html::tag('ul', $attrib, '', html::$common_attrib);
  }

  /**
   *
   */
  public function resource_info($attrib = array())
  {
    $attrib += array('id' => 'calendar-resources-info');

    $this->rc->output->add_gui_object('resourceinfo', $attrib['id']);
    $this->rc->output->add_gui_object('resourceownerinfo', $attrib['id'] . '-owner');

    // copy address book labels for owner details to client
    $this->rc->output->add_label('name','firstname','surname','department','jobtitle','email','phone','address');

    $table_attrib = array('id','class','style','width','summary','cellpadding','cellspacing','border');

    return html::tag('table', $attrib,
        html::tag('tbody', null, ''), $table_attrib) .

      html::tag('table', array('id' => $attrib['id'] . '-owner', 'style' => 'display:none') + $attrib,
        html::tag('thead', null,
          html::tag('tr', null,
            html::tag('td', array('colspan' => 2), rcube_utils::rep_specialchars_output($this->cal->gettext('resourceowner')))
          )
        ) .
        html::tag('tbody', null, ''),
        $table_attrib);
  }

  /**
   *
   */
  public function resource_calendar($attrib = array())
  {
    $attrib += array('id' => 'calendar-resources-calendar');

    $this->rc->output->add_gui_object('resourceinfocalendar', $attrib['id']);

    return html::div($attrib, '');
  }

  /**
   * GUI object 'searchform' for the resource finder dialog
   *
   * @param array Named parameters
   * @return string HTML code for the gui object
   */
  function resources_search_form($attrib)
  {
    $attrib += array('command' => 'search-resource', 'id' => 'rcmcalresqsearchbox', 'autocomplete' => 'off');
    $attrib['name'] = '_q';

    $input_q = new html_inputfield($attrib);
    $out = $input_q->show();

    // add form tag around text field
    $out = $this->rc->output->form_tag(array(
      'name' => "rcmcalresoursqsearchform",
      'onsubmit' => rcmail_output::JS_OBJECT_NAME . ".command('" . $attrib['command'] . "'); return false",
      'style' => "display:inline"),
      $out);

    return $out;
  }

  /**
   *
   */
  function attendees_freebusy_table($attrib = array())
  {
    $table = new html_table(array('cols' => 2, 'border' => 0, 'cellspacing' => 0));
    $table->add('attendees',
      html::tag('h3', 'boxtitle', $this->cal->gettext('tabattendees')) .
      html::div('timesheader', '&nbsp;') .
      html::div(array('id' => 'schedule-attendees-list', 'class' => 'attendees-list'), '')
    );
    $table->add('times',
      html::div('scroll',
        html::tag('table', array('id' => 'schedule-freebusy-times', 'border' => 0, 'cellspacing' => 0), html::tag('thead') . html::tag('tbody')) .
        html::div(array('id' => 'schedule-event-time', 'style' => 'display:none'), '&nbsp;')
      )
    );
    
    return $table->show($attrib);
  }

  /**
   *
   */
  function event_invitebox($attrib = array())
  {
    if ($this->cal->event) {
      return html::div($attrib,
        $this->cal->itip->itip_object_details_table($this->cal->event, $this->cal->itip->gettext('itipinvitation')) .
        $this->cal->invitestatus
      );
    }
    
    return '';
  }

  function event_rsvp_buttons($attrib = array())
  {
    $actions = array('accepted','tentative','declined');
    if ($attrib['delegate'] !== 'false')
      $actions[] = 'delegated';

    return $this->cal->itip->itip_rsvp_buttons($attrib, $actions);
  }

}
