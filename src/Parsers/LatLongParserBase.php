<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use DataValues\Geo\Values\LatLongValue;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;

/**
 * @since 0.1, renamed in 2.0
 *
 * @license GPL-2.0-or-later
 * @author H. Snater < mediawiki@snater.com >
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
abstract class LatLongParserBase implements ValueParser {

	public const FORMAT_NAME = 'geo-coordinate';

	/**
	 * The symbols representing the different directions for usage in directional notation.
	 */
	public const OPT_NORTH_SYMBOL = 'north';
	public const OPT_EAST_SYMBOL = 'east';
	public const OPT_SOUTH_SYMBOL = 'south';
	public const OPT_WEST_SYMBOL = 'west';

	/**
	 * The symbol to use as separator between latitude and longitude.
	 */
	public const OPT_SEPARATOR_SYMBOL = 'separator';

	/**
	 * @var ParserOptions
	 */
	private $options;

	public function __construct( ParserOptions $options = null ) {
		$this->options = $options ?: new ParserOptions();

		$this->options->defaultOption( ValueParser::OPT_LANG, 'en' );

		$this->options->defaultOption( self::OPT_NORTH_SYMBOL, 'N' );
		$this->options->defaultOption( self::OPT_EAST_SYMBOL, 'E' );
		$this->options->defaultOption( self::OPT_SOUTH_SYMBOL, 'S' );
		$this->options->defaultOption( self::OPT_WEST_SYMBOL, 'W' );

		$this->options->defaultOption( self::OPT_SEPARATOR_SYMBOL, ',' );
	}

	/**
	 * Parses a single coordinate segment (either latitude or longitude) and returns it as a float.
	 *
	 * @param string $coordinateSegment
	 *
	 * @throws ParseException
	 * @return float
	 */
	abstract protected function getParsedCoordinate( string $coordinateSegment ): float;

	/**
	 * Returns whether a coordinate split into its two segments is in the representation expected by
	 * this parser.
	 *
	 * @param string[] $normalizedCoordinateSegments
	 *
	 * @return bool
	 */
	abstract protected function areValidCoordinates( array $normalizedCoordinateSegments ): bool;

	/**
	 * @see ValueParser::parse
	 *
	 * @param string $value
	 *
	 * @throws ParseException
	 * @return LatLongValue
	 */
	public function parse( $value ): LatLongValue {
		if ( !is_string( $value ) ) {
			throw new ParseException( 'Not a string' );
		}

		$rawValue = $value;

		$value = $this->removeInvalidChars( $value );

		$normalizedCoordinateSegments = $this->splitString( $value );

		if ( !$this->areValidCoordinates( $normalizedCoordinateSegments ) ) {
			throw new ParseException( 'Not a valid geographical coordinate', $rawValue, static::FORMAT_NAME );
		}

		list( $latitude, $longitude ) = $normalizedCoordinateSegments;

		return new LatLongValue(
			$this->getParsedCoordinate( $latitude ),
			$this->getParsedCoordinate( $longitude )
		);
	}

	/**
	 * Returns a string trimmed and with control characters and characters with ASCII values above
	 * 126 removed. SPACE characters within the string are not removed to retain the option to split
	 * the string using that character.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function removeInvalidChars( string $string ): string {
		$filtered = [];

		foreach ( str_split( $string ) as $character ) {
			$asciiValue = ord( $character );

			if (
				( $asciiValue >= 32 && $asciiValue < 127 )
				|| $asciiValue == 194
				|| $asciiValue == 176
			) {
				$filtered[] = $character;
			}
		}

		return trim( implode( '', $filtered ) );
	}

	/**
	 * Splits a string into two strings using the separator specified in the options. If the string
	 * could not be split using the separator, the method will try to split the string by analyzing
	 * the used symbols. If the string could not be split into two parts, an empty array is
	 * returned.
	 *
	 * @param string $normalizedCoordinateString
	 *
	 * @throws ParseException if unable to split input string into two segments
	 * @return string[]
	 */
	abstract protected function splitString( string $normalizedCoordinateString ): array;

	/**
	 * Turns directional notation (N/E/S/W) of a single coordinate into non-directional notation
	 * (+/-).
	 * This method assumes there are no preceding or tailing spaces.
	 *
	 * @param string $coordinateSegment
	 *
	 * @return string
	 */
	protected function resolveDirection( string $coordinateSegment ): string {
		$n = $this->getOption( self::OPT_NORTH_SYMBOL );
		$e = $this->getOption( self::OPT_EAST_SYMBOL );
		$s = $this->getOption( self::OPT_SOUTH_SYMBOL );
		$w = $this->getOption( self::OPT_WEST_SYMBOL );

		// If there is a direction indicator, remove it, and prepend a minus sign for south and west
		// directions. If there is no direction indicator, the coordinate is already non-directional
		// and no work is required.
		foreach ( [ $n, $e, $s, $w ] as $direction ) {
			// The coordinate segment may either start or end with a direction symbol.
			preg_match(
				'/^(' . $direction . '|)([^' . $direction . ']+)(' . $direction . '|)$/i',
				$coordinateSegment,
				$matches
			);

			if ( $matches[1] !== '' || $matches[3] !== '' ) {
				$coordinateSegment = $matches[2];

				if ( in_array( $direction, [ $s, $w ] ) ) {
					$coordinateSegment = '-' . $coordinateSegment;
				}

				return $coordinateSegment;
			}
		}

		// Coordinate segment does not include a direction symbol.
		return $coordinateSegment;
	}

	protected function getOption( string $optionName ) {
		return $this->options->getOption( $optionName );
	}

}
