<?php

namespace ValueParsers\Test;

use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use ValueParsers\ParserOptions;

/**
 * @covers ValueParsers\GlobeCoordinateParser
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
class GlobeCoordinateParserTest extends StringValueParserTest {

	/**
	 * @see ValueParserTestBase::validInputProvider
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function validInputProvider() {
		$argLists = array();

		$valid = array(
			// Float
			'55.7557860 N, 37.6176330 W' => array( 55.7557860, -37.6176330, 0.000001 ),
			'55.7557860, -37.6176330' => array( 55.7557860, -37.6176330, 0.000001 ),
			'55 S, 37.6176330 W' => array( -55, -37.6176330, 0.000001 ),
			'-55, -37.6176330' => array( -55, -37.6176330, 0.000001 ),
			'5.5S,37W ' => array( -5.5, -37, 0.1 ),
			'-5.5,-37 ' => array( -5.5, -37, 0.1 ),
			'4,2' => array( 4, 2, 1 ),
			'5.5S 37W ' => array( -5.5, -37, 0.1 ),
			'-5.5 -37 ' => array( -5.5, -37, 0.1 ),
			'4 2' => array( 4, 2, 1 ),
			'S5.5 W37 ' => array( -5.5, -37, 0.1 ),

			// DD
			'55.7557860° N, 37.6176330° W' => array( 55.7557860, -37.6176330, 0.000001 ),
			'55.7557860°, -37.6176330°' => array( 55.7557860, -37.6176330, 0.000001 ),
			'55° S, 37.6176330 ° W' => array( -55, -37.6176330, 0.000001 ),
			'-55°, -37.6176330 °' => array( -55, -37.6176330, 0.000001 ),
			'5.5°S,37°W ' => array( -5.5, -37, 0.1 ),
			'-5.5°,-37° ' => array( -5.5, -37, 0.1 ),
			'-55° -37.6176330 °' => array( -55, -37.6176330, 0.000001 ),
			'5.5°S 37°W ' => array( -5.5, -37, 0.1 ),
			'-5.5 ° -37 ° ' => array( -5.5, -37, 0.1 ),
			'S5.5° W37°' => array( -5.5, -37, 0.1 ),

			// DMS
			'55° 45\' 20.8296", 37° 37\' 3.4788"' => array( 55.755786, 37.617633, 1 / 36000000 ),
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => array( 55.755786, -37.617633, 1 / 36000000 ),
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => array( -55.755786, -37.617633, 1 / 36000000 ),
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => array( -55.755786, 37.617633, 1 / 36000000 ),
			'55° 0\' 0", 37° 0\' 0"' => array( 55, 37, 1 / 3600 ),
			'55° 30\' 0", 37° 30\' 0"' => array( 55.5, 37.5, 1 / 3600 ),
			'55° 0\' 18", 37° 0\' 18"' => array( 55.005, 37.005, 1 / 3600 ),
			'0° 0\' 0", 0° 0\' 0"' => array( 0, 0, 1 / 3600 ),
			'0° 0\' 18" N, 0° 0\' 18" E' => array( 0.005, 0.005, 1 / 3600 ),
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => array( -0.005, -0.005, 1 / 3600 ),
			'0° 0′ 18″ N, 0° 0′ 18″ E' => array( 0.005, 0.005, 1 / 3600 ),
			'0° 0\' 18" N  0° 0\' 18" E' => array( 0.005, 0.005, 1 / 3600 ),
			' 0 ° 0 \' 18 " S   0 °  0 \' 18 "  W ' => array( -0.005, -0.005, 1 / 3600 ),
			'0° 0′ 18″ N 0° 0′ 18″ E' => array( 0.005, 0.005, 1 / 3600 ),
			'N 0° 0\' 18" E 0° 0\' 18"' => array( 0.005, 0.005, 1 / 3600 ),

			// DM
			'55° 0\', 37° 0\'' => array( 55, 37, 1 / 60 ),
			'55° 30\', 37° 30\'' => array( 55.5, 37.5, 1 / 60 ),
			'0° 0\', 0° 0\'' => array( 0, 0, 1 / 60 ),
			'-55° 30\', -37° 30\'' => array( -55.5, -37.5, 1 / 60 ),
			'0° 0.3\' S, 0° 0.3\' W' => array( -0.005, -0.005, 1 / 3600 ),
			'-55° 30′, -37° 30′' => array( -55.5, -37.5, 1 / 60 ),
			'-55 ° 30 \' -37 ° 30 \'' => array( -55.5, -37.5, 1 / 60 ),
			'0° 0.3\' S 0° 0.3\' W' => array( -0.005, -0.005, 1 / 3600 ),
			'-55° 30′ -37° 30′' => array( -55.5, -37.5, 1 / 60 ),
			'S 0° 0.3\' W 0° 0.3\'' => array( -0.005, -0.005, 1 / 3600 ),
		);

		foreach ( $valid as $value => $expected ) {
			$expected = new GlobeCoordinateValue( new LatLongValue( $expected[0], $expected[1] ), $expected[2] );
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
	 * @dataProvider withGlobeOptionProvider
	 */
	public function testWithGlobeOption( $expected, $value, $options = null ) {
		$parser = $this->getInstance();

		if ( $options ) {
			$parserOptions = new ParserOptions( json_decode( $options, true ) );
			$parser->setOptions( $parserOptions );
		}

		$value = $parser->parse( $value );

		$this->assertEquals( $expected, $value );
	}

	public function withGlobeOptionProvider() {
		$data = array();

		$data[] = array(
			new GlobeCoordinateValue(
				new LatLongValue( 55.7557860, -37.6176330 ),
				0.000001,
				'http://www.wikidata.org/entity/Q2'
			),
			'55.7557860° N, 37.6176330° W',
			'{"globe":"http://www.wikidata.org/entity/Q2"}'
		);

		$data[] = array(
			new GlobeCoordinateValue(
				new LatLongValue( 60.5, 260 ),
				0.1,
				'http://www.wikidata.org/entity/Q111'
			),
			'60.5, 260',
			'{"globe":"http://www.wikidata.org/entity/Q111"}'
		);

		$data[] = array(
			new GlobeCoordinateValue(
				new LatLongValue( 40.2, 22.5 ),
				0.1,
				'http://www.wikidata.org/entity/Q2'
			),
			'40.2, 22.5',
		);

		return $data;
	}

	/**
	 * @see ValueParserTestBase::getParserClass
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getParserClass() {
		return 'ValueParsers\GlobeCoordinateParser';
	}

}
