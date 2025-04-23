<?php

namespace Eclipse\Common\Foundation\Models;

use Eclipse\Core\Models\Locale;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

/**
 * Trait with helper methods that is used on models which use the Scout Searchable trait
 */
trait IsSearchable
{
    use Searchable;

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray()
    {
        return $this->createSearchableArray();
    }

    protected function createSearchableArray(): array
    {
        $data = array_merge($this->toArray(), [
            // ID can safely always be cast to string
            'id' => (string) $this->id,
        ]);

        // Add timestamp casts
        // "Created at" and "Updated at" are automatically cast by Laravel
        if (isset($data['created_at'])) {
            $data['created_at'] = $this->created_at->timestamp;
        }

        if (isset($data['updated_at'])) {
            $data['updated_at'] = $this->updated_at->timestamp;
        }

        foreach ($this->getCasts() as $attr => $cast) {
            if ($cast === 'datetime' && $this->$attr) {
                $data[$attr] = $this->$attr->timestamp;
            }
        }

        // Remove translatable attributes from the array
        $data = Arr::except($data, $this->getTranslatableAttributes());

        // Add translatable attributes with language suffix and remove HTML tags
        foreach ($this->getTranslatableAttributes() as $attribute) {
            foreach (Locale::getAvailableLocales()->pluck('id') as $locale) {
                $data[$attribute.'_'.$locale] = str($this->getTranslationWithoutFallback($attribute, $locale))->stripTags();
            }
        }

        // Remove empty values and return
        return array_filter($data);
    }
}
