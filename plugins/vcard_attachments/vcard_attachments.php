<?php

/**
 * Detect VCard attachments and show a button to add them to address book
 *
 * @version @package_version@
 * @license GNU GPLv3+
 * @author Thomas Bruederli, Aleksander Machniak
 */
class vcard_attachments extends rcube_plugin
{
    public $task = 'mail';

    private $message;
    private $vcard_parts = array();
    private $vcard_bodies = array();

    function init()
    {
        $rcmail = rcmail::get_instance();
        if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
            $this->add_hook('message_load', array($this, 'message_load'));
            $this->add_hook('template_object_messagebody', array($this, 'html_output'));
        }
        else if (!$rcmail->output->framed && (!$rcmail->action || $rcmail->action == 'list')) {
            $icon = 'plugins/vcard_attachments/' .$this->local_skin_path(). '/vcard.png';
            $rcmail->output->set_env('vcard_icon', $icon);
            $this->include_script('vcardattach.js');
        }

        $this->register_action('plugin.savevcard', array($this, 'save_vcard'));
    }

    /**
     * Check message bodies and attachments for vcards
     */
    function message_load($p)
    {
        $this->message = $p['object'];

        // handle attachments vcard attachments
        foreach ((array)$this->message->attachments as $attachment) {
            if ($this->is_vcard($attachment)) {
                $this->vcard_parts[] = $attachment->mime_id;
            }
        }
        // the same with message bodies
        foreach ((array)$this->message->parts as $idx => $part) {
            if ($this->is_vcard($part)) {
                $this->vcard_parts[] = $part->mime_id;
                $this->vcard_bodies[] = $part->mime_id;
            }
        }

        if ($this->vcard_parts)
            $this->add_texts('localization');
    }

    /**
     * This callback function adds a box below the message content
     * if there is a vcard attachment available
     */
    function html_output($p)
    {
        $attach_script = false;
        $icon = 'plugins/vcard_attachments/' .$this->local_skin_path(). '/vcard_add_contact.png';

        foreach ($this->vcard_parts as $part) {
            $vcards = rcube_vcard::import($this->message->get_part_content($part, null, true));

            // successfully parsed vcards?
            if (empty($vcards))
                continue;

            // remove part's body
            if (in_array($part, $this->vcard_bodies))
                $p['content'] = '';

            foreach ($vcards as $idx => $vcard) {
                $display = $vcard->displayname;
                if ($vcard->email[0])
                    $display .= ' <'.$vcard->email[0].'>';

                // add box below messsage body
                $p['content'] .= html::p(array('class' => 'vcardattachment'),
                    html::a(array(
                        'href' => "#",
                        'onclick' => "return plugin_vcard_save_contact('" . JQ($part.':'.$idx) . "')",
                        'title' => $this->gettext('addvcardmsg'),
                        ),
                        html::span(null, Q($display)))
                    );
            }

            $attach_script = true;
        }

        if ($attach_script) {
            $this->include_script('vcardattach.js');
            $this->include_stylesheet($this->local_skin_path() . '/style.css');
        }

        return $p;
    }

    /**
     * Handler for request action
     */
    function save_vcard()
    {
	    $this->add_texts('localization', true);

        $uid = get_input_value('_uid', RCUBE_INPUT_POST);
        $mbox = get_input_value('_mbox', RCUBE_INPUT_POST);
        $mime_id = get_input_value('_part', RCUBE_INPUT_POST);

        $rcmail  = rcmail::get_instance();
        $storage = $rcmail->get_storage();
        $storage->set_folder($mbox);

        if ($uid && $mime_id) {
            list($mime_id, $index) = explode(':', $mime_id);
            $part = $storage->get_message_part($uid, $mime_id, null, null, null, true);
        }

        $error_msg = $this->gettext('vcardsavefailed');

        if ($part && ($vcards = rcube_vcard::import($part))
            && ($vcard = $vcards[$index]) && $vcard->displayname && $vcard->email
        ) {
            $CONTACTS = $this->get_address_book();
            $email    = $vcard->email[0];
            $contact  = $vcard->get_assoc();
            $valid    = true;

            // skip entries without an e-mail address or invalid
            if (empty($email) || !$CONTACTS->validate($contact, true)) {
                $valid = false;
            }
            else {
                // We're using UTF8 internally
                $email = rcube_idn_to_utf8($email);

                // compare e-mail address
                $existing = $CONTACTS->search('email', $email, 1, false);
                // compare display name
                if (!$existing->count && $vcard->displayname) {
                    $existing = $CONTACTS->search('name', $vcard->displayname, 1, false);
                }

                if ($existing->count) {
                    $rcmail->output->command('display_message', $this->gettext('contactexists'), 'warning');
                    $valid = false;
                }
            }

            if ($valid) {
                $plugin = $rcmail->plugins->exec_hook('contact_create', array('record' => $contact, 'source' => null));
                $contact = $plugin['record'];

                if (!$plugin['abort'] && $CONTACTS->insert($contact))
                    $rcmail->output->command('display_message', $this->gettext('addedsuccessfully'), 'confirmation');
                else
                    $rcmail->output->command('display_message', $error_msg, 'error');
            }
        }
        else {
            $rcmail->output->command('display_message', $error_msg, 'error');
        }

        $rcmail->output->send();
    }

    /**
     * Checks if specified message part is a vcard data
     *
     * @param rcube_message_part Part object
     *
     * @return boolean True if part is of type vcard
     */
    function is_vcard($part)
    {
        return (
            // Content-Type: text/vcard;
            $part->mimetype == 'text/vcard' ||
            // Content-Type: text/x-vcard;
            $part->mimetype == 'text/x-vcard' ||
            // Content-Type: text/directory; profile=vCard;
            ($part->mimetype == 'text/directory' && (
                ($part->ctype_parameters['profile'] &&
                    strtolower($part->ctype_parameters['profile']) == 'vcard')
            // Content-Type: text/directory; (with filename=*.vcf)
                    || ($part->filename && preg_match('/\.vcf$/i', $part->filename))
                )
            )
        );
    }

    /**
     * Getter for default (writable) addressbook
     */
    private function get_address_book()
    {
        if ($this->abook) {
            return $this->abook;
        }

        $rcmail = rcmail::get_instance();
        $abook  = $rcmail->config->get('default_addressbook');

        // Get configured addressbook
        $CONTACTS = $rcmail->get_address_book($abook, true);

        // Get first writeable addressbook if the configured doesn't exist
        // This can happen when user deleted the addressbook (e.g. Kolab folder)
        if ($abook === null || $abook === '' || !is_object($CONTACTS)) {
            $source   = reset($rcmail->get_address_sources(true));
            $CONTACTS = $rcmail->get_address_book($source['id'], true);
        }

        return $this->abook = $CONTACTS;
    }
}
