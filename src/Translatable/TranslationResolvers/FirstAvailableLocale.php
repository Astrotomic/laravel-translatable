<?php

namespace Astrotomic\Translatable\TranslationResolvers;

use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

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
