<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

use DataValues\Geo\Values\LatLongValue;

abstract class PrecisionDetector {

	public function detectPrecision( LatLongValue $latLong ): float {
		return min(
			$this->detectDegreePrecision( $latLong->getLatitude() ),
			$this->detectDegreePrecision( $latLong->getLongitude() )
		);
	}

	public abstract function detectDegreePrecision( float $degree ): float;

}