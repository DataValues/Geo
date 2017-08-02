<?php

namespace Tests\DataValues\Geo\Parsers;

use DataValues\Geo\Parsers\FloatCoordinateParser;
use DataValues\Geo\Values\LatLongValue;
use ValueParsers\Test\StringValueParserTest;

/**
 * @covers DataValues\Geo\Parsers\FloatCoordinateParser
 *
 * @group ValueParsers
 * @group DataValueExtensions
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class FloatCoordinateParserTest extends StringValueParserTest {

	/**
	 * @deprecated since DataValues Common 0.3, just use getInstance.
	 */
	protected function getParserClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return FloatCoordinateParser
	 */
	protected function getInstance() {
		return new FloatCoordinateParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$argLists = [];

		// TODO: test with different parser options

		$valid = [
			// Whitespace
			"1N 1E\n" => [ 1, 1 ],
			' 1N 1E ' => [ 1, 1 ],

			'55.7557860 N, 37.6176330 W' => [ 55.7557860, -37.6176330 ],
			'55.7557860, -37.6176330' => [ 55.7557860, -37.6176330 ],
			'55 S, 37.6176330 W' => [ -55, -37.6176330 ],
			'-55, -37.6176330' => [ -55, -37.6176330 ],
			'5.5S,37W ' => [ -5.5, -37 ],
			'-5.5,-37 ' => [ -5.5, -37 ],
			'4,2' => [ 4, 2 ],

			// Coordinate strings without separator:
			'55.7557860 N 37.6176330 W' => [ 55.7557860, -37.6176330 ],
			'55.7557860 -37.6176330' => [ 55.7557860, -37.6176330 ],
			'55 S 37.6176330 W' => [ -55, -37.6176330 ],
			'-55 -37.6176330' => [ -55, -37.6176330 ],
			'5.5S 37W ' => [ -5.5, -37 ],
			'-5.5 -37 ' => [ -5.5, -37 ],
			'4 2' => [ 4, 2 ],

			// Coordinate string starting with direction character:
			'S5.5 W37 ' => [ -5.5, -37 ],
			'N 5.5 E 37 ' => [ 5.5, 37 ],
		];

		foreach ( $valid as $value => $expected ) {
			$expected = new LatLongValue( $expected[0], $expected[1] );
			$argLists[] = [ (string)$value, $expected ];
		}

		return $argLists;
	}

	/**
	 * @see StringValueParserTest::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = [
			'~=[,,_,,]:3',
			'ohi there',
		];

		foreach ( $invalid as $value ) {
			$argLists[] = [ $value ];
		}

		return $argLists;
	}

}
