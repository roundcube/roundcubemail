<?php

/**
 * Sample plugin that adds a new tab to the settings section
 * to display some information about the current user
 */
class userinfo extends rcube_plugin
{
    public $task    = 'settings';
    public $noajax  = true;
    public $noframe = true;

    function init()
    {
        $this->add_texts('localization/', array('userinfo'));
        $this->add_hook('settings_actions', array($this, 'settings_actions'));
        $this->register_action('plugin.userinfo', array($this, 'infostep'));
    }

    function settings_actions($args)
    {
        $args['actions'][] = array(
            'action' => 'plugin.userinfo',
            'class'  => 'userinfo',
            'label'  => 'userinfo',
            'domain' => 'userinfo',
        );

        return $args;
    }

    function infostep()
    {
        $this->register_handler('plugin.body', array($this, 'infohtml'));

        $rcmail = rcmail::get_instance();
        $rcmail->output->set_pagetitle($this->gettext('userinfo'));
        $rcmail->output->send('plugin');
    }

    function infohtml()
    {
        $rcmail   = rcmail::get_instance();
        $user     = $rcmail->user;
        $identity = $user->get_identity();

        $table = new html_table(array('cols' => 2, 'class' => 'propform'));

        $table->add('title', html::label('', 'ID'));
        $table->add('', rcube::Q($user->ID));

        $table->add('title', html::label('', rcube::Q($this->gettext('username'))));
        $table->add('', rcube::Q($user->data['username']));

        $table->add('title', html::label('', rcube::Q($this->gettext('server'))));
        $table->add('', rcube::Q($user->data['mail_host']));

        $table->add('title', html::label('', rcube::Q($this->gettext('created'))));
        $table->add('', rcube::Q($user->data['created']));

        $table->add('title', html::label('', rcube::Q($this->gettext('lastlogin'))));
        $table->add('', rcube::Q($user->data['last_login']));

        $table->add('title', html::label('', rcube::Q($this->gettext('defaultidentity'))));
        $table->add('', rcube::Q($identity['name'] . ' <' . $identity['email'] . '>'));

        $legend = rcube::Q('Infos for ' . $user->get_username());
        $out    = html::tag('fieldset', '', html::tag('legend', '', $legend) . $table->show());

        return html::div(array('class' => 'box formcontent'),
            html::div(array('class' => 'boxtitle'), $this->gettext('userinfo'))
            . html::div(array('class' => 'boxcontent'), $out));
    }
}
