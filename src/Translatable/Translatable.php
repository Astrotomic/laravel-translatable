<?php

namespace Astrotomic\Translatable;

use Astrotomic\Translatable\Traits\Relationship;
use Astrotomic\Translatable\Traits\Scopes;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

/**
 * @property-read null|Model $translation
 * @property-read Collection|Model[] $translations
 * @property-read string $translationModel
 * @property-read string $translationForeignKey
 * @property-read string $localeKey
 * @property-read bool $useTranslationFallback
 *
 * @mixin Model
 */
trait Translatable
{
    use Scopes, Relationship;

    protected static $autoloadTranslations = null;

    protected static $deleteTranslationsCascade = false;

    protected $defaultLocale;

    public static function bootTranslatable(): void
    {
        static::saved(function (Model $model) {
            /* @var Translatable $model */
            return $model->saveTranslations();
        });

        static::deleting(function (Model $model) {
            /* @var Translatable $model */
            if (self::$deleteTranslationsCascade === true) {
                return $model->deleteTranslations();
            }
        });
    }

    public static function defaultAutoloadTranslations(): void
    {
        self::$autoloadTranslations = null;
    }

    public static function disableAutoloadTranslations(): void
    {
        self::$autoloadTranslations = false;
    }

    public static function enableAutoloadTranslations(): void
    {
        self::$autoloadTranslations = true;
    }

    public static function disableDeleteTranslationsCascade(): void
    {
        self::$deleteTranslationsCascade = false;
    }

    public static function enableDeleteTranslationsCascade(): void
    {
        self::$deleteTranslationsCascade = true;
    }

    public function attributesToArray()
    {
        $attributes = parent::attributesToArray();

        if (
            (! $this->relationLoaded('translations') && ! $this->toArrayAlwaysLoadsTranslations() && is_null(self::$autoloadTranslations))
            || self::$autoloadTranslations === false
        ) {
            return $attributes;
        }

        $hiddenAttributes = $this->getHidden();

        foreach ($this->translatedAttributes as $field) {
            if (in_array($field, $hiddenAttributes)) {
                continue;
            }

            $attributes[$field] = $this->getAttributeOrFallback(null, $field);
        }

        return $attributes;
    }

    /**
     * @param string|array|null $locales The locales to be deleted
     */
    public function deleteTranslations($locales = null): void
    {
        if ($locales === null) {
            $translations = $this->translations()->get();
        } else {
            $locales = (array) $locales;
            $translations = $this->translations()->whereIn($this->getLocaleKey(), $locales)->get();
        }

        $translations->each->delete();

        // we need to manually "reload" the collection built from the relationship
        // otherwise $this->translations()->get() would NOT be the same as $this->translations
        $this->load('translations');
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $values) {
            if (
                $this->getLocalesHelper()->has($key)
                && is_array($values)
            ) {
                $this->getTranslationOrNew($key)->fill($values);
                unset($attributes[$key]);
            } else {
                [$attribute, $locale] = $this->getAttributeAndLocale($key);

                if (
                    $this->getLocalesHelper()->has($locale)
                    && $this->isTranslationAttribute($attribute)
                ) {
                    $this->getTranslationOrNew($locale)->fill([$attribute => $values]);
                    unset($attributes[$key]);
                }
            }
        }

        return parent::fill($attributes);
    }

    public function getAttribute($key)
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if ($this->isTranslationAttribute($attribute)) {
            if ($this->getTranslation($locale) === null) {
                return $this->getAttributeValue($attribute);
            }

            // If the given $attribute has a mutator, we push it to $attributes and then call getAttributeValue
            // on it. This way, we can use Eloquent's checking for Mutation, type casting, and
            // Date fields.
            if ($this->hasGetMutator($attribute)) {
                $this->attributes[$attribute] = $this->getAttributeOrFallback($locale, $attribute);

                return $this->getAttributeValue($attribute);
            }

            return $this->getAttributeOrFallback($locale, $attribute);
        }

        return parent::getAttribute($key);
    }

    public function getDefaultLocale(): ?string
    {
        return $this->defaultLocale;
    }

    /**
     * @internal will change to protected
     */
    public function getLocaleKey(): string
    {
        return $this->localeKey ?: config('translatable.locale_key', 'locale');
    }

    public function getNewTranslation(string $locale): Model
    {
        $modelName = $this->getTranslationModelName();

        /** @var Model $translation */
        $translation = new $modelName();
        $translation->setAttribute($this->getLocaleKey(), $locale);
        $this->translations->add($translation);

        return $translation;
    }

    public function getTranslation(?string $locale = null, bool $withFallback = null): ?Model
    {
        $configFallbackLocale = $this->getFallbackLocale();
        $locale = $locale ?: $this->locale();
        $withFallback = $withFallback === null ? $this->useFallback() : $withFallback;
        $fallbackLocale = $this->getFallbackLocale($locale);

        if ($translation = $this->getTranslationByLocaleKey($locale)) {
            return $translation;
        }

        if ($withFallback && $fallbackLocale) {
            if ($translation = $this->getTranslationByLocaleKey($fallbackLocale)) {
                return $translation;
            }

            if (
                is_string($configFallbackLocale)
                && $fallbackLocale !== $configFallbackLocale
                && $translation = $this->getTranslationByLocaleKey($configFallbackLocale)
            ) {
                return $translation;
            }
        }

        if ($withFallback && $configFallbackLocale === null) {
            $configuredLocales = $this->getLocalesHelper()->all();

            foreach ($configuredLocales as $configuredLocale) {
                if (
                    $locale !== $configuredLocale
                    && $fallbackLocale !== $configuredLocale
                    && $translation = $this->getTranslationByLocaleKey($configuredLocale)
                ) {
                    return $translation;
                }
            }
        }

        return null;
    }

    public function getTranslationOrNew(?string $locale = null): Model
    {
        $locale = $locale ?: $this->locale();

        if (($translation = $this->getTranslation($locale, false)) === null) {
            $translation = $this->getNewTranslation($locale);
        }

        return $translation;
    }

    public function getTranslationOrFail(string $locale): Model
    {
        if (($translation = $this->getTranslation($locale, false)) === null) {
            throw (new ModelNotFoundException)->setModel($this->getTranslationModelName(), $locale);
        }

        return $translation;
    }

    public function getTranslationsArray(): array
    {
        $translations = [];

        foreach ($this->translations as $translation) {
            foreach ($this->translatedAttributes as $attr) {
                $translations[$translation->{$this->getLocaleKey()}][$attr] = $translation->{$attr};
            }
        }

        return $translations;
    }

    public function hasTranslation(?string $locale = null): bool
    {
        $locale = $locale ?: $this->locale();

        foreach ($this->translations as $translation) {
            if ($translation->getAttribute($this->getLocaleKey()) == $locale) {
                return true;
            }
        }

        return false;
    }

    public function isTranslationAttribute(string $key): bool
    {
        return in_array($key, $this->translatedAttributes);
    }

    public function replicateWithTranslations(array $except = null): Model
    {
        $newInstance = $this->replicate($except);

        unset($newInstance->translations);
        foreach ($this->translations as $translation) {
            $newTranslation = $translation->replicate();
            $newInstance->translations->add($newTranslation);
        }

        return $newInstance;
    }

    public function setAttribute($key, $value)
    {
        [$attribute, $locale] = $this->getAttributeAndLocale($key);

        if ($this->isTranslationAttribute($attribute)) {
            $this->getTranslationOrNew($locale)->$attribute = $value;

            return $this;
        }

        return parent::setAttribute($key, $value);
    }

    public function setDefaultLocale(?string $locale)
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    public function translate(?string $locale = null, bool $withFallback = false): ?Model
    {
        return $this->getTranslation($locale, $withFallback);
    }

    public function translateOrDefault(?string $locale = null): ?Model
    {
        return $this->getTranslation($locale, true);
    }

    public function translateOrNew(?string $locale = null): Model
    {
        return $this->getTranslationOrNew($locale);
    }

    public function translateOrFail(string $locale): Model
    {
        return $this->getTranslationOrFail($locale);
    }

    protected function getLocalesHelper(): Locales
    {
        return app(Locales::class);
    }

    protected function isEmptyTranslatableAttribute(string $key, $value): bool
    {
        return empty($value);
    }

    protected function isTranslationDirty(Model $translation): bool
    {
        $dirtyAttributes = $translation->getDirty();
        unset($dirtyAttributes[$this->getLocaleKey()]);

        return count($dirtyAttributes) > 0;
    }

    protected function locale(): string
    {
        if ($this->getDefaultLocale()) {
            return $this->getDefaultLocale();
        }

        return $this->getLocalesHelper()->current();
    }

    protected function saveTranslations(): bool
    {
        $saved = true;

        if (! $this->relationLoaded('translations')) {
            return $saved;
        }

        foreach ($this->translations as $translation) {
            if ($saved && $this->isTranslationDirty($translation)) {
                if (! empty($connectionName = $this->getConnectionName())) {
                    $translation->setConnection($connectionName);
                }

                $translation->setAttribute($this->getTranslationRelationKey(), $this->getKey());
                $saved = $translation->save();
            }
        }

        return $saved;
    }

    protected function getAttributeAndLocale(string $key): array
    {
        if (Str::contains($key, ':')) {
            return explode(':', $key);
        }

        return [$key, $this->locale()];
    }

    protected function getAttributeOrFallback(?string $locale, string $attribute)
    {
        $translation = $this->getTranslation($locale);

        if (
            (
                ! $translation instanceof Model
                || $this->isEmptyTranslatableAttribute($attribute, $translation->$attribute)
            )
            && $this->usePropertyFallback()
        ) {
            $translation = $this->getTranslation($this->getFallbackLocale(), false);
        }

        if ($translation instanceof Model) {
            return $translation->$attribute;
        }

        return null;
    }

    protected function getFallbackLocale(?string $locale = null): ?string
    {
        if ($locale && $this->getLocalesHelper()->isLocaleCountryBased($locale)) {
            if ($fallback = $this->getLocalesHelper()->getLanguageFromCountryBasedLocale($locale)) {
                return $fallback;
            }
        }

        return config('translatable.fallback_locale');
    }

    protected function getTranslationByLocaleKey(string $key): ?Model
    {
        if (
            $this->relationLoaded('translation')
            && $this->translation
            && $this->translation->getAttribute($this->getLocaleKey()) == $key
        ) {
            return $this->translation;
        }

        return $this->translations->firstWhere($this->getLocaleKey(), $key);
    }

    protected function toArrayAlwaysLoadsTranslations(): bool
    {
        return config('translatable.to_array_always_loads_translations', true);
    }

    protected function useFallback(): bool
    {
        if (isset($this->useTranslationFallback) && is_bool($this->useTranslationFallback)) {
            return $this->useTranslationFallback;
        }

        return (bool) config('translatable.use_fallback');
    }

    protected function usePropertyFallback(): bool
    {
        return $this->useFallback() && config('translatable.use_property_fallback', false);
    }

    public function __isset($key)
    {
        return $this->isTranslationAttribute($key) || parent::__isset($key);
    }
}
