<?php

/*
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
 |   Mail messages search action                                         |
 +-----------------------------------------------------------------------+
 | Author: Benjamin Smith <defitro@gmail.com>                            |
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/

class rcmail_action_mail_search extends rcmail_action_mail_index
{
    protected static $mode = self::MODE_AJAX;

    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    #[Override]
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        @set_time_limit(170);  // extend default max_execution_time to ~3 minutes

        // reset list_page and old search results
        $rcmail->storage->set_page(1);
        $rcmail->storage->set_search_set(null);
        $_SESSION['page'] = 1;

        // get search string
        $str = trim(rcube_utils::get_input_string('_q', rcube_utils::INPUT_GET, true));
        $mbox = trim(rcube_utils::get_input_string('_mbox', rcube_utils::INPUT_GET, true));
        $filter = trim(rcube_utils::get_input_string('_filter', rcube_utils::INPUT_GET));
        $headers = trim(rcube_utils::get_input_string('_headers', rcube_utils::INPUT_GET));
        $scope = trim(rcube_utils::get_input_string('_scope', rcube_utils::INPUT_GET));
        $interval = trim(rcube_utils::get_input_string('_interval', rcube_utils::INPUT_GET));
        $continue = trim(rcube_utils::get_input_string('_continue', rcube_utils::INPUT_GET));

        // Set message set for already stored (but incomplete) search request
        if (!empty($continue) && isset($_SESSION['search']) && $_SESSION['search_request'] == $continue) {
            $rcmail->storage->set_search_set($_SESSION['search']);
            $search = $_SESSION['search'][0];
            $search_request = $continue;
        } else {
            // Parse input parameters into an IMAP search criteria
            $search = self::search_input($str, $headers, $filter, $interval);
            $search_request = md5($mbox . $scope . $interval . $filter . $str);

            // Save search modifiers for the current folder to user prefs
            if (strlen($search) && strlen($headers) && strlen($mbox) && $scope != 'all') {
                self::update_search_mods($mbox, $headers);
            }
        }

        $sort_column = self::sort_column();
        $sort_order = self::sort_order();

        // execute IMAP search
        if ($search) {
            $mboxes = [];

            // search all, current or subfolders folders
            if ($scope == 'all') {
                $mboxes = $rcmail->storage->list_folders_subscribed('', '*', 'mail', null, true);
                // we want natural alphabetic sorting of folders in the result set
                natcasesort($mboxes);
            } elseif ($scope == 'sub') {
                $delim = $rcmail->storage->get_hierarchy_delimiter();
                $mboxes = $rcmail->storage->list_folders_subscribed($mbox . $delim, '*', 'mail');
                array_unshift($mboxes, $mbox);
            }

            if ($scope != 'all') {
                // Remember current folder, it can change in meantime (plugins)
                // but we need it to e.g. recognize Sent folder to handle From/To column later
                $rcmail->output->set_env('mailbox', $mbox);
            }

            $result = $rcmail->storage->search($mboxes, $search, RCUBE_CHARSET, $sort_column);
        }

        // save search results in session
        if (!isset($_SESSION['search']) || !is_array($_SESSION['search'])) {
            $_SESSION['search'] = [];
        }

        if ($search) {
            $_SESSION['search'] = $rcmail->storage->get_search_set();
            $_SESSION['last_text_search'] = $str;
        }

        $_SESSION['search_request'] = $search_request;
        $_SESSION['search_scope'] = $scope;
        $_SESSION['search_interval'] = $interval;
        $_SESSION['search_filter'] = $filter;

        // Get the headers
        if (!isset($result) || empty($result->incomplete)) {
            $result_h = $rcmail->storage->list_messages($mbox, 1, $sort_column, $sort_order);
        }

        // Make sure we got the headers
        if (!empty($result_h)) {
            $count = $rcmail->storage->count($mbox, $rcmail->storage->get_threading() ? 'THREADS' : 'ALL');

            self::js_message_list($result_h, false);

            if ($search) {
                $all_count = $rcmail->storage->count(null, 'ALL');
                $rcmail->output->show_message('searchsuccessful', 'confirmation', ['nr' => $all_count]);
            }

            // remember last HIGHESTMODSEQ value (if supported)
            // we need it for flag updates in check-recent
            if (strlen($mbox)) {
                $data = $rcmail->storage->folder_data($mbox);
                if (!empty($data['HIGHESTMODSEQ'])) {
                    $_SESSION['list_mod_seq'] = $data['HIGHESTMODSEQ'];
                }
            }
        }
        // handle IMAP errors (e.g. #1486905)
        elseif ($err_code = $rcmail->storage->get_error_code()) {
            $count = 0;
            self::display_server_error();
        }
        // advice the client to re-send the (cross-folder) search request
        elseif (!empty($result) && !empty($result->incomplete)) {
            $count = 0;  // keep UI locked
            $rcmail->output->command('continue_search', $search_request);
        } else {
            $count = 0;

            $rcmail->output->show_message('searchnomatch', 'notice');
            $rcmail->output->set_env('multifolder_listing', isset($result) ? !empty($result->multi) : false);

            if (isset($result) && !empty($result->multi) && $scope == 'all') {
                $rcmail->output->command('select_folder', '');
            }
        }

        // update message count display
        $rcmail->output->set_env('search_request', $search ? $search_request : '');
        $rcmail->output->set_env('search_filter', $_SESSION['search_filter']);
        $rcmail->output->set_env('messagecount', $count);
        $rcmail->output->set_env('pagecount', ceil($count / $rcmail->storage->get_pagesize()));
        $rcmail->output->set_env('exists', !strlen($mbox) ? 0 : $rcmail->storage->count($mbox, 'EXISTS'));
        $rcmail->output->command('set_rowcount', self::get_messagecount_text($count, 1), $mbox);

        self::list_pagetitle();

        // update unseen messages count
        if ($search === '') {
            self::send_unread_count($mbox, false, empty($result_h) ? 0 : null);
        }

        if (isset($result) && empty($result->incomplete)) {
            $rcmail->output->command('set_quota', self::quota_content(null, !empty($result->multi) ? 'INBOX' : $mbox));
        }

        $rcmail->output->send();
    }

    /**
     * Creates BEFORE/SINCE search criteria from the specified interval
     * Interval can be: 1W, 1M, 1Y, -1W, -1M, -1Y
     */
    public static function search_interval_criteria($interval)
    {
        if (empty($interval)) {
            return;
        }

        $interval = strtoupper($interval);

        if ($interval[0] == '-') {
            $search = 'BEFORE';
            $interval = substr($interval, 1);
        } else {
            $search = 'SINCE';
        }

        $date = new DateTime('now');
        $interval = new DateInterval('P' . $interval);

        $date->sub($interval);

        return $search . ' ' . $date->format('j-M-Y');
    }

    /**
     * Parse search input.
     *
     * @param string $str      Search string
     * @param string $headers  Comma-separated list of headers/fields to search in
     * @param string $filter   Additional IMAP filter query
     * @param string $interval Additional interval filter
     *
     * @return string IMAP search query
     */
    public static function search_input($str, $headers = '', $filter = 'ALL', $interval = null)
    {
        $headers = $headers ? explode(',', $headers) : ['subject'];

        // Add list filter string
        $result = $filter && $filter != 'ALL' ? $filter : '';

        // Add the interval filter string
        if ($search_interval = self::search_interval_criteria($interval)) {
            $result .= ' ' . $search_interval;
        }

        $value_function = static function ($value) {
            $value = trim($value);
            $value = preg_replace('/(^"|"$)/', '', $value);
            $value = str_replace('\"', '"', $value);

            return $value;
        };

        // Explode the search input into "tokens"
        $parts = rcube_utils::explode_quoted_string('\s+', $str);
        $parts = array_filter($parts);

        foreach ($parts as $idx => $part) {
            if (strcasecmp($part, 'OR') === 0) {
                $parts[$idx] = 'OR';
                continue;
            }

            if (strcasecmp($part, 'AND') === 0) {
                $parts[$idx] = 'AND';
                continue;
            }

            $not = '';

            if (preg_match('/^(-?[a-zA-Z-]+):(.*)$/', $part, $matches)) {
                $option = $matches[1];
                $value = $value_function($matches[2]);

                if ($option[0] == '-') {
                    $not = 'NOT ';
                    $option = substr($option, 1);
                }

                if ($imap_query = self::search_input_option($option, $value)) {
                    $parts[$idx] = $not . $imap_query;
                    continue;
                }
            }

            if (preg_match('/^-".*"$/', $part)) {
                $not = 'NOT ';
                $part = substr($part, 1);
            }

            if ($imap_query = self::search_input_text($value_function($part), $headers)) {
                $parts[$idx] = $not . $imap_query;
            }
        }

        foreach ($parts as $idx => $part) {
            if ($part == 'OR') {
                // Ignore OR on the start and end, and successive ORs
                if ($idx === 0 || !isset($parts[$idx + 1]) || $parts[$idx + 1] == 'OR') {
                    unset($parts[$idx]);
                    continue;
                }

                $index = $idx;

                while ($index-- >= 0) {
                    if (isset($parts[$index])) {
                        $parts[$index] = 'OR ' . $parts[$index];
                        break;
                    }
                }

                unset($parts[$idx]);
            } elseif ($part == 'AND') {
                unset($parts[$idx]);
            }
        }

        $result = trim($result . ' ' . implode(' ', $parts));

        return $result != 'ALL' ? $result : '';
    }

    /**
     * Parse search input token.
     *
     * @param string $option Option name
     * @param string $value  Option value
     *
     * @return ?string IMAP search query, NULL if the option is unsupported
     */
    protected static function search_input_option($option, $value)
    {
        if (!strlen($value)) {
            return null;
        }

        $supported = ['subject', 'from', 'to', 'cc', 'bcc'];
        $option = strtolower($option);
        $escaped = rcube_imap_generic::escape($value);

        switch ($option) {
            case 'body':
                return "BODY {$escaped}";
            case 'text':
                return "TEXT {$escaped}";
            case 'replyto':
            case 'reply-to':
                return "OR HEADER REPLY-TO {$escaped} HEADER MAIL-REPLY-TO {$escaped}";
            case 'followupto':
            case 'followup-to':
                return "OR HEADER FOLLOWUP-TO {$escaped} HEADER MAIL-FOLLOWUP-TO {$escaped}";
            case 'larger':
            case 'smaller':
                if (preg_match('/([0-9\.]+)(k|m|g|b|kb|mb|gb)/i', $value)) {
                    return strtoupper($option) . ' ' . parse_bytes($value);
                }

                break;
            case 'is':
                $map = [
                    'unread' => 'UNSEEN',
                    'read' => 'SEEN',
                    'unseen' => 'UNSEEN',
                    'seen' => 'SEEN',
                    'flagged' => 'FLAGGED',
                    'unflagged' => 'UNFLAGGED',
                    'deleted' => 'DELETED',
                    'undeleted' => 'UNDELETED',
                    'answered' => 'ANSWERED',
                    'unanswered' => 'UNANSWERED',
                ];

                $value = strtolower($value);
                if (isset($map[$value])) {
                    return $map[$value];
                }

                break;
            case 'has':
                if ($value == 'attachment') {
                    // Content-Type values of messages with attachments
                    // the same as in app.js:add_message_row()
                    $ctypes = ['application/', 'multipart/m', 'multipart/signed', 'multipart/report'];

                    // Build search string of "with attachment" filter
                    $result = str_repeat(' OR', count($ctypes) - 1);
                    foreach ($ctypes as $type) {
                        $result .= ' HEADER Content-Type ' . rcube_imap_generic::escape($type);
                    }

                    return trim($result);
                }

                break;
            case 'older_than': // GMail alias
                $option = 'before';
            case 'newer_than': // GMail alias
                $option = 'since';
            case 'since':
            case 'before':
                if (preg_match('/^[0-9]+[WMY]$/i', $value)) {
                    if ($option == 'before') {
                        $value = "-{$value}";
                    }

                    if ($search_interval = self::search_interval_criteria(strtoupper($value))) {
                        return $search_interval;
                    }
                } elseif (preg_match('|^([0-9]{4})[-/]([0-9]{1,2})[-/]([0-9]{1,2})$|i', $value, $m)) {
                    $dt = new DateTime(sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]) . 'T00:00:00Z');
                    return strtoupper($option) . ' ' . $dt->format('j-M-Y');
                }

                break;
            default:
                if (in_array($option, $supported)) {
                    $header = strtoupper($option);
                    return "HEADER {$header} {$escaped}";
                }
        }

        return null;
    }

    /**
     * Converts search text into an imap query
     *
     * @param string $text    Search text
     * @param array  $headers List of headers/fields to search in
     *
     * @return string IMAP search query
     */
    protected static function search_input_text($text, $headers)
    {
        $query = [];

        foreach ($headers as $header) {
            if ($imap = self::search_input_option($header, $text)) {
                $query[$header] = $imap;
            }
        }

        $result = '';

        if (!empty($query)) {
            if (($size = count($query)) > 1) {
                $result .= str_repeat('OR ', $size - 1);
            }

            $result .= implode(' ', $query);
        }

        return $result;
    }

    /**
     * Update search mods for the specified folder
     *
     * @param string $mbox    Folder name
     * @param string $headers Headers list input (comma-separated)
     */
    protected static function update_search_mods($mbox, $headers)
    {
        $supported = ['subject', 'from', 'to', 'cc', 'bcc', 'replyto', 'followupto', 'body', 'text'];
        $headers = explode(',', strtolower($headers));
        $headers = array_intersect($headers, $supported);

        $search_mods = self::search_mods();
        $search_mods_value = array_fill_keys($headers, 1);

        if (!isset($search_mods[$mbox]) || $search_mods[$mbox] != $search_mods_value) {
            $search_mods[$mbox] = $search_mods_value;
            rcmail::get_instance()->user->save_prefs(['search_mods' => $search_mods]);
        }
    }
}
