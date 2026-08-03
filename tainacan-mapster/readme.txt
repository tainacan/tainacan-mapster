=== Tainacan Mapster Integration ===
Contributors: tainacan, wetah
Tags: tainacan, mapster, maps, metadata, digital collection
Requires at least: 6.5
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Unofficial integration: a Tainacan metadata type for selecting Mapster WP Maps features and rendering them on a map.

== Description ==

**Tainacan Mapster Integration** connects [Tainacan](https://wordpress.org/plugins/tainacan/) with [Mapster WP Maps](https://wordpress.org/plugins/mapster-wp-maps/).

This plugin is **not** an official Mapster product. Mapster WP Maps is developed by Mapster Technology Inc.

It provides a **Mapster Feature** metadata type so collection items can reference Mapster locations, lines, and/or polygons and display them using a configured Mapster map.

Both Tainacan and Mapster WP Maps must be installed and active. The free Mapster plugin slug is `mapster-wp-maps` (also used by `Requires Plugins`). If you use a Pro build under a different folder name, activate it so the `mapster-wp-map` post type exists.

= Rendering =

* On normal theme PHP output, the Mapster shortcode is rendered directly.
* In the Tainacan admin (and other REST/AJAX contexts where HTML is injected later), maps are shown in a same-origin iframe that loads a minimal document running the same shortcode—so Mapster can boot after Vue `v-html`.

Developers can override that choice with the `tainacan_mapster_should_use_embed_iframe` filter.

== Installation ==

1. Install and activate Tainacan.
2. Install and activate Mapster WP Maps.
3. Upload the `tainacan-mapster` plugin folder to `/wp-content/plugins/` or install the ZIP via Plugins → Add New.
4. Activate **Tainacan Mapster Integration**.
5. In a collection, add a metadatum of type **Mapster Feature**, choose the Mapster map and allowed feature types, then edit items to select features.

== Frequently Asked Questions ==

= Why is the plugin inactive or showing an error notice? =

It requires both Tainacan and Mapster WP Maps (so the `mapster-wp-map` post type exists). Activate those plugins first.

= Does it work in the Tainacan admin item page? =

Yes. Admin/REST responses use a same-origin iframe so Mapster boots correctly after metadata HTML is injected.

= Is this an official Mapster plugin? =

No. It is maintained for Tainacan interoperability with Mapster WP Maps.

== Development ==

Source code, including the Vue metadata input (`metadata_type/metadata-type.vue`) and build scripts, is at:

https://github.com/tainacan/tainacan-mapster

Build the metadata input bundle with `npm install && npm run build` inside the plugin directory (compiles to `metadata_type/dist/metadata-type.bundle.js` without bundling Vue).

== Changelog ==

= 0.1.0 =
* Initial release: Mapster Feature metadata type, shortcode rendering, and iframe embed for SPA/REST contexts.
