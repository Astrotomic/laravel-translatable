<?php

namespace Astrotomic\Translatable\Contracts;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface FallbackStrategy
{
    public function fallback(
        Translatable $translatable,
        string $locale,
        Collection $alreadyCheckedLocales
    ): ?Model;

    public function fallbackWithAttribute(
        Translatable $translatable,
        string $locale,
        Collection $alreadyCheckedLocales,
        string $attribute
    ): ?Model;
}
