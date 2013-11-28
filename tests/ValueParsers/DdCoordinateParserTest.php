<?php

namespace ValueParsers\Test;

use DataValues\LatLongValue;

/**
 * @covers ValueParsers\DdCoordinateParser
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
class DdCoordinateParserTest extends StringValueParserTest {

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
			'55.7557860° N, 37.6176330° W' => array( 55.7557860, -37.6176330 ),
			'55.7557860°, -37.6176330°' => array( 55.7557860, -37.6176330 ),
			'55° S, 37.6176330 ° W' => array( -55, -37.6176330, 0.000001 ),
			'-55°, -37.6176330 °' => array( -55, -37.6176330, 0.000001 ),
			'5.5°S,37°W ' => array( -5.5, -37, 0.1 ),
			'-5.5°,-37° ' => array( -5.5, -37, 0.1 ),

			// Coordinate strings without separator:
			'55.7557860° N 37.6176330° W' => array( 55.7557860, -37.6176330 ),
			'55.7557860° -37.6176330°' => array( 55.7557860, -37.6176330 ),
			'55° S 37.6176330 ° W' => array( -55, -37.6176330 ),
			'-55° -37.6176330 °' => array( -55, -37.6176330 ),
			'5.5°S 37°W ' => array( -5.5, -37 ),
			'-5.5° -37° ' => array( -5.5, -37 ),

			// Coordinate string starting with direction character:
			'N5.5° W37°' => array( 5.5, -37 ),
			'S 5.5° E 37°' => array( -5.5, 37 ),
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
		return 'ValueParsers\DdCoordinateParser';
	}

}
