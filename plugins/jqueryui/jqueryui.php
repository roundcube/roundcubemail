<?php

/**
 * jQuery UI
 *
 * Provide the jQuery UI library with according themes.
 *
 * @version 1.8.18
 * @author Cor Bosman <roundcube@wa.ter.net>
 * @author Thomas Bruederli <roundcube@gmail.com>
 */
class jqueryui extends rcube_plugin
{
    public $noajax = true;

    public function init()
    {
        $version = '1.8.18';

        $rcmail = rcmail::get_instance();
        $this->load_config();

        // include UI scripts
        $this->include_script("js/jquery-ui-$version.custom.min.js");

        // include UI stylesheet
        $skin = $rcmail->config->get('skin', 'default');
        $ui_map = $rcmail->config->get('jquery_ui_skin_map', array());
        $ui_theme = $ui_map[$skin] ? $ui_map[$skin] : $skin;

        if (file_exists($this->home . "/themes/$ui_theme/jquery-ui-$version.custom.css")) {
            $this->include_stylesheet("themes/$ui_theme/jquery-ui-$version.custom.css");
        }
        else {
            $this->include_stylesheet("themes/default/jquery-ui-$version.custom.css");
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
