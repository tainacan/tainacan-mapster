<?php
/**
 * Minimal WordPress function stubs for unit tests (no WP bootstrap).
 *
 * @package Tainacan_Mapster
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/**
 * Reset post fixtures between tests.
 */
function tainacan_mapster_test_reset_posts() {
	$GLOBALS['tainacan_mapster_test_posts']    = [];
	$GLOBALS['tainacan_mapster_test_can_read'] = false;
	$GLOBALS['tainacan_mapster_test_titles']   = [];
	$GLOBALS['tainacan_mapster_test_permalinks'] = [];
}

tainacan_mapster_test_reset_posts();

if ( ! function_exists( 'absint' ) ) {
	/**
	 * @param mixed $value Value.
	 * @return int
	 */
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/**
	 * @param string $key Key.
	 * @return string
	 */
	function sanitize_key( $key ) {
		$key = strtolower( (string) $key );
		return preg_replace( '/[^a-z0-9_\-]/', '', $key );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/**
	 * @param string $str String.
	 * @return string
	 */
	function sanitize_text_field( $str ) {
		return trim( strip_tags( (string) $str ) );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/**
	 * @param string|array $value Value.
	 * @return string|array
	 */
	function wp_unslash( $value ) {
		return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * @param mixed $data Data.
	 * @return string|false
	 */
	function wp_json_encode( $data ) {
		return json_encode( $data ); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}
}

if ( ! function_exists( 'get_post_type' ) ) {
	/**
	 * @param int $post_id Post ID.
	 * @return string|false
	 */
	function get_post_type( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! isset( $GLOBALS['tainacan_mapster_test_posts'][ $post_id ] ) ) {
			return false;
		}
		return $GLOBALS['tainacan_mapster_test_posts'][ $post_id ]['type'];
	}
}

if ( ! function_exists( 'get_post_status' ) ) {
	/**
	 * @param int $post_id Post ID.
	 * @return string|false
	 */
	function get_post_status( $post_id ) {
		$post_id = (int) $post_id;
		if ( ! isset( $GLOBALS['tainacan_mapster_test_posts'][ $post_id ] ) ) {
			return false;
		}
		return $GLOBALS['tainacan_mapster_test_posts'][ $post_id ]['status'];
	}
}

if ( ! function_exists( 'current_user_can' ) ) {
	/**
	 * @param string $capability Capability.
	 * @param mixed  ...$args    Extra args.
	 * @return bool
	 */
	function current_user_can( $capability, ...$args ) {
		return ! empty( $GLOBALS['tainacan_mapster_test_can_read'] );
	}
}

if ( ! function_exists( 'get_the_title' ) ) {
	/**
	 * @param int $post_id Post ID.
	 * @return string
	 */
	function get_the_title( $post_id ) {
		$post_id = (int) $post_id;
		return $GLOBALS['tainacan_mapster_test_titles'][ $post_id ] ?? '';
	}
}

if ( ! function_exists( 'get_permalink' ) ) {
	/**
	 * @param int $post_id Post ID.
	 * @return string
	 */
	function get_permalink( $post_id ) {
		$post_id = (int) $post_id;
		return $GLOBALS['tainacan_mapster_test_permalinks'][ $post_id ] ?? 'https://example.org/?p=' . $post_id;
	}
}

if ( ! function_exists( '__' ) ) {
	/**
	 * @param string $text   Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/**
	 * @param string $text   Text.
	 * @param string $domain Domain.
	 * @return string
	 */
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}
