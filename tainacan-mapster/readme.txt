=== Tainacan Mapster Integration ===
Contributors: tainacan, wetah
Tags: tainacan, mapster, maps, metadata, digital collection
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 0.5.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Unofficial integration: link Tainacan collection items to Mapster WP Maps elements and show them on a map.

== Description ==

**Tainacan Mapster Integration** connects [Tainacan](https://wordpress.org/plugins/tainacan/) with [Mapster WP Maps](https://wordpress.org/plugins/mapster-wp-maps/).

This plugin is **not** an official Mapster product. Mapster WP Maps is developed by Mapster Technology Inc.

It adds a **Mapster Map** metadata type. You choose a base Mapster map, then attach Mapster locations, lines, and/or polygons (map elements) to collection items. When editing an item, you can preview the selection on that map before saving.

**How it works:**

1. In **Mapster**, create the base map (style, zoom, controls) and the map elements (locations, lines, polygons).
2. In **Tainacan**, create a **Mapster Map** metadatum and bind it to that base map (and choose which element types are allowed).
3. When editing a collection item, **select** existing Mapster elements — they are not drawn inside Tainacan. You can also open Mapster from the field to create new ones, then select them afterward.

**Requirements:** Tainacan and Mapster WP Maps (free or Pro) must both be installed and active.

== Installation ==

1. Install and activate [Tainacan](https://wordpress.org/plugins/tainacan/).
2. Install and activate [Mapster WP Maps](https://wordpress.org/plugins/mapster-wp-maps/) (free or Pro).
3. Install and activate **Tainacan Mapster Integration**.
4. In Mapster, create at least one map and any locations, lines, or polygons you want to use.
5. In a Tainacan collection, add a metadatum of type **Mapster Map**.
6. Choose the **base map** (required) and which element types are allowed.
7. Edit items to select map elements; use Preview if you want to check the map before saving.

== Frequently Asked Questions ==

= Why do I see an error notice after activating? =

Tainacan and Mapster WP Maps (free or Pro) must both be active. The notice tells you which one is missing.

= Does it work with Mapster Pro? =

Yes. Free and Pro are both supported.

= Where do I create maps and locations / lines / polygons? =

In the **Mapster** admin screens. This plugin does not replace Mapster’s map editor. Tainacan only lets you pick which existing elements belong to each item and shows them on the base map configured for that metadatum.

= Must every Mapster Map metadatum use a base map? =

Yes. When you create or publish the metadatum, you must select a Mapster map. That map is the canvas (basemap, style, and controls); the item’s selected elements are displayed on it.

= Can I draw new shapes while editing a Tainacan item? =

Not inside the Tainacan form itself. Create or edit elements in Mapster (links from the field can open Mapster), then select them on the item.

= Can I preview the map while editing an item? =

Yes. After you select one or more map elements, use **Preview** on the metadata field to see them on the configured base map.

= Where do maps appear on the public site? =

Wherever your theme shows Tainacan item metadata. The map uses the base Mapster map you configured for that metadatum.

= Can I export items as GeoJSON? =

Yes. Use the **Mapster GeoJSON** exporter under Tainacan → Exporters. It writes a FeatureCollection with one Feature per selected Mapster element. You can optionally attach other item metadata as Feature properties. Items without map geometry are skipped. (CSV/XLSX can also embed GeoJSON in a cell if a Mapster Map metadatum uses the GeoJSON plain-text format — the dedicated exporter is better for GIS tools.)

= Can I fetch GeoJSON from the API? =

Yes. Add `?exposer=mapster-geojson` to a Tainacan items REST URL (collection list or single item). The response is the same FeatureCollection shape as the exporter (`Content-Type: application/geo+json`). Optional query args: `include_item_metadata`, `property_value_format`, `multivalued_delimiter`, `mapster_metadatum`.

= Can I see all items on a map in the items list? =

Yes. Enable the **Mapster Map** view mode for the collection, include a Mapster Map metadatum in the displayed metadata, then choose that view. It plots Mapster elements for the **current page** of results on the metadatum’s base map.

= Is this an official Mapster plugin? =

No. It is maintained for use with Tainacan and Mapster WP Maps.

== Screenshots ==

1. Creating a Mapster Map metadatum: choose the base map and allowed element types (locations, lines, polygons).
2. Previewing the selected elements on the base map while editing an item.
3. Selected map elements listed on the item form, with links to edit them in Mapster.
4. The map displayed on a public item page (theme metadata output).

== Changelog ==

= 0.5.0 =
* Removal of ACF Disabled status from Tainacan admin.

= 0.4.0 =
* Mapster GeoJSON exposer: fetch the same FeatureCollection via the REST API with `?exposer=mapster-geojson`.

= 0.3.0 =
* Mapster Map items-list view mode: select a Mapster Map metadatum and show its elements for the current page on the configured base map (iframe embed).

= 0.2.0 =
* Mapster GeoJSON exporter: FeatureCollection export with optional item metadata as Feature properties (string or JSON), including a multivalue delimiter option.

= 0.1.0 =
* Beta release: Mapster Map metadata type, map preview while editing items, and map display on the public site and in the Tainacan admin.

= 0.0.1 =
* Alpha release: initial Mapster metadata type, map preview while editing items, and map display on the public site and in the Tainacan admin.
