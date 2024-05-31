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
    protected $country = null;
    protected $length = null;
    protected $barcode = null;
    protected $status = null;
    protected $packaging = null;
    protected $type = null;
    protected $genres = array();
    protected $labels = array();
    protected $media = array();
    protected $totalLength = 0;
    protected $extUrls = array();
    protected $coverArt = array();

    /**
     * @param string $id musicBrainz id
     * @param Config $config OPTIONAL override default config
     */
    public function __construct($id, Config $config = null)
    {
        parent::__construct($config);
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
            * 
            * [year] => 1987
            * [country] => Europe
            * [length] => 2288
            * [barcode] => 4007192534814
            * [status] => Official
            * [packaging] => Jewel Case
            * [type] => Album
            * [genres] => Array
                * (
                    * [0] => art pop
                    * [1] => dub
                * )
                * 
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
                * 
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
                                            * [artist] => Grace Jones
                                            * [length] => 258
                                        * )
                                        * 
                                    * [1] => Array
                                        * (
                                            * [id] => 85d1e29e-b8cc-4ead-8337-f376a4e967ed
                                            * [number] => 2
                                            * [title] => Pull Up to the Bumper
                                            * [artist] => Grace Jones
                                            * [length] => 281
                                        * )
                                        * 
                                * )
                                * 
                        * )
                        * 
                * )
                * 
            * [extUrls] => Array
                * (
                    * [0] => Array
                        * (
                            * [name] => discogs
                            * [url] => https://www.discogs.com/release/3999066
                        * )
                        * 
                * )
                * 
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
        $this->country = isset($data->{'release-events'}[0]->area->name) ? $data->{'release-events'}[0]->area->name : null;
        $this->type = isset($data->{'release-group'}->{'primary-type'}) ? $data->{'release-group'}->{'primary-type'} : null;

        // Genres
        if (isset($data->genres) && !empty($data->genres)) {
            foreach ($data->genres as $genre) {
                $this->genres[] = isset($genre->name) ? $genre->name : null;
            }
        }
        
        // External Urls
        if (isset($data->relations) && !empty($data->relations)) {
            foreach ($data->relations as $value) {
                $relation = array(
                    'name' => isset($value->type) ? $value->type : null,
                    'url' => isset($value->url->resource) ? $value->url->resource : null
                );
                $this->extUrls[] = $relation;
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
            'country' => $this->country,
            'length' => $this->totalLength,
            'barcode' => $this->barcode,
            'status' => $this->status,
            'packaging' => $this->packaging,
            'type' => $this->type,
            'genres' => $this->genres,
            'labels' => $this->labels,
            'media' => $this->media,
            'extUrls' => $this->extUrls
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
        if (!empty($data->images) && $data->images != null) {
            foreach ($data->images as $value) {
                if ($value->front == 1) {
                    $this->coverArt['front']['id'] = isset($value->id) ? $value->id : null;
                    $this->coverArt['front']['originalUrl'] = isset($value->image) ? $value->image : null;
                    $this->coverArt['front']['thumbUrl'] = isset($value->thumbnails->small) ? $value->thumbnails->small : null;
                    continue;
                }
                if ($value->back == 1) {
                    $this->coverArt['back']['id'] = isset($value->id) ? $value->id : null;
                    $this->coverArt['back']['originalUrl'] = isset($value->image) ? $value->image : null;
                    $this->coverArt['back']['thumbUrl'] = isset($value->thumbnails->small) ? $value->thumbnails->small : null;
                    continue;
                }
            }
        }
        return $this->coverArt;
    }

}
