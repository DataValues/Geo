<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use DataValues\Geo\PackagePrivate\LatLongPrecisionParser;
use DataValues\Geo\PackagePrivate\Precision;
use DataValues\Geo\Values\GlobeCoordinateValue;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;

/**
 * Extends the LatLongParser by adding precision detection support.
 *
 * The object that gets constructed is a GlobeCoordinateValue rather then a LatLongValue.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author H. Snater < mediawiki@snater.com >
 * @author Thiemo Kreuz
 */
class GlobeCoordinateParser implements ValueParser {

	public const FORMAT_NAME = 'globe-coordinate';

	/**
	 * Option specifying the globe. Should be a string containing a Wikidata concept URI.
	 * Defaults to Earth.
	 */
	public const OPT_GLOBE = 'globe';

	private $options;
	private $latLongPrecisionParser;

	public function __construct( ParserOptions $options = null ) {
		$this->options = $options ?: new ParserOptions();

		$this->options->defaultOption( ValueParser::OPT_LANG, 'en' );
		$this->options->defaultOption( self::OPT_GLOBE, 'http://www.wikidata.org/entity/Q2' );
	}

	/**
	 * @see StringValueParser::stringParse
	 *
	 * @param string $value
	 *
	 * @throws ParseException
	 * @return GlobeCoordinateValue
	 */
	public function parse( $value ): GlobeCoordinateValue {
		$parser = $this->getParser();

		try {
			$latLongPrecision = $parser->parse( $value );
		} catch ( \Exception $ex ) {
			throw new ParseException(
				'The format of the coordinate could not be determined.',
				$value,
				self::FORMAT_NAME
			);
		}

		return new GlobeCoordinateValue(
			$latLongPrecision->getLatLong(),
			$this->getPrecision( $latLongPrecision->getPrecision() ),
			$this->options->getOption( self::OPT_GLOBE )
		);
	}

	private function getParser() {
		if ( $this->latLongPrecisionParser === null ) {
			$this->latLongPrecisionParser = new LatLongPrecisionParser( $this->options );
		}

		return $this->latLongPrecisionParser;
	}

	private function getPrecision( Precision $detectedPrecision ): float {
		if ( $this->options->hasOption( 'precision' ) ) {
			return $this->options->getOption( 'precision' );
		}

		return $detectedPrecision->toFloat();
	}

}
