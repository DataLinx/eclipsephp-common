window.mediaGallery = function({ state, getLocale }) {
    return {
        state: state || [],
        getLocale: getLocale,

        init() {
            if (!Array.isArray(this.state)) {
                this.state = [];
            }

            this.lightboxOpen = false;
            this.lightboxIndex = 0;
            this.lightboxImage = '';
            this.lightboxAlt = '';
            this.lightboxName = '';
            this.lightboxDescription = '';

            document.addEventListener('keydown', (e) => {
                if (this.lightboxOpen) {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.previousImage();
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.nextImage();
                    }
                }
            });
        },

        getLocalizedName(image) {
            const currentLocale = this.getLocale();
            if (!image.name || typeof image.name !== 'object') {
                return image.file_name || '';
            }
            return image.name[currentLocale] || image.name['en'] || image.file_name || '';
        },

        getLocalizedDescription(image) {
            const currentLocale = this.getLocale();
            if (!image.description || typeof image.description !== 'object') {
                return '';
            }
            return image.description[currentLocale] || image.description['en'] || '';
        },

        openImageModal(index) {
            this.lightboxIndex = index;
            const image = this.state[index];
            this.lightboxImage = image.url;
            this.lightboxAlt = image.file_name;
            this.lightboxName = this.getLocalizedName(image);
            this.lightboxDescription = this.getLocalizedDescription(image);
            this.lightboxOpen = true;
        },

        previousImage() {
            this.lightboxIndex = (this.lightboxIndex - 1 + this.state.length) % this.state.length;
            this.updateLightboxImage();
        },

        nextImage() {
            this.lightboxIndex = (this.lightboxIndex + 1) % this.state.length;
            this.updateLightboxImage();
        },

        updateLightboxImage() {
            const image = this.state[this.lightboxIndex];
            this.lightboxImage = image.url;
            this.lightboxAlt = image.file_name;
            this.lightboxName = this.getLocalizedName(image);
            this.lightboxDescription = this.getLocalizedDescription(image);
        },

        handleSetCover(image) {
            if (image.is_cover) return;
            this.$wire.mountFormComponentAction('setCover', { arguments: { uuid: image.uuid } });
        }
    };
};