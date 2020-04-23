<?php

namespace Astrotomic\Translatable\FallbackStrategies;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FirstAvailableStrategy extends BaseFallbackStrategy
{
    public function fallback(
        Translatable $translatable,
        string $locale,
        Collection $alreadyCheckedLocales
    ): ?Model {
        foreach ($this->getLocalesHelper()->all() as $locale) {
            $translation = $this->resolveTranslationByLocale(
                $translatable,
                $locale,
                $alreadyCheckedLocales
            );

            if ($translation instanceof Model) {
                return $translation;
            }
        }

        return null;
    }
}
