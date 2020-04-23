<?php

namespace Astrotomic\Translatable\FallbackStrategies;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CountryLocaleStrategy extends BaseFallbackStrategy
{
    public function fallback(
        Translatable $translatable,
        string $locale,
        Collection $alreadyCheckedLocales
    ): ?Model {
        if (! $translatable->getLocalesHelper()->isLocaleCountryBased($locale)) {
            return null;
        }

        return $this->resolveTranslationByLocale(
            $translatable,
            $this->getLocalesHelper()->getLanguageFromCountryBasedLocale($locale),
            $alreadyCheckedLocales
        );
    }
}
