<?php

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\DmCoordinateParser;
use DataValues\Geo\Values\LatLongValue;

/**
 * @covers \DataValues\Geo\Parsers\DmCoordinateParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DmCoordinateParserTest extends ParserTestBase {

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return DmCoordinateParser
	 */
	protected function getInstance() {
		return new DmCoordinateParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = [];

		// TODO: test with different parser options

		$valid = [
			// Whitespace
			"1°0'N 1°0'E\n" => [ 1, 1 ],
			" 1°0'N 1°0'E " => [ 1, 1 ],

			"55° 0', 37° 0'" => [ 55, 37 ],
			"55° 30', 37° 30'" => [ 55.5, 37.5 ],
			"0° 0', 0° 0'" => [ 0, 0 ],
			"-55° 30', -37° 30'" => [ -55.5, -37.5 ],
			"0° 0.3' S, 0° 0.3' W" => [ -0.005, -0.005 ],
			"55° 30′, 37° 30′" => [ 55.5, 37.5 ],

			// Coordinate strings without separator:
			"55° 0' 37° 0'" => [ 55, 37 ],
			"55 ° 30 ' 37 ° 30 '" => [ 55.5, 37.5 ],
			"0° 0' 0° 0'" => [ 0, 0 ],
			"-55° 30 ' -37 ° 30'" => [ -55.5, -37.5 ],
			"0° 0.3' S 0° 0.3' W" => [ -0.005, -0.005 ],
			"55° 30′ 37° 30′" => [ 55.5, 37.5 ],

			// Coordinate string starting with direction character:
			"S 0° 0.3', W 0° 0.3'" => [ -0.005, -0.005 ],
			"N 0° 0.3' E 0° 0.3'" => [ 0.005, 0.005 ],
		];

		foreach ( $valid as $value => $expected ) {
			$expected = new LatLongValue( $expected[0], $expected[1] );
			$argLists[] = [ (string)$value, $expected ];
		}

		return $argLists;
	}

	/**
	 * @see StringValueParserTest::invalidInputProvider
	 */
	public function invalidInputProvider() {
		return [
			[ null ],
			[ 1 ],
			[ 0.1 ],
			[ '~=[,,_,,]:3' ],
			[ 'ohi there' ],
		];
	}

}
