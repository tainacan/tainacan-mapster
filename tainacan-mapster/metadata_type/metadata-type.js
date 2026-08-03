import TainacanMetadataTypeMapsterSingleFeature from './metadata-type.vue';

/**
 * Webpack/dev entry only. Production build uses `node metadata_type/build.js`,
 * which emits dist/metadata-type.bundle.js without bundling a second Vue copy.
 */
window.tainacan_extra_components = typeof window.tainacan_extra_components !== 'undefined'
	? window.tainacan_extra_components
	: {};

window.tainacan_extra_components['tainacan-metadata-type-mapster-single-feature'] = TainacanMetadataTypeMapsterSingleFeature;
