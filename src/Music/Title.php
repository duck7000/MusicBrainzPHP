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
 * A title on musicBrainz API
 * @author ed (github user: duck7000)
 */
class Title extends MdbBase
{

    protected $art = null;
    protected $wiki = null;
    protected $lyric = null;
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
    protected $releaseGroupUrls = array();
    protected $tags = array();
    protected $labels = array();
    protected $media = array();
    protected $relations = array();
    protected $coverArt = array();
    protected $releaseGroupcoverArt = array();
    protected $annotation = null;
    protected $disambiguation = null;
    protected $wikipedia = array();

    /**
     * @param string $id musicBrainz id
     * @param Config $config OPTIONAL override default config
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(string $id, ?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
        $this->setid($id);
        $this->art = new Cover();
        $this->wiki = new Wiki();
        $this->lyric = new Lyric();
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
                    * [alias] => Array
                    *   (
                        *   [0] => AC DC
                        *   [1] => AC-DC
                        *   [2] => AC.DC
                        *   [3] => AC/DC
                        *   [4] => AC?DC
                        *   [5] => ACDC
                        *   [6] => AC\DC
                        *   [7] => AC|DC
                        *   [8] => AC⚡DC
                        *   [9] => AC⚡︎DC
                        *   [10] => Acca Dacca
                        *   [11] => Akka Dakka
                    *   )
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
            * [releaseGroupUrls] => Array
                * (
                    * [0] => array
                        * (
                            * [type] =>
                            * [url] =>
                        * )
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
                                            * [lyric] => (string) (Linebreaks are included)
                                        * )
                                * )
                            * [discids] => Array
                                * (
                                    * [0] => 8VcyV_HgVNgXje7IgY6NndMP8Tk-
                                    * [1] => GI8Gdv0JZhh9yZyvQMvrauUbz.I-
                                    * [2] => IC7ydeqKPjdd8u7ptRItLGG0wZ0-
                                * )
                        * )
                 * )
                        * )
                * )
            * [relations] => Array
                * (
                    * [credits] => Array
                        * (
                            * [instrument] => Array
                                * (
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
                * )
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
     *      [wikipedia] => Array()
     *          [summary] => Array()
     *              [0] => (string)
     *          [background] => Array()
     *              [0] => (string)
     *          [reception] => Array()
     *              [0] => (string)
     *          [personnel] => Array()
     *              [0] => (string)
        * )
     */
    public function fetchData()
    {
        // Data request
        $data = $this->api->doLookup($this->mbID);
        if (empty($data)) {
            return false;
        }

        $this->title = isset($data->title) ?
                             $data->title : null;
        $this->barcode = isset($data->barcode) ?
                               $data->barcode : null;
        $this->status = isset($data->status) ?
                              $data->status : null;
        $this->packaging = isset($data->packaging) ?
                                 $data->packaging : null;
        $this->year = isset($data->date) ?
                            strtok($data->date, '-') : null;
        $this->date = isset($data->date) ?
                            $data->date : null;
        $this->firstReleaseDate = isset($data->{'release-group'}->{'first-release-date'}) ?
                                        $data->{'release-group'}->{'first-release-date'} : null;
        $this->country = isset($data->{'release-events'}[0]->area->name) ?
                               $data->{'release-events'}[0]->area->name : null;
        $this->primaryType = isset($data->{'release-group'}->{'primary-type'}) ?
                                   $data->{'release-group'}->{'primary-type'} : null;
        $this->releaseGroupId = isset($data->{'release-group'}->id) ?
                                      $data->{'release-group'}->id : null;
        $this->annotation = isset($data->annotation) ?
                                  $data->annotation : null;
        $this->disambiguation = isset($data->disambiguation) ?
                                      $data->disambiguation : null;
        // Secondary Types
        if (!empty($data->{'release-group'}->{'secondary-types'})) {
            foreach ($data->{'release-group'}->{'secondary-types'} as $secType) {
                if (!empty($secType)) {
                    $this->secondaryTypes[] = $secType;
                }
            }
        }
        // Artist
        if (!empty($data->{'artist-credit'})) {
            foreach ($data->{'artist-credit'} as $credit) {
                $aliases = array();
                if (!empty($credit->artist->aliases)) {
                    foreach ($credit->artist->aliases as $alias) {
                        if (!empty($alias->name)) {
                            $aliases[] = $alias->name;
                        }
                    }
                }
                $this->artist[] = array(
                    'name' => isset($credit->artist->name) ?
                                    $credit->artist->name : null,
                    'id' => isset($credit->artist->id) ?
                                  $credit->artist->id : null,
                    'alias' => $aliases
                );
            }
        }
        // Genres
        if (!empty($data->genres)) {
            foreach ($data->genres as $genre) {
                if (!empty($genre->name)) {
                    $this->genres[] = $genre->name;
                }
            }
        }
        // Release-group Genres
        if (!empty($data->{'release-group'}->genres)) {
            foreach ($data->{'release-group'}->genres as $relGenre) {
                if (!empty($relGenre->name)) {
                    $this->releaseGroupGenres[] = $relGenre->name;
                }
            }
        }
        // Release-group relations external urls
        if (!empty($data->{'release-group'}->relations)) {
            foreach ($data->{'release-group'}->relations as $relUrl) {
                if (!empty($relUrl->url->resource)) {
                    $this->releaseGroupUrls[] = array(
                        'type' => isset($relUrl->type) ?
                                        $relUrl->type : null,
                        'url' => isset($relUrl->url->resource) ?
                                       $relUrl->url->resource : null
                    );
                }
            }
        }
        // Tags
        if (!empty($data->tags)) {
            foreach ($data->tags as $tag) {
                if (!empty($tag->name)) {
                    $this->tags[] = $tag->name;
                }
            }
        }
        // Relations
        if (!empty($data->relations)) {
            foreach ($data->relations as $relation) {
                if (!empty($relation->{'target-type'})) {
                    if ($relation->{'target-type'} == "artist") {
                        // attributes
                        $attributes = array();
                        if (!empty($relation->attributes)) {
                            foreach ($relation->attributes as $attribute) {
                                if (!empty($attribute)) {
                                    $attributes[] = $attribute;
                                }
                            }
                        }
                        // Artist
                        $artist = array();
                        if (!empty($relation->artist)) {
                            $artist = array(
                                'name' => isset($relation->artist->name) ?
                                                $relation->artist->name : null,
                                'id' => isset($relation->artist->id) ?
                                              $relation->artist->id : null,
                                'disambiguation' => isset($relation->artist->disambiguation) ?
                                                          $relation->artist->disambiguation : null
                            );
                        }
                        $this->relations['credits']["$relation->type"][] = array(
                            'begin' => isset($relation->begin) ?
                                             $relation->begin : null,
                            'end' => isset($relation->end) ?
                                           $relation->end : null,
                            'artist' => $artist,
                            'attributes' => $attributes
                        );
                    }
                }
            }
        }
        // Labels
        if (!empty($data->{'label-info'})) {
            foreach ($data->{'label-info'} as $label) {
                $label = array(
                    'name' => isset($label->label->name) ?
                                    $label->label->name : null,
                    'id' => isset($label->label->id) ?
                                  $label->label->id : null,
                    'type' => isset($label->label->type) ?
                                    $label->label->type : null,
                    'code' => isset($label->label->{'label-code'}) ?
                                    $label->label->{'label-code'} : null,
                    'catalog' => isset($label->{'catalog-number'}) ?
                                       $label->{'catalog-number'} : null
                );
                $this->labels[] = $label;
            }
        }
        // Media
        if (!empty($data->media)) {
            foreach ($data->media as $medium) {
                // Tracks
                $cdTracks = array();
                $tracktotal = 0;
                if (!empty($medium->tracks)) {
                    foreach ($medium->tracks as $track) {
                        // Artist
                        $artistTrackCredit = array();
                        if (!empty($track->{'artist-credit'})) {
                            foreach ($track->{'artist-credit'} as $trackCredit) {
                                $artistTrackCredit[] = array(
                                    'name' => isset($trackCredit->artist->name) ?
                                                    $trackCredit->artist->name : null,
                                    'id' => isset($trackCredit->artist->id) ?
                                                  $trackCredit->artist->id : null,
                                    'joinphrase' => isset($trackCredit->joinphrase) ?
                                                          $trackCredit->joinphrase : null
                                );
                            }
                        }
                        $trackLength = isset($track->length) ?
                                             round($track->length / 1000) : null;
                        $trackId = isset($track->id) ?
                                         $track->id : null;
                        $trackTitle = isset($track->title) ?
                                            $track->title : null;
                        $trackNumber = isset($track->number) ?
                                             $track->number : null;
                        // check and add lyrics for this track
                        $lyric = null;
                        if ($this->config->addLyricsData == true) {
                            $lyricsData = $this->lyric->getLrclibData($this->title,
                                                                      $artistTrackCredit[0]['name'],
                                                                      $trackTitle, $trackId,
                                                                      $trackLength);
                            if (!empty($lyricsData)) {
                                $lyric = $lyricsData;
                            }
                        }
                        $cdTracks[] = array(
                            'id' => $trackId,
                            'number' => $trackNumber,
                            'title' => $trackTitle,
                            'artist' => $artistTrackCredit,
                            'length' => $trackLength,
                            'lyric' => $lyric
                        );
                        $tracktotal = $tracktotal + ($track->length / 1000);
                    }
                }
                // Discids
                $discids = array();
                if (!empty($medium->discs)) {
                    foreach ($medium->discs as $disc) {
                        if (!empty($disc->id)) {
                            $discids[] = $disc->id;
                        }
                    }
                }
                $this->media[] = array(
                    'mediumTitle' => isset($medium->title) ?
                                           $medium->title : null,
                    'format' => isset($medium->format) ?
                                      $medium->format : null,
                    'tracks' => $cdTracks,
                    'totalPlayTime' => round($tracktotal),
                    'discids' => $discids
                );
            }
        }
        // CoverArt
        if ($this->config->addCoverImages == true) {
            if (!empty($data->{'cover-art-archive'}->count)) {
                $resultTitle = $this->art->fetchCoverArt($this->mbID, false);
                if (!empty($resultTitle)) {
                    $this->coverArt = $resultTitle;
                }
            }
            // Release Group Cover Art
            if (!empty($this->releaseGroupId)) {
                $resultReleaseGroup = $this->art->fetchCoverArt($this->releaseGroupId, true);
                if (!empty($resultReleaseGroup)) {
                    $this->releaseGroupcoverArt = $resultReleaseGroup;
                }
            }
        }
        // check and add wikipedia data for this release
        if ($this->config->addWikipedia == true) {
            $wiki = $this->wiki->checkWikipedia($this->releaseGroupUrls,
                                                $this->title,
                                                $this->artist[0]['name'],
                                                $this->mbID);
            if (!empty($wiki)) {
                $this->wikipedia = $wiki;
            }
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
            'barcode' => $this->barcode,
            'status' => $this->status,
            'packaging' => $this->packaging,
            'primaryType' => $this->primaryType,
            'secondaryTypes' => $this->secondaryTypes,
            'releaseGroupId' => $this->releaseGroupId,
            'genres' => $this->genres,
            'releaseGroupGenres' => $this->releaseGroupGenres,
            'releaseGroupUrls' => $this->releaseGroupUrls,
            'tags' => $this->tags,
            'labels' => $this->labels,
            'media' => $this->media,
            'relations' => $this->relations,
            'annotation' => $this->annotation,
            'disambiguation' => $this->disambiguation,
            'coverArt' => $this->coverArt,
            'releaseGroupcoverArt' => $this->releaseGroupcoverArt,
            'wikipedia' => $this->wikipedia
        );
        return $results;
    }

}
