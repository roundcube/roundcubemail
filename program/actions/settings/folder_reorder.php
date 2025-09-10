<?php

class rcmail_action_settings_folder_reorder extends rcmail_action_settings_folders
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    #[\Override]
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();
        $list = array_flip(rcube_utils::get_input_value('folderorder', rcube_utils::INPUT_POST, true));
        $rcmail->user->save_prefs(['folder_order' => $list]);

        $rcmail->output->show_message('successfullysaved', 'confirmation');
        $rcmail->output->send();
    }
}
