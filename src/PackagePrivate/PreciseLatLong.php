<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

use DataValues\Geo\Values\LatLongValue;

/**
 * @api
 */
class PreciseLatLong {
	public function __construct( private readonly LatLongValue $latLong, private readonly Precision $precision ) {
	}

	public function getLatLong(): LatLongValue {
		return $this->latLong;
	}

	public function getPrecision(): Precision {
		return $this->precision;
	}

}
