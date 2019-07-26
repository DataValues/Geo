<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\PackagePrivate;

use DataValues\Geo\PackagePrivate\DmsPrecisionDetector;
use DataValues\Geo\Parsers\DmsCoordinateParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\PackagePrivate\DmsPrecisionDetector
 * @license GPL-2.0-or-later
 */
class DmsPrecisionDetectorTest extends TestCase {

	/**
	 * @dataProvider precisionDetectionProvider
	 */
	public function testPrecisionDetection( string $coordinate, float $expectedPrecision ) {
		$latLong = ( new DmsCoordinateParser() )->parse( $coordinate );

		$this->assertSame(
			$expectedPrecision,
			( new DmsPrecisionDetector() )->detectPrecision( $latLong )->toFloat()
		);
	}

	public function precisionDetectionProvider() {
		yield [ '1°3\'5" 2°4\'6"', 1 / 3600 ];
		yield [ '1°3\'5" 2°0\'0"', 1 / 3600 ];
		yield [ '1°0\'0" 2°4\'6"', 1 / 3600 ];
		yield [ '1°3\'0" 2°4\'0"', 1 / 3600 ];
		yield [ '1°3\'5.7" 2°4\'6.8"', 1 / 36000 ];
		yield [ '1°3\'5.79" 2°4\'6.8"', 1 / 360000 ];
		yield [ '1°3\'5.001" 2°4\'6.001"', 1 / 3600000 ];
		yield [ '1°3\'5.0001" 2°4\'6.0001"', 1 / 36000000 ];
		yield [ '1°3\'5.00001" 2°4\'6.00001"', 1 / 3600 ];
		yield [ '1°3\'5.55555" 2°4\'6.55555"', 1 / 36000000 ];

		yield [ '-1°3\'5" -2°4\'6"', 1 / 3600 ];
		yield [ '-1°3\'5.00001" -2°4\'6.00001"', 1 / 3600 ];
		yield [ '1°3\'5.55555" -2°4\'6.55555"', 1 / 36000000 ];
	}

}
