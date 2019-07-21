<?php

namespace DataValues\Geo\PackagePrivate;

use DataValues\Geo\Values\GlobeCoordinateValue;
use ValueParsers\ValueParser;

class Derp {

	private $latLongParser;
	private $precisionDetector;
	private $globe;

	public function __construct( ValueParser $latLongParser, PrecisionDetector $precisionDetector, string $globe ) {
		$this->latLongParser = $latLongParser;
		$this->precisionDetector = $precisionDetector;
		$this->globe = $globe;
	}

	public function parse( string $coordinate ): GlobeCoordinateValue {
		$latLong = $this->latLongParser->parse( $coordinate );

		return new GlobeCoordinateValue(
			$latLong,
			$this->precisionDetector->detectPrecision( $latLong ),
			$this->globe
		);
	}

}