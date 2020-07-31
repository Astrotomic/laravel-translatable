<?php

namespace Astrotomic\Translatable\Contracts;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read EloquentCollection|Model[] $translations
 * @property-read string $translationModel
 * @property-read string $translationForeignKey
 * @property-read string $localeKey
 * @property-read bool $useTranslationFallback
 *
 * @mixin Model
 */
interface Translatable
{
    public static function defaultAutoloadTranslations(): void;

    public static function disableAutoloadTranslations(): void;

    public static function enableAutoloadTranslations(): void;

    public static function disableDeleteTranslationsCascade(): void;

    public static function enableDeleteTranslationsCascade(): void;

    public function deleteTranslations($locales = null): self;

    public function getEnforcedLocale(): ?string;

    public function getNewTranslation(string $locale): Model;

    public function getTranslation(?string $locale = null, bool $withFallback = null): ?Model;

    public function getTranslationOrNew(?string $locale = null): Model;

    public function getTranslationOrFail(string $locale): Model;

    public function getTranslationsArray(): array;

    public function hasTranslation(?string $locale = null): bool;

    public function isTranslationAttribute(string $key): bool;

    public function replicateWithTranslations(array $except = null): Model;

    public function setEnforcedLocale(?string $locale);

    public function translate(?string $locale = null, bool $withFallback = false): ?Model;

    public function translateOrDefault(?string $locale = null): ?Model;

    public function translateOrNew(?string $locale = null): Model;

    public function translateOrFail(string $locale): Model;

    public function translations(): HasMany;

    public function isEmptyTranslatableAttribute(string $key, $value): bool;

    public function getLocaleName(): string;

    public function getQualifiedLocaleName(): string;
}
