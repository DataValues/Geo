<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use DataValues\Geo\PackagePrivate\DdPrecisionDetector;
use DataValues\Geo\PackagePrivate\Derp;
use DataValues\Geo\PackagePrivate\DmPrecisionDetector;
use DataValues\Geo\PackagePrivate\DmsPrecisionDetector;
use DataValues\Geo\PackagePrivate\FloatPrecisionDetector;
use DataValues\Geo\PackagePrivate\PrecisionDetector;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
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
	 * Option specifying the globe. Should be a string containing a Wikidata concept URI. Defaults
	 * to Earth.
	 */
	public const OPT_GLOBE = 'globe';

	private $options;

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
		/**
		 * @var $stuff Derp[]
		 */
		$stuff = [
			$this->newDerp( $this->getFloatParser(), new FloatPrecisionDetector() ),
			$this->newDerp( $this->getDmsParser(), new DmsPrecisionDetector() ),
			$this->newDerp( $this->getDmParser(), new DmPrecisionDetector() ),
			$this->newDerp( $this->getDdParser(), new DdPrecisionDetector() ),
		];

		foreach ( $stuff as $derp ) {
			try {
				$globeCoordinate = $derp->parse( $value );
			} catch ( ParseException $parseException ) {
				continue;
			}

			if ( $this->options->hasOption( 'precision' ) ) {
				return $globeCoordinate->withPrecision( $this->options->getOption( 'precision' ) );
			}

			return $globeCoordinate;
		}

		throw new ParseException(
			'The format of the coordinate could not be determined.',
			$value,
			self::FORMAT_NAME
		);
	}

	private function newDerp( ValueParser $floatParser, PrecisionDetector $precisionDetector ): Derp {
		return new Derp(
			$floatParser,
			$precisionDetector,
			$this->options->getOption( self::OPT_GLOBE )
		);
	}

	private function getFloatParser(): ValueParser {
		return new FloatCoordinateParser( $this->options );
	}

	private function getDmsParser(): ValueParser {
		return new DmsCoordinateParser( $this->options );
	}

	private function getDmParser(): ValueParser {
		return new DmCoordinateParser( $this->options );
	}

	private function getDdParser(): ValueParser {
		return new DdCoordinateParser( $this->options );
	}

}
