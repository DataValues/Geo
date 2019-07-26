<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\PackagePrivate;

use DataValues\Geo\PackagePrivate\DmPrecisionDetector;
use DataValues\Geo\Parsers\DmCoordinateParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\PackagePrivate\DmPrecisionDetector
 * @license GPL-2.0-or-later
 */
class DmPrecisionDetectorTest extends TestCase {

	/**
	 * @dataProvider precisionDetectionProvider
	 */
	public function testPrecisionDetection( string $coordinate, float $expectedPrecision ) {
		$latLong = ( new DmCoordinateParser() )->parse( $coordinate );

		$this->assertSame(
			$expectedPrecision,
			( new DmPrecisionDetector() )->detectPrecision( $latLong )->toFloat()
		);
	}

	public function precisionDetectionProvider() {
		yield [ '1°3\' 2°4\'', 1 / 60 ];
		yield [ '1°3\' 2°0\'', 1 / 60 ];
		yield [ '1°0\' 2°4\'', 1 / 60 ];
		yield [ '1°3.5\' 2°4.6\'', 1 / 3600 ];
		yield [ '1°3.57\' 2°4.68\'', 1 / 36000 ];
		yield [ '1°3.579\' 2°4.68\'', 1 / 360000 ];
		yield [ '1°3.0001\' 2°4.0001\'', 1 / 3600000 ];
		yield [ '1°3.00001\' 2°4.00001\'', 1 / 36000000 ];
		yield [ '1°3.000001\' 2°4.000001\'', 1 / 36000000 ];
		yield [ '1°3.0000001\' 2°4.0000001\'', 1 / 60 ];
		yield [ '1°3.5555555\' 2°4.5555555\'', 1 / 36000000 ];

		yield [ '-1°0\' 2°4\'', 1 / 60 ];
		yield [ '1°3.5\' -2°4.6\'', 1 / 3600 ];
		yield [ '-1°3.0000001\' -2°4.0000001\'', 1 / 60 ];
		yield [ '-1°3.5555555\' -2°4.5555555\'', 1 / 36000000 ];
	}

}
