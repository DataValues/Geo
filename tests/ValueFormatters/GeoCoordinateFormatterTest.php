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
 * @author Adam Shorland
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

	public function testDirectionalOptionGetsAppliedForDecimalMinutes() {
		$coordinates = array(
			'55° 0\' N, 37° 0\' E' => array( 55, 37 ),
			'55° 30\' N, 37° 30\' W' => array( 55.5, -37.5 ),
			'55° 30\' S, 37° 30\' E' => array( -55.5, 37.5 ),
			'55° 30\' S, 37° 30\' W' => array( -55.5, -37.5 ),
			'0° 0\' N, 0° 0\' E' => array( 0, 0 ),
		);

		$this->assertIsDirectionalFormatMap( $coordinates, GeoCoordinateFormatter::TYPE_DM );
	}

	private function assertIsDirectionalFormatMap( array $coordinates, $format ) {
		foreach ( $coordinates as $expected => $arguments ) {
			$options = new FormatterOptions();
			$options->setOption( GeoCoordinateFormatter::OPT_FORMAT, $format );
			$options->setOption( GeoCoordinateFormatter::OPT_DIRECTIONAL, true );

			$this->assertFormatsCorrectly(
				new LatLongValue( $arguments[0], $arguments[1] ),
				$options,
				$expected
			);
		}
	}

	public function testDirectionalOptionGetsAppliedForFloats() {
		$coordinates = array(
			'55.755786 N, 37.617633 W' => array( 55.755786, -37.617633 ),
			'55.755786 S, 37.617633 E' => array( -55.755786, 37.617633 ),
			'55 S, 37.617633 W' => array( -55, -37.617633 ),
			'5.5 N, 37 E' => array( 5.5, 37 ),
			'0 N, 0 E' => array( 0, 0 ),
		);

		$this->assertIsDirectionalFormatMap( $coordinates, GeoCoordinateFormatter::TYPE_FLOAT );
	}

	private function provideSpacingLevelOptions() {
		return array(
			'none' => array( ),
			'latlong' => array( GeoCoordinateFormatter::OPT_SPACE_LATLONG ),
			'direction' => array( GeoCoordinateFormatter::OPT_SPACE_DIRECTION ),
			'coordparts' => array( GeoCoordinateFormatter::OPT_SPACE_COORDPARTS ),
			'latlong_direction' => array(
				GeoCoordinateFormatter::OPT_SPACE_LATLONG,
				GeoCoordinateFormatter::OPT_SPACE_DIRECTION
			),
			'all' => array(
				GeoCoordinateFormatter::OPT_SPACE_LATLONG,
				GeoCoordinateFormatter::OPT_SPACE_DIRECTION,
				GeoCoordinateFormatter::OPT_SPACE_COORDPARTS,
			),
		);
	}

	public function testSpacingOptionGetsAppliedForDecimalMinutes() {
		$coordinates = array(
			'none' => array(
				'55°0\'N,37°0\'E' => array( 55, 37 ),
				'55°30\'N,37°30\'W' => array( 55.5, -37.5 ),
				'0°0\'N,0°0\'E' => array( 0, 0 ),
			),
			'latlong' => array(
				'55°0\'N, 37°0\'E' => array( 55, 37 ),
				'55°30\'N, 37°30\'W' => array( 55.5, -37.5 ),
				'0°0\'N, 0°0\'E' => array( 0, 0 ),
			),
			'direction' => array(
				'55°0\' N,37°0\' E' => array( 55, 37 ),
				'55°30\' N,37°30\' W' => array( 55.5, -37.5 ),
				'0°0\' N,0°0\' E' => array( 0, 0 ),
			),
			'coordparts' => array(
				'55° 0\'N,37° 0\'E' => array( 55, 37 ),
				'55° 30\'N,37° 30\'W' => array( 55.5, -37.5 ),
				'0° 0\'N,0° 0\'E' => array( 0, 0 ),
			),
			'latlong_direction' => array(
				'55°0\' N, 37°0\' E' => array( 55, 37 ),
				'55°30\' N, 37°30\' W' => array( 55.5, -37.5 ),
				'0°0\' N, 0°0\' E' => array( 0, 0 ),
			),
		);

		$this->assertSpacingCorrect( $coordinates, GeoCoordinateFormatter::TYPE_DM );
	}

	private function assertSpacingCorrect( array $coordSets, $format ) {
		$spacingLevelOptions = $this->provideSpacingLevelOptions();
		foreach( $coordSets as $spacingKey => $coordinates ) {
			foreach ( $coordinates as $expected => $arguments ) {
				$options = new FormatterOptions();
				$options->setOption( GeoCoordinateFormatter::OPT_FORMAT, $format );
				$options->setOption( GeoCoordinateFormatter::OPT_DIRECTIONAL, true );
				$options->setOption( GeoCoordinateFormatter::OPT_SPACING_LEVEL, $spacingLevelOptions[$spacingKey] );

				$this->assertFormatsCorrectly(
					new LatLongValue( $arguments[0], $arguments[1] ),
					$options,
					$expected
				);
			}
		}
	}

	public function testSpacingOptionGetsAppliedForFloats() {
		$coordinates = array(
			'none' => array(
				'55.755786N,37.617633W' => array( 55.755786, -37.617633 ),
				'0N,0E' => array( 0, 0 ),
			),
			'latlong' => array(
				'55.755786N, 37.617633W' => array( 55.755786, -37.617633 ),
				'0N, 0E' => array( 0, 0 ),
			),
			'direction' => array(
				'55.755786 N,37.617633 W' => array( 55.755786, -37.617633 ),
				'0 N,0 E' => array( 0, 0 ),
			),
			'coordparts' => array(
				'55.755786N,37.617633W' => array( 55.755786, -37.617633 ),
				'0N,0E' => array( 0, 0 ),
			),
			'latlong_direction' => array(
				'55.755786 N, 37.617633 W' => array( 55.755786, -37.617633 ),
				'0 N, 0 E' => array( 0, 0 ),
			),
			'all' => array(
				'55.755786 N, 37.617633 W' => array( 55.755786, -37.617633 ),
				'0 N, 0 E' => array( 0, 0 ),
			),
		);

		$this->assertSpacingCorrect( $coordinates, GeoCoordinateFormatter::TYPE_FLOAT );
	}

}
