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
 * A title on musicBrainz API
 * @author ed (github user: duck7000)
 */
class Title extends MdbBase
{

    protected $art = null;
    protected $title = null;
    protected $artist = array();
    protected $year = null;
    protected $date = null;
    protected $firstReleaseDate = null;
    protected $country = null;
    protected $length = null;
    protected $barcode = null;
    protected $status = null;
    protected $packaging = null;
    protected $primaryType = null;
    protected $secondaryTypes = array();
    protected $releaseGroupId = null;
    protected $genres = array();
    protected $releaseGroupGenres = array();
    protected $tags = array();
    protected $labels = array();
    protected $media = array();
    protected $totalLength = 0;
    protected $relations = array();
    protected $coverArt = array();
    protected $releaseGroupcoverArt = array();
    protected $annotation = null;
    protected $disambiguation = null;

    /**
     * @param string $id musicBrainz id
     * @param Config $config OPTIONAL override default config
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct($id, Config $config = null, LoggerInterface $logger = null, CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
        $this->setid($id);
        $this->art = new Cover();
    }

    /**
     * Fetch all data of a mbID
     * @return array
     * Array
        * (
            * [id] => 095e2e2e-60c4-4f9f-a14a-2cc1b468bf66
            * [title] => Nightclubbing
            * [artist] => Array
                * (
                    * [name] => Grace Jones
                    * [id] => b1c124b3-cf60-41a6-8699-92728c8a3fe0
                * )
            * [year] => 1987
            * [date] => 1987-10-01 (this is the date of this specific release)
            * [firstReleaseDate] => 1980-07-25 (this is the release group first release date)
            * [country] => Europe
            * [length] => 2288
            * [barcode] => 4007192534814
            * [annotation] => Manufactured in Germany by Record Service GmbH, Alsdorf.
            * [disambiguation] => price code CA 835
            * [status] => Official
            * [packaging] => Jewel Case
            * [primaryType] => Album
            * [secondaryTypes] => Array
                * (
                    * [0] => Compilation
                * )
            * [releaseGroupId] => c9673ff0-15b5-394d-a5ec-3d2a27dfce83
            * [genres] => Array
                * (
                    * [0] => art pop
                    * [1] => dub
                * )
            * [releaseGroupGenres] => Array
                * (
                    * [0] => ballad
                    * [1] => pop
                    * [2] => schlager
                * )
            * [tags] => Array
                * (
                    * [0] => art pop
                    * [1] => dub
                * )
            * [labels] => Array
                * (
                    * [0] => Array
                        * (
                            * [name] => Island
                            * [id] => dfd92cd3-4888-46d2-b968-328b1feb2642
                            * [type] => Imprint
                            * [code] => 407
                            * [catalog] => 253 481
                        * )
                        * 
                    * [1] => Array
                        * (
                            * [name] => Island
                            * [id] => dfd92cd3-4888-46d2-b968-328b1feb2642
                            * [type] => Imprint
                            * [code] => 407
                            * [catalog] => CID 9624 (90 093-2)
                        * )
                        * 
                * )
            * [media] => Array
                * (
                    * [0] => Array
                        * (
                            * [mediumTitle] => live from atlantic studios
                            * [format] => CD
                            * [tracks] => Array
                                * (
                                    * [0] => Array
                                        * (
                                            * [id] => 5f76ca86-a0ab-4674-acdf-aa8a19042100
                                            * [number] => 1
                                            * [title] => Walking in the Rain
                                            * [artist] => Array
                                               * (
                                                   * [0] => Array
                                                       * (
                                                           * [name] => Kutmasta Kurt
                                                           * [id] => abf9f319-da2f-4fdf-a3e4-40c4d0b0075d
                                                           * [joinphrase] =>  feat. 
                                                       * )
                                                   * [1] => Array
                                                       * (
                                                           * [name] => Motion Man
                                                           * [id] => 1cee1f74-179d-446d-8347-de31e8202f2b
                                                           * [joinphrase] =>
                                                       * )
                                               * )
                                            * [length] => 258
                                        * )
                                * )
                        * )
                * )
            * [relations] => Array
                * (
                    * [artist] => Array
                        * (
                            * [0] => Array
                                * (
                                    * [type] => instrument
                                    * [begin] => 1977-01
                                    * [end] => 1977-02
                                    * [artist] => Array
                                        * (
                                            * [name] => Mark Evans
                                            * [id] => 6d3da6cf-d443-4ebc-9eac-98456bc2def3
                                            * [disambiguation] => Australian bass guitarist
                                        * )
                                    * [attributes] => Array
                                        * (
                                            * [0] => bass guitar
                                        * )
                                * )
                        * )

                    * [url] => Array
                        * (
                            * [0] => Array
                                * (
                                    * [type] => amazon asin
                                    * [url] => https://www.amazon.com/gp/product/B00008WT5C
                                * )
                            * [1] => Array
                                * (
                                    * [type] => discogs
                                    * [url] => https://www.discogs.com/release/473692
                                * )
                        * )
                * )
                * [coverArt] => Array
                * (
                    *   [front] array
                        * (
                        *   [id] => 22307139959
                        *   [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
                        *   [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
                        *   [mediumUrl] => http://coverartarchive.org/release/527992ea-944f-3f5e-a078-3841f39afcec/18837628851-500.jpg
                        * )
                    *   [back]  array
                        * (
                        *   [id] => 22307139959
                        *   [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
                        *   [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
                        *   [mediumUrl] => http://coverartarchive.org/release/527992ea-944f-3f5e-a078-3841f39afcec/18837629318-500.jpg
                        * )
                * )
                * [releaseGroupcoverArt] => Array
                * (
                    *   [front] array
                        * (
                        *   [id] => 22307139959
                        *   [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
                        *   [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
                        *   [mediumUrl] => http://coverartarchive.org/release/527992ea-944f-3f5e-a078-3841f39afcec/18837628851-500.jpg
                        * )
                    *   [back]  array
                        * (
                        *   [id] => 22307139959
                        *   [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
                        *   [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
                        *   [mediumUrl] => http://coverartarchive.org/release/527992ea-944f-3f5e-a078-3841f39afcec/18837629318-500.jpg
                        * )
                * )
        * )
     */
    public function fetchData()
    {
        // Data request
        $data = $this->api->doLookup($this->mbID);

        $this->title = isset($data->title) ? $data->title : null;
        $this->barcode = isset($data->barcode) ? $data->barcode : null;
        $this->status = isset($data->status) ? $data->status : null;
        $this->packaging = isset($data->packaging) ? $data->packaging : null;
        $this->year = isset($data->date) ? strtok($data->date, '-') : null;
        $this->date = isset($data->date) ? $data->date : null;
        $this->firstReleaseDate = isset($data->{'release-group'}->{'first-release-date'}) ? $data->{'release-group'}->{'first-release-date'} : null;
        $this->country = isset($data->{'release-events'}[0]->area->name) ? $data->{'release-events'}[0]->area->name : null;
        $this->primaryType = isset($data->{'release-group'}->{'primary-type'}) ? $data->{'release-group'}->{'primary-type'} : null;
        $this->releaseGroupId = isset($data->{'release-group'}->id) ? $data->{'release-group'}->id : null;
        $this->annotion = isset($data->annotion) ? $data->annotion : null;
        $this->disambiguation = isset($data->disambiguation) ? $data->disambiguation : null;

        // Secondary Types
        if (isset($data->{'release-group'}->{'secondary-types'}) && !empty($data->{'release-group'}->{'secondary-types'})) {
            foreach ($data->{'release-group'}->{'secondary-types'} as $secType) {
                $this->secondaryTypes[] = $secType;
            }
        }

        // Artist
        if (isset($data->{'artist-credit'}) && !empty($data->{'artist-credit'})) {
            foreach ($data->{'artist-credit'} as $credit) {
                $this->artist[] = array(
                    'name' => isset($credit->artist->name) ? $credit->artist->name : null,
                    'id' => isset($credit->artist->id) ? $credit->artist->id : null
                );
            }
        }

        // Genres
        if (isset($data->genres) && !empty($data->genres)) {
            foreach ($data->genres as $genre) {
                $this->genres[] = isset($genre->name) ? $genre->name : null;
            }
        }

        // Release-group Genres
        if (isset($data->{'release-group'}->genres) && !empty($data->{'release-group'}->genres)) {
            foreach ($data->{'release-group'}->genres as $relGenre) {
                $this->releaseGroupGenres[] = isset($relGenre->name) ? $relGenre->name : null;
            }
        }

        // Tags
        if (isset($data->tags) && !empty($data->tags)) {
            foreach ($data->tags as $tag) {
                $this->tags[] = isset($tag->name) ? $tag->name : null;
            }
        }

        // Relations
        if (isset($data->relations) && !empty($data->relations)) {
            foreach ($data->relations as $relation) {
                if ($relation->{'target-type'} == "artist") {

                    // attributes
                    $attributes = array();
                    if (isset($relation->attributes) && !empty($relation->attributes)) {
                        foreach ($relation->attributes as $attribute) {
                            $attributes[] = $attribute;
                        }
                    }

                    // Artist
                    $artist = array();
                    if (isset($relation->artist) && !empty($relation->artist)) {
                        $artist = array(
                            'name' => isset($relation->artist->name) ? $relation->artist->name : null,
                            'id' => isset($relation->artist->id) ? $relation->artist->id : null,
                            'disambiguation' => isset($relation->artist->disambiguation) ? $relation->artist->disambiguation : null
                        );
                    }
                    
                    $this->relations['artist'][] = array(
                        'type' => isset($relation->type) ? $relation->type : null,
                        'begin' => isset($relation->begin) ? $relation->begin : null,
                        'end' => isset($relation->end) ? $relation->end : null,
                        'artist' => $artist,
                        'attributes' => $attributes
                    );
                }
                if ($relation->{'target-type'} == "url") {
                    $this->relations['url'][] = array(
                        'type' => isset($relation->type) ? $relation->type : null,
                        'url' => isset($relation->url->resource) ? $relation->url->resource : null
                    );
                }
            }
        }

        // Labels
        if (isset($data->{'label-info'}) && !empty($data->{'label-info'})) {
            foreach ($data->{'label-info'} as $label) {
                $label = array(
                    'name' => isset($label->label->name) ? $label->label->name : null,
                    'id' => isset($label->label->id) ? $label->label->id : null,
                    'type' => isset($label->label->type) ? $label->label->type : null,
                    'code' => isset($label->label->{'label-code'}) ? $label->label->{'label-code'} : null,
                    'catalog' => isset($label->{'catalog-number'}) ? $label->{'catalog-number'} : null
                );
                $this->labels[] = $label;
            }
        }

        // Media
        if (isset($data->media) && !empty($data->media)) {
            foreach ($data->media as $medium) {
                $format = isset($medium->format) ? $medium->format : null;
                $mediumTitle = isset($medium->title) ? $medium->title : null;
                $cdTracks = array();
                if (isset($medium->tracks) && !empty($medium->tracks)) {
                    foreach ($medium->tracks as $track) {

                        // Artist
                        $artistTrackCredit = array();
                        if (isset($track->{'artist-credit'}) && !empty($track->{'artist-credit'})) {
                            foreach ($track->{'artist-credit'} as $trackCredit) {
                                $artistTrackCredit[] = array(
                                    'name' => isset($trackCredit->artist->name) ? $trackCredit->artist->name : null,
                                    'id' => isset($trackCredit->artist->id) ? $trackCredit->artist->id : null,
                                    'joinphrase' => isset($trackCredit->joinphrase) ? $trackCredit->joinphrase : null
                                );
                            }
                        }
                        $cdTracks[] = array(
                            'id' => isset($track->id) ? $track->id : null,
                            'number' => isset($track->number) ? $track->number : null,
                            'title' => isset($track->title) ? $track->title : null,
                            'artist' => $artistTrackCredit,
                            'length' => isset($track->length) ? round($track->length / 1000) : null
                        );
                        $this->totalLength = $this->totalLength + round($track->length / 1000);
                    }
                }
                $this->media[] = array(
                    'mediumTitle' => $mediumTitle,
                    'format' => $format,
                    'tracks' => $cdTracks
                );
            }
        }

        // CoverArt
        if (isset($data->{'cover-art-archive'}->count) && $data->{'cover-art-archive'}->count > 0) {
            $this->coverArt = $this->art->fetchCoverArt($this->mbID, false);
        }

        // Release Group Cover Art
        if ($this->releaseGroupId != null) {
            $this->releaseGroupcoverArt = $this->art->fetchCoverArt($this->releaseGroupId, true);
        }

        // results array
        $results = array(
            'id' => $this->mbID,
            'title' => $this->title,
            'artist' => $this->artist,
            'year' => $this->year,
            'date' => $this->date,
            'firstReleaseDate' => $this->firstReleaseDate,
            'country' => $this->country,
            'length' => $this->totalLength,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'packaging' => $this->packaging,
            'primaryType' => $this->primaryType,
            'secondaryTypes' => $this->secondaryTypes,
            'releaseGroupId' => $this->releaseGroupId,
            'genres' => $this->genres,
            'releaseGroupGenres' => $this->releaseGroupGenres,
            'tags' => $this->tags,
            'labels' => $this->labels,
            'media' => $this->media,
            'relations' => $this->relations,
            'annotation' => $this->annotion,
            'disambiguation' => $this->disambiguation,
            'coverArt' => $this->coverArt,
            'releaseGroupcoverArt' => $this->releaseGroupcoverArt
        );
        return $results;
    }

}
