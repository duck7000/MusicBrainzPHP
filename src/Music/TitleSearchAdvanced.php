<?php

#############################################################################
# musicBrainzPHP                                ed (github user: duck7000)  #
# written by ed (github user: duck7000)                                     #
# ------------------------------------------------------------------------- #
# This program is free software; you can redistribute and/or modify it      #
# under the terms of the GNU General Public License (see doc/LICENSE)       #
#############################################################################

namespace Music;

class TitleSearchAdvanced extends MdbBase
{

    /**
     * Search only in artist category/entity of musicBrainz! This is different from the normal search for artist
     * @param string $artist input artist name
     * 
     * @return results[] array of Artists
     *  id: string artist/group id
     *  name: string artist/group name
     *  area: string area like Australia
     *  description: string short description of this artist/group
     *  type: string type like group or person
     * 
     */
    public function searchArtist($artist = '')
    {
        $results = array();

        $artist = trim($artist);
        if (empty($artist)) {
            return $results;
        } else {
            $urlSuffix = '?query=artist:' . rawurlencode($artist);
        }

        // data request
        $data = $this->api->doArtistSearch($urlSuffix);

        foreach ($data->artists as $value) {
            $results[] = array(
                'id' => isset($value->id) ? $value->id : null,
                'name' => isset($value->name) ? $value->name : null,
                'area' => isset($value->area->name) ? $value->area->name : null,
                'description' => isset($value->disambiguation) ? $value->disambiguation : null,
                'type' => isset($value->type) ? $value->type : null
            );
        }
        return $results;
    }

    /**
     * Fetch discography release groups from artist (albums only)
     * @param string $artistId artist id found with searchArtist()
     * @param string $type Include only this type in search, exclude all others
     * values for $type:
     *      album
     *      single
     *      ep
     *      broadcast
     *      other
     *      live
     *      compilation
     *      remix
     *      interview
     *      soundtrack
     *      spokenword
     *      audiobook
     *      demo
     * @return array
     * Array
     *   (
     *       [0] => Array
     *           (
     *               [id] => 2b81e645-4586-4456-843a-9bc19a217470
     *               [title] => High Voltage
     *               [aritst] => AC/DC
     *               [date] => 1975-02-17
     *               [totalReleasesCount] => 8
     *               [primaryType] => Album
     *           )
     *     )
     */
    public function fetchReleaseGroups($artistId, $type)
    {
        // Data request
        $data = $this->api->doReleaseGroupSearch($artistId, $type);

        $results = array();
        foreach ($data->{'release-groups'} as $releaseGroup) {
            $id = isset($releaseGroup->id) ? $releaseGroup->id : null;
            $title = isset($releaseGroup->title) ? $releaseGroup->title : null;
            $artist = isset($releaseGroup->{'artist-credit'}[0]->name) ? $releaseGroup->{'artist-credit'}[0]->name : null;
            $date = isset($releaseGroup->{'first-release-date'}) ? $releaseGroup->{'first-release-date'} : null;
            $totalReleasesCount = isset($releaseGroup->count) ? $releaseGroup->count : null;
            $primaryType = isset($releaseGroup->{'primary-type'}) ? $releaseGroup->{'primary-type'} : null;

            $results[] = array(
                'id' => $id,
                'title' => $title,
                'aritst' => $artist,
                'date' => $date,
                'totalReleasesCount' => $totalReleasesCount,
                'primaryType' => $primaryType
            );
        }
        return $this->sortByDate($results);
    }

    /**
     * Fetch all releases from specific releaseGroupId
     * @param $relGroupId releaseGroup id found with fetchReleaseGroups()
     * @return array
     * Array
        * (
            * [0] => Array
                * (
                    * [id] => ddee5911-8a13-41e6-88db-6534a5f4fc46
                    *[title] => High Voltage
                    * [aritst] => AC/DC
                    * [date] => 1975-02-17
                    * [status] => Official
                    * [barcode] => 
                    * [countryCode] => AU
                    * [labels] => Array
                        * (
                            * [0] => Array
                                * (
                                    * [name] => Albert Productions
                                    * [id] => ff70518a-f935-4b34-9f04-c099c644554b
                                    * [catalog] => APLP-009
                                * )
                        * )
                    * [media] => Array
                        * (
                            * [0] => Array
                                * (
                                    * [format] => 12" Vinyl
                                    * [trackCount] => 8
                                * )
                        * )
                * )
     *     )
     */
    public function releaseGroupReleases($relGroupId)
    {
        // Data request
        $data = $this->api->doReleaseGroupReleases($relGroupId);

        $results = array();
        foreach ($data->releases as $release) {
            $id = isset($release->id) ? $release->id : null;
            $title = isset($release->title) ? $release->title : null;
            $artist = isset($release->{'artist-credit'}[0]->name) ? $release->{'artist-credit'}[0]->name : null;
            $date = isset($release->date) ? $release->date : null;
            $status = isset($release->status) ? $release->status : null;
            $barcode = isset($release->barcode) ? $release->barcode : null;
            $countryCode = isset($release->country) ? $release->country : null;

            // Labels
            $labels = array();
            if (isset($release->{'label-info'}) && !empty($release->{'label-info'})) {
                foreach ($release->{'label-info'} as $label) {
                    $label = array(
                        'name' => isset($label->label->name) ? $label->label->name : null,
                        'id' => isset($label->label->id) ? $label->label->id : null,
                        'catalog' => isset($label->{'catalog-number'}) ? $label->{'catalog-number'} : null
                    );
                    $labels[] = $label;
                }
            }

            // Media
            $media = array();
            if (isset($release->media) && !empty($release->media)) {
                foreach ($release->media as $medium) {
                    $format = isset($medium->format) ? $medium->format : null;
                    $trackCount = isset($medium->{'track-count'}) ? $medium->{'track-count'} : null;
                    $media[] = array(
                        'format' => $format,
                        'trackCount' => $trackCount
                    );
                }
            }

            $results[] = array(
                'id' => $id,
                'title' => $title,
                'aritst' => $artist,
                'date' => $date,
                'status' => $status,
                'barcode' => $barcode,
                'countryCode' => $countryCode,
                'labels' => $labels,
                'media' => $media
            );
        }
        return $this->sortByDate($results);
    }
    
    /**
     * Sort $results array by date
     * @param array $array
     * @return sorted array
     */
    protected function sortByDate($array)
    {
        // sort array by date
        usort($array, function($a, $b) {
            $ad = $a['date'];
            $bd = $b['date'];

            if ($ad == $bd) {
            return 0;
            }

            return $ad < $bd ? -1 : 1;
        });
        return $array;
    }

}
