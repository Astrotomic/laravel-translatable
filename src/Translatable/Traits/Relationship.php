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
    protected function getTranslationModelName(): string
    {
        return $this->translationModel ?: $this->getTranslationModelNameDefault();
    }

    protected function getTranslationModelNameDefault(): string
    {
        $modelName = get_class($this);

        if ($namespace = $this->getTranslationModelNamespace()) {
            $modelName = $namespace.'\\'.class_basename(get_class($this));
        }

        return $modelName.config('translatable.translation_suffix', 'Translation');
    }

    protected function getTranslationModelNamespace(): ?string
    {
        return config('translatable.translation_model_namespace');
    }

    protected function getTranslationRelationKey(): string
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
            ->where('locale', $this->localeOrFallback());
    }

    private function localeOrFallback()
    {
        return $this->useFallback() && ! $this->translations()->where('locale', $this->locale())->exists()
            ? $this->getFallbackLocale()
            : $this->locale();
    }

    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelName(), $this->getTranslationRelationKey());
    }
}
