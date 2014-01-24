<?php

namespace ValueFormatters;

use DataValues\LatLongValue;
use InvalidArgumentException;

/**
 * Geographical coordinates formatter.
 * Formats LatLongValue objects.
 *
 * Supports the following notations:
 * - Degree minute second
 * - Decimal degrees
 * - Decimal minutes
 * - Float
 *
 * Some code in this class has been borrowed from the
 * MapsCoordinateParser class of the Maps extension for MediaWiki.
 *
 * @since 0.1
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Adam Shorland
 */
class GeoCoordinateFormatter extends ValueFormatterBase {

	const TYPE_FLOAT = 'float';
	const TYPE_DMS = 'dms';
	const TYPE_DM = 'dm';
	const TYPE_DD = 'dd';

	/**
	 * The symbols representing the different directions for usage in directional notation.
	 * @since 0.1
	 */
	const OPT_NORTH_SYMBOL = 'north';
	const OPT_EAST_SYMBOL = 'east';
	const OPT_SOUTH_SYMBOL = 'south';
	const OPT_WEST_SYMBOL = 'west';

	/**
	 * The symbols representing degrees, minutes and seconds.
	 * @since 0.1
	 */
	const OPT_DEGREE_SYMBOL = 'degree';
	const OPT_MINUTE_SYMBOL = 'minute';
	const OPT_SECOND_SYMBOL = 'second';

	const OPT_SPACE_LATLONG = 'latlong';
	const OPT_SPACE_DIRECTION = 'direction';
	const OPT_SPACE_COORDPARTS = 'coordparts';

	const OPT_FORMAT = 'geoformat';
	const OPT_DIRECTIONAL = 'directional';

	const OPT_SEPARATOR_SYMBOL = 'separator';
	const OPT_SPACING_LEVEL = 'spacing';
	const OPT_PRECISION = 'precision';

	/**
	 * @since 0.1
	 *
	 * @param FormatterOptions $options
	 */
	public function __construct( FormatterOptions $options ) {
		parent::__construct( $options );

		$this->defaultOption( self::OPT_NORTH_SYMBOL, 'N' );
		$this->defaultOption( self::OPT_EAST_SYMBOL, 'E' );
		$this->defaultOption( self::OPT_SOUTH_SYMBOL, 'S' );
		$this->defaultOption( self::OPT_WEST_SYMBOL, 'W' );

		$this->defaultOption( self::OPT_DEGREE_SYMBOL, '°' );
		$this->defaultOption( self::OPT_MINUTE_SYMBOL, "'" );
		$this->defaultOption( self::OPT_SECOND_SYMBOL, '"' );

		$this->defaultOption( self::OPT_FORMAT, self::TYPE_FLOAT );
		$this->defaultOption( self::OPT_DIRECTIONAL, false );

		$this->defaultOption( self::OPT_SEPARATOR_SYMBOL, ',' );
		$this->defaultOption( self::OPT_SPACING_LEVEL, array(
			self::OPT_SPACE_LATLONG,
			self::OPT_SPACE_DIRECTION,
			self::OPT_SPACE_COORDPARTS )
		);
		$this->defaultOption( self::OPT_PRECISION, 4 );
	}

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
		if ( !( $value instanceof LatLongValue ) ) {
			throw new InvalidArgumentException( 'The ValueFormatters\GeoCoordinateFormatter can only format instances of DataValues\LatLongValue' );
		}

		$formatted = implode(
			$this->getOption( self::OPT_SEPARATOR_SYMBOL ) . $this->getSpacing( self::OPT_SPACE_LATLONG ),
			array(
				$this->formatLatitude( $value->getLatitude() ),
				$this->formatLongitude( $value->getLongitude() )
			)
		);

		return $formatted;
	}

	private function getSpacing( $spacingLevel ) {
		if( in_array( $spacingLevel, $this->getOption( self::OPT_SPACING_LEVEL ) ) ) {
			return ' ';
		}
		return '';
	}

	private function formatLatitude( $latitude ) {
		return $this->makeDirectionalIfNeeded(
			$this->formatCoordinate( $latitude ),
			$this->options->getOption( self::OPT_NORTH_SYMBOL ),
			$this->options->getOption( self::OPT_SOUTH_SYMBOL )
		);
	}

	private function formatLongitude( $longitude ) {
		return $this->makeDirectionalIfNeeded(
			$this->formatCoordinate( $longitude ),
			$this->options->getOption( self::OPT_EAST_SYMBOL ),
			$this->options->getOption( self::OPT_WEST_SYMBOL )
		);
	}

	private function makeDirectionalIfNeeded( $coordinate, $positiveSymbol, $negativeSymbol ) {
		if ( $this->options->getOption( self::OPT_DIRECTIONAL ) ) {
			return $this->makeDirectional( $coordinate , $positiveSymbol, $negativeSymbol);
		}

		return $coordinate;
	}

	private function makeDirectional( $coordinate, $positiveSymbol, $negativeSymbol ) {
		$isNegative = $coordinate{0} == '-';

		if ( $isNegative ) {
			$coordinate = substr( $coordinate, 1 );
		}

		$symbol = $isNegative ? $negativeSymbol : $positiveSymbol;

		return $coordinate . $this->getSpacing( self::OPT_SPACE_DIRECTION ) . $symbol;
	}

	private function formatCoordinate( $coordinate ) {
		switch ( $this->getOption( self::OPT_FORMAT ) ) {
			case self::TYPE_FLOAT:
				return $this->getInFloatFormat( $coordinate );
			case self::TYPE_DMS:
				return $this->getInDegreeMinuteSecondFormat( $coordinate );
			case self::TYPE_DD:
				return $this->getInDecimalDegreeFormat( $coordinate );
			case self::TYPE_DM:
				return $this->getInDecimalMinuteFormat( $coordinate );
			default:
				throw new InvalidArgumentException( 'Invalid coordinate format specified in the options' );
		}
	}

	private function getInFloatFormat( $coordinate ) {
		return (string)$coordinate;
	}

	private function getInDegreeMinuteSecondFormat( $coordinate ) {
		$options = $this->options;

		$isNegative = $coordinate < 0;
		$coordinate = abs( $coordinate );

		$degrees = floor( $coordinate );
		$minutes = ( $coordinate - $degrees ) * 60;
		$seconds = ( $minutes - floor( $minutes ) ) * 60;

		$minutes = floor( $minutes );
		$seconds = $this->getRoundedNumber( $seconds );

		$spacing = $this->getSpacing( self::OPT_SPACE_COORDPARTS );

		$result = $degrees . $options->getOption( self::OPT_DEGREE_SYMBOL )
			. $spacing . $minutes . $options->getOption( self::OPT_MINUTE_SYMBOL )
			. $spacing . $seconds . $options->getOption( self::OPT_SECOND_SYMBOL );

		if ( $isNegative ) {
			$result = '-' . $result;
		}

		return $result;
	}

	private function getInDecimalDegreeFormat( $coordinate ) {
		return $coordinate . $this->options->getOption( self::OPT_DEGREE_SYMBOL );
	}

	private function getInDecimalMinuteFormat( $coordinate ) {
		$options = $this->options;

		$isNegative = $coordinate < 0;
		$coordinate = abs( $coordinate );
		$degrees = floor( $coordinate );

		$minutes = $this->getRoundedNumber( ( $coordinate - $degrees ) * 60 );

		return sprintf(
			"%s%d%s" . $this->getSpacing( self::OPT_SPACE_COORDPARTS ) . "%s%s",
			$isNegative ? '-' : '',
			$degrees,
			$options->getOption( self::OPT_DEGREE_SYMBOL ),
			$minutes,
			$options->getOption( self::OPT_MINUTE_SYMBOL )
		);
	}

	private function getRoundedNumber( $number ) {
		return round(
			$number,
			$this->options->getOption( self::OPT_PRECISION )
		);
	}

}
