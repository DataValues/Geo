<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\PackagePrivate;

use DataValues\Geo\PackagePrivate\Precision;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\PackagePrivate\Precision
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class PrecisionTest extends TestCase {

	public function testConstructorThrowsExceptionForTooHighValues() {
		$this->expectException( \InvalidArgumentException::class );
		new Precision( 360.1 );
	}

	public function testConstructorThrowsExceptionForTooLowValues() {
		$this->expectException( \InvalidArgumentException::class );
		new Precision( -360.1 );
	}

	/**
	 * @dataProvider validDegreeProvider
	 */
	public function testToFloatReturnsConstructorValue( float $validDegree ) {
		$this->assertSame(
			$validDegree,
			( new Precision( $validDegree ) )->toFloat()
		);
	}

	public function validDegreeProvider() {
		yield [ 360 ];
		yield [ -360 ];
		yield [ 0 ];
		yield [ 1 ];
		yield [ -1 ];
		yield [ 0.1 ];
		yield [ 0.000001 ];
		yield [ 0.123456 ];
	}

}
