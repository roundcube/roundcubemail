<?php

/**
 * jQuery UI
 *
 * Provide the jQuery UI library with according themes.
 *
 * @version 1.9.1
 * @author Cor Bosman <roundcube@wa.ter.net>
 * @author Thomas Bruederli <roundcube@gmail.com>
 */
class jqueryui extends rcube_plugin
{
    public $noajax = true;

    public function init()
    {
        $version = '1.9.1';

        $rcmail = rcmail::get_instance();
        $this->load_config();

        // include UI scripts
        $this->include_script("js/jquery-ui-$version.custom.min.js");

        // include UI stylesheet
        $skin = $rcmail->config->get('skin');
        $ui_map = $rcmail->config->get('jquery_ui_skin_map', array());
        $ui_theme = $ui_map[$skin] ? $ui_map[$skin] : $skin;

        if (file_exists($this->home . "/themes/$ui_theme/jquery-ui-$version.custom.css")) {
            $this->include_stylesheet("themes/$ui_theme/jquery-ui-$version.custom.css");
        }
        else {
            $this->include_stylesheet("themes/larry/jquery-ui-$version.custom.css");
        }

        if ($ui_theme == 'larry') {
            // patch dialog position function in order to fully fit the close button into the window
            $rcmail->output->add_script("jQuery.extend(jQuery.ui.dialog.prototype.options.position, {
                using: function(pos) {
                    var me = jQuery(this),
                        offset = me.css(pos).offset(),
                        topOffset = offset.top - 12;
                    if (topOffset < 0)
                        me.css('top', pos.top - topOffset);
                    if (offset.left + me.outerWidth() + 12 > jQuery(window).width())
                        me.css('left', pos.left - 12);
                }
            });", 'foot');
        }

        // jquery UI localization
        $jquery_ui_i18n = $rcmail->config->get('jquery_ui_i18n', array('datepicker'));
        if (count($jquery_ui_i18n) > 0) {
            $lang_l = str_replace('_', '-', substr($_SESSION['language'], 0, 5));
            $lang_s = substr($_SESSION['language'], 0, 2);
            foreach ($jquery_ui_i18n as $package) {
                if (file_exists($this->home . "/js/i18n/jquery.ui.$package-$lang_l.js")) {
                    $this->include_script("js/i18n/jquery.ui.$package-$lang_l.js");
                }
                else
                if (file_exists($this->home . "/js/i18n/jquery.ui.$package-$lang_s.js")) {
                    $this->include_script("js/i18n/jquery.ui.$package-$lang_s.js");
                }
            }
        }

        // Date format for datepicker
        $date_format = $rcmail->config->get('date_format', 'Y-m-d');
        $date_format = strtr($date_format, array(
                'y' => 'y',
                'Y' => 'yy',
                'm' => 'mm',
                'n' => 'm',
                'd' => 'dd',
                'j' => 'd',
        ));
        $rcmail->output->set_env('date_format', $date_format);
    }

}
