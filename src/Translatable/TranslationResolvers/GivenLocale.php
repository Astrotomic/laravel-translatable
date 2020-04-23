<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Translatable;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class GivenLocale extends BaseTranslationResolver
{
    public function resolve(
        Translatable $translatable,
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
        Translatable $translatable,
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
