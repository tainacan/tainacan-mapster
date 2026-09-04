# Tainacan Mapster Integration

WordPress plugin integrating Tainacan custom metadata types with Mapster WP Maps.

This is an **unofficial** integration. Mapster WP Maps is a third-party product.

## Metadata type

Registers one Tainacan metadata type:

- **Mapster Map** — stores Mapster element post ID(s) (location / line / polygon) and renders them on a configured base Mapster map

### Usage flow

1. **Mapster UI:** create the base map and map elements (locations, lines, polygons).
2. **Tainacan metadatum:** create a **Mapster Map** field; `mapster_map_id` (base map) is required when publishing; choose `allowed_feature_types`.
3. **Tainacan item edit:** select existing element IDs (autocomplete). Drawing/editing geometry stays in Mapster; optional create links open Mapster’s “new post” screens.

Plain-text contexts (REST `value_as_string`, CSV/XLSX exporters, etc.) use `get_value_as_string()`. By default that resolves IDs to Mapster element titles. Set the metadatum option `string_format` to `geojson` to emit a GeoJSON FeatureCollection string inside those cells (experimental). Prefer the dedicated **Mapster GeoJSON** exporter for a real `.geojson` file.

### GeoJSON exporter

Registers Tainacan exporter slug `mapster-geojson` (**Mapster GeoJSON**):

- One FeatureCollection file; **one Feature per Mapster element** (multi-valued and multiple Mapster Map metadata all flatten the same way).
- Always-on properties: `tainacan_item_id`, `tainacan_item_title`, `tainacan_item_status`, `tainacan_item_url`, `metadatum_id`, `metadatum_name`, `metadatum_slug`, plus element `id` / `name` / `mapster_type`.
- Option **Include item metadata as properties** (default on): copies other non-Mapster metadata onto each Feature, keyed by metadatum slug. Mapster Map fields are geometry sources only (not duplicated as attributes).
- Option **Property value format** (`string` | `json`, default `string`): delimited strings via `value_as_string`, or structured JSON via `get_value_as_array()` (multivalue/taxonomy/compound as arrays/objects).
- Option **Multivalued metadata delimiter** (default `||`): used only for `string` format.
- Items with no resolvable Mapster geometry are omitted.
- Stored item values remain Mapster element IDs; geometry is resolved at export time from Mapster/ACF.

### Items list view mode

Registers view mode slug `mapster` (**Mapster Map**):

- Component strategy ([extra view modes](https://tainacan.github.io/tainacan-wiki/#/dev/extra-view-modes)); enable it under the collection’s enabled view modes.
- User picks which **displayed** Mapster Map metadatum to use (same idea as core Map view + GeoCoordinate).
- Collects element IDs from items on the **current result page**, builds the existing same-origin Mapster **iframe embed** with that metadatum’s `mapster_map_id`.
- Sidebar lists items that have Mapster values: click to **reload the iframe** focused on that item’s element(s); **Open item** links to the public page; **Show all on map** clears the focus.
- Files: `view_mode/view-mode-mapster.js`, `view_mode/view-mode-mapster.css`.

When available, the type calls `set_manage_multiple_input( true )` so it can own multivalue UI (taginput + selected tab), similar to Relationship. The item form includes an optional **Preview** toggle that embeds the current selection on the configured map (same iframe endpoint as admin/REST rendering).

### Options

- `mapster_map_id` (required when status is `publish` or `private`): base Mapster map post ID (`mapster-wp-map`) used to render the elements.
- `allowed_feature_types` (required, at least one): which Mapster element post types this metadatum may reference:
  - `mapster-wp-location`
  - `mapster-wp-line`
  - `mapster-wp-polygon`
- `string_format` (`label` | `geojson`, default `label`): how `value_as_string` serializes selected elements for exporters/REST.

Default `allowed_feature_types` is all three.

### Rendering

- **Theme PHP** (`get_value_as_html` outside REST/admin/AJAX): Mapster shortcode via `do_shortcode()`.
- **Admin / REST / AJAX**: same-origin iframe → minimal embed document that runs the shortcode (works with Vue `v-html`).

Filter: `tainacan_mapster_should_use_embed_iframe` (bool).

Single value:

```shortcode
[mapster_wp_map id="{mapster_map_id}" single_feature_id="{feature_id}"]
```

Multiple values (one map):

```shortcode
[mapster_wp_map id="{mapster_map_id}" feature_ids="{id1},{id2},{id3}"]
```

Internally, Mapster still calls these “features” (`single_feature_id` / `feature_ids`). User-facing copy uses **map** / **element**.
## Structure

- `tainacan-mapster/tainacan-mapster.php`: plugin bootstrap
- `tainacan-mapster/inc/helpers.php`: shared sanitization / capability / GeoJSON helpers
- `tainacan-mapster/inc/plugin.php`: dependency checks and hook registration
- `tainacan-mapster/inc/embed.php`: iframe embed document + heuristics
- `tainacan-mapster/exporter/class-geojson-exporter.php`: Mapster GeoJSON Tainacan exporter
- `tainacan-mapster/view_mode/view-mode-mapster.js`: items-list Mapster Map view mode (iframe)
- `tainacan-mapster/view_mode/view-mode-mapster.css`: view mode styles
- `tainacan-mapster/metadata_type/metadata-type.vue`: item metadata input (Vue SFC source)
- `tainacan-mapster/metadata_type/build.js`: compiles the SFC into a host-Vue-compatible script
- `tainacan-mapster/metadata_type/dist/metadata-type.bundle.js`: built script loaded by WordPress
- `tainacan-mapster/metadata_type/metadata-type-form.js`: metadata options form (plain JS)
- `tainacan-mapster/assets/images/mapster-map-preview.png`: metadata type picker preview image
- `wp-repo-assets/`: WordPress.org directory screenshots / banners / icons (not in the plugin ZIP; see that folder’s README)

## Dependencies

- [Tainacan](https://wordpress.org/plugins/tainacan/) (Requires at least WP 6.5, PHP 7.4) — also listed in `Requires Plugins`
- [Mapster WP Maps](https://wordpress.org/plugins/mapster-wp-maps/) free **or** Pro

Plugin header: `Requires Plugins: tainacan` only. Mapster Pro often uses a different folder/slug than the free `.org` plugin, so Mapster is detected at runtime (`MAPSTER_WORDPRESS_MAPS_VERSION`, `mapster-wp-map` CPT, or `mapster_wp_map` shortcode) instead of being declared there.

WordPress.org user-facing copy lives in `tainacan-mapster/readme.txt`. Keep technical detail (filters, shortcodes, build, detection) here.

## Development

Repository: https://github.com/tainacan/tainacan-mapster

## Build

From the repository root (or inside the Docker container):

```bash
./build.sh
```

Or manually:

```bash
cd tainacan-mapster
npm install
npm run build
```

`npm run build` runs `metadata_type/build.js`, which compiles `metadata-type.vue` into `metadata_type/dist/metadata-type.bundle.js` **without bundling Vue**. That matters: Tainacan registers the component into its own Vue app, and a second Vue runtime breaks Buefy scoped slots.

Optional packaging into a destination folder:

```bash
./build.sh /path/to/wp-content/plugins
```

The WordPress.org-oriented package drops `node_modules`, npm lockfiles, and Vue build sources (see the GitHub repo). It includes `LICENSE` (GPLv3).

## Manual QA checklist

- Create/edit a Tainacan metadatum and choose **Mapster Map**.
- Confirm options form requires a base Mapster map (when publishing) and at least one element type.
- In item edition, confirm autocomplete searches only the allowed Mapster element post types.
- Save valid and invalid IDs to verify server-side validation.
- Confirm theme rendering outputs the Mapster shortcode / map.
- Confirm admin item view uses the iframe embed and the map boots.
- With “allow multiple values” enabled, confirm all selected elements appear on a single map.
