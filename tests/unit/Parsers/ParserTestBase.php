<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\Parsers;

use DataValues\DataValue;
use PHPUnit\Framework\TestCase;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * @deprecated This is a copy of ValueParserTestBase from DataValues Common, temporarily created to
 * be able to refactor it away in easy to follow steps.
 *
 * @license GPL-2.0-or-later
 */
abstract class ParserTestBase extends TestCase {

	/**
	 * @return ValueParser
	 */
	abstract protected function getInstance();

	/**
	 * @return array[]
	 */
	abstract public function validInputProvider();

	/**
	 * @dataProvider validInputProvider
	 */
	public function testParseWithValidInputs( $value, DataValue $expected ) {
		$actual = $this->getInstance()->parse( $value );
		$msg = json_encode( $actual->toArray() ) . " should equal\n"
			. json_encode( $expected->toArray() );
		$this->assertTrue( $expected->equals( $actual ), $msg );
	}

	/**
	 * @return array[]
	 */
	abstract public function invalidInputProvider();

	/**
	 * @dataProvider invalidInputProvider
	 */
	public function testParseWithInvalidInputs( $value ) {
		$this->expectException( ParseException::class );
		$this->getInstance()->parse( $value );
	}

}
