@php
    $isDraggable = $isDragReorderable();
    $hasLightboxPreview = $hasLightbox();
    $gridClasses = $getGridClasses();
    $gridStyle = $getGridStyle();
    $thumbnailHeight = $getThumbnailHeight();
    $statePath = $getStatePath();
    $gridId = 'media-gallery-grid-' . str_replace(['.', '[', ']'], '-', $statePath);
@endphp

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
                await $wire.mountFormComponentAction('{{ $statePath }}', 'bulkDelete', {
                    arguments: { uuids: this.selectedImages }
                });
                this.clearSelection();
            } catch (error) {
                // Don't clear selection on error so user can retry
            }
        }
    }" wire:key="media-gallery-{{ str_replace('.', '-', $statePath) }}"
        class="eclipse-media-gallery" style="display: flex; flex-direction: column; gap: 1rem;">
        <div x-show="state && state.length > 0" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            @if ($getAllowBulkDelete())
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <x-filament::input.checkbox :checked="false" x-bind:checked="allSelected"
                        x-on:change="toggleSelectAll()" class="flex-shrink-0" />
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none">
                        <span x-show="!hasSelection">Select All</span>
                        <span x-show="allSelected">Deselect All</span>
                        <span x-show="someSelected && !allSelected" x-text="`${selectedCount} selected`"></span>
                    </span>
                </label>

                <div x-show="hasSelection" x-transition class="flex items-center gap-2 ml-2">
                    <x-filament::button size="xs" color="danger" icon="heroicon-o-trash" x-on:click="bulkDelete()">
                        Delete
                    </x-filament::button>
                </div>
            </div>
            @else
            <div></div>
            @endif

            <div class="flex items-center gap-2 flex-shrink-0">
                @if ($getAllowFileUploads() && $getAction('upload'))
                    <x-filament::button size="xs" color="primary" icon="heroicon-o-arrow-up-tray"
                        x-on:click="$wire.mountFormComponentAction('{{ $statePath }}', 'upload')">
                        Upload Files
                    </x-filament::button>
                @endif
                @if ($getAllowUrlUploads() && $getAction('urlUpload'))
                    <x-filament::button size="xs" color="gray" icon="heroicon-o-link"
                        x-on:click="$wire.mountFormComponentAction('{{ $statePath }}', 'urlUpload')">
                        Add from URL
                    </x-filament::button>
                @endif
            </div>
        </div>

        <div x-cloak wire:ignore>
            <div x-show="state && state.length > 0" style="display: flex; flex-direction: column; gap: 1rem;">

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
                        'fi-section relative group overflow-hidden transition-all duration-300',
                        'rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10',
                        'cursor-move' => $isDraggable,
                        'hover:shadow-md' => !$isDraggable,
                    ])
                        :class="{
                            'ring-2 ring-primary-600 dark:ring-primary-400 shadow-lg bg-primary-50 dark:bg-primary-900/20': selectedImages
                                .includes(image.uuid),
                            'opacity-50': draggedIndex === index,
                            'ring-2 ring-primary-400 bg-primary-50 dark:bg-primary-900/20': draggedIndex !== null &&
                                draggedIndex !== index
                        }"
                        @if ($isDraggable) draggable="true"
                        @dragstart="draggedIndex = index"
                        @dragover.prevent
                        @drop.prevent="
                            if (draggedIndex !== index) {
                                const item = state.splice(draggedIndex, 1)[0];
                                state.splice(index, 0, item);
                                $wire.mountFormComponentAction('{{ $statePath }}', 'reorder', { items: state.map(img => img.uuid) });
                            }
                            draggedIndex = null;
                        "
                        @dragend="draggedIndex = null" @endif>

                        <div class="eclipse-image-card-container"
                            style="position: relative; height: {{ $thumbnailHeight }}px;">
                            <img :src="image.thumb_url || image.url" :alt="image.file_name"
                                class="eclipse-image-card-img select-none pointer-events-none" draggable="false"
                                @if ($hasLightboxPreview) @click="openImageModal(index)"
                                    style="cursor: pointer; pointer-events: auto;" @endif />


                            <div class="absolute z-20" style="top: 8px; left: 8px;">
                                <template x-if="!image.is_cover">
                                    <x-filament::button size="xs" color="primary"
                                        x-on:click.stop="$wire.mountFormComponentAction('{{ $statePath }}', 'setCover', { arguments: { uuid: image.uuid } })"
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
                        <div class="p-3">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate"
                                x-text="getLocalizedName(image)"></p>
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-1"
                                x-text="getLocalizedDescription(image)" x-show="getLocalizedDescription(image)"></p>
                            <div class="flex flex-wrap items-center justify-between mt-2">
                                <div class="flex items-center gap-2">
                                    <x-filament::button size="xs" color="gray" icon="heroicon-m-pencil-square"
                                        x-on:click="$wire.mountFormComponentAction('{{ $statePath }}', 'editImage', { arguments: { uuid: image.uuid, selectedLocale: getLocale() } })">
                                        Edit
                                    </x-filament::button>

                                    <x-filament::button size="xs" color="danger" icon="heroicon-m-trash"
                                        x-on:click="$wire.mountFormComponentAction('{{ $statePath }}', 'deleteImage', { arguments: { uuid: image.uuid } })">
                                        Delete
                                    </x-filament::button>
                                </div>

                                @if ($getAllowBulkDelete())
                                <x-filament::input.checkbox :checked="false"
                                    x-bind:checked="selectedImages.includes(image.uuid)"
                                    x-on:change="toggleImageSelection(image.uuid)" class="flex-shrink-0" />
                                @endif
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="!state || state.length === 0"
            class="text-center rounded-xl bg-gray-50/30 dark:bg-gray-900/30 min-h-[280px] flex flex-col items-center justify-center gap-6 py-12 px-6 border-2 border-dashed border-gray-300 dark:border-gray-600"
            style="border-style: dashed !important;">
            <div class="flex flex-col items-center gap-4">
                <div class="p-4 rounded-full bg-gray-100 dark:bg-gray-800">
                    <x-filament::icon icon="heroicon-o-photo" class="h-12 w-12 text-gray-400 dark:text-gray-500" />
                </div>
                <div class="space-y-2">
                    <p class="text-base font-semibold text-gray-700 dark:text-gray-200">No images uploaded yet</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                        @if ($getAllowFileUploads() || $getAllowUrlUploads())
                            Click "Upload Files" or "Add from URL" to add your first image
                        @else
                            No images are currently available for this gallery
                        @endif
                    </p>
                </div>

                @if ($getAllowFileUploads() || $getAllowUrlUploads())
                    <div class="flex items-center gap-3 mt-2">
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
