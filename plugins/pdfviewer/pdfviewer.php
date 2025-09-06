<?php

/**
 * Inline PDF viewer plugin
 *
 * Render PDF files directly in the preview window
 * by using the JavaScript PDF Reader pdf.js by andreasgal (http://mozilla.github.com/pdf.js)
 *
 * @version 0.1.2
 * @author Thomas Bruederli <bruederli@kolabsys.com>
 *
 * Copyright (C) 2013, Kolab Systems AG
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
class pdfviewer extends rcube_plugin
{
    public $task = 'mail|calendar|tasks|logout';

    private $pdf_mimetypes = array(
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'applications/vnd.pdf',
        'text/pdf',
        'text/x-pdf',
    );

    /**
     * Plugin initialization
     */
    function init()
    {
        // pdf.js only supports IE9 or higher
        $ua = new rcube_browser;
        if ($ua->ie && $ua->ver < 9)
            return;

        // extend list of mimetypes that should open in preview
        $rcmail = rcube::get_instance();
        if ($rcmail->action == 'preview' || $rcmail->action == 'show' || $rcmail->task == 'calendar' || $rcmail->task == 'tasks') {
          $mimetypes = (array)$rcmail->config->get('client_mimetypes');
          $rcmail->config->set('client_mimetypes', array_merge($mimetypes, $this->pdf_mimetypes));
        }

        // only use pdf.js if the browser doesn't support inline PDF rendering
        if (empty($_SESSION['browser_caps']['pdf']) || $ua->opera)
            $this->add_hook('message_part_get', array($this, 'get_part'));

        $this->add_hook('message_part_structure', array($this, 'part_structure'));
    }

    /**
     * Handler for message attachment download
     */
    public function get_part($args)
    {
        // redirect to viewer/viewer.html
        if (!$args['download'] && $args['mimetype'] && empty($_GET['_load']) && in_array($args['mimetype'], $this->pdf_mimetypes)) {
            $rcmail   = rcube::get_instance();
            $file_url = $_SERVER['REQUEST_URI'] . '&_load=1';
            $location = $rcmail->output->asset_url($this->urlbase . 'viewer/viewer.html');

            header('Location: ' . $location . '?file=' . urlencode($file_url));
            exit;
        }

        return $args;
    }

    /**
     * Hook for MIME message parsing.
     * This allows us to fix mimetypes of PDF attachments
     */
    public function part_structure($args)
    {
        if (!empty($args['structure']->parts)) {
            foreach (array_keys($args['structure']->parts) as $i) {
                $this->fix_mime_part($args['structure']->parts[$i], $args['object']);
            }
        }
        else if ($args['structure']->mimetype != $args['mimetype']) {
            $args['mimetype'] = $args['structure'];
        }

        return $args;
    }

    /**
     * Helper method to fix potentially invalid mimetypes of PDF attachments
     */
    private function fix_mime_part($part, $message)
    {
        // Some versions of Outlook create garbage Content-Type:
        // application/pdf.A520491B_3BF7_494D_8855_7FAC2C6C0608
        if (preg_match('/^application\/pdf.+/', $part->mimetype)) {
            $part->mimetype = 'application/pdf';
        }

        // try to fix invalid application/octet-stream mimetypes for PDF attachments
        if ($part->mimetype == 'application/octet-stream' && preg_match('/\.pdf$/', strval($part->filename))) {
            $body = $message->get_part_body($part->mime_id, false, 2048);
            $real_mimetype = rcube_mime::file_content_type($body, $part->filename, $part->mimetype, true, true);
            if (in_array($real_mimetype, $this->pdf_mimetypes)) {
                $part->mimetype = $real_mimetype;
            }
        }

        list($part->ctype_primary, $part->ctype_secondary) = explode('/', $part->mimetype);
    }
}
