<template>
    <div
            class="tainacan-mapster-feature-input"
            :class="{ 'is-flex is-flex-wrap-wrap': itemMetadatum.metadatum.multiple != 'yes' }">
        <b-tabs
                v-model="activeTab"
                size="is-small"
                animated>
            <b-tab-item :label="selectTabLabel">
                <b-taginput
                        :id="featureInputId"
                        v-a11y-autocomplete
                        expanded
                        :disabled="disabled"
                        size="is-small"
                        icon="magnify"
                        :model-value="JSON.parse(JSON.stringify(selected))"
                        :data="options"
                        :maxtags="maxTags"
                        autocomplete
                        :remove-on-keys="[]"
                        :dropdown-position="isLastMetadatum ? 'top' : 'auto'"
                        attached
                        :placeholder="itemMetadatum.metadatum.placeholder ? itemMetadatum.metadatum.placeholder : $i18n.get('instruction_type_existing_item')"
                        :loading="isLoading"
                        :aria-close-label="$i18n.get('remove_value')"
                        :class="{ 'has-selected': selected.length > 0 }"
                        field="label"
                        check-infinite-scroll
                        :has-counter="false"
                        @update:model-value="onInput"
                        @blur="onBlur"
                        @typing="onTyping"
                        @infinite-scroll="searchMore"
                        @focus="onMobileSpecialFocus">
                    <template #default="props">
                        <div
                                v-if="props.option"
                                class="tainacan-mapster-autocomplete-option">
                            <span class="tainacan-mapster-autocomplete-option__title ellipsed-text">{{ props.option.label }}</span>
                            <span
                                    v-if="props.option.typeLabel"
                                    class="tainacan-mapster-autocomplete-option__type">{{ props.option.typeLabel }}</span>
                        </div>
                    </template>
                    <template #tag="props">
                        {{ (props.tag && props.tag.label) ? props.tag.label : '' }}
                    </template>
                    <template
                            v-if="!isLoading"
                            #empty>
                        {{ $i18n.get('info_no_item_found') }}
                    </template>
                </b-taginput>
            </b-tab-item>
            <b-tab-item
                    v-if="itemMetadatum && itemMetadatum.value !== undefined"
                    style="min-height: 56px;"
                    :label="selectedTabLabel">
                <div class="tainacan-mapster-results-container">
                    <div
                            v-if="selected.length"
                            class="tainacan-mapster-selected-group">
                        <div
                                v-for="(feature, index) of selected"
                                :key="feature.value + '-' + index"
                                class="tainacan-mapster-selected-item">
                            <div class="tainacan-mapster-selected-item__content">
                                <span class="tainacan-mapster-selected-item__label">{{ feature.label }}</span>
                                <span
                                        v-if="feature.typeLabel"
                                        class="tainacan-mapster-selected-item__type">{{ feature.typeLabel }}</span>
                            </div>
                            <a
                                    v-if="feature.editLink"
                                    class="tainacan-mapster-value-button--edit"
                                    :href="feature.editLink"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :aria-label="editFeatureLabel"
                                    :title="editFeatureLabel">
                                <span
                                        aria-hidden="true"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-edit" />
                                </span>
                            </a>
                            <a
                                    class="tainacan-mapster-value-button--remove"
                                    :aria-label="$i18n.get('label_remove')"
                                    :title="$i18n.get('label_remove')"
                                    tabindex="0"
                                    role="button"
                                    @click.prevent="removeFromSelected(feature.value)"
                                    @keydown.enter.prevent="removeFromSelected(feature.value)"
                                    @keydown.space.prevent="removeFromSelected(feature.value)">
                                <span
                                        aria-hidden="true"
                                        class="icon">
                                    <i class="tainacan-icon tainacan-icon-close" />
                                </span>
                            </a>
                        </div>
                    </div>
                    <div v-else>
                        <p
                                class="has-text-dark"
                                style="font-size: 0.875em;">
                            {{ $i18n.get('info_no_item_found') }}
                        </p>
                    </div>
                </div>
            </b-tab-item>
        </b-tabs>
        <div
                v-if="creatableFeatureTypes.length && !disabled && canAddMore"
                class="tainacan-mapster-create-actions">
            <a
                    v-for="postType in creatableFeatureTypes"
                    :key="postType"
                    class="tainacan-mapster-create-feature add-link"
                    tabindex="0"
                    role="button"
                    @click.prevent="openFeatureCreate(postType)"
                    @keydown.enter.prevent="openFeatureCreate(postType)"
                    @keydown.space.prevent="openFeatureCreate(postType)">
                <span
                        aria-hidden="true"
                        class="icon is-small">
                    <i class="tainacan-icon has-text-secondary tainacan-icon-add" />
                </span>
                &nbsp;{{ getCreateFeatureLabel(postType) }}
            </a>
        </div>
    </div>
</template>

<script>
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

export default {
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
            debouncedSearch: null
        };
    },
    computed: {
        editFeatureLabel() {
            const cfg = typeof tainacanMapsterMetadatumForm !== 'undefined' ? tainacanMapsterMetadatumForm : null;
            if (cfg && cfg.editFeatureLabel) {
                return cfg.editFeatureLabel;
            }
            if (this.$i18n && typeof this.$i18n.get === 'function') {
                return this.$i18n.get('label_edit');
            }
            return 'Edit feature';
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
            this.$emit('update:value', this.selected.map((item) => item.value));
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
</script>

<style>
.tainacan-mapster-feature-input {
    width: 100%;
}

.tainacan-mapster-feature-input.is-flex {
    justify-content: flex-start;
}

.tainacan-mapster-feature-input .b-tabs {
    margin-bottom: 0;
    width: 100%;
}

.tainacan-mapster-feature-input .b-tabs .tab-content {
    padding: 0.5em 0 !important;
}

.tainacan-mapster-feature-input .tabs {
    margin-bottom: 0 !important;
}

.tainacan-mapster-feature-input .tabs ul {
    padding: 0;
}

.tainacan-mapster-autocomplete-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    width: 100%;
}

.tainacan-mapster-autocomplete-option__title {
    min-width: 0;
    flex: 1 1 auto;
}

.tainacan-mapster-autocomplete-option__type {
    flex: 0 0 auto;
    margin-inline-start: auto;
    color: #7a7a7a;
    font-size: 0.8125em;
    white-space: nowrap;
}

.tainacan-mapster-results-container {
    border: 1px solid var(--tainacan-gray1, #dbdbdb);
    border-bottom-right-radius: var(--tainacan-input-border-radius, 4px);
    border-bottom-left-radius: var(--tainacan-input-border-radius, 4px);
    background-color: var(--tainacan-background-color, #fff);
    margin-top: calc(-1 * (0.5em + 1px));
    margin-bottom: calc(-1 * (0.5em + 1px));
    display: flex;
    overflow: auto;
    padding: 12px;
    max-height: 40vh;
}

.tainacan-mapster-selected-group {
    width: 100%;
}

.tainacan-mapster-selected-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    padding-right: 64px;
    padding-bottom: 0.75rem;
}

.tainacan-mapster-selected-item:not(:last-child) {
    margin-bottom: 0.5rem;
    border-bottom: 1px solid var(--tainacan-gray2, #ededed);
}

.tainacan-mapster-selected-item__content {
    display: flex;
    flex-direction: column;
    gap: 0.15rem;
    min-width: 0;
    flex: 1 1 auto;
}

.tainacan-mapster-selected-item__label {
    font-size: 0.875em;
}

.tainacan-mapster-selected-item__type {
    color: #7a7a7a;
    font-size: 0.75em;
}

.tainacan-mapster-value-button--edit,
.tainacan-mapster-value-button--remove {
    position: absolute;
    top: 0;
    right: 4px;
    background-color: var(--tainacan-white, #fff);
    border-radius: 100%;
    padding: 2px;
    color: var(--tainacan-info-color, #505253);
    text-decoration: none;
}

.tainacan-mapster-value-button--edit {
    right: 34px;
}

.tainacan-mapster-value-button--edit:hover,
.tainacan-mapster-value-button--remove:hover {
    background-color: var(--tainacan-gray0, #f6f6f6);
    color: var(--tainacan-secondary, #187181);
}

.tainacan-mapster-create-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem 0.85rem;
    width: 100%;
    margin-top: 0.35rem;
}

.tainacan-mapster-create-feature.add-link {
    font-size: 0.75em;
    display: inline-flex;
    align-items: center;
    color: var(--tainacan-secondary, #187181);
    cursor: pointer;
    text-decoration: none;
}

.ellipsed-text {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>
