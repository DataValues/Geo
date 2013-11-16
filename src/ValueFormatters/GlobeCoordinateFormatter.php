<?php

namespace ValueFormatters;

use DataValues\GlobeCoordinateValue;
use InvalidArgumentException;

/**
 * Geographical coordinates formatter.
 * Formats GlobeCoordinateValue objects.
 *
 * Formatting of latitude and longitude is done via GeoCoordinateFormatter.
 *
 * For now this is a trivial implementation that only forwards to GeoCoordinateFormatter.
 * TODO: add formatting of globe and precision
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GlobeCoordinateFormatter extends ValueFormatterBase {

	/**
	 * @see ValueFormatter::format
	 *
	 * @since 0.1
	 *
	 * @param mixed $value The value to format
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function format( $value ) {
		if ( !( $value instanceof GlobeCoordinateValue ) ) {
			throw new InvalidArgumentException( 'The ValueFormatters\GlobeCoordinateFormatter can only format instances of DataValues\GlobeCoordinateValue' );
		}

		$formatter = new GeoCoordinateFormatter( $this->options );

		return $formatter->format( $value->getLatLong() );
	}

}
