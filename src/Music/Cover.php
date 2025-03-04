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
 * Get coverArt from specific release or release group ID on musicBrainz API
 * @author ed (github user: duck7000)
 */
class Cover extends MdbBase
{

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
     * Fetch Cover art from coverartarchive.org
     * @param $id string release or release group id
     * @param boolean $group true: get release group cover urls, false: get release cover urls
     * @return array
     * Array
     *      [coverArt] => Array()
     *          [front] => Array()
     *              [id] => 22307139959
     *              [originalUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
     *              [thumbUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
     *              [mediumUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-500.jpg
     *              [largeUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-1200.jpg
     *          [back] => Array()
     *              [id] => 22307143843
     *              [originalUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843.jpg
     *              [thumbUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843-250.jpg
     *              [mediumUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843-500.jpg
     *              [largeUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843-1200.jpg
     *          [booklet] => Array()
     *              [0] => Array()
     *                  [id] => 22307145267
     *                  [originalUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307145267.jpg
     *                  [thumbUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307145267-250.jpg
     *                  [mediumUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307145267-500.jpg
     *                  [largeUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307145267-1200.jpg
     *              [1] => Array()
     *                  [id] => 22307146856
     *                  [originalUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307146856.jpg
     *                  [thumbUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307146856-250.jpg
     *                  [mediumUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307146856-500.jpg
     *                  [largeUrl] => https://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307146856-1200.jpg
     */
    public function fetchCoverArt($id, $group)
    {
        // Data request
        if ($group !== false) {
            $data = $this->api->doCoverArtLookupRelGroup($id);
            if (empty($data->images)) {
                return false;
            }
        } else {
            $data = $this->api->doCoverArtLookup($id);
            if (empty($data->images)) {
                return false;
            }
        }
        $coverArt = array();
        foreach ($data->images as $value) {
            if (!empty($value->front) && $value->front == 1) {
                $type = 'front';
            } elseif (!empty($value->back) && $value->back == 1) {
                $type = 'back';
            } elseif (!empty($value->types[0]) && $value->types[0] == 'Booklet') {
                $type = 'booklet';
            } else {
                continue;
            }
            // thumbnail 250
            $thumbUrl = null;
            $checkSmall = $this->checkCoverArtThumb($value, 250);
            if ($checkSmall != false) {
                $thumbUrl= $this->checkHttp($value->thumbnails->$checkSmall);
            }
            // thumbnail 500
            $mediumUrl = null;
            $checkMedium = $this->checkCoverArtThumb($value, 500);
            if ($checkMedium != false) {
                $mediumUrl = $this->checkHttp($value->thumbnails->$checkMedium);
            }
            // thumbnail 1200
            $largeUrl = null;
            $checkLarge = $this->checkCoverArtThumb($value, 1200);
            if ($checkLarge != false) {
                $largeUrl = $this->checkHttp($value->thumbnails->$checkLarge);
            }
            $images = array(
                'id' => isset($value->id) ?
                              $value->id : null,
                'originalUrl' => isset($value->image) ?
                                       $this->checkHttp($value->image) : null,
                'thumbUrl' => $thumbUrl,
                'mediumUrl' => $mediumUrl,
                'largeUrl' => $largeUrl
            );
            if ($type == 'booklet') {
                $coverArt[$type][] = $images;
            } else {
                $coverArt[$type] = $images;
            }
        }
        return $coverArt;
    }

    /**
     * Check if array thumbnails has text or number keys e.g (small versus 250 or large versus 500)
     * @param int $size wanted size like 250 or 500
     * @return thumbnail fieldname
     */
    private function checkCoverArtThumb($value, $size)
    {
        // strval is nessecary, int number will not be accepted
        $thumbNumber = strval(250);
        $small = 'small';
        $mediumNumber = strval(500);
        $medium = 'large';
        $largeNumber = strval(1200);
        // 250 pixels version
        if ($size == 250) {
            if (!empty($value->thumbnails->$thumbNumber)) {
                return $thumbNumber;
            } elseif (!empty($value->thumbnails->small)) {
                return $small;
            } else {
                return false;
            }
        // 500 pixels version
        } elseif ($size == 500) {
            if (!empty($value->thumbnails->$mediumNumber)) {
                return $mediumNumber;
            } elseif (!empty($value->thumbnails->large)) {
                return $medium;
            } else {
                return false;
            }
        // 1200 pixels version (there is no version in text form like large or small)
        } else {
            if (!empty($value->thumbnails->$largeNumber)) {
                return $largeNumber;
            } else {
                return false;
            }
        }
    }
    
    /**
     * Change http to https (coverarttarget returns random http or https so this causes browser warnings)
     * @param string $inputUrl http url
     * @return https url
     */
    private function checkHttp($inputUrl)
    {
        $splitUrl = explode(":", $inputUrl, 2);
        return 'https:' . $splitUrl[1];
    }

}
