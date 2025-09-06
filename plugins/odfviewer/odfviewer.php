<?php

/**
 * Open Document Viewer plugin
 *
 * Render Open Documents directly in the preview window
 * by using the WebODF library by Tobias Hintze http://webodf.org/
 *
 * @version 0.3
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2011-2013, Kolab Systems AG
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
class odfviewer extends rcube_plugin
{
    public $task = 'mail|calendar|tasks';

    private $odf_mimetypes = array(
        'application/vnd.oasis.opendocument.chart',
        'application/vnd.oasis.opendocument.chart-template',
        'application/vnd.oasis.opendocument.formula',
        'application/vnd.oasis.opendocument.formula-template',
        'application/vnd.oasis.opendocument.graphics',
        'application/vnd.oasis.opendocument.graphics-template',
        'application/vnd.oasis.opendocument.presentation',
        'application/vnd.oasis.opendocument.presentation-template',
        'application/vnd.oasis.opendocument.text',
        'application/vnd.oasis.opendocument.text-master',
        'application/vnd.oasis.opendocument.text-template',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.oasis.opendocument.spreadsheet-template',
    );

    function init()
    {
        // webODF only supports IE9 or higher
        $ua = new rcube_browser;
        if ($ua->ie && $ua->ver < 9) {
            return;
        }

        // extend list of mimetypes that should open in preview
        $rcmail = rcube::get_instance();
        if ($rcmail->action == 'preview' || $rcmail->action == 'show' || $rcmail->task == 'calendar' || $rcmail->task == 'tasks') {
            $mimetypes = (array)$rcmail->config->get('client_mimetypes');
            $rcmail->config->set('client_mimetypes', array_merge($mimetypes, $this->odf_mimetypes));
        }

        $this->add_hook('message_part_get', array($this, 'get_part'));
    }

    /**
     * Handler for message attachment download
     */
    function get_part($args)
    {
        if (!$args['download'] && $args['mimetype'] && in_array($args['mimetype'], $this->odf_mimetypes)) {
            $rcmail = rcube::get_instance();
            $params = array(
                'documentUrl' => $_SERVER['REQUEST_URI'] . '&_download=1',
                'filename'    => $args['part']->filename ?: 'file.odt',
                'type'        => $args['mimetype'],
            );

            // send webODF viewer page
            $html = file_get_contents($this->home . '/odf.html');
            header("Content-Type: text/html; charset=" . RCUBE_CHARSET);
            echo strtr($html, array(
                '%%PARAMS%%'             => rcube_output::json_serialize($params),
                '%%viewer.css%%'         => $this->asset_path('viewer.css'),
                '%%viewer.js%%'          => $this->asset_path('viewer.js'),
                '%%ODFViewerPlugin.js%%' => $this->asset_path('ODFViewerPlugin.js'),
                '%%webodf.js%%'          => $this->asset_path('webodf.js'),
            ));

            $args['abort'] = true;
        }

        return $args;
    }

    private function asset_path($path)
    {
        $rcmail     = rcube::get_instance();
        $assets_dir = $rcmail->config->get('assets_dir');

        $mtime = @filemtime($this->home . '/' . $path);
        if (!$mtime && $assets_dir) {
            $mtime = @filemtime($assets_dir . '/plugins/odfviewer/' . $path);
        }

        $path = $this->urlbase . $path . ($mtime ? '?s=' . $mtime : '');

        return $rcmail->output->asset_url($path);
    }
}
