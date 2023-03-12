<?php

/**
 * Present identities settings dialog to new users
 *
 * When a new user is created, this plugin checks the default identity
 * and sets a session flag in case it is incomplete. An overlay box will appear
 * on the screen until the user has reviewed/completed his identity.
 *
 * @license GNU GPLv3+
 * @author Thomas Bruederli
 * @author Aleksander Machniak
 */
class new_user_dialog extends rcube_plugin
{
    public $task    = '';
    public $noframe = true;


    function init()
    {
        $this->add_hook('identity_create', [$this, 'create_identity']);
        $this->add_hook('render_page', [$this, 'render_page']);
        $this->register_action('plugin.newusersave', [$this, 'save_data']);
    }

    /**
     * Check newly created identity at first login
     */
    function create_identity($p)
    {
        // set session flag when a new user was created and the default identity seems to be incomplete
        if (!empty($p['login']) && empty($p['complete']) && !empty($p['record']['standard'])) {
            $rcmail = rcmail::get_instance();
            $rcmail->user->save_prefs(['newuserdialog' => true]);
        }
    }

    /**
     * Callback function when HTML page is rendered
     * We'll add an overlay box here.
     */
    function render_page($p)
    {
        $rcmail = rcmail::get_instance();

        if ($p['template'] != 'login' && $rcmail->config->get('newuserdialog')) {
            $this->add_texts('localization');

            $identity         = $rcmail->user->get_identity();
            $identities_level = intval($rcmail->config->get('identities_level', 0));

            // compose user-identity dialog
            $table = new html_table(['cols' => 2, 'class' => 'propform']);

            $table->add('title', html::label('newuserdialog-name', $this->gettext('name')));
            $table->add(null, html::tag('input', [
                    'id'       => 'newuserdialog-name',
                    'type'     => 'text',
                    'name'     => '_name',
                    'value'    => $identity['name'],
                    'disabled' => $identities_level == 4
                ])
            );

            $table->add('title', html::label('newuserdialog-email', $this->gettext('email')));
            $table->add(null, html::tag('input', [
                    'id'       => 'newuserdialog-email',
                    'type'     => 'text',
                    'name'     => '_email',
                    'value'    => rcube_utils::idn_to_utf8($identity['email']),
                    'disabled' => in_array($identities_level, [1, 3, 4])
                ])
            );

            $table->add('title', html::label('newuserdialog-org', $this->gettext('organization')));
            $table->add(null, html::tag('input', [
                    'id'       => 'newuserdialog-org',
                    'type'     => 'text',
                    'name'     => '_organization',
                    'value'    => $identity['organization'],
                    'disabled' => $identities_level == 4
                ])
            );

            $table->add('title', html::label('newuserdialog-sig', $this->gettext('signature')));
            $table->add(null, html::tag('textarea', [
                    'id'   => 'newuserdialog-sig',
                    'name' => '_signature',
                    'rows' => '5',
                ],
                $identity['signature']
            ));

            // add overlay input box to html page
            $rcmail->output->add_footer(html::tag('form', [
                    'id'     => 'newuserdialog',
                    'action' => $rcmail->url('plugin.newusersave'),
                    'method' => 'post',
                    'class'  => 'formcontent',
                    'style'  => 'display: none',
                ],
                html::p('hint', rcube::Q($this->gettext('identitydialoghint'))) . $table->show()
            ));

            $title  = rcube::JQ($this->gettext('identitydialogtitle'));
            $script = "
var newuserdialog = rcmail.show_popup_dialog($('#newuserdialog'), '$title', [{
    text: rcmail.get_label('save'),
    'class': 'mainaction save',
    click: function() {
      var request = {};
      $.each($('form', this).serializeArray(), function() {
        request[this.name] = this.value;
      });

      rcmail.http_post('plugin.newusersave', request, true);
      return false;
    }
  }],
  {
    resizable: false,
    closeOnEscape: false,
    width: 500,
    open: function() { $('#newuserdialog').show(); $('#newuserdialog-name').focus(); },
    beforeClose: function() { return false; }
  }
);
rcube_webmail.prototype.new_user_dialog_close = function() { newuserdialog.dialog('destroy'); };
";
            // disable keyboard events for messages list (#1486726)
            $rcmail->output->add_script($script, 'docready');
        }
    }

    /**
     * Handler for submitted form (ajax request)
     *
     * Check fields and save to default identity if valid.
     * Afterwards the session flag is removed and we're done.
     */
    function save_data()
    {
        $rcmail      = rcmail::get_instance();
        $identity    = $rcmail->user->get_identity();
        $ident_level = intval($rcmail->config->get('identities_level', 0));
        $disabled    = [];

        $save_data = [
            'name'         => rcube_utils::get_input_string('_name', rcube_utils::INPUT_POST),
            'email'        => rcube_utils::get_input_string('_email', rcube_utils::INPUT_POST),
            'organization' => rcube_utils::get_input_string('_organization', rcube_utils::INPUT_POST),
            'signature'    => rcube_utils::get_input_string('_signature', rcube_utils::INPUT_POST),
        ];

        if ($ident_level == 4) {
            $disabled = ['name', 'email', 'organization'];
        }
        else if (in_array($ident_level, [1, 3])) {
            $disabled = ['email'];
        }

        foreach ($disabled as $key) {
            $save_data[$key] = $identity[$key];
        }

        if (empty($save_data['name']) || empty($save_data['email'])) {
            $rcmail->output->show_message('formincomplete', 'error');
        }
        else if (!rcube_utils::check_email($save_data['email'] = rcube_utils::idn_to_ascii($save_data['email']))) {
            $rcmail->output->show_message('emailformaterror', 'error', ['email' => $save_data['email']]);
        }
        else {
            // execute hook
            $plugin = $rcmail->plugins->exec_hook('identity_update', [
                'id' => $identity['identity_id'],
                'record' => $save_data
            ]);

            if (!$plugin['abort']) {
                // save identity
                $updated = $rcmail->user->update_identity($plugin['id'], $plugin['record']);
            } else {
                $updated = $plugin['result'];
            }

            if ($updated) {
                // save prefs to not show dialog again
                $rcmail->user->save_prefs(['newuserdialog' => null]);
                // hide dialog
                $rcmail->output->command('new_user_dialog_close');
                $rcmail->output->show_message('successfullysaved', 'confirmation');
            } else {
                // show error
                $error = !empty($plugin['message']) ? $plugin['message'] : 'errorsaving';
                $rcmail->output->show_message($error, 'error', null, false);
            }
        }

        $rcmail->output->send();
    }
}
