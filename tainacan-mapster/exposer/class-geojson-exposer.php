<?php

namespace TainacanMapster\Exposer;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Entities\Item;
use TainacanMapster\GeoJSON\Feature_Builder;

/**
 * Expose items as GeoJSON via the Tainacan REST API (?exposer=mapster-geojson).
 */
class GeoJSON extends \Tainacan\Exposers\Exposer {

	public $slug = 'mapster-geojson';

	public $accept_no_mapper = true;

	/**
	 * Accept all mappers (native shape when none is requested).
	 *
	 * @var bool
	 */
	protected $mappers = true;

	public function __construct() {
		$this->set_name( __( 'Mapster GeoJSON', 'tainacan-mapster' ) );
		$this->set_description(
			__( 'GeoJSON FeatureCollection of Mapster Map elements (one Feature per map element).', 'tainacan-mapster' )
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rest_request_after_callbacks( $response, $handler, $request ) {
		require_once TAINACAN_MAPSTER_PLUGIN_DIR_PATH . '/inc/class-geojson-feature-builder.php';

		$builder = new Feature_Builder( $this->options_from_request( $request ) );
		$features = [];

		foreach ( $this->extract_item_ids( $response->get_data() ) as $item_id ) {
			$item = \Tainacan\Repositories\Items::get_instance()->fetch( (int) $item_id, 'OBJECT' );
			if ( ! ( $item instanceof Item ) ) {
				continue;
			}

			foreach ( $builder->features_from_item( $item ) as $feature ) {
				$features[] = $feature;
			}
		}

		$collection = [
			'type'     => 'FeatureCollection',
			'features' => $features,
		];

		$json = wp_json_encode( $collection );
		if ( ! is_string( $json ) ) {
			$json = '{"type":"FeatureCollection","features":[]}';
		}

		$response->set_headers(
			[
				'Content-Type: application/geo+json; charset=' . get_option( 'blog_charset' ),
			]
		);
		// addslashes: URL-param exposer path runs stripcslashes before echo (same as JSON_flat).
		$response->set_data( addslashes( $json ) );

		return $response;
	}

	/**
	 * Query options mirroring the Mapster GeoJSON exporter.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return array
	 */
	protected function options_from_request( $request ) {
		$include = $request->get_param( 'include_item_metadata' );
		if ( null === $include || '' === $include ) {
			$include = '1';
		}

		$format = $request->get_param( 'property_value_format' );
		if ( ! is_string( $format ) || ! in_array( $format, [ 'string', 'json' ], true ) ) {
			$format = 'string';
		}

		$delimiter = $request->get_param( 'multivalued_delimiter' );
		if ( ! is_string( $delimiter ) || '' === $delimiter ) {
			$delimiter = '||';
		}

		return [
			'include_item_metadata' => $include,
			'property_value_format' => $format,
			'multivalued_delimiter' => $delimiter,
			'mapster_metadatum'     => absint( $request->get_param( 'mapster_metadatum' ) ),
		];
	}

	/**
	 * Item IDs from a collection list or single-item REST payload.
	 *
	 * @param mixed $data Response data.
	 * @return int[]
	 */
	protected function extract_item_ids( $data ) {
		if ( ! is_array( $data ) ) {
			return [];
		}

		$items = [];
		if ( isset( $data['items'] ) && is_array( $data['items'] ) ) {
			$items = $data['items'];
		} elseif ( isset( $data['id'] ) ) {
			$items = [ $data ];
		}

		$ids = [];
		foreach ( $items as $item ) {
			if ( is_array( $item ) && isset( $item['id'] ) ) {
				$id = absint( $item['id'] );
				if ( $id ) {
					$ids[] = $id;
				}
			}
		}

		return $ids;
	}
}
