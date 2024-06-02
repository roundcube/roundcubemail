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
 |    Implementation of backchannel logout from IDP                      |
 |                                                                       |
 | @see https://openid.net/specs/openid-connect-backchannel-1_0.html     |
 |                                                                       |
 | URL to declare: <roundcube instance>/index.php/login/backchannel      |
 +-----------------------------------------------------------------------+
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
 */

class rcmail_action_login_oauth_backchannel extends rcmail_action
{
    /**
     * Request handler.
     *
     * @param array $args Arguments from the previous step(s)
     */
    #[Override]
    public function run($args = [])
    {
        $rcmail = rcmail::get_instance();

        // default message
        $answer = ['error' => 'invalid_request', 'error_description' => 'Error, no action'];

        // Beware we are in back-channel from OP (IDP)
        $logout_token = rcube_utils::get_input_string('logout_token', rcube_utils::INPUT_POST);

        if (!empty($logout_token)) {
            try {
                $event = $rcmail->oauth->jwt_decode($logout_token);

                /* return event example
                {
                    "typ":"Logout",                                      // event type
                    "iat":1700263584,                                    // emition date
                    "jti":"4a953d6e-dc6b-4cc1-8d29-cb54b2351d0a",        // token identifier
                    "iss":"https://....",                                // issuer identifier
                    "aud":"my client id",                                // audience = client id
                    "sub":"82c8f487-df95-4960-972c-4e680c3c72f5",        // subject
                    "sid":"28101815-0017-4ade-a550-e054bde07ded",        // session
                    "events":{"http://schemas.openid.net/event/backchannel-logout":[]}
                }
                */

                if ($event['typ'] !== 'Logout') {
                    throw new RuntimeException('handle only Logout events');
                }
                if (!isset($event['sub'])) {
                    throw new RuntimeException('event has no "sub"');
                }

                $rcmail->oauth->log_debug('backchannel: logout event received, schedule a revocation for token\'s sub: %s', $event['sub']);
                $rcmail->oauth->schedule_token_revocation($event['sub']);

                http_response_code(200); // 204 works also
                header('Content-Type: application/json; charset=UTF-8');
                header('Cache-Control: no-store');
                echo '{}';
                exit;
            } catch (Exception $e) {
                rcube::raise_error($e, true);
                $answer['error_description'] = 'Error decoding JWT';
            }
        } else {
            rcube::raise_error(sprintf('oidc backchannel called from %s without any parameter', rcube_utils::remote_addr()), true);
        }

        http_response_code(400);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        echo json_encode($answer);
        exit;
    }
}
