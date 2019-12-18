<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo\PackagePrivate;

use DataValues\Geo\PackagePrivate\LatLongPrecisionParser;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\PackagePrivate\LatLongPrecisionParser
 * @license GPL-2.0-or-later
 */
class LatLongPrecisionParserTest extends TestCase {

	public function testSuccessiveFloatParses() {
		$parser = new LatLongPrecisionParser();

		$this->assertEquals(
			$parser->parse( 'S5.5 W37' ),
			$parser->parse( 'S5.5 W37' )
		);
	}

	public function testSuccessiveDmsParses() {
		$parser = new LatLongPrecisionParser();

		$this->assertEquals(
			$parser->parse( '55° 0\' 0", 37° 0\' 0"' ),
			$parser->parse( '55° 0\' 0", 37° 0\' 0"' )
		);
	}

}
