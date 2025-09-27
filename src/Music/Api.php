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
     * @var baseUrl
     */
    private $baseUrl = 'https://musicbrainz.org/ws/2/';
    
    /**
     * @var baseCoverUrl
     */
    private $baseCoverUrl = 'https://coverartarchive.org/';

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
     * @param string $format override default format from config
     * @return \stdClass
     */
    public function doSearch($urlSuffix, $format)
    {
        $setFormat = empty($format) ? $this->config->titleSearchFormat : $format;
        $entity = 'release/';
        if (stripos($setFormat, "all") !== false) {
            $incUrl = '';
        } else {
            $incUrl = '%20AND%20format:' . $setFormat;
        }
        $incUrl .= '&limit=' . $this->config->titleSearchAmount .
                   '&fmt=json';
        $url = $this->baseUrl . $entity . $urlSuffix . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Search request for TitleSearch class on discid
     * @param string $discid musicbrainz discid
     * @return \stdClass
     */
    public function doDiscidSearch($discid)
    {
        $entity = 'discid/';
        $incUrl = '?inc=artists' .
                  '+labels' .
                  '+release-groups' .
                  '&cdstubs=no' .
                  '&fmt=json';
        $url = $this->baseUrl . $entity . $discid . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Search for specific artist name in TitleSearchAdvanced class, this is not the same as normal artist search in TitleSearch class!
     * @param string $urlSuffix
     * @return \stdClass
     */
    public function doArtistSearch($urlSuffix)
    {
        $entity = 'artist/';
        $incUrl = '&limit=25' .
                  '&fmt=json';
        $url = $this->baseUrl . $entity . $urlSuffix . $incUrl;
        return $this->execRequest($url);
    }

    /**
     * Search for Artist bio info
     * @param string $artistId Artist Id
     * @return \stdClass
     */
    public function doArtistBioLookup($artistId)
    {
        $entity = 'artist/';
        $incUrl = '?&inc=aliases';
        $url = $this->baseUrl . $entity . $artistId . $incUrl;
        $releaseType = "title";
        $cacheNameExtension = '_bio';
        return $this->checkCache($artistId, $url, $releaseType, $cacheNameExtension);
    }

    /**
     * Search for all releasegroups of specific artistId in TitleSearchAdvanced class
     * @param string $artistId
     * @param string $type Include only this type in search, exclude all others
     * values for $type:
     *      album (musicBrainz website defaults with primarytype album only)
     *      discography (musicBrainz website defaults are included)
     *      all (all releasegroups)
     * if more than 100 items paging is required and will take a long time!)
     * @return array()
     */
    public function doReleaseGroupSearch($artistId, $type)
    {
        if ($type == "album") {
            $incUrl = '&release-group-status=website-default'.
                      '&inc=artist-credits'.
                      '&type=album' .
                      '&limit=100';
        }
        if ($type == "discography") {
            $incUrl = '&release-group-status=website-default'.
                      '&inc=artist-credits'.
                      '&limit=100';
        }
        if ($type == "all") {
            $incUrl = '&release-group-status=all'.
                      '&inc=artist-credits'.
                      '&limit=100';
        }
        $entitiy = 'release-group?&artist=';
        $url = $this->baseUrl . $entitiy . $artistId . $incUrl;
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
        $entitiy = 'artist/';
        $incUrl = '?&inc=releases+various-artists' .
                  '&status=official' .
                  '&limit=100';
        $url = $this->baseUrl . $entitiy . $artistId . $incUrl;
        $releaseType = "title";
        $cacheNameExtension = '_various';
        return $this->checkCache($artistId, $url, $releaseType, $cacheNameExtension);
    }

    /**
     * Search for all releases inside releasegroup id in TitleSearchAdvanced class
     * @param string $relGroupId
     * @return \stdClass
     */
    public function doReleaseGroupReleases($relGroupId)
    {
        $entitiy = 'release?query=rgid:';
        $incUrl = '&limit=100';
        $url = $this->baseUrl . $entitiy . $relGroupId . $incUrl;
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
        $entitiy = 'release/';
        $incUrl = '?inc=artist-credits' .
                  '+labels' .
                  '+discids' .
                  '+recordings' .
                  '+release-groups' .
                  '+release-group-level-rels' .
                  '+genres' .
                  '+tags' .
                  '+url-rels' .
                  '+annotation' .
                  '+artist-rels' .
                  '+aliases' .
                  '+area-rels';
        $url = $this->baseUrl . $entitiy . $mbID . $incUrl;
        $releaseType = "title";
        return $this->checkCache($mbID, $url, $releaseType);
    }

    /**
     * Cover art lookup in Title Class
     * @param string $mbID
     * @return \stdClass
     */
    public function doCoverArtLookup($mbID)
    {
        $entitiy = 'release/';
        $url = $this->baseCoverUrl . $entitiy . $mbID;
        $releaseType = "title";
        $cacheNameExtension = '_mbidCover';
        return $this->checkCache($mbID, $url, $releaseType, $cacheNameExtension);
    }

    /**
     * Cover art lookup in Title Class for release group images
     * @param string $rgid release group id
     * @return \stdClass
     */
    public function doCoverArtLookupRelGroup($rgid)
    {
        $entitiy = 'release-group/';
        $url = $this->baseCoverUrl . $entitiy . $rgid;
        $releaseType = "title";
        $cacheNameExtension = '_rgidCover';
        return $this->checkCache($rgid, $url, $releaseType, $cacheNameExtension);
    }

    /**
     * Execute request
     * @param string $url
     * @return \stdClass
     */
    public function execRequest($url)
    {
        $request = new Request($url, $this->config);
        $request->sendRequest();
        if (200 == $request->getStatus() || 307 == $request->getStatus()) {
            return json_decode($request->getResponseBody());
        } elseif (404 == $request->getStatus()) {
            return false;
        } else {
            $this->logger->error(
                "[API] Failed to retrieve query. Response headers:{headers}. Response body:{body}",
                array('headers' => $request->getLastResponseHeaders(), 'body' => $request->getResponseBody())
            );
            if ($this->config->throwHttpExceptions) {
                throw new \Exception("Failed to retrieve query");
            } else {
                return new \StdClass();
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
        $totalCount = $releaseType == "release-groups" ? $data->{"release-group-count"} : $data->count;
        $initReleaseCount = count($data->{"$releaseType"});
        $releaseGroups = array();
        $releaseGroups = array_merge($releaseGroups, $data->{"$releaseType"});
        for ($offset = $initReleaseCount; $offset < $totalCount; $offset += 100) {
            sleep(1);
            $request = $this->execRequest($url . '&offset=' . $offset . '&fmt=json');
            $releaseGroups = array_merge($releaseGroups, $request->{"$releaseType"});
        }
        return $releaseGroups;
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

        // check for release, cover urls or wiki
        if (strpos($cacheNameExtension, "Cover") !== false ||
            strpos($cacheNameExtension, "Lyric") !== false ||
            strpos($cacheNameExtension, "Wiki") !== false) {
            $data = $this->execRequest($url);
        } else {
            $data = $this->execRequest($url . '&fmt=json');
        }

        // check which releaseType is set
        if ($releaseType == "title") {
            $this->cache->set($key, json_encode($data));
            return $data;
        }
        $totalReturnCount = $releaseType == "release-groups" ? $data->{"release-group-count"} : $data->count;
        if ($totalReturnCount <= 100) {
            $results = $data->{"$releaseType"};
            $this->cache->set($key, json_encode($results));
            return $results;
        } else {
            $results = $this->paging($data, $url, $releaseType);
            $this->cache->set($key, json_encode($results));
            return $results;
        }
    }

}
