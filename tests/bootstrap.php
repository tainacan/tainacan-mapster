<?php
/**
 * PHPUnit bootstrap for Tainacan Mapster Integration.
 *
 * Lightweight stubs — no WordPress test suite / database required.
 *
 * @package Tainacan_Mapster
 */

require_once dirname( __DIR__ ) . '/vendor/autoload.php';
require_once __DIR__ . '/stubs/wordpress-stubs.php';
require_once __DIR__ . '/stubs/tainacan-stubs.php';

$plugin_dir = dirname( __DIR__ ) . '/tainacan-mapster';

if ( ! defined( 'TAINACAN_MAPSTER_PLUGIN_DIR_PATH' ) ) {
	define( 'TAINACAN_MAPSTER_PLUGIN_DIR_PATH', $plugin_dir );
}

if ( ! defined( 'TAINACAN_MAPSTER_VERSION' ) ) {
	define( 'TAINACAN_MAPSTER_VERSION', '0.4.0-test' );
}

require_once $plugin_dir . '/inc/helpers.php';
require_once $plugin_dir . '/inc/class-geojson-feature-builder.php';
require_once $plugin_dir . '/exposer/class-geojson-exposer.php';
