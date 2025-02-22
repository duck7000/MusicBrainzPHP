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
 * Get wiki data from wikipedia from MusicBrainz release
 * If there is no wikidata link form MusicBrainz wikipedia itself is searched
 * It includes wikipedia sections text including text in inline blockquotes
 * Excluding small blockquotes in frames
 * Excluded sections:
 *      Track_listing
 *      Charts
 *      Certifications
 *      Notes
 *      References
 *      Bibliography
 *      See_also
 *      Release_history
 *      External_links
 * @author ed (github user: duck7000)
 */
class Wiki extends MdbBase
{

    protected $wikipediaData = array();
    protected $wikipediaApiUrl = 'https://en.wikipedia.org/w/api.php';
    protected $wikidataApiUrl = 'https://www.wikidata.org/w/api.php';
    protected $formatJson = '&format=json';

    /**
     * @param Config $config OPTIONAL override default config
     * @param LoggerInterface $logger OPTIONAL override default logger `\Imdb\Logger` with a custom one
     * @param CacheInterface $cache OPTIONAL override the default cache with any PSR-16 cache.
     */
    public function __construct(Config $config = null, LoggerInterface $logger = null, CacheInterface $cache = null)
    {
        parent::__construct($config, $logger, $cache);
    }

    /**
     * Check if wikipedia data can be used
     * @param array $releaseGroupUrls input urls
     * @param string $inputTitle release title
     * @param string $inputArtist release artist
     * @param string $reId release id
     * @return array wikipedia text or false
     */
    public function checkWikipedia($releaseGroupUrls, $inputTitle, $inputArtist, $reId)
    {
        foreach ($releaseGroupUrls as $url) {
            if (!empty($url['url']) && $url['type'] == "wikidata") {
                $returnValue = $this->getWikipediaId($url['url'], $reId);
                if (!empty($returnValue)) {
                    // get wikipedia text
                    return $returnValue;
                }
            }
        }
        // no wikidata from musicbrainz, search wikipedia itself
        //search for a matching wikipedia id
        if (($wikipediaData = $this->searchWikipediaApi($inputTitle, $inputArtist, $reId))) {
            return $wikipediaData;
        }
        return false;
    }

    /**
     * Search wikipedia api for this release title, artist
     * @param string $title release title
     * @param string $aritst release artist
     * @param string $reId release id (needed for cache)
     * @return array or false
     */
    protected function searchWikipediaApi($title, $artist, $reId)
    {
        if (!empty($title) && !empty($artist)) {
            $wikipediaUrl = $this->wikipediaApiUrl .
                            '?action=query' .
                            '&list=search' .
                            '&srsearch=' .
                                rawurlencode($title) . '%20' .
                                rawurlencode($artist) .
                            $this->formatJson;
            $data = $this->api->execRequest($wikipediaUrl);
            if (!empty($data->query->search)) {
                foreach ($data->query->search as $result) {
                    if (!empty($result->title)) {
                        if (stripos($result->title, $title) !== false) {
                            $checkedTitle = str_replace(" ", "_", $result->title);
                            if (($returnValue = $this->getWikipediaText($checkedTitle, $reId))) {
                                return $returnValue;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get wikipedia url from wikidata url
     * @param string $wikidataUrl input wikidata url
     * @return array() or false
     */
    protected function getWikipediaId($wikidataUrl, $reId)
    {
        $posWikidataUrl = strripos(rtrim($wikidataUrl, "/"), "/");
        if (empty($posWikidataUrl)) {
            return false;
        }
        $qid = substr($wikidataUrl, $posWikidataUrl + 1);
        $wikidataUrl = $this->wikidataApiUrl .
                       '?action=wbgetentities' .
                       '&props=sitelinks/urls' .
                       '&ids=' . $qid .
                       '&sitefilter=enwiki' .
                       $this->formatJson;
        $data = $this->api->execRequest($wikidataUrl);
        if (!empty($data) &&
            $data->success == 1 &&
            !empty($data->entities->$qid->sitelinks->enwiki->url)
           )
        {
            if (($wikipediaUrl = $data->entities->$qid->sitelinks->enwiki->url)) {
                if (($parseUrlParts = parse_url(rtrim($wikipediaUrl, "/")))) {
                    $urlParts = explode('/', ltrim($parseUrlParts['path'], "/"), 2);
                    if (isset($urlParts[1]) && !empty($urlParts[1])) {
                        $wikipediaId = $urlParts[1];
                        if (($returnValue = $this->getWikipediaText($wikipediaId, $reId))) {
                            return $returnValue;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get wikipedia html from wikipedia page
     * @param string $wikipediaId input wikipedia id in url form
     * @return array() or false
     */
    protected function getWikipediaText($wikipediaId, $reId)
    {
        $wikipediaUrl = $this->wikipediaApiUrl .
                        '?action=parse' .
                        '&page=' . $wikipediaId .
                        '&prop=text' .
                        '&redirects=1' .
                        $this->formatJson;
        $data = $this->api->checkCache($reId, $wikipediaUrl, "title", "_WikiText");
        if (!empty($data) && ($textData = $data->parse->text->{'*'})) {
            if (($result = $this->procesWikipediaText($textData))) {
                return $result;
            }
        }
        return false;
    }

    /**
     * proces wikipedia html, clean and add to array
     * @param string $textData input wikipedia html
     * @return array() or false
     */
    protected function procesWikipediaText($textData)
    {
        $dom = new \DOMDocument();
        //Add, if available, summary first to $this->wikipediaData
        if (stripos($textData, "<h2") !== false) {
            $posText = stripos($textData, "<h2");
            $textSummary = substr($textData, 0, $posText - 3);
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $textSummary);
            if (($pElements = $dom->getElementsByTagName('p'))) {
                foreach($pElements as $node) {
                    if (stripos($node->getAttribute('class'), "mw-empty-elt") !== false) {
                        continue;
                    }
                    $this->wikipediaData['summary'][] = $this->cleanHtml($dom->saveHTML($node));
                }
            }
        }
        //Add, if available, all wanted sections to $this->wikipediaData
        @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $textData);
        if (($divElements = $dom->getElementsByTagName('div'))) {
            foreach($divElements as $node) {
                if (($element = $node->getAttribute('class')) !== false &&
                    stripos($element, "mw-heading mw-heading2") === false
                   )
                {
                    continue;
                }
                if (($elementId = $node->getElementsByTagName('h2')->item(0)->getAttribute('id')) === false ||
                    stripos($elementId, "Track_listing") !== false ||
                    stripos($elementId, "Charts") !== false ||
                    stripos($elementId, "Certifications") !== false ||
                    stripos($elementId, "Notes") !== false ||
                    stripos($elementId, "References") !== false ||
                    stripos($elementId, "Bibliography") !== false ||
                    stripos($elementId, "See_also") !== false ||
                    stripos($elementId, "Release_history") !== false ||
                    stripos($elementId, "Awards") !== false ||
                    stripos($elementId, "External_links") !== false
                   )
                {
                    continue;
                }
                // in case anyone does want camelCase instead of snake_case as array index
                //$arrayIndex = lcfirst(str_replace('_', '', ucwords($elementId, '_')));
                $text = array();
                while(($node = $node->nextSibling)) {
                    if ($node->nodeName === 'div') {
                        if (($elementSibling = $node->getAttribute('class'))) {
                            if (stripos($elementSibling, "mw-heading mw-heading2") !== false) {
                                break;
                            }
                        }
                    }
                    // personel
                    if (stripos($elementId, "Personnel") !== false ||
                        stripos($elementId, "Credits") !== false
                       )
                    {
                        $elementId = 'Personnel';
                        if (!empty(trim(strip_tags($node->nodeValue))) &&
                            ($node->nodeName === 'ul' || $node->nodeName === 'div')
                           )
                        {
                            if (stripos($node->getAttribute('class'), "mw-heading mw-heading3") === false) {
                                if (!empty($node->getElementsByTagName('li'))) {
                                    foreach($node->getElementsByTagName('li') as $listItem) {
                                        if (!empty(trim(strip_tags($listItem->nodeValue)))) {
                                            if (stripos($listItem->nodeValue, "\n") !== false) {
                                                $parts = explode("\n", $listItem->nodeValue, 2);
                                                $value = $parts[0];
                                            } else {
                                                $value = $listItem->nodeValue;
                                            }
                                            $text[] = $this->cleanHtml($value);
                                        }
                                    }
                                    $this->wikipediaData[$elementId] = $text;
                                }
                            }
                        }
                    } else {
                        // All other sections
                        if (!empty(trim(strip_tags($node->nodeValue))) &&
                            ($node->nodeName === 'p' || $node->nodeName === 'blockquote')
                           )
                        {
                            $text[] = $this->cleanHtml($dom->saveHTML($node));
                            $this->wikipediaData[$elementId] = $text;
                        }
                    }
                }
            }
        }
        return $this->wikipediaData;
    }

    /**
     * Clean up html
     * @param string $html input html
     * @return string
     */
    protected function cleanHtml($html)
    {
        $html = preg_replace('/<\s*style.+?<\s*\/\s*style.*?>/si', '', $html);
        $cleanTags =  trim(strip_tags($html));
        $pattern = array('/(\[\D+\])/', '/(\[\d+\])/');
        $cleanHtml = preg_replace($pattern, '', $cleanTags);
        return $cleanHtml;
    }

}
