<?php

// This is a IDE helper to understand class aliasing.
// It should not be included anywhere.
// Actual aliasing happens in the entry point using class_alias.

namespace { throw new Exception( 'This code is not meant to be executed' ); }

namespace DataValues {

	class LatLongValue extends \DataValues\Geo\Values\LatLongValue {}

	class GlobeCoordinateValue extends \DataValues\Geo\Values\GlobeCoordinateValue {}

}
