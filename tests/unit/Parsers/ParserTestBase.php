<?php

namespace Tests\DataValues\Geo\Parsers;

use DataValues\DataValue;
use ValueParsers\ParseException;
use ValueParsers\ValueParser;

/**
 * @deprecated
 * TODO: remove
 * This is a copy of ValueParserTestBase from DataValues Common,
 * created so we can stop depending on that comment even though we
 * did not refactor away the inheritance abuse here yet.
 *
 * @license GPL-2.0+
 */
abstract class ParserTestBase extends \PHPUnit_Framework_TestCase {

	/**
	 * @return array[]
	 */
	abstract public function validInputProvider();

	/**
	 * @return array[]
	 */
	abstract public function invalidInputProvider();

	/**
	 * @return ValueParser
	 */
	abstract protected function getInstance();

	/**
	 * @dataProvider validInputProvider
	 * @param mixed $value
	 * @param mixed $expected
	 * @param ValueParser|null $parser
	 */
	public function testParseWithValidInputs( $value, $expected, ValueParser $parser = null ) {
		if ( $parser === null ) {
			$parser = $this->getInstance();
		}

		$this->assertSmartEquals( $expected, $parser->parse( $value ) );
	}

	/**
	 * @param DataValue|mixed $expected
	 * @param DataValue|mixed $actual
	 */
	private function assertSmartEquals( $expected, $actual ) {
		if ( $expected instanceof DataValue && $actual instanceof DataValue ) {
			$msg = "testing equals():\n"
				. preg_replace( '/\s+/', ' ', print_r( $actual->toArray(), true ) ) . " should equal\n"
				. preg_replace( '/\s+/', ' ', print_r( $expected->toArray(), true ) );
		} else {
			$msg = 'testing equals()';
		}

		$this->assertTrue( $expected->equals( $actual ), $msg );
	}

	/**
	 * @dataProvider invalidInputProvider
	 * @param mixed $value
	 * @param ValueParser|null $parser
	 */
	public function testParseWithInvalidInputs( $value, ValueParser $parser = null ) {
		if ( $parser === null ) {
			$parser = $this->getInstance();
		}

		$this->setExpectedException( ParseException::class );
		$parser->parse( $value );
	}

}
