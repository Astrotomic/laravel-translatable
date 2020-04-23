<?php

namespace Astrotomic\Translatable\FallbackStrategies;

use Astrotomic\Translatable\Contracts\FallbackStrategy;
use Astrotomic\Translatable\Locales;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseFallbackStrategy implements FallbackStrategy
{
    public function fallbackWithAttribute(
        Translatable $translatable,
        string $locale,
        Collection $alreadyCheckedLocales,
        string $attribute
    ): ?Model {
        $translation = $this->fallback($translatable, $locale, $alreadyCheckedLocales);

        if ($translation === null) {
            return null;
        }

        if ($attribute === null) {
            return $translation;
        }

        if ($translatable->isEmptyTranslatableAttribute($attribute, $translation->getAttribute($attribute))) {
            return null;
        }

        return $translation;
    }

    protected function getLocalesHelper(): Locales
    {
        return app(Locales::class);
    }
}
