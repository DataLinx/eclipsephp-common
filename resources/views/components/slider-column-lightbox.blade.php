<div x-data="imagePreviewLightbox()" x-init="init()">
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="close()"
         @keydown.escape.window="close()"
         class="image-preview-lightbox-overlay"
         x-cloak>
        
        <div class="image-preview-lightbox-container" @click.stop>
            <button type="button" @click.stop="close()" class="image-preview-lightbox-close">
                <x-filament::icon icon="heroicon-o-x-mark" />
            </button>
            
            <div class="image-preview-lightbox-image-wrapper">
                <img :src="currentImage.url" 
                     :alt="getDisplayName()"
                     class="image-preview-lightbox-image">
                <div class="image-preview-lightbox-info" x-show="currentImage.title || currentImage.link">
                    <p class="image-preview-lightbox-title" x-text="currentImage.title" x-show="currentImage.title"></p>
                    <p class="image-preview-lightbox-link" x-show="currentImage.link">
                        <a :href="currentImage.link" target="_blank" class="text-blue-400 hover:text-blue-300">
                            <x-filament::icon icon="heroicon-o-arrow-top-right-on-square" class="w-5 h-5" />
                        </a>
                    </p>
                </div>
            </div>
            <template x-if="images && images.length > 1">
                <div>
                    <button type="button" @click.stop.prevent="previous()" class="image-preview-lightbox-nav prev">
                        <x-filament::icon icon="heroicon-o-chevron-left" />
                    </button>
                    
                    <button type="button" @click.stop.prevent="next()" class="image-preview-lightbox-nav next">
                        <x-filament::icon icon="heroicon-o-chevron-right" />
                    </button>
                </div>
            </template>
        </div>
    </div>
</div>