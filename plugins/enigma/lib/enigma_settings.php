<?php

/**
 +-------------------------------------------------------------------------+
 | User Preferences handler for the Enigma Plugin                          |
 |                                                                         |
 | Copyright (C) The Roundcube Dev Team                                    |
 |                                                                         |
 | Licensed under the GNU General Public License version 3 or              |
 | any later version with exceptions for skins & plugins.                  |
 | See the README file for a full license statement.                       |
 +-------------------------------------------------------------------------+
 | Author: Aleksander Machniak <alec@alec.pl>                              |
 +-------------------------------------------------------------------------+
*/

class enigma_settings
{
    protected $plugin;
    protected $rc;

    // List of images (security background) - from FontAwesome
    protected static $images = array(
        'lock'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z"/></svg>',
        'key'      => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M512 176.001C512 273.203 433.202 352 336 352c-11.22 0-22.19-1.062-32.827-3.069l-24.012 27.014A23.999 23.999 0 0 1 261.223 384H224v40c0 13.255-10.745 24-24 24h-40v40c0 13.255-10.745 24-24 24H24c-13.255 0-24-10.745-24-24v-78.059c0-6.365 2.529-12.47 7.029-16.971l161.802-161.802C163.108 213.814 160 195.271 160 176 160 78.798 238.797.001 335.999 0 433.488-.001 512 78.511 512 176.001zM336 128c0 26.51 21.49 48 48 48s48-21.49 48-48-21.49-48-48-48-48 21.49-48 48z"/></svg>',
        'shield'   => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M466.5 83.7l-192-80a48.15 48.15 0 0 0-36.9 0l-192 80C27.7 91.1 16 108.6 16 128c0 198.5 114.5 335.7 221.5 380.3 11.8 4.9 25.1 4.9 36.9 0C360.1 472.6 496 349.3 496 128c0-19.4-11.7-36.9-29.5-44.3zM256.1 446.3l-.1-381 175.9 73.3c-3.3 151.4-82.1 261.1-175.8 307.7z"/></svg>',
        'envelope' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"/></svg>',
        'user'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M383.9 308.3l23.9-62.6c4-10.5-3.7-21.7-15-21.7h-58.5c11-18.9 17.8-40.6 17.8-64v-.3c39.2-7.8 64-19.1 64-31.7 0-13.3-27.3-25.1-70.1-33-9.2-32.8-27-65.8-40.6-82.8-9.5-11.9-25.9-15.6-39.5-8.8l-27.6 13.8c-9 4.5-19.6 4.5-28.6 0L182.1 3.4c-13.6-6.8-30-3.1-39.5 8.8-13.5 17-31.4 50-40.6 82.8-42.7 7.9-70 19.7-70 33 0 12.6 24.8 23.9 64 31.7v.3c0 23.4 6.8 45.1 17.8 64H56.3c-11.5 0-19.2 11.7-14.7 22.3l25.8 60.2C27.3 329.8 0 372.7 0 422.4v44.8C0 491.9 20.1 512 44.8 512h358.4c24.7 0 44.8-20.1 44.8-44.8v-44.8c0-48.4-25.8-90.4-64.1-114.1zM176 480l-41.6-192 49.6 32 24 40-32 120zm96 0l-32-120 24-40 49.6-32L272 480zm41.7-298.5c-3.9 11.9-7 24.6-16.5 33.4-10.1 9.3-48 22.4-64-25-2.8-8.4-15.4-8.4-18.3 0-17 50.2-56 32.4-64 25-9.5-8.8-12.7-21.5-16.5-33.4-.8-2.5-6.3-5.7-6.3-5.8v-10.8c28.3 3.6 61 5.8 96 5.8s67.7-2.1 96-5.8v10.8c-.1.1-5.6 3.2-6.4 5.8z"/></svg>',
    );

    // List of image scale options (security background)
    protected static $scale_options = array(
        10, 15, 20, 25, 30, 35, 40
    );

    // List of image angle (rotation) options (security background)
    protected static $angle_options = array(
        0, 45, 90, 135, 180, 225, 270, 315
    );


    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->rc     = rcube::get_instance();
    }

    /**
     * Handler for preferences_list hook.
     * Adds options blocks into Enigma settings sections in Preferences.
     *
     * @param array Original parameters
     *
     * @return array Modified parameters
     */
    public function preferences_list($p)
    {
        $no_override = array_flip((array)$this->rc->config->get('dont_override'));

        $p['blocks']['main']['name']  = $this->plugin->gettext('mainoptions');
        $p['blocks']['secbg']['name'] = $this->plugin->gettext('securitybg');

        if (!isset($no_override['enigma_encryption'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_encryption';
            $input    = new html_checkbox(array(
                    'name'  => '_enigma_encryption',
                    'id'    => $field_id,
                    'value' => 1,
            ));

            $p['blocks']['main']['options']['enigma_encryption'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('supportencryption')),
                'content' => $input->show(intval($this->rc->config->get('enigma_encryption'))),
            );
        }

        if (!isset($no_override['enigma_signatures'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_signatures';
            $input    = new html_checkbox(array(
                    'name'  => '_enigma_signatures',
                    'id'    => $field_id,
                    'value' => 1,
            ));

            $p['blocks']['main']['options']['enigma_signatures'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('supportsignatures')),
                'content' => $input->show(intval($this->rc->config->get('enigma_signatures'))),
            );
        }

        if (!isset($no_override['enigma_decryption'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_decryption';
            $input    = new html_checkbox(array(
                    'name'  => '_enigma_decryption',
                    'id'    => $field_id,
                    'value' => 1,
            ));

            $p['blocks']['main']['options']['enigma_decryption'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('supportdecryption')),
                'content' => $input->show(intval($this->rc->config->get('enigma_decryption'))),
            );
        }

        if (!isset($no_override['enigma_sign_all'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_sign_all';
            $input    = new html_checkbox(array(
                    'name'  => '_enigma_sign_all',
                    'id'    => $field_id,
                    'value' => 1,
            ));

            $p['blocks']['main']['options']['enigma_sign_all'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('signdefault')),
                'content' => $input->show($this->rc->config->get('enigma_sign_all') ? 1 : 0),
            );
        }

        if (!isset($no_override['enigma_encrypt_all'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_encrypt_all';
            $input    = new html_checkbox(array(
                    'name'  => '_enigma_encrypt_all',
                    'id'    => $field_id,
                    'value' => 1,
            ));

            $p['blocks']['main']['options']['enigma_encrypt_all'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('encryptdefault')),
                'content' => $input->show($this->rc->config->get('enigma_encrypt_all') ? 1 : 0),
            );
        }

        if (!isset($no_override['enigma_attach_pubkey'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_attach_pubkey';
            $input    = new html_checkbox(array(
                    'name'  => '_enigma_attach_pubkey',
                    'id'    => $field_id,
                    'value' => 1,
            ));

            $p['blocks']['main']['options']['enigma_attach_pubkey'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('attachpubkeydefault')),
                'content' => $input->show($this->rc->config->get('enigma_attach_pubkey') ? 1 : 0),
            );
        }

        if (!isset($no_override['enigma_password_time'])) {
            if (!$p['current']) {
                $p['blocks']['main']['content'] = true;
                return $p;
            }

            $field_id = 'rcmfd_enigma_password_time';
            $select   = new html_select(array('name' => '_enigma_password_time', 'id' => $field_id));

            foreach (array(1, 5, 10, 15, 30) as $m) {
                $label = $this->plugin->gettext(array('name' => 'nminutes', 'vars' => array('m' => $m)));
                $select->add($label, $m);
            }
            $select->add($this->plugin->gettext('wholesession'), 0);

            $p['blocks']['main']['options']['enigma_password_time'] = array(
                'title'   => html::label($field_id, $this->plugin->gettext('passwordtime')),
                'content' => $select->show(intval($this->rc->config->get('enigma_password_time'))),
            );
        }

        if (!$p['current']) {
            $p['blocks']['secbg']['content'] = true;
            return $p;
        }

        $prefs = self::security_bg_settings();

        $field_id = 'rcmfd_enigma_bg_icon';
        $select   = new html_select(array('name' => '_enigma_bg_icon', 'id' => $field_id));

        foreach (array_keys(self::$images) as $icon) {
            $select->add($icon, $icon);
        }

        $p['blocks']['secbg']['options']['enigma_bg_icon'] = array(
            'title'   => html::label($field_id, $this->plugin->gettext('bgicon')),
            'content' => $select->show($prefs['enigma_bg_icon']),
        );

        foreach (array('bg_scale' => self::$scale_options, 'bg_angle' => self::$angle_options) as $item => $list) {
            $opt_name = "enigma_$item";
            $field_id = "rcmfd_$opt_name";
            $range    = new html_inputfield(array('type' => 'range', 'name' => "_$opt_name", 'id' => $field_id,
                'min' => 0, 'max' => count($list)-1, 'step' => 1));

            $p['blocks']['secbg']['options'][$opt_name] = array(
                'title'   => html::label($field_id, $this->plugin->gettext(str_replace('_', '', $item))),
                'content' => $range->show((int) $prefs[$opt_name]),
            );
        }

        $p['blocks']['secbg']['options']['enigma_bg_preview'] = array(
            'class'   => 'enigma-bg-preview',
            'title'   => html::label(null, $this->plugin->gettext('bgpreview')),
            'content' => html::div(array('id' => 'enigma-message', 'class' => 'boxconfirmation enigmanotice encrypted'), $this->plugin->gettext('samplebox')),
        );

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
    public function preferences_save($p)
    {
        if ($p['section'] == 'enigma') {
            $p['prefs'] = array(
                'enigma_signatures'    => (bool) rcube_utils::get_input_value('_enigma_signatures', rcube_utils::INPUT_POST),
                'enigma_decryption'    => (bool) rcube_utils::get_input_value('_enigma_decryption', rcube_utils::INPUT_POST),
                'enigma_encryption'    => (bool) rcube_utils::get_input_value('_enigma_encryption', rcube_utils::INPUT_POST),
                'enigma_sign_all'      => (bool) rcube_utils::get_input_value('_enigma_sign_all', rcube_utils::INPUT_POST),
                'enigma_encrypt_all'   => (bool) rcube_utils::get_input_value('_enigma_encrypt_all', rcube_utils::INPUT_POST),
                'enigma_attach_pubkey' => (bool) rcube_utils::get_input_value('_enigma_attach_pubkey', rcube_utils::INPUT_POST),
                'enigma_password_time' => (int) rcube_utils::get_input_value('_enigma_password_time', rcube_utils::INPUT_POST),
                'enigma_bg_icon'       => rcube_utils::get_input_value('_enigma_bg_icon', rcube_utils::INPUT_POST),
                'enigma_bg_scale'      => (int) rcube_utils::get_input_value('_enigma_bg_scale', rcube_utils::INPUT_POST),
                'enigma_bg_angle'      => (int) rcube_utils::get_input_value('_enigma_bg_angle', rcube_utils::INPUT_POST),
            );

            if (!preg_match('/^[a-z]+$/', $p['prefs']['enigma_bg_icon'])) {
                unset($p['prefs']['enigma_bg_icon']);
            }
        }

        return $p;
    }

    /**
     * Get the security background configuration. If there's no configuration
     * we'll generate random values and save them as user preferences.
     *
     * @return array Configuration options
     */
    protected static function security_bg_settings()
    {
        $rcube = rcube::get_instance();

        $prefs = array(
            'enigma_bg_icon'  => $rcube->config->get('enigma_bg_icon'),
            'enigma_bg_scale' => $rcube->config->get('enigma_bg_scale'),
            'enigma_bg_angle' => $rcube->config->get('enigma_bg_angle'),
        );

        if (empty($prefs['enigma_bg_icon'])) {
            $images = array_keys(self::$images);

            $prefs['enigma_bg_icon']  = $images[mt_rand(0, count($images)-1)];
            $prefs['enigma_bg_scale'] = mt_rand(0, count(self::$scale_options)-1);
            $prefs['enigma_bg_angle'] = mt_rand(0, count(self::$angle_options)-1);

            $rcube->user->save_prefs($prefs);
        }

        return $prefs;
    }

    /**
     * Generate css rule with security background
     *
     * @param bool $post Replace user preferences with POST arguments
     *
     * @return string CSS rule
     */
    public static function security_bg_style($post = false)
    {
        // get user preferences
        $prefs = self::security_bg_settings();

        // use POSTed values (testing background in Preferences > Encryption)
        if ($post) {
            if (isset($_POST['_enigma_bg_icon'])) {
                $prefs['enigma_bg_icon'] = rcube_utils::get_input_value('_enigma_bg_icon', rcube_utils::INPUT_POST);
            }
            if (isset($_POST['_enigma_bg_scale'])) {
                $prefs['enigma_bg_scale'] = (int) rcube_utils::get_input_value('_enigma_bg_scale', rcube_utils::INPUT_POST);
            }
            if (isset($_POST['_enigma_bg_angle'])) {
                $prefs['enigma_bg_angle'] = (int) rcube_utils::get_input_value('_enigma_bg_angle', rcube_utils::INPUT_POST);
            }
        }

        $angle = self::$angle_options[$prefs['enigma_bg_angle']];
        $scale = self::$scale_options[$prefs['enigma_bg_scale']] ?: 10;
        $icon  = self::$images[$prefs['enigma_bg_icon']] ?: self::$images['lock'];

        // Get image viewport size for rotation arguments
        $width = $height = 256;
        if (preg_match('/viewbox="[0-9]+ [0-9]+ ([0-9]+) ([0-9]+)"/i', $icon, $m)) {
            $width  = intval($m[1] / 2);
            $height = intval($m[2] / 2);
        }

        // Modify the background image:
        // - apply angle (rotation)
        // - scale the image to get a nice margin around it
        // - apply semi-transparent color
        $icon  = str_replace('<path', sprintf('<path transform="rotate(%s,%d,%d) scale(.9)" fill="rgba(0,0,0,.05)"',
            $angle, $width, $height), $icon);

        // Return the css rules, apply scale via background-size
        return sprintf('background-size: %dpx; background-image: url(\'data:image/svg+xml;us-ascii,%s\');', $scale, $icon);
    }
}
