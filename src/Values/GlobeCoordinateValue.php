<?php

namespace DataValues\Geo\Values;

use DataValues\DataValueObject;
use DataValues\IllegalValueException;

/**
 * Class representing a geographical coordinate value.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class GlobeCoordinateValue extends DataValueObject {

	/**
	 * @var LatLongValue
	 */
	private $latLong;

	/**
	 * The precision of the coordinate in degrees, e.g. 0.01.
	 *
	 * @var float|null
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
	public const GLOBE_EARTH = 'http://www.wikidata.org/entity/Q2';

	/**
	 * @param LatLongValue $latLong
	 * @param float|int|null $precision in degrees, e.g. 0.01.
	 * @param string|null $globe IRI, defaults to 'http://www.wikidata.org/entity/Q2'.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( LatLongValue $latLong, float $precision = null, string $globe = null ) {
		$this->assertIsPrecision( $precision );

		if ( $globe === null ) {
			$globe = self::GLOBE_EARTH;
		} elseif ( $globe === '' ) {
			throw new IllegalValueException( '$globe must be a non-empty string or null' );
		}

		$this->latLong = $latLong;
		$this->precision = $precision;
		$this->globe = $globe;
	}

	private function assertIsPrecision( ?float $precision ) {
		if ( is_float( $precision ) && ( $precision < -360 || $precision > 360 ) ) {
			throw new IllegalValueException( '$precision needs to be between -360 and 360' );
		}
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize(): string {
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
	public static function getType(): string {
		return 'globecoordinate';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @return float
	 */
	public function getSortKey(): float {
		return $this->getLatitude();
	}

	public function getLatitude(): float {
		return $this->latLong->getLatitude();
	}

	public function getLongitude(): float {
		return $this->latLong->getLongitude();
	}

	/**
	 * @see DataValue::getValue
	 *
	 * @return self
	 */
	public function getValue(): self {
		return $this;
	}

	public function getLatLong(): LatLongValue {
		return $this->latLong;
	}

	/**
	 * Returns the precision of the coordinate in degrees, e.g. 0.01.
	 *
	 * @return float|int|null
	 */
	public function getPrecision(): ?float {
		return $this->precision;
	}

	/**
	 * Returns the IRI of the globe on which the location resides.
	 *
	 * @return string
	 */
	public function getGlobe(): string {
		return $this->globe;
	}

	/**
	 * @see Hashable::getHash
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function getHash(): string {
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
	public function getArrayValue(): array {
		return [
			'latitude' => $this->latLong->getLatitude(),
			'longitude' => $this->latLong->getLongitude(),

			// The altitude field is no longer used in this class.
			// It is kept here for compatibility reasons.
			'altitude' => null,

			'precision' => $this->precision,
			'globe' => $this->globe,
		];
	}

	/**
	 * Constructs a new instance from the provided data. Required for @see DataValueDeserializer.
	 * This is expected to round-trip with @see getArrayValue.
	 *
	 * @deprecated since 2.0.1. Static DataValue::newFromArray constructors like this are
	 *  underspecified (not in the DataValue interface), and misleadingly named (should be named
	 *  newFromArrayValue). Instead, use DataValue builder callbacks in @see DataValueDeserializer.
	 *
	 * @param mixed $data Warning! Even if this is expected to be a value as returned by
	 *  @see getArrayValue, callers of this specific newFromArray implementation can not guarantee
	 *  this. This is not even guaranteed to be an array!
	 *
	 * @throws IllegalValueException if $data is not in the expected format. Subclasses of
	 *  InvalidArgumentException are expected and properly handled by @see DataValueDeserializer.
	 * @return self
	 */
	public static function newFromArray( $data ): self {
		self::requireArrayFields( $data, [ 'latitude', 'longitude' ] );

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
