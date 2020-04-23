<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Contracts\TranslationResolver;
use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseTranslationResolver implements TranslationResolver
{
    protected function resolveTranslationByLocale(
        TranslatableContract $translatable,
        string $locale,
        Collection $alreadyCheckedLocales
    ): ?Model {
        if ($alreadyCheckedLocales->contains($locale)) {
            return null;
        }

        $alreadyCheckedLocales->push($locale);

        return $translatable->translations->firstWhere($translatable->getLocaleKey(), $locale);
    }

    protected function resolveTranslationWithAttributeByLocale(
        TranslatableContract $translatable,
        string $locale,
        Collection $alreadyCheckedLocales,
        string $attribute
    ): ?Model {
        $translation = $this->resolveTranslationByLocale($translatable, $locale, $alreadyCheckedLocales);

        if ($translation === null) {
            return null;
        }

        if ($translatable->isEmptyTranslatableAttribute($attribute, $translation->getAttribute($attribute))) {
            return null;
        }

        return $translation;
    }
}
