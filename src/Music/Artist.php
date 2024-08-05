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
 * Artist info on musicBrainz API
 * @author ed (github user: duck7000)
 */
class Artist extends MdbBase
{

    // Artist Bio
    protected $bioName = null;
    protected $bioId = null;
    protected $bioType = null;
    protected $bioGender = null;
    protected $bioArea = array();
    protected $bioAreaBegin = array();
    protected $bioAreaEnd = array();
    protected $bioDisambiguation = null;
    protected $bioLifeSpan = array();
    protected $bioAliases = array();

    /**
     * @param string $id musicBrainz id
     * @param Config $config OPTIONAL override default config
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct($id, Config $config = null, LoggerInterface $logger = null, CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
        $this->setArtistId($id);
    }

    /**
     * Fetch all info from artist (Bio)
     * @param string $artistId
     * @return array
     * Array
        * (
            * [name] => AC/DC
            * [id] => 66c662b6-6e2f-4930-8610-912e24c63ed1
            * [type] => Group
            * [gender] => 
            * [country] => Australia
            * [disambiguation] => Australian hard rock band
            * [lifeSpan] => Array
                * (
                    * [begin] => 1973-11
                    * [end] => 
                    * [ended] => (boolean, true if artist/group is ended/died)
                * )
            * [area] => Array
                * (
                    * [id] => f61848ab-0ba0-4534-8b71-d9c7c03e854c
                    * [name] => Salford
                * )
            * [areaBegin] => Array
                * (
                    * [id] => f61848ab-0ba0-4534-8b71-d9c7c03e854c
                    * [name] => Salford
                * )
            * [areaEnd] => Array
                * (
                * )
            * [aliases] => array
                * (
                    * [name] => Manson Marilyn
                    * [type] => Search hint
                    * [begin] => 
                    * [end] => 
                    * [locale] => 
                * )
        * )
     */
    public function fetchArtistBio()
    {
        // Data request
        $Artistdata = $this->api->doArtistBioLookup($this->arID);

        $this->bioName = isset($Artistdata->name) ? $Artistdata->name : null;
        $this->bioId = isset($Artistdata->id) ? $Artistdata->id : null;
        $this->bioType = isset($Artistdata->type) ? $Artistdata->type : null;
        $this->bioGender = isset($Artistdata->gender) ? $Artistdata->gender : null;
        $this->bioDisambiguation = isset($Artistdata->disambiguation) ? $Artistdata->disambiguation : null;

        // Life span
        if (isset($Artistdata->{'life-span'}) && !empty($Artistdata->{'life-span'})) {
            $this->bioLifeSpan = array(
                'begin' => isset($Artistdata->{'life-span'}->begin) ? $Artistdata->{'life-span'}->begin : null,
                'end' => isset($Artistdata->{'life-span'}->end) ? $Artistdata->{'life-span'}->end : null,
                'ended' => isset($Artistdata->{'life-span'}->ended) ? $Artistdata->{'life-span'}->ended : false
            );
        }

        // Aliases
        if (isset($Artistdata->aliases) && !empty($Artistdata->aliases)) {
            foreach ($Artistdata->aliases as $alias) {
                $this->bioAliases[] = array(
                    'name' => isset($alias->name) ? $alias->name : null,
                    'type' => isset($alias->type) ? $alias->type : null,
                    'begin' => isset($alias->begin) ? $alias->begin : null,
                    'end' => isset($alias->end) ? $alias->end : null,
                    'locale' => isset($alias->locale) ? $alias->locale : null
                );
            }
        }

        // Area
        if (isset($Artistdata->area->name) && !empty($Artistdata->area->name)) {
            $this->bioArea = array(
                'id' => $Artistdata->area->id,
                'name' => $Artistdata->area->name
            );
        }

        // Begin area
        if (isset($Artistdata->{'begin-area'}) && !empty($Artistdata->{'begin-area'})) {
            $this->bioAreaBegin = array(
                'id' => isset($Artistdata->{'begin-area'}->id) ? $Artistdata->{'begin-area'}->id : null,
                'name' => isset($Artistdata->{'begin-area'}->name) ? $Artistdata->{'begin-area'}->name : null
            );
        }

        // End area
        if (isset($Artistdata->{'end-area'}) && !empty($Artistdata->{'end-area'})) {
            $this->bioAreaEnd = array(
                'id' => isset($Artistdata->{'end-area'}->id) ? $Artistdata->{'end-area'}->id : null,
                'name' => isset($Artistdata->{'end-area'}->name) ? $Artistdata->{'end-area'}->name : null
            );
        }

        // results array
        $results = array(
            'name' => $this->bioName,
            'id' => $this->bioId,
            'type' => $this->bioType,
            'gender' => $this->bioGender,
            'disambiguation' => $this->bioDisambiguation,
            'lifeSpan' => $this->bioLifeSpan,
            'area' => $this->bioArea,
            'areaBegin' => $this->bioAreaBegin,
            'areaEnd' => $this->bioAreaEnd,
            'aliases' => $this->bioAliases
        );
        return $results;
    }

}
