<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Mapster element post types this integration may reference.
 *
 * @return string[]
 */
function tainacan_mapster_get_feature_post_types() {
	return [
		'mapster-wp-location',
		'mapster-wp-line',
		'mapster-wp-polygon',
	];
}

/**
 * Whether the current user (or public) may view a post in embed/public HTML.
 *
 * @param int $post_id Post ID.
 * @return bool
 */
function tainacan_mapster_user_can_view_post( $post_id ) {
	$post_id = absint( $post_id );
	if ( ! $post_id ) {
		return false;
	}

	$status = get_post_status( $post_id );
	if ( 'publish' === $status ) {
		return true;
	}

	return current_user_can( 'read_post', $post_id );
}

/**
 * Sanitize a list of element IDs to allowed, viewable Mapster element posts.
 *
 * @param int[]    $feature_ids       Candidate IDs.
 * @param string[] $allowed_post_types Optional CPT allow-list (defaults to all feature types).
 * @return int[]
 */
function tainacan_mapster_sanitize_feature_ids( $feature_ids, $allowed_post_types = null ) {
	if ( null === $allowed_post_types ) {
		$allowed_post_types = tainacan_mapster_get_feature_post_types();
	}

	$allowed_post_types = array_values(
		array_intersect(
			array_map( 'sanitize_key', (array) $allowed_post_types ),
			tainacan_mapster_get_feature_post_types()
		)
	);

	$sanitized = [];
	foreach ( (array) $feature_ids as $feature_id ) {
		$feature_id = absint( $feature_id );
		if ( ! $feature_id ) {
			continue;
		}

		$post_type = get_post_type( $feature_id );
		if ( ! in_array( $post_type, $allowed_post_types, true ) ) {
			continue;
		}

		if ( ! tainacan_mapster_user_can_view_post( $feature_id ) ) {
			continue;
		}

		$sanitized[] = $feature_id;
	}

	return array_values( array_unique( $sanitized ) );
}

/**
 * Build a safe CSS length from a numeric value and unit (e.g. ACF layout fields).
 *
 * @param mixed  $value   Numeric length.
 * @param string $unit    CSS unit.
 * @param string $default Fallback dimension (e.g. "320px").
 * @return string
 */
function tainacan_mapster_sanitize_css_dimension( $value, $unit, $default = '320px' ) {
	$allowed_units = [ 'px', '%', 'vh', 'vw', 'em', 'rem' ];
	$unit          = strtolower( trim( (string) $unit ) );

	if ( ! in_array( $unit, $allowed_units, true ) ) {
		$unit = 'px';
	}

	if ( ! is_numeric( $value ) ) {
		return $default;
	}

	$number = 0 + $value;
	if ( $number < 0 ) {
		return $default;
	}

	// Avoid scientific notation / junk in attributes.
	$formatted = ( floor( $number ) == $number )
		? (string) (int) $number
		: rtrim( rtrim( sprintf( '%.4F', $number ), '0' ), '.' );

	return $formatted . $unit;
}

/**
 * ACF field name that holds GeoJSON for a Mapster element post type.
 *
 * @param string $post_type Post type slug.
 * @return string Empty when unknown.
 */
function tainacan_mapster_get_element_geometry_field( $post_type ) {
	switch ( $post_type ) {
		case 'mapster-wp-location':
			return 'location';
		case 'mapster-wp-line':
			return 'line';
		case 'mapster-wp-polygon':
			return 'polygon';
		default:
			return '';
	}
}

/**
 * Build a GeoJSON Feature for one Mapster element post.
 *
 * @param int   $element_id       Mapster element post ID.
 * @param array $extra_properties Optional properties merged into the Feature.
 * @return array|null Feature array, or null when geometry is unavailable.
 */
function tainacan_mapster_get_element_geojson_feature( $element_id, $extra_properties = [] ) {
	$element_id = absint( $element_id );
	if ( ! $element_id || ! tainacan_mapster_user_can_view_post( $element_id ) ) {
		return null;
	}

	$post_type = get_post_type( $element_id );
	$field     = tainacan_mapster_get_element_geometry_field( $post_type );
	if ( ! $field || ! function_exists( 'get_field' ) ) {
		return null;
	}

	$raw = get_field( $field, $element_id );
	if ( is_string( $raw ) ) {
		if ( '' === trim( $raw ) ) {
			return null;
		}
		$decoded = json_decode( $raw );
	} elseif ( is_array( $raw ) || is_object( $raw ) ) {
		// ACF may already return decoded structures depending on field config.
		$decoded = json_decode( wp_json_encode( $raw ) );
	} else {
		return null;
	}

	if ( ! $decoded ) {
		return null;
	}

	$geometry = null;
	if ( isset( $decoded->type ) && 'FeatureCollection' === $decoded->type && ! empty( $decoded->features[0]->geometry ) ) {
		$geometry = $decoded->features[0]->geometry;
	} elseif ( isset( $decoded->type ) && 'Feature' === $decoded->type && ! empty( $decoded->geometry ) ) {
		$geometry = $decoded->geometry;
	} elseif ( isset( $decoded->type ) && isset( $decoded->coordinates ) ) {
		$geometry = $decoded;
	}

	if ( ! $geometry ) {
		return null;
	}

	$title = get_the_title( $element_id );
	$properties = array_merge(
		[
			'id'           => $element_id,
			'name'         => is_string( $title ) ? $title : '',
			'mapster_type' => $post_type,
		],
		(array) $extra_properties
	);

	return [
		'type'       => 'Feature',
		'geometry'   => $geometry,
		'properties' => $properties,
	];
}

/**
 * Build a GeoJSON FeatureCollection string for Mapster element IDs.
 *
 * @param int[] $element_ids      Mapster element post IDs.
 * @param array $extra_properties Properties merged into every Feature.
 * @return string JSON FeatureCollection, or empty string when nothing usable.
 */
function tainacan_mapster_get_elements_geojson_string( $element_ids, $extra_properties = [] ) {
	$features = [];

	foreach ( (array) $element_ids as $element_id ) {
		$feature = tainacan_mapster_get_element_geojson_feature( $element_id, $extra_properties );
		if ( $feature ) {
			$features[] = $feature;
		}
	}

	if ( empty( $features ) ) {
		return '';
	}

	$collection = [
		'type'     => 'FeatureCollection',
		'features' => $features,
	];

	$json = wp_json_encode( $collection );
	return is_string( $json ) ? $json : '';
}
