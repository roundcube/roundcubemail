<?php
/**
 * html5_notifier
 * Shows a desktop notification every time a new (recent) mail comes in
 *
 * @version 0.5.0 - 19.12.2013
 * @author Tilman Stremlau <tilman@stremlau.net>
 * @website stremlau.net/html5_notifier
 * @licence GNU GPL
 *
 **/

class html5_notifier extends rcube_plugin
{
    public $task = '?(?!login|logout).*';

    function init()
    {
        $RCMAIL = rcmail::get_instance();

        if(file_exists("./plugins/html5_notifier/config/config.inc.php"))
        {
            $this->load_config('config/config.inc.php');
        }

        $this->add_hook('preferences_list', array($this, 'prefs_list'));
        $this->add_hook('preferences_save', array($this, 'prefs_save'));

        if ($RCMAIL->config->get('html5_notifier_duration').'' != '0')
        {
            $this->add_hook('new_messages', array($this, 'show_notification'));
        }

        $this->include_script("html5_notifier.js");

        if ($RCMAIL->action != 'check-recent')
        {
            $this->add_texts('localization', array('notification_title', 'ok_notifications', 'no_notifications', 'check_ok', 'check_fail', 'check_fail_blocked')); //PR�ZESIEREN
        }
    }

    function show_notification($args)
    {
        $RCMAIL = rcmail::get_instance();

        //$search = $RCMAIL->config->get('html5_notifier_only_new', false) ?'NEW'  : 'RECENT';
		$deleted = $RCMAIL->config->get('skip_deleted') ? 'UNDELETED ' : '';
		$search  = $deleted . 'UNSEEN UID ' . $args['diff']['new'];

		$RCMAIL->storage->set_folder($args['mailbox']);
		$RCMAIL->storage->search($args['mailbox'], $search, null);
		$msgs = (array) $RCMAIL->storage->list_messages($args['mailbox']);
		$excluded_directories = preg_split("/(,|;| )+/", $RCMAIL->config->get('html5_notifier_excluded_directories'));

		foreach ($msgs as $msg) {
		    $from = $msg->get('from');
			$mbox = '';
			switch ($RCMAIL->config->get('html5_notifier_smbox')) {
				case 1: $mbox = array_pop(explode('.', str_replace('INBOX.', '', $args['mailbox']))); break;
				case 2: $mbox = str_replace('.', '/', str_replace('INBOX.', '', $args['mailbox'])); break;
			}
			$subject = ((!empty($mbox)) ? rcube_charset::convert($mbox, 'UTF7-IMAP') . ': ' : '') . $msg->get('subject');

            if(strtolower($_SESSION['username']) == strtolower($RCMAIL->user->data['username']) && !in_array($mbox, $excluded_directories))
            {
                $RCMAIL->output->command("plugin.showNotification", array(
                    'duration' => $RCMAIL->config->get('html5_notifier_duration'),
                    'opentype' => $RCMAIL->config->get('html5_notifier_popuptype'),
                    'subject' => $subject,
                    'from' => $from,
                    'uid' => $msg->uid.'&_mbox='.$args['mailbox'],
                ));
            }
        }
		$RCMAIL->storage->search($args['mailbox'], "ALL", null);
    }
    
    function prefs_list($args)
    {
        if($args['section'] == 'mailbox')
        {
            $RCMAIL = rcmail::get_instance();
              
            $field_id = 'rcmfd_html5_notifier'; 
			
            $select_duration = new html_select(array('name' => '_html5_notifier_duration', 'id' => $field_id));
            $select_duration->add($this->gettext('off'), '0');
            $times = array('3', '5', '8', '10', '12', '15', '20', '25', '30');
            foreach ($times as $time)
                $select_duration->add($time.' '.$this->gettext('seconds'), $time);
            $select_duration->add($this->gettext('durable'), '-1');
			
			$select_smbox = new html_select(array('name' => '_html5_notifier_smbox', 'id' => $field_id));
            $select_smbox->add($this->gettext('no_mailbox'), '0');
			$select_smbox->add($this->gettext('short_mailbox'), '1');
			$select_smbox->add($this->gettext('full_mailbox'), '2');

            $content = $select_duration->show($RCMAIL->config->get('html5_notifier_duration').'');
			$content .= $select_smbox->show($RCMAIL->config->get('html5_notifier_smbox').'');
            $content .= html::a(array('href' => '#', 'id' => 'rcmfd_html5_notifier_browser_conf', 'onclick' => 'rcmail_browser_notifications(); return false;'), $this->gettext('conf_browser')).' ';
            $content .= html::a(array('href' => '#', 'onclick' => 'rcmail_browser_notifications_test(); return false;'), $this->gettext('test_browser'));
            $args['blocks']['new_message']['options']['html5_notifier'] = array( 
                'title' => html::label($field_id, rcube::Q($this->gettext('shownotifies'))), 
                'content' => $content,
            );

            $check_only_new = new html_checkbox(array('name' => '_html5_notifier_only_new', 'id' => $field_id . '_only_new', 'value' => 1));
            $content = $check_only_new->show($RCMAIL->config->get('html5_notifier_only_new', false));
            $args['blocks']['new_message']['options']['html5_notifier_only_new'] = array(
                'title' => html::label($field_id, rcube::Q($this->gettext('onlynew'))),
                'content' => $content,
            );

			$input_excluded = new html_inputfield(array('name' => '_html5_notifier_excluded_directories', 'id' => $field_id . '_excluded'));
			$args['blocks']['new_message']['options']['html5_notifier_excluded_directories'] = array(
                'title' => html::label($field_id, rcube::Q($this->gettext('excluded_directories'))),
                'content' => $input_excluded->show($RCMAIL->config->get('html5_notifier_excluded_directories').''),
            );
            
			$select_type = new html_select(array('name' => '_html5_notifier_popuptype', 'id' => $field_id . '_popuptype'));
			$select_type->add($this->gettext('new_tab'), '0');
			$select_type->add($this->gettext('new_window'), '1');
			$args['blocks']['new_message']['options']['html5_notifier_popuptype'] = array(
				'title' => html::label($field_id, rcube::Q($this->gettext('notifier_popuptype'))),
				'content' => $select_type->show($RCMAIL->config->get('html5_notifier_popuptype').'')
			);

            $RCMAIL->output->add_script("$(document).ready(function(){ rcmail_browser_notifications_colorate(); });");
        }
        return $args;
    }

    function prefs_save($args)
    {
        if($args['section'] == 'mailbox')
        {
            $args['prefs']['html5_notifier_only_new'] = !empty($_POST['_html5_notifier_only_new']);
            $args['prefs']['html5_notifier_duration'] = rcube_utils::get_input_value('_html5_notifier_duration', rcube_utils::INPUT_POST);
			$args['prefs']['html5_notifier_smbox'] = rcube_utils::get_input_value('_html5_notifier_smbox', rcube_utils::INPUT_POST);
			$args['prefs']['html5_notifier_excluded_directories'] = rcube_utils::get_input_value('_html5_notifier_excluded_directories', rcube_utils::INPUT_POST);
			$args['prefs']['html5_notifier_popuptype'] = rcube_utils::get_input_value('_html5_notifier_popuptype', rcube_utils::INPUT_POST);
			return $args;
        }
    }
}
?>
