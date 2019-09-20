<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\DmsCoordinateParser;
use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;
use ValueParsers\ParseException;

/**
 * @covers \DataValues\Geo\Parsers\DmsCoordinateParser
 * @covers \DataValues\Geo\Parsers\LatLongParserBase
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class DmsCoordinateParserTest extends TestCase {

	/**
	 * @dataProvider validInputProvider
	 */
	public function testParseWithValidInputs( $value, LatLongValue $expected ) {
		$this->assertEquals(
			$expected,
			( new DmsCoordinateParser() )->parse( $value )
		);
	}

	/**
	 * @see ParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$valid = [
			// Whitespace
			"1°0'0\"N 1°0'0\"E\n" => new LatLongValue( 1, 1 ),
			' 1°0\'0"N 1°0\'0"E ' => new LatLongValue( 1, 1 ),

			'55° 45\' 20.8296", 37° 37\' 3.4788"' => new LatLongValue( 55.755786, 37.617633 ),
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => new LatLongValue( 55.755786, -37.617633 ),
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => new LatLongValue( -55.755786, -37.617633 ),
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => new LatLongValue( -55.755786, 37.617633 ),
			'55° 0\' 0", 37° 0\' 0"' => new LatLongValue( 55, 37 ),
			'55° 30\' 0", 37° 30\' 0"' => new LatLongValue( 55.5, 37.5 ),
			'55° 0\' 18", 37° 0\' 18"' => new LatLongValue( 55.005, 37.005 ),
			'0° 0\' 0", 0° 0\' 0"' => new LatLongValue( 0, 0 ),
			'0° 0\' 18" N, 0° 0\' 18" E' => new LatLongValue( 0.005, 0.005 ),
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => new LatLongValue( -0.005, -0.005 ),
			'55° 0′ 18″, 37° 0′ 18″' => new LatLongValue( 55.005, 37.005 ),

			// Coordinate strings without separator:
			'55° 45\' 20.8296" 37° 37\' 3.4788"' => new LatLongValue( 55.755786, 37.617633 ),
			'55 ° 45 \' 20.8296 " -37 ° 37 \' 3.4788 "' => new LatLongValue( 55.755786, -37.617633 ),
			'-55 ° 45 \' 20.8296 " -37° 37\' 3.4788"' => new LatLongValue( -55.755786, -37.617633 ),
			'55° 0′ 18″ 37° 0′ 18″' => new LatLongValue( 55.005, 37.005 ),

			// Coordinate string starting with direction character:
			'N 0° 0\' 18", E 0° 0\' 18"' => new LatLongValue( 0.005, 0.005 ),
			'S 0° 0\' 18" E 0° 0\' 18"' => new LatLongValue( -0.005, 0.005 ),
		];

		foreach ( $valid as $input => $expected ) {
			yield [ $input, $expected ];
		}
	}

	/**
	 * @dataProvider invalidInputProvider
	 */
	public function testParseWithInvalidInputs( $value ) {
		$this->expectException( ParseException::class );
		( new DmsCoordinateParser() )->parse( $value );
	}

	public function invalidInputProvider() {
		yield [ null ];
		yield [ 1 ];
		yield [ 0.1 ];
		yield [ '~=[,,_,,]:3' ];
		yield [ 'ohi there' ];
	}

	public function testWhenSingleMinutePositionIsMissing_itGetsDefaultedToZero() {
		$this->assertEquals(
			new LatLongValue( 1.0005555555555556, 4.085 ),
			$this->parse( '1°2"N, 4°5\'6"E' )
		);
	}

	private function parse( string $input ): LatLongValue {
		return ( new DmsCoordinateParser() )->parse( $input );
	}

}
