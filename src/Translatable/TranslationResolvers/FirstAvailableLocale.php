<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Locales;

class FirstAvailableLocale extends BaseFallbackResolver
{
    /** @var Locales */
    protected $locales;

    public function __construct(Locales $locales)
    {
        $this->locales = $locales;
    }

    protected function fallbackLocales(string $locale): array
    {
        return $this->locales->all();
    }
}
