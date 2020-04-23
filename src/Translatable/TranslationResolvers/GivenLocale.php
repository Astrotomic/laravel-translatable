<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class GivenLocale extends BaseTranslationResolver
{
    public function resolve(
        TranslatableContract $translatable,
        string $locale,
        bool $withFallback,
        Collection $alreadyCheckedLocales
    ): ?Model {
        return $this->resolveTranslationByLocale(
            $translatable,
            $locale,
            $alreadyCheckedLocales
        );
    }

    public function resolveWithAttribute(
        TranslatableContract $translatable,
        string $locale,
        bool $withFallback,
        Collection $alreadyCheckedLocales,
        string $attribute
    ): ?Model {
        return $this->resolveTranslationWithAttributeByLocale(
            $translatable,
            $locale,
            $alreadyCheckedLocales,
            $attribute
        );
    }
}
