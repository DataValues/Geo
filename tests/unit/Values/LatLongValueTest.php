<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Values;

use DataValues\Geo\Values\LatLongValue;

/**
 * @covers \DataValues\Geo\Values\LatLongValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LatLongValueTest extends DataValueTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return LatLongValue::class;
	}

	public function validConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ 4.2, 4.2 ];
		$argLists[] = [ 4.2, 42 ];
		$argLists[] = [ 42, 4.2 ];
		$argLists[] = [ 42, 42 ];
		$argLists[] = [ -4.2, -4.2 ];
		$argLists[] = [ 4.2, -42 ];
		$argLists[] = [ -42, 4.2 ];
		$argLists[] = [ 360, -360 ];
		$argLists[] = [ 48.269, -225.99 ];
		$argLists[] = [ 0, 0 ];

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ -361, 0 ];
		$argLists[] = [ -999, 1 ];
		$argLists[] = [ 360.001, 2 ];
		$argLists[] = [ 3, 361 ];
		$argLists[] = [ 4, -1337 ];

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 * @param LatLongValue $latLongValue
	 * @param array $arguments
	 */
	public function testGetLatitude( LatLongValue $latLongValue, array $arguments ) {
		$actual = $latLongValue->getLatitude();

		$this->assertInternalType( 'float', $actual );
		$this->assertSame( (float)$arguments[0], $actual );
	}

	/**
	 * @dataProvider instanceProvider
	 * @param LatLongValue $latLongValue
	 * @param array $arguments
	 */
	public function testGetLongitude( LatLongValue $latLongValue, array $arguments ) {
		$actual = $latLongValue->getLongitude();

		$this->assertInternalType( 'float', $actual );
		$this->assertSame( (float)$arguments[1], $actual );
	}

	/**
	 * @dataProvider invalidCoordinatesProvider
	 */
	public function testConstructorThrowsExceptionWhenParametersAreInvalid( float $latitude, float $longitude ) {
		$this->expectException( \InvalidArgumentException::class );
		new LatLongValue( $latitude, $longitude );
	}

	public function invalidCoordinatesProvider() {
		yield 'latitude too small' => [ -361, 0 ];
		yield 'latitude too big' => [ 361, 0 ];
		yield 'longitude too big' => [ 0, 361 ];
		yield 'longitude too small' => [ 0, -361 ];
	}

	public function testCopyProducesIdenticalObject() {
		$latLong = new LatLongValue( 1, 2 );
		$this->assertEquals(
			$latLong,
			$latLong->getCopy()
		);
	}

	public function testCopyProducesObjectWithDifferentIdentity() {
		$latLong = new LatLongValue( 1, 2 );
		$this->assertNotSame(
			$latLong,
			$latLong->getCopy()
		);
	}

	public function testGetHashProducesMd5() {
		$this->assertSame( '7a6ba7398547fbc6bc26fb3d77b93897', ( new LatLongValue( 0, 0 ) )->getHash() );
		$this->assertSame( 'b8af9bef608c55ae8c1610daa89e937f', ( new LatLongValue( 1, 2 ) )->getHash() );
	}

}
