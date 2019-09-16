<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Values;

use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use DataValues\IllegalValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\Values\GlobeCoordinateValue
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GlobeCoordinateValueTest extends TestCase {

	public function testGetLatitudeReturnsConstructorValue() {
		$this->assertSame(
			12.34,
			( new GlobeCoordinateValue( new LatLongValue( 12.34, 56.78 ) ) )->getLatitude()
		);
	}

	public function testGetLongitudeReturnsConstructorValue() {
		$this->assertSame(
			56.78,
			( new GlobeCoordinateValue( new LatLongValue( 12.34, 56.78 ) ) )->getLongitude()
		);
	}

	/**
	 * @dataProvider validNonNullGlobeProvider
	 */
	public function testGetGlobeReturnsConstructorValue( ?string $globe ) {
		$this->assertSame(
			$globe,
			( new GlobeCoordinateValue(
				new LatLongValue( 12.34, 56.78 ),
				null,
				$globe
			) )->getGlobe()
		);
	}

	public function validNonNullGlobeProvider() {
		yield [ GlobeCoordinateValue::GLOBE_EARTH ];
		yield [ "coruscant" ];
		yield [ "Schar's World" ];
		yield [ 'a' ];
		yield [ '0' ];
	}

	public function testNullGlobeDefaultToEarth() {
		$this->assertSame(
			GlobeCoordinateValue::GLOBE_EARTH,
			( new GlobeCoordinateValue( new LatLongValue( 12.34, 56.78 ) ) )->getGlobe()
		);
	}

	/**
	 * @dataProvider validPrecisionProvider
	 */
	public function testGetPrecisionReturnsConstructorValue( float $precision ) {
		$this->assertSame(
			$precision,
			( new GlobeCoordinateValue( new LatLongValue( 12.34, 56.78 ), $precision ) )->getPrecision()
		);
	}

	public function validPrecisionProvider() {
		yield [ 360 ];
		yield [ 359.9 ];
		yield [ -360 ];
		yield [ -359.9 ];
		yield [ 0 ];
		yield [ 1 ];
		yield [ -1 ];
		yield [ 0.1 ];
		yield [ -0.1 ];
		yield [ 123 ];
		yield [ -123 ];
		yield [ 123.4567890123456789 ];
	}

	/**
	 * @dataProvider illegalArrayValueProvider
	 */
	public function testNewFromArrayErrorHandling( $data ) {
		$this->expectException( IllegalValueException::class );
		GlobeCoordinateValue::newFromArray( $data );
	}

	public function illegalArrayValueProvider() {
		return [
			[ null ],
			[ '' ],
			[ [] ],
			[ [ 'latitude' => 0 ] ],
			[ [ 'longitude' => 0 ] ],
		];
	}

	public function testArrayValueCompatibility() {
		// These serializations where generated using revision f91f65f989cc3ffacbe924012d8f5b574e0b710c
		// The strings are the result of calling getArrayValue on the objects and then feeding this to serialize.

		$serialization = 'a:5:{s:8:"latitude";d:-4.2000000000000002;'
			. 's:9:"longitude";d:42;'
			. 's:8:"altitude";N;'
			. 's:9:"precision";d:0.01;'
			. 's:5:"globe";s:4:"mars";}';

		$arrayForm = unserialize( $serialization );
		$globeCoordinate = GlobeCoordinateValue::newFromArray( $arrayForm );

		$this->assertSame( -4.2, $globeCoordinate->getLatitude() );
		$this->assertSame( 42.0, $globeCoordinate->getLongitude() );
		$this->assertSame( 0.01, $globeCoordinate->getPrecision() );
		$this->assertSame( 'mars', $globeCoordinate->getGlobe() );

		$serialization = 'a:5:{s:8:"latitude";d:-4.2000000000000002;'
			. 's:9:"longitude";d:-42;'
			. 's:8:"altitude";d:9001;'
			. 's:9:"precision";d:1;'
			. 's:5:"globe";s:33:"http://www.wikidata.org/entity/Q2";}';

		$arrayForm = unserialize( $serialization );
		$globeCoordinate = GlobeCoordinateValue::newFromArray( $arrayForm );

		$this->assertSame( -4.2, $globeCoordinate->getLatitude() );
		$this->assertSame( -42.0, $globeCoordinate->getLongitude() );
		$this->assertSame( 1.0, $globeCoordinate->getPrecision() );
		$this->assertSame( 'http://www.wikidata.org/entity/Q2', $globeCoordinate->getGlobe() );
	}

	public function testSerializeCompatibility() {
		$globeCoordinate = unserialize(
			'C:42:"DataValues\Geo\Values\GlobeCoordinateValue":27:{[-4.2,-42,null,0.01,"mars"]}'
		);
		$this->assertInstanceOf( GlobeCoordinateValue::class, $globeCoordinate );

		$this->assertSame( -4.2, $globeCoordinate->getLatitude() );
		$this->assertSame( -42.0, $globeCoordinate->getLongitude() );
		$this->assertSame( 0.01, $globeCoordinate->getPrecision() );
		$this->assertSame( 'mars', $globeCoordinate->getGlobe() );

		$globeCoordinate = unserialize(
			'C:42:"DataValues\Geo\Values\GlobeCoordinateValue":27:{[-4.2,-42,9001,0.01,"mars"]}'
		);
		$this->assertInstanceOf( GlobeCoordinateValue::class, $globeCoordinate );
	}

	public function testHashIsConsistentAcrossDifferentRuntimeEnvironments() {
		$latLongValue = new LatLongValue( 12.2, 12.2 );

		$globeCoordinateValue = new GlobeCoordinateValue( $latLongValue, 0.1, 'does not matter' );

		$this->assertSame( '08a33f1bbb4c8bd91b6531b5bffd91fd', $globeCoordinateValue->getHash() );
	}

	public function testGetLatLong() {
		$latLong = new LatLongValue( 1, 2 );

		$this->assertSame(
			$latLong,
			( new GlobeCoordinateValue( $latLong ) )->getLatLong()
		);
	}

	public function testTooHighPrecisionCausesInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new GlobeCoordinateValue( new LatLongValue( 1, 2 ), 360.1 );
	}

	public function testTooLowPrecisionCausesInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new GlobeCoordinateValue( new LatLongValue( 1, 2 ), -360.1 );
	}

	public function testEmptyGlobeCausesInvalidArgumentException() {
		$this->expectException( \InvalidArgumentException::class );
		new GlobeCoordinateValue( new LatLongValue( 1, 2 ), null, '' );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testValuesEqualThemselves( GlobeCoordinateValue $globeValue ) {
		$this->assertTrue( $globeValue->equals( $globeValue ) );
	}

	public function instanceProvider() {
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), 1 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 42 ), 1 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 42, 4.2 ), 0.1 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 42, 42 ), 0.1 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( -4.2, -4.2 ), 0.1 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, -42 ), 0.1 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( -42, 4.2 ), 10 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 0, 0 ), 0.001 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 0, 0 ), 360 ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 0, 0 ), -360 ) ];

		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), 1, GlobeCoordinateValue::GLOBE_EARTH ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), 1, 'terminus' ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), 1, "Schar's World" ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), 1, 'coruscant' ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), 1, null ) ];
		$argLists[] = yield [ new GlobeCoordinateValue( new LatLongValue( 4.2, 4.2 ), null ) ];
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testIdenticalValuesAreEqual( GlobeCoordinateValue $globeValue ) {
		$this->assertTrue( $globeValue->equals( $globeValue->getCopy() ) );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testSerializeRountripsWithUnserialize( GlobeCoordinateValue $globeValue ) {
		$this->assertEquals(
			$globeValue,
			unserialize( serialize( $globeValue ) )
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetArrayValueAndNewFromArrayRoundtrip( GlobeCoordinateValue $globeValue ) {
		$this->assertEquals(
			$globeValue,
			GlobeCoordinateValue::newFromArray( $globeValue->getArrayValue() )
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetSortkeyReturnsLatitude( GlobeCoordinateValue $globeValue ) {
		$this->assertSame(
			$globeValue->getLatitude(),
			$globeValue->getSortKey()
		);
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetValueReturnsItself( GlobeCoordinateValue $globeValue ) {
		$this->assertSame(
			$globeValue,
			$globeValue->getValue()
		);
	}

	public function testNewFromArrayWithoutLatitudeCausesException() {
		$this->expectException( \InvalidArgumentException::class );

		GlobeCoordinateValue::newFromArray( [
			'longitude' => 56.78,
		] );
	}

	public function testNewFromArrayWithoutLongitudeCausesException() {
		$this->expectException( \InvalidArgumentException::class );

		GlobeCoordinateValue::newFromArray( [
			'latitude' => 12.34,
		] );
	}

	public function testNewFromArrayWithNonArrayParameterCausesException() {
		$this->expectException( \InvalidArgumentException::class );
		GlobeCoordinateValue::newFromArray( 'such' );
	}

	/**
	 * @dataProvider withPrecisionProvider
	 */
	public function testWithPrecisionReturnsValueWithNewPrecision( ?float $originalPrecision, ?float $newPrecision ) {
		$this->assertEquals(
			$this->newGlobeValueWithPrecision( $newPrecision ),
			$this->newGlobeValueWithPrecision( $originalPrecision )->withPrecision( $newPrecision )
		);
	}

	public function withPrecisionProvider() {
		yield [ null, 0.0001 ];
		yield [ 0.1, 0.0001 ];
		yield [ 0.0001, 0.1 ];
		yield [ 0.0001, null ];
	}

	private function newGlobeValueWithPrecision( ?float $precision ) {
		return new GlobeCoordinateValue( new LatLongValue( 5, 6 ), $precision, 'globe' );
	}

	public function testWithPrecisionDoesNotReturnTheSameInstance() {
		$globeValue = $this->newGlobeValueWithPrecision( 0.1 );

		$this->assertNotSame(
			$globeValue,
			$globeValue->withPrecision( 0.1 )
		);
	}

	public function testToArrayTypeKey() {
		$globeValue = new GlobeCoordinateValue( new LatLongValue( 42, 23 ), 0.1 );

		$this->assertSame(
			'globecoordinate',
			$globeValue->toArray()['type']
		);
	}

	public function testToArrayValueKey() {
		$globeValue = new GlobeCoordinateValue( new LatLongValue( 42, 23 ), 0.1 );

		$this->assertSame(
			[
				'latitude' => 42.0,
				'longitude' => 23.0,
				'altitude' => null,
				'precision' => 0.1,
				'globe' => 'http://www.wikidata.org/entity/Q2',
			],
			$globeValue->toArray()['value']
		);
	}

	public function testGetType() {
		$this->assertSame(
			'globecoordinate',
			GlobeCoordinateValue::getType()
		);
	}

}
