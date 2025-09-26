# MediaGallery Form Field

A powerful image gallery form field for Filament with drag & drop reordering, multiple upload options, and preview features.

## Basic Usage

```php
use Eclipse\Common\Filament\Forms\Components\MediaGallery;

MediaGallery::make('images')
    ->collection('gallery')
    ->required()
```

## Collection Configuration

```php
MediaGallery::make('images')
    ->collection('product-gallery') // Media collection name
    ->collection(fn () => 'dynamic-' . $this->category) // Dynamic collection
```

## Upload Options

```php
MediaGallery::make('images')
    ->maxFiles(10) // Maximum number of files
    ->maxFileSize(2048) // Max size in KB
    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
    ->allowUploads() // Enable both upload methods
    ->single() // Single file mode
    ->multiple() // Multiple files (default)
```

## Preview & Layout

```php
MediaGallery::make('images')
    ->columns(6) // Number of columns (default: 4)
    ->thumbnailHeight(200) // Thumbnail height in pixels (default: 150)
    ->preview() // Enable lightbox preview (disabled by default)
    ->orderable() // Enable drag & drop reordering (disabled by default)
```

### Responsive Columns

```php
MediaGallery::make('images')
    ->columns([
        'default' => 2,
        'sm' => 3,
        'lg' => 4,
        'xl' => 6
    ])
```

## Methods Available

### Layout Control
- `columns(int|array)` - Set number of grid columns or responsive column configuration (default: 4)
- `thumbnailHeight(int)` - Set thumbnail image height in pixels (default: 150)

### Interactive Features
- `preview()` - Enable lightbox modal for image preview (disabled by default)
- `lightbox(bool)` - Explicitly enable/disable lightbox functionality
- `orderable()` - Enable drag & drop reordering (disabled by default)

### Upload Configuration
- `collection(string)` - Set media collection name
- `maxFiles(int)` - Maximum number of uploadable files
- `maxFileSize(int)` - Maximum file size in KB
- `acceptedFileTypes(array)` - Allowed file MIME types
- `allowFileUploads()` - Enable file upload button (disabled by default)
- `allowUrlUploads()` - Enable URL upload button (disabled by default)
- `allowUploads()` - Enable both file and URL upload buttons
- `single()` - Single file mode
- `multiple()` - Multiple file mode (default)

## Actions Available

The MediaGallery provides these built-in actions:

- **Upload**: File upload with drag & drop interface
- **URL Upload**: Add images from external URLs
- **Edit**: Edit image details and metadata
- **Delete**: Remove images with confirmation
- **Set Cover**: Mark image as cover/featured
- **Reorder**: Drag & drop reordering (when `orderable()` is enabled)

## Examples

### Basic Gallery (View Only)
```php
MediaGallery::make('images')
    ->collection('products')
```

### Gallery with Uploads
```php
MediaGallery::make('images')
    ->collection('gallery')
    ->allowUploads() // Enable both upload methods
    ->maxFiles(20)
```

### Gallery with Preview
```php
MediaGallery::make('images')
    ->collection('gallery')
    ->allowUploads()
    ->preview() // Enables lightbox
    ->columns(3)
    ->thumbnailHeight(180)
```

### Orderable Gallery
```php
MediaGallery::make('images')
    ->collection('portfolio')
    ->allowUploads()
    ->orderable() // Enables drag & drop
    ->columns(5)
    ->maxFiles(50)
```

### File Upload Only
```php
MediaGallery::make('images')
    ->collection('secure-documents')
    ->allowFileUploads() // Only file uploads
    ->maxFiles(10)
```

### URL Upload Only
```php
MediaGallery::make('images')
    ->collection('external-media')
    ->allowUrlUploads() // Only URL uploads
    ->columns(6)
```

### Complete Configuration
```php
MediaGallery::make('images')
    ->collection('products')
    ->allowUploads() // Enable both upload methods
    ->maxFiles(20)
    ->columns(4)
    ->thumbnailHeight(180)
    ->preview() // Enable lightbox
    ->orderable() // Enable reordering
    ->acceptedFileTypes(['image/jpeg', 'image/png'])
```

### Dynamic Configuration
```php
MediaGallery::make('images')
    ->collection(fn () => $this->record?->category . '-images')
    ->maxFiles(fn () => $this->record?->isPremium() ? 50 : 10)
    ->columns(fn () => $this->getColumnCount())
```

## Default Behaviors

- **Upload Buttons**: Disabled by default (use `->allowUploads()`, `->allowFileUploads()`, or `->allowUrlUploads()`)
- **Lightbox**: Disabled by default (use `->preview()` to enable)
- **Drag & Drop**: Disabled by default (use `->orderable()` to enable)
- **Grid Columns**: 4 columns by default
- **File Types**: Images only (jpeg, png, gif, webp)

## Requirements

- Spatie Media Library package
- Model must implement `HasMedia` interface
- Images are stored in configured media collections