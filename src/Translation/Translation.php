<?php

namespace Astrotomic\Translation;

use Illuminate\Support\Str;

/**
 * @property-read string $translatableModel
 * @property-read string $translatableForeignKey
 *
 * @mixin Model
 */
trait Translation
{
    public function getTranslatableModelName(): string
    {
        return $this->translatableModel ?: $this->getTranslatableModelNameDefault();
    }

    public function getTranslatableModelNameDefault(): string
    {
        $modelName = get_class($this);

        if ($namespace = $this->getTranslatableModelNamespace()) {
            $modelName = $namespace.'\\'.class_basename(get_class($this));
        }

        return Str::replaceLast(config('translatable.translation_suffix', 'Translation'), '', $modelName);
    }

    public function getTranslatableModelNamespace(): ?string
    {
        return config('translatable.translatable_model_namespace');
    }

    public function getTranslatableRelationKey(): string
    {
        if ($this->translatableForeignKey) {
            return $this->translatableForeignKey;
        }

        return Str::replaceFirst(Str::lower(config('translatable.translation_suffix', 'Translation')) . '_', '', Str::snake(class_basename($this)).'_'.$this->getKeyName());
    }

    public function translatable()
    {
        return $this->belongsTo($this->getTranslatableModelName(), $this->getTranslatableRelationKey());
    }
}
