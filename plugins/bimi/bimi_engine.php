<?php

/**
 * @license GNU GPLv3+
 * @author Craig Andrews <candrews@integralblue.com>
 */
class bimi_engine
{
    private string $email;
    private ?string $binary;

    const MIME_TYPE = 'image/svg+xml';
    const CACHE_NULL_VALUE = 'NOT FOUND';

    /**
     * Class constructor
     *
     * @param string $email   email address
     */
    public function __construct($email)
    {
        $this->email  = $email;
        $this->retrieve();
    }

    /**
     * Returns image mimetype
     */
    public function getMimetype()
    {
        return self::MIME_TYPE;
    }

    /**
     * Returns the image in binary form
     */
    public function getBinary()
    {
        return $this->binary;
    }

    /**
     * Sends the image to the browser
     */
    public function sendOutput()
    {
        if ($this->binary) {
            $rcmail = rcmail::get_instance();
            $rcmail->output->future_expire_header(10 * 60);

            header('Content-Type: ' . self::MIME_TYPE);
            header('Content-Size: ' . strlen($this->binary));
            echo $this->binary;

            return true;
        }

        return false;
    }

    /**
     * BIMI retriever
     */
    private function retrieve()
    {
        if (preg_match('/.*@(.*)/', $this->email, $matches)) {
            do {
                $domain = $matches[1];
                $this->binary = $this->cache_get_bimi_image($domain);
                // If there's no BIMI at the subdomain, check the parent domain
            }
	    while($this->binary == null && preg_match('/.*?\.(.*)/', $domain, $matches));
        }
        else {
            $this->binary = null;
        }
    }

    /**
     * Using the cache, given a domain, returns the BIMI image. The image is always SVG XML. Returns null if no image could be retrieved.
     */
    private function cache_get_bimi_image(string $domain): ?string
    {
        $rcmail = rcmail::get_instance();
        $cache = $rcmail->get_cache_shared('bimi');
        if ($cache && $cached_data=$cache->get($domain)) {
            if ($cached_data==self::CACHE_NULL_VALUE) {
                return null;
            }
            else {
                return $cached_data;
            }
        }
        else {
            $data = $this->get_bimi_image($domain);
            $cached_data=$data == null ? self::CACHE_NULL_VALUE : $data;
            if ($cache) {
                $cache->set($domain, $cached_data);
            }
            return $data;
        }
    }

    /**
     * Given a domain, returns the BIMI image. The image is always SVG XML. Returns null if no image could be retrieved.
     */
    private function get_bimi_image(string $domain): ?string
    {
        if ($bimi_url = $this->get_bimi_url($domain)) {
            $rcmail = rcmail::get_instance();
            $client = $rcmail->get_http_client();
            $res = $client->request('GET', $bimi_url);
            if ( $res->getStatusCode() == 200 && $res->hasHeader('Content-Type') && strcasecmp($res->getHeader('Content-Type')[0], self::MIME_TYPE) == 0) {
                $svg = $res->getBody()->getContents();
                $svg = rcmail_attachment_handler::svg_filter($svg);
                return $svg;
            }
        }
        return null;
    }

    /**
     * Given a domain, returns the BIMI URL or null if there no such domain or the domain doesn't have a BIMI record.
     */
    private function get_bimi_url(string $domain): ?string
    {
        $bimi_record = dns_get_record("default._bimi.".$domain, DNS_TXT);
        if ($bimi_record && sizeof($bimi_record) >= 1 && array_key_exists('txt', $bimi_record[0])) {
            $bimi_record_value = $bimi_record[0]['txt'];
            if (preg_match('@v=BIMI1(?:;|$)@i', $bimi_record_value, $svg) && preg_match('@l=(https://.+?)(?:;|$)@', $bimi_record_value, $matches)) {
                $bimi_url = $matches[1];
                return $bimi_url;
            }
        }
        return null;
    }
}
