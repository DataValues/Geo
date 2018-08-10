<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use ValueParsers\ParseException;
use ValueParsers\ParserOptions;

/**
 * Parser for geographical coordinates in Degree Minute Second notation.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author H. Snater < mediawiki@snater.com >
 */
class DmsCoordinateParser extends DmCoordinateParser {

	public const FORMAT_NAME = 'dms-coordinate';

	/**
	 * The symbol representing seconds.
	 * @since 0.1
	 */
	public const OPT_SECOND_SYMBOL = 'second';

	/**
	 * @param ParserOptions|null $options
	 */
	public function __construct( ParserOptions $options = null ) {
		$options = $options ?: new ParserOptions();
		$options->defaultOption( self::OPT_SECOND_SYMBOL, '"' );

		parent::__construct( $options );

		$this->defaultDelimiters = [ $this->getOption( self::OPT_SECOND_SYMBOL ) ];
	}

	/**
	 * @see LatLongParserBase::areValidCoordinates
	 *
	 * @param string[] $normalizedCoordinateSegments
	 *
	 * @return bool
	 */
	protected function areValidCoordinates( array $normalizedCoordinateSegments ): bool {
		// At least one coordinate segment needs to have seconds specified (which additionally
		// requires minutes to be specified).
		$regExpLoose = '(\d{1,3}'
			. preg_quote( $this->getOption( self::OPT_DEGREE_SYMBOL ) )
			. ')(\d{1,2}'
			. preg_quote( $this->getOption( self::OPT_MINUTE_SYMBOL ) )
			. ')?((\d{1,2}'
			. preg_quote( $this->getOption( self::OPT_SECOND_SYMBOL ) )
			// TODO: Implement localized decimal separator.
			. ')?|(\d{1,2}\.\d{1,20}'
			. preg_quote( $this->getOption( self::OPT_SECOND_SYMBOL ) )
			. ')?)';
		$regExpStrict = str_replace( '?', '', $regExpLoose );

		// Cache whether seconds have been detected within the coordinate:
		$detectedSecond = false;

		// Cache whether the coordinates are specified in directional format (a mixture of
		// directional and non-directional is regarded invalid).
		$directional = false;

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
				'/^(' . $regExpStrict . $direction . '|' . $direction . $regExpStrict . ')$/i',
				$segment
			);

			if ( $match ) {
				$detectedSecond = true;
			} else {
				$match = preg_match(
					'/^(' . $regExpLoose . $direction . '|' . $direction . $regExpLoose . ')$/i',
					$segment
				);
			}

			if ( $match ) {
				$directional = true;
			} elseif ( !$directional ) {
				$match = preg_match( '/^(-)?' . $regExpStrict . '$/i', $segment );

				if ( $match ) {
					$detectedSecond = true;
				} else {
					$match = preg_match( '/^(-)?' . $regExpLoose . '$/i', $segment );
				}
			}

			if ( !$match ) {
				return false;
			}
		}

		return $detectedSecond;
	}

	/**
	 * @see DdCoordinateParser::getNormalizedNotation
	 *
	 * @param string $coordinates
	 *
	 * @return string
	 */
	protected function getNormalizedNotation( string $coordinates ): string {
		$second = $this->getOption( self::OPT_SECOND_SYMBOL );
		$minute = $this->getOption( self::OPT_MINUTE_SYMBOL );

		$coordinates = str_replace(
			[ '&#8243;', '&Prime;', $minute . $minute, '´´', '′′', '″' ],
			$second,
			$coordinates
		);
		$coordinates = str_replace( [ '&acute;', '&#180;' ], $second, $coordinates );

		$coordinates = parent::getNormalizedNotation( $coordinates );

		$coordinates = $this->removeInvalidChars( $coordinates );

		return $coordinates;
	}

	/**
	 * @see DdCoordinateParser::parseCoordinate
	 *
	 * @param string $coordinateSegment
	 *
	 * @return float
	 */
	protected function parseCoordinate( string $coordinateSegment ): float {
		$isNegative = mb_substr( $coordinateSegment, 0, 1 ) === '-';

		if ( $isNegative ) {
			$coordinateSegment = mb_substr( $coordinateSegment, 1 );
		}

		$degreeSymbol = $this->getOption( self::OPT_DEGREE_SYMBOL );
		$degreePosition = mb_strpos( $coordinateSegment, $degreeSymbol );

		if ( $degreePosition === false ) {
			throw new ParseException(
				'Did not find degree symbol (' . $degreeSymbol . ')',
				$coordinateSegment,
				self::FORMAT_NAME
			);
		}

		$degrees = (float)mb_substr( $coordinateSegment, 0, $degreePosition );

		$minutePosition = mb_strpos( $coordinateSegment, $this->getOption( self::OPT_MINUTE_SYMBOL ) );

		if ( $minutePosition === false ) {
			$minutes = 0;
		} else {
			$degSignLength = mb_strlen( $this->getOption( self::OPT_DEGREE_SYMBOL ) );
			$minuteLength = $minutePosition - $degreePosition - $degSignLength;
			$minutes = (float)mb_substr( $coordinateSegment, $degreePosition + $degSignLength, $minuteLength );
		}

		$secondPosition = mb_strpos( $coordinateSegment, $this->getOption( self::OPT_SECOND_SYMBOL ) );

		if ( $secondPosition === false ) {
			$seconds = 0;
		} else {
			$seconds = (float)mb_substr(
				$coordinateSegment,
				( $minutePosition === false ? $degreePosition : $minutePosition ) + 1,
				-1
			);
		}

		$coordinateSegment = $degrees + ( $minutes + $seconds / 60 ) / 60;

		if ( $isNegative ) {
			$coordinateSegment *= -1;
		}

		return (float)$coordinateSegment;
	}

}
