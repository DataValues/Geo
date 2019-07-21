<?php

declare( strict_types = 1 );

namespace DataValues\Geo;

use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;

/**
 * Logical and mathematical helper functions for normalizations and calculations with
 * GlobeCoordinateValue objects.
 *
 * @since 0.2
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class GlobeMath {

	/**
	 * @todo Move this constant next to GlobeCoordinateValue::GLOBE_EARTH?
	 */
	public const GLOBE_MOON = 'http://www.wikidata.org/entity/Q405';

	/**
	 * @param string|null $globe IRI of a globe.
	 *
	 * @return string Normalized IRI, defaults to 'http://www.wikidata.org/entity/Q2'.
	 */
	public function normalizeGlobe( ?string $globe ) {
		if ( !is_string( $globe ) || $globe === '' ) {
			return GlobeCoordinateValue::GLOBE_EARTH;
		}

		return $globe;
	}

	/**
	 * Normalizes latitude to [-90°..+90°]. Normalizes longitude to [-180°..+180°[ on Earth and
	 * Moon and to [0°..+360°[ on all other globes.
	 * @see http://planetarynames.wr.usgs.gov/TargetCoordinates
	 *
	 * @param GlobeCoordinateValue $value
	 *
	 * @return GlobeCoordinateValue
	 */
	public function normalizeGlobeCoordinate( GlobeCoordinateValue $value ): GlobeCoordinateValue {
		return new GlobeCoordinateValue(
			$this->normalizeGlobeLatLong( $value->getLatLong(), $value->getGlobe() ),
			$value->getPrecision(),
			$value->getGlobe()
		);
	}

	/**
	 * @param LatLongValue $value
	 * @param string|null $globe
	 *
	 * @return LatLongValue
	 */
	public function normalizeGlobeLatLong( LatLongValue $value, string $globe = null ): LatLongValue {
		switch ( $this->normalizeGlobe( $globe ) ) {
			case GlobeCoordinateValue::GLOBE_EARTH:
			case self::GLOBE_MOON:
				$minimumLongitude = -180;
				break;
			default:
				$minimumLongitude = 0;
		}

		return $this->normalizeLatLong( $value, $minimumLongitude );
	}

	/**
	 * @param LatLongValue $value
	 * @param float $minimumLongitude
	 *
	 * @return LatLongValue
	 */
	public function normalizeLatLong( LatLongValue $value, float $minimumLongitude = -180.0 ): LatLongValue {
		$lon = $this->getNormalizedLongitude( $value->getLongitude(), $minimumLongitude );
		$lat = $value->getLatitude();

		if ( $lat >= 270 ) {
			// Same side of the globe, on the southern hemisphere.
			$lat -= 360;
		} elseif ( $lat <= -270 ) {
			// Same side of the globe, on the northern hemisphere.
			$lat += 360;
		} elseif ( $lat > 90 ) {
			// Other side of the globe
			$lat = 180 - $lat;
			$lon += $lon - 180 >= $minimumLongitude ? -180 : 180;
		} elseif ( $lat < -90 ) {
			// Other side of the globe
			$lat = -180 - $lat;
			$lon += $lon - 180 >= $minimumLongitude ? -180 : 180;
		}

		// North/south pole
		if ( abs( $lat ) === 90.0 ) {
			$lon = 0;
		}

		return new LatLongValue( $lat, $lon );
	}

	private function getNormalizedLongitude( float $longitude, float $minimumLongitude ): float {
		// Normalize to [-180°..+180°[ on Earth/Moon, [0°..+360°[ on other globes.
		if ( $longitude >= $minimumLongitude + 360 ) {
			$longitude -= 360;
		} elseif ( $longitude < $minimumLongitude ) {
			$longitude += 360;
		}

		return $longitude;
	}

}
