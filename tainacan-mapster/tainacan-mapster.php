<?php
/*
Plugin Name: Tainacan Mapster Integration
Plugin URI: https://github.com/tainacan/tainacan-mapster
Description: Registers a Tainacan metadata type for selecting Mapster map elements and displaying them on a base map.
Author: Tainacan
Version: 0.5.0
Text Domain: tainacan-mapster
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Requires at least: 6.5
Requires PHP: 7.4
Requires Plugins: tainacan
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define( 'TAINACAN_MAPSTER_VERSION', '0.5.0' );
define( 'TAINACAN_MAPSTER_PLUGIN_FILE', __FILE__ );
define( 'TAINACAN_MAPSTER_PLUGIN_DIR_PATH', __DIR__ );
define( 'TAINACAN_MAPSTER_PLUGIN_URL_PATH', plugin_dir_url( __FILE__ ) );

require_once TAINACAN_MAPSTER_PLUGIN_DIR_PATH . '/inc/helpers.php';
require_once TAINACAN_MAPSTER_PLUGIN_DIR_PATH . '/inc/plugin.php';
require_once TAINACAN_MAPSTER_PLUGIN_DIR_PATH . '/inc/embed.php';
