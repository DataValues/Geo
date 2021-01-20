<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Values;

use DataValues\DataValue;
use DataValues\IllegalValueException;
use InvalidArgumentException;

/**
 * Value Object representing a latitude-longitude pair with a certain precision on a certain globe.
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Thiemo Kreuz
 */
class GlobeCoordinateValue implements DataValue {

	private $latLong;

	/**
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

	public function getLatLong(): LatLongValue {
		return $this->latLong;
	}

	/**
	 * Returns the precision of the coordinate in degrees, e.g. 0.01.
	 */
	public function getPrecision(): ?float {
		return $this->precision;
	}

	/**
	 * Returns the IRI of the globe on which the location resides.
	 */
	public function getGlobe(): string {
		return $this->globe;
	}

	public function getLatitude(): float {
		return $this->latLong->getLatitude();
	}

	public function getLongitude(): float {
		return $this->latLong->getLongitude();
	}

	/**
	 * @param mixed $target
	 * @return bool
	 */
	public function equals( $target ): bool {
		return $target instanceof self
			&& $this->latLong->equals( $target->latLong )
			&& $this->precision === $target->precision
			&& $this->globe === $target->globe;
	}

	public function getCopy(): self {
		return new self(
			$this->latLong,
			$this->precision,
			$this->globe
		);
	}

	/**
	 * @since 2.0
	 */
	public function getHash(): string {
		return md5(
			$this->latLong->getLatitude() . '|'
			. $this->latLong->getLongitude() . '|'
			. (string)$this->precision . '|'
			. $this->globe
		);
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
	 * @throws InvalidArgumentException
	 */
	public function unserialize( $value ) {
		[ $latitude, $longitude, $altitude, $precision, $globe ] = json_decode( $value );
		$this->__construct( new LatLongValue( $latitude, $longitude ), $precision, $globe );
	}

	/**
	 * @see DataValue::getType
	 */
	public static function getType(): string {
		return 'globecoordinate';
	}

	/**
	 * @see DataValue::getSortKey
	 */
	public function getSortKey(): float {
		return $this->getLatitude();
	}

	/**
	 * @see DataValue::getValue
	 */
	public function getValue(): self {
		return $this;
	}

	/**
	 * @see DataValue::getArrayValue
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

	public function toArray(): array {
		return [
			'value' => $this->getArrayValue(),
			'type' => $this->getType(),
		];
	}

	public function withPrecision( ?float $precision ): self {
		return new self(
			$this->latLong,
			$precision,
			$this->globe
		);
	}

	/**
	 * Constructs a new instance from the provided array. Round-trips with @see getArrayValue.
	 *
	 * @param array $data
	 * @return self
	 * @throws InvalidArgumentException
	 */
	public static function newFromArray( $data ): self {
		if ( !is_array( $data ) ) {
			throw new IllegalValueException( 'array expected' );
		}

		if ( !array_key_exists( 'latitude', $data ) ) {
			throw new IllegalValueException( 'latitude field required' );
		}

		if ( !array_key_exists( 'longitude', $data ) ) {
			throw new IllegalValueException( 'longitude field required' );
		}

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
