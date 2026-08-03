<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Mapster feature post types this integration may reference.
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
 * Sanitize a list of feature IDs to allowed, viewable Mapster feature posts.
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
