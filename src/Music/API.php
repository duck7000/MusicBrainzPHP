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
     * Search request for TitleSearch class
     * @param string $urlSuffix
     * @return \stdClass
     */
    public function doSearch($urlSuffix)
    {
        $baseUrl = 'https://musicbrainz.org/ws/2/release/';
        $incUrl = '%20AND%20format:' . $this->config->titleSearchFormat .
                  '&limit=' . $this->config->titleSearchAmount .
                  '&fmt=json';
        $url = $baseUrl . $urlSuffix . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Search for specific artist name in TitleSearchAdvanced class, this is not the same as normal artist search in TitleSearch class!
     * @param string $urlSuffix
     * @return \stdClass
     */
    public function doArtistSearch($urlSuffix)
    {
        $baseArtistUrl = 'https://musicbrainz.org/ws/2/artist/';
        $incUrl = '&limit=25' .
                  '&fmt=json';
        $url = $baseArtistUrl . $urlSuffix . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Search for all releasegroups of specific artistId in TitleSearchAdvanced class
     * @param string $artistId
     * @param string $type Include only this type in search, exclude all others
     * values for $type:
     *      album
     *      single
     *      ep
     *      broadcast
     *      other
     *      live
     *      compilation
     *      remix
     *      interview
     *      soundtrack
     *      spokenword
     *      audiobook
     *      demo
     * @return \stdClass
     */
    public function doReleaseGroupSearch($artistId, $type)
    {
        $releaseTypes = array(
            "primarytype:album",
            "primarytype:single",
            "primarytype:ep",
            "primarytype:broadcast",
            "primarytype:other",
            "secondarytype:live",
            "secondarytype:compilation",
            "secondarytype:remix",
            "secondarytype:interview",
            "secondarytype:soundtrack",
            "secondarytype:spokenword",
            "secondarytype:audiobook",
            "secondarytype:demo"
        );
        $incUrl = '';
        foreach ($releaseTypes as $releaseType) {
            if (stripos($releaseType, $type) !== false) {
                $incUrl .= '%20AND%20' . $releaseType;
            } else {
                $incUrl .= '%20NOT%20' . $releaseType;
            }
        }
        $incUrl .= '&limit=100&fmt=json';
        $baseUrl = 'https://musicbrainz.org/ws/2/release-group?query=arid:';
        $url = $baseUrl . $artistId . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Search for all releases inside releasegroup id in TitleSearchAdvanced class
     * @param string $relGroupId
     * @return \stdClass
     */
    public function doReleaseGroupReleases($relGroupId)
    {
        $baseRelGroupUrl = 'https://musicbrainz.org/ws/2/release?query=rgid:';
        $incUrl = '&fmt=json';
        $url = $baseRelGroupUrl . $relGroupId . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Lookup request in Title class
     * @param string $mbID
     * @return \stdClass
     */
    public function doLookup($mbID)
    {
        $baseUrl = 'https://musicbrainz.org/ws/2/release/';
        $incUrl = '?inc=artist-credits' .
                        '+labels' .
                        '+discids' .
                        '+recordings' .
                        '+release-groups' .
                        '+genres' .
                        '+tags' .
                        '+url-rels' .
                        '+annotation' .
                        '&fmt=json';
        $url = $baseUrl . $mbID . $incUrl;
        return $this->execRequest($url);
    }
    
    /**
     * Cover art lookup in Title Class
     * @param string $mbID
     * @return \stdClass
     */
    public function doCoverArtLookup($mbID)
    {
        $url = 'https://ia600903.us.archive.org/6/items/mbid-' . $mbID . '/index.json';
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
