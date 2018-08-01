<?php

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\DmsCoordinateParser;
use DataValues\Geo\Values\LatLongValue;

/**
 * @covers \DataValues\Geo\Parsers\DmsCoordinateParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DmsCoordinateParserTest extends ParserTestBase {

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return DmsCoordinateParser
	 */
	protected function getInstance() {
		return new DmsCoordinateParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = [];

		// TODO: test with different parser options

		$valid = [
			// Whitespace
			"1°0'0\"N 1°0'0\"E\n" => [ 1, 1 ],
			' 1°0\'0"N 1°0\'0"E ' => [ 1, 1 ],

			'55° 45\' 20.8296", 37° 37\' 3.4788"' => [ 55.755786, 37.617633 ],
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => [ 55.755786, -37.617633 ],
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => [ -55.755786, -37.617633 ],
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => [ -55.755786, 37.617633 ],
			'55° 0\' 0", 37° 0\' 0"' => [ 55, 37 ],
			'55° 30\' 0", 37° 30\' 0"' => [ 55.5, 37.5 ],
			'55° 0\' 18", 37° 0\' 18"' => [ 55.005, 37.005 ],
			'0° 0\' 0", 0° 0\' 0"' => [ 0, 0 ],
			'0° 0\' 18" N, 0° 0\' 18" E' => [ 0.005, 0.005 ],
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => [ -0.005, -0.005 ],
			'55° 0′ 18″, 37° 0′ 18″' => [ 55.005, 37.005 ],

			// Coordinate strings without separator:
			'55° 45\' 20.8296" 37° 37\' 3.4788"' => [ 55.755786, 37.617633 ],
			'55 ° 45 \' 20.8296 " -37 ° 37 \' 3.4788 "' => [ 55.755786, -37.617633 ],
			'-55 ° 45 \' 20.8296 " -37° 37\' 3.4788"' => [ -55.755786, -37.617633 ],
			'55° 0′ 18″ 37° 0′ 18″' => [ 55.005, 37.005 ],

			// Coordinate string starting with direction character:
			'N 0° 0\' 18", E 0° 0\' 18"' => [ 0.005, 0.005 ],
			'S 0° 0\' 18" E 0° 0\' 18"' => [ -0.005, 0.005 ],
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
