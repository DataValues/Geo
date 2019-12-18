<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Parsers;

use DataValues\DataValue;
use DataValues\Geo\Parsers\GlobeCoordinateParser;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;
use ValueParsers\ParseException;
use ValueParsers\ParserOptions;

/**
 * @covers \DataValues\Geo\Parsers\GlobeCoordinateParser
 * @covers \DataValues\Geo\PackagePrivate\LatLongPrecisionParser
 * @covers \DataValues\Geo\PackagePrivate\PreciseLatLong
 * @covers \DataValues\Geo\PackagePrivate\PrecisionParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class GlobeCoordinateParserTest extends TestCase {

	/**
	 * @dataProvider invalidInputProvider
	 */
	public function testParseWithInvalidInputs( $value ) {
		$this->expectException( ParseException::class );
		( new GlobeCoordinateParser() )->parse( $value );
	}

	public function invalidInputProvider() {
		return [
			[ '~=[,,_,,]:3' ],
			[ 'ohi there' ],
		];
	}

	/**
	 * @dataProvider validInputProvider
	 */
	public function testParseWithValidInputs( $value, DataValue $expected ) {
		$actual = ( new GlobeCoordinateParser() )->parse( $value );
		$msg = json_encode( $actual->toArray() ) . " should equal\n"
			. json_encode( $expected->toArray() );
		$this->assertTrue( $expected->equals( $actual ), $msg );
	}

	public function validInputProvider() {
		$argLists = [];

		$valid = [
			// Whitespace
			"1N 1E\n" => [ 1, 1, 1 ],
			' 1N 1E ' => [ 1, 1, 1 ],

			// Float
			'55.7557860 N, 37.6176330 W' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55.7557860N,37.6176330W' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55.7557860, -37.6176330' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55.7557860, -37.6176330    ' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55 S, 37.6176330 W' => [ -55, -37.6176330, 0.000001 ],
			'55 S,37.6176330W' => [ -55, -37.6176330, 0.000001 ],
			'-55, -37.6176330' => [ -55, -37.6176330, 0.000001 ],
			'5.5S,37W ' => [ -5.5, -37, 0.1 ],
			'-5.5,-37 ' => [ -5.5, -37, 0.1 ],
			'-5.5 -37 ' => [ -5.5, -37, 0.1 ],
			'4,2' => [ 4, 2, 1 ],
			'5.5S 37W ' => [ -5.5, -37, 0.1 ],
			'5.5 S 37 W ' => [ -5.5, -37, 0.1 ],
			'4 2' => [ 4, 2, 1 ],
			'S5.5 W37 ' => [ -5.5, -37, 0.1 ],

			// DD
			'55.7557860° N, 37.6176330° W' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55.7557860°, -37.6176330°' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55.7557860°,-37.6176330°' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55.7557860°,-37.6176330°  ' => [ 55.7557860, -37.6176330, 0.000001 ],
			'55° S, 37.6176330 ° W' => [ -55, -37.6176330, 0.000001 ],
			'-55°, -37.6176330 °' => [ -55, -37.6176330, 0.000001 ],
			'5.5°S,37°W ' => [ -5.5, -37, 0.1 ],
			'5.5° S,37° W ' => [ -5.5, -37, 0.1 ],
			'-5.5°,-37° ' => [ -5.5, -37, 0.1 ],
			'-55° -37.6176330 °' => [ -55, -37.6176330, 0.000001 ],
			'5.5°S 37°W ' => [ -5.5, -37, 0.1 ],
			'-5.5 ° -37 ° ' => [ -5.5, -37, 0.1 ],
			'S5.5° W37°' => [ -5.5, -37, 0.1 ],
			' S 5.5° W 37°' => [ -5.5, -37, 0.1 ],

			// DMS
			'55° 45\' 20.8296", 37° 37\' 3.4788"' => [ 55.755786, 37.617633, 0.0001 / 3600 ],
			'55° 45\' 20.8296", -37° 37\' 3.4788"' => [ 55.755786, -37.617633, 0.0001 / 3600 ],
			'-55° 45\' 20.8296", -37° 37\' 3.4788"' => [ -55.755786, -37.617633, 0.0001 / 3600 ],
			'-55° 45\' 20.8296", 37° 37\' 3.4788"' => [ -55.755786, 37.617633, 0.0001 / 3600 ],
			'-55° 45\' 20.8296", 37° 37\' 3.4788"  ' => [ -55.755786, 37.617633, 0.0001 / 3600 ],
			'55° 0\' 0", 37° 0\' 0"' => [ 55, 37, 1 / 3600 ],
			'55° 30\' 0", 37° 30\' 0"' => [ 55.5, 37.5, 1 / 3600 ],
			'55° 0\' 18", 37° 0\' 18"' => [ 55.005, 37.005, 1 / 3600 ],
			'  55° 0\' 18", 37° 0\' 18"' => [ 55.005, 37.005, 1 / 3600 ],
			'0° 0\' 0", 0° 0\' 0"' => [ 0, 0, 1 / 3600 ],
			'0° 0\' 18" N, 0° 0\' 18" E' => [ 0.005, 0.005, 1 / 3600 ],
			' 0° 0\' 18" S  , 0°  0\' 18"  W ' => [ -0.005, -0.005, 1 / 3600 ],
			'0° 0′ 18″ N, 0° 0′ 18″ E' => [ 0.005, 0.005, 1 / 3600 ],
			'0° 0\' 18" N  0° 0\' 18" E' => [ 0.005, 0.005, 1 / 3600 ],
			' 0 ° 0 \' 18 " S   0 °  0 \' 18 "  W ' => [ -0.005, -0.005, 1 / 3600 ],
			'0° 0′ 18″ N 0° 0′ 18″ E' => [ 0.005, 0.005, 1 / 3600 ],
			'N 0° 0\' 18" E 0° 0\' 18"' => [ 0.005, 0.005, 1 / 3600 ],
			'N0°0\'18"E0°0\'18"' => [ 0.005, 0.005, 1 / 3600 ],
			'N0°0\'18" E0°0\'18"' => [ 0.005, 0.005, 1 / 3600 ],

			// DM
			'55° 0\', 37° 0\'' => [ 55, 37, 1 / 60 ],
			'55° 30\', 37° 30\'' => [ 55.5, 37.5, 1 / 60 ],
			'0° 0\', 0° 0\'' => [ 0, 0, 1 / 60 ],
			'   0° 0\', 0° 0\'' => [ 0, 0, 1 / 60 ],
			'   0° 0\', 0° 0\'  ' => [ 0, 0, 1 / 60 ],
			'-55° 30\', -37° 30\'' => [ -55.5, -37.5, 1 / 60 ],
			'0° 0.3\' S, 0° 0.3\' W' => [ -0.005, -0.005, 1 / 3600 ],
			'-55° 30′, -37° 30′' => [ -55.5, -37.5, 1 / 60 ],
			'-55 ° 30 \' -37 ° 30 \'' => [ -55.5, -37.5, 1 / 60 ],
			'0° 0.3\' S 0° 0.3\' W' => [ -0.005, -0.005, 1 / 3600 ],
			'-55° 30′ -37° 30′' => [ -55.5, -37.5, 1 / 60 ],
			'S 0° 0.3\' W 0° 0.3\'' => [ -0.005, -0.005, 1 / 3600 ],
			'S0°0.3\'W0°0.3\'' => [ -0.005, -0.005, 1 / 3600 ],
			'S0°0.3\' W0°0.3\'' => [ -0.005, -0.005, 1 / 3600 ],
		];

		foreach ( $valid as $value => $expected ) {
			$expected = new GlobeCoordinateValue( new LatLongValue( $expected[0], $expected[1] ), $expected[2] );
			$argLists[] = [ (string)$value, $expected ];
		}

		return $argLists;
	}

	public function testWithGlobeOptionMatchingTheDefault() {
		$parser = new GlobeCoordinateParser( new ParserOptions( [
			'globe' => 'http://www.wikidata.org/entity/Q2'
		] ) );

		$this->assertEquals(
			new GlobeCoordinateValue(
				new LatLongValue( 55.7557860, -37.6176330 ),
				0.000001,
				'http://www.wikidata.org/entity/Q2'
			),
			$parser->parse( '55.7557860° N, 37.6176330° W' )
		);
	}

	public function testWithGlobeOptionDifferingFromTheDefault() {
		$parser = new GlobeCoordinateParser( new ParserOptions( [
			'globe' => 'http://www.wikidata.org/entity/Q111'
		] ) );

		$this->assertEquals(
			new GlobeCoordinateValue(
				new LatLongValue( 60.5, 260 ),
				0.1,
				'http://www.wikidata.org/entity/Q111'
			),
			$parser->parse( '60.5, 260' )
		);
	}

	public function testWithoutGlobeOption() {
		$parser = new GlobeCoordinateParser();

		$this->assertEquals(
			new GlobeCoordinateValue(
				new LatLongValue( 40.2, 22.5 ),
				0.1,
				'http://www.wikidata.org/entity/Q2'
			),
			$parser->parse( '40.2, 22.5' )
		);
	}

	/**
	 * @dataProvider precisionDetectionProvider
	 */
	public function testPrecisionDetection( $value, $expected ) {
		$parser = new GlobeCoordinateParser();
		$globeCoordinateValue = $parser->parse( $value );

		$this->assertSame( (float)$expected, $globeCoordinateValue->getPrecision() );
	}

	public function precisionDetectionProvider() {
		return [
			// Float
			[ '10 20', 1 ],
			[ '1 2', 1 ],
			[ '1.3 2.4', 0.1 ],
			[ '1.3 20', 0.1 ],
			[ '10 2.4', 0.1 ],
			[ '1.35 2.46', 0.01 ],
			[ '1.357 2.468', 0.001 ],
			[ '1.3579 2.468', 0.0001 ],
			[ '1.00001 2.00001', 0.00001 ],
			[ '1.000001 2.000001', 0.000001 ],
			[ '1.0000001 2.0000001', 0.0000001 ],
			[ '1.00000001 2.00000001', 0.00000001 ],
			[ '1.000000001 2.000000001', 1 ],
			[ '1.555555555 2.555555555', 0.00000001 ],

			// Dd
			[ '10° 20°', 1 ],
			[ '1° 2°', 1 ],
			[ '1.3° 2.4°', 0.1 ],
			[ '1.3° 20°', 0.1 ],
			[ '10° 2.4°', 0.1 ],
			[ '1.35° 2.46°', 0.01 ],
			[ '1.357° 2.468°', 0.001 ],
			[ '1.3579° 2.468°', 0.0001 ],
			[ '1.00001° 2.00001°', 0.00001 ],
			[ '1.000001° 2.000001°', 0.000001 ],
			[ '1.0000001° 2.0000001°', 0.0000001 ],
			[ '1.00000001° 2.00000001°', 0.00000001 ],
			[ '1.000000001° 2.000000001°', 1 ],
			[ '1.555555555° 2.555555555°', 0.00000001 ],

			// Dm
			[ '1°3\' 2°4\'', 1 / 60 ],
			[ '1°3\' 2°0\'', 1 / 60 ],
			[ '1°0\' 2°4\'', 1 / 60 ],
			[ '1°3.5\' 2°4.6\'', 1 / 3600 ],
			[ '1°3.57\' 2°4.68\'', 1 / 36000 ],
			[ '1°3.579\' 2°4.68\'', 1 / 360000 ],
			[ '1°3.0001\' 2°4.0001\'', 1 / 3600000 ],
			[ '1°3.00001\' 2°4.00001\'', 1 / 36000000 ],
			[ '1°3.000001\' 2°4.000001\'', 1 / 36000000 ],
			[ '1°3.0000001\' 2°4.0000001\'', 1 / 60 ],
			[ '1°3.5555555\' 2°4.5555555\'', 1 / 36000000 ],

			// Dms
			[ '1°3\'5" 2°4\'6"', 1 / 3600 ],
			[ '1°3\'5" 2°0\'0"', 1 / 3600 ],
			[ '1°0\'0" 2°4\'6"', 1 / 3600 ],
			[ '1°3\'0" 2°4\'0"', 1 / 3600 ],
			[ '1°3\'5.7" 2°4\'6.8"', 1 / 36000 ],
			[ '1°3\'5.79" 2°4\'6.8"', 1 / 360000 ],
			[ '1°3\'5.001" 2°4\'6.001"', 1 / 3600000 ],
			[ '1°3\'5.0001" 2°4\'6.0001"', 1 / 36000000 ],
			[ '1°3\'5.00001" 2°4\'6.00001"', 1 / 3600 ],
			[ '1°3\'5.55555" 2°4\'6.55555"', 1 / 36000000 ],

			/**
			 * @fixme What do the users expect in this case, 1/3600 or 1/360000?
			 * @see https://bugzilla.wikimedia.org/show_bug.cgi?id=64820
			 */
			[ '47°42\'0.00"N, 15°27\'0.00"E', 1 / 3600 ],
		];
	}

	public function testCanParseSuccessiveValues() {
		$parser = new GlobeCoordinateParser();

		$this->assertEquals(
			$parser->parse( 'S5.5 W37' ),
			$parser->parse( 'S5.5 W37' )
		);

		$this->assertEquals(
			$parser->parse( '55° 0\' 0", 37° 0\' 0"' ),
			$parser->parse( '55° 0\' 0", 37° 0\' 0"' )
		);
	}

}
