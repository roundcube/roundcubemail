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
 |   Display error message page                                          |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_utils_error extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        $ERROR_CODE    = !empty($args['code']) ? $args['code'] : 500;
        $ERROR_MESSAGE = !empty($args['message']) ? $args['message'] : null;

        // authorization error
        if ($ERROR_CODE == 401) {
            $__error_title = mb_strtoupper($rcmail->gettext('errauthorizationfailed'));
            $__error_text  = nl2br($rcmail->gettext('errunauthorizedexplain')
                . "\n" . $rcmail->gettext('errcontactserveradmin'));
        }
        // forbidden due to request check
        else if ($ERROR_CODE == 403) {
            if ($_SERVER['REQUEST_METHOD'] == 'GET' && $rcmail->request_status == rcube::REQUEST_ERROR_URL) {
                $url = $rcmail->url($_GET, true, false, true);
                $add = html::a($url, $rcmail->gettext('clicktoresumesession'));
            }
            else {
                $add = $rcmail->gettext('errcontactserveradmin');
            }

            $__error_title = mb_strtoupper($rcmail->gettext('errrequestcheckfailed'));
            $__error_text  = nl2br($rcmail->gettext('errcsrfprotectionexplain')) . '<p>' . $add . '</p>';
        }
        // failed request (wrong step in URL)
        else if ($ERROR_CODE == 404) {
            $request_url   = htmlentities($_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
            $__error_title = mb_strtoupper($rcmail->gettext('errnotfound'));
            $__error_text  = nl2br($rcmail->gettext('errnotfoundexplain')
                . "\n" . $rcmail->gettext('errcontactserveradmin'));

            $__error_text .= '<p><i>' . $rcmail->gettext('errfailedrequest') . ": $request_url</i></p>";
        }
        // browser is not compatible with this application
        else if ($ERROR_CODE == 409) {
            $user_agent    = htmlentities($_SERVER['HTTP_USER_AGENT']);
            $__error_title = 'Your browser does not suit the requirements for this application';
            $__error_text  = "Required features: <i>JavaScript enabled</i> and <i>XMLHTTPRequest support</i>."
                . "<p><i>Your configuration:</i><br>$user_agent</p>";
        }
        // Gone, e.g. message cached but not in the storage
        else if ($ERROR_CODE == 410) {
            $__error_title = 'INTERNAL ERROR';
            $__error_text  = $rcmail->gettext('messageopenerror');
        }
        // invalid compose ID
        else if ($ERROR_CODE == 450 && $_SERVER['REQUEST_METHOD'] == 'GET' && $rcmail->action == 'compose') {
            $url = $rcmail->url('compose');

            $__error_title = mb_strtoupper($rcmail->gettext('errcomposesession'));
            $__error_text  = nl2br($rcmail->gettext('errcomposesessionexplain'))
                . '<p>' . html::a($url, $rcmail->gettext('clicktocompose')) . '</p>';
        }
        // database connection error
        else if ($ERROR_CODE == 601) {
            $__error_title = "CONFIGURATION ERROR";
            $__error_text  =  nl2br($ERROR_MESSAGE) . "<br />Please read the INSTALL instructions!";
        }
        // database connection error
        else if ($ERROR_CODE == 603) {
            $__error_title = "DATABASE ERROR: CONNECTION FAILED!";
            $__error_text  =  "Unable to connect to the database!<br />Please contact your server-administrator.";
        }
        // system error
        else {
            $__error_title = "SERVICE CURRENTLY NOT AVAILABLE!";
            $__error_text  = sprintf('Error No. [%s]', $ERROR_CODE);
        }

        // inform plugins
        if ($rcmail->plugins) {
            $plugin = $rcmail->plugins->exec_hook('error_page', [
                    'code'  => $ERROR_CODE,
                    'title' => $__error_title,
                    'text'  => $__error_text,
            ]);

            if (!empty($plugin['title'])) {
                $__error_title = $plugin['title'];
            }
            if (!empty($plugin['text'])) {
                $__error_text = $plugin['text'];
            }
        }

        $HTTP_ERR_CODE = $ERROR_CODE && $ERROR_CODE < 600 ? $ERROR_CODE : 500;

        // Ajax request
        if ($rcmail->output && $rcmail->output->type == 'js') {
            header("HTTP/1.0 $HTTP_ERR_CODE $__error_title");
            die;
        }

        // compose page content
        $__page_content = '<div class="boxerror">'
            .'<h3 class="error-title">' . $__error_title . '</h3>'
            .'<div class="error-text">' . $__error_text . '</div>'
            .'</div>';

        if ($rcmail->output && $rcmail->output->template_exists('error')) {
            $GLOBALS['__page_content'] = $__page_content;

            $task = empty($rcmail->user) || empty($rcmail->user->ID) ? '-login' : '';

            $rcmail->output->reset();
            $rcmail->output->set_env('error_task', 'error' . $task);
            $rcmail->output->set_env('server_error', $ERROR_CODE);
            $rcmail->output->set_env('comm_path', $rcmail->comm_path);
            $rcmail->output->send('error');
        }

        $__skin    = $rcmail->config->get('skin', 'default');
        $__product = $rcmail->config->get('product_name', 'Roundcube Webmail');

        // print system error page
        echo '<!doctype html><html><head>'
            . '<title>' . $__product . ':: ERROR</title>'
            . '<link rel="stylesheet" type="text/css" href="skins/$__skin/common.css" />'
            . '</head><body>'
            . '<table border="0" cellsapcing="0" cellpadding="0" width="100%" height="80%">'
            . '<tr><td align="center">' . $__page_content . '</td></tr>'
            . '</table>'
            . '</body></html>';

        exit;
    }
}
