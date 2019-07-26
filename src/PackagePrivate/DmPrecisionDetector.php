<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

class DmPrecisionDetector extends PrecisionDetector {

	private $dmsPrecisionDetector;

	public function __construct() {
		$this->dmsPrecisionDetector = new DmsPrecisionDetector();
	}

	protected function detectDegreePrecision( float $degree ): float {
		$minutes = $degree * 60;
		$split = explode( '.', (string)round( $minutes, 6 ) );

		if ( isset( $split[1] ) ) {
			return $this->dmsPrecisionDetector->detectDegreePrecision( $degree );
		}

		return 1 / 60;
	}

}
