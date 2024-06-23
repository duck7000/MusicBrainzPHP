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

    #========================================================[ Cache options]===
    /**
     * Directory to store cached pages. This must be writable by the web
     * server. It doesn't need to be under documentroot.
     * @var string
     */
    public $cacheDir = './cache/';

    /**
     * Use cached pages if available?
     * @var boolean
     */
    public $cacheUse = false;

    /**
     * Store the pages retrieved for later use?
     * @var boolean
     */
    public $cacheStore = false;

    /**
     * Use zip compression for caching the retrieved html-files?
     * @see $converttozip if you're changing from false to true
     * @var boolean
     */
    public $cacheUseZip = true;

    /**
     * Convert non-zip cache-files to zip
     * You might want to use this if you weren't gzipping your cache files, but now are. They will be rewritten when they're used
     * @var boolean
     */
    public $cacheConvertZip = false;

    /**
     * Cache expiration time - cached pages older than this value (in seconds) will
     * be automatically deleted.
     * If 0 cached pages will never expire
     * @var integer
     */
    public $cacheExpire = 604800;


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
     * @var string
     */
    public $titleSearchFormat = "cd";

    // Debug config
    /**
     * Debug mode true or false
     * @var boolean
     */
    public $debug = false;

}
