<?php

namespace ValueFormatters\Test;

use DataValues\GlobeCoordinateValue;
use DataValues\LatLongValue;
use ValueFormatters\GeoCoordinateFormatter;

/**
 * @covers ValueFormatters\GlobeCoordinateFormatter
 *
 * @ingroup ValueFormattersTest
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GlobeCoordinateFormatterTest extends ValueFormatterTestBase {

	/**
	 * @see ValueFormatterTestBase::validProvider
	 *
	 * @since 0.1
	 *
	 * @return array
	 */
	public function validProvider() {
		$floats = array(
			'55.755786, -37.617633' => array( 55.755786, -37.617633 ),
			'-55.755786, 37.617633' => array( -55.755786, 37.617633 ),
			'-55, -37.617633' => array( -55, -37.617633 ),
			'5.5, 37' => array( 5.5, 37 ),
			'0, 0' => array( 0, 0 ),
		);

		$decimalDegrees = array(
			'55.755786°, 37.617633°' => array( 55.755786, 37.617633 ),
			'55.755786°, -37.617633°' => array( 55.755786, -37.617633 ),
			'-55°, -37.617633°' => array( -55, -37.617633 ),
			'-5.5°, -37°' => array( -5.5, -37 ),
			'0°, 0°' => array( 0, 0 ),
		);

		$dmsCoordinates = array(
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

		$dmCoordinates = array(
			'55° 0\', 37° 0\'' => array( 55, 37 ),
			'55° 30\', 37° 30\'' => array( 55.5, 37.5 ),
			'0° 0\', 0° 0\'' => array( 0, 0 ),
			'-55° 30\', -37° 30\'' => array( -55.5, -37.5 ),
			'-0° 0.3\', -0° 0.3\'' => array( -0.005, -0.005 ),
		);

		$argLists = array();

		$tests = array(
			GeoCoordinateFormatter::TYPE_FLOAT => $floats,
			GeoCoordinateFormatter::TYPE_DD => $decimalDegrees,
			GeoCoordinateFormatter::TYPE_DMS => $dmsCoordinates,
			GeoCoordinateFormatter::TYPE_DM => $dmCoordinates,
		);

		foreach ( $tests as $format => $coords ) {
			foreach ( $coords as $expectedOutput => $arguments ) {
				$options = new \ValueFormatters\FormatterOptions();
				$options->setOption( GeoCoordinateFormatter::OPT_FORMAT, $format );

				$input = new GlobeCoordinateValue(
					new LatLongValue( $arguments[0], $arguments[1] ),
					1
				);

				$argLists[] = array( $input, $expectedOutput, $options );
			}
		}

		return $argLists;
	}

	/**
	 * @see ValueFormatterTestBase::getFormatterClass
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	protected function getFormatterClass() {
		return 'ValueFormatters\GlobeCoordinateFormatter';
	}

}
