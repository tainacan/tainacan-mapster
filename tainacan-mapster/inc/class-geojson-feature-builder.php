<?php

namespace TainacanMapster\GeoJSON;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Entities\Item;
use Tainacan\Entities\Item_Metadata_Entity;

/**
 * Build GeoJSON Features from a Tainacan item (shared by exporter and exposer).
 */
class Feature_Builder {

	const MAPSTER_TYPE = 'TainacanMapster\\Metadata_Types\\Mapster_Feature';

	/**
	 * @var array{
	 *   include_item_metadata?: bool,
	 *   property_value_format?: string,
	 *   multivalued_delimiter?: string,
	 *   mapster_metadatum?: int
	 * }
	 */
	protected $options;

	/**
	 * @param array $options Builder options (same semantics as the Mapster GeoJSON exporter).
	 */
	public function __construct( array $options = [] ) {
		$this->options = array_merge(
			[
				'include_item_metadata' => true,
				'property_value_format' => 'string',
				'multivalued_delimiter' => '||',
				'mapster_metadatum'     => 0,
			],
			$options
		);
	}

	/**
	 * @param Item $item Item entity.
	 * @return array[] GeoJSON Feature arrays.
	 */
	public function features_from_item( Item $item ) {
		$features   = [];
		$item_props = $this->build_item_core_properties( $item );
		$metadata   = $item->get_metadata();
		$extra      = [];

		if ( $this->should_include_item_metadata() ) {
			$use_string = ! $this->should_use_json_property_values();
			if ( $use_string ) {
				add_filter( 'tainacan-item-metadata-get-multivalue-separator', [ $this, 'filter_multivalue_separator' ], 20 );
			}
			$extra = $this->build_item_metadata_properties( $metadata );
			if ( $use_string ) {
				remove_filter( 'tainacan-item-metadata-get-multivalue-separator', [ $this, 'filter_multivalue_separator' ], 20 );
			}
		}

		$filter_metadatum = absint( $this->options['mapster_metadatum'] ?? 0 );

		foreach ( (array) $metadata as $item_metadata ) {
			if ( ! ( $item_metadata instanceof Item_Metadata_Entity ) ) {
				continue;
			}

			$metadatum = $item_metadata->get_metadatum();
			if ( ! $metadatum || ! $this->is_mapster_map_metadatum( $metadatum ) ) {
				continue;
			}

			if ( $filter_metadatum && (int) $metadatum->get_id() !== $filter_metadatum ) {
				continue;
			}

			$value = $item_metadata->get_value();
			if ( empty( $value ) && '0' !== $value && 0 !== $value ) {
				continue;
			}

			$ids = array_values(
				array_filter(
					array_map(
						'absint',
						is_array( $value ) ? $value : [ $value ]
					)
				)
			);

			if ( empty( $ids ) ) {
				continue;
			}

			$geometry_source_props = [
				'metadatum_id'   => (int) $metadatum->get_id(),
				'metadatum_name' => (string) $metadatum->get_name(),
				'metadatum_slug' => (string) $metadatum->get_slug(),
			];

			foreach ( $ids as $element_id ) {
				$merged  = array_merge( $item_props, $extra, $geometry_source_props );
				$feature = tainacan_mapster_get_element_geojson_feature( $element_id, $merged );
				if ( $feature ) {
					$features[] = $feature;
				}
			}
		}

		return $features;
	}

	/**
	 * Multivalue separator for get_value_as_string().
	 *
	 * @param string $separator Default separator.
	 * @return string
	 */
	public function filter_multivalue_separator( $separator ) {
		$custom = $this->options['multivalued_delimiter'] ?? '||';
		return ( is_string( $custom ) && '' !== $custom ) ? $custom : '||';
	}

	/**
	 * @return bool
	 */
	protected function should_include_item_metadata() {
		$option = $this->options['include_item_metadata'] ?? true;
		return true === $option || '1' === (string) $option || 'yes' === $option;
	}

	/**
	 * @return bool
	 */
	protected function should_use_json_property_values() {
		return 'json' === ( $this->options['property_value_format'] ?? 'string' );
	}

	/**
	 * @param \Tainacan\Entities\Metadatum $metadatum Metadatum entity.
	 * @return bool
	 */
	protected function is_mapster_map_metadatum( $metadatum ) {
		$type = $metadatum->get_metadata_type();
		if ( self::MAPSTER_TYPE === $type ) {
			return true;
		}

		return is_string( $type ) && false !== strpos( $type, 'Mapster_Feature' );
	}

	/**
	 * @return string[]
	 */
	protected function get_reserved_property_keys() {
		return [
			'tainacan_item_id',
			'tainacan_item_title',
			'tainacan_item_status',
			'tainacan_item_url',
			'metadatum_id',
			'metadatum_name',
			'metadatum_slug',
			'id',
			'name',
			'mapster_type',
		];
	}

	/**
	 * @param Item $item Item entity.
	 * @return array<string, mixed>
	 */
	protected function build_item_core_properties( Item $item ) {
		$item_id = (int) $item->get_id();

		return [
			'tainacan_item_id'     => $item_id,
			'tainacan_item_title'  => (string) $item->get_title(),
			'tainacan_item_status' => (string) $item->get_status(),
			'tainacan_item_url'    => $item_id ? get_permalink( $item_id ) : '',
		];
	}

	/**
	 * @param Item_Metadata_Entity[] $metadata Item metadata list.
	 * @return array<string, mixed>
	 */
	protected function build_item_metadata_properties( $metadata ) {
		$props    = [];
		$reserved = array_fill_keys( $this->get_reserved_property_keys(), true );
		$as_json  = $this->should_use_json_property_values();

		foreach ( (array) $metadata as $item_metadata ) {
			if ( ! ( $item_metadata instanceof Item_Metadata_Entity ) ) {
				continue;
			}

			$metadatum = $item_metadata->get_metadatum();
			if ( ! $metadatum || $this->is_mapster_map_metadatum( $metadatum ) ) {
				continue;
			}

			$slug = (string) $metadatum->get_slug();
			if ( '' === $slug || isset( $reserved[ $slug ] ) ) {
				continue;
			}

			$value = $item_metadata->get_value();
			if ( empty( $value ) && '0' !== $value && 0 !== $value ) {
				continue;
			}

			if ( $as_json ) {
				$encoded_value = $this->get_metadata_value_as_json( $item_metadata );
				if ( null === $encoded_value ) {
					continue;
				}
				$props[ $slug ] = $encoded_value;
				continue;
			}

			$string = $item_metadata->get_value_as_string();
			if ( ! is_string( $string ) || '' === $string ) {
				continue;
			}

			$props[ $slug ] = $string;
		}

		return $props;
	}

	/**
	 * @param Item_Metadata_Entity $item_metadata Item metadatum entity.
	 * @return mixed|null
	 */
	protected function get_metadata_value_as_json( Item_Metadata_Entity $item_metadata ) {
		if ( ! method_exists( $item_metadata, 'get_value_as_array' ) ) {
			$string = $item_metadata->get_value_as_string();
			return ( is_string( $string ) && '' !== $string ) ? $string : null;
		}

		$raw = $item_metadata->get_value_as_array();
		if ( null === $raw || ( is_array( $raw ) && [] === $raw ) || '' === $raw ) {
			return null;
		}

		$json = wp_json_encode( $raw );
		if ( ! is_string( $json ) ) {
			return null;
		}

		$decoded = json_decode( $json, true );
		return ( null === $decoded && 'null' !== $json ) ? null : $decoded;
	}
}
