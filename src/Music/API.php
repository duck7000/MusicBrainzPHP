<?php

#############################################################################
# musicBrainzPHP                                ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

use Psr\Log\LoggerInterface;

/**
 * Accessing cd album information through musicBrainz API
 * @author Ed (duck7000)
 */
class Api
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * API constructor.
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct($logger, $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Search request
     * @param string $urlSuffix
     * @return \stdClass
     */
    public function doSearch($urlSuffix)
    {
        $baseUrl = $this->config->baseUrl;
        $incUrl = '%20AND%20format:' . $this->config->titleSearchFormat .
                  '&limit=' . $this->config->titleSearchAmount .
                  '&fmt=json';
        $url = $baseUrl . $urlSuffix . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Lookup request
     * @param string $mbID
     * @return \stdClass
     */
    public function doLookup($mbID)
    {
        $baseUrl = $this->config->baseUrl;
        $incUrl = '?inc=artist-credits' .
                        '+labels' .
                        '+discids' .
                        '+recordings' .
                        '+release-groups' .
                        '+genres' .
                        '+url-rels' .
                        '&fmt=json';
        $url = $baseUrl . $mbID . $incUrl;
        return $this->execRequest($url);
    }
    
    /**
     * Cover art lookup
     * @param string $mbID
     * @return \stdClass
     */
    public function doCoverArtLookup($mbID)
    {
        $url = $this->config->baseCoverArtUrl. $mbID . '/index.json';
        return $this->execRequest($url);
    }

    /**
     * Execute request
     * @param string $url
     * @return \stdClass
     */
    protected function execRequest($url)
    {
        $request = new Request($url, $this->config);
        $request->sendRequest();
        if (200 == $request->getStatus()) {
            return json_decode($request->getResponseBody());
        } elseif ($redirectUrl = $request->getRedirect()) {
            $request2 = new Request($redirectUrl, $this->config);
            $request2->sendRequest();
            if (200 == $request2->getStatus()) {
                return json_decode($request2->getResponseBody());
            } else {
                throw new \Exception("Failed to retrieve query");
            }
        } else {
            $this->logger->error(
                "[API] Failed to retrieve query. Response headers:{headers}. Response body:{body}",
                array('headers' => $request->getLastResponseHeaders(), 'body' => $request->getResponseBody())
            );
            throw new \Exception("Failed to retrieve query");
        }
    }

}
