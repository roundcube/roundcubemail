<?php

/**
 * Emoticons.
 *
 * Plugin to replace emoticons in plain text message body with real emoji.
 * Also it enables emoticons in HTML compose editor. Both features are optional.
 *
 * @license GNU GPLv3+
 * @author Thomas Bruederli
 * @author Aleksander Machniak
 * @website https://roundcube.net
 */
class emoticons extends rcube_plugin
{
    public $task = 'mail|settings|utils';


    /**
     * Plugin initialization.
     */
    function init()
    {
        $rcube = rcube::get_instance();

        $this->add_hook('message_part_after', [$this, 'message_part_after']);
        $this->add_hook('html_editor', [$this, 'html_editor']);

        if ($rcube->task == 'settings') {
            $this->add_hook('preferences_list', [$this, 'preferences_list']);
            $this->add_hook('preferences_save', [$this, 'preferences_save']);
        }
    }

    /**
     * 'message_part_after' hook handler to replace common
     * plain text emoticons with emoji
     */
    function message_part_after($args)
    {
        if ($args['type'] == 'plain') {
            $this->load_config();

            $rcube = rcube::get_instance();
            if (!$rcube->config->get('emoticons_display', false)) {
                return $args;
            }

            $args['body'] = self::text2icons($args['body']);
        }

        return $args;
    }

    /**
     * 'html_editor' hook handler, where we enable emoticons in TinyMCE
     */
    function html_editor($args)
    {
        $rcube = rcube::get_instance();

        $this->load_config();

        if ($rcube->config->get('emoticons_compose', true)) {
            $args['extra_plugins'][] = 'emoticons';
            $args['extra_buttons'][] = 'emoticons';
        }

        return $args;
    }

    /**
     * 'preferences_list' hook handler
     */
    function preferences_list($args)
    {
        $rcube         = rcube::get_instance();
        $dont_override = $rcube->config->get('dont_override', []);

        if ($args['section'] == 'mailview' && !in_array('emoticons_display', $dont_override)) {
            $this->load_config();
            $this->add_texts('localization');

            $field_id = 'emoticons_display';
            $checkbox = new html_checkbox(['name' => '_' . $field_id, 'id' => $field_id, 'value' => 1]);

            $args['blocks']['main']['options']['emoticons_display'] = [
                    'title'   => html::label($field_id, $this->gettext('emoticonsdisplay')),
                    'content' => $checkbox->show(intval($rcube->config->get('emoticons_display', false)))
            ];
        }
        else if ($args['section'] == 'compose' && !in_array('emoticons_compose', $dont_override)) {
            $this->load_config();
            $this->add_texts('localization');

            $field_id = 'emoticons_compose';
            $checkbox = new html_checkbox(['name' => '_' . $field_id, 'id' => $field_id, 'value' => 1]);

            $args['blocks']['main']['options']['emoticons_compose'] = [
                    'title'   => html::label($field_id, $this->gettext('emoticonscompose')),
                    'content' => $checkbox->show(intval($rcube->config->get('emoticons_compose', true)))
            ];
        }

        return $args;
    }

    /**
     * 'preferences_save' hook handler
     */
    function preferences_save($args)
    {
        if ($args['section'] == 'mailview') {
            $args['prefs']['emoticons_display'] = (bool) rcube_utils::get_input_value('_emoticons_display', rcube_utils::INPUT_POST);
        }
        else if ($args['section'] == 'compose') {
            $args['prefs']['emoticons_compose'] = (bool) rcube_utils::get_input_value('_emoticons_compose', rcube_utils::INPUT_POST);
        }

        return $args;
    }

    /**
     * Replace common plain text emoticons with emoji
     *
     * @param string $text Text
     *
     * @return string Converted text
     */
    protected static function text2icons($text)
    {
        // This is a lookbehind assertion which will exclude html entities
        // E.g. situation when ";)" in "&quot;)" shouldn't be replaced by the icon
        // It's so long because of assertion format restrictions
        $entity = '(?<!&'
            . '[a-zA-Z0-9]{2}' . '|' . '#[0-9]{2}' . '|'
            . '[a-zA-Z0-9]{3}' . '|' . '#[0-9]{3}' . '|'
            . '[a-zA-Z0-9]{4}' . '|' . '#[0-9]{4}' . '|'
            . '[a-zA-Z0-9]{5}' . '|'
            . '[a-zA-Z0-9]{6}' . '|'
            . '[a-zA-Z0-9]{7}'
            . ')';

        // map of emoticon replacements
        $map = [
            '/(?<!mailto):-?D/'   => self::ico_tag('1f603', ':D'   ), // laugh
            '/:-?\(/'             => self::ico_tag('1f626', ':('   ), // frown
            '/'.$entity.';-?\)/'  => self::ico_tag('1f609', ';)'   ), // wink
            '/8-?\)/'             => self::ico_tag('1f60e', '8)'   ), // cool
            '/(?<!mailto):-?O/i'  => self::ico_tag('1f62e', ':O'   ), // surprised
            '/(?<!mailto):-?P/i'  => self::ico_tag('1f61b', ':P'   ), // tongue out
            '/(?<!mailto):-?@/i'  => self::ico_tag('1f631', ':-@'  ), // yell
            '/O:-?\)/i'           => self::ico_tag('1f607', 'O:-)' ), // innocent
            '/(?<!O):-?\)/'       => self::ico_tag('1f60a', ':-)'  ), // smile
            '/(?<!mailto):-?\$/'  => self::ico_tag('1f633', ':-$'  ), // embarrassed
            '/(?<!mailto):-?\*/i' => self::ico_tag('1f48b', ':-*'  ), // kiss
            '/(?<!mailto):-?S/i'  => self::ico_tag('1f615', ':-S'  ), // undecided
        ];

        return preg_replace(array_keys($map), array_values($map), $text);
    }

    protected static function ico_tag($ico, $title)
    {
        return html::span(['title' => $title], "&#x{$ico};");
    }
}
