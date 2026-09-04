<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Read a sanitized string from $_GET without requiring a nonce.
 *
 * Embed URLs are public, shareable iframe targets (like attachment HTML pages).
 * Capability checks happen when rendering.
 *
 * @param string $key     Query arg name.
 * @param string $default Default when missing.
 * @return string
 */
function tainacan_mapster_get_query_arg( $key, $default = '' ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public GET embed endpoint; no form submission.
	if ( ! isset( $_GET[ $key ] ) ) {
		return $default;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Public GET embed endpoint; no form submission.
	return sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
}

/**
 * Build the URL for the bare Mapster embed page.
 *
 * Query-arg based so it works without flushing rewrite rules.
 *
 * @param int   $map_id      Mapster map post ID.
 * @param int[] $feature_ids Feature post IDs.
 * @return string
 */
function tainacan_mapster_get_embed_url( $map_id, $feature_ids ) {
	$feature_ids = array_values(
		array_filter(
			array_map( 'absint', (array) $feature_ids )
		)
	);

	$args = [
		'tainacan_mapster_embed' => '1',
		'map_id'                 => absint( $map_id ),
	];

	if ( 1 === count( $feature_ids ) ) {
		$args['single_feature_id'] = $feature_ids[0];
	} elseif ( ! empty( $feature_ids ) ) {
		$args['feature_ids'] = implode( ',', $feature_ids );
	}

	return add_query_arg( $args, home_url( '/' ) );
}

/**
 * Whether Mapster HTML should be rendered via iframe embed.
 *
 * Use the embed when the HTML will be injected later (REST / admin / AJAX),
 * where Mapster's shortcode boot cannot run on the parent document.
 * On a normal theme PHP render, prefer the shortcode directly.
 *
 * @return bool
 */
function tainacan_mapster_should_use_embed_iframe() {
	$use_embed = false;

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		$use_embed = true;
	} elseif ( is_admin() ) {
		$use_embed = true;
	} elseif ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
		$use_embed = true;
	}

	/**
	 * Filter whether Mapster Map metadata HTML uses the iframe embed.
	 *
	 * @param bool $use_embed True to render an iframe; false for do_shortcode().
	 */
	return (bool) apply_filters( 'tainacan_mapster_should_use_embed_iframe', $use_embed );
}

/**
 * Whether the current request is our Mapster embed document.
 *
 * @return bool
 */
function tainacan_mapster_is_embed_request() {
	return '1' === tainacan_mapster_get_query_arg( 'tainacan_mapster_embed' );
}

/**
 * Allow same-origin framing (Tainacan admin / theme iframes).
 */
function tainacan_mapster_embed_frame_headers() {
	if ( ! tainacan_mapster_is_embed_request() ) {
		return;
	}

	header_remove( 'X-Frame-Options' );
	header( "Content-Security-Policy: frame-ancestors 'self'" );
}

add_action( 'send_headers', 'tainacan_mapster_embed_frame_headers' );

/**
 * Render a minimal document that only outputs the Mapster shortcode.
 */
function tainacan_mapster_render_embed_document() {
	if ( ! tainacan_mapster_is_embed_request() ) {
		return;
	}

	if ( ! tainacan_mapster_has_dependencies() ) {
		status_header( 503 );
		wp_die( esc_html__( 'Mapster or Tainacan is not available.', 'tainacan-mapster' ), '', [ 'response' => 503 ] );
	}

	$map_id = absint( tainacan_mapster_get_query_arg( 'map_id' ) );
	if ( ! $map_id || get_post_type( $map_id ) !== 'mapster-wp-map' ) {
		status_header( 404 );
		wp_die( esc_html__( 'Map not found.', 'tainacan-mapster' ), '', [ 'response' => 404 ] );
	}

	$map_status = get_post_status( $map_id );
	if ( 'publish' !== $map_status && ! current_user_can( 'read_post', $map_id ) ) {
		status_header( 403 );
		wp_die( esc_html__( 'You are not allowed to view this map.', 'tainacan-mapster' ), '', [ 'response' => 403 ] );
	}

	$single_feature_id = absint( tainacan_mapster_get_query_arg( 'single_feature_id' ) );
	$feature_ids_raw   = tainacan_mapster_get_query_arg( 'feature_ids' );
	$feature_ids       = [];

	if ( $single_feature_id ) {
		$feature_ids = [ $single_feature_id ];
	} elseif ( $feature_ids_raw !== '' ) {
		$feature_ids = array_map( 'absint', explode( ',', $feature_ids_raw ) );
	}

	$feature_ids = tainacan_mapster_sanitize_feature_ids( $feature_ids );

	if ( empty( $feature_ids ) ) {
		// Base map only (e.g. items-list view mode with no located items on the page).
		$shortcode = sprintf( '[mapster_wp_map id="%d"]', $map_id );
	} elseif ( 1 === count( $feature_ids ) ) {
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

	status_header( 200 );
	nocache_headers();
	show_admin_bar( false );

	?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo esc_html( get_the_title( $map_id ) ); ?></title>
	<?php wp_head(); ?>
	<style>
		html, body {
			margin: 0;
			padding: 0;
			width: 100%;
			height: 100%;
			overflow: hidden;
			background: #fff;
		}
		/* Hide theme chrome; keep Mapster container visible. Do not force
		   display on descendants — Mapster must hide its own loading overlay. */
		body > *:not(.mapster-wp-maps-container):not(script):not(style):not(link) {
			display: none !important;
		}
		.mapster-wp-maps-container {
			display: block !important;
			position: absolute !important;
			inset: 0 !important;
			width: 100% !important;
			height: 100% !important;
			margin: 0 !important;
		}
		.mapster-wp-maps-loader-container,
		.mapster-wp-maps {
			width: 100% !important;
			height: 100% !important;
			max-width: none !important;
		}
		.mapster-wp-maps {
			position: absolute !important;
			inset: 0 !important;
		}
	</style>
</head>
<body>
	<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Mapster shortcode HTML ?>
	<?php wp_footer(); ?>
</body>
</html>
	<?php
	exit;
}

add_action( 'template_redirect', 'tainacan_mapster_render_embed_document', 0 );
