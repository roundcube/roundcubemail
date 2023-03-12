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
 |   Render a simple HTML page with the given contents                   |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

/**
 * Class to create an empty HTML page with some default styles
 *
 * @package    Webmail
 * @subpackage View
 */
class rcmail_html_page extends rcmail_output_html
{
    protected $inline_warning;

    /**
     * Process the page content and write to stdOut
     *
     * @param string $contents HTML page content
     */
    public function write($contents = '')
    {
        self::reset(true);

        // load embed.css from skin folder (if exists)
        $embed_css = $this->config->get('embed_css_location', '/embed.css');
        if ($embed_css = $this->get_skin_file($embed_css, $path, null, true)) {
            $this->include_css($embed_css);
        }
        else {  // set default styles for warning blocks inside the attachment part frame
            $this->add_header(html::tag('style', ['type' => 'text/css'],
                ".rcmail-inline-message { font-family: sans-serif; border:2px solid #ffdf0e;"
                                        . "background:#fef893; padding:0.6em 1em; margin-bottom:0.6em }\n" .
                ".rcmail-inline-buttons { margin-bottom:0 }"
            ));
        }

        if (empty($contents)) {
            $contents = '<html><body></body></html>';
        }

        if ($this->inline_warning) {
            $body_start = 0;
            if ($body_pos = strpos($contents, '<body')) {
                $body_start = strpos($contents, '>', $body_pos) + 1;
            }

            $contents = substr_replace($contents, $this->inline_warning, $body_start, 0);
        }

        parent::write($contents);
    }

    /**
     * Add inline warning with optional button
     *
     * @param string $text         Warning content
     * @param string $button_label Button label
     * @param string $button_url   Button URL
     */
    public function register_inline_warning($text, $button_label = null, $button_url = null)
    {
        $text = html::span(null, $text);

        if ($button_label) {
            $onclick = "location.href = '$button_url'";
            $button  = html::tag('button', ['onclick' => $onclick], rcube::Q($button_label));
            $text   .= html::p(['class' => 'rcmail-inline-buttons'], $button);
        }

        $this->inline_warning = html::div(['class' => 'rcmail-inline-message rcmail-inline-warning'], $text);
    }
}
