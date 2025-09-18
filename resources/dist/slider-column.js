window.imagePreviewLightbox = function() {
    return {
        isOpen: false,
        currentIndex: 0,
        images: [],
        currentImage: {
            url: '',
            title: '',
            link: '',
            filename: ''
        },
        
        getDisplayName() {
            return this.currentImage.title || this.currentImage.filename || '';
        },
        
        init() {
            const self = this;
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('image-preview-trigger')) {
                    e.preventDefault();
                    e.stopPropagation();
                    self.openFromTable(e.target);
                }
            }, true);
            document.addEventListener('keydown', (e) => {
                if (this.isOpen) {
                    if (e.key === 'ArrowLeft') {
                        e.preventDefault();
                        this.previous();
                    } else if (e.key === 'ArrowRight') {
                        e.preventDefault();
                        this.next();
                    }
                }
            });
        },
        
        openFromTable(imageElement) {
            const allRows = document.querySelectorAll('tbody tr');
            this.images = [];
            let clickedIndex = 0;

            const imageGrid = [];
            let maxColumns = 0;

            allRows.forEach((row) => {
                const rowImages = row.querySelectorAll('.fi-ta-image .image-preview-trigger');
                const rowImageArray = Array.from(rowImages);
                imageGrid.push(rowImageArray);
                maxColumns = Math.max(maxColumns, rowImageArray.length);
            });
            
            let clickedRowIndex = -1;
            let clickedColIndex = -1;
            
            imageGrid.forEach((rowImages, rowIndex) => {
                rowImages.forEach((img, colIndex) => {
                    if (img === imageElement) {
                        clickedRowIndex = rowIndex;
                        clickedColIndex = colIndex;
                    }
                });
            });
            
            imageGrid.forEach((rowImages, rowIndex) => {
                rowImages.forEach((img, colIndex) => {
                    this.addImageToCollection(img, imageElement, () => {
                        if (rowIndex === clickedRowIndex && colIndex === clickedColIndex) {
                            clickedIndex = this.images.length - 1;
                        }
                    });
                });
            });
            
            if (this.images.length > 0) {
                this.currentIndex = clickedIndex;
                this.updateCurrentImage();
                this.open();
            }
        },
        
        addImageToCollection(img, imageElement, onMatch) {
            const configData = img.dataset.lightboxConfig;
            
            if (configData) {
                try {
                    const lightboxData = JSON.parse(configData);
                    const matchingImageData = lightboxData.find(data => 
                        img.src.includes(data.url) || data.url.includes(img.src) || data.url === img.src
                    );
                    
                    if (matchingImageData) {
                        this.images.push({
                            url: img.src,
                            title: matchingImageData.title || '',
                            link: matchingImageData.link || '',
                            filename: img.alt || ''
                        });
                        
                        if (img === imageElement) {
                            onMatch();
                        }
                        return;
                    }
                } catch (e) {
                    console.error('Error parsing lightbox config:', e);
                }
            }

            const imageData = {
                url: img.src,
                title: '',
                link: '',
                filename: img.alt || ''
            };
            
            this.images.push(imageData);
            
            if (img === imageElement) {
                onMatch();
            }
        },
        
        open() {
            this.isOpen = true;
            document.body.style.overflow = 'hidden';
        },
        
        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },
        
        next() {
            if (this.images.length > 0) {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
                this.updateCurrentImage();
            }
        },
        
        previous() {
            if (this.images.length > 0) {
                this.currentIndex = (this.currentIndex - 1 + this.images.length) % this.images.length;
                this.updateCurrentImage();
            }
        },
        
        updateCurrentImage() {
            if (this.images[this.currentIndex]) {
                this.currentImage = this.images[this.currentIndex];
            }
        }
    };
};