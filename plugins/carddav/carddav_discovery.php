<?php
/*
    RCM CardDAV Plugin
    Copyright (C) 2011-2016 Benjamin Schieder <rcmcarddav@wegwerf.anderdonau.de>,
                            Michael Stilkerich <ms@mike2k.de>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


require_once("carddav_backend.php");
require_once("carddav_common.php");

class carddav_discovery
{
	private static $helper;

	/**
	 * Determines the location of the addressbook for the current user on the
	 * CardDAV server.
	 *
	 * Returns: Array of found addressbook. Each array element is array with keys:
	 *   - name: Name of the addressbook reported by server
	 *   - href: URL to the addressbook collection
	 *
	 * On error, false is returned.
	 */
	public function find_addressbooks($url, $user, $password)
	{{{
	if (!preg_match(';^(([^:]+)://)?(([^/:]+)(:([0-9]+))?)(/?.*)$;', $url, $match))
		return false;

	$protocol = $match[2]; // optional
	$host     = $match[4]; // mandatory
	$port     = $match[6]; // optional
	$path     = $match[7]; // optional

	// plain is only used if http was explicitly given
	$use_ssl   = !($protocol == "http");

	// setup default values if no user values given
	if($use_ssl) {
		$protocol = $protocol?$protocol:'https';
		$port     = $port    ?$port    :443;
	} else {
		$protocol = $protocol?$protocol:'http';
		$port     = $port    ?$port    :80;
	}

	$services = $this->find_servers($host, $use_ssl);

	// Fallback: If no DNS provided, we use the data given by the user/defaults
	if(count($services) == 0) {
		$services[] = array(
			'host'    => $host,
			'port'    => ($port ? $port : ($use_ssl ? 443 : 80)),
			'baseurl' => "$protocol://$host:$port",
		);
	}

	$services = $this->find_baseurls($services);

	// if the user specified a full URL, we try that first
	if(strlen($path) > 2) {
		$userspecified = array(
			'host'    => $host,
			'port'    => ($port ? $port : ($use_ssl ? 443 : 80)),
			'baseurl' => "$protocol://$host:$port",
			'paths'   => array($path),
		);
		array_unshift($services, $userspecified);
	}

	$cdfopen_cfg = array('username'=>$user, 'password'=>$password);

	// now check each of them until we find something (or don't)
	foreach($services as $service) {
		$cdfopen_cfg['url'] = $service['baseurl'];

		foreach($service['paths'] as $path) {
			$aBooks = $this->retrieve_addressbooks($path, $cdfopen_cfg);
			if(is_array($aBooks) && count($aBooks)>0)
				return $aBooks;
		}
	}
	return false;
	}}}

	private function retrieve_addressbooks($path, $cdfopen_cfg, $recurse=false)
	{{{
	$baseurl = $cdfopen_cfg['url'];
	$url = carddav_common::concaturl($baseurl, $path);
	$depth = ($recurse ? 1 : 0);
	self::$helper->debug("SEARCHING $url (Depth: ".$depth.")");

	// check if the given URL points to an addressbook
	$opts = array(
		'method'=>"PROPFIND",
		'header'=>array("Depth: " . $depth, 'Content-Type: application/xml; charset="utf-8"'),
		'content'=> <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<D:propfind xmlns:D="DAV:" xmlns:C="urn:ietf:params:xml:ns:carddav"><D:prop>
	<D:current-user-principal/>
    <D:resourcetype />
    <D:displayname />
	<C:addressbook-home-set/>
</D:prop></D:propfind>
EOF
	);

	$reply = self::$helper->cdfopen($url, $opts, $cdfopen_cfg);
	$xml = self::$helper->checkAndParseXML($reply);
	if($xml === false) return false;

	$aBooks = array();

	// Check if we found addressbooks at the URL
	$xpresult = $xml->xpath('//RCMCD:response[descendant::RCMCD:resourcetype/RCMCC:addressbook]');
	foreach($xpresult as $ab) {
		self::$helper->registerNamespaces($ab);
		$aBook = array();
		list($aBook['href']) = $ab->xpath('child::RCMCD:href');
		list($aBook['name']) = $ab->xpath('descendant::RCMCD:displayname');
		$aBook['href'] = (string) $aBook['href'];
		$aBook['name'] = (string) $aBook['name'];

		if(strlen($aBook['href']) > 0) {
			$aBook['href'] = carddav_common::concaturl($baseurl, $aBook['href']);
			self::$helper->debug("found abook: ".$aBook['name']." at ".$aBook['href']);
			$aBooks[] = $aBook;
		}
	}

	// found -> done
	if(count($aBooks) > 0) return $aBooks;

	if ($recurse === false) {
		// Check some additional URLs:
		$additional_urls = array();

		// (1) original URL
		$additional_urls[] = $url;

		// (2) see if the server told us the addressbook home location
		$abookhome = $xml->xpath('//RCMCC:addressbook-home-set/RCMCD:href');
		if (count($abookhome) != 0) {
			self::$helper->debug("addressbook home: ".$abookhome[0]);
			$additional_urls[] = $abookhome[0];
		}
		// (3) see if we got a principal URL
		$princurl = $xml->xpath('//RCMCD:current-user-principal/RCMCD:href');
		if (count($princurl) != 0) {
			self::$helper->debug("principal URL: ".$princurl[0]);
			$additional_urls[] = $princurl[0];
		}

		foreach($additional_urls as $other_url) {
			self::$helper->debug("Searching additional URL: $other_url");
			if(strlen($other_url) <= 0) continue;

			// if the server returned a full URL, adjust the base url
			if (preg_match(';^[^/]+://[^/]+;', $other_url, $match)) {
				$cdfopen_cfg['url'] = $match[0];
			} else {
				// restore original baseurl, may have changed in prev URL check
				$cdfopen_cfg['url'] = $baseurl;
			}
			$aBooks = $this->retrieve_addressbooks($other_url, $cdfopen_cfg, $other_url != $princurl[0]);
			// found -> done
			if (!($aBooks === false) && count($aBooks) > 0) return $aBooks;
		}
	}

	// (4) there is no more we can do -> fail
	self::$helper->debug("no principal URL found");
	return false;
	}}}

	// get services by querying DNS SRV records
	private function find_servers($host, $ssl)
	{{{
	if($ssl) {
		$srvpfx   = '_carddavs';
		$defport  = 443;
		$protocol = 'https';
	} else {
		$srvpfx   = '_carddav';
		$defport  = 80;
		$protocol = 'http';
	}

	$srv    = "$srvpfx._tcp.$host";

	// query SRV records
	$dnsresults = dns_get_record($srv, DNS_SRV);

	// order according to priority and weight
	// TODO weight is not quite correctly handled atm, see RFC2782,
	// but this is not crucial to functionality
	$sortPrioWeight = function($a, $b) {
		if ($a['pri'] != $b['pri']) {
			return $b['pri'] - $a['pri'];
		}

		return $a['weight'] - $b['weight'];
	};

	usort($dnsresults, $sortPrioWeight);

	// build results
	$result = array();
	foreach($dnsresults as $dnsres) {
		$target  = $dnsres['target'];
		$port    = $dnsres['port'] ? $dnsres['port'] : $defport;
		$baseurl = "$protocol://$target:$port";
		if($target) {
			self::$helper->debug("found service: $baseurl");

			$result[] = array(
				'host'    => $target,
				'port'    => $port,
				'baseurl' => $baseurl,
				'dnssrv'  => "$srvpfx._tcp.$target",
			);
		}
	}

	return $result;
	}}}

	// discover path and add default paths to services
	private function find_baseurls($services)
	{{{
	foreach($services as &$service) {
		$baseurl = $service['baseurl'];
		$dnssrv  = $service['dnssrv'];

		$paths = array();

		$dnsresults = dns_get_record($dnssrv, DNS_TXT);
		foreach($dnsresults as $dnsresult) {
			if($dnsresult['host'] != $dnssrv) continue;

			foreach($dnsresult['entries'] as $ent) {
				if (preg_match('^path=(.+)', $ent, $match))
					$paths[] = $match[1];
			}
		}

		// as fallback try these default paths
		$paths[] = '/.well-known/carddav';
		$paths[] = '/';

		$service['paths'] = $paths;
	}

	return $services;
	}}}

	public static function initClass()
	{{{
	self::$helper = new carddav_common('DISCOVERY: ');
	}}}
}

carddav_discovery::initClass();

?>
