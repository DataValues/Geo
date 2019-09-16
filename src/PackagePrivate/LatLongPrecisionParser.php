<?php

namespace DataValues\Geo\PackagePrivate;

use DataValues\Geo\Parsers\DdCoordinateParser;
use DataValues\Geo\Parsers\DmCoordinateParser;
use DataValues\Geo\Parsers\DmsCoordinateParser;
use DataValues\Geo\Parsers\FloatCoordinateParser;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;

class LatLongPrecisionParser {

	private $options;
	private $parsers;

	public function __construct( ParserOptions $options = null ) {
		$this->options = $options;
	}

	public function parse( string $coordinate ): PreciseLatLong {
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
		yield new PrecisionParser( new DdCoordinateParser( $this->options ), new FloatPrecisionDetector() );
	}

}
