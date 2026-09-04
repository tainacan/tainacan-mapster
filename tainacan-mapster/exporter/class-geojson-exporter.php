<?php

namespace TainacanMapster\Exporter;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Tainacan\Entities\Item;
use Tainacan\Entities\Item_Metadata_Entity;

/**
 * Export a collection as GeoJSON: one Feature per Mapster Map element.
 */
class GeoJSON extends \Tainacan\Exporter\Exporter {

	const MAPSTER_TYPE = 'TainacanMapster\\Metadata_Types\\Mapster_Feature';

	/**
	 * Output file key (basename with extension).
	 *
	 * @var string
	 */
	private $collection_filename = 'mapster_export.geojson';

	public function __construct( $attributes = [] ) {
		parent::__construct( $attributes );

		$this->set_accepted_mapping_methods( 'list', '', [] );
		$this->accept_no_mapping = true;

		$this->set_default_options(
			[
				'include_item_metadata' => '1',
				'property_value_format' => 'string',
				'multivalued_delimiter' => '||',
			]
		);

		if ( $current_collection = $this->get_current_collection_object() ) {
			$name = $current_collection->get_name();
			$this->collection_filename = sanitize_title( $name ) . '_mapster.geojson';
		}
	}

	/**
	 * Multivalue separator for get_value_as_string().
	 *
	 * @param string $separator Default separator.
	 * @return string
	 */
	public function filter_multivalue_separator( $separator ) {
		$custom = $this->get_option( 'multivalued_delimiter' );
		return ( is_string( $custom ) && '' !== $custom ) ? $custom : '||';
	}

	/**
	 * Open the FeatureCollection JSON document.
	 *
	 * @return false
	 */
	public function begin_exporter() {
		if ( $current_collection = $this->get_current_collection_object() ) {
			$name = $current_collection->get_name();
			$this->collection_filename = sanitize_title( $name ) . '_mapster.geojson';
		}

		$this->add_transient( 'geojson_feature_count', 0 );
		$this->add_transient( 'geojson_filename', $this->collection_filename );
		$this->append_to_file( $this->collection_filename, '{"type":"FeatureCollection","features":[' );

		return false;
	}

	/**
	 * Close the FeatureCollection JSON document.
	 *
	 * @return false
	 */
	public function end_exporter() {
		$filename = $this->get_transient( 'geojson_filename' );
		if ( ! is_string( $filename ) || '' === $filename ) {
			$filename = $this->collection_filename;
		}

		$this->append_to_file( $filename, ']}' );

		$count = (int) $this->get_transient( 'geojson_feature_count' );
		$this->add_log( sprintf( 'Wrote %d GeoJSON features.', $count ) );

		return false;
	}

	/**
	 * Skip the default [header] marker from the base Exporter.
	 */
	public function output_header() {
		return false;
	}

	/**
	 * Skip the default [footer] marker from the base Exporter.
	 */
	public function output_footer() {
		return false;
	}

	/**
	 * @param Item                   $item     Item entity.
	 * @param Item_Metadata_Entity[] $metadata Item metadata list.
	 */
	public function process_item( $item, $metadata ) {
		if ( ! ( $item instanceof Item ) ) {
			return;
		}

		$filename = $this->get_transient( 'geojson_filename' );
		if ( ! is_string( $filename ) || '' === $filename ) {
			$filename = $this->collection_filename;
		}

		$item_props = $this->build_item_core_properties( $item );
		$extra_props = [];

		if ( $this->should_include_item_metadata() ) {
			$use_string = ! $this->should_use_json_property_values();
			if ( $use_string ) {
				add_filter( 'tainacan-item-metadata-get-multivalue-separator', [ $this, 'filter_multivalue_separator' ], 20 );
			}
			$extra_props = $this->build_item_metadata_properties( $metadata );
			if ( $use_string ) {
				remove_filter( 'tainacan-item-metadata-get-multivalue-separator', [ $this, 'filter_multivalue_separator' ], 20 );
			}
		}

		foreach ( (array) $metadata as $item_metadata ) {
			if ( ! ( $item_metadata instanceof Item_Metadata_Entity ) ) {
				continue;
			}

			$metadatum = $item_metadata->get_metadatum();
			if ( ! $metadatum || ! $this->is_mapster_map_metadatum( $metadatum ) ) {
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
				$merged = array_merge( $item_props, $extra_props, $geometry_source_props );
				$feature = tainacan_mapster_get_element_geojson_feature( $element_id, $merged );
				if ( ! $feature ) {
					continue;
				}

				$this->append_feature( $filename, $feature );
			}
		}
	}

	/**
	 * Append one Feature to the open FeatureCollection (with comma separation).
	 *
	 * @param string $filename Output file key.
	 * @param array  $feature  GeoJSON Feature array.
	 */
	protected function append_feature( $filename, array $feature ) {
		$json = wp_json_encode( $feature );
		if ( ! is_string( $json ) || '' === $json ) {
			return;
		}

		$count = (int) $this->get_transient( 'geojson_feature_count' );
		$prefix = $count > 0 ? ',' : '';
		$this->append_to_file( $filename, $prefix . $json );
		$this->add_transient( 'geojson_feature_count', $count + 1 );
	}

	/**
	 * @return bool
	 */
	protected function should_include_item_metadata() {
		$option = $this->get_option( 'include_item_metadata' );
		return '1' === (string) $option || 'yes' === $option || true === $option;
	}

	/**
	 * Whether item metadata properties should be JSON (arrays/objects) instead of strings.
	 *
	 * @return bool
	 */
	protected function should_use_json_property_values() {
		return 'json' === $this->get_option( 'property_value_format' );
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
	 * Reserved property keys always present on Features.
	 *
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
	 * Core item fields always attached to every Feature.
	 *
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
	 * Non-Mapster metadata as slug => string or JSON-serializable value.
	 *
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
	 * JSON-safe property value via Tainacan's get_value_as_array().
	 *
	 * @param Item_Metadata_Entity $item_metadata Item metadatum entity.
	 * @return mixed|null Null when empty or not JSON-encodable.
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

	/**
	 * Finish message with download link.
	 *
	 * @return string
	 */
	public function get_output() {
		$filename = $this->get_transient( 'geojson_filename' );
		if ( ! is_string( $filename ) || '' === $filename ) {
			$filename = $this->collection_filename;
		}

		$files = $this->get_output_files();
		if ( ! is_array( $files ) || ! isset( $files[ $filename ] ) ) {
			$this->add_error_log( 'Output file not found! Maybe you need to correct the permissions of your upload folder' );
			return '';
		}

		$file        = $files[ $filename ];
		$current_user = wp_get_current_user();
		$author_name = $current_user->user_login;
		$count       = (int) $this->get_transient( 'geojson_feature_count' );

		$collections_names = [];
		foreach ( $this->get_collections() as $col ) {
			$collection = \Tainacan\Repositories\Collections::get_instance()->fetch( (int) $col['id'], 'OBJECT' );
			if ( $collection ) {
				$collections_names[] = $collection->get_name();
			}
		}

		$message  = __( 'Target collections:', 'tainacan-mapster' );
		$message .= ' <b>' . implode( ', ', $collections_names ) . '</b><br/>';
		$message .= __( 'Exported by:', 'tainacan-mapster' );
		$message .= ' <b> ' . $author_name . ' </b><br/>';
		$message .= sprintf(
			/* translators: %d: number of GeoJSON features written. */
			__( 'Features written: %d', 'tainacan-mapster' ),
			$count
		);
		$message .= '<br/>';
		$message .= __( 'Your GeoJSON file is ready! Access it in the link below:', 'tainacan-mapster' );
		$message .= '<br/><br/>';
		// Keep `_wpnonce=[nonce]` literal — Tainacan replaces it when serving the process output.
		// Do not esc_url() here or the placeholder breaks and download returns 403.
		$message .= '<a target="_blank" href="' . $file['url'] . '">' . __( 'Download', 'tainacan-mapster' ) . '</a>';

		return $message;
	}

	/**
	 * Exporter options form (static HTML).
	 *
	 * @return string
	 */
	public function options_form() {
		$include = $this->should_include_item_metadata();
		$format  = $this->should_use_json_property_values() ? 'json' : 'string';

		ob_start();
		?>
		<div class="field">
			<p class="help">
				<?php esc_html_e( 'Exports all Mapster Map metadata in the collection as GeoJSON geometries (one Feature per map element). Items with no resolvable Mapster geometry are omitted.', 'tainacan-mapster' ); ?>
			</p>
		</div>
		<div class="field">
			<label class="label"><?php esc_html_e( 'Include item metadata as properties', 'tainacan-mapster' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button">
					<span class="icon is-small">
						<i class="tainacan-icon tainacan-icon-help"></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php esc_html_e( 'Include item metadata as properties', 'tainacan-mapster' ); ?></h5>
					</div>
					<div class="help-tooltip-body">
						<p><?php esc_html_e( 'When enabled, other (non-Mapster) metadata values are copied onto each Feature as properties, keyed by metadatum slug. Mapster Map fields are used only as geometry sources and are not duplicated as attributes. Core item fields (id, title, status, URL) and the source metadatum id/name/slug are always included.', 'tainacan-mapster' ); ?></p>
					</div>
				</div>
			</span>
			<div class="control is-clearfix">
				<input type="hidden" name="include_item_metadata" value="0">
				<label class="b-checkbox checkbox">
					<input
						type="checkbox"
						name="include_item_metadata"
						value="1"
						<?php checked( $include ); ?>
					>
					<span class="check"></span>
					<span class="control-label"><?php esc_html_e( 'Yes', 'tainacan-mapster' ); ?></span>
				</label>
			</div>
		</div>
		<div class="field">
			<label class="label"><?php esc_html_e( 'Property value format', 'tainacan-mapster' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button">
					<span class="icon is-small">
						<i class="tainacan-icon tainacan-icon-help"></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php esc_html_e( 'Property value format', 'tainacan-mapster' ); ?></h5>
					</div>
					<div class="help-tooltip-body">
						<p><?php esc_html_e( 'How attached item metadata are written into Feature properties. “Delimited string” matches CSV-style flat attributes. “JSON” uses Tainacan’s structured values (arrays/objects for multivalue, taxonomy, compound, etc.), which is more natural in GeoJSON but less friendly to some desktop GIS tools.', 'tainacan-mapster' ); ?></p>
					</div>
				</div>
			</span>
			<div class="control is-clearfix">
				<div class="select">
					<select name="property_value_format">
						<option value="string" <?php selected( $format, 'string' ); ?>>
							<?php esc_html_e( 'Delimited string', 'tainacan-mapster' ); ?>
						</option>
						<option value="json" <?php selected( $format, 'json' ); ?>>
							<?php esc_html_e( 'JSON (structured)', 'tainacan-mapster' ); ?>
						</option>
					</select>
				</div>
			</div>
		</div>
		<div class="field">
			<label class="label"><?php esc_html_e( 'Multivalued metadata delimiter', 'tainacan-mapster' ); ?></label>
			<span class="help-wrapper">
				<a class="help-button">
					<span class="icon is-small">
						<i class="tainacan-icon tainacan-icon-help"></i>
					</span>
				</a>
				<div class="help-tooltip">
					<div class="help-tooltip-header">
						<h5><?php esc_html_e( 'Multivalued metadata delimiter', 'tainacan-mapster' ); ?></h5>
					</div>
					<div class="help-tooltip-body">
						<p><?php esc_html_e( 'Used only when property value format is “Delimited string”. Multiple values for one metadatum are joined with this delimiter (e.g. ||).', 'tainacan-mapster' ); ?></p>
					</div>
				</div>
			</span>
			<div class="control is-clearfix">
				<input
					class="input"
					type="text"
					name="multivalued_delimiter"
					value="<?php echo esc_attr( $this->get_option( 'multivalued_delimiter' ) ); ?>"
				>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
