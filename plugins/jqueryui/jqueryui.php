<?php

/**
 * jQuery UI
 *
 * Provide the jQuery UI library with according themes.
 *
 * @version 1.10.4
 * @author Cor Bosman <roundcube@wa.ter.net>
 * @author Thomas Bruederli <roundcube@gmail.com>
 * @license GNU GPLv3+
 */
class jqueryui extends rcube_plugin
{
    public $noajax = true;
    public $version = '1.10.4';

    private static $features = array();
    private static $ui_theme;

    public function init()
    {
        $rcmail = rcmail::get_instance();

        // the plugin might have been force-loaded so do some sanity check first
        if ($rcmail->output->type != 'html' || self::$ui_theme) {
          return;
        }

        $this->load_config();

        // include UI scripts
        $this->include_script("js/jquery-ui-$this->version.custom.min.js");

        // include UI stylesheet
        $skin     = $rcmail->config->get('skin');
        $ui_map   = $rcmail->config->get('jquery_ui_skin_map', array());
        $ui_theme = $ui_map[$skin] ?: $skin;

        self::$ui_theme = $ui_theme;

        if (file_exists($this->home . "/themes/$ui_theme/jquery-ui-$this->version.custom.css")) {
            $this->include_stylesheet("themes/$ui_theme/jquery-ui-$this->version.custom.css");
        }
        else {
            $this->include_stylesheet("themes/larry/jquery-ui-$this->version.custom.css");
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

    public static function miniColors()
    {
        if (in_array('miniColors', self::$features)) {
            return;
        }

        self::$features[] = 'miniColors';

        $ui_theme = self::$ui_theme;
        $rcube    = rcube::get_instance();
        $script   = 'plugins/jqueryui/js/jquery.miniColors.min.js';
        $css      = "plugins/jqueryui/themes/$ui_theme/jquery.miniColors.css";

        if (!file_exists(INSTALL_PATH . $css)) {
            $css = "plugins/jqueryui/themes/larry/jquery.miniColors.css";
        }

        $rcube->output->include_css($css);
        $rcube->output->add_header(html::tag('script', array('type' => "text/javascript", 'src' => $script)));
        $rcube->output->add_script('$("input.colors").miniColors({colorValues: rcmail.env.mscolors})', 'docready');
        $rcube->output->set_env('mscolors', self::get_color_values());
    }

    public static function tagedit()
    {
        if (in_array('tagedit', self::$features)) {
            return;
        }

        self::$features[] = 'tagedit';

        $script   = 'plugins/jqueryui/js/jquery.tagedit.js';
        $rcube    = rcube::get_instance();
        $ui_theme = self::$ui_theme;
        $css      = "plugins/jqueryui/themes/$ui_theme/tagedit.css";

        if (!file_exists(INSTALL_PATH . $css)) {
            $css = "plugins/jqueryui/themes/larry/tagedit.css";
        }

        $rcube->output->include_css($css);
        $rcube->output->add_header(html::tag('script', array('type' => "text/javascript", 'src' => $script)));
    }

    /**
     * Return a (limited) list of color values to be used for calendar and category coloring
     *
     * @return mixed List for colors as hex values or false if no presets should be shown
     */
    public static function get_color_values()
    {
        // selection from http://msdn.microsoft.com/en-us/library/aa358802%28v=VS.85%29.aspx
        return array('000000','006400','2F4F4F','800000','808000','008000',
            '008080','000080','800080','4B0082','191970','8B0000','008B8B',
            '00008B','8B008B','556B2F','8B4513','228B22','6B8E23','2E8B57',
            'B8860B','483D8B','A0522D','0000CD','A52A2A','00CED1','696969',
            '20B2AA','9400D3','B22222','C71585','3CB371','D2691E','DC143C',
            'DAA520','00FA9A','4682B4','7CFC00','9932CC','FF0000','FF4500',
            'FF8C00','FFA500','FFD700','FFFF00','9ACD32','32CD32','00FF00',
            '00FF7F','00FFFF','5F9EA0','00BFFF','0000FF','FF00FF','808080',
            '708090','CD853F','8A2BE2','778899','FF1493','48D1CC','1E90FF',
            '40E0D0','4169E1','6A5ACD','BDB76B','BA55D3','CD5C5C','ADFF2F',
            '66CDAA','FF6347','8FBC8B','DA70D6','BC8F8F','9370DB','DB7093',
            'FF7F50','6495ED','A9A9A9','F4A460','7B68EE','D2B48C','E9967A',
            'DEB887','FF69B4','FA8072','F08080','EE82EE','87CEEB','FFA07A',
            'F0E68C','DDA0DD','90EE90','7FFFD4','C0C0C0','87CEFA','B0C4DE',
            '98FB98','ADD8E6','B0E0E6','D8BFD8','EEE8AA','AFEEEE','D3D3D3',
            'FFDEAD'
        );
    }
}
