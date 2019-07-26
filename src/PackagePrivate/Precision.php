<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

class Precision {

	private $precision;

	public function __construct( float $precisionInDegrees ) {
		if ( $precisionInDegrees < -360 || $precisionInDegrees > 360 ) {
			throw new \InvalidArgumentException( '$precision needs to be between -360 and 360' );
		}

		$this->precision = $precisionInDegrees;
	}

	public function toFloat(): float {
		return $this->precision;
	}

}
