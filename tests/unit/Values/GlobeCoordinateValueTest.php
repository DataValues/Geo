<?php

namespace Tests\DataValues\Geo\Values;

use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use DataValues\IllegalValueException;

/**
 * @covers \DataValues\Geo\Values\GlobeCoordinateValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GlobeCoordinateValueTest extends DataValueTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return GlobeCoordinateValue::class;
	}

	public function validConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1 ];
		$argLists[] = [ new LatLongValue( 4.2, 42 ), 1 ];
		$argLists[] = [ new LatLongValue( 42, 4.2 ), 0.1 ];
		$argLists[] = [ new LatLongValue( 42, 42 ), 0.1 ];
		$argLists[] = [ new LatLongValue( -4.2, -4.2 ), 0.1 ];
		$argLists[] = [ new LatLongValue( 4.2, -42 ), 0.1 ];
		$argLists[] = [ new LatLongValue( -42, 4.2 ), 10 ];
		$argLists[] = [ new LatLongValue( 0, 0 ), 0.001 ];
		$argLists[] = [ new LatLongValue( 0, 0 ), 360 ];
		$argLists[] = [ new LatLongValue( 0, 0 ), -360 ];

		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1, GlobeCoordinateValue::GLOBE_EARTH ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1, 'terminus' ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1, "Schar's World" ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1, 'coruscant' ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1, null ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), null ];

		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$argLists = [];

		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 361 ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), -361 ];
		$argLists[] = [ new LatLongValue( 4.2, 4.2 ), 1, '' ];

		return $argLists;
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetLatitude( GlobeCoordinateValue $globeCoordinate, array $arguments ) {
		$actual = $globeCoordinate->getLatitude();

		$this->assertInternalType( 'float', $actual );
		$this->assertSame( $arguments[0]->getLatitude(), $actual );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetLongitude( GlobeCoordinateValue $globeCoordinate, array $arguments ) {
		$actual = $globeCoordinate->getLongitude();

		$this->assertInternalType( 'float', $actual );
		$this->assertSame( $arguments[0]->getLongitude(), $actual );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetPrecision( GlobeCoordinateValue $globeCoordinate, array $arguments ) {
		$actual = $globeCoordinate->getPrecision();

		$this->assertTrue(
			is_float( $actual ) || is_int( $actual ) || $actual === null,
			'Precision is int or float or null'
		);
		$this->assertEquals( $arguments[1], $actual );
	}

	/**
	 * @dataProvider instanceProvider
	 */
	public function testGetGlobe( GlobeCoordinateValue $globeCoordinate, array $arguments ) {
		$expected = isset( $arguments[2] )
			? $arguments[2]
			: GlobeCoordinateValue::GLOBE_EARTH;

		$actual = $globeCoordinate->getGlobe();

		$this->assertTrue(
			is_string( $actual ),
			'getGlobe should return a string'
		);

		$this->assertSame( $expected, $actual );
	}

	public function provideIllegalArrayValue() {
		return [
			[ null ],
			[ '' ],
			[ [] ],
			[ [ 'latitude' => 0 ] ],
			[ [ 'longitude' => 0 ] ],
		];
	}

	/**
	 * @dataProvider provideIllegalArrayValue
	 */
	public function testNewFromArrayErrorHandling( $data ) {
		$this->expectException( IllegalValueException::class );
		GlobeCoordinateValue::newFromArray( $data );
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
		$this->assertInstanceOf( $this->getClass(), $globeCoordinate );

		$this->assertSame( -4.2, $globeCoordinate->getLatitude() );
		$this->assertSame( -42.0, $globeCoordinate->getLongitude() );
		$this->assertSame( 0.01, $globeCoordinate->getPrecision() );
		$this->assertSame( 'mars', $globeCoordinate->getGlobe() );

		$globeCoordinate = unserialize(
			'C:42:"DataValues\Geo\Values\GlobeCoordinateValue":27:{[-4.2,-42,9001,0.01,"mars"]}'
		);
		$this->assertInstanceOf( $this->getClass(), $globeCoordinate );
	}

	public function testHashIsConsistentAcrossDifferentRuntimeEnvironments() {
		$latLongValue = new LatLongValue( 12.2, 12.2 );

		$globeCoordinateValue = new GlobeCoordinateValue( $latLongValue, 0.1, 'does not matter' );

		$this->assertEquals( '08a33f1bbb4c8bd91b6531b5bffd91fd', $globeCoordinateValue->getHash() );
	}

}
