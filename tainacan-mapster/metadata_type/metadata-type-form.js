window.tainacan_extra_components = typeof window.tainacan_extra_components !== "undefined" ? window.tainacan_extra_components : {};

/**
 * Metadata type options form for Mapster Map.
 *
 * Component slug must match set_form_component() in the PHP class:
 * tainacan-metadata-form-type-mapster-single-feature
 *
 * Form labels come from get_form_labels() and are exposed under helpers_label
 * keyed by the metadata type *input* component name
 * (tainacan-metadata-type-mapster-single-feature).
 */
if ( ! window.tainacan_extra_components['tainacan-metadata-form-type-mapster-single-feature'] ) {
var TainacanMetadataFormMapsterSingleFeature = {
    name: "TainacanMetadataFormTypeMapsterSingleFeature",
    props: {
        value: [Object, String, Number, Array]
    },
    emits: ['update:value'],
    data: function() {
        var cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : {};
        var mapsScreenUrl = cfg.mapsScreenUrl || '';
        if (!mapsScreenUrl && typeof tainacan_plugin !== 'undefined' && tainacan_plugin.wp_ajax_url) {
            mapsScreenUrl = tainacan_plugin.wp_ajax_url.replace('admin-ajax.php', 'edit.php?post_type=mapster-wp-map');
        }
        var availableFeatureTypes = Array.isArray(cfg.availableFeatureTypes) && cfg.availableFeatureTypes.length
            ? cfg.availableFeatureTypes.slice()
            : ['mapster-wp-location', 'mapster-wp-line', 'mapster-wp-polygon'];

        return {
            mapId: '',
            mapOptions: [],
            isLoading: false,
            mapsScreenUrl: mapsScreenUrl,
            mapSelectHelpText: cfg.mapSelectHelpText || '',
            mapSelectLinkText: cfg.mapSelectLinkText || '',
            featureTypeLabels: cfg.featureTypeLabels || {},
            availableFeatureTypes: availableFeatureTypes,
            allowedFeatureTypes: availableFeatureTypes.slice()
        };
    },
    created: function() {
        this.mapId = this.value && this.value.mapster_map_id ? String(this.value.mapster_map_id) : '';
        if (this.value && Array.isArray(this.value.allowed_feature_types) && this.value.allowed_feature_types.length) {
            this.allowedFeatureTypes = this.value.allowed_feature_types.filter((type) => {
                return this.availableFeatureTypes.indexOf(type) !== -1;
            });
        }
        if (!this.allowedFeatureTypes.length) {
            this.allowedFeatureTypes = this.availableFeatureTypes.slice();
        }
        this.loadMaps();
    },
    methods: {
        emitOptions: function() {
            this.$emit('update:value', {
                mapster_map_id: this.mapId,
                allowed_feature_types: this.allowedFeatureTypes.slice()
            });
        },
        onSelectMap: function(value) {
            this.mapId = value;
            this.emitOptions();
        },
        onToggleFeatureType: function(postType, checked) {
            var next = this.allowedFeatureTypes.slice();
            var index = next.indexOf(postType);
            if (checked && index === -1) {
                next.push(postType);
            } else if (!checked && index !== -1) {
                next.splice(index, 1);
            }
            this.allowedFeatureTypes = next;
            this.emitOptions();
        },
        isFeatureTypeChecked: function(postType) {
            return this.allowedFeatureTypes.indexOf(postType) !== -1;
        },
        featureTypeLabel: function(postType) {
            return this.featureTypeLabels[postType] || postType;
        },
        loadMaps: function() {
            this.isLoading = true;
            window.wp.apiFetch({
                path: '/wp/v2/mapster-wp-map?_fields=id,title&orderby=title&order=asc&per_page=100'
            }).then((posts) => {
                this.mapOptions = (Array.isArray(posts) ? posts : []).map((post) => ({
                    id: String(post.id),
                    label: post.title && post.title.rendered ? post.title.rendered : '#' + post.id
                }));
            }).catch(() => {
                this.mapOptions = [];
            }).finally(() => {
                this.isLoading = false;
            });
        }
    },
    template: `
    <section>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-metadata-type-mapster-single-feature', 'mapster_map_id') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-metadata-type-mapster-single-feature', 'mapster_map_id')"
                        :message="$i18n.getHelperMessage('tainacan-metadata-type-mapster-single-feature', 'mapster_map_id')"/>
            </label>
            <b-select
                    name="mapster_map_id"
                    :model-value="mapId"
                    :loading="isLoading"
                    expanded
                    required
                    @update:model-value="onSelectMap">
                <option value="" disabled>
                    {{ $i18n.get('label_selectbox_init') }}
                </option>
                <option
                        v-for="mapOption in mapOptions"
                        :key="mapOption.id"
                        :value="mapOption.id">
                    {{ mapOption.label }}
                </option>
            </b-select>
            <p
                    v-if="mapsScreenUrl"
                    class="help">
                {{ mapSelectHelpText }}
                <a
                        :href="mapsScreenUrl"
                        target="_blank"
                        rel="noopener noreferrer">{{ mapSelectLinkText }}</a>
            </p>
        </b-field>
        <b-field :addons="false">
            <label class="label is-inline">
                {{ $i18n.getHelperTitle('tainacan-metadata-type-mapster-single-feature', 'allowed_feature_types') }}<span>&nbsp;*&nbsp;</span>
                <help-button
                        :title="$i18n.getHelperTitle('tainacan-metadata-type-mapster-single-feature', 'allowed_feature_types')"
                        :message="$i18n.getHelperMessage('tainacan-metadata-type-mapster-single-feature', 'allowed_feature_types')"/>
            </label>
            <div>
                <label
                        v-for="postType in availableFeatureTypes"
                        :key="postType"
                        class="b-checkbox checkbox"
                        style="display: block; margin-bottom: 0.35rem;">
                    <input
                            type="checkbox"
                            :checked="isFeatureTypeChecked(postType)"
                            @change="onToggleFeatureType(postType, $event.target.checked)">
                    <span class="check"></span>
                    <span class="control-label">{{ featureTypeLabel(postType) }}</span>
                </label>
            </div>
        </b-field>
    </section>
    `
};

window.tainacan_extra_components["tainacan-metadata-form-type-mapster-single-feature"] = TainacanMetadataFormMapsterSingleFeature;

}
