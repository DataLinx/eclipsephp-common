@php
    $isDraggable = $isDragReorderable();
    $hasLightboxPreview = $hasLightbox();
    $gridClasses = $getGridClasses();
    $gridStyle = $getGridStyle();
    $thumbnailHeight = $getThumbnailHeight();
    $statePath = $getStatePath();
    $componentKey = $getKey();
    $gridId = 'media-gallery-grid-' . str_replace(['.', '[', ']'], '-', $statePath);
@endphp

@assets(['eclipse-common::media-gallery-styles', 'eclipse-common::media-gallery-scripts'])

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div x-data="{
        ...window.mediaGallery({
            state: $wire.{{ $applyStateBindingModifiers("entangle('{$statePath}')") }},
            getLocale: () => $wire.activeLocale || 'en',
        }),

        selectedImages: [],

        bulkActionsOpen: false,

        draggedIndex: null,

        get hasSelection() { return this.selectedImages.length > 0; },

        get selectedCount() { return this.selectedImages.length; },

        get totalCount() { return this.state ? this.state.length : 0; },

        get allSelected() { return this.totalCount > 0 && this.selectedImages.length === this.totalCount; },

        get someSelected() { return this.selectedImages.length > 0 && this.selectedImages.length < this.totalCount; },

        toggleSelectAll() {
            if (this.allSelected) {
                this.selectedImages = [];
            } else {
                this.selectedImages = this.state ? this.state.map(img => img.uuid) : [];
            }
            this.updateBulkActionsVisibility();
        },

        toggleImageSelection(uuid) {
            const index = this.selectedImages.indexOf(uuid);
            if (index > -1) {
                this.selectedImages.splice(index, 1);
            } else {
                this.selectedImages.push(uuid);
            }
            this.updateBulkActionsVisibility();
        },

        updateBulkActionsVisibility() {
            this.bulkActionsOpen = this.hasSelection;
        },

        clearSelection() {
            this.selectedImages = [];
            this.bulkActionsOpen = false;
        },

        async bulkDelete() {
            if (this.selectedImages.length === 0) return;

            try {
                await $wire.mountAction('bulkDelete', { uuids: this.selectedImages }, { schemaComponent: '{{ $componentKey }}' });
                this.clearSelection();
            } catch (error) {
                // Don't clear selection on error so user can retry
            }
        }
    }" wire:key="media-gallery-{{ str_replace('.', '-', $statePath) }}"
        class="eclipse-media-gallery">
        <div x-show="state && state.length > 0" class="eclipse-media-gallery-header">
            @if ($getAllowBulkDelete())
            <div class="eclipse-media-gallery-bulk-controls">
                <label class="eclipse-media-gallery-select-label">
                    <x-filament::input.checkbox :checked="false" x-bind:checked="allSelected"
                        x-on:change="toggleSelectAll()" />
                    <span class="eclipse-media-gallery-select-text">
                        <span x-show="!hasSelection">Select All</span>
                        <span x-show="allSelected">Deselect All</span>
                        <span x-show="someSelected && !allSelected" x-text="`${selectedCount} selected`"></span>
                    </span>
                </label>

                <div x-show="hasSelection" x-transition class="eclipse-media-gallery-bulk-actions">
                    <x-filament::button size="xs" color="danger" icon="heroicon-o-trash" x-on:click="bulkDelete()">
                        Delete
                    </x-filament::button>
                </div>
            </div>
            @else
            <div></div>
            @endif

            <div class="eclipse-media-gallery-action-buttons">
                @if ($getAllowFileUploads() && $getAction('upload'))
                    <x-filament::button size="xs" color="primary" icon="heroicon-o-arrow-up-tray"
                        x-on:click="$wire.mountAction('upload', {}, { schemaComponent: '{{ $componentKey }}' })">
                        Upload Files
                    </x-filament::button>
                @endif
                @if ($getAllowUrlUploads() && $getAction('urlUpload'))
                    <x-filament::button size="xs" color="gray" icon="heroicon-o-link"
                        x-on:click="$wire.mountAction('urlUpload', {}, { schemaComponent: '{{ $componentKey }}' })">
                        Add from URL
                    </x-filament::button>
                @endif
            </div>
        </div>

        <div x-cloak wire:ignore>
            <div x-show="state && state.length > 0" class="eclipse-media-gallery-grid-wrapper">

                @if (str_contains($gridStyle, '@media'))
                    <style>
                        #{{ $gridId }} {
                            {{ $gridStyle }}
                        }
                    </style>
                    <div id="{{ $gridId }}" class="{{ $gridClasses }}">
                    @else
                        <div class="{{ $gridClasses }}" style="{{ $gridStyle }}">
                @endif
                <template x-for="(image, index) in state" :key="image.uuid">
                    <div @class([
                        'eclipse-media-gallery-card',
                        'draggable' => $isDraggable,
                        'hoverable' => !$isDraggable,
                    ])
                        :class="{
                            'selected': selectedImages.includes(image.uuid),
                            'dragged': draggedIndex === index,
                            'drag-over': draggedIndex !== null && draggedIndex !== index
                        }"
                        @if ($isDraggable) draggable="true"
                        @dragstart="draggedIndex = index"
                        @dragover.prevent
                        @drop.prevent="
                            if (draggedIndex !== index) {
                                const item = state.splice(draggedIndex, 1)[0];
                                state.splice(index, 0, item);
                                $wire.mountAction('reorder', { items: state.map(img => img.uuid) }, { schemaComponent: '{{ $componentKey }}' });
                            }
                            draggedIndex = null;
                        "
                        @dragend="draggedIndex = null" @endif>

                        <div class="eclipse-image-card-container"
                            style="height: {{ $thumbnailHeight }}px;">
                            <img :src="image.thumb_url || image.url" :alt="image.file_name"
                                @class([
                                    'eclipse-image-card-img',
                                    'clickable' => $hasLightboxPreview,
                                ])
                                draggable="false"
                                @if ($hasLightboxPreview) @click="openImageModal(index)" @endif />


                            <div class="eclipse-image-card-cover-badge">
                                <template x-if="!image.is_cover">
                                    <x-filament::button size="xs" color="primary"
                                        x-on:click.stop="$wire.mountAction('setCover', { uuid: image.uuid }, { schemaComponent: '{{ $componentKey }}' })"
                                        class="shadow-sm">
                                        Set as Cover
                                    </x-filament::button>
                                </template>
                                <template x-if="image.is_cover">
                                    <x-filament::button size="xs" color="success" class="shadow-sm" disabled>
                                        ✓ Cover
                                    </x-filament::button>
                                </template>
                            </div>
                        </div>
                        <div class="eclipse-image-card-content">
                            <p class="eclipse-image-card-title"
                                x-text="getLocalizedName(image)"></p>
                            <p class="eclipse-image-card-description"
                                x-text="getLocalizedDescription(image)" x-show="getLocalizedDescription(image)"></p>
                            <div class="eclipse-image-card-actions">
                                <div class="eclipse-image-card-buttons">
                                    <x-filament::button size="xs" color="gray" icon="heroicon-m-pencil-square"
                                        x-on:click="$wire.mountAction('editImage', { uuid: image.uuid, selectedLocale: getLocale() }, { schemaComponent: '{{ $componentKey }}' })">
                                        Edit
                                    </x-filament::button>

                                    <x-filament::button size="xs" color="danger" icon="heroicon-m-trash"
                                        x-on:click="$wire.mountAction('deleteImage', { uuid: image.uuid }, { schemaComponent: '{{ $componentKey }}' })">
                                        Delete
                                    </x-filament::button>
                                </div>

                                @if ($getAllowBulkDelete())
                                <x-filament::input.checkbox :checked="false"
                                    x-bind:checked="selectedImages.includes(image.uuid)"
                                    x-on:change="toggleImageSelection(image.uuid)" />
                                @endif
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="!state || state.length === 0"
            class="eclipse-media-gallery-empty">
            <div class="eclipse-media-gallery-empty-content">
                <div class="eclipse-media-gallery-empty-icon-wrapper">
                    <x-filament::icon icon="heroicon-o-photo" class="eclipse-media-gallery-empty-icon" />
                </div>
                <div class="eclipse-media-gallery-empty-text">
                    <p class="eclipse-media-gallery-empty-title">No images uploaded yet</p>
                    <p class="eclipse-media-gallery-empty-description">
                        @if ($getAllowFileUploads() || $getAllowUrlUploads())
                            Click "Upload Files" or "Add from URL" to add your first image
                        @else
                            No images are currently available for this gallery
                        @endif
                    </p>
                </div>

                @if ($getAllowFileUploads() || $getAllowUrlUploads())
                    <div class="eclipse-media-gallery-empty-actions">
                        @if ($getAllowFileUploads() && $getAction('upload'))
                            {{ $getAction('upload') }}
                        @endif
                        @if ($getAllowUrlUploads() && $getAction('urlUpload'))
                            {{ $getAction('urlUpload') }}
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($hasLightboxPreview)
        @include('eclipse-common::components.media-lightbox')
    @endif
    </div>
</x-dynamic-component>
