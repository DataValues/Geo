<?php

declare( strict_types = 1 );

namespace DataValues\Geo\PackagePrivate;

/**
 * @api
 */
class FloatPrecisionDetector extends PrecisionDetector {

	protected function detectDegreePrecision( float $degree ): float {
		$split = explode( '.', (string)round( $degree, 8 ) );

		if ( isset( $split[1] ) ) {
			return pow( 10, -strlen( $split[1] ) );
		}

		return 1;
	}

}
