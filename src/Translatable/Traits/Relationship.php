<?php

namespace Astrotomic\Translatable\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read string $translationModel
 * @property-read string $translationForeignKey
 */
trait Relationship
{
    /**
     * @deprecated
     */
    public function getRelationKey(): string
    {
        return $this->getTranslationRelationKey();
    }

    /**
     * @internal will change to protected
     */
    public function getTranslationModelName(): string
    {
        return $this->translationModel ?: $this->getTranslationModelNameDefault();
    }

    /**
     * @internal will change to private
     */
    public function getTranslationModelNameDefault(): string
    {
        $modelName = get_class($this);

        if ($namespace = $this->getTranslationModelNamespace()) {
            $modelName = $namespace.'\\'.class_basename(get_class($this));
        }

        return $modelName.config('translatable.translation_suffix', 'Translation');
    }

    /**
     * @internal will change to private
     */
    public function getTranslationModelNamespace(): ?string
    {
        return config('translatable.translation_model_namespace');
    }

    protected function eloquentRelationshipOnlyTranslatedAttributes(): bool
    {
        return config('translatable.eloquent_relationship_only_translated_attributes', false);
    }

    /**
     * @internal will change to protected
     */
    public function getTranslationRelationKey(): string
    {
        if ($this->translationForeignKey) {
            return $this->translationForeignKey;
        }

        return $this->getForeignKey();
    }

    public function translation(): HasOne
    {
        return $this
            ->hasOne($this->getTranslationModelName(), $this->getTranslationRelationKey())
            ->where($this->getLocaleKey(), $this->localeOrFallback());
    }

    protected function localeOrFallback()
    {
        return $this->useFallback() && ! $this->translations()->where($this->getLocaleKey(), $this->locale())->exists()
            ? $this->getFallbackLocale()
            : $this->locale();
    }

    public function translations(): HasMany
    {
        $translationRelationKey = $this->getTranslationRelationKey();
        $hasMany =  $this->hasMany($this->getTranslationModelName(), $translationRelationKey);
        if (false === $this->eloquentRelationshipOnlyTranslatedAttributes()) {
            return $hasMany;
        }
        $translatedAttributes = $this->translatedAttributes;
        if (!in_array($translationRelationKey, $translatedAttributes, true)) {
            $translatedAttributes[] = $translationRelationKey;
        }
        if (!in_array($this->getLocaleKey(), $translatedAttributes, true)) {
            $translatedAttributes[] = $this->getLocaleKey();
        }
        $hasMany->getQuery()->select($translatedAttributes);
        return $hasMany;
    }
}
