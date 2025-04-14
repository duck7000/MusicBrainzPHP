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
 * It includes plaintext lyrics, timed lyrics are available
 * @author ed (github user: duck7000)
 */
class Lyric extends MdbBase
{

    protected $lrclibApiUrl = 'https://lrclib.net/api/get?';

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
     * @param string $trId track id
     * @param string $trackLength track length (in seconds)
     * @return array() or false
     */
    public function getLrclibData(
        $albumTitle,
        $trackArtist,
        $trackName,
        $trId,
        $trackLength)
    {
        $replace = array(
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
            $data = $this->api->checkCache($trId, $url, "title", "_Lyric");
            if (isset($data->plainLyrics) && $data->plainLyrics != '') {
                if ($this->config->uncensor == true) {
                    return strtr(trim($data->plainLyrics), $replace);
                } else {
                    return trim($data->plainLyrics);
                }
            } elseif (isset($data->instrumental)) {
                return 'Instrumental';
            } else {
                $searchData = $this->api->checkCache($trId, $searchUrl, "title", "_Lyric");
                if (is_array($searchData) && count($searchData) > 0) {
                    return $this->config->uncensor == true ?
                           strtr(trim($searchData[0]->plainLyrics), $replace) :
                           trim($searchData[0]->plainLyrics);
                } elseif (isset($searchData[0]->instrumental)) {
                    return 'Instrumental';
                }
            }
        }
        return false;
    }

}
