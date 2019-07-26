<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

class DmsPrecisionDetector extends PrecisionDetector {

	protected function detectDegreePrecision( float $degree ): float {
		$seconds = $degree * 3600;
		$split = explode( '.', (string)round( $seconds, 4 ) );

		if ( isset( $split[1] ) ) {
			return pow( 10, -strlen( $split[1] ) ) / 3600;
		}

		return 1 / 3600;
	}

}
