<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! function_exists( 'tainacan_mapster_has_tainacan' ) ) {
	/**
	 * Whether Tainacan is active and loaded.
	 *
	 * @return bool
	 */
	function tainacan_mapster_has_tainacan() {
		return defined( 'TAINACAN_VERSION' ) && class_exists( '\Tainacan\Metadata_Types\Metadata_Type_Helper' );
	}
}

if ( ! function_exists( 'tainacan_mapster_has_mapster' ) ) {
	/**
	 * Whether Mapster WP Maps is active (free or Pro).
	 *
	 * Pro often uses a different plugin folder/slug than `mapster-wp-maps`, so we
	 * detect by Mapster's own bootstrap constant, CPT, or shortcode — not by slug.
	 *
	 * @return bool
	 */
	function tainacan_mapster_has_mapster() {
		if ( defined( 'MAPSTER_WORDPRESS_MAPS_VERSION' ) ) {
			return true;
		}

		if ( post_type_exists( 'mapster-wp-map' ) ) {
			return true;
		}

		return shortcode_exists( 'mapster_wp_map' );
	}
}

if ( ! function_exists( 'tainacan_mapster_has_dependencies' ) ) {
	/**
	 * Whether both Tainacan and Mapster (free or Pro) are available.
	 *
	 * @return bool
	 */
	function tainacan_mapster_has_dependencies() {
		return tainacan_mapster_has_tainacan() && tainacan_mapster_has_mapster();
	}
}

if ( ! function_exists( 'tainacan_mapster_register_metadata_types' ) ) {
	/**
	 * Register the Mapster Feature metadata type on Tainacan's helper.
	 *
	 * Note: `tainacan-register-metadata-type` fires while Tainacan loads (too early
	 * for plugins that load after it), so we call the helper directly on `init`.
	 *
	 * @param \Tainacan\Metadata_Types\Metadata_Type_Helper $helper Helper instance.
	 */
	function tainacan_mapster_register_metadata_types( $helper ) {
		require_once TAINACAN_MAPSTER_PLUGIN_DIR_PATH . '/metadata_type/class-mapster-feature-metadata-type.php';

		$metadata_script_url = TAINACAN_MAPSTER_PLUGIN_URL_PATH . 'metadata_type/dist/metadata-type.bundle.js';

		$helper->register_metadata_type(
			'tainacan-mapster-feature-metadata',
			'TainacanMapster\Metadata_Types\Mapster_Feature',
			$metadata_script_url
		);
	}
}

if ( ! function_exists( 'tainacan_mapster_register_metadata_form_component' ) ) {
	/**
	 * Register the metadata type options form Vue component.
	 *
	 * Hooked to `tainacan-register-vuejs-component` (fires on `init` priority 80).
	 *
	 * @param \Tainacan\Component_Hooks $helper Component hooks helper.
	 */
	function tainacan_mapster_register_metadata_form_component( $helper ) {
		if ( ! tainacan_mapster_has_dependencies() ) {
			return;
		}

		$form_script_url = TAINACAN_MAPSTER_PLUGIN_URL_PATH . 'metadata_type/metadata-type-form.js';
		$helper->register_vuejs_component( 'tainacan-mapster-metadata-type-form', $form_script_url );
	}
}

if ( ! function_exists( 'tainacan_mapster_admin_notice_missing_dependencies' ) ) {
	/**
	 * Admin notice when Tainacan and/or Mapster WP Maps (free or Pro) is missing.
	 */
	function tainacan_mapster_admin_notice_missing_dependencies() {
		if ( tainacan_mapster_has_dependencies() ) {
			return;
		}

		$missing = [];
		if ( ! tainacan_mapster_has_tainacan() ) {
			$missing[] = 'Tainacan';
		}
		if ( ! tainacan_mapster_has_mapster() ) {
			$missing[] = 'Mapster WP Maps (free or Pro)';
		}

		$message = sprintf(
			/* translators: %s: comma-separated list of missing plugin names. */
			__( 'Tainacan Mapster Integration requires the following to be installed and active: %s.', 'tainacan-mapster' ),
			implode( ', ', $missing )
		);
		?>
		<div class="notice notice-error">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}
}

if ( ! function_exists( 'tainacan_mapster_bootstrap' ) ) {
	function tainacan_mapster_bootstrap() {
		if ( ! tainacan_mapster_has_dependencies() ) {
			add_action( 'admin_notices', 'tainacan_mapster_admin_notice_missing_dependencies' );
			return;
		}

		static $did_register = false;
		if ( $did_register ) {
			return;
		}
		$did_register = true;

		if ( class_exists( '\Tainacan\Metadata_Types\Metadata_Type_Helper' ) ) {
			tainacan_mapster_register_metadata_types( \Tainacan\Metadata_Types\Metadata_Type_Helper::get_instance() );
		}
	}
}

/*
 * Priority 20: after typical `register_post_type` calls on `init` (default 10), so
 * `mapster-wp-map` exists for dependency checks.
 */
add_action( 'init', 'tainacan_mapster_bootstrap', 20 );

/*
 * Metadata options form — same hook documented in the Tainacan wiki
 * (fires from Component_Hooks on `init` priority 80).
 */
add_action( 'tainacan-register-vuejs-component', 'tainacan_mapster_register_metadata_form_component' );

if ( ! function_exists( 'tainacan_mapster_get_script_localization' ) ) {
	/**
	 * Shared localization payload for Mapster metadata scripts.
	 *
	 * @return array<string, mixed>
	 */
	function tainacan_mapster_get_script_localization() {
		require_once TAINACAN_MAPSTER_PLUGIN_DIR_PATH . '/metadata_type/class-mapster-feature-metadata-type.php';

		$labels        = \TainacanMapster\Metadata_Types\Mapster_Feature::get_feature_post_type_labels();
		$feature_types = \TainacanMapster\Metadata_Types\Mapster_Feature::get_available_feature_post_types();
		$feature_create = [];

		foreach ( $feature_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			$can_create       = $post_type_object && current_user_can( $post_type_object->cap->create_posts );
			$type_label       = isset( $labels[ $post_type ] ) ? $labels[ $post_type ] : $post_type;

			$feature_create[ $post_type ] = [
				'can_create'  => (bool) $can_create,
				'create_link' => $can_create ? admin_url( 'post-new.php?post_type=' . $post_type ) : '',
				'label'       => $type_label,
				'create_label' => sprintf(
					/* translators: %s: Mapster feature type label (location, line, polygon). */
					__( 'New %s', 'tainacan-mapster' ),
					strtolower( $type_label )
				),
			];
		}

		return [
			'mapsScreenUrl'         => admin_url( 'edit.php?post_type=mapster-wp-map' ),
			'mapSelectHelpText'     => __( 'Maps are created in Mapster. Use the Maps screen to add or edit them.', 'tainacan-mapster' ),
			'mapSelectLinkText'     => __( 'Open Mapster Maps', 'tainacan-mapster' ),
			'featureTypeLabels'     => $labels,
			'availableFeatureTypes' => $feature_types,
			'featureCreate'         => $feature_create,
			'editFeatureLabel'      => __( 'Edit feature', 'tainacan-mapster' ),
			'editPostUrl'           => admin_url( 'post.php' ),
		];
	}
}

if ( ! function_exists( 'tainacan_mapster_localize_metadata_form_script' ) ) {
	/**
	 * Strings and URL for the metadata options form (after scripts are enqueued).
	 */
	function tainacan_mapster_localize_metadata_form_script() {
		if ( ! tainacan_mapster_has_dependencies() || ! wp_script_is( 'tainacan-mapster-metadata-type-form', 'registered' ) ) {
			return;
		}

		wp_localize_script(
			'tainacan-mapster-metadata-type-form',
			'tainacanMapsterMetadatumForm',
			tainacan_mapster_get_script_localization()
		);
	}
}

add_action( 'admin_enqueue_scripts', 'tainacan_mapster_localize_metadata_form_script', 85 );

if ( ! function_exists( 'tainacan_mapster_localize_metadata_input_script' ) ) {
	/**
	 * Shared strings for the item metadata input script.
	 */
	function tainacan_mapster_localize_metadata_input_script() {
		if ( ! tainacan_mapster_has_dependencies() || ! wp_script_is( 'tainacan-mapster-feature-metadata', 'registered' ) ) {
			return;
		}

		wp_localize_script(
			'tainacan-mapster-feature-metadata',
			'tainacanMapsterMetadatumForm',
			tainacan_mapster_get_script_localization()
		);
	}
}

add_action( 'admin_enqueue_scripts', 'tainacan_mapster_localize_metadata_input_script', 85 );

