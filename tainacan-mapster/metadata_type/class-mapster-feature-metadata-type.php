<?php

namespace TainacanMapster\Metadata_Types;

use Tainacan\Entities\Item_Metadata_Entity;
use Tainacan\Entities\Metadatum;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Mapster Map metadatum type.
 *
 * Stores Mapster element post ID(s) (location / line / polygon) and renders them
 * on a configured base Mapster map. Which element post types are accepted is
 * controlled by the `allowed_feature_types` option.
 */
class Mapster_Feature extends \Tainacan\Metadata_Types\Metadata_Type {

	const POST_TYPE_LOCATION = 'mapster-wp-location';
	const POST_TYPE_LINE     = 'mapster-wp-line';
	const POST_TYPE_POLYGON  = 'mapster-wp-polygon';
	const POST_TYPE_MAP      = 'mapster-wp-map';

	public function __construct() {
		parent::__construct();
		$this->set_name( __( 'Mapster Map', 'tainacan-mapster' ) );
		$this->set_description( __( 'Select Mapster locations, lines, and/or polygons and display them on a base Mapster map.', 'tainacan-mapster' ) );
		$this->set_primitive_type( 'string' );
		$this->set_component( 'tainacan-metadata-type-mapster-single-feature' );
		$this->set_form_component( 'tainacan-metadata-form-type-mapster-single-feature' );
		if ( method_exists( $this, 'set_manage_multiple_input' ) ) {
			$this->set_manage_multiple_input( true );
		}
		$this->set_default_options(
			[
				'mapster_map_id'         => '',
				'allowed_feature_types'  => [
					self::POST_TYPE_LOCATION,
					self::POST_TYPE_LINE,
					self::POST_TYPE_POLYGON,
				],
				'string_format'          => 'label',
			]
		);
		$preview_image = esc_url( TAINACAN_MAPSTER_PLUGIN_URL_PATH . 'assets/images/mapster-map-preview.png' );
		$this->set_preview_template(
			'<div>
				<div class="control">
					<img src="' . $preview_image . '" alt="' . esc_attr__( 'Preview of a Mapster map with selected map elements.', 'tainacan-mapster' ) . '" />
				</div>
			</div>'
		);
	}

	/**
	 * Allowed Mapster element post type slugs.
	 *
	 * @return string[]
	 */
	public static function get_available_feature_post_types() {
		return [
			self::POST_TYPE_LOCATION,
			self::POST_TYPE_LINE,
			self::POST_TYPE_POLYGON,
		];
	}

	/**
	 * Human-readable labels for Mapster element post types.
	 *
	 * @return array<string, string>
	 */
	public static function get_feature_post_type_labels() {
		return [
			self::POST_TYPE_LOCATION => __( 'Location', 'tainacan-mapster' ),
			self::POST_TYPE_LINE     => __( 'Line', 'tainacan-mapster' ),
			self::POST_TYPE_POLYGON  => __( 'Polygon', 'tainacan-mapster' ),
		];
	}

	/**
	 * Normalized list of allowed element post types for this metadatum.
	 *
	 * @return string[]
	 */
	public function get_allowed_feature_post_types() {
		$allowed  = $this->get_option( 'allowed_feature_types' );
		$available = self::get_available_feature_post_types();

		if ( ! is_array( $allowed ) ) {
			return $available;
		}

		$normalized = array_values(
			array_intersect(
				array_map( 'sanitize_key', $allowed ),
				$available
			)
		);

		return ! empty( $normalized ) ? $normalized : $available;
	}

	public function get_form_labels() {
		return [
			'mapster_map_id' => [
				'title'       => __( 'Base map', 'tainacan-mapster' ),
				'description' => __( 'The Mapster map used as the base to display the selected elements.', 'tainacan-mapster' ),
			],
			'allowed_feature_types' => [
				'title'       => __( 'Allowed element types', 'tainacan-mapster' ),
				'description' => __( 'Choose which Mapster element types this metadatum may reference: locations, lines, and/or polygons. Select one or more.', 'tainacan-mapster' ),
			],
			'string_format' => [
				'title'       => __( 'Plain-text / export format', 'tainacan-mapster' ),
				'description' => __( 'How this metadatum appears as text (REST value_as_string, CSV/XLSX exporters, etc.). “Labels” uses element titles; “GeoJSON” outputs a FeatureCollection of the selected elements.', 'tainacan-mapster' ),
			],
		];
	}

	public function validate_options( Metadatum $metadatum ) {
		$errors   = [];
		$map_id   = absint( $this->get_option( 'mapster_map_id' ) );
		$status   = $metadatum->get_status();
		$map_required = in_array( $status, [ 'publish', 'private' ], true );

		$allowed_raw = $this->get_option( 'allowed_feature_types' );
		$allowed     = is_array( $allowed_raw ) ? array_values( array_intersect( array_map( 'sanitize_key', $allowed_raw ), self::get_available_feature_post_types() ) ) : [];

		if ( empty( $allowed ) ) {
			$errors['allowed_feature_types'] = __( 'Select at least one Mapster element type (Location, Line, or Polygon).', 'tainacan-mapster' );
		}

		if ( $map_required && ! $map_id ) {
			$errors['mapster_map_id'] = __( 'The base map option is required.', 'tainacan-mapster' );
		}

		if ( $map_id && get_post_type( $map_id ) !== self::POST_TYPE_MAP ) {
			$errors['mapster_map_id'] = __( 'Selected map does not belong to Mapster maps.', 'tainacan-mapster' );
		}

		return empty( $errors ) ? true : $errors;
	}

	public function validate( Item_Metadata_Entity $item_metadata ) {
		$value = $item_metadata->get_value();

		if ( empty( $value ) ) {
			return true;
		}

		$allowed = $this->get_allowed_feature_post_types();
		$values  = is_array( $value ) ? $value : [ $value ];

		foreach ( $values as $single_value ) {
			$feature_id = absint( $single_value );
			if ( ! $feature_id ) {
				$this->add_error( __( 'Mapster map element value must be a numeric post ID.', 'tainacan-mapster' ) );
				return false;
			}

			$post_type = get_post_type( $feature_id );
			if ( ! in_array( $post_type, $allowed, true ) ) {
				$this->add_error( __( 'Selected post does not match the allowed Mapster element types for this metadatum.', 'tainacan-mapster' ) );
				return false;
			}

			if ( ! function_exists( 'tainacan_mapster_user_can_view_post' ) || ! tainacan_mapster_user_can_view_post( $feature_id ) ) {
				$this->add_error( __( 'Selected Mapster map element is not available.', 'tainacan-mapster' ) );
				return false;
			}
		}

		return true;
	}

	/**
	 * Plain-text or GeoJSON for stored Mapster element IDs.
	 *
	 * Format is controlled by the `string_format` metadatum option (`label` or
	 * `geojson`). Used by REST `value_as_string`, CSV/XLSX exporters, etc.
	 * Facet chip labels still need core support.
	 *
	 * @param Item_Metadata_Entity $item_metadata Item metadatum entity.
	 * @return string
	 */
	public function get_value_as_string( Item_Metadata_Entity $item_metadata ) {
		$value = $item_metadata->get_value();

		if ( empty( $value ) && '0' !== $value && 0 !== $value ) {
			return '';
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
			return '';
		}

		$format = $this->get_option( 'string_format' );
		if ( 'geojson' !== $format ) {
			$format = 'label';
		}

		/**
		 * Filter the string format used for Mapster Map `value_as_string`.
		 *
		 * @param string               $format         `label` or `geojson`.
		 * @param Item_Metadata_Entity $item_metadata  Item metadatum entity.
		 */
		$format = apply_filters( 'tainacan_mapster_value_as_string_format', $format, $item_metadata );

		if ( 'geojson' === $format && function_exists( 'tainacan_mapster_get_elements_geojson_string' ) ) {
			$extra = [];
			$item  = $item_metadata->get_item();
			if ( $item && method_exists( $item, 'get_id' ) && $item->get_id() ) {
				$extra['tainacan_item_id'] = (int) $item->get_id();
			}

			$return = tainacan_mapster_get_elements_geojson_string( $ids, $extra );
		} else {
			$labels = [];
			foreach ( $ids as $element_id ) {
				$label = $this->get_element_label( $element_id );
				if ( '' !== $label ) {
					$labels[] = $label;
				}
			}

			if ( empty( $labels ) ) {
				return '';
			}

			if ( $item_metadata->is_multiple() ) {
				$prefix    = $item_metadata->get_multivalue_prefix();
				$suffix    = $item_metadata->get_multivalue_suffix();
				$separator = $item_metadata->get_multivalue_separator();
				$parts     = [];

				foreach ( $labels as $label ) {
					$parts[] = $prefix . $label . $suffix;
				}

				$return = implode( $separator, $parts );
			} else {
				$return = $labels[0];
			}
		}

		/**
		 * Filter the string representation of a Mapster Map metadatum value.
		 *
		 * @param string               $return         Labels or GeoJSON string.
		 * @param Item_Metadata_Entity $item_metadata  Item metadatum entity.
		 */
		return apply_filters( 'tainacan-item-metadata-get-value-as-string--type-mapster-map', $return, $item_metadata );
	}

	/**
	 * Resolve a Mapster element post ID to a display label.
	 *
	 * @param int $element_id Mapster location / line / polygon post ID.
	 * @return string Empty when missing or not allowed/viewable.
	 */
	protected function get_element_label( $element_id ) {
		$element_id = absint( $element_id );
		if ( ! $element_id ) {
			return '';
		}

		$allowed = $this->get_allowed_feature_post_types();
		$post_type = get_post_type( $element_id );
		if ( ! in_array( $post_type, $allowed, true ) ) {
			return '';
		}

		if ( function_exists( 'tainacan_mapster_user_can_view_post' ) && ! tainacan_mapster_user_can_view_post( $element_id ) ) {
			return '';
		}

		$title = get_the_title( $element_id );
		if ( ! is_string( $title ) || '' === $title ) {
			return '#' . $element_id;
		}

		return $title;
	}

	public function get_value_as_html( Item_Metadata_Entity $item_metadata ) {
		$value  = $item_metadata->get_value();
		$map_id = absint( $this->get_option( 'mapster_map_id' ) );

		if ( empty( $value ) || ! $map_id ) {
			return '';
		}

		$feature_ids = array_values(
			array_filter(
				array_map(
					'absint',
					is_array( $value ) ? $value : [ $value ]
				)
			)
		);

		if ( function_exists( 'tainacan_mapster_sanitize_feature_ids' ) ) {
			$feature_ids = tainacan_mapster_sanitize_feature_ids(
				$feature_ids,
				$this->get_allowed_feature_post_types()
			);
		}

		if ( empty( $feature_ids ) ) {
			return '';
		}

		// Theme PHP output can boot Mapster via shortcode; SPA/REST needs the iframe.
		if ( function_exists( 'tainacan_mapster_should_use_embed_iframe' ) && tainacan_mapster_should_use_embed_iframe() ) {
			return $this->render_embed_iframe( $map_id, $feature_ids );
		}

		return $this->render_shortcode( $map_id, $feature_ids );
	}

	/**
	 * Outer iframe pointing at our same-origin Mapster embed document.
	 *
	 * @param int   $map_id      Mapster map post ID.
	 * @param int[] $feature_ids Feature post IDs.
	 * @return string
	 */
	protected function render_embed_iframe( $map_id, $feature_ids ) {
		if ( ! function_exists( 'tainacan_mapster_get_embed_url' ) ) {
			return $this->render_shortcode( $map_id, $feature_ids );
		}

		$feature_ids = function_exists( 'tainacan_mapster_sanitize_feature_ids' )
			? tainacan_mapster_sanitize_feature_ids( $feature_ids, $this->get_allowed_feature_post_types() )
			: array_values( array_filter( array_map( 'absint', (array) $feature_ids ) ) );

		if ( empty( $feature_ids ) ) {
			return '';
		}

		$url    = tainacan_mapster_get_embed_url( $map_id, $feature_ids );
		$height = '320px';
		$width  = '100%';

		if ( function_exists( 'get_field' ) && function_exists( 'tainacan_mapster_sanitize_css_dimension' ) ) {
			$height = tainacan_mapster_sanitize_css_dimension(
				get_field( 'layout_height', $map_id ),
				get_field( 'layout_height_units', $map_id ),
				'320px'
			);
			$width = tainacan_mapster_sanitize_css_dimension(
				get_field( 'layout_width', $map_id ),
				get_field( 'layout_width_units', $map_id ),
				'100%'
			);
		}

		return sprintf(
			'<div class="tainacan-mapster-embed" style="width:%1$s;height:%2$s;max-width:100%%;"><iframe class="tainacan-mapster-embed__frame" src="%3$s" title="%4$s" loading="lazy" style="width:100%%;height:100%%;border:0;display:block;" allowfullscreen></iframe></div>',
			esc_attr( $width ),
			esc_attr( $height ),
			esc_url( $url ),
			esc_attr__( 'Mapster map', 'tainacan-mapster' )
		);
	}

	/**
	 * Render Mapster shortcode for one or more feature IDs.
	 *
	 * @param int       $map_id      Mapster map post ID.
	 * @param int|int[] $feature_ids One feature ID or a list of feature IDs.
	 * @return string
	 */
	protected function render_shortcode( $map_id, $feature_ids ) {
		$feature_ids = array_values(
			array_filter(
				array_map( 'absint', (array) $feature_ids )
			)
		);

		if ( empty( $feature_ids ) ) {
			return '';
		}

		if ( 1 === count( $feature_ids ) ) {
			$shortcode = sprintf(
				'[mapster_wp_map id="%d" single_feature_id="%d"]',
				$map_id,
				$feature_ids[0]
			);
		} else {
			$shortcode = sprintf(
				'[mapster_wp_map id="%d" feature_ids="%s"]',
				$map_id,
				implode( ',', $feature_ids )
			);
		}

		$styles = function_exists( 'tainacan_mapster_get_frontend_styles_html' )
			? tainacan_mapster_get_frontend_styles_html()
			: '';

		// Wrapper + inline CSS keep the map block-level when themes style
		// metadata values as inline-block (which collapses Mapster to 0×0).
		return $styles . sprintf(
			'<div class="tainacan-mapster-map">%s</div>',
			do_shortcode( $shortcode )
		);
	}
}
