<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Formatters;

use DataValues\Geo\Formatters\GlobeCoordinateFormatter;
use DataValues\Geo\Formatters\LatLongFormatter;
use DataValues\Geo\Parsers\GlobeCoordinateParser;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;
use ValueFormatters\FormatterOptions;
use ValueParsers\ParserOptions;

/**
 * @covers \DataValues\Geo\Formatters\GlobeCoordinateFormatter
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class GlobeCoordinateFormatterTest extends TestCase {

	/**
	 * @dataProvider validProvider
	 */
	public function testValidFormat( $value, $expected, FormatterOptions $options ) {
		$formatter = new GlobeCoordinateFormatter( $options );
		$this->assertSame( $expected, $formatter->format( $value ) );
	}

	public function validProvider() {
		$floats = [
			'55.755786, -37.617633' => [ 55.755786, -37.617633, 0.000001 ],
			'-55.7558, 37.6176' => [ -55.755786, 37.617633, 0.0001 ],
			'-55, -38' => [ -55, -37.617633, 1 ],
			'5.5, 37' => [ 5.5, 37, 0.1 ],
			'0, 0' => [ 0, 0, 1 ],
		];

		$decimalDegrees = [
			'55.755786°, 37.617633°' => [ 55.755786, 37.617633, 0.000001 ],
			'55.7558°, -37.6176°' => [ 55.755786, -37.617633, 0.0001 ],
			'-55°, -38°' => [ -55, -37.617633, 1 ],
			'-5.5°, -37.0°' => [ -5.5, -37, 0.1 ],
			'0°, 0°' => [ 0, 0, 1 ],
		];

		$dmsCoordinates = [
			'55° 45\' 20.830", 37° 37\' 3.479"' => [ 55.755786, 37.617633, 0.000001 ],
			'55° 45\' 20.830", -37° 37\' 3.479"' => [ 55.755786, -37.617633, 0.000001 ],
			'-55° 45\' 20.9", -37° 37\' 3.4"' => [ -55.755786, -37.617633, 0.0001 ],
			'-55° 45\' 20.9", 37° 37\' 3.4"' => [ -55.755786, 37.617633, 0.0001 ],

			'55°, 37°' => [ 55, 37, 1 ],
			'55° 30\' 0", 37° 30\' 0"' => [ 55.5, 37.5, 0.01 ],
			'55° 0\' 18", 37° 0\' 18"' => [ 55.005, 37.005, 0.001 ],
			'0° 0\' 0", 0° 0\' 0"' => [ 0, 0, 0.001 ],
			'0° 0\' 18", 0° 0\' 18"' => [ 0.005, 0.005, 0.001 ],
			'-0° 0\' 18", -0° 0\' 18"' => [ -0.005, -0.005, 0.001 ],
		];

		$dmCoordinates = [
			'55°, 37°' => [ 55, 37, 1 ],
			'0°, 0°' => [ 0, 0, 1 ],
			'55° 31\', 37° 31\'' => [ 55.5, 37.5, 0.04 ],
			'-55° 31\', -37° 31\'' => [ -55.5, -37.5, 0.04 ],
			'-0° 0.3\', -0° 0.3\'' => [ -0.005, -0.005, 0.005 ],
		];

		$argLists = [];

		$tests = [
			LatLongFormatter::TYPE_FLOAT => $floats,
			LatLongFormatter::TYPE_DD => $decimalDegrees,
			LatLongFormatter::TYPE_DMS => $dmsCoordinates,
			LatLongFormatter::TYPE_DM => $dmCoordinates,
		];

		$i = 0;
		foreach ( $tests as $format => $coords ) {
			foreach ( $coords as $expectedOutput => $arguments ) {
				$options = new FormatterOptions();
				$options->setOption( LatLongFormatter::OPT_FORMAT, $format );

				$input = new GlobeCoordinateValue(
					new LatLongValue( $arguments[0], $arguments[1] ),
					$arguments[2]
				);

				$key = "[$i] $format: $expectedOutput";
				$argLists[$key] = [ $input, $expectedOutput, $options ];

				$i++;
			}
		}

		return $argLists;
	}

	public function testFormatWithInvalidPrecision_fallsBackToDefaultPrecision() {
		$options = new FormatterOptions();
		$options->setOption( LatLongFormatter::OPT_PRECISION, 0 );
		$formatter = new GlobeCoordinateFormatter( $options );

		$formatted = $formatter->format( new GlobeCoordinateValue( new LatLongValue( 1.2, 3.4 ), null ) );
		$this->assertSame( '1.2, 3.4', $formatted );
	}

	/**
	 * @dataProvider validProvider
	 */
	public function testFormatterRoundTrip(
		GlobeCoordinateValue $coord,
		$expectedValue,
		FormatterOptions $options
	) {
		$formatter = new GlobeCoordinateFormatter( $options );

		$parser = new GlobeCoordinateParser(
			new ParserOptions( [ 'precision' => $coord->getPrecision() ] )
		);

		$formatted = $formatter->format( $coord );
		$parsed = $parser->parse( $formatted );

		// NOTE: $parsed may be != $coord, because of rounding, so we can't compare directly.
		$formattedParsed = $formatter->format( $parsed );

		$this->assertSame( $formatted, $formattedParsed );
	}

}
