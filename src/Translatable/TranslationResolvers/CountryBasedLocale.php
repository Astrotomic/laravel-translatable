<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Locales;

class CountryBasedLocale extends BaseFallbackResolver
{
    /** @var Locales */
    protected $locales;

    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    protected function fallbackLocales(string $locale): array
    {
        if ($this->locales->isLocaleCountryBased($locale)) {
            return [$this->locales->getLanguageFromCountryBasedLocale($locale)];
        }

        return [];
    }
}
