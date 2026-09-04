# WordPress.org repository assets

Images for the [Plugin Directory](https://developer.wordpress.org/plugins/wordpress-org/plugin-assets/) live here. They are **not** shipped inside the plugin ZIP (`build.sh` only copies `tainacan-mapster/`).

On wordpress.org SVN, upload these files into the plugin’s `assets/` directory (sibling of `trunk/`), using the standard names below.

## Screenshots

Captions are defined in `tainacan-mapster/readme.txt` under `== Screenshots ==`. File names must match the caption numbers:

| File | Caption (readme.txt) |
|------|----------------------|
| `screenshot-1.png` (or `.jpg`) | Creating a Mapster Map metadatum: choose the base map and allowed element types (locations, lines, polygons). |
| `screenshot-2.png` | Previewing the selected elements on the base map while editing an item. |
| `screenshot-3.png` | Selected map elements listed on the item form, with links to edit them in Mapster. |
| `screenshot-4.png` | The map displayed on a public item page (theme metadata output). |

Prefer PNG or JPG. Keep width reasonable for the directory (often ~1280px wide is enough).

## Optional branding assets

| File | Size |
|------|------|
| `banner-772x250.png` | Header banner (1×) |
| `banner-1544x500.png` | Header banner (2× / HiDPI) |
| `icon-128x128.png` | Plugin icon |
| `icon-256x256.png` | Plugin icon (HiDPI) |

You can omit branding assets until you have designs; screenshots alone are enough for the Screenshots tab once captions and files are in sync.
