<?php
#############################################################################
# imdbphp6                                                (c) Ed (duck7000) #
# written by Ed                                                             #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

use Psr\SimpleCache\CacheInterface;

/**
 * File caching
 * Caches files to disk in cacheDir optionally gzipping if cacheUseZip
 *
 */
class Cache implements CacheInterface
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * Cache constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        if (($this->config->cacheUse|| $this->config->cacheStore) && !is_dir($this->config->cacheDir)) {
            @mkdir($this->config->cacheDir, 0700, true);
            if (!is_dir($this->config->cacheDir)) {
                echo 'Configured cache directory does not exist!';
                return null;
            }
        }
        if ($this->config->cacheStore && !is_writable($this->config->cacheDir)) {
            echo 'Configured cache directory lacks write permission!';
            return null;
        }

        // @TODO add a limit on how frequently a purge can occur
        $this->purge();
    }

    /**
     * @inheritdoc
     */
    public function get($key, $default = null)
    {
        if (!$this->config->cacheUse) {
            return $default;
        }

        $cleanKey = $this->sanitiseKey($key);
        $fname = $this->config->cacheDir . '/' . $cleanKey;
        if (!file_exists($fname)) {
            return $default;
        }

        if ($this->config->cacheUseZip) {
            $content = file_get_contents('compress.zlib://' . $fname); // This can read uncompressed files too
            if (!$content) {
                return $default;
            }
            if ($this->config->cacheConvertZip) {
                @$fp = fopen($fname, "r");
                $zipchk = fread($fp, 2);
                fclose($fp);
                if (!($zipchk[0] == chr(31) && $zipchk[1] == chr(139))) { //checking for zip header
                    /* converting on access */
                    file_put_contents('compress.zlib://' . $fname, $content);
                }
            }
            return $content;
        } else { // no zip
            return file_get_contents($fname);
        }
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value, $ttl = null)
    {
        if (!$this->config->cacheStore) {
            return false;
        }

        $cleanKey = $this->sanitiseKey($key);
        $fname = $this->config->cacheDir . '/' . $cleanKey;
        if ($this->config->cacheUseZip) {
            $fp = gzopen($fname, "w");
            gzputs($fp, $value);
            gzclose($fp);
        } else { // no zip
            file_put_contents($fname, $value);
        }

        return true;
    }

    /**
     * This method looks for files older than the cache_expire set in the
     * \Imdb\Config and removes them
     *
     */
    public function purge()
    {
        if (!$this->config->cacheStore || $this->config->cacheExpire == 0) {
            return;
        }

        $cacheDir = $this->config->cacheDir;

        $thisdir = dir($cacheDir);
        $now = time();
        while ($file = $thisdir->read()) {
            if ($file != "." && $file != ".." && $file != ".placeholder") {
                $fname = $cacheDir . '/' . $file;
                if (is_dir($fname)) {
                    continue;
                }
                $mod = filemtime($fname);
                if ($mod && ($now - $mod > $this->config->cacheExpire)) {
                    unlink($fname);
                }
            }
        }
        $thisdir->close();
    }

    /**
     * Replace characters the OS won't like using with the filesystem
     */
    protected function sanitiseKey($key)
    {
        return str_replace(array('/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'), '.', $key);
    }

    // Some empty functions so we match the interface. These will never be used
    public function getMultiple($keys, $default = null)
    {
        return [];
    }

    public function clear()
    {
        return false;
    }

    public function delete($key)
    {
        return false;
    }

    public function deleteMultiple($keys)
    {
        return false;
    }

    public function has($key)
    {
        return false;
    }

    public function setMultiple($values, $ttl = null)
    {
        return false;
    }
}
