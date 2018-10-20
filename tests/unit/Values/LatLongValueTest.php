<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Values;

use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\Values\LatLongValue
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LatLongValueTest extends TestCase {

	public function testTooHighLatitudesCauseInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new LatLongValue( 361, 0 );
	}

	public function testTooLowLatitudesCauseInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new LatLongValue( -360.001, 0 );
	}

	public function testTooHighLongitudesCauseInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new LatLongValue( 0, 360.001 );
	}

	public function testLo9wHighLongitudesCauseInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new LatLongValue( 0, -361 );
	}

	public function testGetLatitudeReturnsConstructorValue() {
		$this->assertSame(
			12.34,
			( new LatLongValue( 12.34, 56.78 ) )->getLatitude()
		);
	}

	public function testGetLongitudeReturnsConstructorValue() {
		$this->assertSame(
			56.78,
			( new LatLongValue( 12.34, 56.78 ) )->getLongitude()
		);
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

	/**
	 * @dataProvider instanceProvider
	 */
	public function testValuesEqualThemselves( LatLongValue $latLongValue ) {
		$this->assertTrue( $latLongValue->equals( $latLongValue ) );
	}

	public function instanceProvider() {
		yield [ new LatLongValue( 12.34, 56.78 ) ];
		yield [ new LatLongValue( 1, 1 ) ];
		yield [ new LatLongValue( 0, 0 ) ];
		yield [ new LatLongValue( -1, 10 ) ];
		yield [ new LatLongValue( 10, -1 ) ];
		yield [ new LatLongValue( -360, -360 ) ];
		yield [ new LatLongValue( 360, 360 ) ];
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testIdenticalValuesAreEqual( LatLongValue $latLongValue ) {
		$this->assertTrue( $latLongValue->equals( $latLongValue->getCopy() ) );
	}

	public function testDifferentValuesDoNotEqual() {
		$this->assertFalse(
			( new LatLongValue( 0, 0 ) )->equals( new LatLongValue( 1, 0 ) )
		);

		$this->assertFalse(
			( new LatLongValue( 0, 0 ) )->equals( new LatLongValue( 0, 1 ) )
		);

		$this->assertFalse(
			( new LatLongValue( 0, 0 ) )->equals( new LatLongValue( 0, 0.01 ) )
		);

		$this->assertFalse(
			( new LatLongValue( 0, 1 ) )->equals( new LatLongValue( 0, -1 ) )
		);
	}

	public function testSerialize() {
		$this->assertSame(
			'12.34|56.78',
			( new LatLongValue( 12.34, 56.78 ) )->serialize()
		);

		$this->assertSame(
			'-12.34|0',
			( new LatLongValue( -12.34, 0 ) )->serialize()
		);

		$this->assertSame(
			'0|-56.78',
			( new LatLongValue( 0, -56.78 ) )->serialize()
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSerializeRountripsWithUnserialize( LatLongValue $latLong ) {
		$this->assertEquals(
			$latLong,
			unserialize( serialize( $latLong ) )
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetArrayValueAndNewFromArrayRoundtrip( LatLongValue $latLong ) {
		$this->assertEquals(
			$latLong,
			LatLongValue::newFromArray( $latLong->getArrayValue() )
		);
	}

	public function testToArray() {
		$this->assertSame(
			[
				'value' => [
					'latitude' => 12.34,
					'longitude' => 56.78
				],
				'type' => 'geocoordinate',
			],
			( new LatLongValue( 12.34, 56.78 ) )->toArray()
		);
	}

	public function testGetType() {
		$this->assertSame(
			'geocoordinate',
			LatLongValue::getType()
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetSortkeyReturnsLatitude( LatLongValue $latLong ) {
		$this->assertSame(
			$latLong->getLatitude(),
			$latLong->getSortKey()
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetValueReturnsItself( LatLongValue $latLong ) {
		$this->assertSame(
			$latLong,
			$latLong->getValue()
		);
	}

	public function testNewFromArrayWithoutLatitudeCausesException() {
		$this->expectException( \InvalidArgumentException::class );

		LatLongValue::newFromArray( [
			'longitude' => 56.78,
		] );
	}

	public function testNewFromArrayWithoutLongitudeCausesException() {
		$this->expectException( \InvalidArgumentException::class );

		LatLongValue::newFromArray( [
			'latitude' => 12.34,
		] );
	}

	public function testNewFromArrayWithNonArrayParameterCausesException() {
		$this->expectException( \InvalidArgumentException::class );
		LatLongValue::newFromArray( 'such' );
	}

}
