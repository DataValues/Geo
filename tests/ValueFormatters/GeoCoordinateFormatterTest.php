<?php

namespace ValueFormatters\Test;

use DataValues\LatLongValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\GeoCoordinateFormatter;

/**
 * @covers ValueFormatters\GeoCoordinateFormatter
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GeoCoordinateFormatterTest extends \PHPUnit_Framework_TestCase {

	public function testFloatNotationFormatting() {
		$coordinates = array(
			'55.755786, -37.617633' => array( 55.755786, -37.617633 ),
			'-55.755786, 37.617633' => array( -55.755786, 37.617633 ),
			'-55, -37.617633' => array( -55, -37.617633 ),
			'5.5, 37' => array( 5.5, 37 ),
			'0, 0' => array( 0, 0 ),
		);

		$this->assertIsFormatMap( $coordinates, GeoCoordinateFormatter::TYPE_FLOAT );
	}

	public function testDecimalDegreeNotationFormatting() {
		$coordinates = array(
			'55.755786°, 37.617633°' => array( 55.755786, 37.617633 ),
			'55.755786°, -37.617633°' => array( 55.755786, -37.617633 ),
			'-55°, -37.617633°' => array( -55, -37.617633 ),
			'-5.5°, -37°' => array( -5.5, -37 ),
			'0°, 0°' => array( 0, 0 ),
		);

		$this->assertIsFormatMap( $coordinates, GeoCoordinateFormatter::TYPE_DD );
	}

	public function testDMSNotationFormatting() {
		$coordinates = array(
			'55° 45\' 20.8296", 37° 37\' 3.4788"' => array( 55.755786, 37.617633 ),
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => array( 55.755786, -37.617633 ),
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => array( -55.755786, -37.617633 ),
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => array( -55.755786, 37.617633 ),

			'55° 0\' 0", 37° 0\' 0"' => array( 55, 37 ),
			'55° 30\' 0", 37° 30\' 0"' => array( 55.5, 37.5 ),
			'55° 0\' 18", 37° 0\' 18"' => array( 55.005, 37.005 ),
			'0° 0\' 0", 0° 0\' 0"' => array( 0, 0 ),
			'0° 0\' 18", 0° 0\' 18"' => array( 0.005, 0.005 ),
			'-0° 0\' 18", -0° 0\' 18"' => array( -0.005, -0.005 ),
		);

		$this->assertIsFormatMap( $coordinates, GeoCoordinateFormatter::TYPE_DMS );
	}

	public function testDecimalMinuteNotationFormatting() {
		$coordinates = array(
			'55° 0\', 37° 0\'' => array( 55, 37 ),
			'55° 30\', 37° 30\'' => array( 55.5, 37.5 ),
			'0° 0\', 0° 0\'' => array( 0, 0 ),
			'-55° 30\', -37° 30\'' => array( -55.5, -37.5 ),
			'-0° 0.3\', -0° 0.3\'' => array( -0.005, -0.005 ),
		);

		$this->assertIsFormatMap( $coordinates, GeoCoordinateFormatter::TYPE_DM );
	}

	private function assertIsFormatMap( array $coordinates, $format ) {
		foreach ( $coordinates as $expected => $arguments ) {
			$options = new FormatterOptions();
			$options->setOption( GeoCoordinateFormatter::OPT_FORMAT, $format );

			$this->assertFormatsCorrectly(
				new LatLongValue( $arguments[0], $arguments[1] ),
				$options,
				$expected
			);
		}
	}

	private function assertFormatsCorrectly( LatLongValue $latLong, $options, $expected ) {
		$formatter = new GeoCoordinateFormatter( $options );

		$this->assertEquals(
			$expected,
			$formatter->format( $latLong )
		);
	}

}
