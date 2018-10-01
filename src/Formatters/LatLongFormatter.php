<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Formatters;

use DataValues\Geo\Values\LatLongValue;
use InvalidArgumentException;
use ValueFormatters\FormatterOptions;
use ValueFormatters\ValueFormatterBase;

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
 * @since 0.1, renamed in 2.0
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Addshore
 * @author Thiemo Kreuz
 */
class LatLongFormatter extends ValueFormatterBase {

	/**
	 * Output formats for use with the self::OPT_FORMAT option.
	 */
	public const TYPE_FLOAT = 'float';
	public const TYPE_DMS = 'dms';
	public const TYPE_DM = 'dm';
	public const TYPE_DD = 'dd';

	/**
	 * The symbols representing the different directions for usage in directional notation.
	 * @since 0.1
	 */
	public const OPT_NORTH_SYMBOL = 'north';
	public const OPT_EAST_SYMBOL = 'east';
	public const OPT_SOUTH_SYMBOL = 'south';
	public const OPT_WEST_SYMBOL = 'west';

	/**
	 * The symbols representing degrees, minutes and seconds.
	 * @since 0.1
	 */
	public const OPT_DEGREE_SYMBOL = 'degree';
	public const OPT_MINUTE_SYMBOL = 'minute';
	public const OPT_SECOND_SYMBOL = 'second';

	/**
	 * Flags for use with the self::OPT_SPACING_LEVEL option.
	 */
	public const OPT_SPACE_LATLONG = 'latlong';
	public const OPT_SPACE_DIRECTION = 'direction';
	public const OPT_SPACE_COORDPARTS = 'coordparts';

	/**
	 * Option specifying the output format (also referred to as output type). Must be one of the
	 * self::TYPE_… constants.
	 */
	public const OPT_FORMAT = 'geoformat';

	/**
	 * Boolean option specifying if negative coordinates should have minus signs, e.g. "-1°, -2°"
	 * (false) or cardinal directions, e.g. "1° S, 2° W" (true). Default is false.
	 */
	public const OPT_DIRECTIONAL = 'directional';

	/**
	 * Option for the separator character between latitude and longitude. Defaults to a comma.
	 */
	public const OPT_SEPARATOR_SYMBOL = 'separator';

	/**
	 * Option specifying the amount and position of space characters in the output. Must be an array
	 * containing zero or more of the self::OPT_SPACE_… flags.
	 */
	public const OPT_SPACING_LEVEL = 'spacing';

	/**
	 * Option specifying the precision in fractional degrees. Must be a number or numeric string.
	 */
	public const OPT_PRECISION = 'precision';

	private const DEFAULT_PRECISION = 1 / 3600;

	public function __construct( FormatterOptions $options = null ) {
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
		$this->defaultOption( self::OPT_SPACING_LEVEL, [
			self::OPT_SPACE_LATLONG,
			self::OPT_SPACE_DIRECTION,
			self::OPT_SPACE_COORDPARTS,
		] );
		$this->defaultOption( self::OPT_PRECISION, 0 );
	}

	/**
	 * @see ValueFormatter::format
	 *
	 * Calls formatLatLongValue() using OPT_PRECISION for the $precision parameter.
	 *
	 * @param LatLongValue $value
	 *
	 * @return string Plain text
	 * @throws InvalidArgumentException
	 */
	public function format( $value ): string {
		if ( !( $value instanceof LatLongValue ) ) {
			throw new InvalidArgumentException( 'Data value type mismatch. Expected a LatLongValue.' );
		}

		return $this->formatLatLongValue( $value, $this->getPrecisionFromOptions() );
	}

	private function getPrecisionFromOptions(): float {
		$precision = $this->options->getOption( self::OPT_PRECISION );

		if ( is_string( $precision ) ) {
			return (float)$precision;
		}

		if ( is_float( $precision ) || is_int( $precision ) ) {
			return $precision;
		}

		return self::DEFAULT_PRECISION;
	}

	/**
	 * Formats a LatLongValue with the desired precision.
	 *
	 * @since 0.5
	 *
	 * @param LatLongValue $value
	 * @param float|int $precision The desired precision, given as fractional degrees.
	 *
	 * @return string Plain text
	 * @throws InvalidArgumentException
	 */
	public function formatLatLongValue( LatLongValue $value, ?float $precision ): string {
		if ( $precision <= 0 || !is_finite( $precision ) ) {
			$precision = self::DEFAULT_PRECISION;
		}

		$formatted = implode(
			$this->getOption( self::OPT_SEPARATOR_SYMBOL ) . $this->getSpacing( self::OPT_SPACE_LATLONG ),
			[
				$this->formatLatitude( $value->getLatitude(), $precision ),
				$this->formatLongitude( $value->getLongitude(), $precision )
			]
		);

		return $formatted;
	}

	/**
	 * @param string $spacingLevel One of the self::OPT_SPACE_… constants
	 *
	 * @return string
	 */
	private function getSpacing( string $spacingLevel ): string {
		if ( in_array( $spacingLevel, $this->getOption( self::OPT_SPACING_LEVEL ) ) ) {
			return ' ';
		}
		return '';
	}

	private function formatLatitude( float $latitude, float $precision ): string {
		return $this->makeDirectionalIfNeeded(
			$this->formatCoordinate( $latitude, $precision ),
			$this->options->getOption( self::OPT_NORTH_SYMBOL ),
			$this->options->getOption( self::OPT_SOUTH_SYMBOL )
		);
	}

	private function formatLongitude( float $longitude, float $precision ): string {
		return $this->makeDirectionalIfNeeded(
			$this->formatCoordinate( $longitude, $precision ),
			$this->options->getOption( self::OPT_EAST_SYMBOL ),
			$this->options->getOption( self::OPT_WEST_SYMBOL )
		);
	}

	private function makeDirectionalIfNeeded( string $coordinate, string $positiveSymbol,
		string $negativeSymbol ): string {

		if ( $this->options->getOption( self::OPT_DIRECTIONAL ) ) {
			return $this->makeDirectional( $coordinate, $positiveSymbol, $negativeSymbol );
		}

		return $coordinate;
	}

	private function makeDirectional( string $coordinate, string $positiveSymbol,
		string $negativeSymbol ): string {

		$isNegative = substr( $coordinate, 0, 1 ) === '-';

		if ( $isNegative ) {
			$coordinate = substr( $coordinate, 1 );
		}

		$symbol = $isNegative ? $negativeSymbol : $positiveSymbol;

		return $coordinate . $this->getSpacing( self::OPT_SPACE_DIRECTION ) . $symbol;
	}

	private function formatCoordinate( float $degrees, float $precision ): string {
		// Remove insignificant detail
		$degrees = $this->roundDegrees( $degrees, $precision );
		$format = $this->getOption( self::OPT_FORMAT );

		if ( $format === self::TYPE_FLOAT ) {
			return $this->getInFloatFormat( $degrees );
		}

		if ( $format !== self::TYPE_DD ) {
			if ( $precision >= 1 - 1 / 60 && $precision < 1 ) {
				$precision = 1;
			} elseif ( $precision >= 1 / 60 - 1 / 3600 && $precision < 1 / 60 ) {
				$precision = 1 / 60;
			}
		}

		if ( $format === self::TYPE_DD || $precision >= 1 ) {
			return $this->getInDecimalDegreeFormat( $degrees, $precision );
		}
		if ( $format === self::TYPE_DM || $precision >= 1 / 60 ) {
			return $this->getInDecimalMinuteFormat( $degrees, $precision );
		}
		if ( $format === self::TYPE_DMS ) {
			return $this->getInDegreeMinuteSecondFormat( $degrees, $precision );
		}

		throw new InvalidArgumentException( 'Invalid coordinate format specified in the options' );
	}

	private function roundDegrees( float $degrees, float $precision ): float {
		$sign = $degrees > 0 ? 1 : -1;
		$reduced = round( abs( $degrees ) / $precision );
		$expanded = $reduced * $precision;

		return $sign * $expanded;
	}

	private function getInFloatFormat( float $floatDegrees ): string {
		$stringDegrees = (string)$floatDegrees;

		if ( $stringDegrees === '-0' ) {
			return '0';
		}

		return $stringDegrees;
	}

	private function getInDecimalDegreeFormat( float $floatDegrees, float $precision ): string {
		$degreeDigits = $this->getSignificantDigits( 1, $precision );
		$stringDegrees = $this->formatNumber( $floatDegrees, $degreeDigits );

		return $stringDegrees . $this->options->getOption( self::OPT_DEGREE_SYMBOL );
	}

	private function getInDegreeMinuteSecondFormat( float $floatDegrees, float $precision ): string {
		$isNegative = $floatDegrees < 0;
		$secondDigits = $this->getSignificantDigits( 3600, $precision );

		$seconds = round( abs( $floatDegrees ) * 3600, max( 0, $secondDigits ) );
		$minutes = (int)( $seconds / 60 );
		$degrees = (int)( $minutes / 60 );

		$seconds -= $minutes * 60;
		$minutes -= $degrees * 60;

		$space = $this->getSpacing( self::OPT_SPACE_COORDPARTS );
		$result = $this->formatNumber( $degrees )
			. $this->options->getOption( self::OPT_DEGREE_SYMBOL )
			. $space
			. $this->formatNumber( $minutes )
			. $this->options->getOption( self::OPT_MINUTE_SYMBOL )
			. $space
			. $this->formatNumber( $seconds, $secondDigits )
			. $this->options->getOption( self::OPT_SECOND_SYMBOL );

		if ( $isNegative && ( $degrees + $minutes + $seconds ) > 0 ) {
			$result = '-' . $result;
		}

		return $result;
	}

	private function getInDecimalMinuteFormat( float $floatDegrees, float $precision ): string {
		$isNegative = $floatDegrees < 0;
		$minuteDigits = $this->getSignificantDigits( 60, $precision );

		$minutes = round( abs( $floatDegrees ) * 60, max( 0, $minuteDigits ) );
		$degrees = (int)( $minutes / 60 );

		$minutes -= $degrees * 60;

		$space = $this->getSpacing( self::OPT_SPACE_COORDPARTS );
		$result = $this->formatNumber( $degrees )
			. $this->options->getOption( self::OPT_DEGREE_SYMBOL )
			. $space
			. $this->formatNumber( $minutes, $minuteDigits )
			. $this->options->getOption( self::OPT_MINUTE_SYMBOL );

		if ( $isNegative && ( $degrees + $minutes ) > 0 ) {
			$result = '-' . $result;
		}

		return $result;
	}

	/**
	 * @param float|int $unitsPerDegree The number of target units per degree
	 * (60 for minutes, 3600 for seconds)
	 * @param float|int $degreePrecision
	 *
	 * @return int The number of digits to show after the decimal point
	 * (resp. before, if the result is negative).
	 */
	private function getSignificantDigits( float $unitsPerDegree, float $degreePrecision ): int {
		return (int)ceil( -log10( $unitsPerDegree * $degreePrecision ) );
	}

	/**
	 * @param float $number
	 * @param int $digits The number of digits after the decimal point.
	 *
	 * @return string
	 */
	private function formatNumber( float $number, int $digits = 0 ): string {
		// TODO: use NumberLocalizer
		return sprintf( '%.' . ( $digits > 0 ? $digits : 0 ) . 'F', $number );
	}

}
