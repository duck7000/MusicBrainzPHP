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
class MdbBase extends Config
{
    public $version = '1.3.5';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var GraphQL
     */
    protected $api;

    /**
     * @var string musicBrainz id
     */
    protected $mbID;

    /**
     * @var string musicBrainz artist id
     */
    protected $arID;

    /**
     * @param Config $config OPTIONAL override default config
     * @param LoggerInterface $logger OPTIONAL override default logger `\Imdb\Logger` with a custom one
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(?Config $config = null, ?LoggerInterface $logger = null, ?CacheInterface $cache = null)
    {
        $this->config = $config ?: $this;
        $this->logger = empty($logger) ? new Logger($this->debug) : $logger;
        $this->cache = empty($cache) ? new Cache($this->config, $this->logger) : $cache;
        $this->api = new Api($this->cache, $this->logger, $this->config);
    }

    /**
     * Retrieve the mbID (musicBrainz id)
     * @return string id mbID currently used
     */
    public function mbid()
    {
        return $this->mbID;
    }

    /**
     * Set mbID
     * @param string id musicBrainz ID
     */
    protected function setid($id)
    {
        $this->mbID = $id;
    }

    /**
     * Set arID (Aritst Id)
     * @param string id musicBrainz Artist ID
     */
    protected function setArtistId($id)
    {
        $this->arID = $id;
    }

    #---------------------------------------------------------[ Debug helpers ]---
    protected function debug_scalar($scalar)
    {
        $this->logger->error($scalar);
    }

    protected function debug_object($object)
    {
        $this->logger->error('{object}', array('object' => $object));
    }

    protected function debug_html($html)
    {
        $this->logger->error(htmlentities($html));
    }
}
