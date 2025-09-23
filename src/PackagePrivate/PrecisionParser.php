<?php

namespace DataValues\Geo\PackagePrivate;

use ValueParsers\ValueParser;

/**
 * @api
 */
class PrecisionParser {
	public function __construct( private readonly ValueParser $latLongParser, private readonly PrecisionDetector $precisionDetector ) {
	}

	public function parse( string $coordinate ): PreciseLatLong {
		$latLong = $this->latLongParser->parse( $coordinate );

		return new PreciseLatLong(
			$latLong,
			$this->precisionDetector->detectPrecision( $latLong )
		);
	}

}
