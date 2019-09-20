<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\DdCoordinateParser;
use DataValues\Geo\Values\LatLongValue;

/**
 * @covers \DataValues\Geo\Parsers\DdCoordinateParser
 * @covers \DataValues\Geo\Parsers\LatLongParserBase
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DdCoordinateParserTest extends ParserTestBase {

	/**
	 * @see ParserTestBase::getInstance
	 *
	 * @return DdCoordinateParser
	 */
	protected function getInstance() {
		return new DdCoordinateParser();
	}

	/**
	 * @see ParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = [];

		// TODO: test with different parser options

		$valid = [
			// Whitespace
			"1°N 1°E\n" => [ 1, 1 ],
			' 1°N 1°E ' => [ 1, 1 ],

			'55.7557860° N, 37.6176330° W' => [ 55.7557860, -37.6176330 ],
			'55.7557860°, -37.6176330°' => [ 55.7557860, -37.6176330 ],
			'55° S, 37.6176330 ° W' => [ -55, -37.6176330, 0.000001 ],
			'-55°, -37.6176330 °' => [ -55, -37.6176330, 0.000001 ],
			'5.5°S,37°W ' => [ -5.5, -37, 0.1 ],
			'-5.5°,-37° ' => [ -5.5, -37, 0.1 ],

			// Coordinate strings without separator:
			'55.7557860° N 37.6176330° W' => [ 55.7557860, -37.6176330 ],
			'55.7557860° -37.6176330°' => [ 55.7557860, -37.6176330 ],
			'55° S 37.6176330 ° W' => [ -55, -37.6176330 ],
			'-55° -37.6176330 °' => [ -55, -37.6176330 ],
			'5.5°S 37°W ' => [ -5.5, -37 ],
			'-5.5° -37° ' => [ -5.5, -37 ],

			// Coordinate string starting with direction character:
			'N5.5° W37°' => [ 5.5, -37 ],
			'S 5.5° E 37°' => [ -5.5, 37 ],
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
