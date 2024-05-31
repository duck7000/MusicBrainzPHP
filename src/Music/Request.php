<?php

#############################################################################
# musicBrainzPHP                                ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

/**
 * The request class
 * Here we emulate a browser accessing the API.
 */
class Request
{
    private $ch;
    private $page;
    private $requestHeaders = array();
    private $responseHeaders = array();
    private $config;

    /**
     * No need to call this.
     * @param string $url URL to open
     */
    public function __construct($url, Config $config)
    {
        $this->config = $config;
        $this->ch = curl_init($url);
        curl_setopt($this->ch, CURLOPT_ENCODING, "");
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array(&$this, "callback_CURLOPT_HEADERFUNCTION"));
        curl_setopt($this->ch, CURLOPT_USERAGENT, $this->config->userAgent);
    }

    public function addHeaderLine($name, $value)
    {
        $this->requestHeaders[] = "$name: $value";
    }

    /**
     * Send a request to the website
     */
    public function sendRequest()
    {
        $this->responseHeaders = array();
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->requestHeaders);
        $this->page = curl_exec($this->ch);
        curl_close($this->ch);
        if ($this->page !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get the Response body
     * @return string page
     */
    public function getResponseBody()
    {
        return $this->page;
    }

    /**
     * HTTP status code of the last response
     * @return int|null null if last request failed
     */
    public function getStatus()
    {
        $headers = $this->getLastResponseHeaders();
        if (empty($headers[0])) {
            return null;
        }

        if (!preg_match("#^HTTP/[\d\.]+ (\d+)#i", $headers[0], $matches)) {
            return null;
        }

        return (int)$matches[1];
    }

    public function getLastResponseHeaders()
    {
        return $this->responseHeaders;
    }

    private function callback_CURLOPT_HEADERFUNCTION($ch, $str)
    {
        $len = strlen($str);
        if ($len) {
            $this->responseHeaders[] = $str;
        }
        return $len;
    }
    
    /**
     * Get the URL to redirect to if a 30* was returned
     * @return string|null URL to redirect to if 300, otherwise null
     */
    public function getRedirect()
    {
        $status = $this->getStatus();
        if ($status == 301 || $status == 302 || $status == 303 || $status == 307 || $status == 308) {
            foreach ($this->getLastResponseHeaders() as $header) {
                if (strpos(trim(strtolower($header)), 'location') !== 0) {
                    continue;
                }
                $aline = explode(': ', $header);
                $target = trim($aline[1]);
                $urlParts = parse_url($target);
                if (!isset($urlParts['host'])) {
                    $initialRequestUrlParts = parse_url($url);
                    $target = $initialRequestUrlParts['scheme'] . "://" . $initialRequestUrlParts['host'] . $target;
                }
                return $target;
            }
        }
    }
}
