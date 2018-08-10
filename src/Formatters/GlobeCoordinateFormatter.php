<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Formatters;

use DataValues\Geo\Values\GlobeCoordinateValue;
use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatter;

/**
 * Geographical coordinates formatter.
 * Formats GlobeCoordinateValue objects.
 *
 * Formatting of latitude and longitude is done via LatLongFormatter.
 *
 * For now this is a trivial implementation that only forwards to LatLongFormatter.
 * TODO: add formatting of globe and precision
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GlobeCoordinateFormatter implements ValueFormatter {

	/**
	 * @var LatLongFormatter
	 */
	private $formatter;

	public function __construct( FormatterOptions $options = null ) {
		$this->formatter = new LatLongFormatter( $options );
	}

	/**
	 * @see ValueFormatter::format
	 *
	 * @param GlobeCoordinateValue $value
	 *
	 * @return string Plain text
	 * @throws InvalidArgumentException
	 */
	public function format( $value ): string {
		if ( !( $value instanceof GlobeCoordinateValue ) ) {
			throw new InvalidArgumentException( 'Data value type mismatch. Expected a GlobeCoordinateValue.' );
		}

		return $this->formatter->formatLatLongValue( $value->getLatLong(), $value->getPrecision() );
	}

}
