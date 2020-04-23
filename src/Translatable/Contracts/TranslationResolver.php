<?php

namespace Astrotomic\Translatable\Contracts;

use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TranslationResolver
{
    public function resolve(
        Translatable $translatable,
        string $locale,
        bool $withFallback,
        Collection $alreadyCheckedLocales
    ): ?Model;

    public function resolveWithAttribute(
        Translatable $translatable,
        string $locale,
        bool $withFallback,
        Collection $alreadyCheckedLocales,
        string $attribute
    ): ?Model;
}
