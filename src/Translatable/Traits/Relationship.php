<?php

namespace Astrotomic\Translatable\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

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
        $namespace = $this->getTranslationModelNamespace();

        if (!empty($namespace)) {
            $modelName = $namespace.'\\'.class_basename($modelName);
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

    public function translations(): HasMany
    {
        return $this->hasMany($this->getTranslationModelName(), $this->getTranslationRelationKey());
    }
}
