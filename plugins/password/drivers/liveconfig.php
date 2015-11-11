<?php

/**
 * Roundcube Password Driver for LiveConfig Control Panel
 *
 * @author Christoph Russow <cr@keppler-it.de>
 * @copyright Keppler IT GmbH <info@keppler-it.de>
 *
 * This driver changes the E-Mail-Password on LiveConfig managed
 * Mailservers.
 *
 * Requirements:
 *  LiveConfig 2.0.0
 *  LiveConfig UI reachable over https
 *
 * Config needed:
 *  $config['password_driver'] = 'liveconfig';
 *  $config['password_liveconfig_host'] = '<domainname of the LiveConfig-server instance>';
 *  $config['password_liveconfig_port'] = '<port where the LiveConfig UI is running>'; // defaults to 8443
 *  $config['password_liveconfig_accept_selfsigned'] = true/false; // accept self signed certificates
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

class rcube_liveconfig_password {

  /**
   * this method is called from roundcube to change the password
   *
   * roundcube allready validated the old password so we just need to change it at this point
   *
   * @author Christoph Russow <cr@keppler-it.de>
   * @param string $oldpw Current password
   * @param string $newpw New password
   * @returns int PASSWORD_SUCCESS|PASSWORD_ERROR
   */
  public function save($oldpw, $newpw) {
    $rcmail = rcmail::get_instance();

    $lc_host = $rcmail->config->get('password_liveconfig_host', NULL);
    $lc_port = $rcmail->config->get('password_liveconfig_port', NULL);
    $lc_selfsigned = $rcmail->config->get('password_liveconfig_accept_selfsigned', FALSE);

    if(!isset($lc_host)) {
      $this->log('LiveConfig Host ($config[\'password_liveconfig_host\']) not set unable to request password change');
      return array('code' => PASSWORD_ERROR, 'Unable to request password change.');
    }
    if(!isset($lc_port)) {
      $lc_port = '8443';
    }

    $lc_url = 'https://'. $lc_host .':'. $lc_port .'/liveconfig/hosting/mailpwd';
    $content = 'addr='. urlencode($_SESSION['username']). '&old='. urlencode($oldpw) .'&new='. urlencode($newpw);

    $result_json = $this->post_request($lc_url, $content, $lc_selfsigned);
    if($result_json === false) {
      return array('code' => PASSWORD_ERROR, 'message' => 'Failed to save password in LiveConfig');
    }

    $result = json_decode($result_json, true);
    if($result === NULL) {
      $this->log('Failed to decode expected json structure. Received: '. $result_json);
      return array('code' => PASSWORD_ERROR, 'message' => 'Failed to save password in LiveConfig');
    }

    /**
     * expected json structure
     * {
     *   'status':true/false,
     *   'error':'Error message if status is set to false'
     * }
     */

    if(!isset($result['status']) || $result['status'] === false) {
      $errormsg = 'Failed to save password in LiveConfig';
      if(isset($result['error'])) {
        $errormsg = $result['error'];
      }
      return array('code' => PASSWORD_ERROR, 'message' => $errormsg);
    }

    return PASSWORD_SUCCESS;
  }

  /**
   * This function executes the roundcube mail logging function with a local prefix
   *
   * @param string $line The line which we want to write to the logfile
   */
  private function log($line) {
    rcube::write_log('errors', 'Plugin password (liveconfig driver): '. $line);
  }

  /**
   * This function generates a POST request to an given URL with the fiven Content
   *
   * @param string $url The URL of the post request
   * @param string $content The content of the post request
   * @param boolean $accept_self_signed Are self signed certificates acceptable
   * @param string $content_type The Content-Type of the given content
   * @return mixed false if an error occured or the content of the response
   */
  private function post_request($url, $content, $accept_self_signed = false, $content_type = 'application/x-www-form-urlencoded') {

    $useragent = 'RCmail LiveConfig Password Driver 0.1';

    if((bool)ini_get('allow_url_fopen')) {
      // Do Request with file_get_contents

      $lc_ctx_opts = array(
        'http' => array(
          'method'      => 'POST',
          'header'      => 'Content-type: '.$content_type . "\r\n",
          'user_agent'  => $useragent,
          'content'     => $content,
          //'proxy'       => "tcp://192.168.141.155:8080",
        ),
        'ssl' => array(
          'allow_self_signed' => $accept_self_signed,
          'verify_peer' => !$accept_self_signed,
          'verify_peer_name' => !$accept_self_signed,
        ),
      );

      $lc_req_ctx = stream_context_create($lc_ctx_opts);

      return file_get_contents($url, false, $lc_req_ctx);
    }

    if(function_exists('curl_version')) {
      // Do Request with CURL
      $header = array(
        "Content-type" => $content_type,
      );
      $http_client = curl_init($url);
      curl_setopt($http_client, CURLOPT_USERAGENT, $useragent);
      curl_setopt($http_client, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
      curl_setopt($http_client, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($http_client, CURLOPT_TIMEOUT, 30);
      curl_setopt($http_client, CURLOPT_CONNECTTIMEOUT, 30);
      curl_setopt($http_client, CURLOPT_RETURNTRANSFER, true);
      if($accept_self_signed) {
        curl_setopt($http_client, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($http_client, CURLOPT_SSL_VERIFYHOST, false);
      }
      curl_setopt($http_client, CURLOPT_HTTPHEADER, $header);
      curl_setopt($http_client, CURLOPT_POSTFIELDS, $content);

      $result = curl_exec($http_client);
      if($result === false) {
        $this->log('Error while executing HTTP Request: '. curl_error($http_client));
      }

      curl_close($http_client);
      return $result;
    }

    // no suitable method found to generate post request
    return false;
  }
}

?>
