/*! Tainacan Mapster metadata type – built from metadata-type.vue */
(function () {
	if (window.tainacan_extra_components && window.tainacan_extra_components["tainacan-metadata-type-mapster-single-feature"]) {
		return;
	}

	window.tainacan_extra_components = typeof window.tainacan_extra_components !== 'undefined'
		? window.tainacan_extra_components
		: {};

(function () {
	if (document.getElementById('tainacan-mapster-metadata-type-styles')) {
		return;
	}
	var styleEl = document.createElement('style');
	styleEl.id = 'tainacan-mapster-metadata-type-styles';
	styleEl.textContent = ".tainacan-mapster-feature-input {\n    width: 100%;\n}\n\n.tainacan-mapster-feature-input.is-flex {\n    justify-content: flex-start;\n}\n\n.tainacan-mapster-feature-input .b-tabs {\n    margin-bottom: 0;\n    width: 100%;\n}\n\n.tainacan-mapster-feature-input .b-tabs .tab-content {\n    padding: 0.5em 0 !important;\n}\n\n.tainacan-mapster-feature-input .tabs {\n    margin-bottom: 0 !important;\n}\n\n.tainacan-mapster-feature-input .tabs ul {\n    padding: 0;\n}\n\n.tainacan-mapster-autocomplete-option {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    gap: 0.75rem;\n    width: 100%;\n}\n\n.tainacan-mapster-autocomplete-option__title {\n    min-width: 0;\n    flex: 1 1 auto;\n}\n\n.tainacan-mapster-autocomplete-option__type {\n    flex: 0 0 auto;\n    margin-inline-start: auto;\n    color: #7a7a7a;\n    font-size: 0.8125em;\n    white-space: nowrap;\n}\n\n.tainacan-mapster-results-container {\n    border: 1px solid var(--tainacan-gray1, #dbdbdb);\n    border-bottom-right-radius: var(--tainacan-input-border-radius, 4px);\n    border-bottom-left-radius: var(--tainacan-input-border-radius, 4px);\n    background-color: var(--tainacan-background-color, #fff);\n    margin-top: calc(-1 * (0.5em + 1px));\n    margin-bottom: calc(-1 * (0.5em + 1px));\n    display: flex;\n    overflow: auto;\n    padding: 12px;\n    max-height: 40vh;\n}\n\n.tainacan-mapster-selected-group {\n    width: 100%;\n}\n\n.tainacan-mapster-selected-item {\n    position: relative;\n    display: flex;\n    align-items: flex-start;\n    gap: 0.5rem;\n    padding-right: 64px;\n    padding-bottom: 0.75rem;\n}\n\n.tainacan-mapster-selected-item:not(:last-child) {\n    margin-bottom: 0.5rem;\n    border-bottom: 1px solid var(--tainacan-gray2, #ededed);\n}\n\n.tainacan-mapster-selected-item__content {\n    display: flex;\n    flex-direction: column;\n    gap: 0.15rem;\n    min-width: 0;\n    flex: 1 1 auto;\n}\n\n.tainacan-mapster-selected-item__label {\n    font-size: 0.875em;\n}\n\n.tainacan-mapster-selected-item__type {\n    color: #7a7a7a;\n    font-size: 0.75em;\n}\n\n.tainacan-mapster-value-button--edit,\n.tainacan-mapster-value-button--remove {\n    position: absolute;\n    top: 0;\n    right: 4px;\n    background-color: var(--tainacan-white, #fff);\n    border-radius: 100%;\n    padding: 2px;\n    color: var(--tainacan-info-color, #505253);\n    text-decoration: none;\n}\n\n.tainacan-mapster-value-button--edit {\n    right: 34px;\n}\n\n.tainacan-mapster-value-button--edit:hover,\n.tainacan-mapster-value-button--remove:hover {\n    background-color: var(--tainacan-gray0, #f6f6f6);\n    color: var(--tainacan-secondary, #187181);\n}\n\n.tainacan-mapster-toolbar {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    justify-content: space-between;\n    gap: 0.5rem 0.85rem;\n    width: 100%;\n    margin-top: 0.35rem;\n}\n\n.tainacan-mapster-toolbar__create {\n    display: flex;\n    flex-wrap: wrap;\n    align-items: center;\n    justify-content: flex-end;\n    gap: 0.5rem 0.85rem;\n    margin-left: auto;\n}\n\n.tainacan-mapster-create-feature.add-link {\n    font-size: 0.75em;\n    display: inline-flex;\n    align-items: center;\n    color: var(--tainacan-secondary, #187181);\n    cursor: pointer;\n    text-decoration: none;\n}\n\n.tainacan-mapster-preview-toggle.add-link {\n    font-size: 0.75em;\n    display: inline-flex;\n    align-items: center;\n    color: var(--tainacan-secondary, #187181);\n    cursor: pointer;\n    text-decoration: none;\n}\n\n.tainacan-mapster-preview-toggle .icon .tainacan-icon {\n    font-size: 1.35em;\n}\n\n.tainacan-mapster-preview-missing.help {\n    margin: 0;\n    font-size: 0.75em;\n    color: var(--tainacan-info-color, #505253);\n}\n\n.tainacan-mapster-preview {\n    width: 100%;\n    margin-top: 0.5rem;\n    height: 320px;\n    max-width: 100%;\n    border: 1px solid var(--tainacan-gray1, #dbdbdb);\n    border-radius: var(--tainacan-input-border-radius, 4px);\n    overflow: hidden;\n    background: var(--tainacan-background-color, #fff);\n}\n\n.tainacan-mapster-preview__frame {\n    display: block;\n    width: 100%;\n    height: 100%;\n    border: 0;\n}\n\n.ellipsed-text {\n    display: inline-block;\n    max-width: 100%;\n    overflow: hidden;\n    text-overflow: ellipsis;\n    white-space: nowrap;\n}";
	document.head.appendChild(styleEl);
})();

const FEATURE_TYPE_LABELS = {
    'mapster-wp-location': 'Location',
    'mapster-wp-line': 'Line',
    'mapster-wp-polygon': 'Polygon'
};

const DEFAULT_FEATURE_TYPES = [
    'mapster-wp-location',
    'mapster-wp-line',
    'mapster-wp-polygon'
];

const REST_FIELDS = 'id,title,_links';

var __tainacanMapsterMetadataTypeComponent = {
    name: 'TainacanMetadataTypeMapsterSingleFeature',
    props: {
        itemMetadatum: Object,
        value: [String, Number, Array],
        disabled: false,
        isLastMetadatum: false,
        isMobileScreen: false
    },
    emits: [
        'update:value',
        'blur',
        'mobile-special-focus'
    ],
    data() {
        return {
            selected: [],
            options: [],
            isLoading: false,
            searchQuery: '',
            page: 1,
            hasMore: true,
            searchRequestToken: 0,
            activeTab: 0,
            debouncedSearch: null,
            isPreviewing: false
        };
    },
    computed: {
        showSecondaryActions() {
            const showPreview = this.selected.length > 0;
            const showCreate = this.creatableFeatureTypes.length > 0 && !this.disabled && this.canAddMore;
            return showPreview || showCreate;
        },
        configuredMapId() {
            const options = this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.metadata_type_options
                ? this.itemMetadatum.metadatum.metadata_type_options
                : {};
            const mapId = parseInt(options.mapster_map_id, 10);
            return mapId > 0 ? mapId : 0;
        },
        previewMapLabel() {
            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            if (cfg && cfg.previewMapLabel) {
                return cfg.previewMapLabel;
            }
            if (this.$i18n && typeof this.$i18n.get === 'function') {
                return this.$i18n.get('label_preview', 'tainacan');
            }
            return 'Preview';
        },
        previewMapMissing() {
            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            if (cfg && cfg.previewMapMissing) {
                return cfg.previewMapMissing;
            }
            return 'Configure a base Mapster map for this metadatum to preview the selection.';
        },
        previewEmbedUrl() {
            if (!this.isPreviewing || !this.configuredMapId || !this.selected.length) {
                return '';
            }
            return this.buildEmbedUrl(
                this.configuredMapId,
                this.selected.map((item) => item.value)
            );
        },
        editFeatureLabel() {
            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            if (cfg && cfg.editFeatureLabel) {
                return cfg.editFeatureLabel;
            }
            if (this.$i18n && typeof this.$i18n.get === 'function') {
                return this.$i18n.get('label_edit');
            }
            return 'Edit element';
        },
        creatableFeatureTypes() {
            return this.getAllowedFeaturePostTypes().filter((postType) => {
                return !!this.getFeatureCreateMeta(postType).createLink;
            });
        },
        maxMultipleValues() {
            return (
                this.itemMetadatum &&
                this.itemMetadatum.metadatum &&
                this.itemMetadatum.metadatum.cardinality &&
                !isNaN(this.itemMetadatum.metadatum.cardinality) &&
                this.itemMetadatum.metadatum.cardinality > 1
            ) ? Number(this.itemMetadatum.metadatum.cardinality) : undefined;
        },
        maxTags() {
            if (this.itemMetadatum.metadatum.multiple == 'yes') {
                return this.maxMultipleValues !== undefined ? this.maxMultipleValues : null;
            }
            return 1;
        },
        canAddMore() {
            if (this.itemMetadatum.metadatum.multiple != 'yes') {
                return this.selected.length < 1;
            }
            if (this.maxMultipleValues === undefined) {
                return true;
            }
            return this.selected.length < this.maxMultipleValues;
        },
        featureInputId() {
            if (this.itemMetadatum && this.itemMetadatum.metadatum) {
                return 'tainacan-item-metadatum_id-' + this.itemMetadatum.metadatum.id + (this.itemMetadatum.parent_meta_id ? ('_parent_meta_id-' + this.itemMetadatum.parent_meta_id) : '');
            }
            return '';
        },
        selectTabLabel() {
            const isSingleSelected = this.itemMetadatum.value && this.itemMetadatum.value.length == 1;
            if (isSingleSelected || this.itemMetadatum.metadatum.multiple != 'yes') {
                return this.$i18n.get('label_select_item');
            }
            return this.$i18n.get('label_insert_items');
        },
        selectedTabLabel() {
            const isSingleSelected = this.selected.length == 1 || this.itemMetadatum.metadatum.multiple != 'yes';
            if (isSingleSelected) {
                return this.$i18n.get('label_selected_item');
            }
            return this.$i18n.get('label_selected_items') + ' (' + this.selected.length + ')';
        }
    },
    created() {
        this.debouncedSearch = this.createDebouncedSearch(350);
        const initialIds = this.getInitialValueIds();

        if (initialIds.length) {
            this.fetchFeaturesByIds(initialIds).then(() => {
                if (this.selected.length > 0 && this.itemMetadatum.metadatum.multiple != 'yes') {
                    this.activeTab = 1;
                }
            });
        }
    },
    beforeUnmount() {
        if (this.debouncedSearch && this.debouncedSearch.cancel) {
            this.debouncedSearch.cancel();
        }
    },
    methods: {
        createDebouncedSearch(wait) {
            if (typeof window._ !== 'undefined' && typeof window._.debounce === 'function') {
                return window._.debounce((query) => {
                    this.search(query, false);
                }, wait);
            }

            let timeoutId = null;
            const debounced = (query) => {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                }
                timeoutId = setTimeout(() => {
                    timeoutId = null;
                    this.search(query, false);
                }, wait);
            };
            debounced.cancel = function() {
                if (timeoutId) {
                    clearTimeout(timeoutId);
                    timeoutId = null;
                }
            };
            return debounced;
        },
        getInitialValueIds() {
            let raw = null;
            if (this.itemMetadatum && this.itemMetadatum.value !== undefined && this.itemMetadatum.value !== null && this.itemMetadatum.value !== '') {
                raw = this.itemMetadatum.value;
            } else if (this.value !== undefined && this.value !== null && this.value !== '') {
                raw = this.value;
            }

            if (raw === null) {
                return [];
            }

            const ids = Array.isArray(raw) ? raw : [raw];
            return ids
                .map((id) => String(id))
                .filter((id) => id && id !== 'null' && id !== 'undefined');
        },
        onBlur() {
            this.$emit('blur');
        },
        onMobileSpecialFocus() {
            this.$emit('mobile-special-focus');
        },
        onInput(newSelected) {
            this.searchQuery = '';
            this.options = [];
            this.page = 1;
            this.selected = Array.isArray(newSelected) ? newSelected : [];
            if (!this.selected.length) {
                this.isPreviewing = false;
            }
            this.$emit('update:value', this.selected.map((item) => item.value));
        },
        togglePreview() {
            if (!this.configuredMapId || !this.selected.length) {
                this.isPreviewing = false;
                return;
            }
            this.isPreviewing = !this.isPreviewing;
        },
        buildEmbedUrl(mapId, featureIds) {
            const ids = (Array.isArray(featureIds) ? featureIds : [featureIds])
                .map((id) => parseInt(id, 10))
                .filter((id) => id > 0);

            if (!mapId || !ids.length) {
                return '';
            }

            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            const homeUrl = cfg && cfg.homeUrl ? String(cfg.homeUrl) : '/';
            let url;

            try {
                url = new URL(homeUrl, window.location.origin);
            } catch (e) {
                url = new URL('/', window.location.origin);
            }

            url.searchParams.set('tainacan_mapster_embed', '1');
            url.searchParams.set('map_id', String(mapId));
            url.searchParams.delete('single_feature_id');
            url.searchParams.delete('feature_ids');

            if (ids.length === 1) {
                url.searchParams.set('single_feature_id', String(ids[0]));
            } else {
                url.searchParams.set('feature_ids', ids.join(','));
            }

            return url.toString();
        },
        onTyping(query) {
            this.debouncedSearch(query);
        },
        removeFromSelected(featureId) {
            const index = this.selected.findIndex((item) => String(item.value) === String(featureId));
            if (index >= 0) {
                this.selected.splice(index, 1);
                this.onInput(this.selected);
            }
        },
        getFeatureCreateMeta(postType) {
            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            const meta = cfg && cfg.featureCreate && cfg.featureCreate[postType]
                ? cfg.featureCreate[postType]
                : null;

            return {
                canCreate: !!(meta && meta.can_create),
                createLink: meta && meta.can_create && meta.create_link ? String(meta.create_link) : '',
                label: meta && meta.label ? String(meta.label) : this.getFeatureTypeLabel(postType),
                createLabel: meta && meta.create_label ? String(meta.create_label) : ''
            };
        },
        getCreateFeatureLabel(postType) {
            const meta = this.getFeatureCreateMeta(postType);
            if (meta.createLabel) {
                return meta.createLabel;
            }
            return 'New ' + (meta.label || this.getFeatureTypeLabel(postType)).toLowerCase();
        },
        openFeatureCreate(postType) {
            const createLink = this.getFeatureCreateMeta(postType).createLink;
            if (!createLink) {
                return;
            }
            window.open(createLink, '_blank', 'noopener,noreferrer');
        },
        getAllowedFeaturePostTypes() {
            const options = this.itemMetadatum && this.itemMetadatum.metadatum && this.itemMetadatum.metadatum.metadata_type_options
                ? this.itemMetadatum.metadatum.metadata_type_options
                : {};
            const allowed = options.allowed_feature_types;

            if (!Array.isArray(allowed) || !allowed.length) {
                return DEFAULT_FEATURE_TYPES.slice();
            }

            return allowed.filter((type) => DEFAULT_FEATURE_TYPES.indexOf(type) !== -1);
        },
        getFeatureTypeLabel(postType) {
            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            if (cfg && cfg.featureTypeLabels && cfg.featureTypeLabels[postType]) {
                return cfg.featureTypeLabels[postType];
            }
            return FEATURE_TYPE_LABELS[postType] || postType;
        },
        getFeatureMeta(post) {
            const selfLink = post && post._links && Array.isArray(post._links.self) ? post._links.self[0] : null;
            const allow = selfLink && selfLink.targetHints && Array.isArray(selfLink.targetHints.allow)
                ? selfLink.targetHints.allow
                : [];
            const canEdit = allow.indexOf('PUT') !== -1 || allow.indexOf('PATCH') !== -1;

            let editLink = '';
            if (canEdit && post && post.id) {
                const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
                const editPostUrl = cfg && cfg.editPostUrl ? String(cfg.editPostUrl) : '';
                if (editPostUrl) {
                    editLink = editPostUrl + (editPostUrl.indexOf('?') === -1 ? '?' : '&') + 'post=' + encodeURIComponent(post.id) + '&action=edit';
                }
            }

            return {
                canEdit: canEdit,
                editLink: editLink
            };
        },
        stripHtml(html) {
            if (!html) {
                return '';
            }
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            return tmp.textContent || tmp.innerText || '';
        },
        normalizeOptions(posts, postType) {
            const typeLabel = this.getFeatureTypeLabel(postType);
            return posts.map((post) => {
                const title = post.title && post.title.rendered
                    ? this.stripHtml(post.title.rendered)
                    : '#' + post.id;
                const featureMeta = this.getFeatureMeta(post);
                return {
                    value: String(post.id),
                    label: title,
                    postType: postType,
                    typeLabel: typeLabel,
                    canEdit: featureMeta.canEdit,
                    editLink: featureMeta.editLink
                };
            });
        },
        fetchFeatureById(featureId) {
            const postTypes = this.getAllowedFeaturePostTypes();
            if (!postTypes.length || !featureId) {
                return Promise.resolve(null);
            }

            const tryNext = (index) => {
                if (index >= postTypes.length) {
                    return Promise.resolve(null);
                }

                const postType = postTypes[index];
                return window.wp.apiFetch({
                    path: `/wp/v2/${postType}/${featureId}?_fields=${REST_FIELDS}`
                }).then((post) => {
                    return this.normalizeOptions([post], postType)[0];
                }).catch(() => {
                    return tryNext(index + 1);
                });
            };

            return tryNext(0);
        },
        fetchFeaturesByIds(featureIds) {
            this.isLoading = true;
            return Promise.all(
                featureIds.map((featureId) => this.fetchFeatureById(featureId))
            ).then((features) => {
                this.selected = features.filter(Boolean);
            }).finally(() => {
                this.isLoading = false;
            });
        },
        searchMore() {
            if (!this.hasMore || !this.searchQuery) {
                return;
            }
            this.search(this.searchQuery, true);
        },
        search(query, append) {
            if (query != this.searchQuery) {
                this.searchQuery = query;
                this.options = [];
                this.page = 1;
                this.hasMore = true;
            }

            if (!query || !query.length) {
                this.searchQuery = '';
                this.options = [];
                this.page = 1;
                this.hasMore = true;
                this.isLoading = false;
                return;
            }

            if (this.selected.length > 0 && this.itemMetadatum.metadatum.multiple === 'no') {
                return;
            }

            const postTypes = this.getAllowedFeaturePostTypes();
            if (!postTypes.length) {
                this.isLoading = false;
                return;
            }

            this.isLoading = true;
            const page = this.page;
            const searchToken = ++this.searchRequestToken;
            const selectedIds = this.selected.map((item) => String(item.value));

            Promise.all(
                postTypes.map((postType) => {
                    return window.wp.apiFetch({
                        path: `/wp/v2/${postType}?search=${encodeURIComponent(query)}&_fields=${REST_FIELDS}&orderby=title&order=asc&per_page=12&page=${page}`
                    }).then((posts) => {
                        return this.normalizeOptions(Array.isArray(posts) ? posts : [], postType)
                            .filter((option) => selectedIds.indexOf(String(option.value)) === -1);
                    }).catch(() => {
                        return [];
                    });
                })
            ).then((resultsByType) => {
                if (searchToken !== this.searchRequestToken) {
                    return;
                }
                let merged = [];
                resultsByType.forEach((chunk) => {
                    merged = merged.concat(chunk);
                });
                merged.sort((a, b) => a.label.localeCompare(b.label));
                this.options = append ? this.options.concat(merged) : merged;
                this.hasMore = resultsByType.some((chunk) => chunk.length === 12);
                this.page += 1;
            }).finally(() => {
                if (searchToken === this.searchRequestToken) {
                    this.isLoading = false;
                }
            });
        }
    }
};

	__tainacanMapsterMetadataTypeComponent.template = "\n    <div\n            class=\"tainacan-mapster-feature-input\"\n            :class=\"{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' }\">\n        <b-tabs\n                v-model=\"activeTab\"\n                size=\"is-small\"\n                animated>\n            <b-tab-item :label=\"selectTabLabel\">\n                <b-taginput\n                        :id=\"featureInputId\"\n                        v-a11y-autocomplete\n                        expanded\n                        :disabled=\"disabled\"\n                        size=\"is-small\"\n                        icon=\"magnify\"\n                        :model-value=\"JSON.parse(JSON.stringify(selected))\"\n                        :data=\"options\"\n                        :maxtags=\"maxTags\"\n                        autocomplete\n                        :remove-on-keys=\"[]\"\n                        :dropdown-position=\"isLastMetadatum ? 'top' : 'auto'\"\n                        attached\n                        :placeholder=\"itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_existing_item')\"\n                        :loading=\"isLoading\"\n                        :aria-close-label=\"$i18n.get('remove_value')\"\n                        :class=\"{ 'has-selected': selected.length > 0 }\"\n                        field=\"label\"\n                        check-infinite-scroll\n                        :has-counter=\"false\"\n                        @update:model-value=\"onInput\"\n                        @blur=\"onBlur\"\n                        @typing=\"onTyping\"\n                        @infinite-scroll=\"searchMore\"\n                        @focus=\"onMobileSpecialFocus\">\n                    <template #default=\"props\">\n                        <div\n                                v-if=\"props.option\"\n                                class=\"tainacan-mapster-autocomplete-option\">\n                            <span class=\"tainacan-mapster-autocomplete-option__title ellipsed-text\">{{ props.option.label }}</span>\n                            <span\n                                    v-if=\"props.option.typeLabel\"\n                                    class=\"tainacan-mapster-autocomplete-option__type\">{{ props.option.typeLabel }}</span>\n                        </div>\n                    </template>\n                    <template #tag=\"props\">\n                        {{ (props.tag && props.tag.label) ? props.tag.label : '' }}\n                    </template>\n                    <template\n                            v-if=\"!isLoading\"\n                            #empty>\n                        {{ $i18n.get('info_no_item_found') }}\n                    </template>\n                </b-taginput>\n            </b-tab-item>\n            <b-tab-item\n                    v-if=\"itemMetadatum && itemMetadatum.value !== undefined\"\n                    style=\"min-height: 56px;\"\n                    :label=\"selectedTabLabel\">\n                <div class=\"tainacan-mapster-results-container\">\n                    <div\n                            v-if=\"selected.length\"\n                            class=\"tainacan-mapster-selected-group\">\n                        <div\n                                v-for=\"(feature, index) of selected\"\n                                :key=\"feature.value + '-' + index\"\n                                class=\"tainacan-mapster-selected-item\">\n                            <div class=\"tainacan-mapster-selected-item__content\">\n                                <span class=\"tainacan-mapster-selected-item__label\">{{ feature.label }}</span>\n                                <span\n                                        v-if=\"feature.typeLabel\"\n                                        class=\"tainacan-mapster-selected-item__type\">{{ feature.typeLabel }}</span>\n                            </div>\n                            <a\n                                    v-if=\"feature.editLink\"\n                                    class=\"tainacan-mapster-value-button--edit\"\n                                    :href=\"feature.editLink\"\n                                    target=\"_blank\"\n                                    rel=\"noopener noreferrer\"\n                                    :aria-label=\"editFeatureLabel\"\n                                    :title=\"editFeatureLabel\">\n                                <span\n                                        aria-hidden=\"true\"\n                                        class=\"icon\">\n                                    <i class=\"tainacan-icon tainacan-icon-edit\" />\n                                </span>\n                            </a>\n                            <a\n                                    class=\"tainacan-mapster-value-button--remove\"\n                                    :aria-label=\"$i18n.get('label_remove')\"\n                                    :title=\"$i18n.get('label_remove')\"\n                                    tabindex=\"0\"\n                                    role=\"button\"\n                                    @click.prevent=\"removeFromSelected(feature.value)\"\n                                    @keydown.enter.prevent=\"removeFromSelected(feature.value)\"\n                                    @keydown.space.prevent=\"removeFromSelected(feature.value)\">\n                                <span\n                                        aria-hidden=\"true\"\n                                        class=\"icon\">\n                                    <i class=\"tainacan-icon tainacan-icon-close\" />\n                                </span>\n                            </a>\n                        </div>\n                    </div>\n                    <div v-else>\n                        <p\n                                class=\"has-text-dark\"\n                                style=\"font-size: 0.875em;\">\n                            {{ $i18n.get('info_no_item_found') }}\n                        </p>\n                    </div>\n                </div>\n            </b-tab-item>\n        </b-tabs>\n        <div\n                v-if=\"showSecondaryActions\"\n                class=\"tainacan-mapster-toolbar\">\n            <template v-if=\"selected.length\">\n                <a\n                        v-if=\"configuredMapId\"\n                        class=\"tainacan-mapster-preview-toggle add-link\"\n                        role=\"button\"\n                        tabindex=\"0\"\n                        @click.prevent=\"togglePreview\"\n                        @keydown.enter.prevent=\"togglePreview\"\n                        @keydown.space.prevent=\"togglePreview\">\n                    <span\n                            aria-hidden=\"true\"\n                            class=\"icon\">\n                        <i class=\"tainacan-icon has-text-secondary tainacan-icon-see\" />\n                    </span>\n                    &nbsp;{{ previewMapLabel }}\n                </a>\n                <p\n                        v-else\n                        class=\"tainacan-mapster-preview-missing help\">\n                    {{ previewMapMissing }}\n                </p>\n            </template>\n            <div\n                    v-if=\"creatableFeatureTypes.length && !disabled && canAddMore\"\n                    class=\"tainacan-mapster-toolbar__create\">\n                <a\n                        v-for=\"postType in creatableFeatureTypes\"\n                        :key=\"postType\"\n                        class=\"tainacan-mapster-create-feature add-link\"\n                        tabindex=\"0\"\n                        role=\"button\"\n                        @click.prevent=\"openFeatureCreate(postType)\"\n                        @keydown.enter.prevent=\"openFeatureCreate(postType)\"\n                        @keydown.space.prevent=\"openFeatureCreate(postType)\">\n                    <span\n                            aria-hidden=\"true\"\n                            class=\"icon is-small\">\n                        <i class=\"tainacan-icon has-text-secondary tainacan-icon-add\" />\n                    </span>\n                    &nbsp;{{ getCreateFeatureLabel(postType) }}\n                </a>\n            </div>\n        </div>\n        <transition name=\"filter-item\">\n            <div\n                    v-if=\"isPreviewing && previewEmbedUrl\"\n                    class=\"tainacan-mapster-preview\">\n                <iframe\n                        :key=\"previewEmbedUrl\"\n                        class=\"tainacan-mapster-preview__frame\"\n                        :src=\"previewEmbedUrl\"\n                        :title=\"previewMapLabel\"\n                        loading=\"lazy\"\n                        allowfullscreen />\n            </div>\n        </transition>\n    </div>\n";
	window.tainacan_extra_components["tainacan-metadata-type-mapster-single-feature"] = __tainacanMapsterMetadataTypeComponent;
})();
