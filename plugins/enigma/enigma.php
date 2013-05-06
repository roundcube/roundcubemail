<?php
/*
 +-------------------------------------------------------------------------+
 | Enigma Plugin for Roundcube                                             |
 | Version 0.1                                                             |
 |                                                                         |
 | This program is free software; you can redistribute it and/or modify    |
 | it under the terms of the GNU General Public License version 2          |
 | as published by the Free Software Foundation.                           |
 |                                                                         |
 | This program is distributed in the hope that it will be useful,         |
 | but WITHOUT ANY WARRANTY; without even the implied warranty of          |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
 | GNU General Public License for more details.                            |
 |                                                                         |
 | You should have received a copy of the GNU General Public License along |
 | with this program; if not, write to the Free Software Foundation, Inc., |
 | 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
 |                                                                         |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

/*
    This class contains only hooks and action handlers.
    Most plugin logic is placed in enigma_engine and enigma_ui classes.
*/

class enigma extends rcube_plugin
{
    public $task = 'mail|settings';
    public $rc;
    public $engine;

    private $env_loaded;
    private $message;
    private $keys_parts = array();
    private $keys_bodies = array();


    /**
     * Plugin initialization.
     */
    function init()
    {
        $rcmail = rcmail::get_instance();
        $this->rc = $rcmail;

        $section = rcube_utils::get_input_value('_section', rcube_utils::INPUT_GET);

        if ($this->rc->task == 'mail') {
            // message parse/display hooks
            $this->add_hook('message_part_structure', array($this, 'parse_structure'));
            $this->add_hook('message_body_prefix', array($this, 'status_message'));

            // message displaying
            if ($rcmail->action == 'show' || $rcmail->action == 'preview') {
                $this->add_hook('message_load', array($this, 'message_load'));
                $this->add_hook('template_object_messagebody', array($this, 'message_output'));
                $this->register_action('plugin.enigmaimport', array($this, 'import_file'));
            }
            // message composing
            else if ($rcmail->action == 'compose') {
                $this->load_ui();
                $this->ui->init($section);
            }
            // message sending (and draft storing)
            else if ($rcmail->action == 'sendmail') {
                //$this->add_hook('outgoing_message_body', array($this, 'msg_encode'));
                //$this->add_hook('outgoing_message_body', array($this, 'msg_sign'));
            }
        }
        else if ($this->rc->task == 'settings') {
            // add hooks for Enigma settings
            $this->add_hook('preferences_sections_list', array($this, 'preferences_section'));
            $this->add_hook('preferences_list', array($this, 'preferences_list'));
            $this->add_hook('preferences_save', array($this, 'preferences_save'));

            // register handler for keys/certs management
            $this->register_action('plugin.enigma', array($this, 'preferences_ui'));

            // grab keys/certs management iframe requests
            if ($this->rc->action == 'edit-prefs' && preg_match('/^enigma(certs|keys)/', $section)) {
                $this->load_ui();
                $this->ui->init($section);
            }
        }
    }

    /**
     * Plugin environment initialization.
     */
    function load_env()
    {
        if ($this->env_loaded)
            return;

        $this->env_loaded = true;

        // Add include path for Enigma classes and drivers
        $include_path = $this->home . '/lib' . PATH_SEPARATOR;
        $include_path .= ini_get('include_path');
        set_include_path($include_path);

        // load the Enigma plugin configuration
        $this->load_config();

        // include localization (if wasn't included before)
        $this->add_texts('localization/');
    }

    /**
     * Plugin UI initialization.
     */
    function load_ui()
    {
        if ($this->ui)
            return;

        // load config/localization
        $this->load_env();

        // Load UI
        $this->ui = new enigma_ui($this, $this->home);
    }

    /**
     * Plugin engine initialization.
     */
    function load_engine()
    {
        if ($this->engine)
            return;

        // load config/localization
        $this->load_env();

        $this->engine = new enigma_engine($this);
    }

    /**
     * Handler for message_part_structure hook.
     * Called for every part of the message.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function parse_structure($p)
    {
        $struct = $p['structure'];

        if ($p['mimetype'] == 'text/plain' || $p['mimetype'] == 'application/pgp') {
            $this->parse_plain($p);
        }
        else if ($p['mimetype'] == 'multipart/signed') {
            $this->parse_signed($p);
        }
        else if ($p['mimetype'] == 'multipart/encrypted') {
            $this->parse_encrypted($p);
        }
        else if ($p['mimetype'] == 'application/pkcs7-mime') {
            $this->parse_encrypted($p);
        }

        return $p;
    }

    /**
     * Handler for preferences_sections_list hook.
     * Adds Enigma settings sections into preferences sections list.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_section($p)
    {
        // add labels
        $this->add_texts('localization/');

        $p['list']['enigmasettings'] = array(
            'id' => 'enigmasettings', 'section' => $this->gettext('enigmasettings'),
        );
        $p['list']['enigmacerts'] = array(
            'id' => 'enigmacerts', 'section' => $this->gettext('enigmacerts'),
        );
        $p['list']['enigmakeys'] = array(
            'id' => 'enigmakeys', 'section' => $this->gettext('enigmakeys'),
        );

        return $p;
    }

    /**
     * Handler for preferences_list hook.
     * Adds options blocks into Enigma settings sections in Preferences.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_list($p)
    {
        if ($p['section'] == 'enigmasettings') {
            // This makes that section is not removed from the list
            $p['blocks']['dummy']['options']['dummy'] = array();
        }
        else if ($p['section'] == 'enigmacerts') {
            // This makes that section is not removed from the list
            $p['blocks']['dummy']['options']['dummy'] = array();
        }
        else if ($p['section'] == 'enigmakeys') {
            // This makes that section is not removed from the list
            $p['blocks']['dummy']['options']['dummy'] = array();
        }

        return $p;
    }

    /**
     * Handler for preferences_save hook.
     * Executed on Enigma settings form submit.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function preferences_save($p)
    {
        if ($p['section'] == 'enigmasettings') {
            $a['prefs'] = array(
//                'dummy' => get_input_value('_dummy', RCUBE_INPUT_POST),
            );
        }

        return $p;
    }

    /**
     * Handler for keys/certs management UI template.
     */
    function preferences_ui()
    {
        $this->load_ui();
        $this->ui->init();
    }

    /**
     * Handler for message_body_prefix hook.
     * Called for every displayed (content) part of the message.
     * Adds infobox about signature verification and/or decryption
     * status above the body.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    function status_message($p)
    {
        $part_id = $p['part']->mime_id;

        // skip: not a message part
        if ($p['part'] instanceof rcube_message)
            return $p;

        // skip: message has no signed/encoded content
        if (!$this->engine)
            return $p;

        // Decryption status
        if (isset($this->engine->decryptions[$part_id])) {

            // get decryption status
            $status = $this->engine->decryptions[$part_id];

            // Load UI and add css script
            $this->load_ui();
            $this->ui->add_css();

            // display status info
            $attrib['id'] = 'enigma-message';

            if ($status instanceof enigma_error) {
                $attrib['class'] = 'enigmaerror';
                $code = $status->getCode();
                if ($code == enigma_error::E_KEYNOTFOUND)
                    $msg = Q(str_replace('$keyid', enigma_key::format_id($status->getData('id')),
                        $this->gettext('decryptnokey')));
                else if ($code == enigma_error::E_BADPASS)
                    $msg = Q($this->gettext('decryptbadpass'));
                else
                    $msg = Q($this->gettext('decrypterror'));
            }
            else {
                $attrib['class'] = 'enigmanotice';
                $msg = Q($this->gettext('decryptok'));
            }

            $p['prefix'] .= html::div($attrib, $msg);
        }

        // Signature verification status
        if (isset($this->engine->signed_parts[$part_id])
            && ($sig = $this->engine->signatures[$this->engine->signed_parts[$part_id]])
        ) {
            // add css script
            $this->load_ui();
            $this->ui->add_css();

            // display status info
            $attrib['id'] = 'enigma-message';

            if ($sig instanceof enigma_signature) {
                if ($sig->valid) {
                    $attrib['class'] = 'enigmanotice';
                    $sender = ($sig->name ? $sig->name . ' ' : '') . '<' . $sig->email . '>';
                    $msg = Q(str_replace('$sender', $sender, $this->gettext('sigvalid')));
                }
                else {
                    $attrib['class'] = 'enigmawarning';
                    $sender = ($sig->name ? $sig->name . ' ' : '') . '<' . $sig->email . '>';
                    $msg = Q(str_replace('$sender', $sender, $this->gettext('siginvalid')));
                }
            }
            else if ($sig->getCode() == enigma_error::E_KEYNOTFOUND) {
                $attrib['class'] = 'enigmawarning';
                $msg = Q(str_replace('$keyid', enigma_key::format_id($sig->getData('id')),
                    $this->gettext('signokey')));
            }
            else {
                $attrib['class'] = 'enigmaerror';
                $msg = Q($this->gettext('sigerror'));
            }
/*
            $msg .= '&nbsp;' . html::a(array('href' => "#sigdetails",
                'onclick' => JS_OBJECT_NAME.".command('enigma-sig-details')"),
                Q($this->gettext('showdetails')));
*/
            // test
//            $msg .= '<br /><pre>'.$sig->body.'</pre>';

            $p['prefix'] .= html::div($attrib, $msg);

            // Display each signature message only once
            unset($this->engine->signatures[$this->engine->signed_parts[$part_id]]);
        }

        return $p;
    }

    /**
     * Handler for plain/text message.
     *
     * @param array Reference to hook's parameters (see enigma::parse_structure())
     */
    private function parse_plain(&$p)
    {
        $this->load_engine();
        $this->engine->parse_plain($p);
    }
    
    /**
     * Handler for multipart/signed message.
     * Verifies signature.
     *
     * @param array Reference to hook's parameters (see enigma::parse_structure())
     */
    private function parse_signed(&$p)
    {
        $this->load_engine();
        $this->engine->parse_signed($p);
    }

    /**
     * Handler for multipart/encrypted and application/pkcs7-mime message.
     *
     * @param array Reference to hook's parameters (see enigma::parse_structure())
     */
    private function parse_encrypted(&$p)
    {
        $this->load_engine();
        $this->engine->parse_encrypted($p);
    }
    
    /**
     * Handler for message_load hook.
     * Check message bodies and attachments for keys/certs.
     */
    function message_load($p)
    {
        $this->message = $p['object'];
    
        // handle attachments vcard attachments
        foreach ((array)$this->message->attachments as $attachment) {
            if ($this->is_keys_part($attachment)) {
                $this->keys_parts[] = $attachment->mime_id;
            }
        }
        // the same with message bodies
        foreach ((array)$this->message->parts as $idx => $part) {
            if ($this->is_keys_part($part)) {
                $this->keys_parts[] = $part->mime_id;
                $this->keys_bodies[] = $part->mime_id;
            }
        }
        // @TODO: inline PGP keys

        if ($this->keys_parts) {
            $this->add_texts('localization');
        }
    }

    /**
     * Handler for template_object_messagebody hook.
     * This callback function adds a box below the message content
     * if there is a key/cert attachment available
     */
    function message_output($p)
    {
        $attach_script = false;

        foreach ($this->keys_parts as $part) {

            // remove part's body
            if (in_array($part, $this->keys_bodies))
                $p['content'] = '';

            $style = "margin:0 1em; padding:0.2em 0.5em; border:1px solid #999; width: auto"
                ." border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px";

            // add box below message body
            $p['content'] .= html::p(array('style' => $style),
                html::a(array(
                    'href' => "#",
                    'onclick' => "return ".JS_OBJECT_NAME.".enigma_import_attachment('".JQ($part)."')",
                    'title' => $this->gettext('keyattimport')),
                    html::img(array('src' => $this->url('skins/classic/key_add.png'), 'style' => "vertical-align:middle")))
                . ' ' . html::span(null, $this->gettext('keyattfound')));

            $attach_script = true;
        }

        if ($attach_script) {
            $this->include_script('enigma.js');
        }

        return $p;
    }

    /**
     * Handler for attached keys/certs import
     */
    function import_file()
    {
        $this->load_engine();
        $this->engine->import_file();
    }

    /**
     * Checks if specified message part is a PGP-key or S/MIME cert data
     *
     * @param rcube_message_part Part object
     *
     * @return boolean True if part is a key/cert
     */
    private function is_keys_part($part)
    {
        // @TODO: S/MIME
        return (
            // Content-Type: application/pgp-keys
            $part->mimetype == 'application/pgp-keys'
        );
    }
}
