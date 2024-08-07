<?php
#############################################################################
# musicBrainzPHP                                ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

use Psr\SimpleCache\CacheInterface;

/**
 * Get coverArt from specific release or release group ID on musicBrainz API
 * @author ed (github user: duck7000)
 */
class Cover extends MdbBase
{

    protected $coverArt = array();
    protected $releaseGroupcoverArt = array();

    /**
     * @param Config $config OPTIONAL override default config
     * @param LoggerInterface $logger OPTIONAL override default logger `\Imdb\Logger` with a custom one
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(Config $config = null, LoggerInterface $logger = null, CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
    }

    /**
     * Fetch Cover art from coverartarchive.org
     * @param $id string release or release group id
     * @param boolean $group true: get release group cover urls, false: get release cover urls
     * @return array
     * Array
     *   (
     *      [front] => Array
     *           (
     *               [id] => 22307139959
     *               [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
     *               [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
     *               [mediumUrl] => http://coverartarchive.org/release/527992ea-944f-3f5e-a078-3841f39afcec/18837628851-500.jpg
     *           )
     *       [back] => Array
     *           (
     *               [id] => 22307143843
     *               [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843.jpg
     *               [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843-250.jpg
     *               [mediumUrl] => http://coverartarchive.org/release/527992ea-944f-3f5e-a078-3841f39afcec/18837629318-500.jpg
     *           )
     *   )
     */
    public function fetchCoverArt($id, $group)
    {
        // Data request
        if ($group !== false) {
            $data = $this->api->doCoverArtLookupRelGroup($id);
            $arrayName = 'releaseGroupcoverArt';
            if ($data === false) {
                return $this->$arrayName;
            }
        } else {
            $data = $this->api->doCoverArtLookup($id);
            $arrayName = 'coverArt';
        }

        if (!empty($data->images) && $data->images != null) {
            $this->$arrayName['front'] = array();
            $this->$arrayName['back'] = array();
            foreach ($data->images as $value) {
                if ($value->front == 1) {
                    $this->$arrayName['front']['id'] = isset($value->id) ? $value->id : null;
                    $this->$arrayName['front']['originalUrl'] = isset($value->image) ? $value->image : null;

                    // thumbnail 250
                    $checkSmall = $this->checkCoverArtThumb($value, 250);
                    if ($checkSmall != false) {
                        $this->$arrayName['front']['thumbUrl'] = $value->thumbnails->$checkSmall;
                    }

                    // thumbnail 500
                    $checkLarge = $this->checkCoverArtThumb($value, 500);
                    if ($checkLarge != false) {
                        $this->$arrayName['front']['mediumUrl'] = $value->thumbnails->$checkLarge;
                    }
                    continue;
                }
                if ($value->back == 1) {
                    $this->$arrayName['back']['id'] = isset($value->id) ? $value->id : null;
                    $this->$arrayName['back']['originalUrl'] = isset($value->image) ? $value->image : null;

                    // thumbnail 250
                    $checkSmall = $this->checkCoverArtThumb($value, 250);
                    if ($checkSmall != false) {
                        $this->$arrayName['back']['thumbUrl'] = $value->thumbnails->$checkSmall;
                    }

                    // thumbnail 500
                    $checkLarge = $this->checkCoverArtThumb($value, 500);
                    if ($checkLarge != false) {
                        $this->$arrayName['back']['mediumUrl'] = $value->thumbnails->$checkLarge;
                    }
                    continue;
                }
            }
        }
        return $this->$arrayName;
    }

    /**
     * Check if array thumbnails has text or number keys e.g (small versus 250 or large versus 500)
     * @param int $size wanted size like 250 or 500
     * @return thumbnail fieldname
     */
    private function checkCoverArtThumb($value, $size)
    {
        $thumbNumber = strval(250);
        $small = 'small';
        $mediumNumber = strval(500);
        $medium = 'large';
        if(isset($value->thumbnails) && !empty($value->thumbnails)) {
            if ($size == 250) {
                if (isset($value->thumbnails->$thumbNumber)) {
                    return $thumbNumber;
                } elseif (isset($value->thumbnails->small)) {
                    return $small;
                } else {
                    return false;
                }
            } else {
                if (isset($value->thumbnails->$mediumNumber)) {
                    return $mediumNumber;
                } elseif (isset($value->thumbnails->large)) {
                    return $medium;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

}
