<?php
#############################################################################
# musicBrainzPHP                                ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

/**
 * A title on musicBrainz API
 * @author ed (github user: duck7000)
 */
class Title extends MdbBase
{

    protected $title = null;
    protected $artist = array();
    protected $year = null;
    protected $date = null;
    protected $country = null;
    protected $length = null;
    protected $barcode = null;
    protected $status = null;
    protected $packaging = null;
    protected $type = null;
    protected $genres = array();
    protected $releaseGroupGenres = array();
    protected $tags = array();
    protected $labels = array();
    protected $media = array();
    protected $totalLength = 0;
    protected $relations = array();
    protected $coverArt = array();
    protected $annotation = null;
    protected $disambiguation = null;

    /**
     * @param string $id musicBrainz id
     * @param Config $config OPTIONAL override default config
     */
    public function __construct($id, Config $config = null, LoggerInterface $logger = null)
    {
        parent::__construct($config, $logger);
        $this->setid($id);
    }

    /**
     * Fetch all data of a mbID
     * @return array
     * Array
        * (
            * [id] => 095e2e2e-60c4-4f9f-a14a-2cc1b468bf66
            * [title] => Nightclubbing
            * [aritst] => Array
                * (
                    * [name] => Grace Jones
                    * [id] => b1c124b3-cf60-41a6-8699-92728c8a3fe0
                * )
            * [year] => 1987
            * [date] => 1987-10-01
            * [country] => Europe
            * [length] => 2288
            * [barcode] => 4007192534814
            * [annotation] => Manufactured in Germany by Record Service GmbH, Alsdorf.
            * [disambiguation] => price code CA 835
            * [status] => Official
            * [packaging] => Jewel Case
            * [type] => Album
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
                            * [format] => CD
                            * [tracks] => Array
                                * (
                                    * [0] => Array
                                        * (
                                            * [id] => 5f76ca86-a0ab-4674-acdf-aa8a19042100
                                            * [number] => 1
                                            * [title] => Walking in the Rain
                                            * [artistId] => 66c662b6-6e2f-4930-8610-912e24c63ed1
                                            * [artist] => Grace Jones
                                            * [length] => 258
                                        * )
                                    * [1] => Array
                                        * (
                                            * [id] => 85d1e29e-b8cc-4ead-8337-f376a4e967ed
                                            * [number] => 2
                                            * [title] => Pull Up to the Bumper
                                            * [artistId] => 66c662b6-6e2f-4930-8610-912e24c63e33
                                            * [artist] => Grace Jones
                                            * [length] => 281
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
        * )
     */
    public function fetchData()
    {
        // Data request
        $data = $this->api->doLookup($this->mbID);

        $this->title = isset($data->title) ? $data->title : null;
        $this->artist['name'] = isset($data->{'artist-credit'}[0]->name) ? $data->{'artist-credit'}[0]->name : null;
        $this->artist['id'] = isset($data->{'artist-credit'}[0]->artist->id) ? $data->{'artist-credit'}[0]->artist->id : null;
        $this->barcode = isset($data->barcode) ? $data->barcode : null;
        $this->status = isset($data->status) ? $data->status : null;
        $this->packaging = isset($data->packaging) ? $data->packaging : null;
        $this->year = isset($data->date) ? strtok($data->date, '-') : null;
        $this->date = isset($data->date) ? $data->date : null;
        $this->country = isset($data->{'release-events'}[0]->area->name) ? $data->{'release-events'}[0]->area->name : null;
        $this->type = isset($data->{'release-group'}->{'primary-type'}) ? $data->{'release-group'}->{'primary-type'} : null;
        $this->annotion = isset($data->annotion) ? $data->annotion : null;
        $this->disambiguation = isset($data->disambiguation) ? $data->disambiguation : null;

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
                $cdTracks = array();
                if (isset($medium->tracks) && !empty($medium->tracks)) {
                    foreach ($medium->tracks as $track) {
                        $cdTracks[] = array(
                            'id' => isset($track->id) ? $track->id : null,
                            'number' => isset($track->number) ? $track->number : null,
                            'title' => isset($track->title) ? $track->title : null,
                            'artistId' => isset($track->{'artist-credit'}[0]->artist->id) ? $track->{'artist-credit'}[0]->artist->id : null,
                            'artist' => isset($track->{'artist-credit'}[0]->artist->name) ? $track->{'artist-credit'}[0]->artist->name : null,
                            'length' => isset($track->length) ? round($track->length / 1000) : null
                        );
                        $this->totalLength = $this->totalLength + round($track->length / 1000);
                    }
                }
                $this->media[] = array(
                    'format' => $format,
                    'tracks' => $cdTracks
                );
            }
        }

        // results array
        $results = array(
            'id' => $this->mbID,
            'title' => $this->title,
            'aritst' => $this->artist,
            'year' => $this->year,
            'date' => $this->date,
            'country' => $this->country,
            'length' => $this->totalLength,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'packaging' => $this->packaging,
            'type' => $this->type,
            'genres' => $this->genres,
            'releaseGroupGenres' => $this->releaseGroupGenres,
            'tags' => $this->tags,
            'labels' => $this->labels,
            'media' => $this->media,
            'relations' => $this->relations,
            'annotation' => $this->annotion,
            'disambiguation' => $this->disambiguation
        );
        return $results;
    }

    /**
     * Fetch Cover art from coverartarchive.org
     * @return array
     * Array
     *   (
     *      [front] => Array
     *           (
     *               [id] => 22307139959
     *               [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959.jpg
     *               [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307139959-250.jpg
     *           )
     *       [back] => Array
     *           (
     *               [id] => 22307143843
     *               [originalUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843.jpg
     *               [thumbUrl] => http://coverartarchive.org/release/095e2e2e-60c4-4f9f-a14a-2cc1b468bf66/22307143843-250.jpg
     *           )
     *   )
     */
        public function fetchCoverArt()
    {
        // Data request
        $data = $this->api->doCoverArtLookup($this->mbID);

        $this->coverArt['front'] = array();
        $this->coverArt['back'] = array();
        $small = strval(250);
        if (!empty($data->images) && $data->images != null) {
            foreach ($data->images as $value) {
                if ($value->front == 1) {
                    $this->coverArt['front']['id'] = isset($value->id) ? $value->id : null;
                    $this->coverArt['front']['originalUrl'] = isset($value->image) ? $value->image : null;
                    $this->coverArt['front']['thumbUrl'] = isset($value->thumbnails->$small) ? $value->thumbnails->$small : null;
                    continue;
                }
                if ($value->back == 1) {
                    $this->coverArt['back']['id'] = isset($value->id) ? $value->id : null;
                    $this->coverArt['back']['originalUrl'] = isset($value->image) ? $value->image : null;
                    $this->coverArt['back']['thumbUrl'] = isset($value->thumbnails->$small) ? $value->thumbnails->$small : null;
                    continue;
                }
            }
        }
        return $this->coverArt;
    }

}
