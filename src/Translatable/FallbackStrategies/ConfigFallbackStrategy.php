<?php

namespace Astrotomic\Translatable\FallbackStrategies;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ConfigFallbackStrategy extends BaseFallbackStrategy
{
    public function fallback(
        Translatable $translatable,
        string $locale,
        Collection $alreadyCheckedLocales
    ): ?Model
    {
        $locale = config('translatable.fallback_locale');

        if ($alreadyCheckedLocales->contains($locale)) {
            return null;
        }

        $alreadyCheckedLocales->push($locale);

        return $translatable->translations->firstWhere($translatable->getLocaleKey(), $locale);
    }
}