<?php

/*
 * Roundcube Password Driver for Plesk-RPC.
 *
 * This driver changes a E-Mail-Password via Plesk-RPC
 * Deps: PHP-Curl, SimpleXML
 *
 * @author     Cyrill von Wattenwyl <cyrill.vonwattenwyl@adfinis-sygroup.ch>
 * @copyright  Adfinis SyGroup AG, 2014
 *
 * Config needed:
 * $config['password_plesk_host']     = '10.0.0.5';
 * $config['password_plesk_user']     = 'admin';
 * $config['password_plesk_pass']     = 'pass';
 * $config['password_plesk_rpc_port'] = 8443;
 * $config['password_plesk_rpc_path'] = enterprise/control/agent.php;
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/.
 */

/**
 * Roundcube Password Driver Class
 *
 * See {ROUNDCUBE_ROOT}/plugins/password/README for API description
 *
 * @author Cyrill von Wattenwyl <cyrill.vonwattenwyl@adfinis-sygroup.ch>
 */
class rcube_plesk_password
{
    private $client;
    private $url;
    private $old_version = false;

    /**
     * This method is called from roundcube to change the password
     *
     * roundcube already validated the old password so we just need to change it at this point
     *
     * @author Cyrill von Wattenwyl <cyrill.vonwattenwyl@adfinis-sygroup.ch>
     *
     * @param string $currpass Current password
     * @param string $newpass  New password
     * @param string $username Current username
     *
     * @return int|array PASSWORD_SUCCESS|PASSWORD_ERROR or an array with error code + message
     */
    public function save($currpass, $newpass, $username)
    {
        // get config
        $rcmail = rcmail::get_instance();
        $host = $rcmail->config->get('password_plesk_host');
        $port = $rcmail->config->get('password_plesk_rpc_port', 8443);
        $path = $rcmail->config->get('password_plesk_rpc_path');

        $this->client = password::get_http_client();
        $this->url = "https://{$host}:{$port}/{$path}";

        // try to change password and return the status
        $result = $this->change_mailbox_password($username, $newpass);

        if ($result === true) {
            return PASSWORD_SUCCESS;
        }

        if (is_array($result)) {
            return $result;
        }

        return PASSWORD_ERROR;
    }

    /**
     * send a request to the plesk
     *
     * @param string $packet XML-Packet to send to Plesk
     *
     * @return string|null Response body
     */
    public function send_request($packet)
    {
        $rcmail = rcmail::get_instance();
        $user = $rcmail->config->get('password_plesk_user');
        $pass = $rcmail->config->get('password_plesk_pass');

        $options = [
            'body' => $packet,
            'http_errors' => true,
            'headers' => [
                'HTTP_AUTH_LOGIN' => $user,
                'HTTP_AUTH_PASSWD' => $pass,
                'Content-Type' => 'text/xml',
            ],
        ];

        try {
            $response = $this->client->post($this->url, $options);

            $body = $response->getBody()->getContents();

            return $body && strpos($body, '<?xml') === 0 ? $body : null;
        } catch (Exception $e) {
            rcube::raise_error("Error on {$this->url}: {$e->getMessage()}", true);
        }

        return null;
    }

    /**
     * Get all hosting-information of a domain
     *
     * @param string $domain domain-name
     *
     * @return object|null SimpleXML object
     */
    private function domain_info($domain)
    {
        // build xml
        $request = new SimpleXMLElement('<packet></packet>');
        $site = $request->addChild('site');
        $get = $site->addChild('get');
        $filter = $get->addChild('filter');

        $filter->addChild('name', $domain);
        $dataset = $get->addChild('dataset');

        $dataset->addChild('hosting');
        $packet = $request->asXML();
        $xml = null;

        // send the request and make it to simple-xml-object
        if ($res = $this->send_request($packet)) {
            $xml = new SimpleXMLElement($res);
        }

        // Old Plesk versions require version attribute, add it and try again
        if ($xml && strval($xml->site->get->result->status) === 'error'
            && intval($xml->site->get->result->errcode ?? null) === 1017
        ) {
            $request->addAttribute('version', '1.6.3.0');
            $packet = $request->asXML();

            $this->old_version = true;

            // send the request and make it to simple-xml-object
            if ($res = $this->send_request($packet)) {
                $xml = new SimpleXMLElement($res);
            }
        }

        return $xml;
    }

    /**
     * Get psa-id of a domain
     *
     * @param string $domain domain-name
     *
     * @return int|null Domain ID
     */
    private function get_domain_id($domain)
    {
        if ($xml = $this->domain_info($domain)) {
            return intval($xml->site->get->result->id);
        }

        return null;
    }

    /**
     * Change Password of a mailbox
     *
     * @param string $mailbox full email-address (user@domain.tld)
     * @param string $newpass new password of mailbox
     *
     * @return bool|array True on success, false or array on error
     */
    private function change_mailbox_password($mailbox, $newpass)
    {
        [$user, $domain] = explode('@', $mailbox);
        $domain_id = $this->get_domain_id($domain);

        // if domain cannot be resolved to an id, do not continue
        if (!$domain_id) {
            return false;
        }

        // build xml-packet
        $request = new SimpleXMLElement('<packet></packet>');
        $mail = $request->addChild('mail');
        $update = $mail->addChild('update');
        $add = $update->addChild('set');
        $filter = $add->addChild('filter');
        $filter->addChild('site-id', $domain_id);

        $mailname = $filter->addChild('mailname');
        $mailname->addChild('name', $user);

        $password = $mailname->addChild('password');
        $password->addChild('value', $newpass);
        $password->addChild('type', 'plain');

        if ($this->old_version) {
            $request->addAttribute('version', '1.6.3.0');
        }

        $packet = $request->asXML();

        // send the request to plesk
        if ($res = $this->send_request($packet)) {
            $xml = new SimpleXMLElement($res);
            $res = strval($xml->mail->update->set->result->status);

            if ($res == 'ok') {
                return true;
            }

            return [
                'code' => PASSWORD_ERROR,
                'message' => strval($xml->mail->update->set->result->errtext),
            ];
        }

        return false;
    }
}
