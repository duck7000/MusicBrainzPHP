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
 * Get lyrics from lrclib for this MusicBrainz release
 * It includes plaintext lyrics, timed lyrics are available but not included
 * $replace is used for uncensoring censored words
 * @author ed (github user: duck7000)
 */
class Lyric extends MdbBase
{

    protected $lrclibApiUrl = 'https://lrclib.net/api/get?';
    protected $lrclibApiSearchUrl = 'https://lrclib.net/api/search?';
    protected $replace = array(
                'f*ck' => 'fuck',
                'f**ck' => 'fuck',
                'f**k' => 'fuck',
                'F*ck' => 'Fuck',
                'F**ck' => 'Fuck',
                'F**k' => 'Fuck',
                'sh*t' => 'shit',
                's**t' => 'shit',
                'Sh*t' => 'Shit',
                'S**t' => 'Shit'
            );

    /**
     * @param Config $config OPTIONAL override default config
     * @param LoggerInterface $logger OPTIONAL override default logger `\Imdb\Logger` with a custom one
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
    }

    /**
     * Get lrclib song lyric data
     * @param string $albumTitle release album title
     * @param string $trackArtist track artist
     * @param string $trackName track name
     * @param string $trackId track id number from MusicBrainz
     * @param string $trackLength track length (in seconds)
     * @return string lyric text or false
     */
    public function getLrclibData(
        $albumTitle,
        $trackArtist,
        $trackName,
        $trackId,
        $trackLength)
    {
        if (!empty($albumTitle) && !empty($trackArtist) && !empty($trackName)) {
            $albumTitle = urlencode($albumTitle);
            $trackArtist = urlencode($trackArtist);
            $trackName = urlencode($trackName);
            $url = $this->lrclibApiUrl .
                   'artist_name=' . $trackArtist .
                   '&track_name=' . $trackName .
                   '&album_name=' . $albumTitle;
            if (!empty($trackLength)) {
                $url .= '&duration=' . $trackLength;
            }
            $results = $this->exactMatchApiCall($trackId, $url);
            if ($results !== false) {
                return $results;
            }
            if ($this->config->apiSearch === true) {
                $searchUrl = $this->lrclibApiSearchUrl . 'track_name=' . $trackName . '&artist_name=' . $trackArtist;
                $searchResults = $this->searchApiCall($trackId, $searchUrl);
                if ($searchResults !== false) {
                    return $searchResults;
                }
            }
        }
        return false;
    }

    /**
     * Uncensor lyrics data
     * @param string $inputLyrics input censored lyrics text
     * @return string uncensored lyrics text
     */
    private function removeCensoring($inputLyrics)
    {
        if ($this->config->uncensor === true) {
            return strtr(trim($inputLyrics), $this->replace);
        } else {
            return trim($inputLyrics);
        }
    }

    /**
     * Get and process api call data
     * @param string $trackId Id tracknumber from MusicBrainz
     * @param string $url api call url
     * @return string if lyrics data found, false otherwise
     */
    private function exactMatchApiCall($trackId, $url)
    {
        $data = $this->api->checkCache($trackId, $url, "title", "_Lyric");
        if (isset($data->plainLyrics) && $data->plainLyrics != '') {
            return $this->removeCensoring($data->plainLyrics);
        } elseif (isset($data->instrumental) && $data->instrumental === true) {
            return 'Instrumental';
        }
        return false;
    }

    /**
     * Get and process api search call data (if there is no exact match)
     * @param string $trackId Id tracknumber from MusicBrainz
     * @param string $searchUrl api call search url
     * @return string if lyrics data found, false otherwise
     */
    private function searchApiCall($trackId, $searchUrl)
    {
        $searchData = $this->api->checkCache($trackId, $searchUrl, "title", "_Lyric");
        if (is_array($searchData) && count($searchData) > 0) {
            if (isset($searchData[0]->plainLyrics) && $searchData[0]->plainLyrics != '') {
                return $this->removeCensoring($searchData[0]->plainLyrics);
            } elseif (isset($searchData[0]->instrumental) && $searchData[0]->instrumental === true) {
                return 'Instrumental';
            }
        }
        return false;
    }

}
