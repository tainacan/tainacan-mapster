<?php
/**
 * Unit tests for Mapster GeoJSON exposer parsing helpers.
 *
 * @package Tainacan_Mapster
 */

use TainacanMapster\Exposer\GeoJSON;

/**
 * Expose protected exposer helpers.
 */
class GeoJSON_Exposer_Testable extends GeoJSON {

	/**
	 * @param mixed $data Response data.
	 * @return int[]
	 */
	public function expose_extract_item_ids( $data ) {
		return $this->extract_item_ids( $data );
	}

	/**
	 * @param WP_REST_Request $request Request.
	 * @return array
	 */
	public function expose_options_from_request( $request ) {
		return $this->options_from_request( $request );
	}
}

/**
 * @covers \TainacanMapster\Exposer\GeoJSON
 */
class GeoJSON_Exposer_Test extends PHPUnit\Framework\TestCase {

	public function test_slug_and_labels() {
		$exposer = new GeoJSON_Exposer_Testable();

		$this->assertSame( 'mapster-geojson', $exposer->slug );
		$this->assertSame( 'Mapster GeoJSON', $exposer->get_name() );
		$this->assertNotEmpty( $exposer->get_description() );
	}

	public function test_extract_item_ids_from_collection_payload() {
		$exposer = new GeoJSON_Exposer_Testable();

		$ids = $exposer->expose_extract_item_ids(
			[
				'items' => [
					[ 'id' => 10 ],
					[ 'id' => '20' ],
					[ 'name' => 'no-id' ],
					[ 'id' => 0 ],
				],
			]
		);

		$this->assertSame( [ 10, 20 ], $ids );
	}

	public function test_extract_item_ids_from_single_item_payload() {
		$exposer = new GeoJSON_Exposer_Testable();

		$this->assertSame( [ 42 ], $exposer->expose_extract_item_ids( [ 'id' => 42, 'title' => 'X' ] ) );
		$this->assertSame( [], $exposer->expose_extract_item_ids( null ) );
		$this->assertSame( [], $exposer->expose_extract_item_ids( 'bad' ) );
		$this->assertSame( [], $exposer->expose_extract_item_ids( [] ) );
	}

	public function test_options_from_request_defaults() {
		$exposer = new GeoJSON_Exposer_Testable();
		$options = $exposer->expose_options_from_request( new WP_REST_Request() );

		$this->assertSame(
			[
				'include_item_metadata' => '1',
				'property_value_format' => 'string',
				'multivalued_delimiter' => '||',
				'mapster_metadatum'     => 0,
			],
			$options
		);
	}

	public function test_options_from_request_custom_values() {
		$exposer = new GeoJSON_Exposer_Testable();
		$options = $exposer->expose_options_from_request(
			new WP_REST_Request(
				[
					'include_item_metadata' => '0',
					'property_value_format' => 'json',
					'multivalued_delimiter' => ';;',
					'mapster_metadatum'     => '55',
				]
			)
		);

		$this->assertSame( '0', $options['include_item_metadata'] );
		$this->assertSame( 'json', $options['property_value_format'] );
		$this->assertSame( ';;', $options['multivalued_delimiter'] );
		$this->assertSame( 55, $options['mapster_metadatum'] );
	}

	public function test_options_from_request_rejects_invalid_format() {
		$exposer = new GeoJSON_Exposer_Testable();
		$options = $exposer->expose_options_from_request(
			new WP_REST_Request( [ 'property_value_format' => 'xml' ] )
		);

		$this->assertSame( 'string', $options['property_value_format'] );
	}
}
