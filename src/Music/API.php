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
     *      album (only studio albums with primarytype album)
     *      discography (only offical, EP and musicBrainz website defaults are included)
     *      all (all releasegroups)
     * if more than 100 items paging is required and will take a long time!)
     * @return array()
     */
    public function doReleaseGroupSearch($artistId, $type)
    {
        if ($type == "album") {
            $incUrl = '%20AND%20primarytype:album'.
                      '%20NOT%20primarytype:single'.
                      '%20NOT%20primarytype:ep'.
                      '%20NOT%20primarytype:broadcast'.
                      '%20NOT%20primarytype:other'.
                      '%20NOT%20secondarytype:live'.
                      '%20NOT%20secondarytype:compilation'.
                      '%20NOT%20secondarytype:remix'.
                      '%20NOT%20secondarytype:interview'.
                      '%20NOT%20secondarytype:soundtrack'.
                      '%20NOT%20secondarytype:spokenword'.
                      '%20NOT%20secondarytype:audiobook'.
                      '%20NOT%20secondarytype:demo'.
                      '%20NOT%20secondarytype:mixtape/street'.
                      '%20NOT%20secondarytype:dj-mix'.
                      '%20NOT%20secondarytype:audio%20drama'.
                      '&limit=100';
        }
        if ($type == "discography") {
            $incUrl = '%20AND%20status:official'.
                      '%20NOT%20primarytype:single'.
                      '%20NOT%20primarytype:broadcast'.
                      '%20NOT%20primarytype:other'.
                      '&release-group-status:website-default'.
                      '&limit=100';
        }
        if ($type == "all") {
            $incUrl = '&limit=100';
        }
        $baseUrl = 'https://musicbrainz.org/ws/2/release-group?query=arid:';
        $url = $baseUrl . $artistId . $incUrl;
        $data = $this->execRequest($url . '&fmt=json');
        if ($data->count <= 100) {
            return $data->{'release-groups'};
        } else {
            return $this->paging($data, $url);
        }
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
                        '+artist-rels' .
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

    /**
     * If more than 100 items paging is required to get all items
     * @param object $data initial result of start query
     * @param string $url the complete url from initial request
     * @array
     */
    public function paging($data, $url)
    {
        $totalCount = $data->count;
        $initReleaseCount = count($data->{'release-groups'});
        $ReleaseGroups = array();
        $ReleaseGroups = array_merge($ReleaseGroups, $data->{'release-groups'});
        for ($offset = $initReleaseCount; $offset < $totalCount; $offset += 100) {
            sleep(1);
            $request = $this->execRequest($url . '&offset=' . $offset . '&fmt=json');
            $ReleaseGroups = array_merge($ReleaseGroups, $request->{'release-groups'});
        }
        if (count($ReleaseGroups) == $totalCount) {
            return $ReleaseGroups;
        } else {
            throw new \Exception("Failed to retrieve all release groups");
        }
    }

}
