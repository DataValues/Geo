<?php

namespace DataValues\Geo\Formatters;

use DataValues\Geo\Values\GlobeCoordinateValue;
use InvalidArgumentException;
use ValueFormatters\ValueFormatterBase;

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
	 * @param GlobeCoordinateValue $value
	 *
	 * @throws InvalidArgumentException
	 * @return string Text
	 */
	public function format( $value ) {
		if ( !( $value instanceof GlobeCoordinateValue ) ) {
			throw new InvalidArgumentException( 'Data value type mismatch. Expected a GlobeCoordinateValue.' );
		}

		$formatter = new GeoCoordinateFormatter( $this->options );

		return $formatter->formatLatLongValue( $value->getLatLong(), $value->getPrecision() );
	}

}
