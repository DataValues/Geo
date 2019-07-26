<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Parsers;

use DataValues\Geo\Values\LatLongValue;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;
use ValueParsers\ValueParser;

/**
 * ValueParser that parses the string representation of a geographical coordinate.
 *
 * The resulting objects are of type @see LatLongValue.
 *
 * Supports the following notations:
 * - Degree minute second
 * - Decimal degrees
 * - Decimal minutes
 * - Float
 *
 * And for all these notations direction can be indicated either with
 * + and - or with N/E/S/W, the later depending on the set options.
 *
 * The delimiter between latitude and longitude can be set in the options.
 * So can the symbols used for degrees, minutes and seconds.
 *
 * Some code in this class has been borrowed from the
 * MapsCoordinateParser class of the Maps extension for MediaWiki.
 *
 * @since 0.1, name changed in 2.0
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LatLongParser implements ValueParser {

	public const TYPE_FLOAT = 'float';
	public const TYPE_DMS = 'dms';
	public const TYPE_DM = 'dm';
	public const TYPE_DD = 'dd';

	/**
	 * The symbols representing the different directions for usage in directional notation.
	 */
	public const OPT_NORTH_SYMBOL = 'north';
	public const OPT_EAST_SYMBOL = 'east';
	public const OPT_SOUTH_SYMBOL = 'south';
	public const OPT_WEST_SYMBOL = 'west';

	/**
	 * The symbols representing degrees, minutes and seconds.
	 */
	public const OPT_DEGREE_SYMBOL = 'degree';
	public const OPT_MINUTE_SYMBOL = 'minute';
	public const OPT_SECOND_SYMBOL = 'second';

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
		foreach ( $this->getParsers() as $parser ) {
			try {
				return $parser->parse( $value );
			} catch ( ParseException $ex ) {
				continue;
			}
		}

		throw new ParseException( 'The format of the coordinate could not be determined. Parsing failed.' );
	}

	/**
	 * @return LatLongParserBase[]
	 */
	private function getParsers(): iterable {
		yield new FloatCoordinateParser( $this->options );
		yield new DmsCoordinateParser( $this->options );
		yield new DmCoordinateParser( $this->options );
		yield new DdCoordinateParser( $this->options );
	}

}
