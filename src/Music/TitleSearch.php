<?php

#############################################################################
# musicBrainzPHP                                ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

class TitleSearch extends MdbBase
{

    /**
     * Search for titles matching input search querys
     * @param string $title input cd title
     * @param string $artist input cd artist
     * @param string $barcode input cd barcode
     * @param string $discid input discid from disc
     * if barcode or discid is provided text inputs are ignored
     * artist and/or title can be used together or separate.
     * 
     * @return results[] array of Titles
     *  id: string title id
     *  title: string title
     *  artist: string matching artist for cd title (multiple artists returns Various Artists)
     *  format: string format of the title e.g CD
     *  trackCount: (int)Total tracks on this release
     *  countryCode: string countryCode of release e.g US (or Continent: XE for Europe, XW for WorldWide)
     *  date: string date of release (can be year, month or day) e.g 1988, 1988-10-01, 1988-10
     *  label: array(id: string, name: string) all labels of this title
     *  catalogNumber: string catalognumber found on the back cover for this title
     *  barcode: string barcode found on the back cover for this title
     *  primaryType: string type of this title e.g Album
     *  secondaryType: array() all secondary types of this title e.g Compilation
     *  status: string status of this title e.g original or bootleg
     * 
     */
    public function search($title = '', $artist = '', $barcode = '', $discid = '')
    {
        $results = array();
        // check input parameters
        $urlSuffix = $this->checkInput($title, $artist, $barcode, $discid);
        if (empty($urlSuffix)) {
            return $results;
        }
        $functionName = !empty($discid) ? 'doDiscidSearch' : 'doSearch';
        $data = $this->api->$functionName($urlSuffix);
        if (empty($data) || empty($data->releases)) {
            return $results;
        }

        foreach ($data->releases as $value) {
            $labelCodes = array();
            $labels = array();
            if (!empty($value->{'label-info'})) {
                foreach ($value->{'label-info'} as $labelCode) {
                    //catalognumbers
                    if (!empty($labelCode->{'catalog-number'})) {
                        $labelCodes[] = $labelCode->{'catalog-number'};
                    }
                    //labels
                    $labels[] = array(
                        'id' => isset($labelCode->label->id) ? $labelCode->label->id : null,
                        'name' => isset($labelCode->label->name) ? $labelCode->label->name : null
                    );
                }
            }
            // secondary types
            $secTypes = array();
            if (!empty($value->{'release-group'}->{'secondary-types'})) {
                foreach ($value->{'release-group'}->{'secondary-types'} as $secType) {
                    if (!empty($secType)) {
                        $secTypes[] = $secType;
                    }
                }
            }
            // trackCount
            $trackCount = null;
            if (!empty($value->{'track-count'})) {
                $trackCount = $value->{'track-count'};
            } elseif (!empty($value->media[0]->{'track-count'})) {
                $trackCount = $value->media[0]->{'track-count'};
            }
            $results[] = array(
                'id' => isset($value->id) ?
                              $value->id : null,
                'title' => isset($value->title) ?
                                 $value->title : null,
                'artist' => isset($value->{'artist-credit'}[0]->name) ?
                                  $value->{'artist-credit'}[0]->name : null,
                'format' => isset($value->media[0]->format) ?
                                  $value->media[0]->format : null,
                'trackCount' => $trackCount,
                'countryCode' => isset($value->country) ?
                                       $value->country : null,
                'date' => isset($value->date) ?
                                $value->date : null,
                'label' => $labels,
                'catalogNumber' => $labelCodes,
                'barcode' => isset($value->barcode) ?
                                   $value->barcode : null,
                'primaryType' => isset($value->{'release-group'}->{'primary-type'}) ?
                                       $value->{'release-group'}->{'primary-type'} : null,
                'secondaryType' => $secTypes,
                'status' => isset($value->status) ?
                                  $value->status : null
            );
        }
        return $results;
    }

    /**
     * Check search input parameters
     * @param string $title input cd title
     * @param string $artist input cd artist
     * @param string $barcode input cd barcode
     * @param string $discid input cd barcode
     * 
     * @return string urlSuffix or false
     */
    protected function checkInput($title, $artist, $barcode, $discid)
    {
        $title = trim($title);
        $artist = trim($artist);
        $barcode = trim($barcode);
        $discid = trim($discid);
        if (!empty($discid)) {
            return $discid;
        } elseif (!empty($barcode) && $this->isValidBarcode($barcode) == true) {
            return '?query=barcode:' . $barcode;
        } elseif (!empty($title) && empty($artist)) {
            return '?query=release:' . rawurlencode($title);
        } elseif (empty($title) && !empty($artist)) {
            return '?query=artist:' . rawurlencode($artist);
        } elseif (!empty($title) && !empty($artist)) {
            return '?query=release:' . rawurlencode($title) . '%20AND%20artist:' . rawurlencode($artist);
        } else {
            return false;
        }
    }


    /**
     * Check if $barcode is valid in terms of digits only and specific length
     * @param string $barcode
     * 
     * @return boolean, true if valid
     */
    protected function isValidBarcode($barcode)
    {
        //checks validity of: GTIN-8, GTIN-12, GTIN-13, GTIN-14, GSIN, SSCC
        $barcode = (string) $barcode;
        //we accept only digits
        if (!preg_match("/^[0-9]+$/", $barcode)) {
            return false;
        }
        //check valid lengths:
        $l = strlen($barcode);
        if(!in_array($l, [8,12,13,14,17,18])) {
            return false;
        } else {
            return true;
        }
    }

}
