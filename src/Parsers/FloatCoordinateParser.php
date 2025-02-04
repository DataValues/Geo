<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use ValueParsers\ParseException;

/**
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author H. Snater < mediawiki@snater.com >
 */
class FloatCoordinateParser extends LatLongParserBase {

	public const FORMAT_NAME = 'float-coordinate';

	/**
	 * @see LatLongParserBase::getParsedCoordinate
	 *
	 * @param string $coordinateSegment
	 *
	 * @return float
	 */
	protected function getParsedCoordinate( string $coordinateSegment ): float {
		return (float)$this->resolveDirection( str_replace( ' ', '', $coordinateSegment ) );
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
		$baseRegExp = '\d{1,3}(\.\d{1,20})?';

		$match = false;

		foreach ( $normalizedCoordinateSegments as $i => $segment ) {
			$segment = str_replace( ' ', '', $segment );

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

			if ( $match ) {
				continue;
			}

			$match = preg_match( '/^(-)?' . $baseRegExp . '$/i', $segment );

			if ( !$match ) {
				// Does neither match directional nor non-directional.
				break;
			}
		}

		return ( $match === 1 );
	}

	/**
	 * @see LatLongParserBase::splitString
	 *
	 * @param string $normalizedCoordinateString
	 *
	 * @throws ParseException if unable to split input string into two segments
	 * @return string[]
	 */
	protected function splitString( string $normalizedCoordinateString ): array {
		$separator = $this->getOption( self::OPT_SEPARATOR_SYMBOL );

		$normalizedCoordinateSegments = explode( $separator, $normalizedCoordinateString );

		if ( count( $normalizedCoordinateSegments ) !== 2 ) {
			// Separator not present within the string, trying to figure out the segments by
			// splitting at the the first SPACE after the first direction character or digit:
			$numberRegEx = '-?\d{1,3}(\.\d{1,20})?';

			$ns = '('
				. $this->getOption( self::OPT_NORTH_SYMBOL ) . '|'
				. $this->getOption( self::OPT_SOUTH_SYMBOL ) . ')';

			$latitudeRegEx = '(' . $ns . '\s*)?' . $numberRegEx . '(\s*' . $ns . ')?';

			$ew = '('
				. $this->getOption( self::OPT_EAST_SYMBOL ) . '|'
				. $this->getOption( self::OPT_WEST_SYMBOL ) . ')';

			$longitudeRegEx = '(' . $ew . '\s*)?' . $numberRegEx . '(\s*' . $ew . ')?';

			$match = preg_match(
				'/^(' . $latitudeRegEx . ') (' . $longitudeRegEx . ')$/i',
				$normalizedCoordinateString,
				$matches
			);

			if ( $match ) {
				$normalizedCoordinateSegments = [ $matches[1], $matches[7] ];
			}
		}

		if ( count( $normalizedCoordinateSegments ) !== 2 ) {
			throw new ParseException(
				'Unable to split input into two coordinate segments',
				$normalizedCoordinateString,
				self::FORMAT_NAME
			);
		}

		return $normalizedCoordinateSegments;
	}

}
