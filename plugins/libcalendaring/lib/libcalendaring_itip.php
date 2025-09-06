<?php

/**
 * iTIP functions for the calendar-based Roudncube plugins
 *
 * Class providing functionality to manage iTIP invitations
 *
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2011-2014, Kolab Systems AG <contact@kolabsys.com>
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
class libcalendaring_itip
{
    protected $rc;
    protected $lib;
    protected $plugin;
    protected $sender;
    protected $domain;
    protected $itip_send = false;
    protected $rsvp_actions = array('accepted','tentative','declined','delegated');
    protected $rsvp_status  = array('accepted','tentative','declined','delegated');

    function __construct($plugin, $domain = 'libcalendaring')
    {
        $this->plugin = $plugin;
        $this->rc = rcube::get_instance();
        $this->lib = libcalendaring::get_instance();
        $this->domain = $domain;

        $hook = $this->rc->plugins->exec_hook('calendar_load_itip',
            array('identity' => $this->rc->user->list_emails(true)));
        $this->sender = $hook['identity'];

        $this->plugin->add_hook('message_before_send', array($this, 'before_send_hook'));
        $this->plugin->add_hook('smtp_connect', array($this, 'smtp_connect_hook'));
    }

    public function set_sender_email($email)
    {
        if (!empty($email))
            $this->sender['email'] = $email;
    }

    public function set_rsvp_actions($actions)
    {
        $this->rsvp_actions = (array)$actions;
        $this->rsvp_status = array_merge($this->rsvp_actions, array('delegated'));
    }

    public function set_rsvp_status($status)
    {
        $this->rsvp_status = $status;
    }

    /**
     * Wrapper for rcube_plugin::gettext()
     * Checking for a label in different domains
     *
     * @see rcube::gettext()
     */
    public function gettext($p)
    {
        $label = is_array($p) ? $p['name'] : $p;
        $domain = $this->domain;
        if (!$this->rc->text_exists($label, $domain)) {
            $domain = 'libcalendaring';
        }
        return $this->rc->gettext($p, $domain);
    }

    /**
     * Send an iTip mail message
     *
     * @param array   Event object to send
     * @param string  iTip method (REQUEST|REPLY|CANCEL)
     * @param array   Hash array with recipient data (name, email)
     * @param string  Mail subject
     * @param string  Mail body text label
     * @param object  Mail_mime object with message data
     * @param boolean Request RSVP
     * @return boolean True on success, false on failure
     */
    public function send_itip_message($event, $method, $recipient, $subject, $bodytext, $message = null, $rsvp = true)
    {
        if (!$this->sender['name'])
            $this->sender['name'] = $this->sender['email'];

        if (!$message) {
            libcalendaring::identify_recurrence_instance($event);
            $message = $this->compose_itip_message($event, $method, $rsvp);
        }

        $mailto = rcube_utils::idn_to_ascii($recipient['email']);

        $headers = $message->headers();
        $headers['To'] = format_email_recipient($mailto, $recipient['name']);
        $headers['Subject'] = $this->gettext(array(
            'name' => $subject,
            'vars' => array(
                'title' => $event['title'],
                'name' => $this->sender['name']
            )
        ));

        // compose a list of all event attendees
        $attendees_list = array();
        foreach ((array)$event['attendees'] as $attendee) {
            $attendees_list[] = ($attendee['name'] && $attendee['email']) ?
                $attendee['name'] . ' <' . $attendee['email'] . '>' :
                ($attendee['name'] ? $attendee['name'] : $attendee['email']);
        }

        $recurrence_info = '';
        if (!empty($event['recurrence_id'])) {
            $recurrence_info = "\n\n** " . $this->gettext($event['thisandfuture'] ? 'itipmessagefutureoccurrence' : 'itipmessagesingleoccurrence') . ' **';
        }
        else if (!empty($event['recurrence'])) {
            $recurrence_info = sprintf("\n%s: %s", $this->gettext('recurring'), $this->lib->recurrence_text($event['recurrence']));
        }

        $mailbody = $this->gettext(array(
            'name' => $bodytext,
            'vars' => array(
                'title' => $event['title'],
                'date' => $this->lib->event_date_text($event, true) . $recurrence_info,
                'attendees' => join(",\n ", $attendees_list),
                'sender' => $this->sender['name'],
                'organizer' => $this->sender['name'],
            )
        ));

        // if (!empty($event['comment'])) {
        //     $mailbody .= "\n\n" . $this->gettext('itipsendercomment') . $event['comment'];
        // }

        // append links for direct invitation replies
        if ($method == 'REQUEST' && $rsvp && ($token = $this->store_invitation($event, $recipient['email']))) {
            $mailbody .= "\n\n" . $this->gettext(array(
        //        'name' => 'invitationattendlinks',
        //        'vars' => array('url' => $this->plugin->get_url(array('action' => 'attend', 't' => $token))),
            ));
        }
        else if ($method == 'CANCEL' && $event['cancelled']) {
            $this->cancel_itip_invitation($event);
        }

        $message->headers($headers, true);
        $message->setTXTBody(rcube_mime::format_flowed($mailbody, 79));

        if ($this->rc->config->get('libcalendaring_itip_debug', false)) {
            console('iTip ' . $method, $message->txtHeaders() . "\n\r" . $message->get());
        }

        // finally send the message
        $this->itip_send = true;
        $sent = $this->rc->deliver_message($message, $headers['X-Sender'], $mailto, $smtp_error);
        $this->itip_send = false;

        return $sent;
    }

    /**
     * Plugin hook triggered by rcube::deliver_message() before delivering a message.
     * Here we can set the 'smtp_server' config option to '' in order to use
     * PHP's mail() function for unauthenticated email sending.
     */
    public function before_send_hook($p)
    {
        if ($this->itip_send && !$this->rc->user->ID && $this->rc->config->get('calendar_itip_smtp_server', null) === '') {
            $this->rc->config->set('smtp_server', '');
        }

        return $p;
    }

    /**
     * Plugin hook to alter SMTP authentication.
     * This is used if iTip messages are to be sent from an unauthenticated session
     */
    public function smtp_connect_hook($p)
    {
        // replace smtp auth settings if we're not in an authenticated session
        if ($this->itip_send && !$this->rc->user->ID) {
            foreach (array('smtp_server', 'smtp_user', 'smtp_pass') as $prop) {
                $p[$prop] = $this->rc->config->get("calendar_itip_$prop", $p[$prop]);
            }
        }

      return $p;
    }

    /**
     * Helper function to build a Mail_mime object to send an iTip message
     *
     * @param array   Event object to send
     * @param string  iTip method (REQUEST|REPLY|CANCEL)
     * @param boolean Request RSVP
     * @return object Mail_mime object with message data
     */
    public function compose_itip_message($event, $method, $rsvp = true)
    {
        $from = rcube_utils::idn_to_ascii($this->sender['email']);
        $from_utf = rcube_utils::idn_to_utf8($from);
        $sender = format_email_recipient($from, $this->sender['name']);

        // truncate list attendees down to the recipient of the iTip Reply.
        // constraints for a METHOD:REPLY according to RFC 5546
        if ($method == 'REPLY') {
            $replying_attendee = null;
            $reply_attendees = array();
            foreach ($event['attendees'] as $attendee) {
                if ($attendee['role'] == 'ORGANIZER') {
                    $reply_attendees[] = $attendee;
                }
                else if (strcasecmp($attendee['email'], $from) == 0 || strcasecmp($attendee['email'], $from_utf) == 0) {
                    $replying_attendee = $attendee;
                    if ($attendee['status'] != 'DELEGATED') {
                        unset($replying_attendee['rsvp']);  // unset the RSVP attribute
                    }
                }
                // include attendees relevant for delegation (RFC 5546, Section 4.2.5)
                else if ((!empty($attendee['delegated-to']) &&
                            (strcasecmp($attendee['delegated-to'], $from) == 0 || strcasecmp($attendee['delegated-to'], $from_utf) == 0)) ||
                         (!empty($attendee['delegated-from']) &&
                            (strcasecmp($attendee['delegated-from'], $from) == 0 || strcasecmp($attendee['delegated-from'], $from_utf) == 0))) {
                    $reply_attendees[] = $attendee;
                }
            }
            if ($replying_attendee) {
                array_unshift($reply_attendees, $replying_attendee);
                $event['attendees'] = $reply_attendees;
            }
            if ($event['recurrence']) {
                unset($event['recurrence']['EXCEPTIONS']);
            }
        }
        // set RSVP for every attendee
        else if ($method == 'REQUEST') {
            foreach ($event['attendees'] as $i => $attendee) {
                if (($rsvp || !isset($attendee['rsvp'])) && ($attendee['status'] != 'DELEGATED' && $attendee['role'] != 'NON-PARTICIPANT')) {
                    $event['attendees'][$i]['rsvp']= (bool)$rsvp;
                }
            }
        }
        else if ($method == 'CANCEL') {
            if ($event['recurrence']) {
                unset($event['recurrence']['EXCEPTIONS']);
            }
        }

        // compose multipart message using PEAR:Mail_Mime
        if (defined(RCUBE_CHARSET)) {
            $charset = RCUBE_CHARSET;
        } elseif (defined(RCUBE_CHARSET)) {
            $charset = RCUBE_CHARSET;
        } else {
            $charset = $this->rc->config->get('default_charset');
        }

        $message = new Mail_mime("\r\n");
        $message->setParam('text_encoding', 'quoted-printable');
        $message->setParam('head_encoding', 'quoted-printable');
        $message->setParam('head_charset', $charset);
        $message->setParam('text_charset', $charset . ";\r\n format=flowed");
        $message->setContentType('multipart/alternative');

        // compose common headers array
        $headers = array(
            'From' => $sender,
            'Date' => $this->rc->user_date(),
            'Message-ID' => $this->rc->gen_message_id(),
            'X-Sender' => $from,
        );
        if ($agent = $this->rc->config->get('useragent')) {
            $headers['User-Agent'] = $agent;
        }

        $message->headers($headers);

        // attach ics file for this event
        $ical = libcalendaring::get_ical();
        $ics = $ical->export(array($event), $method, false, $method == 'REQUEST' && $this->plugin->driver ? array($this->plugin->driver, 'get_attachment_body') : false);
        $filename = $event['_type'] == 'task' ? 'todo.ics' : 'event.ics';
        $message->addAttachment($ics, 'text/calendar', $filename, false, '8bit', '', $charset . "; method=" . $method);

        return $message;
    }

    /**
     * Forward the given iTip event as delegation to another person
     *
     * @param array Event object to delegate
     * @param mixed Delegatee as string or hash array with keys 'name' and 'mailto'
     * @param boolean The delegator's RSVP flag
     * @param array List with indexes of new/updated attendees
     * @return boolean True on success, False on failure
     */
    public function delegate_to(&$event, $delegate, $rsvp = false, &$attendees = array())
    {
        if (is_string($delegate)) {
            $delegates = rcube_mime::decode_address_list($delegate, 1, false);
            if (count($delegates) > 0) {
                $delegate = reset($delegates);
            }
        }

        $emails = $this->lib->get_user_emails();
        $me     = $this->rc->user->list_emails(true);

        // find/create the delegate attendee
        $delegate_attendee = array(
            'email' => $delegate['mailto'],
            'name'  => $delegate['name'],
            'role'  => 'REQ-PARTICIPANT',
        );
        $delegate_index = count($event['attendees']);

        foreach ($event['attendees'] as $i => $attendee) {
          // set myself the DELEGATED-TO parameter
          if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
              $event['attendees'][$i]['delegated-to'] = $delegate['mailto'];
              $event['attendees'][$i]['status'] = 'DELEGATED';
              $event['attendees'][$i]['role'] = 'NON-PARTICIPANT';
              $event['attendees'][$i]['rsvp'] = $rsvp;

              $me['email'] = $attendee['email'];
              $delegate_attendee['role'] = $attendee['role'];
          }
          // the disired delegatee is already listed as an attendee
          else if (stripos($delegate['mailto'], $attendee['email']) !== false && $attendee['role'] != 'ORGANIZER') {
              $delegate_attendee = $attendee;
              $delegate_index = $i;
              break;
          }
          // TODO: remove previous delegatee (i.e. attendee that has DELEGATED-FROM == $me)
        }

        // set/add delegate attendee with RSVP=TRUE and DELEGATED-FROM parameter
        $delegate_attendee['rsvp'] = true;
        $delegate_attendee['status'] = 'NEEDS-ACTION';
        $delegate_attendee['delegated-from'] = $me['email'];
        $event['attendees'][$delegate_index] = $delegate_attendee;

        $attendees[] = $delegate_index;

        $this->set_sender_email($me['email']);
        return $this->send_itip_message($event, 'REQUEST', $delegate_attendee, 'itipsubjectdelegatedto', 'itipmailbodydelegatedto');
    }

    /**
     * Handler for calendar/itip-status requests
     */
    public function get_itip_status($event, $existing = null)
    {
      $action = $event['rsvp'] ? 'rsvp' : '';
      $status = $event['fallback'];
      $latest = false;
      $html = '';

      if (is_numeric($event['changed']))
        $event['changed'] = new DateTime('@'.$event['changed']);

      // check if the given itip object matches the last state
      if ($existing) {
        $latest = (isset($event['sequence']) && intval($existing['sequence']) == intval($event['sequence'])) ||
                  (!isset($event['sequence']) && $existing['changed'] && $existing['changed'] >= $event['changed']);
      }

      // determine action for REQUEST
      if ($event['method'] == 'REQUEST') {
        $html = html::div('rsvp-status', $this->gettext('acceptinvitation'));

        if ($existing) {
          $rsvp = $event['rsvp'];
          $emails = $this->lib->get_user_emails();
          foreach ($existing['attendees'] as $attendee) {
            if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
              $status = strtoupper($attendee['status']);
              break;
            }
          }
        }
        else {
          $rsvp = $event['rsvp'] && $this->rc->config->get('calendar_allow_itip_uninvited', true);
        }

        $status_lc = strtolower($status);

        if ($status_lc == 'unknown' && !$this->rc->config->get('calendar_allow_itip_uninvited', true)) {
          $html = html::div('rsvp-status', $this->gettext('notanattendee'));
          $action = 'import';
        }
        else if (in_array($status_lc, $this->rsvp_status)) {
          $status_text = $this->gettext(($latest ? 'youhave' : 'youhavepreviously') . $status_lc);

          if ($existing && ($existing['sequence'] > $event['sequence'] || (!isset($event['sequence']) && $existing['changed'] && $existing['changed'] > $event['changed']))) {
            $action = '';  // nothing to do here, outdated invitation
            if ($status_lc == 'needs-action')
              $status_text = $this->gettext('outdatedinvitation');
          }
          else if (!$existing && !$rsvp) {
            $action = 'import';
          }
          else if ($latest && $status_lc != 'needs-action') {
            $action = 'update';
          }

          $html = html::div('rsvp-status ' . $status_lc, $status_text);
        }
      }
      // determine action for REPLY
      else if ($event['method'] == 'REPLY') {
        // check whether the sender already is an attendee
        if ($existing) {
          $action = $this->rc->config->get('calendar_allow_itip_uninvited', true) ? 'accept' : '';
          $listed = false;
          foreach ($existing['attendees'] as $attendee) {
            if ($attendee['role'] != 'ORGANIZER' && strcasecmp($attendee['email'], $event['attendee']) == 0) {
              $status_lc = strtolower($status);
              if (in_array($status_lc, $this->rsvp_status)) {
                $html = html::div('rsvp-status ' . $status_lc, $this->gettext(array(
                    'name' => 'attendee' . $status_lc,
                    'vars' => array(
                        'delegatedto' => rcube::Q($event['delegated-to'] ?: ($attendee['delegated-to'] ?: '?')),
                    )
                )));
              }
              $action = $attendee['status'] == $status || !$latest ? '' : 'update';
              $listed = true;
              break;
            }
          }

          if (!$listed) {
            $html = html::div('rsvp-status', $this->gettext('itipnewattendee'));
          }
        }
        else {
          $html = html::div('rsvp-status hint', $this->gettext('itipobjectnotfound'));
          $action = '';
        }
      }
      else if ($event['method'] == 'CANCEL') {
        if (!$existing) {
          $html = html::div('rsvp-status hint', $this->gettext('itipobjectnotfound'));
          $action = '';
        }
      }

      return array(
          'uid' => $event['uid'],
          'id' => asciiwords($event['uid'], true),
          'existing' => $existing ? true : false,
          'saved' => $existing ? true : false,
          'latest' => $latest,
          'status' => $status,
          'action' => $action,
          'html' => $html,
      );
    }

    /**
     * Build inline UI elements for iTip messages
     */
    public function mail_itip_inline_ui($event, $method, $mime_id, $task, $message_date = null, $preview_url = null)
    {
        $buttons = array();
        $dom_id = asciiwords($event['uid'], true);
        $rsvp_status = 'unknown';

        // pass some metadata about the event and trigger the asynchronous status check
        $changed = is_object($event['changed']) ? $event['changed'] : $message_date;
        $metadata = array(
            'uid'      => $event['uid'],
            '_instance' => $event['_instance'],
            'changed'  => $changed ? $changed->format('U') : 0,
            'sequence' => intval($event['sequence']),
            'method'   => $method,
            'task'     => $task,
        );

        // create buttons to be activated from async request checking existence of this event in local calendars
        $buttons[] = html::div(array('id' => 'loading-'.$dom_id, 'class' => 'rsvp-status loading'), $this->gettext('loading'));

        // on iTip REPLY we have two options:
        if ($method == 'REPLY') {
            $title = $this->gettext('itipreply');

            foreach ($event['attendees'] as $attendee) {
                if (!empty($attendee['email']) && $attendee['role'] != 'ORGANIZER' &&
                      (empty($event['_sender']) || ($attendee['email'] == $event['_sender'] || $attendee['email'] == $event['_sender_utf']))) {
                    $metadata['attendee'] = $attendee['email'];
                    $rsvp_status = strtoupper($attendee['status']);
                    if ($attendee['delegated-to'])
                        $metadata['delegated-to'] = $attendee['delegated-to'];
                    break;
                }
            }

            // 1. update the attendee status on our copy
            $update_button = html::tag('input', array(
                'type' => 'button',
                'class' => 'button',
                'onclick' => "rcube_libcalendaring.add_from_itip_mail('" . rcube::JQ($mime_id) . "', '$task')",
                'value' => $this->gettext('updateattendeestatus'),
            ));

            // 2. accept or decline a new or delegate attendee
            $accept_buttons = html::tag('input', array(
                'type' => 'button',
                'class' => "button accept",
                'onclick' => "rcube_libcalendaring.add_from_itip_mail('" . rcube::JQ($mime_id) . "', '$task')",
                'value' => $this->gettext('acceptattendee'),
            ));
            $accept_buttons .= html::tag('input', array(
                'type' => 'button',
                'class' => "button decline",
                'onclick' => "rcube_libcalendaring.decline_attendee_reply('" . rcube::JQ($mime_id) . "', '$task')",
                'value' => $this->gettext('declineattendee'),
            ));

            $buttons[] = html::div(array('id' => 'update-'.$dom_id, 'style' => 'display:none'), $update_button);
            $buttons[] = html::div(array('id' => 'accept-'.$dom_id, 'style' => 'display:none'), $accept_buttons);
        }
        // when receiving iTip REQUEST messages:
        else if ($method == 'REQUEST') {
            $emails = $this->lib->get_user_emails();
            $title = $event['sequence'] > 0 ? $this->gettext('itipupdate') : $this->gettext('itipinvitation');
            $metadata['rsvp'] = true;
            $metadata['sensitivity'] = $event['sensitivity'];

            if (is_object($event['start'])) {
                $metadata['date'] = $event['start']->format('U');
            }

            // check for X-KOLAB-INVITATIONTYPE property and only show accept/decline buttons
            if (self::get_custom_property($event, 'X-KOLAB-INVITATIONTYPE') == 'CONFIRMATION') {
                $this->rsvp_actions = array('accepted','declined');
                $metadata['nosave'] = true;
            }

            // 1. display RSVP buttons (if the user was invited)
            foreach ($this->rsvp_actions as $method) {
                $rsvp_buttons .= html::tag('input', array(
                    'type' => 'button',
                    'class' => "button $method",
                    'onclick' => "rcube_libcalendaring.add_from_itip_mail('" . rcube::JQ($mime_id) . "', '$task', '$method', '$dom_id')",
                    'value' => $this->gettext('itip' . $method),
                ));
            }

            // add button to open calendar/preview
            if (!empty($preview_url)) {
              $msgref = $this->lib->ical_message->folder . '/' . $this->lib->ical_message->uid . '#' . $mime_id;
              $rsvp_buttons .= html::tag('input', array(
                  'type' => 'button',
                  'class' => "button preview",
                  'onclick' => "rcube_libcalendaring.open_itip_preview('" . rcube::JQ($preview_url) . "', '" . rcube::JQ($msgref) . "')",
                  'value' => $this->gettext('openpreview'),
              ));
            }

            // 3. Simply import the event without replying
            $import_button = html::tag('input', array(
                'type' => 'button',
                'class' => 'button',
                'onclick' => "rcube_libcalendaring.add_from_itip_mail('" . rcube::JQ($mime_id) . "', '$task')",
                'value' => $this->gettext('importtocalendar'),
            ));

            // check my status
            foreach ($event['attendees'] as $attendee) {
                if ($attendee['email'] && in_array(strtolower($attendee['email']), $emails)) {
                    $metadata['attendee'] = $attendee['email'];
                    $metadata['rsvp'] = $attendee['rsvp'] || $attendee['role'] != 'NON-PARTICIPANT';
                    $rsvp_status = !empty($attendee['status']) ? strtoupper($attendee['status']) : 'NEEDS-ACTION';
                    break;
                }
            }

            // add itip reply message controls
            $rsvp_buttons .= html::div('itip-reply-controls', $this->itip_rsvp_options_ui($dom_id, $metadata['nosave']));

            $buttons[] = html::div(array('id' => 'rsvp-'.$dom_id, 'class' => 'rsvp-buttons', 'style' => 'display:none'), $rsvp_buttons);
            $buttons[] = html::div(array('id' => 'update-'.$dom_id, 'style' => 'display:none'), $update_button);

            // prepare autocompletion for delegation dialog
            if (in_array('delegated', $this->rsvp_actions)) {
                $this->rc->autocomplete_init();
            }
        }
        // for CANCEL messages, we can:
        else if ($method == 'CANCEL') {
            $title = $this->gettext('itipcancellation');
            $event_prop = array_filter(array(
              'uid' => $event['uid'],
              '_instance' => $event['_instance'],
              '_savemode' => $event['_savemode'],
            ));

            // 1. remove the event from our calendar
            $button_remove = html::tag('input', array(
                'type' => 'button',
                'class' => 'button',
                'onclick' => "rcube_libcalendaring.remove_from_itip(" . rcube_output::json_serialize($event_prop) . ", '$task', '" . rcube::JQ($event['title']) . "')",
                'value' => $this->gettext('removefromcalendar'),
            ));

            $buttons[] = html::div(array('id' => 'rsvp-'.$dom_id, 'style' => 'display:none'), $button_remove . $button_update);

            $rsvp_status = 'CANCELLED';
            $metadata['rsvp'] = true;
        }

        // append generic import button
        if ($import_button) {
            $buttons[] = html::div(array('id' => 'import-'.$dom_id, 'style' => 'display:none'), $import_button);
        }

        // pass some metadata about the event and trigger the asynchronous status check
        $metadata['fallback'] = $rsvp_status;
        $metadata['rsvp'] = intval($metadata['rsvp']);

        $this->rc->output->add_script("rcube_libcalendaring.fetch_itip_object_status(" . rcube_output::json_serialize($metadata) . ")", 'docready');

        // get localized texts from the right domain
        foreach (array('savingdata','deleteobjectconfirm','declinedeleteconfirm','declineattendee',
            'cancel','itipdelegated','declineattendeeconfirm','itipcomment','delegateinvitation',
            'delegateto','delegatersvpme','delegateinvalidaddress') as $label) {
          $this->rc->output->command('add_label', "itip.$label", $this->gettext($label));
        }

        // show event details with buttons
        return $this->itip_object_details_table($event, $title) .
            html::div(array('class' => 'itip-buttons', 'id' => 'itip-buttons-' . asciiwords($metadata['uid'], true)), join('', $buttons));
    }

    /**
     * Render an RSVP UI widget with buttons to respond on iTip invitations
     */
    function itip_rsvp_buttons($attrib = array(), $actions = null)
    {
        $attrib += array('type' => 'button');

        if (!$actions)
            $actions = $this->rsvp_actions;

        foreach ($actions as $method) {
            $buttons .= html::tag('input', array(
                'type'  => $attrib['type'],
                'name'  => $attrib['iname'],
                'class' => 'button',
                'rel'   => $method,
                'value' => $this->gettext('itip' . $method),
            ));
        }

        // add localized texts for the delegation dialog
        if (in_array('delegated', $actions)) {
            foreach (array('itipdelegated','itipcomment','delegateinvitation',
                  'delegateto','delegatersvpme','delegateinvalidaddress','cancel') as $label) {
                $this->rc->output->command('add_label', "itip.$label", $this->gettext($label));
            }
        }

        foreach (array('all','current','future') as $mode) {
            $this->rc->output->command('add_label', "rsvpmode$mode", $this->gettext("rsvpmode$mode"));
        }

        $savemode_radio = new html_radiobutton(array('name' => '_rsvpmode', 'class' => 'rsvp-replymode'));

        return html::div($attrib,
            html::div('label', $this->gettext('acceptinvitation')) .
            html::div('rsvp-buttons',
                $buttons .
                html::div('itip-reply-controls', $this->itip_rsvp_options_ui($attrib['id']))
            )
        );
    }

    /**
     * Render UI elements to control iTip reply message sending
     */
    public function itip_rsvp_options_ui($dom_id, $disable = false)
    {
        $itip_sending = $this->rc->config->get('calendar_itip_send_option', 3);

        // itip sending is entirely disabled
        if ($itip_sending === 0) {
            return '';
        }
        // add checkbox to suppress itip reply message
        else if ($itip_sending >= 2) {
            $rsvp_additions = html::label(array('class' => 'noreply-toggle'),
                html::tag('input', array('type' => 'checkbox', 'id' => 'noreply-'.$dom_id, 'value' => 1, 'disabled' => $disable, 'checked' => ($itip_sending & 1) == 0))
                . ' ' . $this->gettext('itipsuppressreply')
            );
        }

        // add input field for reply comment
        $rsvp_additions .= html::a(array('href' => '#toggle', 'class' => 'reply-comment-toggle'), $this->gettext('itipeditresponse'));
        $rsvp_additions .= html::div('itip-reply-comment',
            html::tag('textarea', array('id' => 'reply-comment-'.$dom_id, 'name' => '_comment', 'cols' => 40, 'rows' => 6, 'style' => 'display:none', 'placeholder' => $this->gettext('itipcomment')), '')
        );

        return $rsvp_additions;
    }

    /**
     * Render event/task details in a table
     */
    function itip_object_details_table($event, $title)
    {
        $table = new html_table(array('cols' => 2, 'border' => 0, 'class' => 'calendar-eventdetails'));
        $table->add('ititle', $title);
        $table->add('title', rcube::Q($event['title']));
        if ($event['start'] && $event['end']) {
            $table->add('label', $this->gettext('date'));
            $table->add('date', rcube::Q($this->lib->event_date_text($event)));
        }
        else if ($event['due'] && $event['_type'] == 'task') {
            $table->add('label', $this->gettext('date'));
            $table->add('date', rcube::Q($this->lib->event_date_text($event)));
        }
        if (!empty($event['recurrence_date'])) {
            $table->add('label', '');
            $table->add('recurrence-id', $this->gettext($event['thisandfuture'] ? 'itipfutureoccurrence' : 'itipsingleoccurrence'));
        }
        else if (!empty($event['recurrence'])) {
            $table->add('label', $this->gettext('recurring'));
            $table->add('recurrence', $this->lib->recurrence_text($event['recurrence']));
        }
        if ($event['location']) {
            $table->add('label', $this->gettext('location'));
            $table->add('location', rcube::Q($event['location']));
        }
        if ($event['sensitivity'] && $event['sensitivity'] != 'public') {
            $table->add('label', $this->gettext('sensitivity'));
            $table->add('sensitivity', ucfirst($this->gettext($event['sensitivity'])) . '!');
        }
        if ($event['status'] == 'COMPLETED' || $event['status'] == 'CANCELLED') {
            $table->add('label', $this->gettext('status'));
            $table->add('status', $this->gettext('status-' . strtolower($event['status'])));
        }
        if ($event['comment']) {
            $table->add('label', $this->gettext('comment'));
            $table->add('location', rcube::Q($event['comment']));
        }

        return $table->show();
    }


    /**
     * Create iTIP invitation token for later replies via URL
     *
     * @param array Hash array with event properties
     * @param string Attendee email address
     * @return string Invitation token
     */
    public function store_invitation($event, $attendee)
    {
        // empty stub
        return false;
    }

    /**
     * Mark invitations for the given event as cancelled
     *
     * @param array Hash array with event properties
     */
    public function cancel_itip_invitation($event)
    {
        // empty stub
        return false;
    }

    /**
     * Utility function to get the value of a custom property
     */
    public static function get_custom_property($event, $name)
    {
      $ret = false;

      if (is_array($event['x-custom'])) {
          array_walk($event['x-custom'], function($prop, $i) use ($name, &$ret) {
              if (strcasecmp($prop[0], $name) === 0) {
                  $ret = $prop[1];
              }
          });
      }

      return $ret;
    }

}
