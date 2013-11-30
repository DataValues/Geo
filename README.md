# DataValues Geo

Library containing value objects to represent geographical information, parsers to turn user input
into such value objects, and formatters to turn them back into user consumable representations.

It is part of the [DataValues set of libraries](https://github.com/DataValues).

[![Build Status](https://secure.travis-ci.org/DataValues/Geo.png?branch=master)](http://travis-ci.org/DataValues/Geo)
[![Code Coverage](https://scrutinizer-ci.com/g/DataValues/Geo/badges/coverage.png?s=bf4cfd11f3b985fd05918f395c350b376a9ce0ee)](https://scrutinizer-ci.com/g/DataValues/Geo/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/DataValues/Geo/badges/quality-score.png?s=e695e42b53d74fc02e5cfa2aa218420f062edbd2)](https://scrutinizer-ci.com/g/DataValues/Geo/)

On [Packagist](https://packagist.org/packages/data-values/geo):
[![Latest Stable Version](https://poser.pugx.org/data-values/geo/version.png)](https://packagist.org/packages/data-values/geo)
[![Download count](https://poser.pugx.org/data-values/geo/d/total.png)](https://packagist.org/packages/data-values/geo)

## Installation

The recommended way to use this library is via [Composer](http://getcomposer.org/).

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `data-values/geo` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
version 1.0 of this package:

    {
        "require": {
            "data-values/geo": "1.0.*"
        }
    }

### Manual

Get the code of this package, either via git, or some other means. Also get all dependencies.
You can find a list of the dependencies in the "require" section of the composer.json file.
Then take care of autoloading the classes defined in the src directory.

## Tests

This library comes with a set up PHPUnit tests that cover all non-trivial code. You can run these
tests using the PHPUnit configuration file found in the root directory. The tests can also be run
via TravisCI, as a TravisCI configuration file is also provided in the root directory.

## Authors

DataValues Geo has been written by the Wikidata team, as [Wikimedia Germany]
(https://wikimedia.de) employees for the [Wikidata project](https://wikidata.org/).

It is based upon and contains a lot of code written by [Jeroen De Dauw]
(https://github.com/JeroenDeDauw) for the [Maps](https://github.com/JeroenDeDauw/Maps) and
[Semantic MediaWiki](https://semantic-mediawiki.org/) projects.

## Release notes

### 0.1.1 (2013-11-30)

* Added support for direction notation to GeoCoordinateFormatter
* Decreased complexity of GeoCoordinateFormatter
* Decreased complexity and coupling of GeoCoordinateFormatterTest

### 0.1 (2013-11-17)

Initial release with these features:

* GeoCoordinateValue
* GlobeCoordinateValue
* LatLongValue
* GeoCoordinateFormatter
* GlobeCoordinateFormatter
* DdCoordinateParser
* DmCoordinateParser
* DmsCoordinateParser
* FloatCoordinateParser
* GeoCoordinateParser
* GlobeCoordinateParser

## Links

* [DataValues Geo on Packagist](https://packagist.org/packages/data-values/geo)
* [DataValues Geo on TravisCI](https://travis-ci.org/DataValues/Geo)
