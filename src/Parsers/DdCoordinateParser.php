<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use DataValues\Geo\Values\LatLongValue;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;

/**
 * Parser for geographical coordinates in Decimal Degree notation.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author H. Snater < mediawiki@snater.com >
 */
class DdCoordinateParser extends LatLongParserBase {

	/**
	 * The symbol representing degrees.
	 * @since 0.1
	 */
	public const OPT_DEGREE_SYMBOL = 'degree';

	/**
	 * Delimiters used to split a coordinate string when unable to split by using the separator.
	 * @var string[]
	 */
	protected $defaultDelimiters;

	/**
	 * @param ParserOptions|null $options
	 */
	public function __construct( ParserOptions $options = null ) {
		$options = $options ?: new ParserOptions();
		$options->defaultOption( self::OPT_DEGREE_SYMBOL, '°' );

		parent::__construct( $options );

		$this->defaultDelimiters = [ $this->getOption( self::OPT_DEGREE_SYMBOL ) ];
	}

	/**
	 * @see LatLongParserBase::getParsedCoordinate
	 *
	 * @param string $coordinateSegment
	 *
	 * @return float
	 */
	protected function getParsedCoordinate( string $coordinateSegment ): float {
		$coordinateSegment = $this->resolveDirection( $coordinateSegment );
		return $this->parseCoordinate( $coordinateSegment );
	}

	/**
	 * @see LatLongParserBase::areValidCoordinates
	 *
	 * @param string[] $normalizedCoordinateSegments
	 *
	 * @return bool
	 */
	protected function areValidCoordinates( array $normalizedCoordinateSegments ): bool {
		// TODO: Implement localized decimal separator.
		$baseRegExp = '\d{1,3}(\.\d{1,20})?' . $this->getOption( self::OPT_DEGREE_SYMBOL );

		// Cache whether the coordinates are specified in directional format (a mixture of
		// directional and non-directional is regarded invalid).
		$directional = false;

		$match = false;

		foreach ( $normalizedCoordinateSegments as $i => $segment ) {
			$direction = '('
				. $this->getOption( self::OPT_NORTH_SYMBOL ) . '|'
				. $this->getOption( self::OPT_SOUTH_SYMBOL ) . ')';

			if ( $i === 1 ) {
				$direction = '('
					. $this->getOption( self::OPT_EAST_SYMBOL ) . '|'
					. $this->getOption( self::OPT_WEST_SYMBOL ) . ')';
			}

			$match = preg_match(
				'/^(' . $baseRegExp . $direction . '|' . $direction . $baseRegExp . ')$/i',
				$segment
			);

			if ( $directional ) {
				// Directionality is only set after parsing latitude: When the latitude is
				// is directional, the longitude needs to be as well. Therefore we break here since
				// checking for directionality is the only check needed for longitude.
				break;
			} elseif ( $match ) {
				// Latitude is directional, no need to check for non-directionality.
				$directional = true;
				continue;
			}

			$match = preg_match( '/^(-)?' . $baseRegExp . '$/i', $segment );

			if ( !$match ) {
				// Does neither match directional nor non-directional.
				break;
			}
		}

		return ( 1 === $match );
	}

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

		return parent::parse( $this->getNormalizedNotation( $value ) );
	}

	/**
	 * Returns a normalized version of the coordinate string.
	 *
	 * @param string $coordinates
	 *
	 * @return string
	 */
	protected function getNormalizedNotation( string $coordinates ): string {
		$coordinates = str_replace(
			[ '&#176;', '&deg;' ],
			$this->getOption( self::OPT_DEGREE_SYMBOL ), $coordinates
		);

		$coordinates = $this->removeInvalidChars( $coordinates );

		return $coordinates;
	}

	/**
	 * Returns a string with whitespace, control characters and characters with ASCII values above
	 * 126 removed.
	 *
	 * @see LatLongParserBase::removeInvalidChars
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	protected function removeInvalidChars( string $string ): string {
		return str_replace( ' ', '', parent::removeInvalidChars( $string ) );
	}

	/**
	 * Converts a coordinate segment to float representation.
	 *
	 * @param string $coordinateSegment
	 *
	 * @return float
	 */
	protected function parseCoordinate( string $coordinateSegment ): float {
		return (float)str_replace(
			$this->getOption( self::OPT_DEGREE_SYMBOL ),
			'',
			$coordinateSegment
		);
	}

	/**
	 * @see LatLongParserBase::splitString
	 *
	 * @param string $normalizedCoordinateString
	 *
	 * @return string[]
	 */
	protected function splitString( string $normalizedCoordinateString ): array {
		$separator = $this->getOption( self::OPT_SEPARATOR_SYMBOL );

		$normalizedCoordinateSegments = explode( $separator, $normalizedCoordinateString );

		if ( count( $normalizedCoordinateSegments ) !== 2 ) {
			// Separator not present within the string, trying to figure out the segments by
			// splitting after the first direction character or degree symbol:
			$delimiters = $this->defaultDelimiters;

			$ns = [
				$this->getOption( self::OPT_NORTH_SYMBOL ),
				$this->getOption( self::OPT_SOUTH_SYMBOL )
			];

			$ew = [
				$this->getOption( self::OPT_EAST_SYMBOL ),
				$this->getOption( self::OPT_WEST_SYMBOL )
			];

			foreach ( $ns as $delimiter ) {
				if ( str_starts_with( $normalizedCoordinateString, $delimiter ) ) {
					// String starts with "north" or "west" symbol: Separation needs to be done
					// before the "east" or "west" symbol.
					$delimiters = array_merge( $ew, $delimiters );
					break;
				}
			}

			if ( count( $delimiters ) !== count( $this->defaultDelimiters ) + 2 ) {
				$delimiters = array_merge( $ns, $delimiters );
			}

			foreach ( $delimiters as $delimiter ) {
				$delimiterPos = mb_strpos( $normalizedCoordinateString, $delimiter );
				if ( $delimiterPos !== false ) {
					$adjustPos = ( in_array( $delimiter, $ew ) ) ? 0 : mb_strlen( $delimiter );
					$normalizedCoordinateSegments = [
						mb_substr( $normalizedCoordinateString, 0, $delimiterPos + $adjustPos ),
						mb_substr( $normalizedCoordinateString, $delimiterPos + $adjustPos )
					];
					break;
				}
			}
		}

		return $normalizedCoordinateSegments;
	}

}
