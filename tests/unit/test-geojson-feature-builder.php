<?php
/**
 * Unit tests for GeoJSON Feature_Builder options / helpers.
 *
 * @package Tainacan_Mapster
 */

use TainacanMapster\GeoJSON\Feature_Builder;

/**
 * Expose protected builders for unit assertions.
 */
class Feature_Builder_Testable extends Feature_Builder {

	/**
	 * @return bool
	 */
	public function expose_should_include_item_metadata() {
		return $this->should_include_item_metadata();
	}

	/**
	 * @return bool
	 */
	public function expose_should_use_json_property_values() {
		return $this->should_use_json_property_values();
	}

	/**
	 * @return string[]
	 */
	public function expose_reserved_property_keys() {
		return $this->get_reserved_property_keys();
	}

	/**
	 * @param object $metadatum Metadatum stub.
	 * @return bool
	 */
	public function expose_is_mapster_map_metadatum( $metadatum ) {
		return $this->is_mapster_map_metadatum( $metadatum );
	}
}

/**
 * Minimal metadatum stub.
 */
class Metadatum_Type_Stub {
	/**
	 * @var string
	 */
	private $type;

	/**
	 * @param string $type Metadata type class name.
	 */
	public function __construct( $type ) {
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function get_metadata_type() {
		return $this->type;
	}
}

/**
 * @covers \TainacanMapster\GeoJSON\Feature_Builder
 */
class Feature_Builder_Test extends PHPUnit\Framework\TestCase {

	public function test_default_options() {
		$builder = new Feature_Builder_Testable();

		$this->assertTrue( $builder->expose_should_include_item_metadata() );
		$this->assertFalse( $builder->expose_should_use_json_property_values() );
		$this->assertSame( '||', $builder->filter_multivalue_separator( ',' ) );
	}

	public function test_include_item_metadata_truthy_values() {
		foreach ( [ true, '1', 'yes' ] as $value ) {
			$builder = new Feature_Builder_Testable( [ 'include_item_metadata' => $value ] );
			$this->assertTrue( $builder->expose_should_include_item_metadata(), (string) $value );
		}

		foreach ( [ false, '0', 'no', '' ] as $value ) {
			$builder = new Feature_Builder_Testable( [ 'include_item_metadata' => $value ] );
			$this->assertFalse( $builder->expose_should_include_item_metadata(), (string) json_encode( $value ) );
		}
	}

	public function test_property_value_format_json() {
		$builder = new Feature_Builder_Testable( [ 'property_value_format' => 'json' ] );
		$this->assertTrue( $builder->expose_should_use_json_property_values() );
	}

	public function test_multivalued_delimiter_fallback() {
		$builder = new Feature_Builder_Testable( [ 'multivalued_delimiter' => '' ] );
		$this->assertSame( '||', $builder->filter_multivalue_separator( ',' ) );

		$builder = new Feature_Builder_Testable( [ 'multivalued_delimiter' => ';;' ] );
		$this->assertSame( ';;', $builder->filter_multivalue_separator( ',' ) );
	}

	public function test_reserved_property_keys() {
		$builder = new Feature_Builder_Testable();
		$keys    = $builder->expose_reserved_property_keys();

		$this->assertContains( 'tainacan_item_id', $keys );
		$this->assertContains( 'metadatum_slug', $keys );
		$this->assertContains( 'mapster_type', $keys );
		$this->assertContains( 'id', $keys );
	}

	public function test_is_mapster_map_metadatum() {
		$builder = new Feature_Builder_Testable();

		$this->assertTrue(
			$builder->expose_is_mapster_map_metadatum(
				new Metadatum_Type_Stub( Feature_Builder::MAPSTER_TYPE )
			)
		);
		$this->assertTrue(
			$builder->expose_is_mapster_map_metadatum(
				new Metadatum_Type_Stub( 'Some\\Mapster_Feature' )
			)
		);
		$this->assertFalse(
			$builder->expose_is_mapster_map_metadatum(
				new Metadatum_Type_Stub( 'Tainacan\\Metadata_Types\\Text' )
			)
		);
	}
}
