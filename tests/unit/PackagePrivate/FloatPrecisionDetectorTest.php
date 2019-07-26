<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\PackagePrivate;

use DataValues\Geo\PackagePrivate\FloatPrecisionDetector;
use DataValues\Geo\Parsers\DdCoordinateParser;
use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\PackagePrivate\FloatPrecisionDetector
 * @license GPL-2.0-or-later
 */
class FloatPrecisionDetectorTest extends TestCase {

	/**
	 * @dataProvider precisionDetectionProvider
	 */
	public function testPrecisionDetection( LatLongValue $coordinate, float $expectedPrecision ) {
		$this->assertSame(
			$expectedPrecision,
			( new FloatPrecisionDetector() )->detectPrecision( $coordinate )->toFloat()
		);
	}

	public function precisionDetectionProvider() {
		yield [ new LatLongValue( 10, 20 ), 1 ];
		yield [ new LatLongValue( 1, 2 ), 1 ];
		yield [ new LatLongValue( 1.3, 2.4 ), 0.1 ];
		yield [ new LatLongValue( 1.3, 20 ), 0.1 ];
		yield [ new LatLongValue( 10, 2.4 ), 0.1 ];

		yield [ new LatLongValue( 1.35, 2.46 ), 0.01 ];
		yield [ new LatLongValue( 1.357, 2.468 ), 0.001 ];
		yield [ new LatLongValue( 1.3579, 2.468 ), 0.0001 ];
		yield [ new LatLongValue( 1.00001, 2.00001 ), 0.00001 ];
		yield [ new LatLongValue( 1.000001, 2.000001 ), 0.000001 ];
		yield [ new LatLongValue( 1.0000001, 2.0000001 ), 0.0000001 ];
		yield [ new LatLongValue( 1.00000001, 2.00000001 ), 0.00000001 ];

		yield [ new LatLongValue( 1.000000001, 2.000000001 ), 1 ];
		yield [ new LatLongValue( 1.555555555, 2.555555555 ), 0.00000001 ];

		yield [ new LatLongValue( -10, -20 ), 1 ];
		yield [ new LatLongValue( -10, -2.4 ), 0.1 ];
		yield [ new LatLongValue( -1.00000001, -2.00000001 ), 0.00000001 ];
		yield [ new LatLongValue( -1.000000001, -2.000000001 ), 1 ];
		yield [ new LatLongValue( -1.555555555, -2.555555555 ), 0.00000001 ];
	}

	/**
	 * @dataProvider decimalDegreeProvider
	 */
	public function testDecimalDegreePrecisionDetection( string $coordinate, float $expectedPrecision ) {
		$latLong = ( new DdCoordinateParser() )->parse( $coordinate );

		$this->assertSame(
			$expectedPrecision,
			( new FloatPrecisionDetector() )->detectPrecision( $latLong )->toFloat()
		);
	}

	public function decimalDegreeProvider() {
		yield [ '10° 20°', 1 ];
		yield [ '1° 2°', 1 ];
		yield [ '1.3° 2.4°', 0.1 ];
		yield [ '1.3° 20°', 0.1 ];
		yield [ '10° 2.4°', 0.1 ];
		yield [ '1.35° 2.46°', 0.01 ];
		yield [ '1.357° 2.468°', 0.001 ];
		yield [ '1.3579° 2.468°', 0.0001 ];
		yield [ '1.00001° 2.00001°', 0.00001 ];
		yield [ '1.000001° 2.000001°', 0.000001 ];
		yield [ '1.0000001° 2.0000001°', 0.0000001 ];
		yield [ '1.00000001° 2.00000001°', 0.00000001 ];
		yield [ '1.000000001° 2.000000001°', 1 ];
		yield [ '1.555555555° 2.555555555°', 0.00000001 ];
	}

}
