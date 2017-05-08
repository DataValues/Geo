<?php

namespace DataValues\Geo\Values;

use DataValues\DataValueObject;
use DataValues\IllegalValueException;

/**
 * Class representing a geographical coordinate value.
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Mättig
 */
class GlobeCoordinateValue extends DataValueObject {

	/**
	 * @var LatLongValue
	 */
	private $latLong;

	/**
	 * The precision of the coordinate in degrees, e.g. 0.01.
	 *
	 * @var float|int|null
	 */
	private $precision;

	/**
	 * IRI of the globe on which the location resides.
	 *
	 * @var string
	 */
	private $globe;

	/**
	 * Wikidata concept URI for the Earth. Used as default value when no other globe was specified.
	 */
	const GLOBE_EARTH = 'http://www.wikidata.org/entity/Q2';

	/**
	 * @param LatLongValue $latLong
	 * @param float|int|null $precision in degrees, e.g. 0.01.
	 * @param string|null $globe IRI, defaults to 'http://www.wikidata.org/entity/Q2'.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( LatLongValue $latLong, $precision = null, $globe = null ) {
		$this->assertIsPrecision( $precision );

		if ( $globe === null ) {
			$globe = self::GLOBE_EARTH;
		} elseif ( !is_string( $globe ) || $globe === '' ) {
			throw new IllegalValueException( '$globe must be a non-empty string or null' );
		}

		$this->latLong = $latLong;
		$this->precision = $precision;
		$this->globe = $globe;
	}

	/**
	 * @see LatLongValue::assertIsLatitude
	 * @see LatLongValue::assertIsLongitude
	 *
	 * @param float|int|null $precision
	 *
	 * @throws IllegalValueException
	 */
	private function assertIsPrecision( $precision ) {
		if ( $precision !== null ) {
			if ( !is_float( $precision ) && !is_int( $precision ) ) {
				throw new IllegalValueException( '$precision must be a number or null' );
			} elseif ( $precision < -360 || $precision > 360 ) {
				throw new IllegalValueException( '$precision needs to be between -360 and 360' );
			}
		}
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return json_encode( array_values( $this->getArrayValue() ) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $value
	 *
	 * @throws IllegalValueException
	 */
	public function unserialize( $value ) {
		list( $latitude, $longitude, $altitude, $precision, $globe ) = json_decode( $value );
		$this->__construct( new LatLongValue( $latitude, $longitude ), $precision, $globe );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @return string
	 */
	public static function getType() {
		return 'globecoordinate';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @return float
	 */
	public function getSortKey() {
		return $this->getLatitude();
	}

	/**
	 * @since 0.1
	 *
	 * @return float
	 */
	public function getLatitude() {
		return $this->latLong->getLatitude();
	}

	/**
	 * @since 0.1
	 *
	 * @return float
	 */
	public function getLongitude() {
		return $this->latLong->getLongitude();
	}

	/**
	 * @see DataValue::getValue
	 *
	 * @return self
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * @since 0.1
	 *
	 * @return LatLongValue
	 */
	public function getLatLong() {
		return $this->latLong;
	}

	/**
	 * Returns the precision of the coordinate in degrees, e.g. 0.01.
	 *
	 * @since 0.1
	 *
	 * @return float|int|null
	 */
	public function getPrecision() {
		return $this->precision;
	}

	/**
	 * Returns the IRI of the globe on which the location resides.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getGlobe() {
		return $this->globe;
	}

	/**
	 * @see Hashable::getHash
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function getHash() {
		return md5( $this->latLong->getLatitude() . '|'
			. $this->latLong->getLongitude() . '|'
			. $this->precision . '|'
			. $this->globe );
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @return array
	 */
	public function getArrayValue() {
		return array(
			'latitude' => $this->latLong->getLatitude(),
			'longitude' => $this->latLong->getLongitude(),

			// The altitude field is no longer used in this class.
			// It is kept here for compatibility reasons.
			'altitude' => null,

			'precision' => $this->precision,
			'globe' => $this->globe,
		);
	}

	/**
	 * Constructs a new instance of the DataValue from the provided data.
	 * This can round-trip with @see getArrayValue
	 *
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return self
	 * @throws IllegalValueException
	 */
	public static function newFromArray( array $data ) {
		self::requireArrayFields( $data, array( 'latitude', 'longitude' ) );

		return new static(
			new LatLongValue(
				$data['latitude'],
				$data['longitude']
			),
			( isset( $data['precision'] ) ) ? $data['precision'] : null,
			( isset( $data['globe'] ) ) ? $data['globe'] : null
		);
	}

}
