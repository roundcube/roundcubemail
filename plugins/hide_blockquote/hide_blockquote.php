<?php

/**
 * Quotation block hiding
 *
 * Plugin that adds a possibility to hide long blocks of cited text in messages.
 *
 * Configuration:
 * // Minimum number of citation lines. Longer citation blocks will be hidden.
 * // 0 - no limit (no hiding).
 * $config['hide_blockquote_limit'] = 0;
 *
 * @license GNU GPLv3+
 * @author Aleksander Machniak <alec@alec.pl>
 */
class hide_blockquote extends rcube_plugin
{
    public $task = 'mail|settings';

    /**
     * Plugin initialization
     */
    function init()
    {
        $rcmail = rcmail::get_instance();

        if ($rcmail->task == 'mail'
            && ($rcmail->action == 'preview' || $rcmail->action == 'show')
            && ($limit = $rcmail->config->get('hide_blockquote_limit'))
        ) {
            // include styles
            $this->include_stylesheet($this->local_skin_path() . "/style.css");

            // Script and localization
            $this->include_script('hide_blockquote.js');
            $this->add_texts('localization', true);

            // set env variable for client
            $rcmail->output->set_env('blockquote_limit', $limit);
        }
        else if ($rcmail->task == 'settings') {
            $dont_override = $rcmail->config->get('dont_override', []);
            if (!in_array('hide_blockquote_limit', $dont_override)) {
                $this->add_hook('preferences_list', [$this, 'prefs_table']);
                $this->add_hook('preferences_save', [$this, 'prefs_save']);
            }
        }
    }

    /**
     * Hook to inject plugin-specific user settings
     *
     * @param array $args Hook arguments
     *
     * @return array Modified hook arguments
     */
    function prefs_table($args)
    {
        if ($args['section'] != 'mailview') {
            return $args;
        }

        $this->add_texts('localization');

        $rcmail   = rcmail::get_instance();
        $limit    = (int) $rcmail->config->get('hide_blockquote_limit');
        $field_id = 'hide_blockquote_limit';
        $input    = new html_inputfield([
                'name'  => '_' . $field_id,
                'id'    => $field_id,
                'size'  => 5,
                'class' => 'form-control'
        ]);

        $args['blocks']['main']['options']['hide_blockquote_limit'] = [
            'title'   => html::label($field_id, $this->gettext('quotelimit')),
            'content' => $input->show($limit ?: '')
        ];

        return $args;
    }

    /**
     * Hook to save plugin-specific user settings
     *
     * @param array $args Hook arguments
     *
     * @return array Modified hook arguments
     */
    function prefs_save($args)
    {
        if ($args['section'] == 'mailview') {
            $args['prefs']['hide_blockquote_limit'] = (int) rcube_utils::get_input_value('_hide_blockquote_limit', rcube_utils::INPUT_POST);
        }

        return $args;
    }
}
