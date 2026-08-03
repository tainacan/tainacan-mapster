# Tainacan Mapster Integration

WordPress plugin integrating Tainacan custom metadata types with Mapster WP Maps features.

This is an **unofficial** integration. Mapster WP Maps is a third-party product.

## Metadata type

Registers one Tainacan metadata type:

- **Mapster Feature** — stores Mapster feature post ID(s) and renders them on a Mapster map

When available, the type calls `set_manage_multiple_input( true )` so it can own multivalue UI (taginput + selected tab), similar to Relationship.

### Options

- `mapster_map_id` (required when status is `publish` or `private`): Mapster map post ID (`mapster-wp-map`) used to render the feature.
- `allowed_feature_types` (required, at least one): which Mapster feature post types this metadatum may reference:
  - `mapster-wp-location`
  - `mapster-wp-line`
  - `mapster-wp-polygon`

Default is all three.

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

## Structure

- `tainacan-mapster/tainacan-mapster.php`: plugin bootstrap
- `tainacan-mapster/inc/helpers.php`: shared sanitization / capability helpers
- `tainacan-mapster/inc/plugin.php`: dependency checks and hook registration
- `tainacan-mapster/inc/embed.php`: iframe embed document + heuristics
- `tainacan-mapster/metadata_type/metadata-type.vue`: item metadata input (Vue SFC source)
- `tainacan-mapster/metadata_type/build.js`: compiles the SFC into a host-Vue-compatible script
- `tainacan-mapster/metadata_type/dist/metadata-type.bundle.js`: built script loaded by WordPress
- `tainacan-mapster/metadata_type/metadata-type-form.js`: metadata options form (plain JS)

## Dependencies

- [Tainacan](https://wordpress.org/plugins/tainacan/) (Requires at least WP 6.5, PHP 7.4)
- [Mapster WP Maps](https://wordpress.org/plugins/mapster-wp-maps/)

Plugin header declares both via `Requires Plugins`.

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

- Create/edit a Tainacan metadatum and choose **Mapster Feature**.
- Confirm options form requires a Mapster map (when publishing) and at least one feature type.
- In item edition, confirm autocomplete searches only the allowed Mapster feature post types.
- Save valid and invalid IDs to verify server-side validation.
- Confirm theme rendering outputs the Mapster shortcode / map.
- Confirm admin item view uses the iframe embed and the map boots.
- With “allow multiple values” enabled, confirm all selected features appear on a single map.
