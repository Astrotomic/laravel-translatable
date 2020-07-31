<?php

namespace Astrotomic\Translatable\Contracts;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface TranslationResolver
{
    public function resolve(
        TranslatableContract $translatable,
        string $locale,
        bool $withFallback,
        Collection $alreadyCheckedLocales
    ): ?TranslatableContract;

    public function resolveWithAttribute(
        TranslatableContract $translatable,
        string $locale,
        bool $withFallback,
        Collection $alreadyCheckedLocales,
        string $attribute
    ): ?TranslatableContract;
}
