<?php

namespace DataValues\Geo\Parsers;

use DataValues\Geo\PackagePrivate\DdPrecisionDetector;
use DataValues\Geo\PackagePrivate\DmPrecisionDetector;
use DataValues\Geo\PackagePrivate\DmsPrecisionDetector;
use DataValues\Geo\PackagePrivate\FloatPrecisionDetector;
use DataValues\Geo\PackagePrivate\PrecisionParser;
use DataValues\Geo\Values\LatLongPrecision;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;

class LatLongPrecisionParser {

	private $options;
	private $parsers;

	public function __construct( ParserOptions $options = null ) {
		$this->options = $options;
	}

	public function parse( string $coordinate ): LatLongPrecision {
		foreach ( $this->getParsers() as $parser ) {
			try {
				$latLongPrecision = $parser->parse( $coordinate );
			} catch ( ParseException $parseException ) {
				continue;
			}

			return $latLongPrecision;
		}

		throw new ParseException(
			'The format of the coordinate could not be determined.',
			$coordinate
		);
	}

	/**
	 * @return PrecisionParser[]
	 */
	private function getParsers(): iterable {
		if ( $this->parsers === null ) {
			$this->parsers = new \CachingIterator( $this->getNewParsers(), \CachingIterator::FULL_CACHE );
		}

		return $this->parsers;
	}

	private function getNewParsers(): \Generator {
		yield new PrecisionParser( new FloatCoordinateParser( $this->options ), new FloatPrecisionDetector() );
		yield new PrecisionParser( new DmsCoordinateParser( $this->options ), new DmsPrecisionDetector() );
		yield new PrecisionParser( new DmCoordinateParser( $this->options ), new DmPrecisionDetector() );
		yield new PrecisionParser( new DdCoordinateParser( $this->options ), new DdPrecisionDetector() );
	}

}
