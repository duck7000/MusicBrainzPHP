musicBrainzPHP
=======

PHP library for retrieving CD information from musicBrainz API.<br>
Retrieve most of the information you can see on musicBrainz page of specific title.<br>
Search for title, artist, barcode, musicBrainz discid, catalog number or override format<br>
Get front and back cover art image urls. from coverartarchive.org<br>
Search is default for CD (change in config) or override in TitleSearch class search().<br>
All info is in the wikipages, every class and method is described there!


Quick Start
===========

* If you're not using composer or an autoloader include `bootstrap.php`.
* Search for cd title, artist, barcode, musicBrainz discid, catalog number or override format
```php
$music = new \Music\TitleSearch();
$results = $music->search("", "", "724383065820", "", "", ""); // example with barcode (title and artist are ignored)
$results = $music->search("", "", "", "ys9FSMFAFYjQIkkVXrd3iIs6s.o-", "", ""); // example with discid only
$results = $music->search("who made who", "AC/DC", "", "", "", ""); // example with CD title and artist
$results = $music->search("who made who", "", "", "", "", ""); // example with CD title only
$results = $music->search("", "AC/DC", "", "", "", ""); // example with artist only
$results = $music->search("", "", "", "", "12345-2", ""); // example with CD catalog number only
$results = $music->search("who made who", "", "", "", "", "Vinyl"); // example with format override
```


Installation
============

This library uses musicBrainz API.

Get the files with one of:
* Git clone. Checkout the latest release tag.
* [Zip/Tar download]

### Requirements
* PHP >= 7.4 - 8.3 (all versions < 8.0 are end of life)
* PHP cURL extension


Options
=============

musicBrainz has a few options in config:

Default user agent (this must be something that identifies the user and program!) possible ban!<br>
Default search: CD (this can be others too like vinyl) (this can be overriden in TitleSearch class search()<br>
Default search limit: 25 (range = 1-100 including 1 and 100)<br>


Fetching data from a title
====================

```php
include "bootstrap.php"; // Load the class if you're not using an autoloader
$music = new \Music\Title("095e2e2e-60c4-4f9f-a14a-2cc1b468bf66"); // parameter is the found musicBrainz id from search)
$results = $music->fetchData(); // This returns a array with all available info of this title
```

Credits to imdbphp, musicBrainzPHP is loosly based on it.
