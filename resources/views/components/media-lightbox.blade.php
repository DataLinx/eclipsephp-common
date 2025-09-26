<div x-show="lightboxOpen"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="lightboxOpen = false"
     @keydown.escape.window="lightboxOpen = false"
     class="eclipse-image-lightbox-overlay"
     x-cloak>

    <div class="eclipse-image-lightbox-container" @click.stop>
        <button type="button" @click.stop="lightboxOpen = false" class="eclipse-image-lightbox-close">
            <x-filament::icon icon="heroicon-m-x-mark" class="h-6 w-6" />
        </button>

        <div class="eclipse-image-lightbox-image-wrapper">
            <img :src="lightboxImage"
                 :alt="lightboxAlt"
                 class="eclipse-image-lightbox-image">
            <div class="eclipse-image-lightbox-info" x-show="lightboxName || lightboxDescription">
                <p class="eclipse-image-lightbox-title" x-text="lightboxName" x-show="lightboxName"></p>
                <p class="eclipse-image-lightbox-description" x-text="lightboxDescription" x-show="lightboxDescription"></p>
            </div>
        </div>
        <template x-if="state && state.length > 1">
            <div>
                <button type="button" @click.stop.prevent="previousImage()" class="eclipse-image-lightbox-nav prev">
                    <x-filament::icon icon="heroicon-m-chevron-left" class="h-6 w-6" />
                </button>

                <button type="button" @click.stop.prevent="nextImage()" class="eclipse-image-lightbox-nav next">
                    <x-filament::icon icon="heroicon-m-chevron-right" class="h-6 w-6" />
                </button>
            </div>
        </template>
    </div>
</div>