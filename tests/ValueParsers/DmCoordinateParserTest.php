<?php

namespace ValueParsers\Test;

use DataValues\LatLongValue;

/**
 * @covers ValueParsers\DmCoordinateParser
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
class DmCoordinateParserTest extends StringValueParserTest {

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
			"55° 0', 37° 0'" => array( 55, 37 ),
			"55° 30', 37° 30'" => array( 55.5, 37.5 ),
			"0° 0', 0° 0'" => array( 0, 0 ),
			"-55° 30', -37° 30'" => array( -55.5, -37.5 ),
			"0° 0.3' S, 0° 0.3' W" => array( -0.005, -0.005 ),
			"55° 30′, 37° 30′" => array( 55.5, 37.5 ),

			// Coordinate strings without separator:
			"55° 0' 37° 0'" => array( 55, 37 ),
			"55 ° 30 ' 37 ° 30 '" => array( 55.5, 37.5 ),
			"0° 0' 0° 0'" => array( 0, 0 ),
			"-55° 30 ' -37 ° 30'" => array( -55.5, -37.5 ),
			"0° 0.3' S 0° 0.3' W" => array( -0.005, -0.005 ),
			"55° 30′ 37° 30′" => array( 55.5, 37.5 ),

			// Coordinate string starting with direction character:
			"S 0° 0.3', W 0° 0.3'" => array( -0.005, -0.005 ),
			"N 0° 0.3' E 0° 0.3'" => array( 0.005, 0.005 ),
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
		return 'ValueParsers\DmCoordinateParser';
	}

}
