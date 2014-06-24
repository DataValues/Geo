<?php

namespace DataValues;

/**
 * @since 0.2
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class LatLongValueCalculator {

	/**
	 * Normalizes latitude to [-90°..+90°] and longitude to ]-180°..+180°].
	 *
	 * @param LatLongValue $value
	 *
	 * @return LatLongValue
	 */
	public function normalize( LatLongValue $value ) {
		$lat  = $value->getLatitude();
		$long = $value->getLongitude();

		// Normalize to ]-180°..+180°]
		if ( $long > 180 ) {
			$long -= 360;
		} elseif ( $long <= -180 ) {
			$long += 360;
		}

		if ( $lat >= 270 ) {
			// Same side of the globe, on the southern hemisphere
			$lat -= 360;
		} elseif ( $lat <= -270 ) {
			// Same side of the globe, on the northern hemisphere
			$lat += 360;
		} elseif ( $lat > 90 ) {
			// Other side of the globe
			$lat = 180 - $lat;
			$long += $long > 0 ? -180 : 180;
		} elseif ( $lat < -90 ) {
			// Other side of the globe
			$lat = -180 - $lat;
			$long += $long > 0 ? -180 : 180;
		}

		// North/south pole
		if ( abs( $lat ) === 90.0 ) {
			$long = 0;
		}

		return new LatLongValue( $lat, $long );
	}

}
