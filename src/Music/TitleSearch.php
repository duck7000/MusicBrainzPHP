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
     * if barcode is provided text inputs are ignored
     * artist and/or title can be used together or separate.
     * 
     * @return results[] array of Titles
     *  id: string title id
     *  title: string title
     *  artist: string matching artist for cd title (multiple artists returns Various Artists)
     *  format: string format of the title e.g CD
     *  countryCode: string countryCode of release e.g US (or Continent: XE for Europe, XW for WorldWide)
     *  date: string date of release (can be year, month or day) e.g 1988, 1988-10-01, 1988-10
     *  label: string first label of this title
     *  catalogNumber: string catalognumber found on the back cover for this title
     *  barcode: string barcode found on the back cover for this title
     *  type: string type of this title e.g Album
     *  status: string status of this title e.g original or bootleg
     * 
     */
    public function search($title = '', $artist = '', $barcode = '')
    {
        $results = array();

        // check input parameters
        $urlSuffix = $this->checkInput($title, $artist, $barcode);
        if ($urlSuffix === false) {
            return $results;
        }

        // data request
        $data = $this->api->doSearch($urlSuffix);
        
        foreach ($data->releases as $value) {
            $labelCodes = array();
            if (isset($value->{'label-info'}) &&  $value->{'label-info'} != null) {
                foreach ($value->{'label-info'} as $labelCode) {
                    $labelCodes[] = isset($labelCode->{'catalog-number'}) ? $labelCode->{'catalog-number'} : null;
                }
            }
            $results[] = array(
                'id' => isset($value->id) ? $value->id : null,
                'title' => isset($value->title) ? $value->title : null,
                'artist' => isset($value->{'artist-credit'}[0]->name) ? $value->{'artist-credit'}[0]->name : null,
                'format' => isset($value->media[0]->format) ? $value->media[0]->format : null,
                'countryCode' => isset($value->country) ? $value->country : null,
                'date' => isset($value->date) ? $value->date : null,
                'label' => isset($value->{'label-info'}[0]->label->name) ? $value->{'label-info'}[0]->label->name : null,
                'catalogNumber' => $labelCodes,
                'barcode' => isset($value->barcode) ? $value->barcode : null,
                'type' => isset($value->{'release-group'}->{'primary-type'}) ? $value->{'release-group'}->{'primary-type'} : null,
                'status' => isset($value->status) ? $value->status : null
            );
        }
        return $results;
    }

    /**
     * Check search input parameters
     * @param string $title input cd title
     * @param string $artist input cd artist
     * @param string $barcode input cd barcode
     * 
     * @return string urlSuffix or false
     */
    protected function checkInput($title, $artist, $barcode)
    {
        $title = trim($title);
        $artist = trim($artist);
        $barcode = trim($barcode);
        if (!empty($barcode) && $this->isValidBarcode($barcode) == true) {
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
