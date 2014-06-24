<?php

namespace DataValues\Tests;

use DataValues\LatLongValue;
use DataValues\LatLongValueCalculator;

/**
 * @covers DataValues\LatLongValueCalculator
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Thiemo Mättig
 */
class LatLongValueCalculatorTest extends \PHPUnit_Framework_TestCase {

	const EPSILON = 0.0000000000001;

	/**
	 * @var LatLongValueCalculator
	 */
	private $calculator;

	protected function setUp() {
		$this->calculator = new LatLongValueCalculator();
	}

	public function latLongProvider() {
		// Reminder: Latitude increases from south to north, longitude increases from west to east.
		return array(
			// Yes, there really are nine ways to describe the same point
			array( 0, 0, 0, 0 ),
			array( 0, 0, 0, 360 ),
			array( 0, 0, 0, -360 ),
			array( 0, 0, 360, 0 ),
			array( 0, 0, -360, 0 ),
			array( 0, 0, 180, 180 ),
			array( 0, 0, 180, -180 ),
			array( 0, 0, -180, 180 ),
			array( 0, 0, -180, -180 ),

			// Make sure the methods do not simply return true
			array( 0, 0, 0, 180, false ),
			array( 0, 0, 0, -180, false ),
			array( 0, 0, 180, 0, false ),
			array( 0, 0, 180, 360, false ),

			// Dark side of the moon
			array( 0, 180, 0, 180 ),
			array( 0, 180, 0, -180 ),
			array( 0, 180, 180, 0 ),
			array( 0, 180, -180, 0 ),
			array( 0, 180, -360, -180 ),

			// Half way to the north pole
			array( 45, 0, 45, -360 ),
			array( 45, 0, 135, 180 ),
			array( 45, 0, 135, -180 ),

			// North pole is a special case, drop longitude
			array( 90, 0, 90, -123 ),
			array( 90, 0, -270, 0 ),
			array( 90, 0, -270, 180 ),
			array( 90, 0, -90, 0, false ),
			// Same for the south pole
			array( -90, 0, -90, 123 ),
			array( -90, 0, 270, 0 ),
			array( -90, 0, 270, -180 ),

			// Make sure we cover all cases in the code
			array( 10, 10, 10, 10 ),
			array( 10, 10, 10, -350 ),
			array( 10, 10, -10, -10, false ),
			array( -10, 0, 190, 180 ),
			array( 10, 0, -190, 180 ),
			array( -80, 0, -100, 180 ),
			array( 80, 0, 100, 180 ),

			// Make sure nobody casts to integer
			array( 1.234, -9.3, 178.766, -189.3 ),

			// Avoid loosing precision if not necessary
			array( 0.3, 0.3, 0.3, 0.3 ),

			// IEEE 754
			array( -0.3, -0.3, 359.7, 359.7 ),
			array( 0.3, 0.3, -359.7, -359.7 ),
			array( 0.3, -0.3, 179.7, 179.7 ),
			array( -0.3, 0.3, -179.7, -179.7 ),
		);
	}

	/**
	 * @dataProvider latLongProvider
	 */
	public function testNormalize( $expectedLat, $expectedLong, $lat, $long, $expectedEquality = true ) {
		$expectedLatLong = new LatLongValue( $expectedLat, $expectedLong );
		$latLong = new LatLongValue( $lat, $long );

		$normalized = $this->calculator->normalize( $latLong );
		$equality = $this->equals( $expectedLatLong, $normalized );

		$this->assertEquals( $expectedEquality, $equality );
	}

	private function equals( LatLongValue $a, LatLongValue $b ) {
		return abs( $a->getLatitude()  - $b->getLatitude()  ) < self::EPSILON
			&& abs( $a->getLongitude() - $b->getLongitude() ) < self::EPSILON;
	}

}
