<?php

namespace ValueParsers\Test;

use DataValues\LatLongValue;

/**
 * @covers ValueParsers\DmsCoordinateParser
 *
 * @ingroup ValueParsersTest
 *
 * @group ValueParsers
 * @group DataValueExtensions
 * @group GeoCoordinateParserTest
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DmsCoordinateParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function validInputProvider() {
		$argLists = array();

		// TODO: test with different parser options

		$valid = array(
			'55° 45\' 20.8296", 37° 37\' 3.4788"' => array( 55.755786, 37.617633 ),
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => array( 55.755786, -37.617633 ),
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => array( -55.755786, -37.617633 ),
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => array( -55.755786, 37.617633 ),
			'55° 0\' 0", 37° 0\' 0"' => array( 55, 37 ),
			'55° 30\' 0", 37° 30\' 0"' => array( 55.5, 37.5 ),
			'55° 0\' 18", 37° 0\' 18"' => array( 55.005, 37.005 ),
			'0° 0\' 0", 0° 0\' 0"' => array( 0, 0 ),
			'0° 0\' 18" N, 0° 0\' 18" E' => array( 0.005, 0.005 ),
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => array( -0.005, -0.005 ),
			'55° 0′ 18″, 37° 0′ 18″' => array( 55.005, 37.005 ),

			// Coordinate strings without separator:
			'55° 45\' 20.8296" 37° 37\' 3.4788"' => array( 55.755786, 37.617633 ),
			'55 ° 45 \' 20.8296 " -37 ° 37 \' 3.4788 "' => array( 55.755786, -37.617633 ),
			'-55 ° 45 \' 20.8296 " -37° 37\' 3.4788"' => array( -55.755786, -37.617633 ),
			'55° 0′ 18″ 37° 0′ 18″' => array( 55.005, 37.005 ),

			// Coordinate string starting with direction character:
			'N 0° 0\' 18", E 0° 0\' 18"' => array( 0.005, 0.005 ),
			'S 0° 0\' 18" E 0° 0\' 18"' => array( -0.005, 0.005 ),
		);

		foreach ( $valid as $value => $expected ) {
			$expected = new LatLongValue( $expected[0], $expected[1] );
			$argLists[] = array( (string)$value, $expected );
		}

		return $argLists;
	}

	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			'~=[,,_,,]:3',
			'ohi there',
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}

	/**
	 * @see ValueParserTestBase::getParserClass
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getParserClass() {
		return 'ValueParsers\DmsCoordinateParser';
	}

}
