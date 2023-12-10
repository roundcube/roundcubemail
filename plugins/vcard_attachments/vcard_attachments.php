<?php

/**
 * Detects VCard attachments and show a button to add them to address book
 * Adds possibility to attach a contact vcard to mail messages
 *
 * @license GNU GPLv3+
 * @author Thomas Bruederli, Aleksander Machniak
 */
class vcard_attachments extends rcube_plugin
{
    public $task = 'mail|addressbook';

    private $abook;
    private $message;
    private $vcard_parts  = [];
    private $vcard_bodies = [];

    /**
     * Plugin initialization
     */
    function init()
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->task == 'addressbook') {
            $this->add_texts('localization', !$rcmail->output->ajax_call);
            $this->include_stylesheet($this->local_skin_path() . '/style.css');
            $this->include_script('vcardattach.js');
            $this->add_button([
                    'type'     => 'link-menuitem',
                    'label'    => 'vcard_attachments.forwardvcard',
                    'command'  => 'attach-vcard',
                    'class'    => 'icon vcard',
                    'classact' => 'icon vcard active',
                    'innerclass' => 'icon vcard',
                ],
                'contactmenu'
            );
        }
        else {
            if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
                $this->add_hook('message_load', [$this, 'message_load']);
                $this->add_hook('message_objects', [$this, 'message_objects']);
                $this->add_hook('template_object_messagebody', [$this, 'html_output']);
            }
            else if ($rcmail->action == 'upload') {
                $this->add_hook('attachment_from_uri', [$this, 'attach_vcard']);
            }
            else if ($rcmail->action == 'compose' && !$rcmail->output->framed) {
                $this->add_texts('localization', true);
                $this->include_stylesheet($this->local_skin_path() . '/style.css');
                $this->include_script('vcardattach.js');
                $this->add_button([
                        'type'     => 'link',
                        'label'    => 'vcard_attachments.vcard',
                        'command'  => 'attach-vcard',
                        'class'    => 'listbutton vcard disabled',
                        'classact' => 'listbutton vcard',
                        'title'    => 'vcard_attachments.attachvcard',
                        'innerclass' => 'inner',
                    ],
                    'compose-contacts-toolbar'
                );

                $this->add_hook('message_compose', [$this, 'message_compose']);
            }
            else if (!$rcmail->output->framed && (!$rcmail->action || $rcmail->action == 'list')) {
                $this->include_stylesheet($this->local_skin_path() . '/style.css');
                $this->include_script('vcardattach.js');
            }
        }

        $this->register_action('plugin.savevcard', [$this, 'save_vcard']);
    }

    /**
     * Check message bodies and attachments for vcards
     */
    function message_load($p)
    {
        $this->message = $p['object'];

        // handle attachments vcard attachments
        foreach ((array) $this->message->attachments as $attachment) {
            if ($this->is_vcard($attachment)) {
                $this->vcard_parts[] = $attachment->mime_id;
            }
        }
        // the same with message bodies
        foreach ((array) $this->message->parts as $part) {
            if ($this->is_vcard($part)) {
                $this->vcard_parts[]  = $part->mime_id;
                $this->vcard_bodies[] = $part->mime_id;
            }
        }

        if ($this->vcard_parts) {
            $this->add_texts('localization');
        }
    }

    /**
     * This callback function adds a box above the message content
     * if there is a vcard attachment available
     */
    function message_objects($p)
    {
        $rcmail   = rcmail::get_instance();
        $contacts = [];

        foreach ($this->vcard_parts as $part) {
            $vcards = rcube_vcard::import($this->message->get_part_content($part, null, true));

            foreach ($vcards as $idx => $vcard) {
                // skip invalid vCards
                if (empty($vcard->email) || empty($vcard->email[0])) {
                    continue;
                }

                $contacts["$part:$idx"] = "{$vcard->displayname} <{$vcard->email[0]}>";
            }
        }

        if (!empty($contacts)) {
            $attr = [
                'title' => $this->gettext('addvcardmsg'),
                'class' => 'import btn-sm',
            ];

            if (count($contacts) == 1) {
                $display         = array_first($contacts);
                $attr['onclick'] = "return plugin_vcard_import('" . rcube::JQ(key($contacts)) . "')";
            }
            else {
                $display         = $this->gettext(['name' => 'contactsattached', 'vars' => ['num' => count($contacts)]]);
                $attr['onclick'] = "return plugin_vcard_import()";

                $rcmail->output->set_env('vcards', $contacts);
                $rcmail->output->add_label('vcard_attachments.addvcardmsg', 'import');
            }

            // add box below the message body
            $p['content'][] = html::p(
                ['class' => 'vcardattachment aligned-buttons boxinformation'],
                html::span(null, rcube::Q($display)) . html::tag('button', $attr, rcube::Q($rcmail->gettext('import')))
            );

            $this->include_script('vcardattach.js');
            $this->include_stylesheet($this->local_skin_path() . '/style.css');
        }

        return $p;
    }

    /**
     * This callback function adds a vCard to the message when attached from the Address book
     */
    function message_compose($p)
    {
        if (
            rcube_utils::get_input_string('_attach_vcard', rcube_utils::INPUT_GET) == '1'
            && ($uri = rcube_utils::get_input_string('_uri', rcube_utils::INPUT_GET))
        ) {
            if ($attachment = $this->attach_vcard(['compose_id' => $p['id'], 'uri' => $uri])) {
                $p['attachments'][] = $attachment;
            };
        }

        return $p;
    }

    /**
     * This callback function removes message part's content
     * for parts that are vcards
     */
    function html_output($p)
    {
        foreach ($this->vcard_parts as $part) {
            // remove part's body
            if (in_array($part, $this->vcard_bodies)) {
                $p['content'] = '';
            }
        }

        return $p;
    }

    /**
     * Handler for request action
     */
    function save_vcard()
    {
        $this->add_texts('localization');

        $uid     = rcube_utils::get_input_string('_uid', rcube_utils::INPUT_POST);
        $mbox    = rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_POST);
        $mime_id = rcube_utils::get_input_string('_part', rcube_utils::INPUT_POST);

        $rcmail  = rcmail::get_instance();
        $message = new rcube_message($uid, $mbox);
        $vcards  = [];
        $errors  = 0;

        if (!empty($message->headers) && $mime_id) {
            $index = [];

            foreach (explode(',', $mime_id) as $id) {
                list($part_id, $card_id) = rcube_utils::explode(':', $id);
                if (!isset($index[$part_id])) {
                    $index[$part_id] = [];
                }
                $index[$part_id][] = $card_id;
            }

            foreach ($index as $part_id => $mime_ids) {
                $part = $message->get_part_content($part_id, null, true);

                if (!empty($part) && ($part_vcards = rcube_vcard::import($part))) {
                    foreach ($mime_ids as $id) {
                        if (!empty($part_vcards[$id])
                            && ($vcard = $part_vcards[$id])
                            && !empty($vcard->email)
                            && !empty($vcard->email[0])
                        ) {
                            $vcards[] = $vcard;
                        }
                    }
                }
            }
        }

        $CONTACTS = $this->get_address_book();

        foreach ($vcards as $vcard) {
            $email   = $vcard->email[0];
            $contact = $vcard->get_assoc();
            $valid   = true;

            // skip entries without an e-mail address or invalid
            if (empty($email) || !$CONTACTS->validate($contact, true)) {
                $valid = false;
            }
            else {
                // We're using UTF8 internally
                $email = rcube_utils::idn_to_utf8($email);

                // compare e-mail address
                $existing = $CONTACTS->search('email', $email, 1, false);

                // compare display name
                if (!$existing->count && !empty($vcard->displayname)) {
                    $existing = $CONTACTS->search('name', $vcard->displayname, 1, false);
                }

                if ($existing->count) {
                    // $rcmail->output->command('display_message', $this->gettext('contactexists'), 'warning');
                    $valid = false;
                }
            }

            if ($valid) {
                $plugin = $rcmail->plugins->exec_hook('contact_create', ['record' => $contact, 'source' => null]);
                $contact = $plugin['record'];

                if (!$plugin['abort'] && $CONTACTS->insert($contact)) {
                    // do nothing
                }
                else {
                    $errors++;
                }
            }
        }

        if ($errors || empty($vcards)) {
            $rcmail->output->command('display_message', $this->gettext('vcardsavefailed'), 'error');
        }
        else {
            $rcmail->output->command('display_message', $this->gettext('importedsuccessfully'), 'confirmation');
        }

        $rcmail->output->send();
    }

    /**
     * Checks if specified message part is a vcard data
     *
     * @param rcube_message_part $part Part object
     *
     * @return bool True if part is of type vcard
     */
    private static function is_vcard($part)
    {
        return (
            // Content-Type: text/vcard;
            $part->mimetype == 'text/vcard' ||
            // Content-Type: text/x-vcard;
            $part->mimetype == 'text/x-vcard' ||
            // Content-Type: text/directory; profile=vCard;
            ($part->mimetype == 'text/directory' && (
                    (!empty($part->ctype_parameters['profile']) && strtolower($part->ctype_parameters['profile']) == 'vcard')
            // Content-Type: text/directory; (with filename=*.vcf)
                    || (!empty($part->filename) && preg_match('/\.vcf$/i', $part->filename))
                )
            )
        );
    }

    /**
     * Getter for default (writable) addressbook
     */
    private function get_address_book()
    {
        if (!empty($this->abook)) {
            return $this->abook;
        }

        $rcmail = rcmail::get_instance();

        // Get configured addressbook
        $CONTACTS = $rcmail->get_address_book(rcube_addressbook::TYPE_DEFAULT, true);

        // Get first writeable addressbook if the configured doesn't exist
        // This can happen when user deleted the addressbook (e.g. Kolab folder)
        if (!is_object($CONTACTS)) {
            $source   = reset($rcmail->get_address_sources(true));
            $CONTACTS = $rcmail->get_address_book($source['id'], true);
        }

        return $this->abook = $CONTACTS;
    }

    /**
     * Attaches a contact vcard to composed mail
     */
    public function attach_vcard($args)
    {
        if (preg_match('|^vcard://(.+)$|', $args['uri'], $m)) {
            list($source, $cid, $email) = explode('-', $m[1]);

            $vcard = $this->get_contact_vcard($source, $cid, $filename);

            if ($vcard) {
                $params = [
                    'filename' => $filename,
                    'mimetype' => 'text/vcard',
                ];

                $args['attachment'] = rcmail_action_mail_compose::save_attachment($vcard, null, $args['compose_id'], $params);
            }
        }

        return $args;
    }

    /**
     * Get vcard data for specified contact
     */
    private function get_contact_vcard($source, $cid, &$filename = null)
    {
        $rcmail  = rcmail::get_instance();
        $source  = $rcmail->get_address_book($source);
        $contact = $source->get_record($cid, true);

        if ($contact) {
            $fieldmap = $source ? $source->vcard_map : null;

            if (empty($contact['vcard'])) {
                $vcard = new rcube_vcard('', RCUBE_CHARSET, false, $fieldmap);
                $vcard->reset();

                foreach ($contact as $key => $values) {
                    list($field, $section) = rcube_utils::explode(':', $key);
                    $section = strtoupper($section ?? '');
                    // avoid unwanted casting of DateTime objects to an array
                    // (same as in rcube_contacts::convert_save_data())
                    if (is_object($values) && is_a($values, 'DateTime')) {
                        $values = [$values];
                    }

                    foreach ((array) $values as $value) {
                        if (is_array($value) || is_a($value, 'DateTime') || @strlen($value)) {
                            $vcard->set($field, $value, $section);
                        }
                    }
                }

                $contact['vcard'] = $vcard->export();
            }

            $name     = rcube_addressbook::compose_list_name($contact);
            $filename = (self::parse_filename($name) ?: 'contact') . '.vcf';

            // fix folding and end-of-line chars
            $vcard = preg_replace('/\r|\n\s+/', '', $contact['vcard']);
            $vcard = preg_replace('/\n/', rcube_vcard::$eol, $vcard);

            return rcube_vcard::rfc2425_fold($vcard) . rcube_vcard::$eol;
        }
    }

    /**
     * Helper function to convert contact name into filename
     */
    static private function parse_filename($str)
    {
        $str = preg_replace('/[\t\n\r\0\x0B:\/]+\s*/', ' ', $str);

        return trim($str, " ./_");
    }
}
