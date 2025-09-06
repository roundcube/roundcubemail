<?php

/**
 * This plugin displays an icon showing the status
 * of an authentication results header of the message
 *
 * @version 0.4.0
 * @author pimlie
 * @mail pimlie@hotmail.com
 *
 * based on dkimstatus plugin by Julien vehent - julien@linuxwall.info
 * original plugin from Vladimir Mach - wladik@gmail.com
 *
 * icons by brankic1970: http://brankic1979.com/icons/
 *
 */

class authres_status extends rcube_plugin
{
    public $task = 'mail|settings';

    const STATUS_NOSIG = 1;
    const STATUS_NORES = 2;
    const STATUS_PASS  = 4;
    const STATUS_PARS  = 8;
    const STATUS_THIRD = 16;
    const STATUS_WARN  = 32;
    const STATUS_FAIL  = 64;
    const STATUS_ALL   = 127;

    private static $RFC5451_authentication_methods = array(
        "auth",
        "dkim",
        "domainkeys",
        "sender-id",
        "spf",
        "arc",
    );

    private static $RFC5451_authentication_results = array(
        "none"      => self::STATUS_NOSIG,
        "pass"      => self::STATUS_PASS,
        "fail"      => self::STATUS_FAIL,
        "policy"    => self::STATUS_FAIL,
        "neutral"   => self::STATUS_WARN,
        "temperror" => self::STATUS_WARN,
        "permerror" => self::STATUS_FAIL,
        "hardfail"  => self::STATUS_FAIL,
        "softfail"  => self::STATUS_WARN
    );

    private static $RFC5451_ptypes = array("smtp", "header", "body", "policy");
    private static $RFC5451_properties = array("auth", "d", "i", "from", "sender", "iprev", "mailfrom", "helo");

    private $override;
    private $img_status;
    private $message_headers_done = false;
    private $trusted_mtas = [];

    public function init()
    {
        $this->add_texts('localization', true);

        $rcmail = rcmail::get_instance();
        $this->load_config();

        $this->add_hook('storage_init', array($this, 'storage_init'));
        $this->add_hook('messages_list', array($this, 'messages_list'));
        $this->add_hook('message_headers_output', array($this, 'message_headers'));

        $dont_override = $rcmail->config->get('dont_override', array());

        $this->override = array(
            'list_cols'     => !in_array('list_cols', $dont_override),
            'column'        => !in_array('enable_authres_status_column', $dont_override),
            'fallback'      => !in_array('use_fallback_verifier', $dont_override),
            'statuses'      => !in_array('show_statuses', $dont_override),
            'trusted_mtas'    => !in_array('trusted_mtas', $dont_override),
        );

        if ($this->override['list_cols']) {
            $this->include_stylesheet($this->local_skin_path() . '/authres_status.css');
            if ($rcmail->config->get('enable_authres_status_column')) {
                $this->include_script('authres_status.js');
            }

            if ($this->override['column'] || $this->override['fallback'] || $this->override['statuses']) {
                $this->add_hook('preferences_list', array($this, 'preferences_list'));
                $this->add_hook('preferences_sections_list', array($this, 'preferences_section'));
                $this->add_hook('preferences_save', array($this, 'preferences_save'));
            }
        }

        $this->trusted_mtas = $rcmail->config->get('trusted_mtas', array());
    }

    public function storage_init($p)
    {
        $p['fetch_headers'] = trim($p['fetch_headers'] . ' ' . strtoupper('Authentication-Results') . ' ' . strtoupper('X-DKIM-Authentication-Results') . ' ' . strtoupper('X-Spam-Status') . ' ' . strtoupper('DKIM-Signature') . ' ' . strtoupper('DomainKey-Signature'));
        return $p;
    }

    public function preferences_list($args)
    {
        if ($args['section'] == 'authres_status') {
            $rcmail = rcmail::get_instance();

            if ($this->override['column'] || $this->override['fallback']) {
                $args['blocks']['authrescolumn']['name'] = $this->gettext('title_enable_column');

                if ($this->override['column']) {
                    $args['blocks']['authrescolumn']['options']['enable']['title'] = $this->gettext('label_enable_column');
                    $input = new html_checkbox(array('name' => '_enable_authres_status_column', 'id' => 'enable_authres_status_column', 'value' => 1));
                    $args['blocks']['authrescolumn']['options']['enable']['content'] = $input->show($rcmail->config->get('enable_authres_status_column'));
                }

                if ($this->override['fallback']) {
                    $args['blocks']['authrescolumn']['options']['fallback']['title'] = $this->gettext('label_fallback_verifier');
                    $input = new html_checkbox(array('name' => '_use_fallback_verifier', 'id' => 'use_fallback_verifier', 'value' => 1));
                    $args['blocks']['authrescolumn']['options']['fallback']['content'] = $input->show($rcmail->config->get('use_fallback_verifier'));
                }
            }

            if ($this->override['trusted_mtas']) {
                $args['blocks']['authrestrusted']['name'] = $this->gettext('title_trusted_mtas');
                
                $args['blocks']['authrestrusted']['options']['trusted_mtas']['title'] = $this->gettext('label_trusted_mtas');
                $input = new html_inputfield(array('name' => '_trusted_mtas', 'id' => 'trusted_mtas'));
                $args['blocks']['authrestrusted']['options']['trusted_mtas']['content'] = $input->show(implode(",", (array)$rcmail->config->get('trusted_mtas')));
            }

            if ($this->override['statuses']) {
                $statuses = array(1, 2, 4, 8, 16, 32, 64);
                $show_statuses = $rcmail->config->get('show_statuses');
                if ($show_statuses === null) {
                    $show_statuses = array_sum($statuses) - self::STATUS_NOSIG;
                }

                foreach ($statuses as $status) {
                    $args['blocks']['authresstatus']['name'] = $this->gettext('title_include_status');

                    $args['blocks']['authresstatus']['options']['enable' . $status]['title'] = $this->gettext('label_include_status' . $status);
                    $input = new html_checkbox(array('name' => '_show_statuses[]', 'id' => 'enable_authres_status_column', 'value' => $status));
                    $args['blocks']['authresstatus']['options']['enable' . $status]['content'] = $input->show(($show_statuses & $status));
                }
            }
        }

        return $args;
    }

    public function preferences_section($args)
    {
        $args['list']['authres_status'] = array(
            'id' => 'authres_status',
            'section' => rcube::Q($this->gettext('section_title'))
        );

        return $args;
    }

    public function preferences_save($args)
    {
        if ($args['section'] == 'authres_status') {
            $args['prefs']['enable_authres_status_column'] = isset($_POST["_enable_authres_status_column"]) && $_POST["_enable_authres_status_column"] == 1;
            $list_cols = rcmail::get_instance()->config->get('list_cols');

            $args['prefs']['use_fallback_verifier'] = isset($_POST["_use_fallback_verifier"]) && $_POST["_use_fallback_verifier"] == 1;

            if (isset($_POST['_trusted_mtas'])) {
                $trusted_mtas = array_map(function($value) {
                    return trim($value);
                }, explode(",", $_POST["_trusted_mtas"]));

                $args['prefs']['trusted_mtas'] = array_diff($trusted_mtas, array(""));
            } else {
                $args['prefs']['trusted_mtas'] = array();
            }

            if (!is_array($list_cols)) {
                $list_cols = array();
            }

            if ($args['prefs']['enable_authres_status_column']) {
                if (!in_array('authres_status', $list_cols)) {
                    $list_cols[] = 'authres_status';
                }
            } else {
                $list_cols = array_diff($list_cols, array('authres_status'));
            }

            $args['prefs']['list_cols'] = $list_cols;

            if (is_array($_POST["_show_statuses"])) {
                $args['prefs']['show_statuses'] = (int)array_sum($_POST["_show_statuses"]);
            }
        }

        return $args;
    }

    public function messages_list($p)
    {
        if (!empty($p['messages'])) {
            $rcmail = rcmail::get_instance();
            if ($rcmail->config->get('enable_authres_status_column')) {
                $layout = $rcmail->config->get('layout');
                if ($layout == 'widescreen') {
                    $authres_flags = array();
                }

                $show_statuses = (int)$rcmail->config->get('show_statuses');
                foreach ($p['messages'] as $index => $message) {
                    $img_status = $this->get_authentication_status($message, $show_statuses, $message->uid);

                    if ($layout == 'widescreen') {
                        $authres_flags[$message->uid] = $img_status;
                    } else {
                        $p['messages'][$index]->list_cols['authres_status'] = $img_status;
                    }
                }

                if ($layout == 'widescreen') {
                    $rcmail->output->set_env('authres_flags', $authres_flags);
                }
            }
        }

        return $p;
    }

    public function message_headers($p)
    {
        /* We only have to check the headers once and this method is executed more than once,
        /* so let's cache the result
        */
        if (!$this->message_headers_done) {
            $this->message_headers_done = true;

            $show_statuses = (int)rcmail::get_instance()->config->get('show_statuses');
            $this->img_status = $this->get_authentication_status($p['headers'], $show_statuses, (int)$_GET["_uid"]);
        }

        $p['output']['from']['value'] = $this->img_status . $p['output']['from']['value'];
        $p['output']['from']['html'] = true;

        return $p;
    }

    /* See https://tools.ietf.org/html/rfc5451
    */
    public function rfc5451_extract_authresheader($headers)
    {
        if (!is_array($headers)) {
            $headers = array($headers);
        }

        //rfc2822 token setup
        $crlf        = "(?:\r\n)";
        $wsp         = "[\t ]";
        $text        = "[\\x01-\\x09\\x0B\\x0C\\x0E-\\x7F]";
        $quoted_pair = "(?:\\\\$text)";
        $fws         = "(?:(?:$wsp*$crlf)?$wsp+)";
        $ctext       = "[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F" . "!-'*-[\\]-\\x7F]";
        $comment     = "(\\((?:$fws?(?:$ctext|$quoted_pair|(?1)))*" . "$fws?\\))";
        $cfws        = "(?:(?:$fws?$comment)*(?:(?:$fws?$comment)|$fws))" . "?";
        $atom        = "[a-z0-9!#$%&\'*+-\/=?^_`{|}~]+";

        $results = array();
        foreach ($headers as $header) {
            $authservid = false;
            if (preg_match('/^' . $cfws . '((?=.{1,254}$)((?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,63}(\/[^\s]*)?)' . $cfws . '(\(.*?\))?' . $cfws . ';/i', trim($header), $m)) {
                $authservid = $m[3];
                $header = substr($header, strlen($m[0]));
            }

            if (!$authservid || !count($this->trusted_mtas) || in_array($authservid, $this->trusted_mtas)) {
                $resinfos = array();
                $header_parts = explode(";", $header);
                while (count($header_parts)) {
                    $header_part = array_shift($header_parts);

                    // check whether part is not from within comment, eg 'dkim=pass    (1024-bit key; insecure key)' should be matched as one
                    if (preg_match('/\([^)]*$/', $header_part)) {
                        $resinfos[] = trim($header_part . ';' . array_shift($header_parts));
                    } else {
                        $resinfos[] = trim($header_part);
                    }
                }

                foreach ($resinfos as $resinfo) {
                    if (preg_match('/(' . implode("|", self::$RFC5451_authentication_methods) . ')' . $cfws . '=' . $cfws . '(' . implode("|", array_keys(self::$RFC5451_authentication_results)) . ')' . $cfws . '(\(.*?\))?/i', $resinfo, $m, PREG_OFFSET_CAPTURE)) {
                        $parsed_resinfo = array(
                            'title'  => trim($m[0][0]),
                            'method' => $m[1][0],
                            'result' => $m[6][0],
                            'reason' => isset($m[7]) ? $m[7][0] : '',
                            'props'  => array()
                        );

                        $propspec = trim(($m[0][1] > 0 ? substr($resinfo, 0, $m[0][1]) : '') . substr($resinfo, strlen($m[0][0])));
                        if ($propspec) {
                            if (preg_match_all('/(' . implode("|", self::$RFC5451_ptypes) . ')' . $cfws . '\.' . $cfws . '(' . implode("|", self::$RFC5451_properties) . ')' . $cfws . '=' . $cfws . '([^\s]*)/i', $propspec, $m)) {
                                foreach ($m[0] as $k => $v) {
                                    if (!isset($parsed_resinfo['props'][$m[1][$k]])) {
                                        $parsed_resinfo['props'][$m[1][$k]] = array();
                                    }

                                    $parsed_resinfo['props'][$m[1][$k]] [$m[6][$k]] = $m[11][$k];
                                }
                            }
                        }

                        $results[] = $parsed_resinfo;
                    }
                }
            }
        }

        return $results;
    }

    public function get_authentication_status($headers, $show_statuses = 0, $uid = 0)
    {
        /* If dkimproxy did not find a signature, stop here
        */
        if (($results = $headers->others['x-dkim-authentication-results']) && strpos($results, 'none') !== false) {
            $status = self::STATUS_NOSIG;
        } else {
            if ($headers->others['authentication-results']) {
                $results = $this->rfc5451_extract_authresheader($headers->others['authentication-results']);
                $status = 0;
                $title = '';

                foreach ($results as $result) {
                    $status = $status | (isset(self::$RFC5451_authentication_results[$result['result']]) ? self::$RFC5451_authentication_results[$result['result']] : self::STATUS_FAIL);

                    $title .= ($title ? '; ' : '') . $result['title'];
                }

                if ($status == self::STATUS_PASS) {
                    /* Verify if its an author's domain signature or a third party
                    */
                    if (preg_match("/[@]([a-zA-Z0-9]+([.][a-zA-Z0-9]+)?\.[a-zA-Z]{2,4})/", $headers->from, $m)) {
                        $title = '';
                        $authorDomain = $m[1];
                        $authorDomainFound = false;

                        foreach ($results as $result) {
                            if ($result['method'] == 'dkim' || $result['method'] == 'domainkeys') {
                                if (is_array($result['props']) && isset($result['props']['header'])) {
                                    $pvalue = '';

                                    // d is required, but still not always present
                                    if (isset($result['props']['header']['d'])) {
                                        $pvalue = $result['props']['header']['d'];
                                    } elseif (isset($result['props']['header']['i'])) {
                                        $pvalue = substr($result['props']['header']['i'], strpos($result['props']['header']['i'], '@') + 1);
                                    }

                                    if ($pvalue == $authorDomain || substr($authorDomain, -1 * strlen($pvalue)) == $pvalue) {
                                        $authorDomainFound = true;

                                        if ($status != self::STATUS_PASS) {
                                            $status = self::STATUS_PASS;
                                            $title = $result['title'];
                                        } else {
                                            $title.= ($title ? '; ' : '') . $result['title'];
                                        }
                                    } else {
                                        if ($status == self::STATUS_THIRD) {
                                            $title .= '; ' . $this->gettext('for') . ' ' . $pvalue . ' ' . $this->gettext('by') . ' ' . $result['title'];
                                        } elseif (!$authorDomainFound) {
                                            $status = self::STATUS_THIRD;
                                            $title = $pvalue . ' ' . $this->gettext('by') . ' ' . $result['title'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!$status) {
                    $status = self::STATUS_NOSIG;
                }

                /* Check for spamassassin's X-Spam-Status
                */
            } elseif ($headers->others['x-spam-status']) {
                $status = self::STATUS_NOSIG;

                /* DKIM_* are defined at: http://search.cpan.org/~kmcgrail/Mail-SpamAssassin-3.3.2/lib/Mail/SpamAssassin/Plugin/DKIM.pm */
                $results = $headers->others['x-spam-status'];
                if (is_array($results)) {
                    $results = end($results); // Should we take first or last header found? Last has probably been added by our own MTA
                }

                if (preg_match_all('/DKIM_[^,=]+/', $results, $m)) {
                    if (array_search('DKIM_SIGNED', $m[0]) !== false) {
                        if (array_search('DKIM_VALID', $m[0]) !== false) {
                            if (array_search('DKIM_VALID_AU', $m[0])) {
                                $status = self::STATUS_PASS;
                                $title = 'DKIM_SIGNED, DKIM_VALID, DKIM_VALID_AU';
                            } else {
                                $status = self::STATUS_THIRD;
                                $title = 'DKIM_SIGNED, DKIM_VALID';
                            }
                        } else {
                            $status = self::STATUS_FAIL;
                            $title = 'DKIM_SIGNED';
                        }
                    }
                }
            } elseif ($headers->others['dkim-signature'] || $headers->others['domainkey-signature']) {
                $status = 0;

                if ($uid) {
                    $rcmail = rcmail::get_instance();
                    if ($headers->others['dkim-signature'] && $rcmail->config->get('use_fallback_verifier')) {
                        if (!class_exists('Crypt_RSA')) {
                            $autoload = require __DIR__ . "/../../vendor/autoload.php";
                            $autoload->loadClass('Crypt_RSA'); // Preload for use in DKIM_Verify
                        }

                        try {
                          $dkimVerify = new DKIM_Verify($rcmail->imap->get_raw_body($uid));
                          $results = $dkimVerify->validate();
                        } catch(Exception $e) {
                          $results = array();

                          $status = self::STATUS_NOSIG;
                          $title = "Exception thrown by internal verifier: " . $e->getMessage();
                        }

                        if (count($results)) {
                            $status = 0;
                            $title = '';
                            foreach ($results as $result) {
                                foreach ($result as $res) {
                                    if (count($res)) {
                                        $status = $status | (isset(self::$RFC5451_authentication_results[$res['status']]) ? self::$RFC5451_authentication_results[$res['status']] : self::STATUS_FAIL);

                                        if ($res['status'] == 'pass') {
                                            $title .= ($title ? '; ' : '') . "dkim=pass (internal verifier)";
                                        }
                                    }
                                }
                            }

                            if (!$title) {
                                $title = $res['reason'];
                            }
                        }
                    }
                }

                if (!$status) {
                    $status = self::STATUS_NORES;
                }
            } else {
                $status = self::STATUS_NOSIG;
            }
        }

        if ($status == self::STATUS_NOSIG) {
            $image = 'status_nosig.png';
            $alt = 'nosignature';
        } elseif ($status == self::STATUS_NORES) {
            $image = 'status_nores.png';
            $alt = 'noauthresults';
        } elseif ($status == self::STATUS_PASS) {
            $image = 'status_pass.png';
            $alt = 'signaturepass';
        } else {
            // at least one auth method was passed, show partial pass
            if (($status & self::STATUS_PASS)) {
                $status = self::STATUS_PARS;
                $image = 'status_partial_pass.png';
                $alt = 'partialpass';
            } elseif ($status >= self::STATUS_FAIL) {
                $image = 'status_fail.png';
                $alt = 'invalidsignature';
            } elseif ($status >= self::STATUS_WARN) {
                $image = 'status_warn.png';
                $alt = 'temporaryinvalid';
            } elseif ($status >= self::STATUS_THIRD) {
                $image = 'status_third.png';
                $alt = 'thirdparty';
            }
        }

        if (!$show_statuses || ($show_statuses & $status)) {
            return '<img src="plugins/authres_status/images/' . $image . '" alt="' . $alt . '" title="' . $this->gettext($alt) . htmlentities($title) . '" class="authres-status-img" /> ';
        }

        return '';
    }
}
