/**
 * Mapster Map items-list view mode (iframe embed).
 *
 * Component tag must match tainacan_register_view_mode() `component`:
 * view-mode-mapster
 */
window.tainacan_extra_components = typeof window.tainacan_extra_components !== 'undefined'
	? window.tainacan_extra_components
	: {};

if ( ! window.tainacan_extra_components['view-mode-mapster'] ) {
	window.tainacan_extra_components['view-mode-mapster'] = {
		name: 'ViewModeMapster',
		props: {
			collectionId: [String, Number],
			displayedMetadata: {
				type: Array,
				default: function() {
					return [];
				}
			},
			items: {
				type: Array,
				default: function() {
					return [];
				}
			},
			isLoading: Boolean,
			totalItems: {
				type: Number,
				default: 0
			},
			enabledViewModes: {
				type: Array,
				default: function() {
					return [];
				}
			},
			shouldHideItemsThumbnail: Boolean,
			filtersModalStateHasChanged: Boolean,
			initialItemPosition: Number,
			isRepositoryLevel: Boolean,
			termId: [String, Number]
		},
		data: function() {
			return {
				selectedMapsterMetadatumId: false,
				focusedItemId: null,
				hoveredItemId: null
			};
		},
		computed: {
			cfg: function() {
				return typeof tainacanMapsterViewMode !== 'undefined' ? tainacanMapsterViewMode : {};
			},
			mapsterMetadata: function() {
				var list = Array.isArray( this.displayedMetadata ) ? this.displayedMetadata : [];
				var out = {};
				list.forEach( function( metadatum ) {
					if ( ! metadatum || ! metadatum.id ) {
						return;
					}
					if ( ! this.isMapsterMetadataType( metadatum.metadata_type ) ) {
						return;
					}
					out[ String( metadatum.id ) ] = metadatum;
				}.bind( this ) );
				return out;
			},
			selectedMapsterMetadatum: function() {
				var id = this.selectedMapsterMetadatumId;
				if ( ! id || ! this.mapsterMetadata[ String( id ) ] ) {
					return null;
				}
				return this.mapsterMetadata[ String( id ) ];
			},
			configuredMapId: function() {
				var meta = this.selectedMapsterMetadatum;
				if ( ! meta || ! meta.metadata_type_options ) {
					return 0;
				}
				var mapId = parseInt( meta.metadata_type_options.mapster_map_id, 10 );
				return mapId > 0 ? mapId : 0;
			},
			titleItemMetadatum: function() {
				var list = Array.isArray( this.displayedMetadata ) ? this.displayedMetadata : [];
				return list.find( function( metadatum ) {
					return metadatum &&
						metadatum.display &&
						metadatum.metadata_type_object &&
						metadatum.metadata_type_object.related_mapped_prop === 'title';
				} ) || null;
			},
			recordMetadata: function() {
				var list = Array.isArray( this.displayedMetadata ) ? this.displayedMetadata : [];
				return list.filter( function( column ) {
					if ( ! column || ! column.display || column.slug === 'thumbnail' ) {
						return false;
					}
					if ( this.isMapsterMetadataType( column.metadata_type ) ) {
						return false;
					}
					if (
						column.metadata_type_object &&
						column.metadata_type_object.related_mapped_prop === 'title'
					) {
						return false;
					}
					return true;
				}.bind( this ) );
			},
			listItems: function() {
				var meta = this.selectedMapsterMetadatum;
				if ( ! Array.isArray( this.items ) ) {
					return [];
				}
				var slug = meta && meta.slug ? meta.slug : '';
				var rows = [];
				this.items.forEach( function( item ) {
					if ( ! item ) {
						return;
					}
					var ids = slug ? this.getItemFeatureIds( item, slug ) : [];
					rows.push( {
						item: item,
						featureIds: ids,
						located: ids.length > 0,
						title: this.getItemTitle( item ),
						url: this.getItemUrl( item ),
						thumbnailSrc: this.getCardThumbnailSrc( item ),
						recordThumbnailSrc: this.getThumbnailSrc( item )
					} );
				}.bind( this ) );
				return rows;
			},
			locatedItems: function() {
				return this.listItems.filter( function( row ) {
					return row.located;
				} );
			},
			focusedRow: function() {
				if ( this.focusedItemId == null ) {
					return null;
				}
				return this.listItems.find( function( row ) {
					return String( row.item.id ) === String( this.focusedItemId );
				}.bind( this ) ) || null;
			},
			featureIds: function() {
				if ( this.focusedRow ) {
					return this.focusedRow.located ? this.focusedRow.featureIds.slice() : [];
				}
				var seen = {};
				var ids = [];
				this.locatedItems.forEach( function( row ) {
					row.featureIds.forEach( function( id ) {
						if ( ! seen[ id ] ) {
							seen[ id ] = true;
							ids.push( id );
						}
					} );
				} );
				return ids;
			},
			embedUrl: function() {
				if ( ! this.configuredMapId ) {
					return '';
				}
				return this.buildEmbedUrl( this.configuredMapId, this.featureIds );
			},
			prefsKey: function() {
				return this.collectionId
					? 'mapster_view_mode_selected_metadatum_' + this.collectionId
					: 'mapster_view_mode_selected_metadatum';
			},
			hasMapsterMetadata: function() {
				return Object.keys( this.mapsterMetadata ).length > 0;
			},
			mapStateLabel: function() {
				if ( this.focusedRow ) {
					if ( ! this.focusedRow.located ) {
						return this.cfg.focusedNoLocationLabel || 'Selected item has no map elements; showing the base map.';
					}
					var n = this.focusedRow.featureIds.length;
					return ( this.cfg.focusedElementsLabel || 'Showing %d element(s) for the selected item.' )
						.replace( '%d', String( n ) );
				}
				if ( ! this.locatedItems.length ) {
					return this.cfg.noFeaturesLabel || 'No items with map elements on this page; showing the base map.';
				}
				return ( this.cfg.elementsOnPageLabel || '%d element(s) on this page.' )
					.replace( '%d', String( this.featureIds.length ) );
			},
			statusMessage: function() {
				if ( this.isLoading ) {
					return this.cfg.loadingLabel || 'Loading…';
				}
				if ( ! this.hasMapsterMetadata ) {
					return this.cfg.noMetadataLabel || 'Add a Mapster Map metadatum to the displayed metadata for this collection to use this view mode.';
				}
				if ( ! this.configuredMapId ) {
					return this.cfg.noMapLabel || 'The selected Mapster Map metadatum has no base map configured.';
				}
				if ( ! this.items || ! this.items.length ) {
					return this.cfg.noItemsLabel || 'No items to show.';
				}
				return '';
			}
		},
		watch: {
			mapsterMetadata: {
				immediate: true,
				handler: function() {
					this.syncSelectedMetadatum();
				}
			},
			selectedMapsterMetadatumId: function( value ) {
				this.clearFocus();
				if ( value && this.$userPrefs && typeof this.$userPrefs.set === 'function' ) {
					this.$userPrefs.set( this.prefsKey, value );
				}
			},
			items: function() {
				if ( this.focusedItemId == null ) {
					return;
				}
				if ( ! this.focusedRow ) {
					this.clearFocus();
				}
			}
		},
		methods: {
			isMapsterMetadataType: function( type ) {
				return !! ( type && String( type ).indexOf( 'Mapster_Feature' ) !== -1 );
			},
			getItemFeatureIds: function( item, slug ) {
				if ( ! item || ! item.metadata || ! item.metadata[ slug ] ) {
					return [];
				}
				var value = item.metadata[ slug ].value;
				var parts = Array.isArray( value ) ? value : ( value || value === 0 ? [ value ] : [] );
				var seen = {};
				var ids = [];
				parts.forEach( function( raw ) {
					var id = parseInt( raw, 10 );
					if ( id > 0 && ! seen[ id ] ) {
						seen[ id ] = true;
						ids.push( id );
					}
				} );
				return ids;
			},
			getItemTitle: function( item ) {
				if ( ! item ) {
					return '';
				}
				if ( this.collectionId && this.titleItemMetadatum ) {
					var rendered = this.renderMetadata( item, this.titleItemMetadatum );
					if ( rendered ) {
						return rendered;
					}
				}
				if ( item.title ) {
					return String( item.title );
				}
				return '#' + item.id;
			},
			getItemTitleText: function( item ) {
				if ( ! item ) {
					return '';
				}
				if ( item.title ) {
					return String( item.title );
				}
				return '#' + item.id;
			},
			getItemUrl: function( item ) {
				if ( ! item ) {
					return '';
				}
				if ( item.url ) {
					return String( item.url );
				}
				if ( item.link ) {
					return String( item.link );
				}
				return '';
			},
			getThumbnailSrc: function( item ) {
				if ( this.shouldHideItemsThumbnail || ! item || ! item.thumbnail ) {
					return '';
				}
				var thumb = item.thumbnail;
				var candidates = [
					'tainacan-medium-full',
					'medium_large',
					'tainacan-medium',
					'medium',
					'tainacan-small',
					'thumbnail'
				];
				for ( var i = 0; i < candidates.length; i++ ) {
					var key = candidates[ i ];
					if ( thumb[ key ] && thumb[ key ][ 0 ] ) {
						return thumb[ key ][ 0 ];
					}
				}
				return '';
			},
			getCardThumbnailSrc: function( item ) {
				if ( this.shouldHideItemsThumbnail || ! item || ! item.thumbnail ) {
					return '';
				}
				var thumb = item.thumbnail;
				var candidates = [
					'tainacan-small',
					'thumbnail',
					'tainacan-medium',
					'medium',
					'tainacan-medium-full'
				];
				for ( var i = 0; i < candidates.length; i++ ) {
					var key = candidates[ i ];
					if ( thumb[ key ] && thumb[ key ][ 0 ] ) {
						return thumb[ key ][ 0 ];
					}
				}
				return '';
			},
			renderMetadata: function( item, metadatum ) {
				if ( ! item || ! metadatum ) {
					return '';
				}
				if ( item.metadata && item.metadata[ metadatum.slug ] != null ) {
					var metadata = item.metadata[ metadatum.slug ];
					if ( metadata.value_as_html ) {
						return metadata.value_as_html;
					}
					if ( metadata.value_as_string ) {
						return metadata.value_as_string;
					}
					return '';
				}
				if (
					metadatum.metadata_type_object &&
					metadatum.metadata_type_object.core &&
					metadatum.metadata_type_object.related_mapped_prop &&
					item[ metadatum.metadata_type_object.related_mapped_prop ]
				) {
					return String( item[ metadatum.metadata_type_object.related_mapped_prop ] );
				}
				return '';
			},
			recordFields: function( item ) {
				if ( ! item ) {
					return [];
				}
				var fields = [];
				this.recordMetadata.forEach( function( column ) {
					var html = this.renderMetadata( item, column );
					if ( ! html ) {
						return;
					}
					fields.push( {
						id: column.id,
						name: column.name,
						html: html
					} );
				}.bind( this ) );
				return fields;
			},
			syncSelectedMetadatum: function() {
				var ids = Object.keys( this.mapsterMetadata );
				if ( ! ids.length ) {
					this.selectedMapsterMetadatumId = false;
					return;
				}
				var preferred = null;
				if ( this.$userPrefs && typeof this.$userPrefs.get === 'function' ) {
					preferred = this.$userPrefs.get( this.prefsKey );
				}
				if ( preferred && this.mapsterMetadata[ String( preferred ) ] ) {
					this.selectedMapsterMetadatumId = String( preferred );
					return;
				}
				this.selectedMapsterMetadatumId = ids[0];
			},
			onChangeSelectedMetadatum: function( event ) {
				this.selectedMapsterMetadatumId = event && event.target ? event.target.value : event;
			},
			isFocused: function( itemId ) {
				return this.focusedItemId != null && String( this.focusedItemId ) === String( itemId );
			},
			isHovered: function( itemId ) {
				return this.hoveredItemId != null && String( this.hoveredItemId ) === String( itemId );
			},
			focusItemOnMap: function( itemId ) {
				this.focusedItemId = itemId;
			},
			clearFocus: function() {
				this.focusedItemId = null;
			},
			buildEmbedUrl: function( mapId, featureIds ) {
				var ids = ( Array.isArray( featureIds ) ? featureIds : [ featureIds ] )
					.map( function( id ) {
						return parseInt( id, 10 );
					} )
					.filter( function( id ) {
						return id > 0;
					} );

				if ( ! mapId ) {
					return '';
				}

				var homeUrl = this.cfg.homeUrl ? String( this.cfg.homeUrl ) : '/';
				var url;
				try {
					url = new URL( homeUrl, window.location.origin );
				} catch ( e ) {
					url = new URL( '/', window.location.origin );
				}

				url.searchParams.set( 'tainacan_mapster_embed', '1' );
				url.searchParams.set( 'map_id', String( mapId ) );
				url.searchParams.delete( 'single_feature_id' );
				url.searchParams.delete( 'feature_ids' );

				if ( ids.length === 1 ) {
					url.searchParams.set( 'single_feature_id', String( ids[0] ) );
				} else if ( ids.length > 1 ) {
					url.searchParams.set( 'feature_ids', ids.join( ',' ) );
				}

				return url.toString();
			}
		},
		template: `
			<div class="tainacan-mapster-view-mode">
				<slot />

				<div
						v-if="statusMessage"
						class="tainacan-mapster-view-mode__status">
					<p>{{ statusMessage }}</p>
				</div>

				<div
						v-else
						class="tainacan-mapster-view-mode__panel"
						:class="{ 'has-selected-item': focusedItemId != null }">
					<div
							v-if="hasMapsterMetadata"
							class="tainacan-mapster-view-mode__toolbar">
						<label class="tainacan-mapster-view-mode__label">
							{{ cfg.showingLabel || 'Showing map elements for' }}
						</label>
						<span class="select">
							<select
									:value="selectedMapsterMetadatumId"
									@change="onChangeSelectedMetadatum">
								<option
										v-for="metadatum in mapsterMetadata"
										:key="metadatum.id"
										:value="String(metadatum.id)">
									{{ metadatum.name }}
								</option>
							</select>
						</span>
						<span class="tainacan-mapster-view-mode__count">
							{{ mapStateLabel }}
						</span>
					</div>

					<div class="tainacan-mapster-view-mode__layout">
						<ul class="tainacan-mapster-view-mode__cards">
							<li
									v-for="row in listItems"
									:key="row.item.id"
									@mouseenter="hoveredItemId = row.item.id"
									@mouseleave="hoveredItemId = null">
								<div
										class="tainacan-mapster-view-mode__card"
										:class="{
											'is-focused': isFocused(row.item.id),
											'is-hovered': isHovered(row.item.id),
											'is-non-located': !row.located
										}"
										role="button"
										tabindex="0"
										@click="focusItemOnMap(row.item.id)"
										@keydown.enter.prevent="focusItemOnMap(row.item.id)"
										@keydown.space.prevent="focusItemOnMap(row.item.id)">
									<div class="tainacan-mapster-view-mode__card-title metadata-title">
										<div
												class="tainacan-mapster-view-mode__card-title-text"
												v-html="row.title" />
										<img
												v-if="row.thumbnailSrc"
												class="tainacan-mapster-view-mode__card-thumb"
												:src="row.thumbnailSrc"
												:alt="getItemTitleText(row.item)"
												width="40"
												height="40" />
									</div>
								</div>
							</li>
						</ul>

						<div class="tainacan-mapster-view-mode__stage">
							<aside
									v-if="focusedRow"
									class="tainacan-mapster-view-mode__record">
								<button
										type="button"
										class="tainacan-mapster-view-mode__record-close"
										:aria-label="cfg.showAllLabel || 'Show all on map'"
										@click.stop="clearFocus">
									×
								</button>
								<a
										v-if="focusedRow.url"
										class="tainacan-mapster-view-mode__record-link-wrap"
										:href="focusedRow.url"
										:aria-label="cfg.openItemLabel || 'Open item'">
									<div class="tainacan-mapster-view-mode__record-title metadata-title">
										<div v-html="focusedRow.title" />
									</div>
									<div class="tainacan-mapster-view-mode__record-body">
										<img
												v-if="focusedRow.recordThumbnailSrc"
												class="tainacan-mapster-view-mode__record-thumb"
												:src="focusedRow.recordThumbnailSrc"
												:alt="getItemTitleText(focusedRow.item)" />
										<template v-for="field in recordFields(focusedRow.item)" :key="field.id">
											<div class="tainacan-mapster-view-mode__record-field">
												<h3 class="metadata-label">{{ field.name }}</h3>
												<div
														class="metadata-value"
														v-html="field.html" />
											</div>
										</template>
									</div>
								</a>
								<div
										v-else
										class="tainacan-mapster-view-mode__record-fallback">
									<div class="tainacan-mapster-view-mode__record-title metadata-title">
										<div v-html="focusedRow.title" />
									</div>
									<div class="tainacan-mapster-view-mode__record-body">
										<img
												v-if="focusedRow.recordThumbnailSrc"
												class="tainacan-mapster-view-mode__record-thumb"
												:src="focusedRow.recordThumbnailSrc"
												:alt="getItemTitleText(focusedRow.item)" />
										<template v-for="field in recordFields(focusedRow.item)" :key="field.id">
											<div class="tainacan-mapster-view-mode__record-field">
												<h3 class="metadata-label">{{ field.name }}</h3>
												<div
														class="metadata-value"
														v-html="field.html" />
											</div>
										</template>
									</div>
								</div>
							</aside>

							<div class="tainacan-mapster-view-mode__map">
								<iframe
										:key="embedUrl"
										class="tainacan-mapster-view-mode__iframe"
										:src="embedUrl"
										:title="cfg.iframeTitle || 'Mapster map'"
										loading="lazy"
										allowfullscreen />
							</div>
						</div>
					</div>
				</div>
			</div>
		`
	};
}
