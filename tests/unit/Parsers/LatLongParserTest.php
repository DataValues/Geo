<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\LatLongParser;
use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;
use ValueParsers\ParseException;

/**
 * @covers \DataValues\Geo\Parsers\LatLongParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LatLongParserTest extends TestCase {

	/**
	 * @dataProvider validInputProvider
	 */
	public function testParseWithValidInputs( $value, LatLongValue $expected ) {
		$actual = ( new LatLongParser() )->parse( $value );
		$msg = json_encode( $actual->toArray() ) . " should equal\n"
			. json_encode( $expected->toArray() );
		$this->assertTrue( $expected->equals( $actual ), $msg );
	}

	public function validInputProvider() {
		$valid = [
			// Whitespace
			"1N 1E\n" => new LatLongValue( 1, 1 ),
			' 1N 1E ' => new LatLongValue( 1, 1 ),

			// Float
			'55.7557860 N, 37.6176330 W' => new LatLongValue( 55.7557860, -37.6176330 ),
			'55.7557860, -37.6176330' => new LatLongValue( 55.7557860, -37.6176330 ),
			'55 S, 37.6176330 W' => new LatLongValue( -55, -37.6176330 ),
			'-55, -37.6176330' => new LatLongValue( -55, -37.6176330 ),
			'5.5S,37W ' => new LatLongValue( -5.5, -37 ),
			'-5.5,-37 ' => new LatLongValue( -5.5, -37 ),
			'4,2' => new LatLongValue( 4, 2 ),
			'5.5S 37W ' => new LatLongValue( -5.5, -37 ),
			'-5.5 -37 ' => new LatLongValue( -5.5, -37 ),
			'4 2' => new LatLongValue( 4, 2 ),
			'S5.5 W37 ' => new LatLongValue( -5.5, -37 ),

			// DD
			'55.7557860° N, 37.6176330° W' => new LatLongValue( 55.7557860, -37.6176330 ),
			'55.7557860°, -37.6176330°' => new LatLongValue( 55.7557860, -37.6176330 ),
			'55° S, 37.6176330 ° W' => new LatLongValue( -55, -37.6176330 ),
			'-55°, -37.6176330 °' => new LatLongValue( -55, -37.6176330 ),
			'5.5°S,37°W ' => new LatLongValue( -5.5, -37 ),
			'-5.5°,-37° ' => new LatLongValue( -5.5, -37 ),
			'-55° -37.6176330 °' => new LatLongValue( -55, -37.6176330 ),
			'5.5°S 37°W ' => new LatLongValue( -5.5, -37 ),
			'-5.5 ° -37 ° ' => new LatLongValue( -5.5, -37 ),
			'S5.5° W37°' => new LatLongValue( -5.5, -37 ),

			// DMS
			'55° 45\' 20.8296", 37° 37\' 3.4788"' => new LatLongValue( 55.755786, 37.6176330000 ),
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => new LatLongValue( 55.755786, -37.6176330000 ),
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => new LatLongValue( -55.755786, -37.6176330000 ),
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => new LatLongValue( -55.755786, 37.6176330000 ),
			'55° 0\' 0", 37° 0\' 0"' => new LatLongValue( 55, 37 ),
			'55° 30\' 0", 37° 30\' 0"' => new LatLongValue( 55.5, 37.5 ),
			'55° 0\' 18", 37° 0\' 18"' => new LatLongValue( 55.005, 37.005 ),
			'0° 0\' 0", 0° 0\' 0"' => new LatLongValue( 0, 0 ),
			'0° 0\' 18" N, 0° 0\' 18" E' => new LatLongValue( 0.005, 0.005 ),
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => new LatLongValue( -0.005, -0.005 ),
			'0° 0′ 18″ N, 0° 0′ 18″ E' => new LatLongValue( 0.005, 0.005 ),
			'0° 0\' 18" N  0° 0\' 18" E' => new LatLongValue( 0.005, 0.005 ),
			' 0 ° 0 \' 18 " S   0 °  0 \' 18 "  W ' => new LatLongValue( -0.005, -0.005 ),
			'0° 0′ 18″ N 0° 0′ 18″ E' => new LatLongValue( 0.005, 0.005 ),
			'N 0° 0\' 18" E 0° 0\' 18"' => new LatLongValue( 0.005, 0.005 ),

			// DM
			'55° 0\', 37° 0\'' => new LatLongValue( 55, 37 ),
			'55° 30\', 37° 30\'' => new LatLongValue( 55.5, 37.5 ),
			'0° 0\', 0° 0\'' => new LatLongValue( 0, 0 ),
			'-55° 30\', -37° 30\'' => new LatLongValue( -55.5, -37.5 ),
			'0° 0.3\' S, 0° 0.3\' W' => new LatLongValue( -0.005, -0.005 ),
			'-55° 30′, -37° 30′' => new LatLongValue( -55.5, -37.5 ),
			'-55 ° 30 \' -37 ° 30 \'' => new LatLongValue( -55.5, -37.5 ),
			'0° 0.3\' S 0° 0.3\' W' => new LatLongValue( -0.005, -0.005 ),
			'-55° 30′ -37° 30′' => new LatLongValue( -55.5, -37.5 ),
			'S 0° 0.3\' W 0° 0.3\'' => new LatLongValue( -0.005, -0.005 ),

			// Case insensitivity
			'55 s, 37.6176330 w' => new LatLongValue( -55, -37.6176330 ),
			'5.5s,37w ' => new LatLongValue( -5.5, -37 ),
			'5.5s 37w ' => new LatLongValue( -5.5, -37 ),
			's5.5 w37 ' => new LatLongValue( -5.5, -37 ),
			'5.5S 37w ' => new LatLongValue( -5.5, -37 ),
			'5.5s 37W ' => new LatLongValue( -5.5, -37 ),
		];

		foreach ( $valid as $input => $expected ) {
			yield [ $input, $expected ];
		}
	}

	/**
	 * @dataProvider invalidInputProvider
	 */
	public function testWhenInvalidInputIsProvided_exceptionIsThrown( $value ) {
		$parser = new LatLongParser();

		$this->expectException( ParseException::class );
		$parser->parse( $value );
	}

	public function invalidInputProvider() {
		yield [ null ];
		yield [ 1 ];
		yield [ 0.1 ];
		yield [ '~=[,,_,,]:3' ];
		yield [ 'ohi there' ];

		yield 'Latitude and longitude mixup' => [ '1E 1N' ];
		yield 'Doubled directional indicators' => [ '1NN, 1EE' ];
		yield 'Invalid directional indicators' => [ '1X, 1Y' ];
		yield 'Directional indicators with junk' => [ '1NX, 1EY' ];
		yield 'Directional indicators on both sides' => [ 'N1N, E1E' ];

		yield 'No coordinates' => [ 'N, E' ];
		yield 'Missing latitude' => [ 'N, 1E' ];
		yield 'Missing longitude' => [ '1N, E' ];
	}

}
