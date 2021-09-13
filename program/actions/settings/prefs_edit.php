<?php

/**
 +-----------------------------------------------------------------------+
 | This file is part of the Roundcube Webmail client                     |
 |                                                                       |
 | Copyright (C) The Roundcube Dev Team                                  |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Provide functionality for user's settings & preferences             |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_settings_prefs_edit extends rcmail_action_settings_index
{
    protected static $mode = self::MODE_HTTP;
    protected static $section;
    protected static $sections;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $rcmail->output->set_pagetitle($rcmail->gettext('preferences'));

        self::$section = rcube_utils::get_input_string('_section', rcube_utils::INPUT_GPC);
        list(self::$sections,) = self::user_prefs(self::$section);

        // register UI objects
        $rcmail->output->add_handlers([
                'userprefs'   => [$this, 'user_prefs_form'],
                'sectionname' => [$this, 'prefs_section_name'],
        ]);

        $rcmail->output->send('settingsedit');
    }

    public static function user_prefs_form($attrib)
    {
        $rcmail = rcmail::get_instance();

        // add some labels to client
        $rcmail->output->add_label('nopagesizewarning', 'nosupporterror');

        unset($attrib['form']);

        $hidden = ['name' => '_section', 'value' => self::$section];
        list($form_start, $form_end) = self::get_form_tags($attrib, 'save-prefs', null, $hidden);

        $out = $form_start;

        if (!empty(self::$sections[self::$section]['header'])) {
            $div_attr = ['id' => 'preferences-header', 'class' =>'boxcontent'];
            $out .= html::div($div_attr, self::$sections[self::$section]['header']);
        }

        foreach (self::$sections[self::$section]['blocks'] as $class => $block) {
            if (!empty($block['options'])) {
                $table = new html_table(['cols' => 2]);

                foreach ($block['options'] as $option) {
                    if (isset($option['title'])) {
                        $table->add('title', $option['title']);
                        $table->add(null, $option['content']);
                    }
                    else {
                        $table->add(['colspan' => 2], $option['content']);
                    }
                }

                $out .= html::tag('fieldset', $class, html::tag('legend', null, $block['name']) . $table->show($attrib));
            }
            else if (!empty($block['content'])) {
                $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $block['content']);
            }
        }

        return $out . $form_end;
    }

    public static function prefs_section_name()
    {
        return self::$sections[self::$section]['section'];
    }
}
