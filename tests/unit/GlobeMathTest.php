<?php

declare( strict_types = 1 );

namespace Tests\DataValues\Geo;

use DataValues\Geo\GlobeMath;
use DataValues\Geo\Values\GlobeCoordinateValue;
use DataValues\Geo\Values\LatLongValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DataValues\Geo\GlobeMath
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @license GPL-2.0-or-later
 * @author Thiemo Kreuz
 */
class GlobeMathTest extends TestCase {

	private const EPSILON = 0.0000000000001;

	/**
	 * @var GlobeMath
	 */
	private $math;

	protected function setUp() {
		$this->math = new GlobeMath();
	}

	/**
	 * @dataProvider globeProvider
	 */
	public function testNormalizeGlobe( $expected, $globe ) {
		$normalized = $this->math->normalizeGlobe( $globe );

		$this->assertSame( $expected, $normalized );
	}

	public function globeProvider() {
		yield [ 'http://www.wikidata.org/entity/Q2', null ];
		yield [ 'http://www.wikidata.org/entity/Q2', '' ];
		yield [ 'Vulcan', 'Vulcan' ];
	}

	public function latLongProvider() {
		// Reminder: On Earth, latitude increases from south to north, longitude increases from
		// west to east. For other globes see http://planetarynames.wr.usgs.gov/TargetCoordinates
		return [
			// Yes, there really are nine ways to describe the same point
			[ 0, 0,    0,    0 ],
			[ 0, 0,    0,  360 ],
			[ 0, 0,    0, -360 ],
			[ 0, 0,  360,    0 ],
			[ 0, 0, -360,    0 ],
			[ 0, 0,  180,  180 ],
			[ 0, 0,  180, -180 ],
			[ 0, 0, -180,  180 ],
			[ 0, 0, -180, -180 ],

			// Earth (default) vs. other globes
			[ 0, -10, 0, -10 ],
			[ 0, 350, 0, -10, 'Vulcan' ],
			[ 0, -10, 0, 350 ],
			[ 0, 350, 0, 350, 'Vulcan' ],

			// Make sure the methods do not simply return true
			[ 0, 0,   0,  180, null, false ],
			[ 0, 0,   0, -180, null, false ],
			[ 0, 0, 180,    0, null, false ],
			[ 0, 0, 180,  360, null, false ],

			// Dark side of the Moon, erm Earth
			[ 0, -180,    0,  180 ],
			[ 0, -180,    0, -180 ],
			[ 0, -180,  180,    0 ],
			[ 0, -180, -180,    0 ],
			[ 0, -180, -360, -180 ],

			// Half way to the north pole
			[ 45, 0,  45, -360 ],
			[ 45, 0, 135,  180 ],
			[ 45, 0, 135, -180 ],

			// North pole is a special case, drop longitude
			[ 90, 0, 90, -123 ],
			[ 90, 0, -270, 0 ],
			[ 90, 0, -270, 180 ],
			[ 90, 0, -90, 0, null, false ],
			// Same for south pole
			[ -90,  0,  -90,  123 ],
			[ -90,  0,  270,    0 ],
			[ -90,  0,  270, -180 ],

			// Make sure we cover all cases in the code
			[ 10, 10, 10, 10 ],
			[ 10, 10, 10, -350 ],
			[ 10, 10, -10, -10, null, false ],
			[ -10, 0, 190, 180 ],
			[ 10, 0, -190, 180 ],
			[ -80, 0, -100, 180 ],
			[ 80, 0, 100, 180 ],

			// Make sure nobody casts to integer
			[ 1.234, -9.3, 178.766, -189.3 ],

			// Avoid messing with precision if not necessary
			[ 0.3, 0.3, 0.3, 0.3 ],

			// IEEE 754
			[ -0.3, -0.3, 359.7, 359.7 ],
			[ 0.3, 0.3, -359.7, -359.7 ],
			[ 0.3, -0.3, 179.7, 179.7 ],
			[ -0.3, 0.3, -179.7, -179.7 ],
		];
	}

	/**
	 * @dataProvider latLongProvider
	 */
	public function testNormalizeGlobeCoordinate(
		$expectedLat, $expectedLon,
		$lat, $lon,
		$globe = null,
		$expectedEquality = true
	) {
		$expectedLatLong = new LatLongValue( $expectedLat, $expectedLon );
		$latLong = new LatLongValue( $lat, $lon );
		if ( $globe === null ) {
			$globe = GlobeCoordinateValue::GLOBE_EARTH;
		}
		$coordinate = new GlobeCoordinateValue( $latLong, null, $globe );

		$normalized = $this->math->normalizeGlobeCoordinate( $coordinate );

		$equality = $this->equals( $expectedLatLong, $normalized->getLatLong() );
		$this->assertSame( $expectedEquality, $equality );
	}

	/**
	 * @dataProvider latLongProvider
	 */
	public function testNormalizeGlobeLatLong(
		$expectedLat, $expectedLon,
		$lat, $lon,
		$globe = null,
		$expectedEquality = true
	) {
		$expectedLatLong = new LatLongValue( $expectedLat, $expectedLon );
		$latLong = new LatLongValue( $lat, $lon );

		$normalized = $this->math->normalizeGlobeLatLong( $latLong, $globe );

		$equality = $this->equals( $expectedLatLong, $normalized );
		$this->assertSame( $expectedEquality, $equality );
	}

	/**
	 * @dataProvider latLongProvider
	 */
	public function testNormalizeLatLong(
		$expectedLat, $expectedLon,
		$lat, $lon,
		$globe = null,
		$expectedEquality = true
	) {
		$expectedLatLong = new LatLongValue( $expectedLat, $expectedLon );
		$latLong = new LatLongValue( $lat, $lon );
		$minimumLongitude = $globe === null ? -180 : 0;

		$normalized = $this->math->normalizeLatLong( $latLong, $minimumLongitude );

		$equality = $this->equals( $expectedLatLong, $normalized );
		$this->assertSame( $expectedEquality, $equality );
	}

	/**
	 * @param LatLongValue $a
	 * @param LatLongValue $b
	 *
	 * @return bool
	 */
	private function equals( LatLongValue $a, LatLongValue $b ) {
		return abs( $a->getLatitude() - $b->getLatitude() ) < self::EPSILON
			&& abs( $a->getLongitude() - $b->getLongitude() ) < self::EPSILON;
	}

}
