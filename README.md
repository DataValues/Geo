# DataValues Geo

Small library for **parsing, formatting and representing coordinates**. This library supports multiple coordinate formats,
it is well tested, and it is used by the software behind Wikipedia and Wikidata.

[![Build Status](https://travis-ci.org/DataValues/Geo.svg?branch=master)](https://travis-ci.org/DataValues/Geo)
[![Code Coverage](https://scrutinizer-ci.com/g/DataValues/Geo/badges/coverage.png?s=bf4cfd11f3b985fd05918f395c350b376a9ce0ee)](https://scrutinizer-ci.com/g/DataValues/Geo/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/DataValues/Geo/badges/quality-score.png?s=e695e42b53d74fc02e5cfa2aa218420f062edbd2)](https://scrutinizer-ci.com/g/DataValues/Geo/)

On [Packagist](https://packagist.org/packages/data-values/geo):
[![Latest Stable Version](https://poser.pugx.org/data-values/geo/version.png)](https://packagist.org/packages/data-values/geo)
[![Download count](https://poser.pugx.org/data-values/geo/d/total.png)](https://packagist.org/packages/data-values/geo)

## Usage

To **parse a string to a `LatLongValue` object** you use one of the coordinate parsers.

```php
$parser = new LatLongParser();
$latLongValue = $parser->parse('55.7557860 N, 37.6176330 W');
var_dump($latLongValue->getLongitude()); // float: -37.6176330
```

These parsers are provided:

* `LatLongParser` - Facade for format specific parsers. In most cases you will be using this one
* `DdCoordinateParser` - Parses decimal degree coordinates
* `DmCoordinateParser` - Parses decimal minute coordinates
* `DmsCoordinateParser` - Parses degree minute second coordinates
* `FloatCoordinateParser` - Parses float coordinates 
* `GlobeCoordinateParser` - Parses coordinates into `GlobeCoordinateValue` objects

To **turn a coordinate object into a string** you use one of the coordinate formatters.

```php
$formatter = new LatLongFormatter();
$coordinateString = $formatter->format(new LatLongValue(42.23, 13.37));
```

These formatters are provided:

* `LatLongFormatter` - Formats a `LatLongValue` into any of the supported formats
* `GlobeCoordinateFormatter` - Formats a `GlobeCoordinateValue`

To **represent a set of coordinates** you use one of the Value Objects.

`LatLongValue` has a float latitude and longitude. `GlobeCoordinateValue` wraps `LatLongValue`
and adds a precision and a globe identifier.

The **supported coordinate formats** are:

* Degree minute second (`55° 45' 20.8296", -37° 37' 3.4788"` or `55° 45' 20.8296" N, 37° 37' 3.4788" W`)
* Decimal minute (`55° 30', -37° 30'` or `55° 30' N, 37° 30' W`)
* Decimal degree (`55.7557860°, -37.6176330°` or `55.7557860° N, 37.6176330° W`)
* Float (`55.7557860, -37.6176330` or `55.7557860 N, 37.6176330 W`)

The parsers and formatters allow you to customize the used symbols for degrees, minutes and seconds and
to change the letters used to indicate direction (N, E, S, W).

## Requirements

**Geo 4.x:** PHP 7.1 or later (tested with PHP 7.1 up to PHP 7.4)

**Geo 3.x:** PHP 5.5 or later (tested with PHP 5.5 up to PHP 7.4 and HHVM)

## Installation

To add this package as a local, per-project dependency to your project, simply add a
dependency on `data-values/geo` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
version 4.x of this package:

```json
    {
        "require": {
            "data-values/geo": "^4.0.0"
        }
    }
```

## Running the tests

For tests only

    composer test

For style checks only

    composer cs

For a full CI run

    composer ci

## Authors

DataValues Geo is based upon and contains a lot of code written by
[Jeroen De Dauw](https://github.com/JeroenDeDauw) for the
[Maps](https://github.com/JeroenDeDauw/Maps) and
[Semantic MediaWiki](https://semantic-mediawiki.org/) projects.

Significant contributions where made by the Wikidata team, as [Wikimedia Germany](https://wikimedia.de/en)
employees for the [Wikidata project](https://wikidata.org/).

## Release notes

### 4.2.1 (2019-12-18)

* Fixed `GlobeCoordinateParser` not being able to parse multiple values (4.2.0 regression)

### 4.2.0 (2019-09-20)

* Added `GlobeCoordinateValue::withPrecision`

### 4.1.0 (2018-10-29)

* Added "PHP strict types" to all files
* `LatLongValue` no longer extends `DataValueObject`
* `GlobeCoordinateValue` no longer extends `DataValueObject`
* Reordered methods in `LatLongValue` and `GlobeCoordinateValue` for readability
* Undeprecated `LatLongValue::newFromArray`
* Undeprecated `GlobeCoordinateValue::newFromArray`

### 4.0.1 (2018-08-10)

* Fixed parsing of coordinates with lowercase S/W directions
* Fixed parsing DMS coordinates that omit a single minute number

### 3.0.1 (2018-08-01)

* Fixed parsing of coordinates with lowercase S/W directions

### 2.1.2 (2018-08-01)

* Fixed parsing of coordinates with lowercase S/W directions

### 4.0.0 (2018-07-13)

* Updated minimum required PHP version from 5.5.9 to 7.1
* Added scalar type hints
* Added return type hints
* Added nullable type hints
* Made constant visibility explicit
* Constructing an invalid `LatLongValue` now causes `InvalidArgumentException` instead of `OutOfRangeException`

### 3.0.0 (2018-03-20)

* Removed `DATAVALUES_GEO_VERSION` constant
* The parsers no longer extend `StringValueParser`
	* They no longer have public methods `setOptions` and `getOptions`
	* They no longer have protected field `options`
	* They no longer have protected methods `requireOption`, `defaultOption` and `stringParse`
	* `GlobeCoordinateParser` and `LatLongParser` no longer have protected method `getOption`
* Made several protected fields and methods private
	* All fields of `LatLongValue`
	* The `detect…Precision` methods in `GlobeCoordinateParser`
	* `LatLongParser::getParsers`
* Removed public static method `LatLongParser::areCoordinates`
* Dropped dependence on the DataValues Common library
* Removed long deprecated class aliases
	* `DataValues\GlobeCoordinateValue` (now in `DataValues\Geo\Values`)
	* `DataValues\LatLongValue` (now in `DataValues\Geo\Values`)
	* `DataValues\Geo\Formatters\GeoCoordinateFormatter` (now `LatLongFormatter`)
	* `DataValues\Geo\Parsers\GeoCoordinateParser` (now `LatLongParser`)

### 2.1.1 (2017-08-09)

* Allow use with ~0.4.0 of DataValues/Common

### 2.1.0 (2017-08-09)

* Remove MediaWiki integration
* Make use of the …::class feature
* Add .gitattributes to exclude not needed files from git exports
* Use Wikibase CodeSniffer instead of Mediawiki's
* Move to short array syntax

### 2.0.1 (2017-06-26)

* Fixed `GlobeCoordinateValue::newFromArray` and `LatLongValue::newFromArray` not accepting mixed
  values.
* Deprecated `GlobeCoordinateValue::newFromArray` and `LatLongValue::newFromArray`.
* Updated minimum required PHP version from 5.3 to 5.5.9.

### 2.0.0 (2017-05-09)

* `GlobeCoordinateValue` does not accept empty strings as globes any more.
* `GlobeCoordinateValue` does not accept precisions outside the [-360..+360] interval any more.
* Changed hash calculation of `GlobeCoordinateValue` in an incompatible way.
* Renamed `GeoCoordinateFormatter` to `LatLongFormatter`, leaving a deprecated alias.
* Renamed `GeoCoordinateParser` to `LatLongParser`, leaving a deprecated alias.
* Renamed `GeoCoordinateParserBase` to `LatLongParserBase`.
* Deprecated `LatLongParser::areCoordinates`.

### 1.2.2 (2017-03-14)

* Fixed multiple rounding issues in `GeoCoordinateFormatter`.

### 1.2.1 (2016-12-16)

* Fixed another IEEE issue in `GeoCoordinateFormatter`.

### 1.2.0 (2016-11-11)

* Added missing inline documentation to public methods and constants.
* Added a basic PHPCS rule set, can be run with `composer phpcs`.

### 1.1.8 (2016-10-12)

* Fixed an IEEE issue in `GeoCoordinateFormatter`
* Fixed a PHP 7.1 compatibility issue in a test

### 1.1.7 (2016-05-25)

* Made minor documentation improvements

### 1.1.6 (2016-04-02)

* Added compatibility with DataValues Common 0.3.x

### 1.1.5 (2015-12-28)

* The component can now be installed together with DataValues Interfaces 0.2.x

### 1.1.4 (2014-11-25)

* Add fall back to default on invalid precision to more places.

### 1.1.3 (2014-11-19)

* Fall back to default on invalid precision instead of dividing by zero.

### 1.1.2 (2014-11-18)

* Precision detection in `GlobeCoordinateParser` now has a lower bound of 0.00000001°

### 1.1.1 (2014-10-21)

* Removed remaining uses of class aliases from messages and comments
* Fixed some types in documentation

### 1.1.0 (2014-10-09)

* Made the component installable with DataValues 1.x
* `GeoCoordinateFormatter` now supports precision in degrees
* `GlobeCoordinateFormatter` now passes the globe precision to the `GeoCoordinateFormatter` it uses
* Introduced `FORMAT_NAME` class constants on ValueParsers in order to use them as expectedFormat
* Changed ValueParsers to pass rawValue and expectedFormat arguments when constructing a `ParseException`

### 1.0.0 (2014-07-31)

* All classes and interfaces have been moved into the `DataValues\Geo` namespace
    * `DataValues\LatLongValue` has been left as deprecated alias
    * `DataValues\GlobeCoordinateValue` has been left as deprecated alias
* Globe in `GlobeCoordinateValue` now defaults to `http://www.wikidata.org/entity/Q2`

### 0.2.0 (2014-07-07)

* Removed deprecated `GeoCoordinateValue`
* Added `GlobeMath`

### 0.1.2 (2014-01-22)

* Added support for different levels of spacing in GeoCoordinateFormatter

### 0.1.1 (2013-11-30)

* Added support for direction notation to GeoCoordinateFormatter
* Decreased complexity of GeoCoordinateFormatter
* Decreased complexity and coupling of GeoCoordinateFormatterTest

### 0.1.0 (2013-11-17)

Initial release with these features:

* LatLongValue
* GlobeCoordinateValue
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
* [DataValues on Wikimedia's Phabricator](https://phabricator.wikimedia.org/project/view/122/)
