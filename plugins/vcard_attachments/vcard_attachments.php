<?php

/**
 * Detect VCard attachments and show a button to add them to address book
 *
 * @version @package_version@
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
            $vcards = rcube_vcard::import($this->message->get_part_content($part));

            // successfully parsed vcards?
            if (empty($vcards))
                continue;

            // remove part's body
            if (in_array($part, $this->vcard_bodies))
                $p['content'] = '';

            $style = 'margin:0.5em 1em; padding:0.2em 0.5em; border:1px solid #999; '
                .'border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px; width: auto';

            foreach ($vcards as $idx => $vcard) {
                $display = $vcard->displayname;
                if ($vcard->email[0])
                    $display .= ' <'.$vcard->email[0].'>';

                // add box below messsage body
                $p['content'] .= html::p(array('style' => $style),
                    html::a(array(
                        'href' => "#",
                        'onclick' => "return plugin_vcard_save_contact('".JQ($part.':'.$idx)."')",
                        'title' => $this->gettext('addvcardmsg')),
                        html::img(array('src' => $icon, 'style' => "vertical-align:middle")))
                    . ' ' . html::span(null, Q($display)));
            }

            $attach_script = true;
        }

        if ($attach_script)
            $this->include_script('vcardattach.js');

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

        $rcmail = rcmail::get_instance();

        if ($uid && $mime_id) {
            list($mime_id, $index) = explode(':', $mime_id);
            $part = $rcmail->imap->get_message_part($uid, $mime_id);
        }

        $error_msg = $this->gettext('vcardsavefailed');

        if ($part && ($vcards = rcube_vcard::import($part))
            && ($vcard = $vcards[$index]) && $vcard->displayname && $vcard->email) {

            $contacts = $rcmail->get_address_book(null, true);

            // check for existing contacts
            $existing = $contacts->search('email', $vcard->email[0], true, false);
            if ($existing->count) {
                $rcmail->output->command('display_message', $this->gettext('contactexists'), 'warning');
            }
            else {
                // add contact
                $contact = array(
                    'name'      => $vcard->displayname,
                    'firstname' => $vcard->firstname,
                    'surname'   => $vcard->surname,
                    'email'     => $vcard->email[0],
                    'vcard'     => $vcard->export(),
                );

                $plugin = $rcmail->plugins->exec_hook('contact_create', array('record' => $contact, 'source' => null));
                $contact = $plugin['record'];

                if (!$plugin['abort'] && ($done = $contacts->insert($contact)))
                    $rcmail->output->command('display_message', $this->gettext('addedsuccessfully'), 'confirmation');
                else
                    $rcmail->output->command('display_message', $error_msg, 'error');
            }
        }
        else
            $rcmail->output->command('display_message', $error_msg, 'error');

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
}
