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
    ): ?Model {
        return $this->resolveTranslationByLocale(
            $translatable,
            config('translatable.fallback_locale'),
            $alreadyCheckedLocales
        );
    }
}
