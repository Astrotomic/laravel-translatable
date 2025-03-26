<?php

namespace Astrotomic\Translatable\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface Translatable
{
    public static function defaultAutoloadTranslations(): void;

    public static function disableAutoloadTranslations(): void;

    public static function enableAutoloadTranslations(): void;

    public static function disableDeleteTranslationsCascade(): void;

    public static function enableDeleteTranslationsCascade(): void;

    /**
     * @param  string|array<string>|null  $locales
     */
    public function deleteTranslations(string|array|null $locales = null): void;

    public function getDefaultLocale(): ?string;

    public function getNewTranslation(string $locale): Model;

    public function getTranslation(?string $locale = null, ?bool $withFallback = null): ?Model;

    public function getTranslationOrNew(?string $locale = null): Model;

    /**
     * @return array<string,array<string,mixed>>
     */
    public function getTranslationsArray(): array;

    public function hasTranslation(?string $locale = null): bool;

    public function isTranslationAttribute(string $key): bool;

    /**
     * @param  null|array<string>  $except
     */
    public function replicateWithTranslations(?array $except = null): Model;

    public function setDefaultLocale(?string $locale);

    public function translate(?string $locale = null, bool $withFallback = false): ?Model;

    public function translateOrDefault(?string $locale = null): ?Model;

    public function translateOrNew(?string $locale = null): Model;

    /**
     * @return HasOne<Model>
     */
    public function translation(): HasOne;

    /**
     * @return HasMany<Model>
     */
    public function translations(): HasMany;
}
