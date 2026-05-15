<?php

namespace App\Traits;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

trait HasArabicSlug
{
    use HasSlug;

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(255);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Override Spatie's default slug generation to support Arabic characters.
     * Keeps Arabic/Latin letters and numbers, replaces spaces with hyphens.
     */
    protected function generateNonUniqueSlug(): string
    {
        $slugField = $this->slugOptions->slugField;

        if ($this->hasCustomSlugBeenUsed() && ! empty($this->$slugField)) {
            return $this->$slugField;
        }

        $sourceString = $this->getSlugSourceString();

        return $this->createArabicFriendlySlug($sourceString);
    }

    protected function createArabicFriendlySlug(string $value): string
    {
        // Trim and replace whitespace with hyphens
        $slug = preg_replace('/\s+/u', '-', trim($value));

        // Keep Arabic letters, Latin letters, numbers, and hyphens only
        $slug = preg_replace('/[^\p{L}\p{N}\-]/u', '', $slug);

        // Collapse multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Trim hyphens from edges
        $slug = trim($slug, '-');

        // Lowercase (works with multibyte)
        $slug = mb_strtolower($slug);

        // Respect max length
        if (function_exists('mb_substr')) {
            $slug = mb_substr($slug, 0, $this->slugOptions->maximumLength);
        } else {
            $slug = substr($slug, 0, $this->slugOptions->maximumLength);
        }

        return $slug;
    }
}
