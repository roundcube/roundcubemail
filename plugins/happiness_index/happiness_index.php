<?php

/**
 * Happiness Index
 *
 * The Happiness Index Plugin adds a happiness index rating scale to the message toolbar.
 *
 * This allows users to rate their happiness from 1-5 alongside sending a message.
 */

class happiness_index extends rcube_plugin
{
    public function init()
    {
        $this->load_config();
        $this->include_script("happiness_button.js");

        $this->add_hook('message_compose', [$this, 'add_happiness_button']);
        $this->add_hook('message_sent', [$this, 'send_happiness_index']);
    }

    //Add Happiness Index select to the compose message toolbar
    public function add_happiness_button()
    {
        $this->add_button([
            'type'      => 'link',
            'label'     => 'Happiness',
            'command'   => 'addHappinessButton',
            'class'     => 'happiness-button',
        ],'compose-toolbar');
    }

    //Get the happiness index as a header when sent

    public function send_happiness_index($args) {
        $output = $args['output'];
        $output->incude_script("happiness_button.js");
        if(isset($_POST['selectedValue'])) {
            $selectedValue = $_POST['selectedValue'];
        }
        else $selectedValue = 0;

        $output->add_header('x-happiness', $selectedValue);
    }
}
