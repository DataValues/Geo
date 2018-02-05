<?php

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\LatLongParser;
use DataValues\Geo\Values\LatLongValue;

/**
 * @covers DataValues\Geo\Parsers\LatLongParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LatLongParserTest extends ParserTestBase {

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return LatLongParser
	 */
	protected function getInstance() {
		return new LatLongParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = [];

		// TODO: test with different parser options

		$valid = [
			// Whitespace
			"1N 1E\n" => [ 1, 1 ],
			' 1N 1E ' => [ 1, 1 ],

			// Float
			'55.7557860 N, 37.6176330 W' => [ 55.7557860, -37.6176330 ],
			'55.7557860, -37.6176330' => [ 55.7557860, -37.6176330 ],
			'55 S, 37.6176330 W' => [ -55, -37.6176330 ],
			'-55, -37.6176330' => [ -55, -37.6176330 ],
			'5.5S,37W ' => [ -5.5, -37 ],
			'-5.5,-37 ' => [ -5.5, -37 ],
			'4,2' => [ 4, 2, 1 ],
			'5.5S 37W ' => [ -5.5, -37 ],
			'-5.5 -37 ' => [ -5.5, -37 ],
			'4 2' => [ 4, 2, 1 ],
			'S5.5 W37 ' => [ -5.5, -37 ],

			// DD
			'55.7557860° N, 37.6176330° W' => [ 55.7557860, -37.6176330 ],
			'55.7557860°, -37.6176330°' => [ 55.7557860, -37.6176330 ],
			'55° S, 37.6176330 ° W' => [ -55, -37.6176330 ],
			'-55°, -37.6176330 °' => [ -55, -37.6176330 ],
			'5.5°S,37°W ' => [ -5.5, -37 ],
			'-5.5°,-37° ' => [ -5.5, -37 ],
			'-55° -37.6176330 °' => [ -55, -37.6176330 ],
			'5.5°S 37°W ' => [ -5.5, -37 ],
			'-5.5 ° -37 ° ' => [ -5.5, -37 ],
			'S5.5° W37°' => [ -5.5, -37 ],

			// DMS
			'55° 45\' 20.8296", 37° 37\' 3.4788"' => [ 55.755786, 37.6176330000 ],
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => [ 55.755786, -37.6176330000 ],
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => [ -55.755786, -37.6176330000 ],
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => [ -55.755786, 37.6176330000 ],
			'55° 0\' 0", 37° 0\' 0"' => [ 55, 37 ],
			'55° 30\' 0", 37° 30\' 0"' => [ 55.5, 37.5 ],
			'55° 0\' 18", 37° 0\' 18"' => [ 55.005, 37.005 ],
			'0° 0\' 0", 0° 0\' 0"' => [ 0, 0 ],
			'0° 0\' 18" N, 0° 0\' 18" E' => [ 0.005, 0.005 ],
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => [ -0.005, -0.005 ],
			'0° 0′ 18″ N, 0° 0′ 18″ E' => [ 0.005, 0.005 ],
			'0° 0\' 18" N  0° 0\' 18" E' => [ 0.005, 0.005 ],
			' 0 ° 0 \' 18 " S   0 °  0 \' 18 "  W ' => [ -0.005, -0.005 ],
			'0° 0′ 18″ N 0° 0′ 18″ E' => [ 0.005, 0.005 ],
			'N 0° 0\' 18" E 0° 0\' 18"' => [ 0.005, 0.005 ],

			// DM
			'55° 0\', 37° 0\'' => [ 55, 37 ],
			'55° 30\', 37° 30\'' => [ 55.5, 37.5 ],
			'0° 0\', 0° 0\'' => [ 0, 0 ],
			'-55° 30\', -37° 30\'' => [ -55.5, -37.5 ],
			'0° 0.3\' S, 0° 0.3\' W' => [ -0.005, -0.005 ],
			'-55° 30′, -37° 30′' => [ -55.5, -37.5 ],
			'-55 ° 30 \' -37 ° 30 \'' => [ -55.5, -37.5 ],
			'0° 0.3\' S 0° 0.3\' W' => [ -0.005, -0.005 ],
			'-55° 30′ -37° 30′' => [ -55.5, -37.5 ],
			'S 0° 0.3\' W 0° 0.3\'' => [ -0.005, -0.005 ],
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
