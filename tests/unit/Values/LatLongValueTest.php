<?php

namespace Tests\DataValues\Geo\Values;

use DataValues\Geo\Values\LatLongValue;
use DataValues\Tests\DataValueTest;

/**
 * @covers DataValues\Geo\Values\LatLongValue
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

		$argLists[] = [ null, null ];

		$argLists[] = [ 42, null ];
		$argLists[] = [ [], null ];
		$argLists[] = [ false, null ];
		$argLists[] = [ true, null ];
		$argLists[] = [ 'foo', null ];
		$argLists[] = [ 42, null ];

		$argLists[] = [ 'en', 42 ];
		$argLists[] = [ 'en', 4.2 ];
		$argLists[] = [ 42, false ];
		$argLists[] = [ 42, [] ];
		$argLists[] = [ 42, 'foo' ];
		$argLists[] = [ 4.2, 'foo' ];

		$argLists[] = [ '4.2', 4.2 ];
		$argLists[] = [ '4.2', '4.2' ];
		$argLists[] = [ 4.2, '4.2' ];
		$argLists[] = [ '42', 42 ];
		$argLists[] = [ 42, '42' ];
		$argLists[] = [ '0', 0 ];

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

}
