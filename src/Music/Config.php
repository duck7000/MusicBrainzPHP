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
 * Configuration class for musicBrainzPHP
 * @author ed (github user: duck7000)
 */
class Config
{
    /**
     * Default base url for musicBrainz api
     * @var string
     */
    public $baseUrl = 'http://musicbrainz.org/ws/2/release/';
    
    /**
     * Default base url for coverart api
     * @var string
     */
    public $baseCoverArtUrl = 'http://ia600903.us.archive.org/6/items/mbid-';
    
    /**
     * Default userAgent to use in request, for musicBrainz must be something that identifys the user and program
     * @var string
     */
    public $userAgent = 'programName V1.0 (www.example.com)';
    
    /**
     * Title search results
     * Possible range = 1 - 100 (1 and 100 included)
     * @var int
     */
    public $titleSearchAmount = 25;
    
    /**
     * Title search format
     * Default: cd (insensitive to case, spaces, and separators)
     * possible types:
     *      CD
     *      Vinyl
     *      7" Vinyl
     *      10" Vinyl
     *      12" Vinyl
     *      Cassette
     *      SACD
     *      DVD
     *      Other
     *      Blu-ray
     *      miniDisc
     *      12" Vinyl
     * @var string
     */
    public $titleSearchFormat = "cd";

    /**
     * Debug mode true or false
     * @var boolean
     */
    public $debug = false;

}
