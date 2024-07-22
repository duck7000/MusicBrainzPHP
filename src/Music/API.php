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
use Psr\SimpleCache\CacheInterface;

/**
 * Accessing cd album information through musicBrainz API
 * @author Ed (duck7000)
 */
class Api
{

    /**
     * @var CacheInterface
     */
    private $cache;

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
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct($cache, $logger, $config)
    {
        $this->cache = $cache;
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
        $incUrl = '%20AND%20format:' . $this->config->titleSearchFormat;
        if (stripos($this->config->titleSearchFormat, "all") !== false) {
            $incUrl = '';
        }
        $incUrl .= '&limit=' . $this->config->titleSearchAmount .
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
     * Search for Artist bio info
     * @param string $artistId Artist Id
     * @return \stdClass
     */
    public function doArtistBioLookup($artistId)
    {
        $baseArtistUrl = 'https://musicbrainz.org/ws/2/artist/';
        $incUrl = '?&fmt=json';
        $url = $baseArtistUrl . $artistId . $incUrl;
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
        $releaseType = "release-groups";
        $cacheNameExtension = '_' . $type;
        return $this->checkCache($artistId, $url, $releaseType, $cacheNameExtension);
    }

    /**
     * Search for all Various Artist releases inside releasegroup id in TitleSearchAdvanced class
     * @param string $relGroupId
     * @return \stdClass
     */
    public function doReleaseGroupReleasesVarious($artistId)
    {
        $baseUrl = 'https://musicbrainz.org/ws/2/artist/';
        $incUrl = '?type=soundtrack|compilation' .
                  '&inc=releases+various-artists' .
                  '&status=official' .
                  '&limit=100' .
                  '&fmt=json';
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
        $incUrl = '&limit=100';
        $url = $baseRelGroupUrl . $relGroupId . $incUrl;
        $releaseType = "releases";
        return $this->checkCache($relGroupId, $url, $releaseType);
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
        $url = 'https://coverartarchive.org/release/' . $mbID;
        return $this->execRequest($url);
    }

    /**
     * Cover art lookup in Title Class for release group images
     * @param string $rgid release group id
     * @return \stdClass
     */
    public function doCoverArtLookupRelGroup($rgid)
    {
        $url = 'https://coverartarchive.org/release-group/' . $rgid;
        return $this->execRequest($url, true);
    }

    /**
     * Execute request
     * @param string $url
     * @return \stdClass
     */
    protected function execRequest($url, $cover = false)
    {
        $request = new Request($url, $this->config);
        $request->sendRequest();
        if (200 == $request->getStatus() || 307 == $request->getStatus()) {
            return json_decode($request->getResponseBody());
        } else {
            $this->logger->error(
                "[API] Failed to retrieve query. Response headers:{headers}. Response body:{body}",
                array('headers' => $request->getLastResponseHeaders(), 'body' => $request->getResponseBody())
            );
            if ($cover !== false) {
                return false;
            } else {
                throw new \Exception("Failed to retrieve query");
            }
        }
    }

    /**
     * If more than 100 items paging is required to get all items
     * @param object $data initial result of start query
     * @param string $url the complete url from initial request
     * @param string $releaseType release type from url string like release or release-groups
     * @array
     */
    public function paging($data, $url, $releaseType)
    {
        $totalCount = $data->count;
        $initReleaseCount = count($data->$releaseType);
        $ReleaseGroups = array();
        $ReleaseGroups = array_merge($ReleaseGroups, $data->$releaseType);
        for ($offset = $initReleaseCount; $offset < $totalCount; $offset += 100) {
            sleep(1);
            $request = $this->execRequest($url . '&offset=' . $offset . '&fmt=json');
            $ReleaseGroups = array_merge($ReleaseGroups, $request->$releaseType);
        }
        return $ReleaseGroups;
    }

    /**
     * Check if caching can be used
     * @param string $artistId artist id from doArtistSearch()
     * @param string $url exec url
     * @param string $releaseType release type from url string like release or release-groups
     * @param string $cacheNameExtension filename extension for different options e.g _all (for option all)
     * @return \stdClass
     */
    public function checkCache($id, $url, $releaseType, $cacheNameExtension = '')
    {
        $key = $id . $cacheNameExtension . '.json';
        $fromCache = $this->cache->get($key);

        if ($fromCache != null) {
            return json_decode($fromCache);
        }

        $data = $this->execRequest($url . '&fmt=json');

        if ($data->count <= 100) {
            $results = $data->$releaseType;
            $this->cache->set($key, json_encode($results));
            return $results;
        } else {
            $results = $this->paging($data, $url, $releaseType);
            $this->cache->set($key, json_encode($results));
            return $results;
        }
    }

}
