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
class MdbBase extends Config
{
    public $version = '1.0.0';

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
     * @param Config $config OPTIONAL override default config
     * @param LoggerInterface $logger OPTIONAL override default logger `\Imdb\Logger` with a custom one
     */
    public function __construct(Config $config = null, LoggerInterface $logger = null)
    {
        $this->config = $config ?: $this;
        $this->logger = empty($logger) ? new Logger($this->debug) : $logger;
        $this->api = new Api($this->logger, $this->config);
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
