<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

use DataValues\Geo\Values\LatLongValue;

class PreciseLatLong {

	private $latLong;
	private $precision;

	public function __construct( LatLongValue $latLong, Precision $precision ) {
		$this->latLong = $latLong;
		$this->precision = $precision;
	}

	public function getLatLong(): LatLongValue {
		return $this->latLong;
	}

	public function getPrecision(): Precision {
		return $this->precision;
	}

}
