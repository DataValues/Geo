<?php

declare( strict_types = 1 );

namespace DataValues\Geo\Values;

use DataValues\DataValue;
use DataValues\IllegalValueException;
use InvalidArgumentException;

/**
 * Value Object representing a geographical point.
 *
 * Latitude is specified in degrees within the range [-360, 360].
 * Longitude is specified in degrees within the range [-360, 360].
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class LatLongValue implements DataValue {

	private $latitude;
	private $longitude;

	/**
	 * @param float|int $latitude Latitude in degrees within the range [-360, 360]
	 * @param float|int $longitude Longitude in degrees within the range [-360, 360]
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( float $latitude, float $longitude ) {
		$this->assertIsLatitude( $latitude );
		$this->assertIsLongitude( $longitude );

		$this->latitude = $latitude;
		$this->longitude = $longitude;
	}

	private function assertIsLatitude( float $latitude ) {
		if ( $latitude < -360 || $latitude > 360 ) {
			throw new InvalidArgumentException( 'Latitude needs to be between -360 and 360' );
		}
	}

	private function assertIsLongitude( float $longitude ) {
		if ( $longitude < -360 || $longitude > 360 ) {
			throw new InvalidArgumentException( 'Longitude needs to be between -360 and 360' );
		}
	}

	public function getLatitude(): float {
		return $this->latitude;
	}

	public function getLongitude(): float {
		return $this->longitude;
	}

	/**
	 * @param mixed $target
	 * @return bool
	 */
	public function equals( $target ): bool {
		return $target instanceof self
			&& $this->latitude === $target->latitude
			&& $this->longitude === $target->longitude;
	}

	public function getHash(): string {
		return md5( $this->getSerializationForHash() );
	}

	/**
	 * Get legacy PHP serialization (as PHP up to version 7.3 would produce).
	 * This is used for self::getHash to make sure hashes stay consistent.
	 * It must not be used to produce serialization meant to be deserialized.
	 *
	 * @return string
	 */
	public function getSerializationForHash(): string {
		$data = $this->serialize();
		return 'C:' . strlen( static::class ) . ':"' . static::class .
		       '":' . strlen( $data ) . ':{' . $data . '}';
	}

	public function getCopy(): self {
		return new self( $this->latitude, $this->longitude );
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize(): string {
		return implode( '|', $this->__serialize() );
	}

	public function __serialize(): array {
		return [ $this->latitude, $this->longitude ];
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $value
	 *
	 * @throws InvalidArgumentException
	 */
	public function unserialize( $value ) {
		$this->__unserialize( explode( '|', $value, 2 ) );
	}

	public function __unserialize( array $data ): void {
		if ( count( $data ) < 2 ) {
			throw new InvalidArgumentException( 'Invalid serialization provided in ' . __METHOD__ );
		}

		$this->__construct( (float)$data[0], (float)$data[1] );
	}

	public static function getType(): string {
		return 'geocoordinate';
	}

	public function getSortKey(): float {
		return $this->latitude;
	}

	public function getValue(): self {
		return $this;
	}

	/**
	 * @return float[]
	 */
	public function getArrayValue(): array {
		return [
			'latitude' => $this->latitude,
			'longitude' => $this->longitude
		];
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

		return new self( $data['latitude'], $data['longitude'] );
	}

	public function toArray(): array {
		return [
			'value' => $this->getArrayValue(),
			'type' => $this->getType(),
		];
	}

}
