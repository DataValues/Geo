<?php

namespace DataValues\Geo\PackagePrivate;

use ValueParsers\ValueParser;

class PrecisionParser {

	private $latLongParser;
	private $precisionDetector;

	public function __construct( ValueParser $latLongParser, PrecisionDetector $precisionDetector ) {
		$this->latLongParser = $latLongParser;
		$this->precisionDetector = $precisionDetector;
	}

	public function parse( string $coordinate ): PreciseLatLong {
		$latLong = $this->latLongParser->parse( $coordinate );

		return new PreciseLatLong(
			$latLong,
			$this->precisionDetector->detectPrecision( $latLong )
		);
	}

}
